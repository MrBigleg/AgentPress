<?php
/**
 * AP-009 sanitizer and logger unit controls.
 *
 * @package AgentPress
 */

use AgentPress\Audit\ArgumentSanitizer;
use AgentPress\Audit\AuditLogger;
use PHPUnit\Framework\TestCase;

/**
 * Verifies recursive no-secret and bounded audit contracts.
 */
final class AuditSanitizerTest extends TestCase {
	/**
	 * Secret-like fields are removed recursively while safe semantics remain.
	 */
	public function test_recursive_secret_fields_are_removed() {
		$sanitizer = new ArgumentSanitizer();
		$safe      = $sanitizer->sanitize(
			array(
				'title'   => 'Services',
				'cookie'  => 'cookie-secret',
				'nested'  => array(
					'X-WP-Nonce'  => 'nonce-secret',
					'password'    => 'password-secret',
					'safe_number' => 17,
				),
				'headers' => array( 'Authorization' => 'Bearer header-secret' ),
				'user_metadata' => array( 'private' => 'metadata-secret' ),
				'idempotency_key' => 'idempotency-secret',
				'source_session'  => 'session-secret',
			)
		);

		$encoded = wp_json_encode( $safe );
		$this->assertSame( 'Services', $safe['title'] );
		$this->assertSame( 17, $safe['nested']['safe_number'] );
		foreach ( array( 'cookie-secret', 'nonce-secret', 'password-secret', 'header-secret', 'metadata-secret', 'idempotency-secret', 'session-secret' ) as $secret ) {
			$this->assertStringNotContainsString( $secret, $encoded );
		}
	}

	/**
	 * Large content fields become exact byte/hash/bounded escaped summaries.
	 */
	public function test_content_is_summarized() {
		$content   = '<script>' . str_repeat( 'x', 204800 - 17 ) . '</script>';
		$sanitizer = new ArgumentSanitizer();
		$safe      = $sanitizer->sanitize( array( 'content' => $content ) );
		$summary   = $safe['content'];

		$this->assertSame( strlen( $content ), $summary['bytes'] );
		$this->assertSame( hash( 'sha256', $content ), $summary['sha256'] );
		$this->assertLessThanOrEqual( ArgumentSanitizer::CONTENT_PREVIEW_CHARS, strlen( $summary['preview'] ) );
		$this->assertStringStartsWith( '&lt;script&gt;', $summary['preview'] );
		$this->assertStringNotContainsString( $content, wp_json_encode( $safe ) );
	}

	/**
	 * Ordinary strings, depth, item counts, and final JSON remain bounded.
	 */
	public function test_non_content_payloads_are_bounded() {
		$arguments = array(
			'ordinary' => str_repeat( 'z', 10000 ),
			'many'     => range( 1, 100 ),
			'deep'     => array( array( array( array( array( array( array( 'hidden' ) ) ) ) ) ) ),
		);
		$safe      = ( new ArgumentSanitizer() )->sanitize( $arguments );
		$encoded   = wp_json_encode( $safe );

		$this->assertLessThanOrEqual( ArgumentSanitizer::STRING_CHARS, strlen( $safe['ordinary'] ) );
		$this->assertCount( ArgumentSanitizer::ITEMS_PER_LEVEL + 1, $safe['many'] );
		$this->assertLessThanOrEqual( ArgumentSanitizer::MAX_ENCODED_BYTES, strlen( $encoded ) );
		$this->assertStringContainsString( '_truncated', $encoded );
	}

	/**
	 * Logger validates metadata and sends only sanitized repository values.
	 */
	public function test_logger_persists_only_sanitized_values() {
		$repository = new class() {
			/** @var array<string, mixed> */
			public $record;

			/** @param array<string, mixed> $record Record. @return int */
			public function create( $record ) {
				$this->record = $record;
				return 7;
			}
		};
		$logger     = new AuditLogger( $repository );
		$id         = $logger->record(
			array(
				'request_id' => '00000000-0000-4000-8000-000000000012',
				'actor_type' => 'human',
				'user_id'    => 9,
				'ability'    => 'AgentPress/Approve',
				'result'     => 'REPLAYED',
				'error_code' => 'AP_STATE_CONFLICT',
				'arguments'  => array( 'token' => 'never-store', 'change_id' => 4 ),
				'duration_ms' => -8,
			)
		);

		$this->assertSame( 7, $id );
		$this->assertSame( 'agentpress/approve', $repository->record['ability'] );
		$this->assertSame( 'AP_STATE_CONFLICT', $repository->record['error_code'] );
		$this->assertSame( 0, $repository->record['duration_ms'] );
		$this->assertSame( array( 'change_id' => 4 ), $repository->record['arguments_sanitized'] );
	}

	/**
	 * Invalid actors, results, and UUIDs fail before repository persistence.
	 *
	 * @dataProvider invalid_event_provider
	 * @param array<string, mixed> $changes Invalid event changes.
	 */
	public function test_logger_rejects_invalid_metadata( $changes ) {
		$repository = new class() {
			/** @param array<string, mixed> $record Record. @return int */
			public function create( $record ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
				return 1;
			}
		};
		$event      = array_merge(
			array(
				'request_id' => '00000000-0000-4000-8000-000000000012',
				'actor_type' => 'webmcp',
				'user_id'    => 1,
				'ability'    => 'agentpress/get-context',
				'result'     => 'SUCCESS',
				'arguments'  => array(),
			),
			$changes
		);

		$this->expectException( InvalidArgumentException::class );
		( new AuditLogger( $repository ) )->record( $event );
	}

	/**
	 * Return invalid audit metadata fixtures.
	 *
	 * @return array<string, array<int, array<string, string>>>
	 */
	public function invalid_event_provider() {
		return array(
			'uuid'   => array( array( 'request_id' => 'not-a-uuid' ) ),
			'actor'  => array( array( 'actor_type' => 'agent' ) ),
			'result' => array( array( 'result' => 'UNKNOWN' ) ),
		);
	}
}
