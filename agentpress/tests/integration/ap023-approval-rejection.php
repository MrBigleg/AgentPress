<?php
/**
 * AP-023 approval and rejection matrix.
 *
 * @package AgentPress
 */

use AgentPress\Changes\ApprovalService;
use AgentPress\Abilities\AbilityCatalog;
use AgentPress\Schemas\SchemaValidator;

/**
 * Assert condition for AP-023.
 *
 * @param bool   $condition Condition to assert.
 * @param string $message   Failure message.
 * @return void
 */
function agentpress_ap023_assert( $condition, $message ) {
	if ( ! $condition ) {
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fwrite
		fwrite( STDERR, "AP-023 assertion failed: {$message}\n" );
		exit( 1 );
	}
}

/**
 * Assert candidate result is expected WP_Error.
 *
 * @param mixed  $result  Candidate result.
 * @param string $code    Expected error code.
 * @param string $message Assertion message.
 * @return void
 */
function agentpress_ap023_error( $result, $code, $message ) {
	agentpress_ap023_assert( is_wp_error( $result ) && $code === $result->get_error_code(), $message );
}

/**
 * Create or fetch a test user with a given role.
 *
 * @param string $login Username.
 * @param string $role  Role name.
 * @return int User ID.
 */
function agentpress_ap023_user( $login, $role ) {
	$existing = get_user_by( 'login', $login );
	$id       = $existing ? (int) $existing->ID : wp_create_user( $login, wp_generate_password( 24 ), $login . '@private.example.test' );
	$user     = new WP_User( $id );
	$user->set_role( $role );
	return $id;
}

/**
 * Add a page menu item to a nav menu.
 *
 * @param int    $menu_id   Menu ID.
 * @param int    $object_id Page ID.
 * @param string $label     Label.
 * @param int    $position  Position.
 * @param int    $parent    Parent item ID.
 * @return int Menu item ID.
 */
function agentpress_ap023_menu_item( $menu_id, $object_id, $label, $position, $parent = 0 ) {
	$result = wp_update_nav_menu_item(
		$menu_id,
		0,
		array(
			'menu-item-title'     => $label,
			'menu-item-object-id' => $object_id,
			'menu-item-object'    => 'page',
			'menu-item-type'      => 'post_type',
			'menu-item-status'    => 'publish',
			'menu-item-position'  => $position,
			'menu-item-parent-id' => $parent,
		)
	);
	agentpress_ap023_assert( ! is_wp_error( $result ) && (int) $result > 0, 'Menu item creation failed.' );
	return (int) $result;
}

global $wpdb;

// 1. Snapshot initial environment state for clean restoration.
$initial_user_id   = get_current_user_id();
$initial_locations = get_theme_mod( 'nav_menu_locations', array() );
if ( ! is_array( $initial_locations ) ) {
	$initial_locations = array();
}
$was_primary_registered = has_nav_menu( 'primary' ) || array_key_exists( 'primary', get_registered_nav_menus() );
if ( ! $was_primary_registered ) {
	register_nav_menus( array( 'primary' => 'Synthetic AP-023 Primary' ) );
}

// 2. Clean up any stale AP-023 fixtures from an interrupted prior run.
$stale_user_ids = array();
foreach ( array( 'administrator', 'author' ) as $role_slug ) {
	$existing = get_user_by( 'login', 'agentpress_ap023_' . $role_slug );
	if ( is_object( $existing ) ) {
		$stale_user_ids[] = (int) $existing->ID;
	}
}
if ( ! empty( $stale_user_ids ) ) {
	$in_stale = implode( ',', array_map( 'intval', $stale_user_ids ) );
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$wpdb->query( "DELETE FROM {$wpdb->prefix}agentpress_changes WHERE actor_user_id IN ({$in_stale})" );
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$wpdb->query( "DELETE FROM {$wpdb->prefix}agentpress_change_sets WHERE initiator_user_id IN ({$in_stale})" );
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$wpdb->query( "DELETE FROM {$wpdb->prefix}agentpress_audit_events WHERE user_id IN ({$in_stale})" );
	foreach ( $stale_user_ids as $sid ) {
		wp_delete_user( $sid );
	}
}

foreach ( wp_get_nav_menus( array( 'hide_empty' => false ) ) as $stale_menu ) {
	if ( 0 === strpos( $stale_menu->name, 'AP023 ' ) ) {
		wp_delete_nav_menu( $stale_menu->term_id );
	}
}
foreach ( get_posts(
	array(
		'post_type'      => 'page',
		'post_status'    => 'any',
		's'              => 'AP023',
		'posts_per_page' => -1,
	)
) as $stale_page ) {
	wp_delete_post( $stale_page->ID, true );
}

// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
$audit_before = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}agentpress_audit_events WHERE actor_type = %s", 'human' ) );

