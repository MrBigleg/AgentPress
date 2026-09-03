<?php
/**
 * Private wp-admin read routes.
 *
 * @package AgentPress
 */

namespace AgentPress\Rest;

// phpcs:disable Generic.Commenting.DocComment.MissingShort,Squiz.Commenting.FunctionComment.ParamCommentFullStop,Squiz.Commenting.FunctionComment.MissingParamTag -- Concise private-controller annotations remain type-complete.

use AgentPress\Audit\ActivityReadService;
use AgentPress\Changes\ChangeSetReadService;
use AgentPress\Errors\ErrorFactory;

/**
 * Exposes the Change Set and Activity services to the signed-in admin UI.
 */
final class AdminReadRoutes {
	/** @var RequestGuard */
	private $guard;

	/** @var ChangeSetReadService */
	private $changes;

	/** @var ActivityReadService */
	private $activity;

	/**
	 * Construct the private read controller.
	 *
	 * @param RequestGuard|null         $guard    Optional guard.
	 * @param ChangeSetReadService|null $changes Optional Change Set service.
	 * @param ActivityReadService|null  $activity Optional Activity service.
	 */
	public function __construct( $guard = null, $changes = null, $activity = null ) {
		$this->guard    = $guard ?? new RequestGuard();
		$this->changes  = $changes ?? new ChangeSetReadService();
		$this->activity = $activity ?? new ActivityReadService();
	}

	/** @return void */
	public function register_hooks() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/** @return void */
	public function register_routes() {
		register_rest_route(
			WebMCPRoutes::REST_NAMESPACE,
			'/change-sets',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'list_change_sets' ),
				'permission_callback' => array( $this, 'authorize' ),
			)
		);
		register_rest_route(
			WebMCPRoutes::REST_NAMESPACE,
			'/change-sets/(?P<id>[\d]+)',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_change_set' ),
				'permission_callback' => array( $this, 'authorize' ),
			)
		);
		register_rest_route(
			WebMCPRoutes::REST_NAMESPACE,
			'/activity',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_activity' ),
				'permission_callback' => array( $this, 'authorize' ),
			)
		);
	}

	/** @param \WP_REST_Request $request Request. @return true|\WP_Error */
	public function authorize( $request ) {
		return $this->guard->authorize_rest( $request, 'admin-read', RequestGuard::DEFAULT_MAX_BYTES, 120 );
	}

	/** @param \WP_REST_Request $request Request. @return \WP_REST_Response|\WP_Error */
	public function list_change_sets( $request ) {
		return $this->response( $this->changes->listing( $this->query_input( $request, array( 'status', 'page', 'per_page' ) ) ) );
	}

	/** @param \WP_REST_Request $request Request. @return \WP_REST_Response|\WP_Error */
	public function get_change_set( $request ) {
		return $this->response( $this->changes->get( array( 'change_set_id' => (int) $request['id'] ) ) );
	}

	/** @param \WP_REST_Request $request Request. @return \WP_REST_Response|\WP_Error */
	public function get_activity( $request ) {
		return $this->response( $this->activity->execute( $this->query_input( $request, array( 'change_set_id', 'result', 'page', 'per_page' ) ) ) );
	}

	/** @param \WP_REST_Request $request Request. @param array<int, string> $allowed Allowed keys. @return array<string, mixed> */
	private function query_input( $request, $allowed ) {
		$query = $request->get_query_params();
		if ( array_diff( array_keys( $query ), $allowed ) ) {
			return array( '__unknown' => true );
		}
		$input = array();
		foreach ( $allowed as $key ) {
			if ( ! array_key_exists( $key, $query ) ) {
				continue;
			}
			$input[ $key ] = in_array( $key, array( 'change_set_id', 'page', 'per_page' ), true ) ? (int) $query[ $key ] : (string) $query[ $key ];
		}
		return $input;
	}

	/** @param mixed $result Service result. @return \WP_REST_Response|\WP_Error */
	private function response( $result ) {
		if ( is_wp_error( $result ) ) {
			return ErrorFactory::normalize( $result );
		}
		$response = rest_ensure_response( $result );
		$response->header( 'Cache-Control', 'private, no-store' );
		$response->header( 'Vary', 'Cookie' );
		return $response;
	}
}
// phpcs:enable Generic.Commenting.DocComment.MissingShort,Squiz.Commenting.FunctionComment.ParamCommentFullStop,Squiz.Commenting.FunctionComment.MissingParamTag
