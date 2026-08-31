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
		return array(
			'ok'         => true,
			'request_id' => $request_id ?? wp_generate_uuid4(),
			'data'       => $data,
		);
	}
}
