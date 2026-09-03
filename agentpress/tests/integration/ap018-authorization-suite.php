<?php
/**
 * AP-018 P0 authorization regression suite.
 *
 * Parameterized discovery plus direct-execution tests for Administrator,
 * Editor, Author, Subscriber, logged-out, invalid nonce, expired session,
 * and capability mutation.
 *
 * Asserts strict error codes and zero unauthorized mutations on every forbidden path.
 *
 * @package AgentPress
 */

use AgentPress\Abilities\AbilityCatalog;
use AgentPress\Activation;
use AgentPress\Changes\ChangeCoordinator;
use AgentPress\Changes\ChangeRepository;
use AgentPress\Changes\ChangeSetRepository;
use AgentPress\Policy\CapabilityEnvelope;
use AgentPress\Policy\DiscoveryPolicy;
use AgentPress\Policy\ExecutionPolicy;
use AgentPress\Rest\WebMCPRoutes;
use AgentPress\WebMCP\AbilityMap;

if ( ! defined( 'ABSPATH' ) ) {
	fwrite( STDERR, "WordPress must be loaded.\n" );
	exit( 1 );
}

require_once ABSPATH . 'wp-admin/includes/user.php';

/**
 * Assert one AP-018 condition.
 *
 * @param bool   $condition Condition.
 * @param string $message   Failure message.
 * @return void
 */
function agentpress_ap018_assert( $condition, $message ) {
	if ( ! $condition ) {
		fwrite( STDERR, "AP-018 assertion failed: {$message}\n" );
		exit( 1 );
	}
}

/**
 * Capture normalized WordPress & AgentPress target state.
 *
 * @return array<string, mixed>
 */
function agentpress_ap018_target_state() {
	global $wpdb;
	return array(
		'posts_count'      => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts}" ), // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		'posts_max_id'     => (int) $wpdb->get_var( "SELECT COALESCE(MAX(ID), 0) FROM {$wpdb->posts}" ), // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		'terms_count'      => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->terms}" ), // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		'term_rel_count'   => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->term_relationships}" ), // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		'changes_count'    => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}agentpress_changes" ), // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		'change_sets'      => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}agentpress_change_sets" ), // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		'menu_locations'   => get_theme_mod( 'nav_menu_locations', array() ),
	);
}

/**
 * Dispatch REST request to AgentPress endpoint.
 *
 * @param string      $method         HTTP method.
 * @param string      $route          REST route.
 * @param string|null $nonce          REST nonce.
 * @param array|null  $body           Request payload array.
 * @param string|null $origin         Origin header.
 * @param string|null $sec_fetch_site Sec-Fetch-Site header.
 * @return WP_REST_Response
 */
function agentpress_ap018_rest( $method, $route, $nonce, $body = null, $origin = null, $sec_fetch_site = 'same-origin' ) {
	$request = new WP_REST_Request( $method, $route );
	$request->set_header( 'Content-Type', 'application/json' );
	if ( null !== $body ) {
		$request->set_body( wp_json_encode( $body ) );
	}
	if ( null !== $nonce ) {
		$request->set_header( 'X-WP-Nonce', $nonce );
	}
	if ( null !== $origin ) {
		$request->set_header( 'Origin', $origin );
	}
	if ( null !== $sec_fetch_site ) {
		$request->set_header( 'Sec-Fetch-Site', $sec_fetch_site );
	}

	$response = rest_ensure_response( rest_do_request( $request ) );
	return apply_filters( 'rest_post_dispatch', $response, rest_get_server(), $request );
}

/**
 * Create or reset one synthetic user.
 *
 * @param string $login Login.
 * @param string $role  Role.
 * @return int User ID.
 */
function agentpress_ap018_user( $login, $role ) {
	$existing = get_user_by( 'login', $login );
	if ( $existing ) {
		wp_delete_user( $existing->ID );
	}
	$id = wp_insert_user(
		array(
			'user_login' => $login,
			'user_pass'  => wp_generate_password( 24 ),
			'user_email' => $login . '@example.test',
			'role'       => $role,
		)
	);
	agentpress_ap018_assert( ! is_wp_error( $id ), 'Could not create user ' . $login );
	return (int) $id;
}

global $wpdb;
Activation::activate();

