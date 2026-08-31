<?php
/**
 * AgentPress schema validation boundary.
 *
 * @package AgentPress
 */

namespace AgentPress\Schemas;

use AgentPress\Errors\ErrorFactory;

/**
 * Wraps WordPress validation and explicit cross-field rules.
 */
final class SchemaValidator {
	/**
	 * Validate a value and optional combination rules.
	 *
	 * @param mixed                            $value  Value.
	 * @param array<string, mixed>             $schema Schema.
	 * @param array<int, callable(array):bool> $rules Cross-field rules.
	 * @return true|\WP_Error
	 */
	public function validate( $value, $schema, $rules = array() ) {
		return $this->validate_input( $value, $schema, $rules );
	}

	/**
	 * Validate untrusted Ability input.
	 *
	 * @param mixed                            $value  Value.
	 * @param array<string, mixed>             $schema Schema.
	 * @param array<int, callable(array):bool> $rules Cross-field rules.
	 * @return true|\WP_Error
	 */
	public function validate_input( $value, $schema, $rules = array() ) {
		return $this->validate_value( $value, $schema, $rules, 'input', 'AP_SCHEMA_INVALID' );
	}

	/**
	 * Validate trusted Ability output before it crosses the bridge.
	 *
	 * @param mixed                            $value  Value.
	 * @param array<string, mixed>             $schema Schema.
	 * @param array<int, callable(array):bool> $rules Cross-field rules.
	 * @return true|\WP_Error
	 */
	public function validate_output( $value, $schema, $rules = array() ) {
		return $this->validate_value( $value, $schema, $rules, 'output', 'AP_INTERNAL_ERROR' );
	}

	/**
	 * Run core schema validation followed by named combination rules.
	 *
	 * @param mixed                            $value      Value.
	 * @param array<string, mixed>             $schema     Schema.
	 * @param array<int, callable(array):bool> $rules      Cross-field rules.
	 * @param string                           $path       Validation path.
	 * @param string                           $error_code Stable error code.
	 * @return true|\WP_Error
	 */
	private function validate_value( $value, $schema, $rules, $path, $error_code ) {
		if ( ! $this->has_strict_types( $value, $schema ) ) {
			return ErrorFactory::make( $error_code );
		}

		$result = rest_validate_value_from_schema( $value, $schema, $path );
		if ( is_wp_error( $result ) ) {
			return ErrorFactory::make( $error_code );
		}

		foreach ( $rules as $rule ) {
			if ( ! is_array( $value ) || true !== call_user_func( $rule, $value ) ) {
				return ErrorFactory::make( $error_code );
			}
		}

		return true;
	}

	/**
	 * Reject PHP values that core's REST validator would coerce across JSON types.
	 *
	 * @param mixed                $value  Value.
	 * @param array<string, mixed> $schema Schema.
	 * @return bool
	 */
	private function has_strict_types( $value, $schema ) {
		$type = isset( $schema['type'] ) ? $schema['type'] : null;
		if ( is_array( $type ) ) {
			foreach ( $type as $candidate ) {
				$candidate_schema         = $schema;
				$candidate_schema['type'] = $candidate;
				if ( $this->has_strict_types( $value, $candidate_schema ) ) {
					return true;
				}
			}
			return false;
		}

		$matches = array(
			'object'  => is_array( $value ) && ( empty( $value ) || ! $this->is_list( $value ) ),
			'array'   => is_array( $value ) && ( empty( $value ) || $this->is_list( $value ) ),
			'string'  => is_string( $value ),
			'number'  => is_int( $value ) || is_float( $value ),
			'integer' => is_int( $value ),
			'boolean' => is_bool( $value ),
			'null'    => null === $value,
		);
		if ( ! is_string( $type ) || ! isset( $matches[ $type ] ) || ! $matches[ $type ] ) {
			return false;
		}
		if ( isset( $schema['enum'] ) && ! in_array( $value, $schema['enum'], true ) ) {
			return false;
		}

		if ( 'object' === $type && isset( $schema['properties'] ) && is_array( $schema['properties'] ) ) {
			foreach ( $schema['properties'] as $name => $property_schema ) {
				if ( array_key_exists( $name, $value ) && ! $this->has_strict_types( $value[ $name ], $property_schema ) ) {
					return false;
				}
			}
		}
		if ( 'array' === $type && isset( $schema['items'] ) && is_array( $schema['items'] ) ) {
			foreach ( $value as $item ) {
				if ( ! $this->has_strict_types( $item, $schema['items'] ) ) {
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Identify a zero-based sequential PHP array without requiring PHP 8.1.
	 *
	 * @param array<mixed> $value Array.
	 * @return bool
	 */
	private function is_list( $value ) {
		return empty( $value ) || array_keys( $value ) === range( 0, count( $value ) - 1 );
	}
}
