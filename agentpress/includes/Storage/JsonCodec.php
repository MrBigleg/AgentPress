<?php
/**
 * Bounded JSON persistence codec.
 *
 * @package AgentPress
 */

namespace AgentPress\Storage;

/**
 * Encodes and decodes repository JSON without accepting unbounded documents.
 */
final class JsonCodec {
	/** Largest JSON document accepted by v0.1 storage. */
	public const MAX_BYTES = 307200;

	/**
	 * Encode one bounded object-like array.
	 *
	 * @param array<string, mixed> $value JSON value.
	 * @return string
	 * @throws \InvalidArgumentException When JSON encoding fails.
	 * @throws \LengthException When encoded JSON exceeds the bound.
	 */
	public function encode( $value ) {
		$json = wp_json_encode( $value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
		if ( false === $json ) {
			throw new \InvalidArgumentException( 'AgentPress could not encode a JSON field.' );
		}

		if ( strlen( $json ) > self::MAX_BYTES ) {
			throw new \LengthException( 'AgentPress JSON exceeds the storage limit.' );
		}

		return $json;
	}

	/**
	 * Decode one bounded object-like array.
	 *
	 * @param string $json Stored JSON.
	 * @return array<string, mixed>
	 * @throws \UnexpectedValueException When stored JSON is oversized or invalid.
	 */
	public function decode( $json ) {
		if ( strlen( $json ) > self::MAX_BYTES ) {
			throw new \UnexpectedValueException( 'Stored AgentPress JSON exceeds the limit.' );
		}

		try {
			$value = json_decode( $json, true, 512, JSON_THROW_ON_ERROR );
		} catch ( \JsonException ) {
			throw new \UnexpectedValueException( 'Stored AgentPress JSON is invalid.' );
		}

		if ( ! is_array( $value ) ) {
			throw new \UnexpectedValueException( 'Stored AgentPress JSON must be an object.' );
		}

		return $value;
	}
}
