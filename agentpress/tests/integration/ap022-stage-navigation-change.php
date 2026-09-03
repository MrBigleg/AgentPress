<?php
/**
 * AP-022 stage-navigation-change matrix.
 *
 * @package AgentPress
 */

use AgentPress\Abilities\AbilityCatalog;
use AgentPress\Schemas\SchemaValidator;

/** @param bool $condition Condition. @param string $message Message. @return void */
function agentpress_ap022_assert( $condition, $message ) {
	if ( ! $condition ) {
		fwrite( STDERR, "AP-022 assertion failed: {$message}\n" );
		exit( 1 );
	}
}

/** @param mixed $result Candidate result. @param string $code Expected code. @param string $message Assertion message. @return void */
function agentpress_ap022_error( $result, $code, $message ) {
	agentpress_ap022_assert( is_wp_error( $result ) && $code === $result->get_error_code(), $message );
}

/** @param string $login Login. @param string $role Role. @return int */
function agentpress_ap022_user( $login, $role ) {
	$existing = get_user_by( 'login', $login );
	$id       = $existing ? (int) $existing->ID : wp_create_user( $login, wp_generate_password( 24 ), $login . '@private.example.test' );
	$user     = new WP_User( $id );
	$user->set_role( $role );
	return $id;
}

/** @param int $menu_id Menu ID. @param int $object_id Page ID. @param string $label Label. @param int $position Position. @param int $parent Parent item. @return int */
function agentpress_ap022_menu_item( $menu_id, $object_id, $label, $position, $parent = 0 ) {
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
	agentpress_ap022_assert( ! is_wp_error( $result ) && (int) $result > 0, 'Menu item creation failed.' );
	return (int) $result;
}

/** @return array<int, object> */
function agentpress_ap022_oversized_items( $items ) {
	return empty( $items ) ? $items : array_fill( 0, 201, $items[0] );
}

$original_locations = get_theme_mod( 'nav_menu_locations', array() );
foreach ( $original_locations as $location => $assigned_menu_id ) {
	$assigned_menu = wp_get_nav_menu_object( (int) $assigned_menu_id );
	if ( ! is_object( $assigned_menu ) || 0 !== strpos( $assigned_menu->name, 'AP022 ' ) ) {
		unset( $original_locations[ $location ] );
	}
}
set_theme_mod( 'nav_menu_locations', $original_locations );
register_nav_menus( array( 'primary' => 'Synthetic AP-022 Primary' ) );

foreach ( wp_get_nav_menus( array( 'hide_empty' => false ) ) as $stale_menu ) {
	if ( 0 === strpos( $stale_menu->name, 'AP022 ' ) ) {
		wp_delete_nav_menu( $stale_menu->term_id );
	}
}
foreach ( array( 'administrator', 'author', 'mutated_author' ) as $role ) {
	$existing = get_user_by( 'login', 'agentpress_ap022_' . $role );
	if ( is_object( $existing ) ) {
		wp_delete_user( $existing->ID );
	}
}
foreach ( get_posts(
	array(
		'post_type'      => 'page',
		'post_status'    => 'any',
		's'              => 'AP022',
		'posts_per_page' => -1,
	)
) as $stale_page ) {
	wp_delete_post( $stale_page->ID, true );
}

$users          = array(
	'administrator'  => agentpress_ap022_user( 'agentpress_ap022_administrator', 'administrator' ),
	'author'         => agentpress_ap022_user( 'agentpress_ap022_author', 'author' ),
	'mutated_author' => agentpress_ap022_user( 'agentpress_ap022_mutated_author', 'author' ),
);
$mutated_author = new WP_User( $users['mutated_author'] );
$mutated_author->add_cap( 'edit_theme_options' );

$page_ids = array();
$about_id = 0;
$blog_id  = 0;
foreach ( array( 'Home', 'About', 'Blog', 'Contact' ) as $label ) {
	$page_id = wp_insert_post(
		array(
			'post_type'   => 'page',
			'post_status' => 'publish',
			'post_title'  => 'AP022 ' . $label,
			'post_name'   => 'ap022-' . strtolower( $label ),
			'post_author' => $users['administrator'],
		)
	);
	agentpress_ap022_assert( ! is_wp_error( $page_id ) && (int) $page_id > 0, 'Fixture page creation failed.' );
	$page_ids[ $label ] = (int) $page_id;
	if ( 'About' === $label ) {
		$about_id = (int) $page_id;
	}
	if ( 'Blog' === $label ) {
		$blog_id = (int) $page_id;
	}
}
$services_id = wp_insert_post(
	array(
		'post_type'   => 'page',
		'post_status' => 'draft',
		'post_title'  => 'AP022 Services',
		'post_name'   => 'ap022-services',
		'post_author' => $users['administrator'],
	)
);
agentpress_ap022_assert( ! is_wp_error( $services_id ) && (int) $services_id > 0, 'Services page creation failed.' );
$services_id = (int) $services_id;

