<?php
/**
 * AgentPress wp-admin shell and Overview page.
 *
 * @package AgentPress
 */

namespace AgentPress\Admin;

use AgentPress\Context\ContextService;

/**
 * Registers and renders the page-scoped AgentPress control surface.
 */
final class AdminPage {
	/** WordPress screen hook suffix. */
	const HOOK_SUFFIX = 'toplevel_page_agentpress';

	/**
	 * Register wp-admin lifecycle hooks.
	 *
	 * @return void
	 */
	public function register_hooks() {
		add_action( 'admin_menu', array( $this, 'register_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	/**
	 * Register the read-authorized top-level page.
	 *
	 * @return void
	 */
	public function register_menu() {
		add_menu_page(
			__( 'AgentPress', 'agentpress' ),
			__( 'AgentPress', 'agentpress' ),
			'read',
			'agentpress',
			array( $this, 'render' ),
			'dashicons-shield-alt',
			58
		);
	}

	/**
	 * Enqueue assets only on the AgentPress screen.
	 *
	 * @param string $hook_suffix Current admin hook.
	 * @return void
	 */
	public function enqueue_assets( $hook_suffix ) {
		if ( self::HOOK_SUFFIX !== $hook_suffix || ! current_user_can( 'read' ) ) {
			return;
		}

		wp_enqueue_style(
			'agentpress-admin',
			plugins_url( 'admin/src/admin-overview.css', AGENTPRESS_FILE ),
			array(),
			AGENTPRESS_VERSION
		);
		wp_enqueue_script_module(
			'agentpress-admin',
			plugins_url( 'admin/src/admin-overview.mjs', AGENTPRESS_FILE ),
			array(),
			AGENTPRESS_VERSION
		);
	}

	/**
	 * Render the stable application root and private runtime settings.
	 *
	 * @return void
	 */
	public function render() {
		if ( ! current_user_can( 'read' ) ) {
			wp_die( esc_html__( 'You do not have permission to view AgentPress.', 'agentpress' ) );
		}

		$settings = array(
			'toolsEndpoint'      => rest_url( 'agentpress/v1/webmcp/tools' ),
			'executeEndpoint'    => rest_url( 'agentpress/v1/webmcp/execute' ),
			'changeSetsEndpoint' => rest_url( 'agentpress/v1/change-sets' ),
			'activityEndpoint'   => rest_url( 'agentpress/v1/activity' ),
			'updatesEndpoint'    => rest_url( 'agentpress/v1/updates' ),
			'changesEndpoint'    => rest_url( 'agentpress/v1/changes/' ),
			'refreshEndpoint'    => admin_url( 'admin-ajax.php' ),
			'pollIntervalMs'     => 5000,
			'context'            => ( new ContextService() )->execute(),
			'nonce'              => wp_create_nonce( 'wp_rest' ),
			'isHttps'            => is_ssl(),
			'abilitiesApi'       => function_exists( 'wp_get_ability' ),
			'wordpress'          => get_bloginfo( 'version' ),
		);
		?>
		<div class="wrap agentpress-admin-wrap">
			<div id="agentpress-admin" class="agentpress-admin" aria-live="polite">
				<div class="agentpress-skeleton" aria-label="<?php esc_attr_e( 'Loading AgentPress', 'agentpress' ); ?>">
					<span></span><span></span><span></span>
				</div>
			</div>
			<noscript><div class="notice notice-error"><p><?php esc_html_e( 'AgentPress requires JavaScript on this page.', 'agentpress' ); ?></p></div></noscript>
			<script type="application/json" id="agentpress-admin-settings"><?php echo wp_json_encode( $settings, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON is hex-escaped for a script data block. ?></script>
		</div>
		<?php
	}
}
