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
		$this->store = new RecordStore(
			'agentpress_changes',
			array( 'change_set_id', 'actor_user_id', 'ability', 'risk_class', 'operation', 'object_type', 'object_id', 'before_json', 'after_json', 'target_state_hash', 'proposal_hash', 'idempotency_hash', 'idempotency_scope', 'status', 'error_code', 'created_at', 'expires_at', 'approved_by', 'approved_at', 'rejected_by', 'rejected_at', 'applied_at' ),
			array( 'change_set_id', 'actor_user_id', 'object_id', 'approved_by', 'rejected_by' ),
			array( 'before_json', 'after_json' ),
			$wpdb
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
	 */
	public function update( $id, $changes ) {
		return $this->store->update( $id, $changes );
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
