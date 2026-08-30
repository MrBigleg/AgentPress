<?php
/**
 * AgentPress uninstall policy.
 *
 * Data is preserved unless the site owner explicitly opts in to removal.
 *
 * @package AgentPress
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

if ( ! defined( 'AGENTPRESS_REMOVE_DATA_ON_UNINSTALL' ) || true !== AGENTPRESS_REMOVE_DATA_ON_UNINSTALL ) {
	return;
}

global $wpdb;

$agentpress_tables = array(
	$wpdb->prefix . 'agentpress_change_sets',
	$wpdb->prefix . 'agentpress_changes',
	$wpdb->prefix . 'agentpress_audit_events',
);

foreach ( $agentpress_tables as $agentpress_table ) {
	$agentpress_drop = $wpdb->prepare( 'DROP TABLE IF EXISTS %i', $agentpress_table );
	$wpdb->query( $agentpress_drop ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Explicit uninstall DDL cannot use cache-backed CRUD helpers.
}

delete_option( 'agentpress_db_version' );
delete_option( 'agentpress_version' );
