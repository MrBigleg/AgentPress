<?php
/**
 * Plugin activation boundary.
 *
 * @package AgentPress
 */

namespace AgentPress;

/**
 * Owns installation-time initialization for supported runtimes.
 */
final class Activation {
	/**
	 * Run AP-001 activation work.
	 *
	 * Product migrations are intentionally introduced by AP-005.
	 *
	 * @return void
	 */
	public static function activate() {
		update_option( 'agentpress_version', AGENTPRESS_VERSION, false );
	}
}
