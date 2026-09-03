<?php
/**
 * AP-020 Change Set and Activity read matrix.
 *
 * @package AgentPress
 */

use AgentPress\Abilities\AbilityCatalog;
use AgentPress\Audit\ActivityReadService;
use AgentPress\Audit\AuditLogger;
use AgentPress\Changes\ChangeRepository;
use AgentPress\Changes\ChangeSetReadService;
use AgentPress\Changes\ChangeSetRepository;
use AgentPress\Schemas\SchemaValidator;
use AgentPress\Storage\Migrator;

/**
 * Assert one AP-020 condition.
 *
 * @param bool   $condition Condition.
 * @param string $message   Message.
 * @return void
 */
function agentpress_ap020_assert( $condition, $message ) {
	if ( ! $condition ) {
		fwrite( STDERR, "AP-020 assertion failed: {$message}\n" );
		exit( 1 );
	}
}

/**
 * Assert one expected error code.
 *
 * @param mixed  $result  Candidate result.
 * @param string $code    Expected code.
 * @param string $message Assertion message.
 * @return void
 */
function agentpress_ap020_error( $result, $code, $message ) {
	agentpress_ap020_assert( is_wp_error( $result ) && $code === $result->get_error_code(), $message );
}

/** @param string $login Login. @param string $role Role. @return int */
function agentpress_ap020_user( $login, $role ) {
	$existing = get_user_by( 'login', $login );
	$id       = $existing ? (int) $existing->ID : wp_create_user( $login, wp_generate_password( 24 ), $login . '@private.example.test' );
	$user     = new WP_User( $id );
	$user->set_role( $role );
	return $id;
}

/** @return array{change_sets:int, changes:int, audit:int} */
function agentpress_ap020_target_state() {
	global $wpdb;
	return array(
		'change_sets' => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}agentpress_change_sets" ), // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		'changes'     => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}agentpress_changes" ), // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		'audit'       => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}agentpress_audit_events" ), // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	);
}

/** @return string */
function agentpress_ap020_hash() {
	return hash( 'sha256', wp_generate_password( 32, false ) );
}

