<?php
/**
 * AgentPress runtime shell.
 *
 * @package AgentPress
 */

namespace AgentPress;

use AgentPress\Abilities\AbilityRegistrar;
use AgentPress\Admin\AdminPage;
use AgentPress\Storage\Migrator;

/**
 * Minimal supported-runtime shell for AgentPress.
 */
final class Plugin {
	/**
	 * Private WebMCP transport.
	 *
	 * @var \AgentPress\Rest\WebMCPRoutes|null
	 */
	private $webmcp_routes;

	/**
	 * Fixed WordPress Ability registrar.
	 *
	 * @var AbilityRegistrar|null
	 */
	private $ability_registrar;

	/**
	 * Page-scoped wp-admin shell.
	 *
	 * @var AdminPage|null
	 */
	private $admin_page;

	/**
	 * Shared plugin instance.
	 *
	 * @var Plugin|null
	 */
	private static $instance;

	/**
	 * Whether the runtime shell has initialized.
	 *
	 * @var bool
	 */
	private $booted = false;

	/**
	 * Return the shared plugin instance.
	 *
	 * @return Plugin
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Mark the supported runtime as initialized.
	 *
	 * @return void
	 */
	public function boot() {
		if ( $this->booted ) {
			return;
		}

		$this->booted            = true;
		$this->ability_registrar = new AbilityRegistrar();
		$this->ability_registrar->register_hooks();
		$this->admin_page = new AdminPage();
		$this->admin_page->register_hooks();
		$this->webmcp_routes = new \AgentPress\Rest\WebMCPRoutes();
		$this->webmcp_routes->register_hooks();
		add_action( 'plugins_loaded', array( Migrator::class, 'maybe_migrate' ) );
		do_action( 'agentpress_initialized', $this );
	}

	/**
	 * Report whether the runtime shell initialized.
	 *
	 * @return bool
	 */
	public function is_booted() {
		return $this->booted;
	}

	/**
	 * Prevent direct construction.
	 */
	private function __construct() {}
}