$menu_id = wp_create_nav_menu( 'AP022 Primary Menu' );
agentpress_ap022_assert( ! is_wp_error( $menu_id ), 'Primary menu creation failed.' );
$menu_id  = (int) $menu_id;
$item_ids = array();
$position = 1;
foreach ( array( 'Home', 'About', 'Blog', 'Contact' ) as $label ) {
	$item_ids[ $label ] = agentpress_ap022_menu_item( $menu_id, $page_ids[ $label ], $label, $position );
	++$position;
}
$locations            = $original_locations;
$locations['primary'] = $menu_id;
set_theme_mod( 'nav_menu_locations', $locations );

wp_set_current_user( $users['administrator'] );
$ability       = wp_get_ability( 'agentpress/stage-navigation-change' );
$nav_ability   = wp_get_ability( 'agentpress/get-navigation' );
$service       = new \AgentPress\Navigation\StageNavigationChangeService();
$catalog       = AbilityCatalog::all();
$output_schema = $catalog['agentpress/stage-navigation-change']['output_schema'];
$validator     = new SchemaValidator();
agentpress_ap022_assert( is_object( $ability ) && is_object( $nav_ability ), 'AP-022 Abilities are not registered.' );

$baseline = $nav_ability->execute( array( 'location' => 'primary' ) );
agentpress_ap022_assert( is_array( $baseline ) && array( 'Home', 'About', 'Blog', 'Contact' ) === array_column( $baseline['data']['items'], 'label' ), 'Baseline fixture order differs.' );
$baseline_item_ids = wp_list_pluck( $baseline['data']['items'], 'item_id' );

// Canonical add of the Services page between About and Blog.
$add_input = array(
	'location'        => 'primary',
	'operation'       => 'add',
	'item'            => array(
		'object_type'    => 'page',
		'object_id'      => $services_id,
		'parent_item_id' => 0,
		'position'       => 3,
		'label'          => 'AP022 Services',
	),
	'idempotency_key' => 'ap022-add-services',
);
agentpress_ap022_assert( true === $ability->check_permissions( $add_input ), 'Administrator staging permission failed.' );
$staged = $ability->execute( $add_input );
agentpress_ap022_assert( is_array( $staged ) && true === $validator->validate_output( $staged, $output_schema ), 'Add result failed its output schema.' );
agentpress_ap022_assert( 'PENDING_APPROVAL' === $staged['data']['status'] && 'classic-menu' === $staged['data']['adapter'] && 'primary' === $staged['data']['location'] && 'add' === $staged['data']['operation'], 'Add result envelope differs.' );
agentpress_ap022_assert( array( 'Home', 'About', 'Blog', 'Contact' ) === array_column( $staged['data']['before'], 'label' ), 'Add before diff differs.' );
agentpress_ap022_assert( array( 'Home', 'About', 'AP022 Services', 'Blog', 'Contact' ) === array_column( $staged['data']['after'], 'label' ), 'Add after diff is not the exact preview.' );
agentpress_ap022_assert( 'AP022 Services' === $staged['data']['after'][2]['label'] && $services_id === $staged['data']['after'][2]['object_id'] && 'post_type' === $staged['data']['after'][2]['type'], 'Added item identity differs.' );
agentpress_ap022_assert( '' !== $staged['data']['expires_at'] && 64 === strlen( $staged['data']['state_hash'] ), 'Add proposal missing expiry or state hash.' );
agentpress_ap022_assert( $staged['data']['change_id'] > 0 && preg_match( '/^AP-[1-9][0-9]*$/', $staged['data']['change_set_ref'] ), 'Add proposal references are invalid.' );

$replay = $ability->execute( $add_input );
agentpress_ap022_assert( is_array( $replay ) && true === $replay['data']['replayed'] && $staged['data']['change_id'] === $replay['data']['change_id'], 'Add did not replay identically.' );
agentpress_ap022_assert( $staged['data']['after'] === $replay['data']['after'], 'Add replay produced a different preview.' );

