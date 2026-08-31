<?php
/**
 * Private WebMCP REST transport.
 *
 * @package AgentPress
 */

namespace AgentPress\Rest;

use AgentPress\Errors\ErrorFactory;
use AgentPress\Policy\DiscoveryPolicy;
use AgentPress\WebMCP\AbilityMap;

/**
 * Registers private discovery, execution, and nonce-refresh interfaces.
 */
final class WebMCPRoutes {
	/** REST namespace. */
	public const REST_NAMESPACE = 'agentpress/v1';

	/** Discovery route. */
	public const TOOLS_ROUTE = '/webmcp/tools';

	/** Execution route. */
	public const EXECUTE_ROUTE = '/webmcp/execute';

	/**
	 * Request guard.
	 *
	 * @var RequestGuard
	 */
	private $guard;

	/**
	 * Definition provider.
	 *
	 * @var callable
	 */
	private $definition_provider;

	/**
	 * Ability resolver.
	 *
	 * @var callable
	 */
	private $ability_resolver;

	/**
	 * Coarse current-user discovery policy.
	 *
	 * @var DiscoveryPolicy
	 */
	private $discovery_policy;

	/**
	 * Constructor.
	 *
	 * @param RequestGuard|null    $guard               Optional guard.
	 * @param callable|null        $definition_provider Optional definition provider.
	 * @param callable|null        $ability_resolver    Optional Ability resolver.
	 * @param DiscoveryPolicy|null $discovery_policy   Optional discovery policy.
	 */
	public function __construct( $guard = null, $definition_provider = null, $ability_resolver = null, $discovery_policy = null ) {
		$this->guard               = $guard ?? new RequestGuard();
		$this->definition_provider = $definition_provider ?? array( $this, 'default_definitions' );
		$this->ability_resolver    = $ability_resolver ?? static function ( $ability_name ) {
			if (
				class_exists( '\\WP_Abilities_Registry' ) &&
				! \WP_Abilities_Registry::get_instance()->is_registered( $ability_name )
			) {
				return null;
			}

			return function_exists( 'wp_get_ability' ) ? wp_get_ability( $ability_name ) : null;
		};
		$this->discovery_policy    = $discovery_policy ?? new DiscoveryPolicy();
	}

