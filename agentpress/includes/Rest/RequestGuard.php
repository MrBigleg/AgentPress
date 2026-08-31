<?php
/**
 * WebMCP transport request guard.
 *
 * @package AgentPress
 */

namespace AgentPress\Rest;

use AgentPress\Errors\ErrorFactory;

/**
 * Applies size, rate, origin, identity, and nonce controls before callbacks.
 */
final class RequestGuard {
	/** Default request body maximum. */
	public const DEFAULT_MAX_BYTES = 102400;

	/** Content-bearing execute request body maximum. */
	public const CONTENT_MAX_BYTES = 307200;

	/**
	 * Request rate limiter.
	 *
	 * @var RequestRateLimiter
	 */
	private $rate_limiter;

	/**
	 * Constructor.
	 *
	 * @param RequestRateLimiter|null $rate_limiter Optional limiter.
	 */
	public function __construct( $rate_limiter = null ) {
		$this->rate_limiter = $rate_limiter ?? new RequestRateLimiter();
	}

	/**
	 * Authorize one REST request.
	 *
	 * @param \WP_REST_Request $request   REST request.
	 * @param string           $bucket    Rate-limit bucket.
	 * @param int              $max_bytes Maximum raw body bytes.
	 * @param int              $rate_limit Requests allowed per minute.
	 * @return true|\WP_Error
	 */
	public function authorize_rest( $request, $bucket, $max_bytes, $rate_limit ) {
		$common = $this->authorize_session(
			(string) $request->get_body(),
			$bucket,
			$max_bytes,
			$request->get_header( 'Origin' ),
			$request->get_header( 'Sec-Fetch-Site' ),
			$rate_limit
		);

		if ( true !== $common ) {
			return $common;
		}

		$nonce = $request->get_header( 'X-WP-Nonce' );
		if ( ! is_string( $nonce ) || '' === $nonce || ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
			return ErrorFactory::make( 'AP_NONCE_INVALID' );
		}

		return true;
	}

	/**
	 * Authorize a signed-in same-origin request that does not require an old nonce.
	 *
	 * Used only to issue a fresh REST nonce.
	 *
	 * @param string      $body           Raw request body.
	 * @param string      $bucket         Rate-limit bucket.
	 * @param int         $max_bytes      Maximum raw body bytes.
	 * @param string|null $origin         Origin header.
	 * @param string|null $sec_fetch_site Sec-Fetch-Site header.
	 * @param int         $rate_limit     Requests allowed per minute.
	 * @return true|\WP_Error
	 */
	public function authorize_session( $body, $bucket, $max_bytes, $origin = null, $sec_fetch_site = null, $rate_limit = 120 ) {
		if ( strlen( $body ) > $max_bytes ) {
			return ErrorFactory::make( 'AP_SCHEMA_INVALID' );
		}

		if ( ! $this->rate_limiter->allow( $bucket, $this->client_key(), $rate_limit ) ) {
			return $this->rate_limit_error();
		}

		if ( ! $this->is_same_origin( $origin, $sec_fetch_site ) ) {
			return ErrorFactory::make( 'AP_POLICY_BLOCKED' );
		}

		if ( ! is_user_logged_in() ) {
			return ErrorFactory::make( 'AP_NOT_AUTHENTICATED' );
		}

		return true;
	}

	/**
	 * Enforce the per-user per-Ability execution ceiling before resolution.
	 *
	 * @param string $ability_name Exact allowlisted Ability name.
	 * @return true|\WP_Error
	 */
	public function authorize_ability( $ability_name ) {
		if ( ! $this->rate_limiter->allow( 'ability_' . $ability_name, $this->client_key(), 30 ) ) {
			return $this->rate_limit_error();
		}

		return true;
	}

	/**
	 * Return a per-user key, falling back to the direct address without trusting proxy headers.
	 *
	 * @return string
	 */
	private function client_key() {
		$user_id = get_current_user_id();
		if ( $user_id > 0 ) {
			return 'user_' . $user_id;
		}

		return isset( $_SERVER['REMOTE_ADDR'] )
			? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) )
			: 'unknown';
	}

	/**
	 * Build the common rate-limit response.
	 *
	 * @return \WP_Error
	 */
	private function rate_limit_error() {
		return ErrorFactory::make(
			'AP_RATE_LIMITED',
			array( 'retry_after' => RequestRateLimiter::retry_after() )
		);
	}

	/**
	 * Validate optional browser origin metadata.
	 *
	 * @param string|null $origin         Origin header.
	 * @param string|null $sec_fetch_site Sec-Fetch-Site header.
	 * @return bool
	 */
	private function is_same_origin( $origin, $sec_fetch_site ) {
		if ( is_string( $sec_fetch_site ) && 'cross-site' === strtolower( trim( $sec_fetch_site ) ) ) {
			return false;
		}

		if ( ! is_string( $origin ) || '' === trim( $origin ) ) {
			return true;
		}

		$source = wp_parse_url( trim( $origin ) );
		$target = wp_parse_url( home_url( '/' ) );

		if ( ! is_array( $source ) || ! is_array( $target ) ) {
			return false;
		}

		return $this->normalized_origin( $source ) === $this->normalized_origin( $target );
	}

	/**
	 * Normalize URL parts to an origin string.
	 *
	 * @param array<string, mixed> $parts URL parts.
	 * @return string
	 */
	private function normalized_origin( $parts ) {
		$scheme = isset( $parts['scheme'] ) ? strtolower( (string) $parts['scheme'] ) : '';
		$host   = isset( $parts['host'] ) ? strtolower( (string) $parts['host'] ) : '';
		$port   = isset( $parts['port'] ) ? (int) $parts['port'] : ( 'https' === $scheme ? 443 : 80 );

		if ( '' === $scheme || '' === $host || ! in_array( $scheme, array( 'http', 'https' ), true ) ) {
			return '';
		}

		return $scheme . '://' . $host . ':' . $port;
	}
}
