<?php
/**
 * Bounded post and page updates.
 *
 * @package AgentPress
 */

namespace AgentPress\Content;

use AgentPress\Changes\ChangeCoordinator;
use AgentPress\Errors\ErrorFactory;
use AgentPress\Policy\AgentCreatedDraftLookup;
use AgentPress\Results\ResultFactory;

/**
 * Applies patches to AgentPress drafts and stages every other editable target.
 */
final class ContentUpdateService {
	/**
	 * Durable mutation coordinator.
	 *
	 * @var ChangeCoordinator|object
	 */
	private $coordinator;

	/**
	 * Durable draft-authority lookup.
	 *
	 * @var AgentCreatedDraftLookup|object
	 */
	private $drafts;

	/**
	 * Construct the content update service.
	 *
	 * @param ChangeCoordinator|object|null       $coordinator Optional coordinator.
	 * @param AgentCreatedDraftLookup|object|null $drafts      Optional draft lookup.
	 */
	public function __construct( $coordinator = null, $drafts = null ) {
		$this->coordinator = $coordinator ?? new ChangeCoordinator();
		$this->drafts      = $drafts ?? new AgentCreatedDraftLookup();
	}

	/**
	 * Apply or stage one bounded content patch.
	 *
	 * @param array<string, mixed> $input Validated Ability input.
	 * @return array<string, mixed>|\WP_Error
	 */
	public function execute( $input ) {
		$validated = $this->validate( $input );
		if ( is_wp_error( $validated ) ) {
			return $validated;
		}

		$actor_id   = get_current_user_id();
		$content_id = (int) $input['content_id'];
		$post       = get_post( $content_id );
		if ( ! is_object( $post ) ) {
			return ErrorFactory::make( 'AP_CONTENT_NOT_FOUND' );
		}
		if ( ! in_array( $post->post_type, array( 'post', 'page' ), true ) ) {
			return ErrorFactory::make( 'AP_UNSUPPORTED_POST_TYPE' );
		}
		if ( $actor_id <= 0 || ! current_user_can( 'edit_post', $content_id ) ) {
			return ErrorFactory::make( 'AP_PERMISSION_DENIED' );
		}

		$parent = $this->validate_parent( $input, $post );
		if ( is_wp_error( $parent ) ) {
			return $parent;
		}

		$before = self::post_state( $post );
		$patch  = $this->sanitize_patch( $input );
		$after  = array_merge( $before, $patch );

		$automatic = 'draft' === $post->post_status && true === $this->drafts->contains( $content_id );
		$command   = array(
			'actor_user_id'   => $actor_id,
			'ability'         => 'agentpress/update-content',
			'operation'       => 'update_content',
			'object_type'     => (string) $post->post_type,
			'object_id'       => $content_id,
			'idempotency_key' => (string) $input['idempotency_key'],
			'before'          => $automatic ? array() : $before,
			'after'           => $after,
		);
		if ( isset( $input['change_set_id'] ) ) {
			$command['change_set_id'] = (int) $input['change_set_id'];
		}

		if ( $automatic ) {
			$coordinated = $this->coordinator->apply(
				$command,
				static function () use ( $content_id, $patch, $before ) {
					$updates = array( 'ID' => $content_id );
					foreach ( $patch as $field => $value ) {
						$updates[ $field ] = $value;
					}
					$updated = wp_update_post( $updates, true );
					if ( is_wp_error( $updated ) || $content_id !== (int) $updated ) {
						return ErrorFactory::make( 'AP_INTERNAL_ERROR' );
					}
					$fresh = get_post( $content_id );
					if ( ! is_object( $fresh ) ) {
						return ErrorFactory::make( 'AP_INTERNAL_ERROR' );
					}
					return array(
						'object_id' => $content_id,
						'before'    => $before,
						'after'     => self::post_state( $fresh ),
					);
				}
			);
		} else {
			$coordinated = $this->coordinator->stage( $command );
		}
		if ( is_wp_error( $coordinated ) ) {
			return $coordinated;
		}

		return ResultFactory::success(
			array(
				'status'            => $automatic ? 'APPLIED' : 'PENDING_APPROVAL',
				'content_id'        => $content_id,
				'approval_required' => ! $automatic,
				'expires_at'        => isset( $coordinated['expires_at'] ) ? (string) $coordinated['expires_at'] : '',
				'change_set_id'     => (int) $coordinated['change_set_id'],
				'change_set_ref'    => 'AP-' . (int) $coordinated['change_set_id'],
				'change_id'         => (int) $coordinated['change_id'],
				'replayed'          => ! empty( $coordinated['replayed'] ),
			)
		);
	}