// 3. Create synthetic test users and pages.
$users    = array(
	'administrator' => agentpress_ap023_user( 'agentpress_ap023_administrator', 'administrator' ),
	'author'        => agentpress_ap023_user( 'agentpress_ap023_author', 'author' ),
);
$page_ids = array();
foreach ( array( 'Home', 'About', 'Blog', 'Contact' ) as $label ) {
	$page_id = wp_insert_post(
		array(
			'post_type'   => 'page',
			'post_status' => 'publish',
			'post_title'  => 'AP023 ' . $label,
			'post_name'   => 'ap023-' . strtolower( $label ),
			'post_author' => $users['administrator'],
		)
	);
	agentpress_ap023_assert( ! is_wp_error( $page_id ) && (int) $page_id > 0, 'Fixture page creation failed.' );
	$page_ids[ $label ] = (int) $page_id;
}
$services_id = wp_insert_post(
	array(
		'post_type'   => 'page',
		'post_status' => 'draft',
		'post_title'  => 'AP023 Services',
		'post_name'   => 'ap023-services',
		'post_author' => $users['administrator'],
	)
);
agentpress_ap023_assert( ! is_wp_error( $services_id ) && (int) $services_id > 0, 'Services draft creation failed.' );
$services_id = (int) $services_id;

$menu_id = wp_create_nav_menu( 'AP023 Primary Menu' );
agentpress_ap023_assert( ! is_wp_error( $menu_id ), 'Primary menu creation failed.' );
$menu_id  = (int) $menu_id;
$item_ids = array();
$position = 1;
foreach ( array( 'Home', 'About', 'Blog', 'Contact' ) as $label ) {
	$item_ids[ $label ] = agentpress_ap023_menu_item( $menu_id, $page_ids[ $label ], $label, $position );
	++$position;
}
$test_locations            = $initial_locations;
$test_locations['primary'] = $menu_id;
set_theme_mod( 'nav_menu_locations', $test_locations );

// 4. Test approval and rejection matrix.
wp_set_current_user( $users['administrator'] );
$stage_ability = wp_get_ability( 'agentpress/stage-navigation-change' );
$nav_ability   = wp_get_ability( 'agentpress/get-navigation' );
$service       = new ApprovalService();
$validator     = new SchemaValidator();
$catalog       = AbilityCatalog::all();
agentpress_ap023_assert( is_object( $stage_ability ) && is_object( $nav_ability ), 'AP-023 Abilities are not registered.' );

$add_input = static function ( $key ) use ( $services_id ) {
	return array(
		'location'        => 'primary',
		'operation'       => 'add',
		'item'            => array(
			'object_type'    => 'page',
			'object_id'      => $services_id,
			'parent_item_id' => 0,
			'position'       => 3,
			'label'          => 'AP023 Services',
		),
		'idempotency_key' => $key,
	);
};

// 1. Approve applies the pending navigation change, records the approver, and moves the set to COMPLETED.
$staged = $stage_ability->execute( $add_input( 'ap023-approve' ) );
agentpress_ap023_assert( is_array( $staged ), 'Staging failed.' );
$change_id = (int) $staged['data']['change_id'];
$set_id    = (int) $staged['data']['change_set_id'];
agentpress_ap023_assert( $set_id > 0, 'Missing Change Set.' );

$approved = $service->approve( $change_id );
agentpress_ap023_assert( is_array( $approved ) && 'APPLIED' === $approved['data']['status'] && 'SUCCESS' === $approved['data']['result'] && 'AP-' . $set_id === $approved['data']['change_set_ref'], 'Approve returned a bad envelope.' );
agentpress_ap023_assert( $users['administrator'] === $approved['data']['approved_by'], 'Approve did not record the approver.' );
$after_approve = $nav_ability->execute( array( 'location' => 'primary' ) );
agentpress_ap023_assert( array( 'Home', 'About', 'AP023 Services', 'Blog', 'Contact' ) === array_column( $after_approve['data']['items'], 'label' ), 'Approve did not apply the proposal.' );
$applied_row = ( new \AgentPress\Changes\ChangeRepository() )->find( $change_id );
agentpress_ap023_assert( 'APPLIED' === $applied_row['status'] && '' !== $applied_row['applied_at'] && $users['administrator'] === (int) $applied_row['approved_by'], 'Applied row fields differ.' );

// 2. Re-approve of an already-applied change conflicts (idempotent, no double apply).
$again = $service->approve( $change_id );
agentpress_ap023_error( $again, 'AP_STATE_CONFLICT', 'Already-applied change was re-approved.' );

