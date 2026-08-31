<?php
/**
 * Ability catalog unit controls.
 *
 * @package AgentPress
 */

use AgentPress\Abilities\AbilityCatalog;
use AgentPress\WebMCP\AbilityMap;
use PHPUnit\Framework\TestCase;

/**
 * Proves the static catalog stays closed and synchronized with the transport map.
 */
final class AbilityCatalogTest extends TestCase {
	/**
	 * Verify the exact catalog identity and common contract.
	 *
	 * @return void
	 */
	public function test_catalog_exactly_matches_fixed_map() {
		$catalog = AbilityCatalog::all();

		$this->assertCount( 15, $catalog );
		$this->assertSame( array_keys( AbilityMap::all() ), array_keys( $catalog ) );
		$this->assertSame( array( 'label', 'description' ), array_keys( AbilityCatalog::category() ) );

		foreach ( $catalog as $ability_name => $definition ) {
			$this->assertSame( array( 'label', 'description', 'input_schema', 'output_schema', 'meta' ), array_keys( $definition ), $ability_name );
			$this->assertNotSame( '', $definition['label'], $ability_name );
			$this->assertNotSame( '', $definition['description'], $ability_name );
			$this->assertFalse( $definition['meta']['show_in_rest'], $ability_name );
			$this->assertSame( 'object', $definition['input_schema']['type'], $ability_name );
			$this->assertFalse( $definition['input_schema']['additionalProperties'], $ability_name );
			$this->assertSame( array( 'ok', 'request_id', 'data' ), $definition['output_schema']['required'], $ability_name );
			$this->assertFalse( $definition['output_schema']['additionalProperties'], $ability_name );
			$this->assertSame( array( true ), $definition['output_schema']['properties']['ok']['enum'], $ability_name );
			$this->assertSame( array( 'readOnlyHint', 'untrustedContentHint' ), array_values( array_intersect( array_keys( $definition['meta']['annotations'] ), array( 'readOnlyHint', 'untrustedContentHint' ) ) ), $ability_name );
		}
	}

	/**
	 * Verify nested object schemas cannot accept unknown fields.
	 *
	 * @return void
	 */
	public function test_every_object_schema_is_closed() {
		foreach ( AbilityCatalog::all() as $ability_name => $definition ) {
			$this->assert_objects_closed( $definition['input_schema'], $ability_name . ' input' );
			$this->assert_objects_closed( $definition['output_schema'], $ability_name . ' output' );
		}
	}

	/**
	 * Recursively assert closure for each object schema.
	 *
	 * @param array<string, mixed> $schema Schema node.
	 * @param string               $path   Assertion path.
	 * @return void
	 */
	private function assert_objects_closed( $schema, $path ) {
		if ( isset( $schema['type'] ) && 'object' === $schema['type'] ) {
			$this->assertArrayHasKey( 'additionalProperties', $schema, $path );
			$this->assertFalse( $schema['additionalProperties'], $path );
		}
		foreach ( array( 'properties', 'items' ) as $child_key ) {
			if ( ! isset( $schema[ $child_key ] ) || ! is_array( $schema[ $child_key ] ) ) {
				continue;
			}
			if ( 'items' === $child_key ) {
				$this->assert_objects_closed( $schema[ $child_key ], $path . '.items' );
				continue;
			}
			foreach ( $schema[ $child_key ] as $name => $child ) {
				if ( is_array( $child ) ) {
					$this->assert_objects_closed( $child, $path . '.' . $name );
				}
			}
		}
	}
}
