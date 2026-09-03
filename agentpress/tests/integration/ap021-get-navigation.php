<?php
/**
 * AP-021 bounded classic-navigation read matrix.
 *
 * @package AgentPress
 */

use AgentPress\Abilities\AbilityCatalog;
use AgentPress\Schemas\SchemaValidator;

/** @param bool $condition Condition. @param string $message Message. @return void */
function agentpress_ap021_assert( $condition, $message ) {
	if ( ! $condition ) {
		fwrite( STDERR, "AP-021 assertion failed: {$message}\n" );
		exit( 1 );
	}
}

/** @param string $login Login. @param string $role Role. @return int */
function agentpress_ap021_user( $login, $role ) {
	$existing = get_user_by( 'login', $login );
	$id       = $existing ? (int) $existing->ID : wp_create_user( $login, wp_generate_password( 24 ), $login . '@private.example.test' );
	$user     = new WP_User( $id );
	$user->set_role( $role );
	return $id;
}

/** @param int $menu_id Menu ID. @param int $object_id Page ID. @param string $label Label. @param int $position Position. @param int $parent Parent item. @return int */
function agentpress_ap021_menu_item( $menu_id, $object_id, $label, $position, $parent = 0 ) {
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
	agentpress_ap021_assert( ! is_wp_error( $result ) && (int) $result > 0, 'Menu item creation failed.' );
	return (int) $result;
}

/** @param int $menu_id Menu ID. @param int $item_id Item ID. @param int $object_id Page ID. @param string $label Label. @param int $position Position. @param int $parent Parent item. @return void */
function agentpress_ap021_update_item( $menu_id, $item_id, $object_id, $label, $position, $parent = 0 ) {
	$result = wp_update_nav_menu_item(
		$menu_id,
		$item_id,
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
	agentpress_ap021_assert( ! is_wp_error( $result ), 'Menu item update failed.' );
}

/** @param mixed $result Candidate result. @param string $code Expected code. @param string $message Assertion message. @return void */
function agentpress_ap021_error( $result, $code, $message ) {
	agentpress_ap021_assert( is_wp_error( $result ) && $code === $result->get_error_code(), $message );
}

/** @return array<string, mixed> */
function agentpress_ap021_target_state() {
	global $wpdb;
	$items = $wpdb->get_results(
		"SELECT ID, post_author, post_date, post_date_gmt, post_content, post_title, post_status, post_name, post_modified, post_modified_gmt, menu_order FROM {$wpdb->posts} WHERE post_type = 'nav_menu_item' ORDER BY ID ASC",
		ARRAY_A
	); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	return array(
		'locations'   => get_theme_mod( 'nav_menu_locations', array() ),
		'items'       => $items,
		'change_sets' => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}agentpress_change_sets" ), // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		'changes'     => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}agentpress_changes" ), // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		'audit'       => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}agentpress_audit_events" ), // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	);
}

/** @param array<int, object> $items Filtered items. @return array<int, object> */
function agentpress_ap021_oversized_items( $items ) {
	return empty( $items ) ? $items : array_fill( 0, 201, $items[0] );
}

/** @param array<int, object> $items Filtered items. @return array<int, object> */
function agentpress_ap021_malformed_items( $items ) {
	if ( ! empty( $items ) ) {
		$items[0]->url = 'not a valid URL PRIVATE-SENTINEL-AP021';
	}
	return $items;
}

$original_locations = get_theme_mod( 'nav_menu_locations', array() );
foreach ( $original_locations as $location => $assigned_menu_id ) {
	$assigned_menu = wp_get_nav_menu_object( (int) $assigned_menu_id );
	if ( ! is_object( $assigned_menu ) || 0 === strpos( $assigned_menu->name, 'AP021 ' ) ) {
		unset( $original_locations[ $location ] );
	}
}
set_theme_mod( 'nav_menu_locations', $original_locations );
register_nav_menus(
	array(
		'primary'   => 'Synthetic AP-021 Primary',
		'secondary' => 'Synthetic AP-021 Secondary',
	)
);

foreach ( wp_get_nav_menus( array( 'hide_empty' => false ) ) as $stale_menu ) {
	if ( 0 === strpos( $stale_menu->name, 'AP021 ' ) ) {
		wp_delete_nav_menu( $stale_menu->term_id );
	}
}
foreach ( array( 'administrator', 'author', 'subscriber', 'mutated_author' ) as $role ) {
	$existing = get_user_by( 'login', 'agentpress_ap021_' . $role );
	if ( is_object( $existing ) ) {
		wp_delete_user( $existing->ID );
	}
}
foreach ( get_posts( array( 'post_type' => 'page', 'post_status' => 'any', 's' => 'AP021', 'posts_per_page' => -1 ) ) as $stale_page ) {
	wp_delete_post( $stale_page->ID, true );
}