// 3. Reject records the rejector and never mutates WordPress.
$staged2 = $stage_ability->execute( $add_input( 'ap023-reject' ) );
agentpress_ap023_assert( is_array( $staged2 ), 'Reject staging failed.' );
$reject_change = (int) $staged2['data']['change_id'];
$before_reject = $nav_ability->execute( array( 'location' => 'primary' ) );
$rejected_info = $service->reject( $reject_change, 'Not this time' );
agentpress_ap023_assert( is_array( $rejected_info ) && 'REJECTED' === $rejected_info['data']['status'] && 'Not this time' === $rejected_info['data']['reason'], 'Reject envelope differs.' );
$after_reject = $nav_ability->execute( array( 'location' => 'primary' ) );
agentpress_ap023_assert( $before_reject['data']['items'] === $after_reject['data']['items'], 'Rejection mutated WordPress.' );
$rejected_row = ( new \AgentPress\Changes\ChangeRepository() )->find( $reject_change );
agentpress_ap023_assert( 'REJECTED' === $rejected_row['status'] && $users['administrator'] === (int) $rejected_row['rejected_by'], 'Reject row fields differ.' );

// 4. Expired proposals fail with AP_CHANGE_EXPIRED.
$staged3 = $stage_ability->execute( $add_input( 'ap023-expired' ) );
agentpress_ap023_assert( is_array( $staged3 ), 'Expired staging failed.' );
$expired_change = (int) $staged3['data']['change_id'];
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
$wpdb->query( $wpdb->prepare( 'UPDATE %i SET expires_at = %s WHERE id = %d', $wpdb->prefix . 'agentpress_changes', gmdate( 'Y-m-d H:i:s', time() - 3600 ), $expired_change ) );
agentpress_ap023_error( $service->approve( $expired_change ), 'AP_CHANGE_EXPIRED', 'Expired proposal was approved.' );

// 5. Stale target (live menu changed after staging) conflicts and does not mutate.
$staged4 = $stage_ability->execute( $add_input( 'ap023-stale' ) );
agentpress_ap023_assert( is_array( $staged4 ), 'Stale staging failed.' );
$stale_change = (int) $staged4['data']['change_id'];
agentpress_ap023_menu_item( $menu_id, $page_ids['Blog'], 'Blog Rival', 5 );
$before_stale = $nav_ability->execute( array( 'location' => 'primary' ) );
agentpress_ap023_error( $service->approve( $stale_change ), 'AP_STATE_CONFLICT', 'Stale-target approval was applied.' );
$after_stale = $nav_ability->execute( array( 'location' => 'primary' ) );
agentpress_ap023_assert( $before_stale['data']['items'] === $after_stale['data']['items'], 'Stale-target conflict mutated the menu.' );
$stale_row = ( new \AgentPress\Changes\ChangeRepository() )->find( $stale_change );
agentpress_ap023_assert( 'CONFLICT' === $stale_row['status'], 'Stale-target change status differs.' );

// 6. Unauthorized approver is rejected.
$staged5 = $stage_ability->execute( $add_input( 'ap023-role' ) );
agentpress_ap023_assert( is_array( $staged5 ), 'Role staging failed.' );
$role_change = (int) $staged5['data']['change_id'];
wp_set_current_user( $users['author'] );
agentpress_ap023_error( $service->approve( $role_change ), 'AP_PERMISSION_DENIED', 'Author approved a change.' );
wp_set_current_user( 0 );
agentpress_ap023_error( $service->approve( $role_change ), 'AP_NOT_AUTHENTICATED', 'Logged out approved a change.' );

// 7. A reusable audit record was written for the human actions.
wp_set_current_user( $users['administrator'] );
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
$audit_delta = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}agentpress_audit_events WHERE actor_type = %s", 'human' ) ) - $audit_before;
agentpress_ap023_assert( $audit_delta >= 2, 'Human audit records were not written.' );

// Clean up synthetic fixtures only.
set_theme_mod( 'nav_menu_locations', $initial_locations );
wp_delete_nav_menu( $menu_id );
foreach ( array_merge( array_values( $page_ids ), array( $services_id ) ) as $page_id ) {
	wp_delete_post( $page_id, true );
}

$user_ids = array_values( $users );
$in_users = implode( ',', array_map( 'intval', $user_ids ) );
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
$wpdb->query( "DELETE FROM {$wpdb->prefix}agentpress_changes WHERE actor_user_id IN ({$in_users})" );
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
$wpdb->query( "DELETE FROM {$wpdb->prefix}agentpress_change_sets WHERE initiator_user_id IN ({$in_users})" );
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
$wpdb->query( "DELETE FROM {$wpdb->prefix}agentpress_audit_events WHERE user_id IN ({$in_users})" );

foreach ( $users as $user_id ) {
	wp_delete_user( $user_id );
}
if ( ! $was_primary_registered ) {
	unregister_nav_menu( 'primary' );
}
wp_set_current_user( $initial_user_id );

echo wp_json_encode(
	array(
		'approve_applied'     => true,
		'approver_recorded'   => true,
		'reapply_conflict'    => true,
		'reject_no_mutation'  => true,
		'rejector_recorded'   => true,
		'expired_denied'      => true,
		'stale_conflict'      => true,
		'stale_no_mutation'   => true,
		'role_denials'        => 2,
		'human_audit_records' => $audit_delta,
	)
) . "\n";