	/**
	 * Validate the direct-service closed boundary.
	 *
	 * @param array<string, mixed> $input Input.
	 * @return true|\WP_Error
	 */
	private function validate( $input ) {
		$patch_fields = array( 'title', 'content', 'excerpt', 'slug', 'parent_id' );
		$allowed      = array_merge( array( 'content_id', 'change_set_id', 'idempotency_key' ), $patch_fields );
		if ( ! is_array( $input ) || array_diff( array_keys( $input ), $allowed ) || ! isset( $input['content_id'], $input['idempotency_key'] ) || ! array_intersect( $patch_fields, array_keys( $input ) ) ) {
			return ErrorFactory::make( 'AP_SCHEMA_INVALID' );
		}
		if ( ! is_int( $input['content_id'] ) || $input['content_id'] <= 0 || ( isset( $input['change_set_id'] ) && ( ! is_int( $input['change_set_id'] ) || $input['change_set_id'] <= 0 ) ) ) {
			return ErrorFactory::make( 'AP_SCHEMA_INVALID' );
		}
		$bounds = array(
			'title'   => array( 1, 200 ),
			'content' => array( 0, 200000 ),
			'excerpt' => array( 0, 5000 ),
			'slug'    => array( 1, 200 ),
		);
		foreach ( $bounds as $field => $range ) {
			if ( isset( $input[ $field ] ) && ( ! is_string( $input[ $field ] ) || $this->text_length( $input[ $field ] ) < $range[0] || $this->text_length( $input[ $field ] ) > $range[1] ) ) {
				return ErrorFactory::make( 'AP_SCHEMA_INVALID' );
			}
		}
		if ( isset( $input['slug'] ) && 1 !== preg_match( '/^[a-z0-9-]+$/', $input['slug'] ) ) {
			return ErrorFactory::make( 'AP_SCHEMA_INVALID' );
		}
		if ( isset( $input['parent_id'] ) && ( ! is_int( $input['parent_id'] ) || $input['parent_id'] < 0 ) ) {
			return ErrorFactory::make( 'AP_SCHEMA_INVALID' );
		}
		if ( ! is_string( $input['idempotency_key'] ) || 1 !== preg_match( '/^[A-Za-z0-9._:-]{8,64}$/', $input['idempotency_key'] ) ) {
			return ErrorFactory::make( 'AP_SCHEMA_INVALID' );
		}
		return true;
	}

	/**
	 * Enforce page-only, readable, acyclic parent changes.
	 *
	 * @param array<string, mixed> $input Input.
	 * @param object               $post  Target post.
	 * @return true|\WP_Error
	 */
	private function validate_parent( $input, $post ) {
		if ( ! array_key_exists( 'parent_id', $input ) ) {
			return true;
		}
		if ( 'page' !== $post->post_type ) {
			return ErrorFactory::make( 'AP_SCHEMA_INVALID' );
		}
		$parent_id = (int) $input['parent_id'];
		if ( 0 === $parent_id ) {
			return true;
		}
		$parent = get_post( $parent_id );
		if ( ! is_object( $parent ) || 'page' !== $parent->post_type ) {
			return ErrorFactory::make( 'AP_CONTENT_NOT_FOUND' );
		}
		if ( ! current_user_can( 'read_post', $parent_id ) ) {
			return ErrorFactory::make( 'AP_PERMISSION_DENIED' );
		}
		if ( (int) $post->ID === $parent_id || in_array( (int) $post->ID, array_map( 'intval', get_post_ancestors( $parent ) ), true ) ) {
			return ErrorFactory::make( 'AP_STATE_CONFLICT' );
		}
		return true;
	}

	/**
	 * Sanitize the accepted patch using the caller's WordPress HTML capability.
	 *
	 * @param array<string, mixed> $input Input.
	 * @return array<string, mixed>
	 */
	private function sanitize_patch( $input ) {
		$patch = array();
		if ( array_key_exists( 'title', $input ) ) {
			$patch['post_title'] = sanitize_text_field( $input['title'] );
		}
		if ( array_key_exists( 'content', $input ) ) {
			$patch['post_content'] = current_user_can( 'unfiltered_html' ) ? $input['content'] : wp_kses_post( $input['content'] );
		}
		if ( array_key_exists( 'excerpt', $input ) ) {
			$patch['post_excerpt'] = current_user_can( 'unfiltered_html' ) ? $input['excerpt'] : wp_kses_post( $input['excerpt'] );
		}
		if ( array_key_exists( 'slug', $input ) ) {
			$patch['post_name'] = sanitize_title( $input['slug'] );
		}
		if ( array_key_exists( 'parent_id', $input ) ) {
			$patch['post_parent'] = (int) $input['parent_id'];
		}
		return $patch;
	}

	/**
	 * Count Unicode characters with a safe runtime fallback.
	 *
	 * @param string $value Text.
	 * @return int
	 */
	private function text_length( $value ) {
		if ( function_exists( 'mb_strlen' ) ) {
			return mb_strlen( $value );
		}
		$count = preg_match_all( '/./us', $value, $matches );
		return false === $count ? strlen( $value ) : $count;
	}

	/**
	 * Project the complete content state used for proposals and stale checks.
	 *
	 * @param object $post Post.
	 * @return array<string, mixed>
	 */
	private static function post_state( $post ) {
		return array(
			'id'           => (int) $post->ID,
			'post_type'    => (string) $post->post_type,
			'post_status'  => (string) $post->post_status,
			'post_author'  => (int) $post->post_author,
			'post_title'   => (string) $post->post_title,
			'post_content' => (string) $post->post_content,
			'post_excerpt' => (string) $post->post_excerpt,
			'post_name'    => (string) $post->post_name,
			'post_parent'  => (int) $post->post_parent,
		);
	}
}