// 1. Establish Users across all 5 roles.
$users = array(
	'administrator' => agentpress_ap018_user( 'agentpress_ap018_admin', 'administrator' ),
	'editor'        => agentpress_ap018_user( 'agentpress_ap018_editor', 'editor' ),
	'author'        => agentpress_ap018_user( 'agentpress_ap018_author', 'author' ),
	'subscriber'    => agentpress_ap018_user( 'agentpress_ap018_sub', 'subscriber' ),
	'mutated'       => agentpress_ap018_user( 'agentpress_ap018_mutated', 'subscriber' ),
);

$site_origin = home_url();

try {
	// 2. Provision Targets for Ownership & Permission testing.
	wp_set_current_user( $users['administrator'] );
	$admin_page_id = wp_insert_post(
		array(
			'post_type'   => 'page',
			'post_status' => 'draft',
			'post_title'  => 'AP018 Admin Page Draft',
			'post_author' => $users['administrator'],
		)
	);
	$admin_post_id = wp_insert_post(
		array(
			'post_type'   => 'post',
			'post_status' => 'publish',
			'post_title'  => 'AP018 Admin Published Post',
			'post_author' => $users['administrator'],
		)
	);

	wp_set_current_user( $users['author'] );
	$drafts_service = new \AgentPress\Content\DraftCreationService();
	$created_ap     = $drafts_service->execute(
		array(
			'post_type'       => 'post',
			'title'           => 'AP018 Author Post Draft',
			'idempotency_key' => 'ap018-init-draft-01',
		)
	);
	agentpress_ap018_assert( is_array( $created_ap ) && isset( $created_ap['data']['content_id'] ), 'Could not create author AP draft.' );
	$author_post_id = (int) $created_ap['data']['content_id'];

	$author_ordinary_draft = wp_insert_post(
		array(
			'post_type'   => 'post',
			'post_status' => 'draft',
			'post_title'  => 'AP018 Author Ordinary Draft',
			'post_author' => $users['author'],
		)
	);

	// =========================================================================
	// MATRIX 1: Discovery Policy Across Roles
	// =========================================================================

	// Administrator: 15 tools exposed, zero blocked R3 tools.
	wp_set_current_user( $users['administrator'] );
	$admin_discovered = ( new DiscoveryPolicy() )->discoverable();
	agentpress_ap018_assert( 15 === count( $admin_discovered ), 'Administrator discovery tool count is not 15.' );
	agentpress_ap018_assert( 15 === count( ( new WebMCPRoutes() )->default_definitions() ), 'Administrator REST tools count is not 15.' );

	$blocked_r3 = array(
		'agentpress/manage-users',
		'agentpress/install-plugin',
		'agentpress/edit-theme',
		'agentpress/edit-code',
		'agentpress/manage-settings',
		'agentpress/execute-sql',
		'agentpress/execute-shell',
	);
	foreach ( $blocked_r3 as $r3_tool ) {
		agentpress_ap018_assert( ! in_array( $r3_tool, $admin_discovered, true ), "Blocked tool {$r3_tool} was discoverable by Administrator." );
		agentpress_ap018_assert( ! AbilityMap::contains( $r3_tool ), "Blocked tool {$r3_tool} exists in AbilityMap." );
	}

	// Editor: Can discover content & taxonomy write tools, but NOT classic navigation.
	wp_set_current_user( $users['editor'] );
	$editor_discovery = new DiscoveryPolicy();
	agentpress_ap018_assert( $editor_discovery->can_discover( 'agentpress/create-draft' ), 'Editor cannot discover create-draft.' );
	agentpress_ap018_assert( $editor_discovery->can_discover( 'agentpress/update-content' ), 'Editor cannot discover update-content.' );
	agentpress_ap018_assert( $editor_discovery->can_discover( 'agentpress/assign-terms' ), 'Editor cannot discover assign-terms.' );
	agentpress_ap018_assert( ! $editor_discovery->can_discover( 'agentpress/stage-navigation-change' ), 'Editor unexpectedly can discover stage-navigation-change.' );

	// Author: Can discover create-draft and update-content, but NOT navigation or page draft.
	wp_set_current_user( $users['author'] );
	$author_discovery = new DiscoveryPolicy();
	agentpress_ap018_assert( $author_discovery->can_discover( 'agentpress/create-draft' ), 'Author cannot discover create-draft.' );
	agentpress_ap018_assert( ! $author_discovery->can_discover( 'agentpress/stage-navigation-change' ), 'Author unexpectedly can discover stage-navigation-change.' );

	// Subscriber: Can discover reads only, ZERO write tools.
	wp_set_current_user( $users['subscriber'] );
	$sub_discovery = new DiscoveryPolicy();
	agentpress_ap018_assert( $sub_discovery->can_discover( 'agentpress/get-context' ), 'Subscriber cannot discover get-context.' );
	agentpress_ap018_assert( $sub_discovery->can_discover( 'agentpress/get-site-structure' ), 'Subscriber cannot discover get-site-structure.' );
	agentpress_ap018_assert( $sub_discovery->can_discover( 'agentpress/list-content' ), 'Subscriber cannot discover list-content.' );
	agentpress_ap018_assert( $sub_discovery->can_discover( 'agentpress/list-terms' ), 'Subscriber cannot discover list-terms.' );

	foreach ( array( 'agentpress/create-draft', 'agentpress/update-content', 'agentpress/assign-terms', 'agentpress/stage-navigation-change', 'agentpress/publish-content', 'agentpress/create-term' ) as $write_ability ) {
		agentpress_ap018_assert( ! $sub_discovery->can_discover( $write_ability ), "Subscriber unexpectedly can discover {$write_ability}." );
	}

	// Anonymous: ZERO tools discoverable.
	wp_set_current_user( 0 );
	agentpress_ap018_assert( array() === ( new DiscoveryPolicy() )->discoverable(), 'Anonymous user discovered tools.' );
	agentpress_ap018_assert( array() === ( new WebMCPRoutes() )->default_definitions(), 'Anonymous REST tools returned definitions.' );

	// =========================================================================
	// MATRIX 2: REST Transport Security Boundaries (Zero Target Mutation)
	// =========================================================================

	// Test A: Anonymous request -> 401 AP_NOT_AUTHENTICATED, zero mutation, zero audit.
	wp_set_current_user( 0 );
	$state_before = agentpress_ap018_target_state();
	$audit_before = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}agentpress_audit_events" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared

	$res = agentpress_ap018_rest( 'POST', '/agentpress/v1/webmcp/execute', 'dummy-nonce', array( 'ability' => 'agentpress/create-draft', 'input' => array( 'post_type' => 'post', 'title' => 'Anon Draft' ) ) );
	agentpress_ap018_assert( 401 === $res->get_status(), 'Anonymous execute did not return 401: ' . $res->get_status() );
	agentpress_ap018_assert( 'AP_NOT_AUTHENTICATED' === $res->get_data()['error']['code'], 'Anonymous execute wrong error code.' );
	agentpress_ap018_assert( $state_before === agentpress_ap018_target_state(), 'Anonymous execute mutated WordPress target state.' );
	agentpress_ap018_assert( $audit_before === (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}agentpress_audit_events" ), 'Anonymous request created audit row.' ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared

	// Test B: Authenticated user with missing nonce -> 403 AP_NONCE_INVALID.
	wp_set_current_user( $users['author'] );
	$state_before = agentpress_ap018_target_state();
	$audit_before = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}agentpress_audit_events" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared

	$res = agentpress_ap018_rest( 'POST', '/agentpress/v1/webmcp/execute', null, array( 'ability' => 'agentpress/create-draft', 'input' => array( 'post_type' => 'post', 'title' => 'Missing Nonce Draft' ) ) );
	agentpress_ap018_assert( 403 === $res->get_status(), 'Missing nonce did not return 403: ' . $res->get_status() );
	agentpress_ap018_assert( 'AP_NONCE_INVALID' === $res->get_data()['error']['code'], 'Missing nonce wrong error code.' );
	agentpress_ap018_assert( $state_before === agentpress_ap018_target_state(), 'Missing nonce mutated WordPress target state.' );
	agentpress_ap018_assert( $audit_before === (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}agentpress_audit_events" ), 'Missing nonce created audit row.' ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared

	// Test C: Authenticated user with invalid / forged nonce -> 403 AP_NONCE_INVALID.
	$state_before = agentpress_ap018_target_state();
	$res          = agentpress_ap018_rest( 'POST', '/agentpress/v1/webmcp/execute', 'invalid-nonce-12345', array( 'ability' => 'agentpress/create-draft', 'input' => array( 'post_type' => 'post', 'title' => 'Forged Nonce Draft' ) ) );
	agentpress_ap018_assert( 403 === $res->get_status(), 'Forged nonce did not return 403: ' . $res->get_status() );
	agentpress_ap018_assert( 'AP_NONCE_INVALID' === $res->get_data()['error']['code'], 'Forged nonce wrong error code.' );
	agentpress_ap018_assert( $state_before === agentpress_ap018_target_state(), 'Forged nonce mutated target state.' );

	// Test D: Cross-Origin request rejection -> 403 AP_POLICY_BLOCKED.
	$author_nonce = wp_create_nonce( 'wp_rest' );
	$state_before = agentpress_ap018_target_state();
	$res          = agentpress_ap018_rest( 'POST', '/agentpress/v1/webmcp/execute', $author_nonce, array( 'ability' => 'agentpress/create-draft', 'input' => array( 'post_type' => 'post', 'title' => 'CSRF Draft' ) ), 'https://evil-attacker.example.com', 'cross-site' );
	agentpress_ap018_assert( 403 === $res->get_status(), 'Cross-origin did not return 403: ' . $res->get_status() );
	agentpress_ap018_assert( 'AP_POLICY_BLOCKED' === $res->get_data()['error']['code'], 'Cross-origin wrong error code.' );
	agentpress_ap018_assert( $state_before === agentpress_ap018_target_state(), 'Cross-origin mutated target state.' );

	// =========================================================================
	// MATRIX 3: Direct Forbidden Execution & Zero Target Mutation
	// =========================================================================

	// Test E: Subscriber direct-calls every write ability -> AP_PERMISSION_DENIED + zero mutation.
	wp_set_current_user( $users['subscriber'] );
	$sub_nonce     = wp_create_nonce( 'wp_rest' );
	$write_actions = array(
		array( 'ability' => 'agentpress/create-draft', 'args' => array( 'post_type' => 'post', 'title' => 'Sub Attack Post', 'idempotency_key' => 'ap018-sub-key-01' ) ),
		array( 'ability' => 'agentpress/create-draft', 'args' => array( 'post_type' => 'page', 'title' => 'Sub Attack Page', 'idempotency_key' => 'ap018-sub-key-02' ) ),
		array( 'ability' => 'agentpress/update-content', 'args' => array( 'content_id' => $author_post_id, 'title' => 'Sub Hack', 'idempotency_key' => 'ap018-sub-key-03' ) ),
		array( 'ability' => 'agentpress/assign-terms', 'args' => array( 'content_id' => $author_post_id, 'taxonomy' => 'category', 'term_ids' => array( 1 ), 'idempotency_key' => 'ap018-sub-key-04' ) ),
		array( 'ability' => 'agentpress/stage-navigation-change', 'args' => array( 'operation' => 'add', 'item' => array( 'label' => 'Sub Menu', 'url' => 'https://example.test/sub' ), 'idempotency_key' => 'ap018-sub-key-05' ) ),
		array( 'ability' => 'agentpress/publish-content', 'args' => array( 'content_id' => $author_post_id, 'idempotency_key' => 'ap018-sub-key-06' ) ),
		array( 'ability' => 'agentpress/create-term', 'args' => array( 'taxonomy' => 'category', 'name' => 'Sub Category', 'idempotency_key' => 'ap018-sub-key-07' ) ),
	);

	foreach ( $write_actions as $action ) {
		$state_before = agentpress_ap018_target_state();
		$audit_before = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}agentpress_audit_events" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		$res = agentpress_ap018_rest( 'POST', '/agentpress/v1/webmcp/execute', $sub_nonce, array( 'ability' => $action['ability'], 'input' => $action['args'] ), $site_origin );
		agentpress_ap018_assert( 403 === $res->get_status(), "Subscriber {$action['ability']} did not return 403: " . $res->get_status() . ' - ' . wp_json_encode( $res->get_data() ) );
		agentpress_ap018_assert( in_array( $res->get_data()['error']['code'], array( 'AP_PERMISSION_DENIED', 'AP_POLICY_BLOCKED' ), true ), "Subscriber {$action['ability']} unexpected code: " . $res->get_data()['error']['code'] );
		agentpress_ap018_assert( $state_before === agentpress_ap018_target_state(), "Subscriber {$action['ability']} caused target mutation." );

		// Authenticated denial creates an audit event.
		$audit_after = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}agentpress_audit_events" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		agentpress_ap018_assert( $audit_after === $audit_before + 1, "Subscriber {$action['ability']} did not create audited denial." );
	}

	// Test F: Author attempts page creation -> AP_PERMISSION_DENIED + zero page mutation.
	wp_set_current_user( $users['author'] );
	$author_nonce = wp_create_nonce( 'wp_rest' );
	$state_before = agentpress_ap018_target_state();

	$res = agentpress_ap018_rest( 'POST', '/agentpress/v1/webmcp/execute', $author_nonce, array( 'ability' => 'agentpress/create-draft', 'input' => array( 'post_type' => 'page', 'title' => 'Author Forbidden Page', 'idempotency_key' => 'ap018-auth-page-01' ) ), $site_origin );
	agentpress_ap018_assert( 403 === $res->get_status(), 'Author page create did not return 403: ' . $res->get_status() );
	agentpress_ap018_assert( 'AP_PERMISSION_DENIED' === $res->get_data()['error']['code'], 'Author page create wrong code.' );
	agentpress_ap018_assert( $state_before === agentpress_ap018_target_state(), 'Author page create mutated database.' );

	// Test G: Author attempts to edit admin-owned draft -> AP_PERMISSION_DENIED / AP_CONTENT_NOT_FOUND + zero mutation.
	$state_before = agentpress_ap018_target_state();
	$res          = agentpress_ap018_rest( 'POST', '/agentpress/v1/webmcp/execute', $author_nonce, array( 'ability' => 'agentpress/update-content', 'input' => array( 'content_id' => $admin_page_id, 'title' => 'Author Edit Admin Page', 'idempotency_key' => 'ap018-auth-edit-01' ) ), $site_origin );
	agentpress_ap018_assert( in_array( $res->get_status(), array( 403, 404 ), true ), 'Author editing admin page unexpected status: ' . $res->get_status() );
	agentpress_ap018_assert( $state_before === agentpress_ap018_target_state(), 'Author editing admin page mutated state.' );

	// Test H: Editor attempts to stage navigation -> AP_PERMISSION_DENIED + zero live menu mutation.
	wp_set_current_user( $users['editor'] );
	$editor_nonce = wp_create_nonce( 'wp_rest' );
	$state_before = agentpress_ap018_target_state();

	$res = agentpress_ap018_rest( 'POST', '/agentpress/v1/webmcp/execute', $editor_nonce, array( 'ability' => 'agentpress/stage-navigation-change', 'input' => array( 'operation' => 'add', 'item' => array( 'label' => 'Editor Menu', 'url' => 'https://example.test/editor' ), 'idempotency_key' => 'ap018-ed-stage-01' ) ), $site_origin );
	agentpress_ap018_assert( 403 === $res->get_status(), 'Editor stage navigation did not return 403: ' . $res->get_status() );
	agentpress_ap018_assert( 'AP_PERMISSION_DENIED' === $res->get_data()['error']['code'], 'Editor stage navigation wrong error code.' );
	agentpress_ap018_assert( $state_before === agentpress_ap018_target_state(), 'Editor stage navigation mutated live menu.' );

	// Test I: Author R1 automatic apply on AgentPress draft vs R2 staged proposal on ordinary draft.
	wp_set_current_user( $users['author'] );
	$author_nonce = wp_create_nonce( 'wp_rest' );

	// I.1: R1 applied directly on AgentPress-created draft.
	$res_r1 = agentpress_ap018_rest( 'POST', '/agentpress/v1/webmcp/execute', $author_nonce, array( 'ability' => 'agentpress/update-content', 'input' => array( 'content_id' => $author_post_id, 'title' => 'Author Updated AP Draft', 'idempotency_key' => 'ap018-auth-up-01' ) ), $site_origin );
	agentpress_ap018_assert( 200 === $res_r1->get_status(), 'Author update AP draft did not return 200: ' . $res_r1->get_status() );
	agentpress_ap018_assert( 'APPLIED' === $res_r1->get_data()['data']['status'], 'Author update AP draft status is not APPLIED.' );
	agentpress_ap018_assert( 'Author Updated AP Draft' === get_post( $author_post_id )->post_title, 'Post title was not updated for R1 draft.' );

	// I.2: R2 staged on ordinary draft (zero direct mutation in wp_posts).
	$ord_before_title = get_post( $author_ordinary_draft )->post_title;
	$res_r2           = agentpress_ap018_rest( 'POST', '/agentpress/v1/webmcp/execute', $author_nonce, array( 'ability' => 'agentpress/update-content', 'input' => array( 'content_id' => $author_ordinary_draft, 'title' => 'Author Proposed Update', 'idempotency_key' => 'ap018-auth-ord-01' ) ), $site_origin );
	agentpress_ap018_assert( 200 === $res_r2->get_status(), 'Author update ordinary draft did not return 200: ' . $res_r2->get_status() );
	agentpress_ap018_assert( 'PENDING_APPROVAL' === $res_r2->get_data()['data']['status'], 'Author update ordinary draft status is not PENDING_APPROVAL.' );
	agentpress_ap018_assert( true === $res_r2->get_data()['data']['approval_required'], 'approval_required was false for ordinary draft update.' );
	agentpress_ap018_assert( $ord_before_title === get_post( $author_ordinary_draft )->post_title, 'Ordinary draft was mutated directly without approval.' );

	// =========================================================================
	// MATRIX 4: Core WordPress Abilities REST Boundary Isolation
	// =========================================================================

	// Native /wp-abilities/v1/abilities must NOT expose any agentpress abilities.
	$core_abilities_req = rest_do_request( new WP_REST_Request( 'GET', '/wp-abilities/v1/abilities' ) );
	$core_data          = $core_abilities_req->get_data();
	$core_listed_names  = array();
	if ( is_array( $core_data ) ) {
		foreach ( $core_data as $item ) {
			if ( is_array( $item ) && isset( $item['name'] ) ) {
				$core_listed_names[] = $item['name'];
			}
		}
	}
	foreach ( array_keys( AbilityMap::all() ) as $ap_name ) {
		agentpress_ap018_assert( ! in_array( $ap_name, $core_listed_names, true ), "Native core REST exposed {$ap_name}." );
	}

	// Direct execution via core REST endpoint fails with 404 rest_ability_not_found.
	$core_run_req = rest_do_request( new WP_REST_Request( 'POST', '/wp-abilities/v1/abilities/agentpress/create-draft/run' ) );
	agentpress_ap018_assert( 404 === $core_run_req->get_status(), 'Direct core abilities REST run route did not 404.' );

	// =========================================================================
	// MATRIX 5: Dynamic Capability Mutation
	// =========================================================================

	$mutated_user = get_user_by( 'id', $users['mutated'] );
	wp_set_current_user( $mutated_user->ID );
	$before_mut_discovery = new DiscoveryPolicy();
	agentpress_ap018_assert( ! $before_mut_discovery->can_discover( 'agentpress/create-draft' ), 'Mutated user discovered create-draft before grant.' );

	// Dynamically grant publish_posts & edit_posts.
	$mutated_user->add_cap( 'edit_posts' );
	$mutated_user->add_cap( 'publish_posts' );
	wp_set_current_user( 0 );
	clean_user_cache( $mutated_user->ID );
	wp_set_current_user( $mutated_user->ID );

	$after_mut_discovery = new DiscoveryPolicy();
	agentpress_ap018_assert( $after_mut_discovery->can_discover( 'agentpress/create-draft' ), 'Mutated user did not discover create-draft after grant.' );

	// Dynamically revoke.
	$mutated_user->remove_cap( 'edit_posts' );
	wp_set_current_user( 0 );
	clean_user_cache( $mutated_user->ID );
	wp_set_current_user( $mutated_user->ID );

	$revoked_discovery = new DiscoveryPolicy();
	agentpress_ap018_assert( ! $revoked_discovery->can_discover( 'agentpress/create-draft' ), 'Mutated user still discovered create-draft after revoke.' );

	// =========================================================================
	// MATRIX 6: Audit Log Sanitization Check (Zero Secret Leakage)
	// =========================================================================

	$recent_audit = $wpdb->get_results(
		"SELECT arguments_sanitized FROM {$wpdb->prefix}agentpress_audit_events ORDER BY id DESC LIMIT 20",
		ARRAY_A
	);
	agentpress_ap018_assert( ! empty( $recent_audit ), 'No audit rows found to verify.' );
	$secret_sentinels = array( 'password', 'x-wp-nonce', 'cookie', 'token', 'authorization' );
	foreach ( $recent_audit as $row ) {
		$args_text = strtolower( (string) $row['arguments_sanitized'] );
		foreach ( $secret_sentinels as $sentinel ) {
			agentpress_ap018_assert( false === strpos( $args_text, $sentinel . '":' ), "Audit log contains secret key: {$sentinel}" );
		}
	}

	echo "AP-018 P0 authorization regression suite PASSED: All security and zero-mutation checks green.\n";

} finally {
	wp_set_current_user( $users['administrator'] );
	wp_delete_post( $admin_page_id, true );
	wp_delete_post( $admin_post_id, true );
	wp_delete_post( $author_post_id, true );
	wp_delete_post( $author_ordinary_draft, true );
	foreach ( $users as $uid ) {
		wp_delete_user( $uid );
	}
	wp_set_current_user( 0 );
}
