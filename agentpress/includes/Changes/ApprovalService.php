<?php
/**
 * Human Change Set approval and rejection service.
 *
 * @package AgentPress
 */

namespace AgentPress\Changes;

use AgentPress\Audit\AuditLogger;
use AgentPress\Errors\ErrorFactory;
use AgentPress\Navigation\ClassicMenuAdapter;
use AgentPress\Navigation\NavigationApplyService;
use AgentPress\Results\ResultFactory;

/**
 * Approves or rejects one pending R2 proposal with stale-state and privilege guards.
 */
final class ApprovalService {
	/**
	 * Change record repository.
	 *
	 * @var ChangeRepository|object
	 */
	private $changes;

	/**
	 * Change Set repository.
	 *
	 * @var ChangeSetRepository|object
	 */
	private $sets;

	/**
	 * Canonical state hasher.
	 *
	 * @var StateHasher
	 */
	private $hasher;

	/**
	 * Parent-state reducer.
	 *
	 * @var ChangeSetStateReducer
	 */
	private $reducer;

	/**
	 * Audit logger.
	 *
	 * @var AuditLogger|object
	 */
	private $audit;

	/**
	 * Navigation apply service.
	 *
	 * @var NavigationApplyService|object
	 */
	private $navigation_apply;

	/**
	 * Classic-menu adapter.
	 *
	 * @var ClassicMenuAdapter
	 */
	private $adapter;

	/**
	 * Construct the approval service.
	 *
	 * @param ChangeRepository|object|null       $changes         Optional change repository.
	 * @param ChangeSetRepository|object|null    $sets            Optional Change Set repository.
	 * @param StateHasher|null                   $hasher          Optional canonical hasher.
	 * @param ChangeSetStateReducer|null         $reducer         Optional parent reducer.
	 * @param AuditLogger|object|null            $audit           Optional audit logger.
	 * @param NavigationApplyService|object|null $navigation_apply Optional navigation applier.
	 * @param ClassicMenuAdapter|null            $adapter         Optional classic-menu adapter.
	 */
	public function __construct( $changes = null, $sets = null, $hasher = null, $reducer = null, $audit = null, $navigation_apply = null, $adapter = null ) {
		$this->changes          = $changes ?? new ChangeRepository();
		$this->sets             = $sets ?? new ChangeSetRepository();
		$this->hasher           = $hasher ?? new StateHasher();
		$this->reducer          = $reducer ?? new ChangeSetStateReducer();
		$this->audit            = $audit ?? new AuditLogger();
		$this->navigation_apply = $navigation_apply ?? new NavigationApplyService();
		$this->adapter          = $adapter ?? new ClassicMenuAdapter();
	}