	/**
	 * Register WordPress hooks.
	 *
	 * @return void
	 */
	public function register_hooks() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
		add_filter( 'rest_post_dispatch', array( $this, 'add_private_headers' ), 10, 3 );
		add_action( 'wp_ajax_agentpress_refresh_nonce', array( $this, 'handle_nonce_refresh' ) );
	}

	/**
	 * Register private REST routes.
	 *
	 * @return void
	 */
	public function register_routes() {
		register_rest_route(
			self::REST_NAMESPACE,
			self::TOOLS_ROUTE,
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_tools' ),
				'permission_callback' => array( $this, 'authorize_tools' ),
			)
		);

		register_rest_route(
			self::REST_NAMESPACE,
			self::EXECUTE_ROUTE,
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'execute' ),
				'permission_callback' => array( $this, 'authorize_execute' ),
			)
		);
	}

	/**
	 * Authorize discovery.
	 *
	 * @param \WP_REST_Request $request REST request.
	 * @return true|\WP_Error
	 */
	public function authorize_tools( $request ) {
		return $this->guard->authorize_rest(
			$request,
			'tools',
			RequestGuard::DEFAULT_MAX_BYTES,
			60
		);
	}

	/**
	 * Authorize execution before decoding JSON or resolving an Ability.
	 *
	 * @param \WP_REST_Request $request REST request.
	 * @return true|\WP_Error
	 */
	public function authorize_execute( $request ) {
		return $this->guard->authorize_rest(
			$request,
			'execute-total',
			RequestGuard::CONTENT_MAX_BYTES,
			120
		);
	}

	/**
	 * Return current-user definitions filtered through the fixed map.
	 *
	 * @return \WP_REST_Response
	 */
	public function get_tools() {
		$definitions = call_user_func( $this->definition_provider );
		$tools       = array();

		if ( is_array( $definitions ) ) {
			foreach ( $definitions as $definition ) {
				if ( ! is_array( $definition ) || ! isset( $definition['ability'] ) ) {
					continue;
				}

				$ability_name = (string) $definition['ability'];
				$tool_name    = AbilityMap::tool_name( $ability_name );
				if ( null === $tool_name ) {
					continue;
				}

				$tools[] = array(
					'ability'     => $ability_name,
					'name'        => $tool_name,
					'description' => isset( $definition['description'] ) ? (string) $definition['description'] : '',
					'inputSchema' => isset( $definition['inputSchema'] ) && is_array( $definition['inputSchema'] ) ? $definition['inputSchema'] : array(
						'type'                 => 'object',
						'properties'           => new \stdClass(),
						'additionalProperties' => false,
					),
					'annotations' => isset( $definition['annotations'] ) && is_array( $definition['annotations'] ) ? $definition['annotations'] : array(),
				);
			}
		}

		return $this->private_response( array( 'tools' => $tools ) );
	}

	/**
	 * Execute one exact allowlisted Ability.
	 *
	 * @param \WP_REST_Request $request REST request.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function execute( $request ) {
		$params = $request->get_json_params();
		if (
			! is_array( $params ) ||
			2 !== count( $params ) ||
			! array_key_exists( 'ability', $params ) ||
			! array_key_exists( 'input', $params )
		) {
			return ErrorFactory::make( 'AP_SCHEMA_INVALID' );
		}

		$ability_name = is_string( $params['ability'] ) ? $params['ability'] : '';
		if ( ! AbilityMap::contains( $ability_name ) ) {
			return ErrorFactory::make( 'AP_PERMISSION_DENIED' );
		}

		if ( ! is_array( $params['input'] ) ) {
			return ErrorFactory::make( 'AP_SCHEMA_INVALID' );
		}

		if ( ! $this->allows_content_body( $ability_name ) && strlen( (string) $request->get_body() ) > RequestGuard::DEFAULT_MAX_BYTES ) {
			return ErrorFactory::make( 'AP_SCHEMA_INVALID' );
		}

		$ability_rate = $this->guard->authorize_ability( $ability_name );
		if ( is_wp_error( $ability_rate ) ) {
			return $ability_rate;
		}

		/**
		 * Fires immediately before an allowlisted Ability is resolved.
		 *
		 * @param string $ability_name Exact allowlisted Ability name.
		 */
		do_action( 'agentpress_webmcp_before_ability_resolve', $ability_name );
		$ability = call_user_func( $this->ability_resolver, $ability_name );
		if ( ! is_object( $ability ) || ! is_callable( array( $ability, 'check_permissions' ) ) || ! is_callable( array( $ability, 'execute' ) ) ) {
			return ErrorFactory::make( 'AP_INTERNAL_ERROR' );
		}
		if ( is_callable( array( $ability, 'validate_input' ) ) ) {
			$valid = $ability->validate_input( $params['input'] );
			if ( true !== $valid ) {
				return ErrorFactory::make( 'AP_SCHEMA_INVALID' );
			}
		}

		$permission = $ability->check_permissions( $params['input'] );
		if ( true !== $permission ) {
			return ErrorFactory::make( 'AP_PERMISSION_DENIED' );
		}

		$result = $ability->execute( $params['input'] );
		if ( is_wp_error( $result ) ) {
			return ErrorFactory::normalize( $result );
		}

		return $this->private_response( $result );
	}

	/**
	 * Add private transport headers to successes and route errors.
	 *
	 * @param \WP_REST_Response $response REST response.
	 * @param \WP_REST_Server   $server   REST server.
	 * @param \WP_REST_Request  $request  REST request.
	 * @return \WP_REST_Response
	 */
	public function add_private_headers( $response, $server, $request ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
		$route = $request->get_route();
		if ( 0 !== strpos( $route, '/' . self::REST_NAMESPACE . '/webmcp/' ) ) {
			return $response;
		}

		$data = $response->get_data();
		if ( $response->get_status() >= 400 && is_array( $data ) && isset( $data['code'] ) ) {
			$source_code = 'rest_invalid_json' === $data['code'] ? 'AP_SCHEMA_INVALID' : (string) $data['code'];
			$source_data = isset( $data['data'] ) && is_array( $data['data'] ) ? $data['data'] : array();
			$normalized  = ErrorFactory::response( new \WP_Error( $source_code, '', $source_data ) );
			$response->set_status( $normalized['status'] );
			$response->set_data( $normalized['body'] );
			$data = $normalized['body'];
		}

		$response->header( 'Cache-Control', 'private, no-store' );
		$response->header( 'Vary', 'Cookie' );

		if (
			is_array( $data ) &&
			isset( $data['error']['code'], $data['error']['details'] ) &&
			'AP_RATE_LIMITED' === $data['error']['code'] &&
			is_array( $data['error']['details'] ) &&
			isset( $data['error']['details']['retry_after'] )
		) {
			$response->header( 'Retry-After', (string) absint( $data['error']['details']['retry_after'] ) );
		}

		return $response;
	}

	/**
	 * Issue one fresh REST nonce to the current signed-in same-origin session.
	 *
	 * @return void
	 */
	public function handle_nonce_refresh() {
		header( 'Cache-Control: private, no-store' );
		header( 'Vary: Cookie' );

		$result = $this->get_refreshed_nonce(
			isset( $_SERVER['HTTP_ORIGIN'] )
				? sanitize_url( wp_unslash( $_SERVER['HTTP_ORIGIN'] ) )
				: null,
			isset( $_SERVER['HTTP_SEC_FETCH_SITE'] )
				? sanitize_key( wp_unslash( $_SERVER['HTTP_SEC_FETCH_SITE'] ) )
				: null
		);

		if ( is_wp_error( $result ) ) {
			$error = ErrorFactory::response( $result );
			wp_send_json( $error['body'], $error['status'] );
		}

		wp_send_json_success( $result );
	}

	/**
	 * Return a fresh nonce payload for a signed-in same-origin session.
	 *
	 * @param string|null $origin         Origin header.
	 * @param string|null $sec_fetch_site Sec-Fetch-Site header.
	 * @return array<string, string>|\WP_Error
	 */
	public function get_refreshed_nonce( $origin = null, $sec_fetch_site = null ) {
		$authorization = $this->guard->authorize_session(
			'',
			'nonce-refresh',
			RequestGuard::DEFAULT_MAX_BYTES,
			$origin,
			$sec_fetch_site
		);

		if ( is_wp_error( $authorization ) ) {
			return $authorization;
		}

		return array( 'nonce' => wp_create_nonce( 'wp_rest' ) );
	}

	/**
	 * Build definitions from currently registered fixed AgentPress Abilities.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function default_definitions() {
		$definitions = array();

		foreach ( AbilityMap::all() as $ability_name => $tool_name ) {
			if ( ! $this->discovery_policy->can_discover( $ability_name ) ) {
				continue;
			}
			do_action( 'agentpress_webmcp_before_ability_resolve', $ability_name );
			$ability = call_user_func( $this->ability_resolver, $ability_name );
			if ( ! is_object( $ability ) ) {
				continue;
			}

			$meta          = is_callable( array( $ability, 'get_meta' ) ) ? $ability->get_meta() : array();
			$annotations   = isset( $meta['annotations'] ) && is_array( $meta['annotations'] ) ? $meta['annotations'] : array();
			$definitions[] = array(
				'ability'     => $ability_name,
				'name'        => $tool_name,
				'description' => $ability->get_description(),
				'inputSchema' => $ability->get_input_schema(),
				'annotations' => array(
					'readOnlyHint'         => ! empty( $annotations['readOnlyHint'] ),
					'untrustedContentHint' => ! empty( $annotations['untrustedContentHint'] ),
				),
			);
		}

		return $definitions;
	}

	/**
	 * Return whether an Ability may use the larger content body cap.
	 *
	 * @param string $ability_name Ability name.
	 * @return bool
	 */
	private function allows_content_body( $ability_name ) {
		return in_array(
			$ability_name,
			array( 'agentpress/create-draft', 'agentpress/update-content' ),
			true
		);
	}

	/**
	 * Build a private response.
	 *
	 * @param mixed $data   Response data.
	 * @param int   $status HTTP status.
	 * @return \WP_REST_Response
	 */
	private function private_response( $data, $status = 200 ) {
		$response = new \WP_REST_Response( $data, $status );
		$response->header( 'Cache-Control', 'private, no-store' );
		$response->header( 'Vary', 'Cookie' );
		return $response;
	}
}
