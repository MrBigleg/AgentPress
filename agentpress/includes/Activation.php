<?php
/**
 * Plugin activation boundary.
 *
 * @package AgentPress
 */

namespace AgentPress;

use AgentPress\Storage\Migrator;

/**
 * Owns installation-time initialization for supported runtimes.
 */
final class Activation {
	/**
	 * Run supported activation work.
	 *
	 * @return void
	 */
	public static function activate() {
		Migrator::migrate();
		update_option( 'agentpress_version', AGENTPRESS_VERSION, false );
	}

	/**
	 * Preserve all durable AgentPress data on deactivation.
	 *
	 * @return void
	 */
	public static function deactivate() {}
}