	/**
	 * Approve one pending change.
	 *
	 * @param int $change_id Change ID.
	 * @return array<string, mixed>|\WP_Error
	 */
	public function approve( $change_id ) {
		$change = $this->load_pending( $change_id );
		if ( is_wp_error( $change ) ) {
			return $change;
		}
		$actor = $this->authorize( $change );
		if ( is_wp_error( $actor ) ) {
			return $actor;
		}
		$approved_at = current_time( 'mysql', true );

		$claimed = $this->transition(
			$change_id,
			'PENDING_APPROVAL',
			array(
				'status'      => 'APPLYING',
				'approved_by' => $actor,
				'approved_at' => $approved_at,
			)
		);
		if ( ! $claimed ) {
			return ErrorFactory::make( 'AP_STATE_CONFLICT' );
		}

		$integrity = $this->verify_proposal( $change );
		if ( ! $integrity ) {
			$this->fail_claim( $change_id, 'CONFLICT' );
			$this->sync_set( (int) $change['change_set_id'] );
			return ErrorFactory::make( 'AP_STATE_CONFLICT' );
		}

		if ( ! empty( $change['expires_at'] ) && strtotime( (string) $change['expires_at'] ) <= time() ) {
			$this->transition( $change_id, 'APPLYING', array( 'status' => 'EXPIRED' ) );
			$this->sync_set( (int) $change['change_set_id'] );
			return ErrorFactory::make( 'AP_CHANGE_EXPIRED' );
		}

		$target_hash = $this->compute_target_hash( $change );
		if ( $target_hash !== $change['target_state_hash'] ) {
			$this->fail_claim( $change_id, 'CONFLICT' );
			$this->sync_set( (int) $change['change_set_id'] );
			return ErrorFactory::make( 'AP_STATE_CONFLICT' );
		}

		$applied = $this->apply_target( $change );
		if ( is_wp_error( $applied ) ) {
			$error = ErrorFactory::normalize( $applied );
			$this->transition(
				$change_id,
				'APPLYING',
				array(
					'status'     => 'FAILED',
					'error_code' => $error->get_error_code(),
				)
			);
			$this->sync_set( (int) $change['change_set_id'] );
			return $error;
		}

		$applied_at = current_time( 'mysql', true );
		$this->transition(
			$change_id,
			'APPLYING',
			array(
				'status'     => 'APPLIED',
				'object_id'  => isset( $applied['object_id'] ) ? (int) $applied['object_id'] : (int) $change['object_id'],
				'applied_at' => $applied_at,
			)
		);
		$this->sync_set( (int) $change['change_set_id'] );
		$this->audit_event( $change, 'SUCCESS', '' );

		return ResultFactory::success(
			array(
				'status'         => 'APPLIED',
				'change_id'      => $change_id,
				'change_set_id'  => (int) $change['change_set_id'],
				'change_set_ref' => 'AP-' . (int) $change['change_set_id'],
				'object_id'      => isset( $applied['object_id'] ) ? (int) $applied['object_id'] : (int) $change['object_id'],
				'applied_at'     => $applied_at,
				'approved_by'    => $actor,
				'result'         => 'SUCCESS',
			)
		);
	}

	/**
	 * Reject one pending change without mutating WordPress.
	 *
	 * @param int    $change_id Change ID.
	 * @param string $reason    Optional rejector reason.
	 * @return array<string, mixed>|\WP_Error
	 */
	public function reject( $change_id, $reason = '' ) {
		$change = $this->load_pending( $change_id );
		if ( is_wp_error( $change ) ) {
			return $change;
		}
		$actor = $this->authorize( $change );
		if ( is_wp_error( $actor ) ) {
			return $actor;
		}
		$rejected_at = current_time( 'mysql', true );
		$claimed     = $this->transition(
			$change_id,
			'PENDING_APPROVAL',
			array(
				'status'      => 'REJECTED',
				'rejected_by' => $actor,
				'rejected_at' => $rejected_at,
			)
		);
		if ( ! $claimed ) {
			return ErrorFactory::make( 'AP_STATE_CONFLICT' );
		}
		$this->sync_set( (int) $change['change_set_id'] );
		$this->audit_event( $change, 'REJECTED', '' );

		return ResultFactory::success(
			array(
				'status'         => 'REJECTED',
				'change_id'      => $change_id,
				'change_set_id'  => (int) $change['change_set_id'],
				'change_set_ref' => 'AP-' . (int) $change['change_set_id'],
				'rejected_by'    => $actor,
				'rejected_at'    => $rejected_at,
				'reason'         => $reason,
				'result'         => 'REJECTED',
			)
		);
	}

	/**
	 * Load one pending change row and verify it is pending.
	 *
	 * @param int $change_id Change ID.
	 * @return array<string, mixed>|\WP_Error
	 */
	private function load_pending( $change_id ) {
		if ( $change_id < 1 ) {
			return ErrorFactory::make( 'AP_CHANGE_NOT_FOUND' );
		}
		$change = $this->changes->find( $change_id );
		if ( ! is_array( $change ) ) {
			return ErrorFactory::make( 'AP_CHANGE_NOT_FOUND' );
		}
		if ( 'PENDING_APPROVAL' !== $change['status'] ) {
			return ErrorFactory::make( 'AP_STATE_CONFLICT' );
		}
		return $change;
	}

	/**
	 * Enforce caller identity plus the operation-specific capability.
	 *
	 * @param array<string, mixed> $change Change row.
	 * @return int|\WP_Error
	 */
	private function authorize( $change ) {
		$actor = (int) get_current_user_id();
		if ( $actor <= 0 ) {
			return ErrorFactory::make( 'AP_NOT_AUTHENTICATED' );
		}
		if ( ! current_user_can( 'manage_options' ) && $actor !== (int) $change['actor_user_id'] ) {
			return ErrorFactory::make( 'AP_PERMISSION_DENIED' );
		}
		if ( ! $this->can_apply( $change ) ) {
			return ErrorFactory::make( 'AP_PERMISSION_DENIED' );
		}
		return $actor;
	}

