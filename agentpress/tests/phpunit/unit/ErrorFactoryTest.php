<?php
/**
 * Safe error contract tests.
 *
 * @package AgentPress
 */

use AgentPress\Errors\ErrorFactory;
use PHPUnit\Framework\TestCase;

final class ErrorFactoryTest extends TestCase {
	/**
	 * @dataProvider declaredErrors
	 */
	public function test_declared_errors_serialize_to_exact_contract( $code, $status, $retryable ) {
		$request_id = '00000000-0000-4000-8000-000000000012';
		$response   = ErrorFactory::response( ErrorFactory::make( $code ), $request_id );
		$body       = $response['body'];

		$this->assertSame( $status, $response['status'] );
		$this->assertSame( array( 'ok', 'request_id', 'error' ), array_keys( $body ) );
		$this->assertFalse( $body['ok'] );
		$this->assertSame( $request_id, $body['request_id'] );
		$this->assertSame( array( 'code', 'message', 'retryable', 'details' ), array_keys( $body['error'] ) );
		$this->assertSame( $code, $body['error']['code'] );
		$this->assertIsString( $body['error']['message'] );
		$this->assertSame( $retryable, $body['error']['retryable'] );
		$this->assertInstanceOf( stdClass::class, $body['error']['details'] );
	}

	public function declaredErrors() {
		return array(
			'not authenticated'      => array( 'AP_NOT_AUTHENTICATED', 401, false ),
			'nonce invalid'          => array( 'AP_NONCE_INVALID', 403, true ),
			'permission denied'      => array( 'AP_PERMISSION_DENIED', 403, false ),
			'policy blocked'         => array( 'AP_POLICY_BLOCKED', 403, false ),
			'approval required'      => array( 'AP_APPROVAL_REQUIRED', 409, false ),
			'schema invalid'         => array( 'AP_SCHEMA_INVALID', 400, false ),
			'content not found'      => array( 'AP_CONTENT_NOT_FOUND', 404, false ),
			'term not found'         => array( 'AP_TERM_NOT_FOUND', 404, false ),
			'change not found'       => array( 'AP_CHANGE_NOT_FOUND', 404, false ),
			'navigation not found'   => array( 'AP_NAVIGATION_NOT_FOUND', 404, false ),
			'state conflict'         => array( 'AP_STATE_CONFLICT', 409, false ),
			'change expired'         => array( 'AP_CHANGE_EXPIRED', 410, false ),
			'rate limited'           => array( 'AP_RATE_LIMITED', 429, true ),
			'unsupported post type'  => array( 'AP_UNSUPPORTED_POST_TYPE', 422, false ),
			'unsupported taxonomy'   => array( 'AP_UNSUPPORTED_TAXONOMY', 422, false ),
			'unsupported navigation' => array( 'AP_UNSUPPORTED_NAVIGATION', 422, false ),
			'internal error'         => array( 'AP_INTERNAL_ERROR', 500, false ),
		);
	}

	public function test_unknown_error_suppresses_message_and_secret_details() {
		$error = new WP_Error(
			'AP_DATABASE_EXPLODED',
			'SQL failed with password=hunter2',
			array(
				'details' => array(
					'authorization' => 'Bearer secret',
					'safe_reason'   => str_repeat( 'x', 400 ),
				),
			)
		);
		$response = ErrorFactory::response( $error, '00000000-0000-4000-8000-000000000012' );
		$failure  = $response['body']['error'];

		$this->assertSame( 500, $response['status'] );
		$this->assertSame( 'AP_INTERNAL_ERROR', $failure['code'] );
		$this->assertStringNotContainsString( 'SQL', $failure['message'] );
		$this->assertArrayNotHasKey( 'authorization', $failure['details'] );
		$this->assertSame( ErrorFactory::MAX_DETAIL_STRING_BYTES, strlen( $failure['details']['safe_reason'] ) );
		$this->assertLessThanOrEqual( ErrorFactory::MAX_DETAILS_BYTES, strlen( wp_json_encode( $failure['details'] ) ) );
	}

	public function test_rate_limit_preserves_only_bounded_retry_guidance() {
		$error    = new WP_Error( 'AP_RATE_LIMITED', 'unsafe', array( 'retry_after' => 60, 'cookie' => 'secret' ) );
		$response = ErrorFactory::response( $error, '00000000-0000-4000-8000-000000000012' );

		$this->assertSame( array( 'retry_after' => 60 ), $response['body']['error']['details'] );
	}
}
