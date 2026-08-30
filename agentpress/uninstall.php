<?php
/**
 * AgentPress uninstall policy.
 *
 * Data is preserved unless the site owner explicitly opts in to removal.
 * Actual table cleanup is introduced with the AP-005 migrations.
 *
 * @package AgentPress
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

if ( ! defined( 'AGENTPRESS_REMOVE_DATA_ON_UNINSTALL' ) || true !== AGENTPRESS_REMOVE_DATA_ON_UNINSTALL ) {
	return;
}

delete_option( 'agentpress_version' );