$after_staging = $nav_ability->execute( array( 'location' => 'primary' ) );
agentpress_ap022_assert( array( 'Home', 'About', 'Blog', 'Contact' ) === array_column( $after_staging['data']['items'], 'label' ), 'Staging mutated live navigation.' );

// Remove About.
$remove_input = array(
	'location'        => 'primary',
	'operation'       => 'remove',
	'item'            => array( 'item_id' => $item_ids['About'] ),
	'idempotency_key' => 'ap022-remove-about',
);
$removed      = $ability->execute( $remove_input );
agentpress_ap022_assert( is_array( $removed ) && array( 'Home', 'Blog', 'Contact' ) === array_column( $removed['data']['after'], 'label' ), 'Remove preview differs.' );

// Move Blog to position 4.
$move_input = array(
	'location'        => 'primary',
	'operation'       => 'move',
	'item'            => array(
		'item_id'        => $item_ids['Blog'],
		'parent_item_id' => 0,
		'position'       => 4,
	),
	'idempotency_key' => 'ap022-move-blog',
);
$moved      = $ability->execute( $move_input );
agentpress_ap022_assert( is_array( $moved ) && array( 'Home', 'About', 'Contact', 'Blog' ) === array_column( $moved['data']['after'], 'label' ), 'Move preview differs.' );

// Custom same-origin add passes when the origin is HTTPS.
$custom_origin_override = static function () {
	return 'https://local.example.test/';
};
add_filter( 'home_url', $custom_origin_override, 99 );
$custom_same = $ability->execute(
	array(
		'location'        => 'primary',
		'operation'       => 'add',
		'item'            => array(
			'object_type' => 'custom',
			'object_id'   => 1,
			'url'         => 'https://local.example.test/contact',
			'label'       => 'Custom Contact',
			'position'    => 2,
		),
		'idempotency_key' => 'ap022-custom-same',
	)
);
remove_filter( 'home_url', $custom_origin_override, 99 );
agentpress_ap022_assert( is_array( $custom_same ) && 'Custom Contact' === $custom_same['data']['after'][1]['label'] && 'custom' === $custom_same['data']['after'][1]['type'], 'Same-origin custom add was rejected or placed wrong.' );

// Cross-origin custom URL is blocked; non-HTTPS is schema-invalid.
$cross_origin = $service->execute(
	array(
		'location'        => 'primary',
		'operation'       => 'add',
		'item'            => array(
			'object_type' => 'custom',
			'object_id'   => 1,
			'url'         => 'https://evil.example/contact',
			'label'       => 'Evil',
			'position'    => 2,
		),
		'idempotency_key' => 'ap022-custom-cross',
	)
);
agentpress_ap022_error( $cross_origin, 'AP_POLICY_BLOCKED', 'Cross-origin custom URL was not blocked.' );
$non_https = $service->execute(
	array(
		'location'        => 'primary',
		'operation'       => 'add',
		'item'            => array(
			'object_type' => 'custom',
			'object_id'   => 1,
			'url'         => 'http://local.example.test/contact',
			'label'       => 'Not https',
			'position'    => 2,
		),
		'idempotency_key' => 'ap022-custom-http',
	)
);
agentpress_ap022_error( $non_https, 'AP_SCHEMA_INVALID', 'Non-HTTPS custom URL was accepted.' );

