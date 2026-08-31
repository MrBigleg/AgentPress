<?php
/**
 * Exact execution authorization and Safe Mode evaluation.
 *
 * @package AgentPress
 */

namespace AgentPress\Policy;

use AgentPress\Errors\ErrorFactory;
use AgentPress\WebMCP\AbilityMap;

/**
 * Rechecks current object/action authority independently of discovery.
 */
final class ExecutionPolicy {
	/**
	 * Live capability resolver.
	 *
	 * @var CapabilityResolver
	 */
	private $capabilities;

	/**
	 * Safe Mode policy.
	 *
	 * @var SafeModePolicy
	 */
	private $safe_mode;

	/**
	 * Risk classifier.
	 *
	 * @var RiskClassifier
	 */
	private $classifier;

	/**
	 * Post resolver.
	 *
	 * @var callable
	 */
	private $post_resolver;

	/**
	 * AgentPress draft-authority lookup.
	 *
	 * @var callable
	 */
	private $agent_created;

	/**
	 * Constructor.
	 *
	 * @param CapabilityResolver|null $capabilities  Optional capability resolver.
	 * @param SafeModePolicy|null     $safe_mode     Optional Safe Mode policy.
	 * @param RiskClassifier|null     $classifier    Optional classifier.
	 * @param callable|null           $post_resolver Optional post resolver.
	 * @param callable|null           $agent_created Optional applied-change lookup.
	 */
	public function __construct( $capabilities = null, $safe_mode = null, $classifier = null, $post_resolver = null, $agent_created = null ) {
		$this->capabilities  = $capabilities ?? new CapabilityResolver();
		$this->classifier    = $classifier ?? new RiskClassifier();
		$this->safe_mode     = $safe_mode ?? new SafeModePolicy( $this->classifier );
		$this->post_resolver = $post_resolver ?? 'get_post';
		if ( null === $agent_created ) {
			$lookup        = new AgentCreatedDraftLookup();
			$agent_created = array( $lookup, 'contains' );
		}
		$this->agent_created = $agent_created;
	}

	/**
	 * Return contextual risk/mode or one stable policy error.
	 *
	 * @param string               $ability Ability name.
	 * @param array<string, mixed> $context Validated target context.
	 * @return array<string, string>|\WP_Error
	 */
	public function evaluate( $ability, $context = array() ) {
		if ( ! $this->capabilities->can_read() ) {
			return ErrorFactory::make( 'AP_NOT_AUTHENTICATED' );
		}
		if ( ! AbilityMap::contains( $ability ) ) {
			return ErrorFactory::make( 'AP_POLICY_BLOCKED' );
		}

		$post = null;
		if ( in_array( $ability, array( 'agentpress/get-content', 'agentpress/update-content', 'agentpress/publish-content', 'agentpress/assign-terms' ), true ) ) {
			$post = isset( $context['content_id'] ) ? call_user_func( $this->post_resolver, (int) $context['content_id'] ) : null;
			if ( ! is_object( $post ) ) {
				return ErrorFactory::make( 'AP_CONTENT_NOT_FOUND' );
			}
			if ( ! in_array( $post->post_type, array( 'post', 'page' ), true ) ) {
				return ErrorFactory::make( 'AP_UNSUPPORTED_POST_TYPE' );
			}
		}

		$permission = $this->check_permission( $ability, $context, $post );
		if ( is_wp_error( $permission ) ) {
			return $permission;
		}

		if ( is_object( $post ) ) {
			$context['post_status']              = $post->post_status;
			$context['agentpress_created_draft'] = 'draft' === $post->post_status && true === call_user_func( $this->agent_created, (int) $post->ID );
		}

		$mode = $this->safe_mode->mode( $ability, $context );
		if ( 'blocked' === $mode ) {
			return ErrorFactory::make( 'AP_POLICY_BLOCKED' );
		}

		return array(
			'risk' => $this->classifier->classify( $ability, $context ),
			'mode' => $mode,
		);
	}

	/**
	 * Check exact capability requirements for one fixed operation.
	 *
	 * @param string               $ability Ability name.
	 * @param array<string, mixed> $context Target context.
	 * @param object|null          $post    Resolved post.
	 * @return true|\WP_Error
	 */
	private function check_permission( $ability, $context, $post ) {
		if ( 'agentpress/get-content' === $ability ) {
			return $this->allowed( $this->capabilities->can( 'read_post', (int) $post->ID ) );
		}
		if ( 'agentpress/create-draft' === $ability ) {
			$post_type = isset( $context['post_type'] ) ? (string) $context['post_type'] : '';
			if ( ! in_array( $post_type, array( 'post', 'page' ), true ) ) {
				return ErrorFactory::make( 'AP_UNSUPPORTED_POST_TYPE' );
			}
			return $this->allowed( $this->capabilities->can_create( $post_type ) );
		}
		if ( 'agentpress/update-content' === $ability ) {
			return $this->allowed( $this->capabilities->can( 'edit_post', (int) $post->ID ) );
		}
		if ( 'agentpress/publish-content' === $ability ) {
			return $this->allowed( $this->capabilities->can( 'edit_post', (int) $post->ID ) && $this->capabilities->can_publish( $post->post_type ) );
		}
		if ( 'agentpress/list-terms' === $ability || 'agentpress/create-term' === $ability ) {
			$taxonomy = isset( $context['taxonomy'] ) ? (string) $context['taxonomy'] : '';
			if ( ! in_array( $taxonomy, array( 'category', 'post_tag' ), true ) ) {
				return ErrorFactory::make( 'AP_UNSUPPORTED_TAXONOMY' );
			}
			return 'agentpress/create-term' === $ability ? $this->allowed( $this->capabilities->can_manage_terms( $taxonomy ) ) : true;
		}
		if ( 'agentpress/assign-terms' === $ability ) {
			$taxonomy = isset( $context['taxonomy'] ) ? (string) $context['taxonomy'] : '';
			if ( 'post' !== $post->post_type || ! in_array( $taxonomy, array( 'category', 'post_tag' ), true ) ) {
				return ErrorFactory::make( 'AP_UNSUPPORTED_TAXONOMY' );
			}
			return $this->allowed( $this->capabilities->can( 'edit_post', (int) $post->ID ) && $this->capabilities->can_assign_terms( $taxonomy ) );
		}
		if ( in_array( $ability, array( 'agentpress/get-navigation', 'agentpress/stage-navigation-change' ), true ) ) {
			return $this->allowed( $this->capabilities->can_manage_navigation() );
		}
		if ( 'agentpress/get-change-set' === $ability ) {
			return $this->allowed( ! empty( $context['owns_resource'] ) || $this->capabilities->can( 'manage_options' ) );
		}

		return true;
	}

	/**
	 * Convert a capability result to the stable permission result.
	 *
	 * @param bool $allowed Whether the operation is allowed.
	 * @return true|\WP_Error
	 */
	private function allowed( $allowed ) {
		return $allowed ? true : ErrorFactory::make( 'AP_PERMISSION_DENIED' );
	}
}