	/**
	 * Check whether the caller holds the stored operation-specific capability.
	 *
	 * @param array<string, mixed> $change Change row.
	 * @return bool
	 */
	private function can_apply( $change ) {
		$ability = isset( $change['ability'] ) ? (string) $change['ability'] : '';
		if ( 'agentpress/get-navigation' === $ability || 'agentpress/stage-navigation-change' === $ability ) {
			return current_user_can( 'edit_theme_options' );
		}
		if ( 'agentpress/update-content' === $ability ) {
			return current_user_can( 'edit_post', (int) $change['object_id'] );
		}
		if ( 'agentpress/assign-terms' === $ability ) {
			return current_user_can( 'edit_post', (int) $change['object_id'] );
		}
		if ( 'agentpress/publish-content' === $ability ) {
			return current_user_can( 'edit_post', (int) $change['object_id'] );
		}
		return current_user_can( 'manage_options' );
	}

	/**
	 * Verify the immutable proposal hash still matches storage.
	 *
	 * @param array<string, mixed> $change Change row.
	 * @return bool
	 */
	private function verify_proposal( $change ) {
		$computed = $this->hasher->proposal_hash(
			(string) $change['ability'],
			(string) $change['operation'],
			$this->as_array( $change['after_json'] ),
			(string) $change['target_state_hash']
		);
		return $computed === $change['proposal_hash'];
	}

	/**
	 * Recompute the current target state hash to detect concurrent change.
	 *
	 * @param array<string, mixed> $change Change row.
	 * @return string
	 */
	private function compute_target_hash( $change ) {
		$ability = isset( $change['ability'] ) ? (string) $change['ability'] : '';
		if ( 'agentpress/stage-navigation-change' === $ability ) {
			$location = $this->location( $change );
			$snapshot = $this->adapter->snapshot( $location );
			return is_wp_error( $snapshot ) ? '' : $this->hasher->state_hash( $snapshot['items'] );
		}
		if ( 'agentpress/assign-terms' === $ability ) {
			$taxonomy = isset( $change['after_json']['taxonomy'] ) ? (string) $change['after_json']['taxonomy'] : '';
			$current  = wp_get_object_terms( (int) $change['object_id'], $taxonomy, array( 'fields' => 'ids' ) );
			return $this->hasher->state_hash(
				array(
					'taxonomy' => $taxonomy,
					'term_ids' => is_array( $current ) ? array_map( 'intval', $current ) : array(),
				)
			);
		}
		$post = get_post( (int) $change['object_id'] );
		if ( ! is_object( $post ) ) {
			return '';
		}
		return $this->hasher->state_hash( $this->post_state( $post ) );
	}

	/**
	 * Apply the stored operation against the live target.
	 *
	 * @param array<string, mixed> $change Change row.
	 * @return array<string, mixed>|\WP_Error
	 */
	private function apply_target( $change ) {
		$ability = isset( $change['ability'] ) ? (string) $change['ability'] : '';
		if ( 'agentpress/stage-navigation-change' === $ability ) {
			return $this->navigation_apply->apply( $change );
		}
		if ( 'agentpress/assign-terms' === $ability ) {
			$after    = $this->as_array( $change['after_json'] );
			$taxonomy = isset( $after['taxonomy'] ) ? (string) $after['taxonomy'] : '';
			$terms    = isset( $after['term_ids'] ) ? array_map( 'intval', (array) $after['term_ids'] ) : array();
			$assigned = wp_set_object_terms( (int) $change['object_id'], $terms, $taxonomy, false );
			if ( is_wp_error( $assigned ) ) {
				return ErrorFactory::make( 'AP_INTERNAL_ERROR' );
			}
			return array( 'object_id' => (int) $change['object_id'] );
		}
		if ( 'agentpress/update-content' === $ability ) {
			return $this->apply_content( $change );
		}
		return ErrorFactory::make( 'AP_UNSUPPORTED_NAVIGATION' );
	}

