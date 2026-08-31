<?php
/**
 * Safe AgentPress error construction and serialization.
 *
 * @package AgentPress
 */

namespace AgentPress\Errors;

/**
 * Normalizes internal failures to the declared v0.1 error contract.
 */
final class ErrorFactory {
	/** Maximum encoded safe-detail bytes. */
	public const MAX_DETAILS_BYTES = 4096;

	/** Maximum safe-detail nesting depth. */
	public const MAX_DETAILS_DEPTH = 4;

	/** Maximum entries retained in one safe-detail array. */
	public const MAX_DETAILS_ITEMS = 25;

	/** Maximum bytes retained in one safe-detail string. */
	public const MAX_DETAIL_STRING_BYTES = 256;

	/**
	 * Return the exact declared error codes.
	 *
	 * @return array<int, string>
	 */
	public static function codes() {
		return array_keys( self::definitions() );
	}

	/**
	 * Create one canonical internal WordPress error.
	 *
	 * @param string               $code       Declared code.
	 * @param array<string, mixed> $details    Reviewed safe details.
	 * @param string|null          $request_id Optional UUIDv4.
	 * @return \WP_Error
	 */
	public static function make( $code, $details = array(), $request_id = null ) {
		$code       = self::declared_code( $code );
		$definition = self::definitions()[ $code ];
		$request_id = self::request_id( $request_id );

		return new \WP_Error(
			$code,
			$definition['message'],
			array(
				'status'     => $definition['status'],
				'request_id' => $request_id,
				'retryable'  => $definition['retryable'],
				'details'    => self::safe_details( $details ),
			)
		);
	}

	/**
	 * Normalize an arbitrary WordPress error without trusting its message.
	 *
	 * @param \WP_Error   $error      Source error.
	 * @param string|null $request_id Optional UUIDv4.
	 * @return \WP_Error
	 */
	public static function normalize( $error, $request_id = null ) {
		$code    = self::declared_code( $error->get_error_code() );
		$data    = $error->get_error_data();
		$details = array();

		if ( is_array( $data ) ) {
			if ( isset( $data['details'] ) && is_array( $data['details'] ) ) {
				$details = $data['details'];
			}
			if ( 'AP_RATE_LIMITED' === $code && isset( $data['retry_after'] ) ) {
				$details['retry_after'] = max( 0, (int) $data['retry_after'] );
			}
			if ( null === $request_id && isset( $data['request_id'] ) && is_string( $data['request_id'] ) ) {
				$request_id = $data['request_id'];
			}
		}

		return self::make( $code, $details, $request_id );
	}

	/**
	 * Serialize one error with its HTTP status and exact response body.
	 *
	 * @param \WP_Error   $error      Source error.
	 * @param string|null $request_id Optional UUIDv4.
	 * @return array{status:int, body:array<string, mixed>}
	 */
	public static function response( $error, $request_id = null ) {
		$normalized = self::normalize( $error, $request_id );
		$data       = $normalized->get_error_data();

		return array(
			'status' => $data['status'],
			'body'   => array(
				'ok'         => false,
				'request_id' => $data['request_id'],
				'error'      => array(
					'code'      => $normalized->get_error_code(),
					'message'   => $normalized->get_error_message(),
					'retryable' => $data['retryable'],
					'details'   => $data['details'],
				),
			),
		);
	}

