<?php
/**
 * Change record persistence.
 *
 * @package AgentPress
 */

namespace AgentPress\Changes;

use AgentPress\Storage\RecordStore;

/**
 * Provides bounded CRUD for individual change records.
 */
final class ChangeRepository {
	/**
	 * Database adapter.
	 *
	 * @var \wpdb
	 */
	private $wpdb;

	/**
	 * Low-level record store.
	 *
	 * @var RecordStore
	 */
	private $store;

	/**
	 * UTC MySQL clock.
	 *
	 * @var callable
	 */
	private $clock;

	/**
	 * Constructor.
	 *
	 * @param \wpdb|null    $wpdb  Optional database adapter.
	 * @param callable|null $clock Optional UTC MySQL clock.
	 */
	public function __construct( $wpdb = null, $clock = null ) {
		$this->wpdb  = $wpdb ?? $GLOBALS['wpdb'];
		$this->store = new RecordStore(
			'agentpress_changes',
			array( 'change_set_id', 'actor_user_id', 'ability', 'risk_class', 'operation', 'object_type', 'object_id', 'before_json', 'after_json', 'target_state_hash', 'proposal_hash', 'idempotency_hash', 'idempotency_scope', 'status', 'error_code', 'created_at', 'expires_at', 'approved_by', 'approved_at', 'rejected_by', 'rejected_at', 'applied_at' ),
			array( 'change_set_id', 'actor_user_id', 'object_id', 'approved_by', 'rejected_by' ),
			array( 'before_json', 'after_json' ),
			$this->wpdb
		);
		$this->clock = $clock ?? static function () {
			return current_time( 'mysql', true );
		};
	}

	/**
	 * Create one change record.
	 *
	 * @param array<string, mixed> $record Change values.
	 * @return int
	 */
	public function create( $record ) {
		$this->require_fields( $record, array( 'change_set_id', 'actor_user_id', 'ability', 'risk_class', 'operation', 'before_json', 'after_json', 'idempotency_hash', 'idempotency_scope', 'status' ) );

		return $this->store->create(
			array_merge(
				array(
					'object_type'       => '',
					'object_id'         => 0,
					'target_state_hash' => '',
					'proposal_hash'     => '',
					'error_code'        => '',
					'created_at'        => call_user_func( $this->clock ),
					'expires_at'        => null,
					'approved_by'       => 0,
					'approved_at'       => null,
					'rejected_by'       => 0,
					'rejected_at'       => null,
					'applied_at'        => null,
				),
				$record
			)
		);
	}

	/**
	 * Find one change.
	 *
	 * @param int $id Record ID.
	 * @return array<string, mixed>|null
	 */
	public function find( $id ) {
		return $this->store->find( $id );
	}

	/**
	 * Update one change through the fixed allowlist.
	 *
	 * @param int                  $id      Record ID.
	 * @param array<string, mixed> $changes Changed fields.
	 * @return bool
	 * @throws \InvalidArgumentException When immutable proposal fields are changed.
	 */
	public function update( $id, $changes ) {
		$current = $this->find( $id );
		if ( is_array( $current ) && 'PENDING_APPROVAL' === $current['status'] ) {
			$immutable = array( 'change_set_id', 'actor_user_id', 'ability', 'risk_class', 'operation', 'before_json', 'after_json', 'target_state_hash', 'proposal_hash', 'idempotency_hash', 'idempotency_scope', 'created_at', 'expires_at' );
			if ( array_intersect( array_keys( $changes ), $immutable ) ) {
				throw new \InvalidArgumentException( 'AgentPress pending proposal fields are immutable.' );
			}
		}

		return $this->store->update( $id, $changes );
	}

	/**
	 * Atomically transition one exact current status.
	 *
	 * @param int                  $id              Change ID.
	 * @param string               $expected_status Required current status.
	 * @param array<string, mixed> $changes         Transition fields including status.
	 * @return bool
	 * @throws \InvalidArgumentException When no destination status is supplied.
	 */
	public function transition( $id, $expected_status, $changes ) {
		if ( ! array_key_exists( 'status', $changes ) ) {
			throw new \InvalidArgumentException( 'AgentPress change transition requires a status.' );
		}
		return $this->store->update_if( $id, 'status', $expected_status, $changes );
	}

	/**
	 * Find one change by its unique idempotency scope.
	 *
	 * @param string $scope SHA-256 scope.
	 * @return array<string, mixed>|null
	 */
	public function find_by_idempotency_scope( $scope ) {
		$query = $this->wpdb->prepare(
			'SELECT id FROM %i WHERE idempotency_scope = %s LIMIT 1',
			$this->wpdb->prefix . 'agentpress_changes',
			$scope
		);
		$id    = $this->wpdb->get_var( $query ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		return null === $id ? null : $this->find( (int) $id );
	}

	/**
	 * Return child statuses for one Change Set in stable row order.
	 *
	 * @param int $change_set_id Change Set ID.
	 * @return array<int, string>
	 */
	public function statuses_for_set( $change_set_id ) {
		$query = $this->wpdb->prepare(
			'SELECT status FROM %i WHERE change_set_id = %d ORDER BY id ASC',
			$this->wpdb->prefix . 'agentpress_changes',
			$change_set_id
		);
		return array_map( 'strval', $this->wpdb->get_col( $query ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	}

	/**
	 * Delete one change.
	 *
	 * @param int $id Record ID.
	 * @return bool
	 */
	public function delete( $id ) {
		return $this->store->delete( $id );
	}

	/**
	 * Require persistence inputs.
	 *
	 * @param array<string, mixed> $record Record.
	 * @param array<int, string>   $fields Required fields.
	 * @return void
	 * @throws \InvalidArgumentException When a required field is absent.
	 */
	private function require_fields( $record, $fields ) {
		foreach ( $fields as $field ) {
			if ( ! array_key_exists( $field, $record ) ) {
				throw new \InvalidArgumentException( 'AgentPress change is missing a required field.' );
			}
		}
	}
}