$users = array(
	'administrator' => agentpress_ap021_user( 'agentpress_ap021_administrator', 'administrator' ),
	'author'        => agentpress_ap021_user( 'agentpress_ap021_author', 'author' ),
	'subscriber'    => agentpress_ap021_user( 'agentpress_ap021_subscriber', 'subscriber' ),
	'mutated_author' => agentpress_ap021_user( 'agentpress_ap021_mutated_author', 'author' ),
);
$mutated_author = new WP_User( $users['mutated_author'] );
$mutated_author->add_cap( 'edit_theme_options' );

$page_ids = array();
foreach ( array( 'Home', 'About', 'Blog', 'Contact' ) as $label ) {
	$page_id = wp_insert_post(
		array(
			'post_type'   => 'page',
			'post_status' => 'publish',
			'post_title'  => 'AP021 ' . $label,
			'post_name'   => 'ap021-' . strtolower( $label ),
			'post_author' => $users['administrator'],
		)
	);
	agentpress_ap021_assert( ! is_wp_error( $page_id ) && (int) $page_id > 0, 'Fixture page creation failed.' );
	$page_ids[ $label ] = (int) $page_id;
}
$private_page = wp_insert_post(
	array(
		'post_type'    => 'page',
		'post_status'  => 'private',
		'post_title'   => 'PRIVATE-SENTINEL-AP021',
		'post_content' => 'PRIVATE-BODY-SENTINEL-AP021',
		'post_author'  => $users['administrator'],
	)
);

$menu_id = wp_create_nav_menu( 'AP021 Primary Menu' );
agentpress_ap021_assert( ! is_wp_error( $menu_id ), 'Primary menu creation failed.' );
$menu_id = (int) $menu_id;
$item_ids = array();
$position = 1;
foreach ( $page_ids as $label => $page_id ) {
	$item_ids[ $label ] = agentpress_ap021_menu_item( $menu_id, $page_id, $label, $position );
	++$position;
}
$locations            = $original_locations;
$locations['primary'] = $menu_id;
set_theme_mod( 'nav_menu_locations', $locations );

wp_set_current_user( $users['administrator'] );
$ability   = wp_get_ability( 'agentpress/get-navigation' );
$catalog   = AbilityCatalog::all();
$schema    = $catalog['agentpress/get-navigation']['output_schema'];
$validator = new SchemaValidator();

$target_before = agentpress_ap021_target_state();
$result        = $ability->execute( array() );
$target_after  = agentpress_ap021_target_state();
agentpress_ap021_assert( $target_before === $target_after, 'Reading navigation mutated WordPress or AgentPress state.' );
agentpress_ap021_assert( is_array( $result ) && true === $validator->validate_output( $result, $schema ), 'Primary result failed its fixed output schema.' );
agentpress_ap021_assert( 'classic-menu' === $result['data']['adapter'] && 'primary' === $result['data']['location'] && $menu_id === $result['data']['menu_id'], 'Adapter/location/menu identity mismatch.' );
agentpress_ap021_assert( 'AP021 Primary Menu' === $result['data']['menu_name'], 'Menu name mismatch.' );
agentpress_ap021_assert( array( 'Home', 'About', 'Blog', 'Contact' ) === array_column( $result['data']['items'], 'label' ), 'Fixture order mismatch.' );
agentpress_ap021_assert( array( 1, 2, 3, 4 ) === array_column( $result['data']['items'], 'position' ), 'Fixture positions mismatch.' );
agentpress_ap021_assert( array( 0, 0, 0, 0 ) === array_column( $result['data']['items'], 'parent_item_id' ), 'Fixture hierarchy mismatch.' );
agentpress_ap021_assert( array_values( $page_ids ) === array_column( $result['data']['items'], 'object_id' ), 'Fixture destinations mismatch.' );
agentpress_ap021_assert( array( 'item_id', 'parent_item_id', 'position', 'label', 'type', 'object', 'object_id', 'url' ) === array_keys( $result['data']['items'][0] ), 'Navigation item field allowlist mismatch.' );

$baseline_hash = $result['data']['state_hash'];
$repeat        = $ability->execute( array( 'location' => 'primary' ) );
agentpress_ap021_assert( $baseline_hash === $repeat['data']['state_hash'], 'Unchanged navigation hash was not deterministic.' );

agentpress_ap021_update_item( $menu_id, $item_ids['About'], $page_ids['About'], 'About Us', 2 );
$relabel = $ability->execute( array() );
agentpress_ap021_assert( $baseline_hash !== $relabel['data']['state_hash'], 'Relabel did not change the state hash.' );

agentpress_ap021_update_item( $menu_id, $item_ids['Blog'], $page_ids['Blog'], 'Blog', 3, $item_ids['About'] );
$move = $ability->execute( array() );
agentpress_ap021_assert( $relabel['data']['state_hash'] !== $move['data']['state_hash'], 'Move did not change the state hash.' );
agentpress_ap021_assert( $item_ids['About'] === $move['data']['items'][2]['parent_item_id'], 'Moved hierarchy was not normalized.' );

