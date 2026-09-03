<?php
/**
 * Bounded publication staging service.
 *
 * @package AgentPress
 */

namespace AgentPress\Content;

use AgentPress\Changes\ChangeCoordinator;
use AgentPress\Errors\ErrorFactory;
use AgentPress\Results\ResultFactory;

/**
 * Stages publication of one post or page for explicit wp-admin approval.
 */
final class PublishContentService {
	/**
	 * Durable mutation coordinator.
	 *
	 * @var ChangeCoordinator|object
	 */
	private $coordinator;

	/**
	 * Construct the publication staging service.
	 *
	 * @param ChangeCoordinator|object|null $coordinator Optional coordinator.
	 */
	public function __construct( $coordinator = null ) {
		$this->coordinator = $coordinator ?? new ChangeCoordinator();
	}

	/**
	 * Stage one publication proposal without publishing immediately.
	 *
	 * @param array<string, mixed> $input Validated Ability input.
	 * @return array<string, mixed>|\WP_Error
	 */
	public function execute( $input ) {
		$validated = $this->validate( $input );
		if ( is_wp_error( $validated ) ) {
			return $validated;
		}
		$actor_id   = (int) get_current_user_id();
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
		$post_type_obj = get_post_type_object( $post->post_type );
		$cap           = is_object( $post_type_obj ) && isset( $post_type_obj->cap->publish_posts ) ? $post_type_obj->cap->publish_posts : 'publish_posts';
		if ( ! current_user_can( $cap ) ) {
			return ErrorFactory::make( 'AP_PERMISSION_DENIED' );
		}
		if ( 'publish' === $post->post_status ) {
			return ErrorFactory::make( 'AP_STATE_CONFLICT' );
		}

		$before = self::post_state( $post );
		$after  = array_merge( $before, array( 'post_status' => 'publish' ) );

		$command = array(
			'actor_user_id'   => $actor_id,
			'ability'         => 'agentpress/publish-content',
			'operation'       => 'publish',
			'object_type'     => (string) $post->post_type,
			'object_id'       => $content_id,
			'idempotency_key' => (string) $input['idempotency_key'],
			'before'          => $before,
			'after'           => $after,
		);
		if ( isset( $input['change_set_id'] ) ) {
			$command['change_set_id'] = (int) $input['change_set_id'];
		}

		$coordinated = $this->coordinator->stage( $command );
		if ( is_wp_error( $coordinated ) ) {
			return $coordinated;
		}

		return ResultFactory::success(
			array(
				'status'          => 'PENDING_APPROVAL',
				'content_id'      => $content_id,
				'proposed_status' => 'publish',
				'change_set_id'   => (int) $coordinated['change_set_id'],
				'change_set_ref'  => 'AP-' . (int) $coordinated['change_set_id'],
				'change_id'       => (int) $coordinated['change_id'],
				'expires_at'      => isset( $coordinated['expires_at'] ) ? (string) $coordinated['expires_at'] : '',
				'replayed'        => ! empty( $coordinated['replayed'] ),
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
		$allowed = array( 'content_id', 'change_set_id', 'idempotency_key' );
		if ( ! is_array( $input ) || array_diff( array_keys( $input ), $allowed ) || ! isset( $input['content_id'], $input['idempotency_key'] ) ) {
			return ErrorFactory::make( 'AP_SCHEMA_INVALID' );
		}
		if ( ! is_int( $input['content_id'] ) || $input['content_id'] <= 0 || ( isset( $input['change_set_id'] ) && ( ! is_int( $input['change_set_id'] ) || $input['change_set_id'] <= 0 ) ) ) {
			return ErrorFactory::make( 'AP_SCHEMA_INVALID' );
		}
		if ( ! is_string( $input['idempotency_key'] ) || 1 !== preg_match( '/^[A-Za-z0-9._:-]{8,64}$/', $input['idempotency_key'] ) ) {
			return ErrorFactory::make( 'AP_SCHEMA_INVALID' );
		}
		return true;
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
