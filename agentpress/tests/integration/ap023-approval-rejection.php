<?php
/**
 * AP-023 approval and rejection matrix.
 *
 * @package AgentPress
 */

use AgentPress\Changes\ApprovalService;
use AgentPress\Abilities\AbilityCatalog;
use AgentPress\Schemas\SchemaValidator;

/** @param bool $condition Condition. @param string $message Message. @return void */
function agentpress_ap023_assert( $condition, $message ) {
	if ( ! $condition ) {
		fwrite( STDERR, "AP-023 assertion failed: {$message}\n" );
		exit( 1 );
	}
}

/** @param mixed $result Candidate result. @param string $code Expected code. @param string $message Assertion message. @return void */
function agentpress_ap023_error( $result, $code, $message ) {
	agentpress_ap023_assert( is_wp_error( $result ) && $code === $result->get_error_code(), $message );
}

/** @param string $login Login. @param string $role Role. @return int */
function agentpress_ap023_user( $login, $role ) {
	$existing = get_user_by( 'login', $login );
	$id       = $existing ? (int) $existing->ID : wp_create_user( $login, wp_generate_password( 24 ), $login . '@private.example.test' );
	$user     = new WP_User( $id );
	$user->set_role( $role );
	return $id;
}

/** @param int $menu_id Menu ID. @param int $object_id Page ID. @param string $label Label. @param int $position Position. @param int $parent Parent item. @return int */
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

$original_locations = get_theme_mod( 'nav_menu_locations', array() );
$installed          = array();
foreach ( $original_locations as $location => $assigned_menu_id ) {
	$assigned_menu = wp_get_nav_menu_object( (int) $assigned_menu_id );
	if ( ! is_object( $assigned_menu ) || 0 !== strpos( $assigned_menu->name, 'AP023 ' ) ) {
		$installed[ $location ] = $assigned_menu_id;
	}
}
set_theme_mod( 'nav_menu_locations', $installed );
register_nav_menus( array( 'primary' => 'Synthetic AP-023 Primary' ) );
foreach ( wp_get_nav_menus( array( 'hide_empty' => false ) ) as $stale_menu ) {
	if ( 0 === strpos( $stale_menu->name, 'AP023 ' ) ) {
		wp_delete_nav_menu( $stale_menu->term_id );
	}
}
foreach ( array( 'administrator', 'author' ) as $role ) {
	$existing = get_user_by( 'login', 'agentpress_ap023_' . $role );
	if ( is_object( $existing ) ) {
		wp_delete_user( $existing->ID );
	}
}
foreach ( get_posts( array( 'post_type' => 'page', 'post_status' => 'any', 's' => 'AP023', 'posts_per_page' => -1 ) ) as $stale_page ) {
	wp_delete_post( $stale_page->ID, true );
}
global $wpdb;
foreach ( array( 'agentpress_changes', 'agentpress_change_sets', 'agentpress_audit_events' ) as $suffix ) {
	$wpdb->query( $wpdb->prepare( 'DELETE FROM %i', $wpdb->prefix . $suffix ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
}
$audit_before = (int) $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM %i WHERE actor_type = %s', $wpdb->prefix . 'agentpress_audit_events', 'human' ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

$users = array(
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
agentpress_ap023_assert( ! is_wp_error( $services_id ) && (int) $services_id > 0, 'Services page creation failed.' );
$services_id = (int) $services_id;

$menu_id = wp_create_nav_menu( 'AP023 Primary Menu' );
agentpress_ap023_assert( ! is_wp_error( $menu_id ), 'Primary menu creation failed.' );
$menu_id = (int) $menu_id;
$item_ids = array();
$position = 1;
foreach ( array( 'Home', 'About', 'Blog', 'Contact' ) as $label ) {
	$item_ids[ $label ] = agentpress_ap023_menu_item( $menu_id, $page_ids[ $label ], $label, $position );
	++$position;
}
set_theme_mod( 'nav_menu_locations', array_merge( $installed, array( 'primary' => $menu_id ) ) );

wp_set_current_user( $users['administrator'] );
$stage_ability = wp_get_ability( 'agentpress/stage-navigation-change' );
$nav_ability   = wp_get_ability( 'agentpress/get-navigation' );
$service       = new ApprovalService();
$validator     = new SchemaValidator();
$catalog       = AbilityCatalog::all();
agentpress_ap023_assert( is_object( $stage_ability ) && is_object( $nav_ability ), 'AP-023 Abilities are not registered.' );

$add_input = function ( $key ) use ( $services_id ) {
	return array(
		'location'       => 'primary',
		'operation'      => 'add',
		'item'           => array( 'object_type' => 'page', 'object_id' => $services_id, 'parent_item_id' => 0, 'position' => 3, 'label' => 'AP023 Services' ),
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
$applied_row  = ( new \AgentPress\Changes\ChangeRepository() )->find( $change_id );
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
global $wpdb;
$wpdb->query( $wpdb->prepare( 'UPDATE %i SET expires_at = %s WHERE id = %d', $wpdb->prefix . 'agentpress_changes', gmdate( 'Y-m-d H:i:s', time() - 3600 ), $expired_change ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
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
$audit_delta = (int) $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM %i WHERE actor_type = %s', $wpdb->prefix . 'agentpress_audit_events', 'human' ) ) - $audit_before; // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
agentpress_ap023_assert( $audit_delta >= 2, 'Human audit records were not written.' );

// Clean up synthetic fixtures.
set_theme_mod( 'nav_menu_locations', $installed );
wp_delete_nav_menu( $menu_id );
foreach ( array_merge( array_values( $page_ids ), array( $services_id ) ) as $page_id ) {
	wp_delete_post( $page_id, true );
}
foreach ( $users as $user_id ) {
	wp_delete_user( $user_id );
}
foreach ( array( 'agentpress_changes', 'agentpress_change_sets', 'agentpress_audit_events' ) as $suffix ) {
	$wpdb->query( $wpdb->prepare( 'DELETE FROM %i', $wpdb->prefix . $suffix ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
}
unregister_nav_menu( 'primary' );

echo wp_json_encode(
	array(
		'approve_applied'       => true,
		'approver_recorded'     => true,
		'reapply_conflict'      => true,
		'reject_no_mutation'    => true,
		'rejector_recorded'     => true,
		'expired_denied'        => true,
		'stale_conflict'        => true,
		'stale_no_mutation'     => true,
		'role_denials'          => 2,
		'human_audit_records'   => $audit_delta,
	)
) . "\n";
