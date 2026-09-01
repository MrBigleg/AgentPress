<?php
/**
 * Safe post and page draft creation.
 *
 * @package AgentPress
 */

namespace AgentPress\Content;

use AgentPress\Changes\ChangeCoordinator;
use AgentPress\Errors\ErrorFactory;
use AgentPress\Results\ResultFactory;

/**
 * Creates one forced draft through the durable R1 coordinator.
 */
final class DraftCreationService {
	/**
	 * Durable mutation coordinator.
	 *
	 * @var ChangeCoordinator|object
	 */
	private $coordinator;

	/**
	 * Construct the draft service.
	 *
	 * @param ChangeCoordinator|object|null $coordinator Optional coordinator.
	 */
	public function __construct( $coordinator = null ) {
		$this->coordinator = $coordinator ?? new ChangeCoordinator();
	}

	/**
	 * Create or replay one post/page draft.
	 *
	 * @param array<string, mixed> $input Validated Ability input.
	 * @return array<string, mixed>|\WP_Error
	 */
	public function execute( $input ) {
		$validated = $this->validate( $input );
		if ( is_wp_error( $validated ) ) {
			return $validated;
		}

		$actor_id  = get_current_user_id();
		$post_type = (string) $input['post_type'];
		if ( $actor_id <= 0 || ! current_user_can( get_post_type_object( $post_type )->cap->create_posts ) ) {
			return ErrorFactory::make( 'AP_PERMISSION_DENIED' );
		}

		$parent_id = isset( $input['parent_id'] ) ? (int) $input['parent_id'] : 0;
		if ( $parent_id > 0 ) {
			$parent = get_post( $parent_id );
			if ( ! is_object( $parent ) || 'page' !== $parent->post_type ) {
				return ErrorFactory::make( 'AP_CONTENT_NOT_FOUND' );
			}
			if ( ! current_user_can( 'read_post', $parent_id ) ) {
				return ErrorFactory::make( 'AP_PERMISSION_DENIED' );
			}
		}

		$after   = array(
			'post_type'    => $post_type,
			'post_status'  => 'draft',
			'post_author'  => $actor_id,
			'post_title'   => (string) $input['title'],
			'post_content' => isset( $input['content'] ) ? (string) $input['content'] : '',
			'post_excerpt' => isset( $input['excerpt'] ) ? (string) $input['excerpt'] : '',
			'post_name'    => isset( $input['slug'] ) ? (string) $input['slug'] : '',
			'post_parent'  => $parent_id,
		);
		$command = array(
			'actor_user_id'   => $actor_id,
			'ability'         => 'agentpress/create-draft',
			'operation'       => 'create',
			'object_type'     => $post_type,
			'idempotency_key' => (string) $input['idempotency_key'],
			'before'          => array(),
			'after'           => $after,
		);
		foreach ( array( 'change_set_id', 'change_set_title' ) as $field ) {
			if ( isset( $input[ $field ] ) ) {
				$command[ $field ] = $input[ $field ];
			}
		}

		$coordinated = $this->coordinator->apply(
			$command,
			static function () use ( $after ) {
				$post_id = wp_insert_post( $after, true );
				if ( is_wp_error( $post_id ) || (int) $post_id <= 0 ) {
					return ErrorFactory::make( 'AP_INTERNAL_ERROR' );
				}
				$post = get_post( (int) $post_id );
				return array(
					'object_id' => (int) $post_id,
					'before'    => array(),
					'after'     => self::post_state( $post ),
				);
			}
		);
		if ( is_wp_error( $coordinated ) ) {
			return $coordinated;
		}

		$post_id = isset( $coordinated['object_id'] ) ? (int) $coordinated['object_id'] : 0;
		$post    = get_post( $post_id );
		if ( ! is_object( $post ) || $post_type !== $post->post_type || 'draft' !== $post->post_status || $actor_id !== (int) $post->post_author ) {
			return ErrorFactory::make( 'AP_INTERNAL_ERROR' );
		}

		return ResultFactory::success(
			array(
				'status'         => 'APPLIED',
				'content_id'     => $post_id,
				'post_status'    => 'draft',
				'edit_url'       => set_url_scheme( get_edit_post_link( $post_id, 'raw' ), 'https' ),
				'change_set_id'  => (int) $coordinated['change_set_id'],
				'change_set_ref' => 'AP-' . (int) $coordinated['change_set_id'],
				'change_id'      => (int) $coordinated['change_id'],
				'replayed'       => ! empty( $coordinated['replayed'] ),
			)
		);
	}

	/**
	 * Validate the closed direct-service input boundary.
	 *
	 * @param array<string, mixed> $input Input.
	 * @return true|\WP_Error
	 */
	private function validate( $input ) {
		$allowed = array( 'post_type', 'title', 'content', 'excerpt', 'slug', 'parent_id', 'change_set_id', 'change_set_title', 'idempotency_key' );
		if ( array_diff( array_keys( $input ), $allowed ) || ! isset( $input['post_type'], $input['title'], $input['idempotency_key'] ) ) {
			return ErrorFactory::make( 'AP_SCHEMA_INVALID' );
		}
		if ( ! is_string( $input['post_type'] ) || ! in_array( $input['post_type'], array( 'post', 'page' ), true ) ) {
			return ErrorFactory::make( 'AP_UNSUPPORTED_POST_TYPE' );
		}
		$string_bounds = array(
			'title'            => array( 1, 200 ),
			'content'          => array( 0, 200000 ),
			'excerpt'          => array( 0, 5000 ),
			'slug'             => array( 1, 200 ),
			'change_set_title' => array( 1, 200 ),
		);
		foreach ( $string_bounds as $field => $bounds ) {
			if ( isset( $input[ $field ] ) && ( ! is_string( $input[ $field ] ) || $this->text_length( $input[ $field ] ) < $bounds[0] || $this->text_length( $input[ $field ] ) > $bounds[1] ) ) {
				return ErrorFactory::make( 'AP_SCHEMA_INVALID' );
			}
		}
		if ( isset( $input['slug'] ) && 1 !== preg_match( '/^[a-z0-9-]+$/', $input['slug'] ) ) {
			return ErrorFactory::make( 'AP_SCHEMA_INVALID' );
		}
		foreach ( array(
			'parent_id'     => 0,
			'change_set_id' => 1,
		) as $field => $minimum ) {
			if ( isset( $input[ $field ] ) && ( ! is_int( $input[ $field ] ) || $input[ $field ] < $minimum ) ) {
				return ErrorFactory::make( 'AP_SCHEMA_INVALID' );
			}
		}
		if ( 'post' === $input['post_type'] && ! empty( $input['parent_id'] ) ) {
			return ErrorFactory::make( 'AP_SCHEMA_INVALID' );
		}
		if ( isset( $input['change_set_id'], $input['change_set_title'] ) || ! is_string( $input['idempotency_key'] ) || 1 !== preg_match( '/^[A-Za-z0-9._:-]{8,64}$/', $input['idempotency_key'] ) ) {
			return ErrorFactory::make( 'AP_SCHEMA_INVALID' );
		}
		return true;
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
	 * Project one durable post state for the change record.
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