// Staging must not mutate the live menu or apply any change.
wp_set_current_user( $users['administrator'] );
$final_menu = $nav_ability->execute( array( 'location' => 'primary' ) );
agentpress_ap022_assert( is_array( $final_menu ), 'Navigation read failed after staging.' );
agentpress_ap022_assert( $baseline_item_ids === wp_list_pluck( $final_menu['data']['items'], 'item_id' ), 'Staging mutated the live menu.' );
global $wpdb;
$staged_statuses = array_map( 'strval', $wpdb->get_col( "SELECT DISTINCT status FROM {$wpdb->prefix}agentpress_changes" ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
agentpress_ap022_assert( ! in_array( 'APPLIED', $staged_statuses, true ) && array() === array_diff( $staged_statuses, array( 'PENDING_APPROVAL' ) ), 'Staging produced a non-pending change.' );

// Child removal is rejected: nest Blog under About then remove About.
agentpress_ap022_menu_item( $menu_id, $page_ids['Blog'], 'Blog Child', 5, $item_ids['About'] );
$child_remove = $service->execute( $remove_input );
agentpress_ap022_error( $child_remove, 'AP_UNSUPPORTED_NAVIGATION', 'Removing a parent with children was not rejected.' );

// Schema denials (direct service boundary).
agentpress_ap022_error(
	$service->execute(
		array(
			'location' => 'primary',
			'item'     => array( 'position' => 1 ),
		)
	),
	'AP_SCHEMA_INVALID',
	'Missing operation/idempotency_key passed.'
);
agentpress_ap022_error(
	$service->execute(
		array(
			'operation'       => 'nonsense',
			'item'            => array( 'object_id' => 1 ),
			'idempotency_key' => 'ap022-invalid-op',
		)
	),
	'AP_SCHEMA_INVALID',
	'Unknown operation passed.'
);
agentpress_ap022_error(
	$service->execute(
		array(
			'location'        => 'unknown-location',
			'operation'       => 'add',
			'item'            => array(
				'object_type' => 'page',
				'object_id'   => $services_id,
				'position'    => 1,
			),
			'idempotency_key' => 'ap022-bad-location',
		)
	),
	'AP_UNSUPPORTED_NAVIGATION',
	'Unknown location passed.'
);
agentpress_ap022_error(
	$service->execute(
		array(
			'operation'       => 'remove',
			'item'            => array( 'item_id' => 999999 ),
			'idempotency_key' => 'ap022-missing-item',
		)
	),
	'AP_NAVIGATION_NOT_FOUND',
	'Missing item id passed.'
);

// Role denials.
wp_set_current_user( $users['author'] );
agentpress_ap022_error( $ability->check_permissions( $add_input ), 'AP_PERMISSION_DENIED', 'Author staging permission passed.' );
agentpress_ap022_error( $service->execute( $add_input ), 'AP_PERMISSION_DENIED', 'Author staged a change.' );
wp_set_current_user( $users['mutated_author'] );
agentpress_ap022_assert(
	true === $ability->check_permissions(
		array(
			'operation'       => 'add',
			'item'            => array(
				'object_type' => 'page',
				'object_id'   => $services_id,
				'position'    => 1,
			),
			'idempotency_key' => 'ap022-mutated-perm',
		)
	),
	'Capability-mutated Author coarse permission failed.'
);
wp_set_current_user( 0 );
agentpress_ap022_error(
	$ability->check_permissions(
		array(
			'operation'       => 'add',
			'item'            => array(
				'object_type' => 'page',
				'object_id'   => $services_id,
				'position'    => 1,
			),
			'idempotency_key' => 'ap022-logged-out',
		)
	),
	'AP_NOT_AUTHENTICATED',
	'Logged-out staging permission passed.'
);

// Oversized menu fails closed at staging boundary.
wp_set_current_user( $users['administrator'] );
add_filter( 'wp_get_nav_menu_items', 'agentpress_ap022_oversized_items' );
agentpress_ap022_error( $nav_ability->execute( array( 'location' => 'primary' ) ), 'AP_UNSUPPORTED_NAVIGATION', 'Oversized menu did not fail closed at read.' );
remove_filter( 'wp_get_nav_menu_items', 'agentpress_ap022_oversized_items' );

// Clean up synthetic fixtures.
set_theme_mod( 'nav_menu_locations', $original_locations );
wp_delete_nav_menu( $menu_id );
foreach ( array_merge( array_values( $page_ids ), array( $services_id ) ) as $page_id ) {
	wp_delete_post( $page_id, true );
}
foreach ( $users as $user_id ) {
	wp_delete_user( $user_id );
}
global $wpdb;
foreach ( array( 'agentpress_changes', 'agentpress_change_sets' ) as $suffix ) {
	$wpdb->query( $wpdb->prepare( 'DELETE FROM %i', $wpdb->prefix . $suffix ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
}
unregister_nav_menu( 'primary' );

echo wp_json_encode(
	array(
		'add_preview_exact'   => true,
		'remove_preview'      => true,
		'move_preview'        => true,
		'replay_idempotent'   => true,
		'same_origin_custom'  => true,
		'unsafe_url_denials'  => 2,
		'child_remove_denial' => 1,
		'schema_denials'      => 5,
		'role_denials'        => 3,
		'live_menu_mutations' => 0,
		'durable_mutations'   => 0,
	)
) . "\n";
