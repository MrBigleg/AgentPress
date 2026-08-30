<?php
/**
 * Bounded JSON codec tests.
 *
 * @package AgentPress
 */

use AgentPress\Storage\JsonCodec;
use PHPUnit\Framework\TestCase;

final class JsonCodecTest extends TestCase {
	public function test_round_trips_nested_unicode_data() {
		$codec = new JsonCodec();
		$value = array( 'title' => 'หลัง', 'nested' => array( 'safe' => true ) );

		$this->assertSame( $value, $codec->decode( $codec->encode( $value ) ) );
	}

	public function test_rejects_oversized_json() {
		$codec = new JsonCodec();

		$this->expectException( LengthException::class );
		$codec->encode( array( 'payload' => str_repeat( 'x', JsonCodec::MAX_BYTES ) ) );
	}

	public function test_rejects_invalid_stored_json() {
		$codec = new JsonCodec();

		$this->expectException( UnexpectedValueException::class );
		$codec->decode( '{broken' );
	}
}
