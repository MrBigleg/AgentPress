<?php
/**
 * Verify and remove the AP-005 row while AgentPress is inactive.
 *
 * @package AgentPress
 */

global $wpdb;

$id    = (int) get_option( 'agentpress_ap005_deactivation_row' );
$table = $wpdb->prefix . 'agentpress_change_sets';
$query = $wpdb->prepare( 'SELECT COUNT(*) FROM %i WHERE id = %d', $table, $id );
$count = (int) $wpdb->get_var( $query ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

if ( 1 !== $count ) {
	throw new RuntimeException( 'AgentPress deactivation did not preserve the sentinel row.' );
}

$wpdb->delete( $table, array( 'id' => $id ), array( '%d' ) );
delete_option( 'agentpress_ap005_deactivation_row' );
WP_CLI::success( 'Deactivation preserved the sentinel row.' );
