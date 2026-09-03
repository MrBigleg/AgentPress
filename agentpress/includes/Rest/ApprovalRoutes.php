<?php
/**
 * Private wp-admin approval routes.
 *
 * @package AgentPress
 */

namespace AgentPress\Rest;

// phpcs:disable Generic.Commenting.DocComment.MissingShort,Squiz.Commenting.FunctionComment.ParamCommentFullStop,Squiz.Commenting.FunctionComment.MissingParamTag -- Concise private-controller annotations remain type-complete.

use AgentPress\Changes\ApprovalService;
use AgentPress\Errors\ErrorFactory;

/**
 * Exposes human approve/reject actions to the signed-in admin UI.
 */
final class ApprovalRoutes {
	/** @var RequestGuard */
	private $guard;

	/** @var ApprovalService|object */
	private $approval;

	/**
	 * Construct the approval controller.
	 *
	 * @param RequestGuard|null    $guard    Optional guard.
	 * @param ApprovalService|null $approval Optional approval service.
	 */
	public function __construct( $guard = null, $approval = null ) {
		$this->guard    = $guard ?? new RequestGuard();
		$this->approval = $approval ?? new ApprovalService();
	}

	/** @return void */
	public function register_hooks() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/** @return void */
	public function register_routes() {
		register_rest_route(
			WebMCPRoutes::REST_NAMESPACE,
			'/changes/(?P<id>[\d]+)/approve',
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'approve' ),
				'permission_callback' => array( $this, 'authorize' ),
			)
		);
		register_rest_route(
			WebMCPRoutes::REST_NAMESPACE,
			'/changes/(?P<id>[\d]+)/reject',
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'reject' ),
				'permission_callback' => array( $this, 'authorize' ),
			)
		);
	}

	/** @param \WP_REST_Request $request Request. @return true|\WP_Error */
	public function authorize( $request ) {
		return $this->guard->authorize_rest( $request, 'admin-approval', RequestGuard::DEFAULT_MAX_BYTES, 60 );
	}

	/** @param \WP_REST_Request $request Request. @return \WP_REST_Response|\WP_Error */
	public function approve( $request ) {
		return $this->response( $this->approval->approve( (int) $request['id'] ) );
	}

	/** @param \WP_REST_Request $request Request. @return \WP_REST_Response|\WP_Error */
	public function reject( $request ) {
		$params = $request->get_json_params();
		$reason = is_array( $params ) && isset( $params['reason'] ) ? (string) $params['reason'] : '';
		return $this->response( $this->approval->reject( (int) $request['id'], $reason ) );
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
