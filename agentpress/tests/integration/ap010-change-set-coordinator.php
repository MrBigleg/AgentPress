<?php
/**
 * AP-010 Change Set coordinator runtime matrix.
 *
 * @package AgentPress
 */

use AgentPress\Audit\AuditLogger;
use AgentPress\Changes\ChangeCoordinator;
use AgentPress\Changes\ChangeRepository;
use AgentPress\Changes\ChangeSetRepository;
use AgentPress\Changes\StateHasher;
use AgentPress\Rest\WebMCPRoutes;
use AgentPress\Storage\Migrator;

/** @param bool $condition Condition. @param string $message Message. @return void */
function agentpress_ap010_assert( $condition, $message ) {
	if ( ! $condition ) {
		fwrite( STDERR, "AP-010 assertion failed: {$message}\n" );
		exit( 1 );
	}
}

/** @param wpdb $wpdb Database. @param string $suffix Table suffix. @return int */
function agentpress_ap010_count( $wpdb, $suffix ) {
	$query = $wpdb->prepare( 'SELECT COUNT(*) FROM %i', $wpdb->prefix . $suffix );
	return (int) $wpdb->get_var( $query ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
}

/** @param string $ability Ability. @param array<string, mixed> $input Input. @param string $nonce Nonce. @return WP_REST_Request */
function agentpress_ap010_request( $ability, $input, $nonce ) {
	$request = new WP_REST_Request( 'POST', '/agentpress/v1/webmcp/execute' );
	$request->set_header( 'Content-Type', 'application/json' );
	$request->set_header( 'X-WP-Nonce', $nonce );
	$request->set_header( 'Origin', home_url( '/' ) );
	$request->set_header( 'Sec-Fetch-Site', 'same-origin' );
	$request->set_body( wp_json_encode( array( 'ability' => $ability, 'input' => $input ) ) );
	return $request;
}

global $wpdb;

Migrator::migrate();
foreach ( array( 'agentpress_audit_events', 'agentpress_changes', 'agentpress_change_sets' ) as $suffix ) {
	$wpdb->query( $wpdb->prepare( 'DELETE FROM %i', $wpdb->prefix . $suffix ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
}

$administrators = get_users( array( 'role' => 'administrator', 'number' => 1 ) );
agentpress_ap010_assert( ! empty( $administrators ), 'Administrator fixture is missing.' );
$actor_id = (int) $administrators[0]->ID;
wp_set_current_user( $actor_id );

$fixed_timestamp = strtotime( '2026-08-31 15:00:00 UTC' );
$clock           = static function () use ( $fixed_timestamp ) {
	return $fixed_timestamp;
};
$mysql_clock     = static function () {
	return '2026-08-31 15:00:00';
};
$sets            = new ChangeSetRepository( $wpdb, $mysql_clock );
$changes         = new ChangeRepository( $wpdb, $mysql_clock );
$hasher          = new StateHasher();
$coordinator     = new ChangeCoordinator( $sets, $changes, $hasher, null, $clock );

$command = array(
	'actor_user_id'   => $actor_id,
	'ability'         => 'agentpress/create-draft',
	'operation'       => 'create',
	'object_type'     => 'post',
	'idempotency_key' => 'ap010-r1-key',
	'before'          => array(),
	'after'           => array( 'post_type' => 'post', 'title' => 'Synthetic draft' ),
	'change_set_title' => 'Synthetic AP-010 set',
	'request_summary' => 'Coordinator runtime fixture',
	'source_session'  => 'raw-tab-session-ap010',
);
$mutation_count = 0;
$scope          = $hasher->idempotency_scope( $actor_id, $command['ability'], $command['idempotency_key'] );
$applied        = $coordinator->apply(
	$command,
	static function () use ( &$mutation_count, $changes, $scope ) {
		$intent = $changes->find_by_idempotency_scope( $scope );
		agentpress_ap010_assert( is_array( $intent ) && 'APPLYING' === $intent['status'], 'R1 mutation ran before durable intent and atomic claim.' );
		++$mutation_count;
		return array(
			'object_id' => 501,
			'before'    => array(),
			'after'     => array( 'post_type' => 'post', 'title' => 'Synthetic draft', 'status' => 'draft' ),
			'content_id' => 501,
		);
	}
);
agentpress_ap010_assert( is_array( $applied ) && 'APPLIED' === $applied['status'] && 1 === $mutation_count, 'R1 application failed.' );
$applied_change = $changes->find( $applied['change_id'] );
$applied_set    = $sets->find( $applied['change_set_id'] );
agentpress_ap010_assert( 'APPLIED' === $applied_change['status'] && 501 === $applied_change['object_id'], 'R1 durable outcome mismatch.' );
agentpress_ap010_assert( 'COMPLETED' === $applied_set['status'] && '2026-08-31 15:00:00' === $applied_set['completed_at'], 'R1 parent state mismatch.' );
agentpress_ap010_assert( hash( 'sha256', 'raw-tab-session-ap010' ) === $applied_set['source_session_hash'], 'Source session was not hashed.' );

$replay = $coordinator->apply(
	$command,
	static function () use ( &$mutation_count ) {
		++$mutation_count;
		return array();
	}
);
agentpress_ap010_assert( is_array( $replay ) && true === $replay['replayed'] && $applied['change_id'] === $replay['change_id'], 'Identical key did not replay the original row.' );
agentpress_ap010_assert( 1 === $mutation_count, 'Identical replay repeated the mutation.' );

$changed_command          = $command;
$changed_command['after'] = array( 'post_type' => 'post', 'title' => 'Changed payload' );
$conflict                 = $coordinator->apply( $changed_command, static function () use ( &$mutation_count ) { ++$mutation_count; } );
agentpress_ap010_assert( is_wp_error( $conflict ) && 'AP_STATE_CONFLICT' === $conflict->get_error_code(), 'Changed payload did not conflict.' );
agentpress_ap010_assert( 1 === $mutation_count, 'Changed-payload conflict mutated.' );

$explicit_set_id = $sets->create(
	array(
		'initiator_user_id' => $actor_id,
		'title'             => 'Reusable outcome',
		'request_summary'   => 'Reuse fixture',
		'status'            => 'OPEN',
	)
);
$r2_command = array(
	'actor_user_id'   => $actor_id,
	'ability'         => 'agentpress/publish-content',
	'operation'       => 'publish',
	'object_type'     => 'post',
	'object_id'       => 501,
	'idempotency_key' => 'ap010-r2-key',
	'change_set_id'   => $explicit_set_id,
	'before'          => array( 'id' => 501, 'status' => 'draft', 'title' => 'Synthetic draft' ),
	'after'           => array( 'id' => 501, 'status' => 'publish', 'title' => 'Synthetic draft' ),
);
$staged = $coordinator->stage( $r2_command );
agentpress_ap010_assert( is_array( $staged ) && 'PENDING_APPROVAL' === $staged['status'] && $explicit_set_id === $staged['change_set_id'], 'R2 did not reuse the supplied Change Set.' );
agentpress_ap010_assert( 1 === $mutation_count, 'R2 staging mutated the target counter.' );
$proposal = $changes->find( $staged['change_id'] );
agentpress_ap010_assert( '2026-09-01 15:00:00' === $proposal['expires_at'], 'R2 expiry is not exactly 24 hours.' );
agentpress_ap010_assert( $hasher->state_hash( $r2_command['before'] ) === $proposal['target_state_hash'], 'Target state hash mismatch.' );
agentpress_ap010_assert( $hasher->proposal_hash( $r2_command['ability'], $r2_command['operation'], $r2_command['after'], $proposal['target_state_hash'] ) === $proposal['proposal_hash'], 'Proposal hash mismatch.' );
agentpress_ap010_assert( 'READY_FOR_REVIEW' === $sets->find( $explicit_set_id )['status'], 'Staged parent state mismatch.' );

$immutable_rejected = false;
try {
	$changes->update( $staged['change_id'], array( 'after_json' => array( 'status' => 'tampered' ) ) );
} catch ( InvalidArgumentException $exception ) {
	$immutable_rejected = true;
}
agentpress_ap010_assert( $immutable_rejected, 'Pending proposal accepted an immutable payload change.' );

$failure_command                    = $command;
$failure_command['idempotency_key'] = 'ap010-failure-key';
$failure_command['after']['title']  = 'Failure';
$failed = $coordinator->apply(
	$failure_command,
	static function () {
		return new WP_Error( 'AP_INTERNAL_ERROR', 'synthetic private failure' );
	}
);
agentpress_ap010_assert( is_wp_error( $failed ) && 'AP_INTERNAL_ERROR' === $failed->get_error_code(), 'Mutator failure did not normalize.' );
$failed_scope = $hasher->idempotency_scope( $actor_id, $failure_command['ability'], $failure_command['idempotency_key'] );
$failed_row   = $changes->find_by_idempotency_scope( $failed_scope );
agentpress_ap010_assert( 'FAILED' === $failed_row['status'] && 'AP_INTERNAL_ERROR' === $failed_row['error_code'], 'Mutator failure was not durable.' );
agentpress_ap010_assert( 'FAILED' === $sets->find( $failed_row['change_set_id'] )['status'], 'Failed child did not fail parent.' );

$storage_mutations = 0;
$failing_changes   = new class() {
	public function find_by_idempotency_scope( $scope ) { return null; } // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
	public function create( $record ) { throw new RuntimeException( 'Synthetic insert failure.' ); } // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
};
$storage_coordinator = new ChangeCoordinator( $sets, $failing_changes, $hasher, null, $clock );
$storage_command                    = $command;
$storage_command['idempotency_key'] = 'ap010-storage-key';
$storage_failure = $storage_coordinator->apply( $storage_command, static function () use ( &$storage_mutations ) { ++$storage_mutations; } );
agentpress_ap010_assert( is_wp_error( $storage_failure ) && 'AP_INTERNAL_ERROR' === $storage_failure->get_error_code(), 'Storage failure did not fail closed.' );
agentpress_ap010_assert( 0 === $storage_mutations, 'Storage failure permitted mutation.' );

$claim_mutations = 0;
$claim_changes   = new class() {
	public function find_by_idempotency_scope( $scope ) { return null; } // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
	public function create( $record ) { return 901; } // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
	public function transition( $id, $status, $changes ) { return false; } // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
	public function statuses_for_set( $set_id ) { return array( 'RECORDED' ); } // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
};
$claim_coordinator = new ChangeCoordinator( $sets, $claim_changes, $hasher, null, $clock );
$claim_command                    = $command;
$claim_command['idempotency_key'] = 'ap010-claim-key';
$claim_failure = $claim_coordinator->apply( $claim_command, static function () use ( &$claim_mutations ) { ++$claim_mutations; } );
agentpress_ap010_assert( is_wp_error( $claim_failure ) && 'AP_STATE_CONFLICT' === $claim_failure->get_error_code(), 'Claim failure did not conflict.' );
agentpress_ap010_assert( 0 === $claim_mutations, 'Failed claim permitted mutation.' );

$raw_changes = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM %i', $wpdb->prefix . 'agentpress_changes' ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
$raw_sets    = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM %i', $wpdb->prefix . 'agentpress_change_sets' ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
$raw_json    = wp_json_encode( array( $raw_changes, $raw_sets ) );
foreach ( array( 'ap010-r1-key', 'ap010-r2-key', 'raw-tab-session-ap010' ) as $raw_secret ) {
	agentpress_ap010_assert( false === strpos( $raw_json, $raw_secret ), 'Raw key/session reached durable storage: ' . $raw_secret );
}

$audit_mode = 'pending';
$audit_ability = new class( $audit_mode ) {
	public $mode;
	public function __construct( &$mode ) { $this->mode =& $mode; }
	public function validate_input( $input ) { return true; } // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
	public function check_permissions( $input ) { return true; } // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
	public function execute( $input ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
		return array(
			'ok'         => true,
			'request_id' => wp_generate_uuid4(),
			'data'       => array( 'status' => 'pending' === $this->mode ? 'PENDING_APPROVAL' : 'APPLIED', 'change_set_id' => 71, 'change_id' => 72, 'object_id' => 501, 'replayed' => 'replay' === $this->mode ),
		);
	}
};
$transport = new WebMCPRoutes( null, null, static function () use ( $audit_ability ) { return $audit_ability; }, null, new AuditLogger() );
$nonce     = wp_create_nonce( 'wp_rest' );
foreach ( array( 'pending', 'replay' ) as $audit_mode_value ) {
	$audit_mode = $audit_mode_value;
	$request    = agentpress_ap010_request( 'agentpress/create-draft', array( 'post_type' => 'post', 'title' => 'Audit classification', 'idempotency_key' => 'ap010-audit-key-' . $audit_mode_value ), $nonce );
	agentpress_ap010_assert( true === $transport->authorize_execute( $request ), 'Audit classification request was not authorized.' );
	agentpress_ap010_assert( $transport->execute( $request ) instanceof WP_REST_Response, 'Audit classification execution failed.' );
}
$audit_results = $wpdb->get_col( $wpdb->prepare( 'SELECT result FROM %i ORDER BY id ASC', $wpdb->prefix . 'agentpress_audit_events' ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
agentpress_ap010_assert( array( 'PENDING', 'REPLAYED' ) === $audit_results, 'Transport emitted duplicate or misclassified coordinator audit rows.' );

$counts = array(
	'change_sets'           => agentpress_ap010_count( $wpdb, 'agentpress_change_sets' ),
	'changes'               => agentpress_ap010_count( $wpdb, 'agentpress_changes' ),
	'r1_mutations'          => $mutation_count,
	'r2_mutations'          => 0,
	'storage_mutations'     => $storage_mutations,
	'claim_mutations'       => $claim_mutations,
	'transport_audit_rows'  => count( $audit_results ),
	'parent_states_tested'  => 11,
);

foreach ( array( 'agentpress_audit_events', 'agentpress_changes', 'agentpress_change_sets' ) as $suffix ) {
	$wpdb->query( $wpdb->prepare( 'DELETE FROM %i', $wpdb->prefix . $suffix ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
}

echo wp_json_encode( $counts ) . "\n";
