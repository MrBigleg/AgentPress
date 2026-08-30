<?php
/**
 * AP-005 database migration, repository, and lifecycle acceptance harness.
 *
 * @package AgentPress
 */

use AgentPress\Activation;
use AgentPress\Audit\AuditEventRepository;
use AgentPress\Changes\ChangeRepository;
use AgentPress\Changes\ChangeSetRepository;
use AgentPress\Storage\JsonCodec;
use AgentPress\Storage\Migrator;

if ( ! defined( 'ABSPATH' ) ) {
	throw new RuntimeException( 'WordPress must be loaded.' );
}

/** @param bool $condition Condition. @param string $message Failure. @return void */
function agentpress_ap005_assert( $condition, $message ) {
	if ( ! $condition ) {
		throw new RuntimeException( $message );
	}
}

/** @param wpdb $wpdb Database. @param string $table Table. @return array<int, string> */
function agentpress_ap005_columns( $wpdb, $table ) {
	$query = $wpdb->prepare( 'SHOW COLUMNS FROM %i', $table );
	return $wpdb->get_col( $query, 0 ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
}

/** @param wpdb $wpdb Database. @param string $table Table. @return array<string, array<int, string>> */
function agentpress_ap005_indexes( $wpdb, $table ) {
	$query  = $wpdb->prepare( 'SHOW INDEX FROM %i', $table );
	$rows   = $wpdb->get_results( $query, ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	$result = array();
	foreach ( $rows as $row ) {
		$result[ $row['Key_name'] ][ (int) $row['Seq_in_index'] ] = $row['Column_name'];
	}
	foreach ( $result as &$columns ) {
		ksort( $columns );
		$columns = array_values( $columns );
	}
	return $result;
}

/** @param wpdb $wpdb Database. @param string $table Table. @return string */
function agentpress_ap005_create_sql( $wpdb, $table ) {
	$query = $wpdb->prepare( 'SHOW CREATE TABLE %i', $table );
	$row   = $wpdb->get_row( $query, ARRAY_N ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	return is_array( $row ) ? (string) $row[1] : '';
}

global $wpdb;

Activation::activate();
$tables = Migrator::table_names();

$expected_columns = array(
	'change_sets' => array( 'id', 'initiator_user_id', 'title', 'request_summary', 'source', 'source_session_hash', 'status', 'created_at', 'updated_at', 'completed_at' ),
	'changes' => array( 'id', 'change_set_id', 'actor_user_id', 'ability', 'risk_class', 'operation', 'object_type', 'object_id', 'before_json', 'after_json', 'target_state_hash', 'proposal_hash', 'idempotency_hash', 'idempotency_scope', 'status', 'error_code', 'created_at', 'expires_at', 'approved_by', 'approved_at', 'rejected_by', 'rejected_at', 'applied_at' ),
	'audit_events' => array( 'id', 'request_id', 'actor_type', 'user_id', 'change_set_id', 'change_id', 'ability', 'object_type', 'object_id', 'result', 'error_code', 'arguments_sanitized', 'duration_ms', 'created_at' ),
);
$expected_indexes = array(
	'change_sets' => array(
		'PRIMARY'          => array( 'id' ),
		'initiator_status' => array( 'initiator_user_id', 'status' ),
		'status_updated'   => array( 'status', 'updated_at' ),
	),
	'changes' => array(
		'PRIMARY'           => array( 'id' ),
		'idempotency_scope' => array( 'idempotency_scope' ),
		'set_status'        => array( 'change_set_id', 'status' ),
		'object_lookup'     => array( 'object_type', 'object_id' ),
		'expires_status'    => array( 'status', 'expires_at' ),
	),
	'audit_events' => array(
		'PRIMARY'        => array( 'id' ),
		'request_id'     => array( 'request_id' ),
		'user_created'   => array( 'user_id', 'created_at' ),
		'set_created'    => array( 'change_set_id', 'created_at' ),
		'result_created' => array( 'result', 'created_at' ),
	),
);

$schema_before = array();
foreach ( $tables as $key => $table ) {
	agentpress_ap005_assert( $expected_columns[ $key ] === agentpress_ap005_columns( $wpdb, $table ), 'Column mismatch for ' . $key . '.' );
	agentpress_ap005_assert( $expected_indexes[ $key ] === agentpress_ap005_indexes( $wpdb, $table ), 'Index mismatch for ' . $key . '.' );
	$schema_before[ $key ] = agentpress_ap005_create_sql( $wpdb, $table );
	agentpress_ap005_assert( false === stripos( $schema_before[ $key ], 'CONSTRAINT' ), 'Foreign key detected for ' . $key . '.' );
}

Migrator::migrate();
foreach ( $tables as $key => $table ) {
	agentpress_ap005_assert( $schema_before[ $key ] === agentpress_ap005_create_sql( $wpdb, $table ), 'Repeated migration changed ' . $key . '.' );
}
agentpress_ap005_assert( Migrator::DB_VERSION === get_option( Migrator::VERSION_OPTION ), 'Database version option mismatch.' );
delete_option( Migrator::VERSION_OPTION );
Migrator::maybe_migrate();
agentpress_ap005_assert( Migrator::DB_VERSION === get_option( Migrator::VERSION_OPTION ), 'Guarded upgrade did not restore the version.' );
foreach ( $tables as $key => $table ) {
	agentpress_ap005_assert( $schema_before[ $key ] === agentpress_ap005_create_sql( $wpdb, $table ), 'Guarded upgrade changed ' . $key . '.' );
}

$clock      = static function () { return '2026-08-30 22:30:00'; };
$set_repo   = new ChangeSetRepository( $wpdb, $clock );
$change_repo = new ChangeRepository( $wpdb, $clock );
$audit_repo = new AuditEventRepository( $wpdb, $clock );

$set_id = $set_repo->create(
	array(
		'initiator_user_id' => 1,
		'title'             => "Synthetic ', status='FAILED",
		'request_summary'   => 'Bounded fixture',
		'status'            => 'OPEN',
	)
);
$set = $set_repo->find( $set_id );
agentpress_ap005_assert( "Synthetic ', status='FAILED" === $set['title'] && 'OPEN' === $set['status'], 'Prepared Change Set insert failed.' );
agentpress_ap005_assert( '2026-08-30 22:30:00' === $set['created_at'], 'Change Set timestamp is not UTC clock output.' );
agentpress_ap005_assert( $set_repo->update( $set_id, array( 'status' => 'WORKING' ) ), 'Change Set update failed.' );
agentpress_ap005_assert( 'WORKING' === $set_repo->find( $set_id )['status'], 'Change Set update did not round-trip.' );

$change_id = $change_repo->create(
	array(
		'change_set_id'    => $set_id,
		'actor_user_id'    => 1,
		'ability'          => 'agentpress/update-content',
		'risk_class'       => 'R1',
		'operation'        => 'update',
		'before_json'      => array( 'title' => 'Before', 'nested' => array( 'safe' => true ) ),
		'after_json'       => array( 'title' => 'หลัง', 'count' => 2 ),
		'idempotency_hash' => str_repeat( 'a', 64 ),
		'idempotency_scope' => str_repeat( 'b', 64 ),
		'status'           => 'RECORDED',
	)
);
$change = $change_repo->find( $change_id );
agentpress_ap005_assert( 'หลัง' === $change['after_json']['title'] && true === $change['before_json']['nested']['safe'], 'Change JSON did not round-trip.' );
agentpress_ap005_assert( $change_repo->update( $change_id, array( 'status' => 'APPLIED', 'object_id' => 42 ) ), 'Change update failed.' );
agentpress_ap005_assert( 42 === $change_repo->find( $change_id )['object_id'], 'Change integer did not round-trip.' );

$audit_id = $audit_repo->create(
	array(
		'request_id'          => '00000000-0000-4000-8000-000000000005',
		'actor_type'         => 'agent',
		'user_id'            => 1,
		'change_set_id'      => $set_id,
		'change_id'          => $change_id,
		'ability'            => 'agentpress/update-content',
		'result'             => 'success',
		'arguments_sanitized' => array( 'content' => array( 'bytes' => 12, 'sha256' => str_repeat( 'c', 64 ) ) ),
		'duration_ms'        => 17,
	)
);
agentpress_ap005_assert( 17 === $audit_repo->find( $audit_id )['duration_ms'], 'Audit event did not round-trip.' );
agentpress_ap005_assert( $audit_repo->update( $audit_id, array( 'duration_ms' => 18 ) ), 'Audit update failed.' );

$bounded = false;
try {
	(new JsonCodec())->encode( array( 'payload' => str_repeat( 'x', JsonCodec::MAX_BYTES ) ) );
} catch ( LengthException $exception ) {
	$bounded = true;
}
agentpress_ap005_assert( $bounded, 'Oversized JSON was accepted.' );

$raw_key_rejected = false;
try {
	$change_repo->create(
		array(
			'change_set_id'     => $set_id,
			'actor_user_id'     => 1,
			'ability'           => 'agentpress/update-content',
			'risk_class'        => 'R1',
			'operation'         => 'update',
			'before_json'       => array(),
			'after_json'        => array(),
			'idempotency_hash'  => str_repeat( 'd', 64 ),
			'idempotency_scope' => str_repeat( 'e', 64 ),
			'idempotency_key'   => 'raw-key-must-not-persist',
			'status'            => 'RECORDED',
		)
	);
} catch ( InvalidArgumentException $exception ) {
	$raw_key_rejected = true;
}
agentpress_ap005_assert( $raw_key_rejected, 'Raw idempotency key was accepted.' );

Activation::deactivate();
agentpress_ap005_assert( null !== $set_repo->find( $set_id ), 'Deactivation removed durable data.' );

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	define( 'WP_UNINSTALL_PLUGIN', 'agentpress/agentpress.php' );
}
require AGENTPRESS_PATH . 'uninstall.php';
agentpress_ap005_assert( null !== $set_repo->find( $set_id ), 'Default uninstall removed durable data.' );
agentpress_ap005_assert( Migrator::DB_VERSION === get_option( Migrator::VERSION_OPTION ), 'Default uninstall removed the database version.' );

$sentinel_table = $wpdb->prefix . 'agentpress_not_owned';
$sentinel_query = $wpdb->prepare( 'CREATE TABLE %i (id bigint unsigned NOT NULL)', $sentinel_table );
$wpdb->query( $sentinel_query ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
define( 'AGENTPRESS_REMOVE_DATA_ON_UNINSTALL', true );
require AGENTPRESS_PATH . 'uninstall.php';
foreach ( $tables as $table ) {
	$query = $wpdb->prepare( 'SHOW TABLES LIKE %s', $table );
	agentpress_ap005_assert( null === $wpdb->get_var( $query ), 'Explicit uninstall preserved ' . $table . '.' ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
}
agentpress_ap005_assert( false === get_option( 'agentpress_db_version' ), 'Explicit uninstall preserved database version.' );
agentpress_ap005_assert( false === get_option( 'agentpress_version' ), 'Explicit uninstall preserved plugin version.' );
$sentinel_lookup = $wpdb->prepare( 'SHOW TABLES LIKE %s', $sentinel_table );
agentpress_ap005_assert( $sentinel_table === $wpdb->get_var( $sentinel_lookup ), 'Explicit uninstall deleted an unowned table.' ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
$sentinel_drop = $wpdb->prepare( 'DROP TABLE %i', $sentinel_table );
$wpdb->query( $sentinel_drop ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

Migrator::migrate();
update_option( 'agentpress_version', AGENTPRESS_VERSION, false );

WP_CLI::success(
	wp_json_encode(
		array(
			'tables'                 => count( $tables ),
			'idempotent_schema'      => true,
			'repository_round_trips' => 3,
			'json_bound'             => JsonCodec::MAX_BYTES,
			'preserve_default'       => true,
			'explicit_cleanup'       => true,
		)
	)
);
