<?php
/**
 * AP-009 sanitized audit runtime matrix.
 *
 * @package AgentPress
 */

use AgentPress\Audit\AuditEventRepository;
use AgentPress\Audit\ArgumentSanitizer;
use AgentPress\Audit\AuditLogger;
use AgentPress\Rest\WebMCPRoutes;

if ( ! function_exists( 'agentpress_ap009_assert' ) ) {
	/**
	 * Stop on one failed runtime assertion.
	 *
	 * @param bool   $condition Assertion condition.
	 * @param string $message   Failure message.
	 * @return void
	 */
	function agentpress_ap009_assert( $condition, $message ) {
		if ( ! $condition ) {
			fwrite( STDERR, "AP-009 assertion failed: {$message}\n" );
			exit( 1 );
		}
	}
}

/**
 * Build one execution request.
 *
 * @param string               $ability Ability name.
 * @param array<string, mixed> $input   Ability input.
 * @param string               $nonce   REST nonce.
 * @return WP_REST_Request
 */
function agentpress_ap009_request( $ability, $input, $nonce ) {
	$request = new WP_REST_Request( 'POST', '/agentpress/v1/webmcp/execute' );
	$request->set_header( 'Content-Type', 'application/json' );
	$request->set_header( 'X-WP-Nonce', $nonce );
	$request->set_header( 'Origin', home_url( '/' ) );
	$request->set_header( 'Sec-Fetch-Site', 'same-origin' );
	$request->set_body( wp_json_encode( array( 'ability' => $ability, 'input' => $input ) ) );
	return $request;
}

/**
 * Authorize and execute exactly as the REST server callback sequence does.
 *
 * @param WebMCPRoutes         $transport Transport instance.
 * @param string               $ability   Ability name.
 * @param array<string, mixed> $input     Ability input.
 * @param string               $nonce     REST nonce.
 * @return mixed
 */
function agentpress_ap009_execute( $transport, $ability, $input, $nonce ) {
	$request       = agentpress_ap009_request( $ability, $input, $nonce );
	$authorization = $transport->authorize_execute( $request );
	if ( true !== $authorization ) {
		return $authorization;
	}
	return $transport->execute( $request );
}

/**
 * Read all audit rows in ID order.
 *
 * @param wpdb $wpdb Database adapter.
 * @return array<int, array<string, mixed>>
 */
