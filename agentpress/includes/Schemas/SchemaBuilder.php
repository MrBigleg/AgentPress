<?php
/**
 * Shared AgentPress JSON Schema fragments.
 *
 * @package AgentPress
 */

namespace AgentPress\Schemas;

/**
 * Builds inline closed schemas supported by WordPress core and WebMCP.
 */
final class SchemaBuilder {
	/** Common persistent-write key pattern. */
	public const IDEMPOTENCY_PATTERN = '^[A-Za-z0-9._:-]{8,64}$';

	/**
	 * Build a closed object.
	 *
	 * @param array<string, array<string, mixed>> $properties Properties.
	 * @param array<int, string>                  $required   Required names.
	 * @return array<string, mixed>
	 */
	public static function closed_object( $properties = array(), $required = array() ) {
		return array(
			'type'                 => 'object',
			'properties'           => empty( $properties ) ? new \stdClass() : $properties,
			'required'             => $required,
			'additionalProperties' => false,
		);
	}

	/**
	 * Build a positive integer schema.
	 *
	 * @return array<string, mixed>
	 */
	public static function positive_integer() {
		return array(
			'type'    => 'integer',
			'minimum' => 1,
		);
	}

	/**
	 * Build a non-negative integer schema.
	 *
	 * @return array<string, mixed>
	 */
	public static function non_negative_integer() {
		return array(
			'type'    => 'integer',
			'minimum' => 0,
		);
	}

	/**
	 * Build a bounded string.
	 *
	 * @param int         $minimum Minimum characters.
	 * @param int         $maximum Maximum characters.
	 * @param string|null $pattern Optional pattern.
	 * @return array<string, mixed>
	 */
	public static function string( $minimum, $maximum, $pattern = null ) {
		$schema = array(
			'type'      => 'string',
			'minLength' => $minimum,
			'maxLength' => $maximum,
		);
		if ( null !== $pattern ) {
			$schema['pattern'] = $pattern;
		}
		return $schema;
	}

	/**
	 * Build an enum schema.
	 *
	 * @param array<int, string|bool> $values Values.
	 * @return array<string, mixed>
	 * @throws \InvalidArgumentException When values are empty or mix supported types.
	 */
	public static function enum( $values ) {
		if ( empty( $values ) ) {
			throw new \InvalidArgumentException( 'Enum values must not be empty.' );
		}

		$type = is_bool( reset( $values ) ) ? 'boolean' : 'string';
		foreach ( $values as $value ) {
			if ( ( 'boolean' === $type && ! is_bool( $value ) ) || ( 'string' === $type && ! is_string( $value ) ) ) {
				throw new \InvalidArgumentException( 'Enum values must share one supported type.' );
			}
		}

		return array(
			'type' => $type,
			'enum' => array_values( $values ),
		);
	}

	/**
	 * Build the persistent-write idempotency-key schema.
	 *
	 * @return array<string, mixed>
	 */
	public static function idempotency_key() {
		return self::string( 8, 64, self::IDEMPOTENCY_PATTERN );
	}

	/**
	 * Build unique positive integer IDs.
	 *
	 * @param int $maximum Maximum items.
	 * @return array<string, mixed>
	 */
	public static function positive_ids( $maximum ) {
		return array(
			'type'        => 'array',
			'items'       => self::positive_integer(),
			'minItems'    => 1,
			'maxItems'    => $maximum,
			'uniqueItems' => true,
		);
	}

	/**
	 * Build the common success output envelope.
	 *
	 * @param array<string, mixed> $data_schema Exact data schema.
	 * @return array<string, mixed>
	 */
	public static function success_envelope( $data_schema ) {
		return self::closed_object(
			array(
				'ok'         => array(
					'type' => 'boolean',
					'enum' => array( true ),
				),
				'request_id' => array(
					'type'   => 'string',
					'format' => 'uuid',
				),
				'data'       => $data_schema,
			),
			array( 'ok', 'request_id', 'data' )
		);
	}
}
