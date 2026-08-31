<?php
/**
 * Bounded audit argument sanitization.
 *
 * @package AgentPress
 */

namespace AgentPress\Audit;

/**
 * Removes secret-bearing fields and summarizes large semantic content.
 */
final class ArgumentSanitizer {
	/** Maximum escaped preview characters for content fields. */
	public const CONTENT_PREVIEW_CHARS = 200;

	/** Maximum retained characters for an ordinary string. */
	public const STRING_CHARS = 256;

	/** Maximum retained array items at one level. */
	public const ITEMS_PER_LEVEL = 50;

	/** Maximum retained nesting depth. */
	public const MAX_DEPTH = 6;

	/** Maximum final encoded bytes. */
	public const MAX_ENCODED_BYTES = 16384;

	/**
	 * Recursively sanitize one argument object.
	 *
	 * @param array<string|int, mixed> $arguments Raw Ability arguments.
	 * @return array<string|int, mixed>
	 */
	public function sanitize( $arguments ) {
		$safe = $this->sanitize_array( $arguments, 0 );
		$json = wp_json_encode( $safe );

		if ( is_string( $json ) && strlen( $json ) <= self::MAX_ENCODED_BYTES ) {
			return $safe;
		}

		return array(
			'_truncated' => true,
			'bytes'      => is_string( $json ) ? strlen( $json ) : 0,
			'sha256'     => is_string( $json ) ? hash( 'sha256', $json ) : hash( 'sha256', '' ),
		);
	}

	/**
	 * Sanitize one nested array.
	 *
	 * @param array<string|int, mixed> $values Candidate values.
	 * @param int                      $depth  Current depth.
	 * @return array<string|int, mixed>
	 */
	private function sanitize_array( $values, $depth ) {
		if ( $depth >= self::MAX_DEPTH ) {
			return array( '_truncated' => true );
		}

		$safe  = array();
		$count = 0;
		foreach ( $values as $key => $value ) {
			if ( $count >= self::ITEMS_PER_LEVEL ) {
				$safe['_truncated'] = true;
				break;
			}

			$normalized_key = is_int( $key ) ? $key : substr( (string) $key, 0, 64 );
			if ( is_string( $normalized_key ) && $this->is_forbidden_key( $normalized_key ) ) {
				continue;
			}

			if ( is_string( $normalized_key ) && $this->is_content_key( $normalized_key ) ) {
				$safe[ $normalized_key ] = $this->content_summary( $value );
				++$count;
				continue;
			}

			$sanitized = $this->sanitize_value( $value, $depth + 1 );
			if ( null !== $sanitized || null === $value ) {
				$safe[ $normalized_key ] = $sanitized;
				++$count;
			}
		}

		return $safe;
	}

	/**
	 * Sanitize one non-keyed value.
	 *
	 * @param mixed $value Candidate value.
	 * @param int   $depth Current depth.
	 * @return mixed
	 */
	private function sanitize_value( $value, $depth ) {
		if ( is_array( $value ) ) {
			return $this->sanitize_array( $value, $depth );
		}
		if ( is_string( $value ) ) {
			return $this->character_slice( $value, self::STRING_CHARS );
		}
		if ( is_int( $value ) || is_bool( $value ) || null === $value ) {
			return $value;
		}
		if ( is_float( $value ) ) {
			return is_finite( $value ) ? $value : null;
		}

		return null;
	}

	/**
	 * Replace content with a bounded semantic summary.
	 *
	 * @param mixed $value Candidate content.
	 * @return array<string, mixed>
	 */
	private function content_summary( $value ) {
		if ( ! is_string( $value ) ) {
			$encoded = wp_json_encode( $value );
			$value   = is_string( $encoded ) ? $encoded : '';
		}

		$preview = esc_html( $this->character_slice( $value, self::CONTENT_PREVIEW_CHARS ) );

		return array(
			'bytes'   => strlen( $value ),
			'sha256'  => hash( 'sha256', $value ),
			'preview' => $this->character_slice( $preview, self::CONTENT_PREVIEW_CHARS ),
		);
	}

	/**
	 * Identify credentials, headers, nonces, or arbitrary user metadata.
	 *
	 * @param string $key Candidate key.
	 * @return bool
	 */
	private function is_forbidden_key( $key ) {
		return 1 === preg_match(
			'/(?:authorization|cookie|set[_-]?cookie|nonce|password|passwd|application[_-]?password|secret|token|api[_-]?key|credential|session[_-]?id|source[_-]?session|idempotency[_-]?key|database|db[_-]?(?:name|user|pass|host)|headers?|user[_-]?meta(?:data)?|metadata)/i',
			$key
		);
	}

	/**
	 * Identify the three unbounded semantic payload fields.
	 *
	 * @param string $key Candidate key.
	 * @return bool
	 */
	private function is_content_key( $key ) {
		return in_array( strtolower( $key ), array( 'content', 'before_json', 'after_json' ), true );
	}

	/**
	 * Return a UTF-8-safe character prefix when mbstring is available.
	 *
	 * @param string $value Candidate string.
	 * @param int    $length Character ceiling.
	 * @return string
	 */
	private function character_slice( $value, $length ) {
		if ( function_exists( 'mb_substr' ) ) {
			return mb_substr( $value, 0, $length, 'UTF-8' );
		}

		return substr( $value, 0, $length );
	}
}
