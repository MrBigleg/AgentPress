<?php
/**
 * Audit event persistence.
 *
 * @package AgentPress
 */

namespace AgentPress\Audit;

use AgentPress\Storage\RecordStore;

/**
 * Provides bounded CRUD for sanitized audit events.
 */
final class AuditEventRepository {
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
			'agentpress_audit_events',
			array( 'request_id', 'actor_type', 'user_id', 'change_set_id', 'change_id', 'ability', 'object_type', 'object_id', 'result', 'error_code', 'arguments_sanitized', 'duration_ms', 'created_at' ),
			array( 'user_id', 'change_set_id', 'change_id', 'object_id', 'duration_ms' ),
			array( 'arguments_sanitized' ),
			$wpdb
		);
		$this->clock = $clock ?? static function () {
			return current_time( 'mysql', true );
		};
	}

	/**
	 * Create one sanitized audit event.
	 *
	 * @param array<string, mixed> $record Audit values.
	 * @return int
	 */
	public function create( $record ) {
		$this->require_fields( $record, array( 'request_id', 'actor_type', 'user_id', 'ability', 'result', 'arguments_sanitized' ) );

		return $this->store->create(
			array_merge(
				array(
					'change_set_id' => 0,
					'change_id'     => 0,
					'object_type'   => '',
					'object_id'     => 0,
					'error_code'    => '',
					'duration_ms'   => 0,
					'created_at'    => call_user_func( $this->clock ),
				),
				$record
			)
		);
	}

	/**
	 * Find one audit event.
	 *
	 * @param int $id Record ID.
	 * @return array<string, mixed>|null
	 */
	public function find( $id ) {
		return $this->store->find( $id );
	}

	/**
	 * Update one audit event through the fixed allowlist.
	 *
	 * @param int                  $id      Record ID.
	 * @param array<string, mixed> $changes Changed fields.
	 * @return bool
	 */
	public function update( $id, $changes ) {
		return $this->store->update( $id, $changes );
	}

	/**
	 * Delete one audit event.
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
				throw new \InvalidArgumentException( 'AgentPress audit event is missing a required field.' );
			}
		}
	}
}