	/**
	 * Return the canonical definition map.
	 *
	 * @return array<string, array{status:int, retryable:bool, message:string}>
	 */
	private static function definitions() {
		return array(
			'AP_NOT_AUTHENTICATED'      => array(
				'status'    => 401,
				'retryable' => false,
				'message'   => __( 'WordPress authentication is required.', 'agentpress' ),
			),
			'AP_NONCE_INVALID'          => array(
				'status'    => 403,
				'retryable' => true,
				'message'   => __( 'A valid WordPress REST nonce is required.', 'agentpress' ),
			),
			'AP_PERMISSION_DENIED'      => array(
				'status'    => 403,
				'retryable' => false,
				'message'   => __( 'The current user does not have permission for this operation.', 'agentpress' ),
			),
			'AP_POLICY_BLOCKED'         => array(
				'status'    => 403,
				'retryable' => false,
				'message'   => __( 'AgentPress policy does not permit this operation.', 'agentpress' ),
			),
			'AP_APPROVAL_REQUIRED'      => array(
				'status'    => 409,
				'retryable' => false,
				'message'   => __( 'This operation requires explicit WordPress approval.', 'agentpress' ),
			),
			'AP_SCHEMA_INVALID'         => array(
				'status'    => 400,
				'retryable' => false,
				'message'   => __( 'The request does not match the required schema.', 'agentpress' ),
			),
			'AP_CONTENT_NOT_FOUND'      => array(
				'status'    => 404,
				'retryable' => false,
				'message'   => __( 'The requested content could not be found.', 'agentpress' ),
			),
			'AP_TERM_NOT_FOUND'         => array(
				'status'    => 404,
				'retryable' => false,
				'message'   => __( 'The requested term could not be found.', 'agentpress' ),
			),
			'AP_CHANGE_NOT_FOUND'       => array(
				'status'    => 404,
				'retryable' => false,
				'message'   => __( 'The requested change could not be found.', 'agentpress' ),
			),
			'AP_NAVIGATION_NOT_FOUND'   => array(
				'status'    => 404,
				'retryable' => false,
				'message'   => __( 'The requested navigation target could not be found.', 'agentpress' ),
			),
			'AP_STATE_CONFLICT'         => array(
				'status'    => 409,
				'retryable' => false,
				'message'   => __( 'The target state changed; inspect it and stage the operation again.', 'agentpress' ),
			),
			'AP_CHANGE_EXPIRED'         => array(
				'status'    => 410,
				'retryable' => false,
				'message'   => __( 'The approval window for this change has expired.', 'agentpress' ),
			),
			'AP_RATE_LIMITED'           => array(
				'status'    => 429,
				'retryable' => true,
				'message'   => __( 'Too many AgentPress requests.', 'agentpress' ),
			),
			'AP_UNSUPPORTED_POST_TYPE'  => array(
				'status'    => 422,
				'retryable' => false,
				'message'   => __( 'AgentPress supports only posts and pages.', 'agentpress' ),
			),
			'AP_UNSUPPORTED_TAXONOMY'   => array(
				'status'    => 422,
				'retryable' => false,
				'message'   => __( 'AgentPress supports only categories and post tags.', 'agentpress' ),
			),
			'AP_UNSUPPORTED_NAVIGATION' => array(
				'status'    => 422,
				'retryable' => false,
				'message'   => __( 'This navigation architecture is not supported in AgentPress v0.1.', 'agentpress' ),
			),
			'AP_INTERNAL_ERROR'         => array(
				'status'    => 500,
				'retryable' => false,
				'message'   => __( 'AgentPress could not complete the request.', 'agentpress' ),
			),
		);
	}

	/**
	 * Map unknown codes to the one safe internal failure.
	 *
	 * @param string $code Candidate code.
	 * @return string
	 */
	private static function declared_code( $code ) {
		return array_key_exists( $code, self::definitions() ) ? $code : 'AP_INTERNAL_ERROR';
	}

	/**
	 * Return a valid request ID, generating one when necessary.
	 *
	 * @param string|null $request_id Candidate request ID.
	 * @return string
	 */
	private static function request_id( $request_id ) {
		if ( is_string( $request_id ) && 1 === preg_match( '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $request_id ) ) {
			return strtolower( $request_id );
		}

		return wp_generate_uuid4();
	}

	/**
	 * Remove secret-like or unbounded detail before client serialization.
	 *
	 * @param array<string, mixed> $details Candidate details.
	 * @return array<string, mixed>|\stdClass
	 */
	private static function safe_details( $details ) {
		$safe = self::safe_value( $details, 0 );
		if ( ! is_array( $safe ) || empty( $safe ) ) {
			return new \stdClass();
		}

		$encoded = wp_json_encode( $safe );
		if ( ! is_string( $encoded ) || strlen( $encoded ) > self::MAX_DETAILS_BYTES ) {
			return new \stdClass();
		}

		return $safe;
	}

	/**
	 * Recursively retain only bounded scalar/list/object detail values.
	 *
	 * @param mixed $value Candidate value.
	 * @param int   $depth Current depth.
	 * @return mixed
	 */
	private static function safe_value( $value, $depth ) {
		if ( $depth > self::MAX_DETAILS_DEPTH ) {
			return null;
		}
		if ( is_string( $value ) ) {
			return substr( $value, 0, self::MAX_DETAIL_STRING_BYTES );
		}
		if ( is_int( $value ) || is_bool( $value ) || null === $value ) {
			return $value;
		}
		if ( is_float( $value ) ) {
			return is_finite( $value ) ? $value : null;
		}
		if ( ! is_array( $value ) ) {
			return null;
		}

		$safe  = array();
		$count = 0;
		foreach ( $value as $key => $item ) {
			if ( $count >= self::MAX_DETAILS_ITEMS ) {
				break;
			}
			if ( is_string( $key ) && 1 === preg_match( '/authorization|cookie|nonce|password|secret|token|api[_-]?key/i', $key ) ) {
				continue;
			}
			$safe_key          = is_int( $key ) ? $key : substr( $key, 0, 64 );
			$safe[ $safe_key ] = self::safe_value( $item, $depth + 1 );
			++$count;
		}

		return $safe;
	}
}
