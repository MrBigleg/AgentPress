<?php
/**
 * Bounded pre-authentication request rate limiter.
 *
 * @package AgentPress
 */

namespace AgentPress\Rest;

/**
 * Limits WebMCP transport traffic by route bucket and signed-in user or direct address.
 */
final class RequestRateLimiter {
	/** Cache group. */
	private const CACHE_GROUP = 'agentpress_webmcp_rate';

	/** Window length in seconds. */
	private const WINDOW_SECONDS = 60;

	/**
	 * Check and increment a request bucket.
	 *
	 * @param string $bucket        Route bucket.
	 * @param string $client_key    User or direct client key.
	 * @param int    $default_limit Default requests per minute.
	 * @return bool
	 */
	public function allow( $bucket, $client_key, $default_limit ) {
		/**
		 * Filter the maximum WebMCP transport requests per client and route.
		 *
		 * @param int    $limit  Default requests per minute.
		 * @param string $bucket Route bucket.
		 */
		$limit = (int) apply_filters( 'agentpress_webmcp_rate_limit', $default_limit, $bucket );
		if ( $limit < 1 ) {
			return false;
		}

		$key   = 'request_' . md5( $bucket . '|' . $client_key );
		$count = get_transient( self::CACHE_GROUP . '_' . $key );

		if ( false === $count ) {
			set_transient( self::CACHE_GROUP . '_' . $key, 1, self::WINDOW_SECONDS );
			return true;
		}

		if ( (int) $count >= $limit ) {
			return false;
		}

		set_transient( self::CACHE_GROUP . '_' . $key, (int) $count + 1, self::WINDOW_SECONDS );
		return true;
	}

	/**
	 * Return the bounded retry delay advertised to clients.
	 *
	 * @return int
	 */
	public static function retry_after() {
		return self::WINDOW_SECONDS;
	}
}