$added_item = agentpress_ap021_menu_item( $menu_id, $page_ids['Home'], 'Added Home', 5 );
$add        = $ability->execute( array() );
agentpress_ap021_assert( $move['data']['state_hash'] !== $add['data']['state_hash'], 'Add did not change the state hash.' );

wp_delete_post( $item_ids['Contact'], true );
$remove = $ability->execute( array() );
agentpress_ap021_assert( $add['data']['state_hash'] !== $remove['data']['state_hash'], 'Remove did not change the state hash.' );

$second_menu = wp_create_nav_menu( 'AP021 Secondary Menu' );
agentpress_ap021_assert( ! is_wp_error( $second_menu ), 'Secondary menu creation failed.' );
$second_menu = (int) $second_menu;
agentpress_ap021_menu_item( $second_menu, $page_ids['Home'], 'Home', 1 );
$locations['primary'] = $second_menu;
set_theme_mod( 'nav_menu_locations', $locations );
$reassigned = $ability->execute( array() );
agentpress_ap021_assert( $remove['data']['state_hash'] !== $reassigned['data']['state_hash'] && $second_menu === $reassigned['data']['menu_id'], 'Location reassignment did not change state identity.' );

$locations['primary'] = 0;
set_theme_mod( 'nav_menu_locations', $locations );
agentpress_ap021_error( $ability->execute( array() ), 'AP_NAVIGATION_NOT_FOUND', 'Unassigned location did not fail closed.' );
agentpress_ap021_error( $ability->execute( array( 'location' => 'unknown' ) ), 'AP_UNSUPPORTED_NAVIGATION', 'Unknown location did not fail as unsupported.' );

$locations['primary'] = $menu_id;
set_theme_mod( 'nav_menu_locations', $locations );
add_filter( 'wp_get_nav_menu_items', 'agentpress_ap021_oversized_items' );
agentpress_ap021_error( $ability->execute( array() ), 'AP_UNSUPPORTED_NAVIGATION', 'Oversized menu did not fail closed.' );
remove_filter( 'wp_get_nav_menu_items', 'agentpress_ap021_oversized_items' );

add_filter( 'wp_get_nav_menu_items', 'agentpress_ap021_malformed_items' );
$malformed = $ability->execute( array() );
agentpress_ap021_error( $malformed, 'AP_UNSUPPORTED_NAVIGATION', 'Malformed destination did not fail closed.' );
agentpress_ap021_assert( false === strpos( wp_json_encode( $malformed->get_error_data() ), 'PRIVATE-SENTINEL-AP021' ), 'Malformed destination leaked through error details.' );
remove_filter( 'wp_get_nav_menu_items', 'agentpress_ap021_malformed_items' );

agentpress_ap021_menu_item( $menu_id, (int) $private_page, 'PRIVATE-SENTINEL-AP021', 6 );
wp_set_current_user( $users['mutated_author'] );
agentpress_ap021_assert( true === $ability->check_permissions( array( 'location' => 'primary' ) ), 'Capability-mutated Author did not pass coarse navigation permission.' );
$private_denial = $ability->execute( array() );
agentpress_ap021_error( $private_denial, 'AP_PERMISSION_DENIED', 'Unreadable private destination did not fail closed.' );
agentpress_ap021_assert( false === strpos( wp_json_encode( $private_denial->get_error_data() ), 'PRIVATE-SENTINEL-AP021' ), 'Private destination leaked through error details.' );

foreach ( array( 'author', 'subscriber' ) as $role ) {
	wp_set_current_user( $users[ $role ] );
	agentpress_ap021_error( $ability->check_permissions( array( 'location' => 'primary' ) ), 'AP_PERMISSION_DENIED', $role . ' permission did not fail closed.' );
}
wp_set_current_user( 0 );
agentpress_ap021_error( $ability->check_permissions( array( 'location' => 'primary' ) ), 'AP_NOT_AUTHENTICATED', 'Logged-out permission did not fail closed.' );

set_theme_mod( 'nav_menu_locations', $original_locations );
wp_delete_nav_menu( $menu_id );
wp_delete_nav_menu( $second_menu );
wp_delete_post( $added_item, true );
foreach ( array_merge( array_values( $page_ids ), array( (int) $private_page ) ) as $page_id ) {
	wp_delete_post( $page_id, true );
}
foreach ( $users as $user_id ) {
	wp_delete_user( $user_id );
}
unregister_nav_menu( 'primary' );
unregister_nav_menu( 'secondary' );

echo wp_json_encode(
	array(
		'fixture_items'             => 4,
		'schema_validations'        => 1,
		'deterministic_hashes'      => 1,
		'material_hash_changes'     => 5,
		'missing_unsupported_cases' => 3,
		'private_malformed_denials' => 2,
		'role_denials'              => 3,
		'read_target_mutations'     => 0,
	)
) . "\n";
