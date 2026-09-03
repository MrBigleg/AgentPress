<?php
/**
 * Plugin Name: AgentPress
 * Plugin URI: https://github.com/MrBigleg/AgentPress
 * Description: The shared human-agent workspace for WordPress.
 * Version: 0.1.0
 * Requires at least: 6.9
 * Requires PHP: 8.0
 * Author: AgentPress contributors
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: agentpress
 *
 * @package AgentPress
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'AGENTPRESS_VERSION', '0.1.0' );
define( 'AGENTPRESS_FILE', __FILE__ );
define( 'AGENTPRESS_PATH', plugin_dir_path( __FILE__ ) );

require_once AGENTPRESS_PATH . 'includes/Autoloader.php';

AgentPress\Autoloader::register();

$agentpress_compatibility = new AgentPress\Compatibility( $GLOBALS['wp_version'], PHP_VERSION );

if ( ! $agentpress_compatibility->is_supported() ) {
	add_action( 'admin_notices', array( $agentpress_compatibility, 'render_admin_notice' ) );
	return;
}

register_activation_hook( AGENTPRESS_FILE, array( AgentPress\Activation::class, 'activate' ) );
register_deactivation_hook( AGENTPRESS_FILE, array( AgentPress\Activation::class, 'deactivate' ) );
AgentPress\Plugin::instance()->boot();
