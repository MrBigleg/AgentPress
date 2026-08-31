<?php
/**
 * AgentPress schema validation boundary.
 *
 * @package AgentPress
 */

namespace AgentPress\Schemas;

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
		$result = rest_validate_value_from_schema( $value, $schema, 'input' );
		if ( is_wp_error( $result ) ) {
			return $this->invalid();
		}

		foreach ( $rules as $rule ) {
			if ( ! is_array( $value ) || true !== call_user_func( $rule, $value ) ) {
				return $this->invalid();
			}
		}

		return true;
	}

	/**
	 * Create the stable invalid-schema error.
	 *
	 * @return \WP_Error
	 */
	private function invalid() {
		return new \WP_Error( 'AP_SCHEMA_INVALID', __( 'The request does not match the required schema.', 'agentpress' ), array( 'status' => 400 ) );
	}
}
