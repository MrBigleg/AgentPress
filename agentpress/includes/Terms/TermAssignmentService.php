<?php
/**
 * Atomic existing-term assignment and staging.
 *
 * @package AgentPress
 */

namespace AgentPress\Terms;

use AgentPress\Changes\ChangeCoordinator;
use AgentPress\Errors\ErrorFactory;
use AgentPress\Policy\AgentCreatedDraftLookup;
use AgentPress\Results\ResultFactory;

/**
 * Applies terms to AgentPress drafts and stages all other editable posts.
 */
final class TermAssignmentService {
	/**
	 * Durable mutation coordinator.
	 *
	 * @var ChangeCoordinator|object
	 */
	private $coordinator;

	/**
	 * Durable draft authority.
	 *
	 * @var AgentCreatedDraftLookup|object
	 */
	private $drafts;

	/**
	 * Construct the assignment service.
	 *
	 * @param ChangeCoordinator|object|null       $coordinator Optional coordinator.
	 * @param AgentCreatedDraftLookup|object|null $drafts      Optional draft lookup.
	 */
	public function __construct( $coordinator = null, $drafts = null ) {
		$this->coordinator = $coordinator ?? new ChangeCoordinator();
		$this->drafts      = $drafts ?? new AgentCreatedDraftLookup();
	}

	/**
	 * Apply or stage one exact existing-term assignment.
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
		$taxonomy   = (string) $input['taxonomy'];
		$post       = get_post( $content_id );
		if ( ! is_object( $post ) ) {
			return ErrorFactory::make( 'AP_CONTENT_NOT_FOUND' );
		}
		if ( 'post' !== $post->post_type ) {
			return ErrorFactory::make( 'AP_UNSUPPORTED_POST_TYPE' );
		}
		$taxonomy_object = get_taxonomy( $taxonomy );
		if ( $actor_id <= 0 || ! current_user_can( 'edit_post', $content_id ) || ! is_object( $taxonomy_object ) || ! current_user_can( $taxonomy_object->cap->assign_terms ) ) {
			return ErrorFactory::make( 'AP_PERMISSION_DENIED' );
		}

		$requested = array_values( array_unique( array_map( 'intval', $input['term_ids'] ) ) );
		sort( $requested, SORT_NUMERIC );
		foreach ( $requested as $term_id ) {
			$term = get_term( $term_id, $taxonomy );
			if ( ! is_object( $term ) || is_wp_error( $term ) ) {
				return ErrorFactory::make( 'AP_TERM_NOT_FOUND' );
			}
		}

		$current = wp_get_object_terms( $content_id, $taxonomy, array( 'fields' => 'ids' ) );
		if ( is_wp_error( $current ) ) {
			return ErrorFactory::make( 'AP_INTERNAL_ERROR' );
		}
		$current = array_values( array_unique( array_map( 'intval', $current ) ) );
		sort( $current, SORT_NUMERIC );
		$mode  = isset( $input['mode'] ) ? (string) $input['mode'] : 'replace';
		$final = 'append' === $mode ? array_values( array_unique( array_merge( $current, $requested ) ) ) : $requested;
		sort( $final, SORT_NUMERIC );

		$automatic = 'draft' === $post->post_status && true === $this->drafts->contains( $content_id );
		$command   = array(
			'actor_user_id'   => $actor_id,
			'ability'         => 'agentpress/assign-terms',
			'operation'       => 'assign_terms',
			'object_type'     => 'post',
			'object_id'       => $content_id,
			'idempotency_key' => (string) $input['idempotency_key'],
			'before'          => $automatic ? array() : array(
				'content_id' => $content_id,
				'taxonomy'   => $taxonomy,
				'term_ids'   => $current,
			),
			'after'           => array(
				'content_id'         => $content_id,
				'taxonomy'           => $taxonomy,
				'mode'               => $mode,
				'requested_term_ids' => $requested,
				'term_ids'           => $final,
			),
		);
		if ( isset( $input['change_set_id'] ) ) {
			$command['change_set_id'] = (int) $input['change_set_id'];
		}

		if ( $automatic ) {
			$coordinated = $this->coordinator->apply(
				$command,
				static function () use ( $content_id, $taxonomy, $mode, $requested, $current, $final ) {
					$assigned = wp_set_object_terms( $content_id, $final, $taxonomy, false );
					if ( is_wp_error( $assigned ) ) {
						return ErrorFactory::make( 'AP_INTERNAL_ERROR' );
					}
					$assigned = array_values( array_unique( array_map( 'intval', $assigned ) ) );
					sort( $assigned, SORT_NUMERIC );
					if ( $assigned !== $final ) {
						return ErrorFactory::make( 'AP_INTERNAL_ERROR' );
					}
					return array(
						'object_id' => $content_id,
						'before'    => array(
							'content_id' => $content_id,
							'taxonomy'   => $taxonomy,
							'term_ids'   => $current,
						),
						'after'     => array(
							'content_id'         => $content_id,
							'taxonomy'           => $taxonomy,
							'mode'               => $mode,
							'requested_term_ids' => $requested,
							'term_ids'           => $assigned,
						),
					);
				}
			);
		} else {
			$coordinated = $this->coordinator->stage( $command );
		}
		if ( is_wp_error( $coordinated ) ) {
			return $coordinated;
		}

		$status = $automatic ? 'APPLIED' : 'PENDING_APPROVAL';
		return ResultFactory::success(
			array(
				'status'            => $status,
				'content_id'        => $content_id,
				'taxonomy'          => $taxonomy,
				'term_ids'          => $final,
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
		$allowed = array( 'content_id', 'taxonomy', 'term_ids', 'mode', 'change_set_id', 'idempotency_key' );
		if ( array_diff( array_keys( $input ), $allowed ) || ! isset( $input['content_id'], $input['taxonomy'], $input['term_ids'], $input['idempotency_key'] ) ) {
			return ErrorFactory::make( 'AP_SCHEMA_INVALID' );
		}
		if ( ! is_int( $input['content_id'] ) || $input['content_id'] <= 0 || ! is_string( $input['taxonomy'] ) || ! in_array( $input['taxonomy'], array( 'category', 'post_tag' ), true ) ) {
			return ErrorFactory::make( 'AP_UNSUPPORTED_TAXONOMY' );
		}
		if ( ! is_array( $input['term_ids'] ) || count( $input['term_ids'] ) < 1 || count( $input['term_ids'] ) > 50 || count( array_unique( $input['term_ids'], SORT_REGULAR ) ) !== count( $input['term_ids'] ) ) {
			return ErrorFactory::make( 'AP_SCHEMA_INVALID' );
		}
		foreach ( $input['term_ids'] as $term_id ) {
			if ( ! is_int( $term_id ) || $term_id <= 0 ) {
				return ErrorFactory::make( 'AP_SCHEMA_INVALID' );
			}
		}
		if ( isset( $input['mode'] ) && ( ! is_string( $input['mode'] ) || ! in_array( $input['mode'], array( 'replace', 'append' ), true ) ) ) {
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
