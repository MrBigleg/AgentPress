<?php
/**
 * AP-010 canonical hashing and state reduction controls.
 *
 * @package AgentPress
 */

use AgentPress\Changes\ChangeSetStateReducer;
use AgentPress\Changes\StateHasher;
use PHPUnit\Framework\TestCase;

/**
 * Verifies deterministic pure Change Set primitives.
 */
final class ChangeCoordinationTest extends TestCase {
	/**
	 * Associative key order does not change canonical state identity.
	 */
	public function test_state_hash_is_object_order_invariant() {
		$hasher = new StateHasher();
		$first  = array( 'title' => 'Page', 'nested' => array( 'b' => 2, 'a' => 1 ) );
		$second = array( 'nested' => array( 'a' => 1, 'b' => 2 ), 'title' => 'Page' );

		$this->assertSame( $hasher->state_hash( $first ), $hasher->state_hash( $second ) );
		$this->assertNotSame( $hasher->state_hash( array( 1, 2 ) ), $hasher->state_hash( array( 2, 1 ) ) );
	}

	/**
	 * Proposal and idempotency hashes change only on material contract changes.
	 */
	public function test_proposal_and_idempotency_hashes_are_sensitive() {
		$hasher      = new StateHasher();
		$target_hash = $hasher->state_hash( array( 'title' => 'Before' ) );
		$proposal    = $hasher->proposal_hash( 'agentpress/update-content', 'update', array( 'title' => 'After' ), $target_hash );

		$this->assertSame( 64, strlen( $proposal ) );
		$this->assertNotSame( $proposal, $hasher->proposal_hash( 'agentpress/update-content', 'update', array( 'title' => 'Changed' ), $target_hash ) );
		$this->assertNotSame( $proposal, $hasher->proposal_hash( 'agentpress/update-content', 'update', array( 'title' => 'After' ), str_repeat( '0', 64 ) ) );
		$this->assertNotSame(
			$hasher->idempotency_scope( 1, 'agentpress/create-draft', 'same-key' ),
			$hasher->idempotency_scope( 2, 'agentpress/create-draft', 'same-key' )
		);
	}

	/**
	 * Every documented and explicitly resolved parent-state class is stable.
	 *
	 * @dataProvider state_provider
	 * @param array<int, string> $children Child states.
	 * @param string             $expected Parent state.
	 */
	public function test_change_set_state_reducer( $children, $expected ) {
		$this->assertSame( $expected, ( new ChangeSetStateReducer() )->reduce( $children ) );
	}

	/**
	 * Return fixed child-to-parent fixtures.
	 *
	 * @return array<string, array{0:array<int, string>,1:string}>
	 */
	public function state_provider() {
		return array(
			'empty'                  => array( array(), 'OPEN' ),
			'recorded'               => array( array( 'RECORDED' ), 'WORKING' ),
			'applying'               => array( array( 'APPLYING' ), 'WORKING' ),
			'pending'                => array( array( 'PENDING_APPROVAL' ), 'READY_FOR_REVIEW' ),
			'pending and applied'    => array( array( 'APPLIED', 'PENDING_APPROVAL' ), 'PARTIALLY_APPROVED' ),
			'pending and rejected'   => array( array( 'REJECTED', 'PENDING_APPROVAL' ), 'PARTIALLY_APPROVED' ),
			'all applied'            => array( array( 'APPLIED', 'APPLIED' ), 'COMPLETED' ),
			'all rejected or expired' => array( array( 'REJECTED', 'EXPIRED' ), 'REJECTED' ),
			'failed'                 => array( array( 'APPLIED', 'FAILED' ), 'FAILED' ),
			'conflict'               => array( array( 'CONFLICT' ), 'FAILED' ),
			'mixed terminal'         => array( array( 'APPLIED', 'REJECTED' ), 'PARTIALLY_APPROVED' ),
		);
	}

	/**
	 * Unknown child states never silently map to a parent.
	 */
	public function test_unknown_child_state_is_rejected() {
		$this->expectException( InvalidArgumentException::class );
		( new ChangeSetStateReducer() )->reduce( array( 'UNKNOWN' ) );
	}
}
