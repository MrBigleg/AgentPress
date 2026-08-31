<?php
/**
 * Cross-field rules that JSON Schema alone does not express clearly.
 *
 * @package AgentPress
 */

namespace AgentPress\Schemas;

/**
 * Provides deterministic, side-effect-free combination callbacks.
 */
final class CombinationRules {
	/**
	 * Require at least one named property.
	 *
	 * @param array<int, string> $fields Candidate fields.
	 * @return callable
	 */
	public static function at_least_one( $fields ) {
		return static function ( $input ) use ( $fields ) {
			return ! empty( array_intersect( $fields, array_keys( $input ) ) );
		};
	}

	/**
	 * Enforce stage-navigation item fields for add/remove/move.
	 *
	 * @return callable
	 */
	public static function navigation_operation() {
		return static function ( $input ) {
			if ( ! isset( $input['operation'], $input['item'] ) || ! is_array( $input['item'] ) ) {
				return false;
			}

			$item = $input['item'];
			if ( 'remove' === $input['operation'] ) {
				return isset( $item['item_id'] ) && 1 === count( $item );
			}
			if ( 'move' === $input['operation'] ) {
				return isset( $item['item_id'], $item['position'] ) && ! isset( $item['object_type'], $item['object_id'], $item['url'], $item['label'] );
			}
			if ( 'add' !== $input['operation'] || ! isset( $item['object_type'], $item['position'] ) || isset( $item['item_id'] ) ) {
				return false;
			}
			if ( 'custom' === $item['object_type'] ) {
				return isset( $item['url'], $item['label'] ) && ! isset( $item['object_id'] );
			}

			return in_array( $item['object_type'], array( 'post', 'page' ), true ) && isset( $item['object_id'] ) && ! isset( $item['url'] );
		};
	}
}
