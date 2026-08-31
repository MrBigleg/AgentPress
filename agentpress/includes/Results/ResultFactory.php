<?php
/**
 * Successful AgentPress result envelopes.
 *
 * @package AgentPress
 */

namespace AgentPress\Results;

/**
 * Creates the one successful v0.1 envelope shape.
 */
final class ResultFactory {
	/**
	 * Create a successful result.
	 *
	 * @param array<string, mixed> $data       Validated result data.
	 * @param string|null          $request_id Optional UUIDv4.
	 * @return array<string, mixed>
	 */
	public static function success( $data, $request_id = null ) {
		if ( ! is_string( $request_id ) || 1 !== preg_match( '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $request_id ) ) {
			$request_id = wp_generate_uuid4();
		}

		return array(
			'ok'         => true,
			'request_id' => strtolower( $request_id ),
			'data'       => $data,
		);
	}
}