global $wpdb;
Migrator::migrate();
foreach ( array( 'agentpress_changes', 'agentpress_change_sets', 'agentpress_audit_events' ) as $suffix ) {
	$wpdb->query( $wpdb->prepare( 'DELETE FROM %i', $wpdb->prefix . $suffix ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
}
foreach ( get_users( array( 'search' => 'agentpress_ap020_', 'fields' => 'ID' ) ) as $stale_id ) {
	wp_delete_user( $stale_id );
}

$users = array(
	'administrator' => agentpress_ap020_user( 'agentpress_ap020_administrator', 'administrator' ),
	'author'        => agentpress_ap020_user( 'agentpress_ap020_author', 'author' ),
	'subscriber'    => agentpress_ap020_user( 'agentpress_ap020_subscriber', 'subscriber' ),
);
$sets    = new ChangeSetRepository();
$changes = new ChangeRepository();
$audit   = new AuditLogger();

$cs_author = $sets->create(
	array(
		'initiator_user_id' => $users['author'],
		'title'             => 'AP020 Author Set',
		'request_summary'   => 'Author staged two content proposals.',
		'status'            => 'READY_FOR_REVIEW',
	)
);
$cs_admin = $sets->create(
	array(
		'initiator_user_id' => $users['administrator'],
		'title'             => 'AP020 Admin Set',
		'request_summary'   => 'Administrator staged one proposal.',
		'status'            => 'OPEN',
	)
);

$change_admin_id = $changes->create(
	array(
		'change_set_id'       => $cs_admin,
		'actor_user_id'       => $users['administrator'],
		'ability'             => 'agentpress/update-content',
		'risk_class'          => 'R2',
		'operation'           => 'update',
		'object_type'         => 'post',
		'object_id'           => 11,
		'before_json'         => array( 'title' => 'PRIVATE-BODY-SENTINEL-AP020' ),
		'after_json'          => array( 'title' => 'Proposed Title' ),
		'target_state_hash'   => agentpress_ap020_hash(),
		'proposal_hash'       => agentpress_ap020_hash(),
		'idempotency_hash'    => agentpress_ap020_hash(),
		'idempotency_scope'   => agentpress_ap020_hash(),
		'status'              => 'PENDING_APPROVAL',
		'expires_at'          => gmdate( 'Y-m-d H:i:s', time() + 3600 ),
	)
);
$change_author_a = $changes->create(
	array(
		'change_set_id'       => $cs_author,
		'actor_user_id'       => $users['author'],
		'ability'             => 'agentpress/create-draft',
		'risk_class'          => 'R1',
		'operation'           => 'create',
		'object_type'         => 'page',
		'object_id'           => 21,
		'before_json'         => array(),
		'after_json'          => array( 'title' => 'Services Page' ),
		'target_state_hash'   => agentpress_ap020_hash(),
		'proposal_hash'       => agentpress_ap020_hash(),
		'idempotency_hash'    => agentpress_ap020_hash(),
		'idempotency_scope'   => agentpress_ap020_hash(),
		'status'              => 'APPLIED',
		'applied_at'          => gmdate( 'Y-m-d H:i:s' ),
	)
);
$change_author_b = $changes->create(
	array(
		'change_set_id'       => $cs_author,
		'actor_user_id'       => $users['author'],
		'ability'             => 'agentpress/assign-terms',
		'risk_class'          => 'R2',
		'operation'           => 'create',
		'object_type'         => 'post',
		'object_id'           => 22,
		'before_json'         => array(),
		'after_json'          => array( 'term_ids' => array( 2, 3 ) ),
		'target_state_hash'   => agentpress_ap020_hash(),
		'proposal_hash'       => agentpress_ap020_hash(),
		'idempotency_hash'    => agentpress_ap020_hash(),
		'idempotency_scope'   => agentpress_ap020_hash(),
		'status'              => 'PENDING_APPROVAL',
		'expires_at'          => gmdate( 'Y-m-d H:i:s', time() + 3600 ),
	)
);

$audit->record(
	array(
		'request_id'    => wp_generate_uuid4(),
		'actor_type'    => 'webmcp',
		'user_id'       => $users['author'],
		'change_set_id' => $cs_author,
		'change_id'     => $change_author_a,
		'ability'       => 'agentpress/create-draft',
		'object_type'   => 'page',
		'object_id'     => 21,
		'result'        => 'SUCCESS',
		'error_code'    => '',
		'arguments'     => array( 'cookie' => 'SECRET-COOKIE-AP020', 'password' => 'SECRET-PASSWORD-AP020', 'title' => 'Services Page' ),
		'duration_ms'   => 42,
	)
);
$audit->record(
	array(
		'request_id'    => wp_generate_uuid4(),
		'actor_type'    => 'webmcp',
		'user_id'       => $users['administrator'],
		'change_set_id' => $cs_admin,
		'change_id'     => $change_admin_id,
		'ability'       => 'agentpress/update-content',
		'object_type'   => 'post',
		'object_id'     => 11,
		'result'        => 'DENIED',
		'error_code'    => 'AP_PERMISSION_DENIED',
		'arguments'     => array( 'content_id' => 11 ),
		'duration_ms'   => 7,
	)
);
$audit->record(
	array(
		'request_id'    => wp_generate_uuid4(),
		'actor_type'    => 'webmcp',
		'user_id'       => $users['subscriber'],
		'change_set_id' => 0,
		'change_id'     => 0,
		'ability'       => 'agentpress/get-content',
		'object_type'   => 'post',
		'object_id'     => 33,
		'result'        => 'DENIED',
		'error_code'    => 'AP_PERMISSION_DENIED',
		'arguments'     => array( 'content_id' => 33 ),
		'duration_ms'   => 3,
	)
);

$catalog    = AbilityCatalog::all();
$validator  = new SchemaValidator();
$change_service   = new ChangeSetReadService();
$activity_service = new ActivityReadService();
$target_before    = agentpress_ap020_target_state();

$change_ability   = wp_get_ability( 'agentpress/get-change-set' );
$list_ability     = wp_get_ability( 'agentpress/list-change-sets' );
$activity_ability = wp_get_ability( 'agentpress/get-agent-activity' );
agentpress_ap020_assert( is_object( $change_ability ) && is_object( $list_ability ) && is_object( $activity_ability ), 'AP-020 read Abilities are not registered.' );

// Administrator can see every Change Set and every event.
wp_set_current_user( $users['administrator'] );
agentpress_ap020_assert( true === $list_ability->check_permissions( array() ), 'Administrator list permission failed.' );
$admin_list = $list_ability->execute( array( 'per_page' => 50 ) );
agentpress_ap020_assert( is_array( $admin_list ) && true === $validator->validate_output( $admin_list, $catalog['agentpress/list-change-sets']['output_schema'] ), 'Admin list failed its output schema.' );
$admin_refs = array_column( $admin_list['data']['items'], 'reference' );
agentpress_ap020_assert( in_array( 'AP-' . $cs_author, $admin_refs, true ) && in_array( 'AP-' . $cs_admin, $admin_refs, true ), 'Administrator did not see every Change Set.' );
agentpress_ap020_assert( 2 <= $admin_list['data']['total'], 'Administrator Change Set total is wrong.' );

agentpress_ap020_assert( true === $change_ability->check_permissions( array( 'change_set_id' => $cs_author ) ), 'Administrator author-set permission failed.' );
$admin_author_set = $change_ability->execute( array( 'change_set_id' => $cs_author ) );
agentpress_ap020_assert( is_array( $admin_author_set ) && true === $validator->validate_output( $admin_author_set, $catalog['agentpress/get-change-set']['output_schema'] ), 'Admin author-set detail failed its output schema.' );
agentpress_ap020_assert( 'READY_FOR_REVIEW' === $admin_author_set['data']['status'] && 2 === count( $admin_author_set['data']['changes'] ), 'Admin author-set detail fields differ.' );
agentpress_ap020_assert( '' !== $admin_author_set['data']['changes'][0]['semantic_after'] && 5000 >= strlen( $admin_author_set['data']['changes'][0]['semantic_after'] ), 'Semantic summary is not bounded/non-empty.' );

$admin_activity = $activity_ability->execute( array( 'per_page' => 50 ) );
agentpress_ap020_assert( is_array( $admin_activity ) && true === $validator->validate_output( $admin_activity, $catalog['agentpress/get-agent-activity']['output_schema'] ), 'Admin activity failed its output schema.' );
agentpress_ap020_assert( 3 <= $admin_activity['data']['total'], 'Administrator did not see every activity event.' );
$admin_serialized = wp_json_encode( $admin_activity );
agentpress_ap020_assert( false === strpos( $admin_serialized, 'SECRET-COOKIE-AP020' ) && false === strpos( $admin_serialized, 'SECRET-PASSWORD-AP020' ), 'Activity leaked secret argument data.' );
agentpress_ap020_assert( false === strpos( wp_json_encode( $admin_author_set ), 'PRIVATE-BODY-SENTINEL-AP020' ), 'Detail disclosed an oversized proposal body verbatim.' );

// Author sees only own rows and cannot discover another user's Change Set.
wp_set_current_user( $users['author'] );
$author_list = $list_ability->execute( array( 'per_page' => 50 ) );
agentpress_ap020_assert( is_array( $author_list ) && 1 === $author_list['data']['total'] && 'AP-' . $cs_author === $author_list['data']['items'][0]['reference'], 'Author Change Set list leaked another user.' );

agentpress_ap020_assert( true === $change_ability->check_permissions( array( 'change_set_id' => $cs_author ) ), 'Author own-set permission failed.' );
$author_own = $change_ability->execute( array( 'change_set_id' => $cs_author ) );
agentpress_ap020_assert( is_array( $author_own ) && 'AP-' . $cs_author === $author_own['data']['reference'], 'Author could not read own Change Set.' );

agentpress_ap020_error( $change_ability->check_permissions( array( 'change_set_id' => $cs_admin ) ), 'AP_CHANGE_NOT_FOUND', 'Author guessed another user set identity.' );
agentpress_ap020_error( $change_service->get( array( 'change_set_id' => $cs_admin ) ), 'AP_CHANGE_NOT_FOUND', 'Author read another user Change Set via service.' );
agentpress_ap020_error( $change_service->get( array( 'change_set_id' => 999999 ) ), 'AP_CHANGE_NOT_FOUND', 'Guessed missing set did not fail closed.' );

$author_activity = $activity_ability->execute( array( 'per_page' => 50 ) );
agentpress_ap020_assert( is_array( $author_activity ) && 1 === $author_activity['data']['total'] && $users['author'] === $author_activity['data']['items'][0]['user_id'], 'Author activity leaked another user.' );
$author_scoped = $activity_service->execute( array( 'change_set_id' => $cs_admin ) );
agentpress_ap020_assert( 0 === $author_scoped['data']['total'], 'Author activity exposed another set events.' );

// Subscriber sees no Change Sets and only its own activity.
wp_set_current_user( $users['subscriber'] );
$sub_list = $list_ability->execute( array( 'per_page' => 50 ) );
agentpress_ap020_assert( is_array( $sub_list ) && 0 === $sub_list['data']['total'], 'Subscriber saw a Change Set.' );
$sub_activity = $activity_ability->execute( array( 'per_page' => 50 ) );
agentpress_ap020_assert( 1 === $sub_activity['data']['total'] && $users['subscriber'] === $sub_activity['data']['items'][0]['user_id'], 'Subscriber did not see its own activity.' );
agentpress_ap020_error( $change_service->get( array( 'change_set_id' => $cs_author ) ), 'AP_CHANGE_NOT_FOUND', 'Subscriber read another user Change Set.' );

// Schema and pagination validation (the validation layer the admin routes also hit).
agentpress_ap020_error( $change_service->listing( array( 'status' => 'OPEN', 'bogus' => 1 ) ), 'AP_SCHEMA_INVALID', 'List accepted an unknown key.' );
agentpress_ap020_error( $change_service->listing( array( 'page' => 0 ) ), 'AP_SCHEMA_INVALID', 'List accepted page 0.' );
agentpress_ap020_error( $change_service->listing( array( 'per_page' => 51 ) ), 'AP_SCHEMA_INVALID', 'List accepted oversized per_page.' );
agentpress_ap020_error( $activity_service->execute( array( 'result' => 'NOT_A_RESULT' ) ), 'AP_SCHEMA_INVALID', 'Activity accepted an unknown result.' );
agentpress_ap020_error( $activity_service->execute( array( 'change_set_id' => '7' ) ), 'AP_SCHEMA_INVALID', 'Activity accepted a non-integer set id.' );
agentpress_ap020_error( $change_service->get( array( 'change_set_id' => 0 ) ), 'AP_SCHEMA_INVALID', 'Detail accepted a non-positive set id.' );
agentpress_ap020_error( $change_service->get( array( 'change_set_id' => -1 ) ), 'AP_SCHEMA_INVALID', 'Detail accepted a negative set id.' );

// Stable pagination and status filtering.
wp_set_current_user( $users['administrator'] );
$page_one_a      = $list_ability->execute( array( 'page' => 1, 'per_page' => 1 ) );
$page_one_b      = $list_ability->execute( array( 'page' => 1, 'per_page' => 1 ) );
agentpress_ap020_assert( $page_one_a['data']['items'] === $page_one_b['data']['items'] && 1 === count( $page_one_a['data']['items'] ), 'Pagination was not stable.' );
$status_filtered = $list_ability->execute( array( 'status' => 'READY_FOR_REVIEW', 'per_page' => 50 ) );
agentpress_ap020_assert( 1 === $status_filtered['data']['total'] && 'READY_FOR_REVIEW' === $status_filtered['data']['items'][0]['status'], 'Status filter differed.' );

// Reads must mutate no durable WordPress/AgentPress state.
agentpress_ap020_assert( $target_before === agentpress_ap020_target_state(), 'Reads mutated durable state.' );

// Clean up synthetic fixtures.
$sets->delete( $cs_author );
$sets->delete( $cs_admin );
$changes->delete( $change_author_a );
$changes->delete( $change_author_b );
$changes->delete( $change_admin_id );
foreach ( array( 'agentpress_changes', 'agentpress_change_sets', 'agentpress_audit_events' ) as $suffix ) {
	$wpdb->query( $wpdb->prepare( 'DELETE FROM %i', $wpdb->prefix . $suffix ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
}
foreach ( $users as $user_id ) {
	wp_delete_user( $user_id );
}

echo wp_json_encode(
	array(
		'admin_visible_sets'         => $admin_list['data']['total'],
		'author_visible_sets'        => 1,
		'subscriber_visible_sets'    => 0,
		'author_guessed_set_denials' => 3,
		'schema_denials'             => 7,
		'secret_leaks'               => 0,
		'read_mutations'             => 0,
	)
) . "\n";
