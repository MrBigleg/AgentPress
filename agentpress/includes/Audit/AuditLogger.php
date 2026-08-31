<?php
/**
 * Sanitized audit event writer.
 *
 * @package AgentPress
 */

namespace AgentPress\Audit;

/**
 * Validates event metadata and persists only sanitized arguments.
 */
final class AuditLogger {
	/** Allowed actor identities. */
	private const ACTORS = array( 'webmcp', 'human' );

	/** Allowed public activity results. */
	private const RESULTS = array( 'SUCCESS', 'DENIED', 'FAILED', 'PENDING', 'REJECTED', 'REPLAYED' );

	/**
	 * Audit repository.
	 *
	 * @var AuditEventRepository|object
	 */
	private $repository;

	/**
	 * Argument sanitizer.
	 *
	 * @var ArgumentSanitizer
	 */
	private $sanitizer;

	/**
	 * Constructor.
	 *
	 * @param AuditEventRepository|object|null $repository Optional repository-compatible writer.
	 * @param ArgumentSanitizer|null           $sanitizer  Optional sanitizer.
	 */
	public function __construct( $repository = null, $sanitizer = null ) {
		$this->repository = $repository ?? new AuditEventRepository();
		$this->sanitizer  = $sanitizer ?? new ArgumentSanitizer();
	}

	/**
	 * Generate one request correlation ID.
	 *
	 * @return string
	 */
	public function request_id() {
		return strtolower( wp_generate_uuid4() );
	}

	/**
	 * Persist one bounded event.
	 *
	 * @param array<string, mixed> $event Raw reviewed event metadata and arguments.
	 * @return int
	 * @throws \InvalidArgumentException When required metadata is invalid.
	 * @throws \RuntimeException When persistence fails.
	 */
	public function record( $event ) {
		foreach ( array( 'request_id', 'actor_type', 'user_id', 'ability', 'result', 'arguments' ) as $required ) {
			if ( ! array_key_exists( $required, $event ) ) {
				throw new \InvalidArgumentException( 'AgentPress audit metadata is incomplete.' );
			}
		}

		$request_id = strtolower( (string) $event['request_id'] );
		$actor_type = (string) $event['actor_type'];
		$result     = (string) $event['result'];
		if ( 1 !== preg_match( '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $request_id ) ) {
			throw new \InvalidArgumentException( 'AgentPress audit request ID is invalid.' );
		}
		if ( ! in_array( $actor_type, self::ACTORS, true ) || ! in_array( $result, self::RESULTS, true ) ) {
			throw new \InvalidArgumentException( 'AgentPress audit actor or result is invalid.' );
		}
		if ( ! is_array( $event['arguments'] ) ) {
			throw new \InvalidArgumentException( 'AgentPress audit arguments must be an array.' );
		}

		$record = array(
			'request_id'          => $request_id,
			'actor_type'          => $actor_type,
			'user_id'             => max( 0, (int) $event['user_id'] ),
			'change_set_id'       => isset( $event['change_set_id'] ) ? max( 0, (int) $event['change_set_id'] ) : 0,
			'change_id'           => isset( $event['change_id'] ) ? max( 0, (int) $event['change_id'] ) : 0,
			'ability'             => $this->bounded_identifier( $event['ability'], 100, 'agentpress/unknown' ),
			'object_type'         => isset( $event['object_type'] ) ? $this->bounded_identifier( $event['object_type'], 40, '' ) : '',
			'object_id'           => isset( $event['object_id'] ) ? max( 0, (int) $event['object_id'] ) : 0,
			'result'              => $result,
			'error_code'          => isset( $event['error_code'] ) ? $this->bounded_identifier( $event['error_code'], 64, '', false ) : '',
			'arguments_sanitized' => $this->sanitizer->sanitize( $event['arguments'] ),
			'duration_ms'         => isset( $event['duration_ms'] ) ? min( 4294967295, max( 0, (int) $event['duration_ms'] ) ) : 0,
		);

		if ( ! is_callable( array( $this->repository, 'create' ) ) ) {
			throw new \RuntimeException( 'AgentPress audit repository is unavailable.' );
		}

		return (int) $this->repository->create( $record );
	}

	/**
	 * Normalize a bounded non-secret identifier.
	 *
	 * @param mixed  $value    Candidate identifier.
	 * @param int    $max      Byte ceiling.
	 * @param string $fallback Empty fallback.
	 * @param bool   $lowercase Whether to lowercase the value.
	 * @return string
	 */
	private function bounded_identifier( $value, $max, $fallback, $lowercase = true ) {
		$value = is_string( $value ) ? $value : '';
		$value = $lowercase ? strtolower( $value ) : $value;
		$value = preg_replace( '/[^a-z0-9_\/-]/i', '', $value );
		$value = is_string( $value ) ? substr( $value, 0, $max ) : '';
		return '' === $value ? $fallback : $value;
	}
}
