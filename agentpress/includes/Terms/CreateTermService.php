<?php
/**
 * Bounded taxonomy creation staging service.
 *
 * @package AgentPress
 */

namespace AgentPress\Terms;

use AgentPress\Changes\ChangeCoordinator;
use AgentPress\Errors\ErrorFactory;
use AgentPress\Results\ResultFactory;

/**
 * Stages creation of one category or tag for wp-admin approval.
 */
final class CreateTermService {
	/**
	 * Durable mutation coordinator.
	 *
	 * @var ChangeCoordinator|object
	 */
	private $coordinator;

	/**
	 * Construct the taxonomy staging service.
	 *
	 * @param ChangeCoordinator|object|null $coordinator Optional coordinator.
	 */
	public function __construct( $coordinator = null ) {
		$this->coordinator = $coordinator ?? new ChangeCoordinator();
	}

	/**
	 * Stage one term proposal without creating the term.
	 *
	 * @param array<string, mixed> $input Validated Ability input.
	 * @return array<string, mixed>|\WP_Error
	 */
	public function execute( $input ) {
		$validated = $this->validate( $input );
		if ( is_wp_error( $validated ) ) {
			return $validated;
		}
		$actor_id     = (int) get_current_user_id();
		$taxonomy     = (string) $input['taxonomy'];
		$taxonomy_obj = get_taxonomy( $taxonomy );
		if ( $actor_id <= 0 || ! is_object( $taxonomy_obj ) || ! current_user_can( $taxonomy_obj->cap->manage_terms ) ) {
			return ErrorFactory::make( 'AP_PERMISSION_DENIED' );
		}
		$parent_id = isset( $input['parent_id'] ) ? (int) $input['parent_id'] : 0;
		if ( $parent_id > 0 ) {
			if ( 'post_tag' === $taxonomy ) {
				return ErrorFactory::make( 'AP_SCHEMA_INVALID' );
			}
			$parent = get_term( $parent_id, $taxonomy );
			if ( ! is_object( $parent ) ) {
				return ErrorFactory::make( 'AP_TERM_NOT_FOUND' );
			}
		}

		$proposed_term = array(
			'taxonomy'    => $taxonomy,
			'name'        => sanitize_text_field( (string) $input['name'] ),
			'slug'        => isset( $input['slug'] ) ? sanitize_title( (string) $input['slug'] ) : '',
			'description' => isset( $input['description'] ) ? (string) $input['description'] : '',
			'parent_id'   => $parent_id,
		);

		$command = array(
			'actor_user_id'   => $actor_id,
			'ability'         => 'agentpress/create-term',
			'operation'       => 'create',
			'object_type'     => $taxonomy,
			'object_id'       => 0,
			'idempotency_key' => (string) $input['idempotency_key'],
			'before'          => array(),
			'after'           => $proposed_term,
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
				'status'         => 'PENDING_APPROVAL',
				'proposed_term'  => $proposed_term,
				'change_set_id'  => (int) $coordinated['change_set_id'],
				'change_set_ref' => 'AP-' . (int) $coordinated['change_set_id'],
				'change_id'      => (int) $coordinated['change_id'],
				'expires_at'     => isset( $coordinated['expires_at'] ) ? (string) $coordinated['expires_at'] : '',
				'replayed'       => ! empty( $coordinated['replayed'] ),
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
		$allowed = array( 'taxonomy', 'name', 'slug', 'description', 'parent_id', 'change_set_id', 'idempotency_key' );
		if ( ! is_array( $input ) || array_diff( array_keys( $input ), $allowed ) || ! isset( $input['taxonomy'], $input['name'], $input['idempotency_key'] ) ) {
			return ErrorFactory::make( 'AP_SCHEMA_INVALID' );
		}
		if ( ! in_array( $input['taxonomy'], array( 'category', 'post_tag' ), true ) ) {
			return ErrorFactory::make( 'AP_UNSUPPORTED_TAXONOMY' );
		}
		if ( ! is_string( $input['name'] ) || 0 === strlen( trim( $input['name'] ) ) || mb_strlen( $input['name'] ) > 200 ) {
			return ErrorFactory::make( 'AP_SCHEMA_INVALID' );
		}
		if ( isset( $input['slug'] ) && ( ! is_string( $input['slug'] ) || 1 !== preg_match( '/^[a-z0-9-]+$/', $input['slug'] ) ) ) {
			return ErrorFactory::make( 'AP_SCHEMA_INVALID' );
		}
		if ( isset( $input['description'] ) && ( ! is_string( $input['description'] ) || mb_strlen( $input['description'] ) > 5000 ) ) {
			return ErrorFactory::make( 'AP_SCHEMA_INVALID' );
		}
		if ( isset( $input['parent_id'] ) && ( ! is_int( $input['parent_id'] ) || $input['parent_id'] < 0 ) ) {
			return ErrorFactory::make( 'AP_SCHEMA_INVALID' );
		}
		if ( isset( $input['change_set_id'] ) && ( ! is_int( $input['change_set_id'] ) || $input['change_set_id'] <= 0 ) ) {
			return ErrorFactory::make( 'AP_SCHEMA_INVALID' );
		}
		if ( ! is_string( $input['idempotency_key'] ) || 1 !== preg_match( '/^[A-Za-z0-9._:-]{8,64}$/', $input['idempotency_key'] ) ) {
			return ErrorFactory::make( 'AP_SCHEMA_INVALID' );
		}
		return true;
	}
}
