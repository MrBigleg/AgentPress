<?php
/**
 * Common schema and success-result unit tests.
 *
 * @package AgentPress
 */

use AgentPress\Results\ResultFactory;
use AgentPress\Schemas\SchemaBuilder;
use PHPUnit\Framework\TestCase;

final class SchemaResultTest extends TestCase {
	public function test_closed_schema_and_common_fragments_are_exact() {
		$schema = SchemaBuilder::closed_object(
			array(
				'id'  => SchemaBuilder::positive_integer(),
				'key' => SchemaBuilder::idempotency_key(),
			),
			array( 'id', 'key' )
		);

		$this->assertFalse( $schema['additionalProperties'] );
		$this->assertSame( array( 'id', 'key' ), $schema['required'] );
		$this->assertSame( 1, $schema['properties']['id']['minimum'] );
		$this->assertSame( SchemaBuilder::IDEMPOTENCY_PATTERN, $schema['properties']['key']['pattern'] );
		$this->assertSame( 'string', SchemaBuilder::enum( array( 'post', 'page' ) )['type'] );
	}

	public function test_enum_rejects_empty_or_mixed_values() {
		foreach ( array( array(), array( 'post', true ) ) as $values ) {
			try {
				SchemaBuilder::enum( $values );
				$this->fail( 'Invalid enum values were accepted.' );
			} catch ( InvalidArgumentException $exception ) {
				$this->assertNotSame( '', $exception->getMessage() );
			}
		}
	}

	public function test_success_factory_returns_exact_envelope() {
		$request_id = '00000000-0000-4000-8000-000000000012';
		$result     = ResultFactory::success( array( 'id' => 7 ), $request_id );

		$this->assertSame( array( 'ok', 'request_id', 'data' ), array_keys( $result ) );
		$this->assertTrue( $result['ok'] );
		$this->assertSame( $request_id, $result['request_id'] );
		$this->assertSame( array( 'id' => 7 ), $result['data'] );
	}

	public function test_success_factory_replaces_invalid_request_id() {
		$result = ResultFactory::success( array(), 'not-a-uuid' );

		$this->assertSame( '00000000-0000-4000-8000-000000000006', $result['request_id'] );
	}
}