	/**
	 * Apply a staged full post-state proposal.
	 *
	 * @param array<string, mixed> $change Change row.
	 * @return array<string, mixed>|\WP_Error
	 */
	private function apply_content( $change ) {
		$after = $this->as_array( $change['after_json'] );
		$post  = get_post( (int) $change['object_id'] );
		if ( ! is_object( $post ) ) {
			return ErrorFactory::make( 'AP_CONTENT_NOT_FOUND' );
		}
		$fields = array( 'ID' => (int) $post->ID );
		foreach ( array( 'post_title', 'post_content', 'post_excerpt', 'post_name', 'post_parent' ) as $field ) {
			if ( array_key_exists( $field, $after ) ) {
				$fields[ $field ] = $after[ $field ];
			}
		}
		$updated = wp_update_post( $fields, true );
		if ( is_wp_error( $updated ) || (int) $updated !== (int) $post->ID ) {
			return ErrorFactory::make( 'AP_INTERNAL_ERROR' );
		}
		return array( 'object_id' => (int) $post->ID );
	}

	/**
	 * Transition one row and return whether it changed.
	 *
	 * @param int                  $id     Change ID.
	 * @param string               $status Expected status.
	 * @param array<string, mixed> $changes Transition fields.
	 * @return bool
	 */
	private function transition( $id, $status, $changes ) {
		try {
			return (bool) $this->changes->transition( $id, $status, $changes );
		} catch ( \Throwable $throwable ) {
			return false;
		}
	}

	/**
	 * Mark an APPLYING claim failed or conflicted, but keep the durable row visible.
	 *
	 * @param int    $change_id Change ID.
	 * @param string $status    Destination status.
	 * @return void
	 */
	private function fail_claim( $change_id, $status ) {
		$this->transition( $change_id, 'APPLYING', array( 'status' => $status ) );
	}

	/**
	 * Derive and persist the parent Change Set status from its children.
	 *
	 * @param int $set_id Set ID.
	 * @return void
	 */
	private function sync_set( $set_id ) {
		$state = $this->reducer->reduce( $this->changes->statuses_for_set( $set_id ) );
		$this->sets->update(
			$set_id,
			array(
				'status'       => $state,
				'completed_at' => in_array( $state, array( 'COMPLETED', 'REJECTED', 'FAILED' ), true ) ? current_time( 'mysql', true ) : null,
			)
		);
	}

	/**
	 * Emit one sanitized human approval audit event.
	 *
	 * @param array<string, mixed> $change Change row.
	 * @param string               $result Result.
	 * @param string               $reason Reason text.
	 * @return void
	 */
	private function audit_event( $change, $result, $reason ) {
		try {
			$this->audit->record(
				array(
					'request_id'    => strtolower( wp_generate_uuid4() ),
					'actor_type'    => 'human',
					'user_id'       => (int) get_current_user_id(),
					'change_set_id' => (int) $change['change_set_id'],
					'change_id'     => (int) $change['id'],
					'ability'       => (string) $change['ability'],
					'object_type'   => (string) $change['object_type'],
					'object_id'     => (int) $change['object_id'],
					'result'        => $result,
					'error_code'    => '',
					'arguments'     => array( 'reason' => $reason ),
					'duration_ms'   => 0,
				)
			);
		} catch ( \Throwable $throwable ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
			// A human audit event must never block the approve/reject outcome.
		}
	}

	/**
	 * Parse the navigation location from a navigation staging change.
	 *
	 * @param array<string, mixed> $change Change row.
	 * @return string
	 */
	private function location( $change ) {
		$object_type = isset( $change['object_type'] ) ? (string) $change['object_type'] : '';
		if ( 0 === strpos( $object_type, 'nav_menu_item:' ) ) {
			return substr( $object_type, strlen( 'nav_menu_item:' ) );
		}
		return 'primary';
	}

	/**
	 * Coerce a stored JSON field into an array.
	 *
	 * @param mixed $value Stored value.
	 * @return array<string, mixed>
	 */
	private function as_array( $value ) {
		return is_array( $value ) ? $value : array();
	}

	/**
	 * Project the complete bounded post state used for target hashing.
	 *
	 * @param object $post Post.
	 * @return array<string, mixed>
	 */
	private function post_state( $post ) {
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