function agentpress_ap009_rows( $wpdb ) {
	$table = $wpdb->prefix . 'agentpress_audit_events';
	$query = $wpdb->prepare( 'SELECT * FROM %i ORDER BY id ASC', $table );
	return $wpdb->get_results( $query, ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
}

global $wpdb;

$table = $wpdb->prefix . 'agentpress_audit_events';
$wpdb->query( $wpdb->prepare( 'DELETE FROM %i', $table ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

$administrators = get_users( array( 'role' => 'administrator', 'number' => 1 ) );
agentpress_ap009_assert( ! empty( $administrators ), 'Administrator fixture is missing.' );
wp_set_current_user( $administrators[0]->ID );
$nonce = wp_create_nonce( 'wp_rest' );

$mode = 'success';
$ability = new class( $mode ) {
	/** @var string */
	public $mode;

	/** @param string $mode Execution mode. */
	public function __construct( &$mode ) {
		$this->mode =& $mode;
	}

	/** @param array<string, mixed> $input Input. @return bool */
	public function validate_input( $input ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
		return true;
	}

	/** @param array<string, mixed> $input Input. @return bool */
	public function check_permissions( $input ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
		return 'denied' !== $this->mode;
	}

	/** @param array<string, mixed> $input Input. @return array<string, mixed>|WP_Error */
	public function execute( $input ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
		if ( 'failed' === $this->mode ) {
			return new WP_Error( 'AP_INTERNAL_ERROR', 'synthetic private failure' );
		}
		return array(
			'ok'         => true,
			'request_id' => '00000000-0000-4000-8000-000000000099',
			'data'       => array( 'accepted' => true ),
		);
	}
};

$logger    = new AuditLogger();
$transport = new WebMCPRoutes(
	null,
	null,
	static function () use ( $ability ) {
		return $ability;
	},
	null,
	$logger
);

$secret_values = array(
	'cookie-secret-ap009',
	'nonce-secret-ap009',
	'password-secret-ap009',
	'header-secret-ap009',
	'metadata-secret-ap009',
	'database-secret-ap009',
);
$content       = '<script>' . str_repeat( 'A', 204783 ) . '</script>';
$secret_input  = array(
	'post_type'       => 'post',
	'title'           => 'Bounded audit fixture',
	'content'         => $content,
	'cookie'          => $secret_values[0],
	'X-WP-Nonce'      => $secret_values[1],
	'password_value'  => $secret_values[2],
	'headers'         => array( 'Authorization' => 'Bearer ' . $secret_values[3] ),
	'user_metadata'   => array( 'private' => $secret_values[4] ),
	'database_password' => $secret_values[5],
	'idempotency_key' => 'idempotency-secret-ap009',
);

$success = agentpress_ap009_execute( $transport, 'agentpress/create-draft', $secret_input, $nonce );
agentpress_ap009_assert( $success instanceof WP_REST_Response && 200 === $success->get_status(), 'Sanitized success request failed.' );
$success_data = $success->get_data();

$mode   = 'denied';
$denied = agentpress_ap009_execute( $transport, 'agentpress/create-draft', array( 'title' => 'Denied' ), $nonce );
agentpress_ap009_assert( is_wp_error( $denied ) && 'AP_PERMISSION_DENIED' === $denied->get_error_code(), 'Authenticated denial did not fail closed.' );

$mode   = 'failed';
$failed = agentpress_ap009_execute( $transport, 'agentpress/create-draft', array( 'title' => 'Failure' ), $nonce );
agentpress_ap009_assert( is_wp_error( $failed ) && 'AP_INTERNAL_ERROR' === $failed->get_error_code(), 'Authenticated failure did not normalize.' );

$replay_request_id = $logger->request_id();
$logger->record(
	array(
		'request_id' => $replay_request_id,
		'actor_type' => 'human',
		'user_id'    => $administrators[0]->ID,
		'ability'    => 'agentpress/approve-change',
		'result'     => 'REPLAYED',
		'arguments'  => array( 'change_id' => 71, 'application_password' => 'replay-secret-ap009' ),
		'change_id'  => 71,
	)
);

$rows = agentpress_ap009_rows( $wpdb );
agentpress_ap009_assert( 4 === count( $rows ), 'Expected success, denial, failure, and replay rows.' );
agentpress_ap009_assert( array( 'SUCCESS', 'DENIED', 'FAILED', 'REPLAYED' ) === array_column( $rows, 'result' ), 'Audit result sequence mismatch.' );
agentpress_ap009_assert( 'AP_PERMISSION_DENIED' === $rows[1]['error_code'] && 'AP_INTERNAL_ERROR' === $rows[2]['error_code'], 'Canonical audit error codes changed case or value.' );
agentpress_ap009_assert( $success_data['request_id'] === $rows[0]['request_id'], 'Success response and audit request IDs diverged.' );
agentpress_ap009_assert( 4 === count( array_unique( array_column( $rows, 'request_id' ) ) ), 'Audit request IDs collided.' );
agentpress_ap009_assert( 'webmcp' === $rows[0]['actor_type'] && 'human' === $rows[3]['actor_type'], 'Actor types were not normalized.' );
agentpress_ap009_assert( (int) $administrators[0]->ID === (int) $rows[0]['user_id'], 'Authenticated actor ID was not recorded.' );

$sanitized = json_decode( $rows[0]['arguments_sanitized'], true );
agentpress_ap009_assert( is_array( $sanitized ) && isset( $sanitized['content'] ), 'Sanitized arguments did not decode.' );
agentpress_ap009_assert( strlen( $rows[0]['arguments_sanitized'] ) <= ArgumentSanitizer::MAX_ENCODED_BYTES, 'Sanitized JSON exceeded its byte bound.' );
agentpress_ap009_assert( strlen( $sanitized['content']['preview'] ) <= ArgumentSanitizer::CONTENT_PREVIEW_CHARS, 'Content preview exceeded 200 characters.' );
agentpress_ap009_assert( strlen( $content ) === $sanitized['content']['bytes'], 'Content byte count mismatch.' );
agentpress_ap009_assert( hash( 'sha256', $content ) === $sanitized['content']['sha256'], 'Content hash mismatch.' );
agentpress_ap009_assert( false === strpos( $rows[0]['arguments_sanitized'], $content ), 'Full content reached durable storage.' );

$all_rows_json = wp_json_encode( $rows );
foreach ( array_merge( $secret_values, array( 'idempotency-secret-ap009', 'replay-secret-ap009' ) ) as $secret ) {
	agentpress_ap009_assert( false === strpos( $all_rows_json, $secret ), 'Secret reached durable storage: ' . $secret );
}

$row_count_before_invalid = count( $rows );
$invalid_nonce_request    = agentpress_ap009_request( 'agentpress/create-draft', array( 'title' => 'Invalid nonce' ), 'invalid-nonce' );
$invalid_nonce_result     = $transport->authorize_execute( $invalid_nonce_request );
agentpress_ap009_assert( is_wp_error( $invalid_nonce_result ) && 'AP_NONCE_INVALID' === $invalid_nonce_result->get_error_code(), 'Invalid nonce control did not fail in the guard.' );
agentpress_ap009_assert( $row_count_before_invalid === count( agentpress_ap009_rows( $wpdb ) ), 'Invalid nonce traffic created a durable row.' );

wp_set_current_user( 0 );
$logged_out_request = agentpress_ap009_request( 'agentpress/create-draft', array( 'title' => 'Logged out' ), $nonce );
$logged_out_result  = $transport->authorize_execute( $logged_out_request );
agentpress_ap009_assert( is_wp_error( $logged_out_result ) && 'AP_NOT_AUTHENTICATED' === $logged_out_result->get_error_code(), 'Logged-out control did not fail in the guard.' );
agentpress_ap009_assert( $row_count_before_invalid === count( agentpress_ap009_rows( $wpdb ) ), 'Logged-out traffic created a durable row.' );

wp_set_current_user( $administrators[0]->ID );
$execution_count = 0;
$denied_ability  = new class( $execution_count ) {
	/** @var int */
	private $execution_count;

	/** @param int $execution_count Counter. */
	public function __construct( &$execution_count ) {
		$this->execution_count =& $execution_count;
	}

	/** @param array<string, mixed> $input Input. @return bool */
	public function validate_input( $input ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
		return true;
	}

	/** @param array<string, mixed> $input Input. @return bool */
	public function check_permissions( $input ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
		return false;
	}

	/** @param array<string, mixed> $input Input. @return array<string, mixed> */
	public function execute( $input ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
		++$this->execution_count;
		return array( 'ok' => true, 'request_id' => wp_generate_uuid4(), 'data' => array() );
	}
};
$failing_repository = new class() {
	/** @param array<string, mixed> $record Record. @return int */
	public function create( $record ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
		throw new RuntimeException( 'Synthetic audit storage failure.' );
	}
};
$failing_transport = new WebMCPRoutes(
	null,
	null,
	static function () use ( $denied_ability ) {
		return $denied_ability;
	},
	null,
	new AuditLogger( $failing_repository )
);
$storage_failure = agentpress_ap009_execute( $failing_transport, 'agentpress/create-draft', array( 'title' => 'Denied storage failure' ), $nonce );
agentpress_ap009_assert( is_wp_error( $storage_failure ) && 'AP_INTERNAL_ERROR' === $storage_failure->get_error_code(), 'Audit storage failure did not fail closed.' );
agentpress_ap009_assert( 0 === $execution_count, 'Audit failure path executed an unauthorized Ability.' );
agentpress_ap009_assert( $row_count_before_invalid === count( agentpress_ap009_rows( $wpdb ) ), 'Synthetic storage failure changed durable audit state.' );

$wpdb->query( $wpdb->prepare( 'DELETE FROM %i', $table ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

echo wp_json_encode(
	array(
		'audit_rows'             => 4,
		'results'                => array( 'SUCCESS', 'DENIED', 'FAILED', 'REPLAYED' ),
		'secrets_absent'         => count( $secret_values ) + 2,
		'content_bytes'          => strlen( $content ),
		'sanitized_bytes'        => strlen( $rows[0]['arguments_sanitized'] ),
		'logged_out_rows'        => 0,
		'invalid_nonce_rows'     => 0,
		'unauthorized_mutations' => $execution_count,
	)
) . "\n";
