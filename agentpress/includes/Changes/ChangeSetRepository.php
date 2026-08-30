<?php
/**
 * Change Set persistence.
 *
 * @package AgentPress
 */

namespace AgentPress\Changes;

use AgentPress\Storage\RecordStore;

/**
 * Provides bounded CRUD for Change Set records only.
 */
final class ChangeSetRepository {
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
			'agentpress_change_sets',
			array( 'initiator_user_id', 'title', 'request_summary', 'source', 'source_session_hash', 'status', 'created_at', 'updated_at', 'completed_at' ),
			array( 'initiator_user_id' ),
			array(),
			$wpdb
		);
		$this->clock = $clock ?? static function () {
			return current_time( 'mysql', true );
		};
	}

	/**
	 * Create one Change Set.
	 *
	 * @param array<string, mixed> $record Change Set values.
	 * @return int
	 */
	public function create( $record ) {
		$this->require_fields( $record, array( 'initiator_user_id', 'title', 'request_summary', 'status' ) );
		$now = call_user_func( $this->clock );

		return $this->store->create(
			array_merge(
				array(
					'source'              => 'webmcp',
					'source_session_hash' => '',
					'created_at'          => $now,
					'updated_at'          => $now,
					'completed_at'        => null,
				),
				$record
			)
		);
	}

	/**
	 * Find one Change Set.
	 *
	 * @param int $id Record ID.
	 * @return array<string, mixed>|null
	 */
	public function find( $id ) {
		return $this->store->find( $id );
	}

	/**
	 * Update allowlisted Change Set fields.
	 *
	 * @param int                  $id      Record ID.
	 * @param array<string, mixed> $changes Changed fields.
	 * @return bool
	 */
	public function update( $id, $changes ) {
		if ( ! array_key_exists( 'updated_at', $changes ) ) {
			$changes['updated_at'] = call_user_func( $this->clock );
		}

		return $this->store->update( $id, $changes );
	}

	/**
	 * Delete one Change Set.
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
				throw new \InvalidArgumentException( 'AgentPress Change Set is missing a required field.' );
			}
		}
	}
}
