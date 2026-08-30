<?php
/**
 * Narrow prepared record persistence.
 *
 * @package AgentPress
 */

namespace AgentPress\Storage;

/**
 * Shared low-level CRUD used only by the three domain repositories.
 */
final class RecordStore {
	/**
	 * Database adapter.
	 *
	 * @var \wpdb
	 */
	private $wpdb;

	/**
	 * Exact prefixed table name.
	 *
	 * @var string
	 */
	private $table;

	/**
	 * Writable column allowlist.
	 *
	 * @var array<int, string>
	 */
	private $columns;

	/**
	 * Integer columns.
	 *
	 * @var array<int, string>
	 */
	private $integer_columns;

	/**
	 * JSON columns.
	 *
	 * @var array<int, string>
	 */
	private $json_columns;

	/**
	 * Bounded JSON codec.
	 *
	 * @var JsonCodec
	 */
	private $json;

	/**
	 * Constructor.
	 *
	 * @param string             $table_suffix    Fixed AgentPress table suffix.
	 * @param array<int, string> $columns         Writable columns.
	 * @param array<int, string> $integer_columns Integer columns.
	 * @param array<int, string> $json_columns    JSON columns.
	 * @param \wpdb|null         $wpdb            Optional database adapter.
	 * @param JsonCodec|null     $json            Optional JSON codec.
	 */
	public function __construct( $table_suffix, $columns, $integer_columns, $json_columns, $wpdb = null, $json = null ) {
		$this->wpdb            = $wpdb ?? $GLOBALS['wpdb'];
		$this->table           = $this->wpdb->prefix . $table_suffix;
		$this->columns         = $columns;
		$this->integer_columns = $integer_columns;
		$this->json_columns    = $json_columns;
		$this->json            = $json ?? new JsonCodec();
	}

	/**
	 * Insert one record.
	 *
	 * @param array<string, mixed> $record Record.
	 * @return int
	 * @throws \RuntimeException When the insert fails.
	 */
	public function create( $record ) {
		$prepared = $this->prepare_record( $record );
		$result   = $this->wpdb->insert( $this->table, $prepared, $this->formats( $prepared ) );

		if ( false === $result ) {
			throw new \RuntimeException( 'AgentPress could not insert the database record.' );
		}

		return (int) $this->wpdb->insert_id;
	}

	/**
	 * Find one record by ID.
	 *
	 * @param int $id Record ID.
	 * @return array<string, mixed>|null
	 */
	public function find( $id ) {
		$query = $this->wpdb->prepare( 'SELECT * FROM %i WHERE id = %d', $this->table, $id );
		$row   = $this->wpdb->get_row( $query, ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		if ( ! is_array( $row ) ) {
			return null;
		}

		foreach ( $this->json_columns as $column ) {
			$row[ $column ] = $this->json->decode( (string) $row[ $column ] );
		}

		foreach ( $this->integer_columns as $column ) {
			if ( array_key_exists( $column, $row ) ) {
				$row[ $column ] = (int) $row[ $column ];
			}
		}

		return $row;
	}

	/**
	 * Update an existing record through the fixed column allowlist.
	 *
	 * @param int                  $id      Record ID.
	 * @param array<string, mixed> $changes Changed columns.
	 * @return bool
	 * @throws \InvalidArgumentException When changes are empty or unknown.
	 * @throws \RuntimeException When the update fails.
	 */
	public function update( $id, $changes ) {
		if ( empty( $changes ) ) {
			throw new \InvalidArgumentException( 'AgentPress record changes cannot be empty.' );
		}

		$prepared = $this->prepare_record( $changes );
		$result   = $this->wpdb->update(
			$this->table,
			$prepared,
			array( 'id' => $id ),
			$this->formats( $prepared ),
			array( '%d' )
		);

		if ( false === $result ) {
			throw new \RuntimeException( 'AgentPress could not update the database record.' );
		}

		return $result > 0;
	}

	/**
	 * Delete one record by ID.
	 *
	 * @param int $id Record ID.
	 * @return bool
	 * @throws \RuntimeException When the delete fails.
	 */
	public function delete( $id ) {
		$result = $this->wpdb->delete( $this->table, array( 'id' => $id ), array( '%d' ) );
		if ( false === $result ) {
			throw new \RuntimeException( 'AgentPress could not delete the database record.' );
		}

		return $result > 0;
	}

	/**
	 * Validate, normalize, and encode writable fields.
	 *
	 * @param array<string, mixed> $record Record.
	 * @return array<string, mixed>
	 * @throws \InvalidArgumentException When a column or JSON value is invalid.
	 */
	private function prepare_record( $record ) {
		$unknown = array_diff( array_keys( $record ), $this->columns );
		if ( ! empty( $unknown ) ) {
			throw new \InvalidArgumentException( 'AgentPress record contains an unknown column.' );
		}

		foreach ( $record as $column => $value ) {
			if ( in_array( $column, $this->json_columns, true ) ) {
				if ( ! is_array( $value ) ) {
					throw new \InvalidArgumentException( 'AgentPress JSON fields must be arrays.' );
				}
				$record[ $column ] = $this->json->encode( $value );
			} elseif ( in_array( $column, $this->integer_columns, true ) ) {
				$record[ $column ] = (int) $value;
			} elseif ( null !== $value ) {
				$record[ $column ] = (string) $value;
			}
		}

		return $record;
	}

	/**
	 * Return wpdb formats in record order.
	 *
	 * @param array<string, mixed> $record Record.
	 * @return array<int, string>
	 */
	private function formats( $record ) {
		return array_map(
			function ( $column ) {
				return in_array( $column, $this->integer_columns, true ) ? '%d' : '%s';
			},
			array_keys( $record )
		);
	}
}
