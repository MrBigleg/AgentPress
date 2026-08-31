<?php
/**
 * Fixed Change Set parent-state reducer.
 *
 * @package AgentPress
 */

namespace AgentPress\Changes;

/**
 * Derives one parent status from its child change statuses.
 */
final class ChangeSetStateReducer {
	/** Known child states. */
	private const CHILD_STATES = array( 'RECORDED', 'APPLYING', 'APPLIED', 'PENDING_APPROVAL', 'REJECTED', 'CONFLICT', 'EXPIRED', 'FAILED' );

	/**
	 * Reduce child states deterministically.
	 *
	 * @param array<int, string> $statuses Child statuses.
	 * @return string
	 * @throws \InvalidArgumentException When a child status is unknown.
	 */
	public function reduce( $statuses ) {
		if ( empty( $statuses ) ) {
			return 'OPEN';
		}
		foreach ( $statuses as $status ) {
			if ( ! in_array( $status, self::CHILD_STATES, true ) ) {
				throw new \InvalidArgumentException( 'AgentPress change status is unknown.' );
			}
		}

		$has_pending  = in_array( 'PENDING_APPROVAL', $statuses, true );
		$has_applied  = in_array( 'APPLIED', $statuses, true );
		$has_rejected = ! empty( array_intersect( $statuses, array( 'REJECTED', 'EXPIRED' ) ) );
		$has_failed   = ! empty( array_intersect( $statuses, array( 'FAILED', 'CONFLICT' ) ) );

		if ( $has_pending ) {
			return ( $has_applied || $has_rejected ) ? 'PARTIALLY_APPROVED' : 'READY_FOR_REVIEW';
		}
		if ( count( array_unique( $statuses ) ) === 1 && 'APPLIED' === $statuses[0] ) {
			return 'COMPLETED';
		}
		if ( $has_failed ) {
			return 'FAILED';
		}
		if ( $has_applied && $has_rejected ) {
			return 'PARTIALLY_APPROVED';
		}
		if ( ! $has_applied && count( array_diff( $statuses, array( 'REJECTED', 'EXPIRED' ) ) ) === 0 ) {
			return 'REJECTED';
		}
		return 'WORKING';
	}
}
