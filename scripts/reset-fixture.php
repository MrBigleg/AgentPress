<?php
/**
 * AgentPress Deterministic Fixture and Reset Script.
 *
 * Configures a deterministic small-business WordPress site state for testing,
 * live client verification, and competition reproducibility.
 *
 * @package AgentPress
 */

use AgentPress\Navigation\ClassicMenuAdapter;
use AgentPress\Storage\Migrator;

if ( ! defined( 'ABSPATH' ) ) {
	fwrite( STDERR, "This script must be run within a WordPress environment.\n" );
	exit( 1 );
}

/**
 * Assert a fixture condition or terminate with failure message.
 *
 * @param bool   $condition Condition to assert.
 * @param string $message   Failure message.
 * @return void
 */
function agentpress_fixture_assert( $condition, $message ) {
	if ( ! $condition ) {
		fwrite( STDERR, 'FIXTURE RESET ERROR: ' . $message . "\n" );
		exit( 1 );
	}
}

/**
 * Ensure a user exists with the exact specified credentials and role.
 *
 * @param string $login    Username.
 * @param string $role     WordPress role.
 * @param string $email    User email.
 * @param string $password Password.
 * @param string $display  Display name.
 * @return int User ID.
 */
function agentpress_fixture_ensure_user( $login, $role, $email, $password, $display ) {
	$user = get_user_by( 'login', $login );
	if ( $user ) {
		$user_id = $user->ID;
		wp_update_user(
			array(
				'ID'           => $user_id,
				'user_email'   => $email,
				'user_pass'    => $password,
				'display_name' => $display,
				'role'         => $role,
			)
		);
	} else {
		$user_id = wp_create_user( $login, $password, $email );
		agentpress_fixture_assert( ! is_wp_error( $user_id ), 'Failed creating user ' . $login . ': ' . ( is_wp_error( $user_id ) ? $user_id->get_error_message() : '' ) );
		$created = new WP_User( $user_id );
		$created->set_role( $role );
		wp_update_user(
			array(
				'ID'           => $user_id,
				'display_name' => $display,
			)
		);
	}
	return (int) $user_id;
}

/**
 * Ensure a category exists with the specified slug, name, and description.
 *
 * @param string $slug        Category slug.
 * @param string $name        Category name.
 * @param string $description Category description.
 * @return int Term ID.
 */
function agentpress_fixture_ensure_category( $slug, $name, $description ) {
	$term = get_term_by( 'slug', $slug, 'category' );
	if ( $term ) {
		wp_update_term(
			$term->term_id,
			'category',
			array(
				'name'        => $name,
				'description' => $description,
			)
		);
		return (int) $term->term_id;
	}

	$created = wp_insert_term(
		$name,
		'category',
		array(
			'slug'        => $slug,
			'description' => $description,
		)
	);
	agentpress_fixture_assert( ! is_wp_error( $created ) && isset( $created['term_id'] ), 'Failed creating category ' . $slug );
	return (int) $created['term_id'];
}

/**
 * Ensure a page exists with fixed slug, title, content, and author.
 *
 * @param string $slug    Page slug.
 * @param string $title   Page title.
 * @param string $content Page content.
 * @param int    $author  Author ID.
 * @return int Page ID.
 */
function agentpress_fixture_ensure_page( $slug, $title, $content, $author ) {
	$existing = get_page_by_path( $slug, OBJECT, 'page' );
	if ( $existing ) {
		wp_update_post(
			array(
				'ID'           => $existing->ID,
				'post_title'   => $title,
				'post_content' => $content,
				'post_status'  => 'publish',
				'post_author'  => $author,
			)
		);
		return (int) $existing->ID;
	}

	$id = wp_insert_post(
		array(
			'post_name'    => $slug,
			'post_title'   => $title,
			'post_content' => $content,
			'post_status'  => 'publish',
			'post_type'    => 'page',
			'post_author'  => $author,
		)
	);
	agentpress_fixture_assert( ! is_wp_error( $id ) && (int) $id > 0, 'Failed creating page ' . $slug );
	return (int) $id;
}

/**
 * Ensure a post exists with fixed slug, title, content, author, and category.
 *
 * @param string $slug        Post slug.
 * @param string $title       Post title.
 * @param string $content     Post content.
 * @param int    $author      Author ID.
 * @param int    $category_id Category term ID.
 * @return int Post ID.
 */
function agentpress_fixture_ensure_post( $slug, $title, $content, $author, $category_id ) {
	$existing = get_posts(
		array(
			'name'        => $slug,
			'post_type'   => 'post',
			'post_status' => 'any',
			'numberposts' => 1,
		)
	);

	if ( ! empty( $existing ) ) {
		$post_id = $existing[0]->ID;
		wp_update_post(
			array(
				'ID'           => $post_id,
				'post_title'   => $title,
				'post_content' => $content,
				'post_status'  => 'publish',
				'post_author'  => $author,
			)
		);
		wp_set_post_categories( $post_id, array( $category_id ) );
		return (int) $post_id;
	}

	$id = wp_insert_post(
		array(
			'post_name'     => $slug,
			'post_title'    => $title,
			'post_content'  => $content,
			'post_status'   => 'publish',
			'post_type'     => 'post',
			'post_author'   => $author,
			'post_category' => array( $category_id ),
		)
	);
	agentpress_fixture_assert( ! is_wp_error( $id ) && (int) $id > 0, 'Failed creating post ' . $slug );
	return (int) $id;
}

global $wpdb;

// 1. Activate classic theme Twenty Twenty-One if installed.
if ( wp_get_theme( 'twentytwentyone' )->exists() && 'twentytwentyone' !== get_stylesheet() ) {
	switch_theme( 'twentytwentyone' );
}

// 2. Configure site identity and reading options.
update_option( 'blogname', 'Acme Web & Digital Studio' );
update_option( 'blogdescription', 'Modern web design, SEO, and digital consulting' );
update_option( 'show_on_front', 'page' );

// 3. Purge dynamic test posts / pages from prior runs.
// Specifically remove any "Services" page created by canonical workflow runs.
$services_page = get_page_by_path( 'services', OBJECT, 'page' );
if ( $services_page ) {
	wp_delete_post( $services_page->ID, true );
}
// Delete posts with titles matching "Services" or "AP0" synthetic test prefixes.
$test_posts = $wpdb->get_results(
	"SELECT ID FROM {$wpdb->posts} WHERE post_title LIKE 'AP0%' OR post_title LIKE 'PRIVATE-SENTINEL%' OR post_title = 'Services' OR post_name LIKE 'ap0%'",
	ARRAY_A
);
if ( ! empty( $test_posts ) ) {
	foreach ( $test_posts as $p ) {
		wp_delete_post( (int) $p['ID'], true );
	}
}

// 4. Ensure canonical accounts.
$admin_pass  = getenv( 'AGENTPRESS_ADMIN_PASSWORD' ) ? getenv( 'AGENTPRESS_ADMIN_PASSWORD' ) : 'AgentPress2026!Admin';
$author_pass = getenv( 'AGENTPRESS_AUTHOR_PASSWORD' ) ? getenv( 'AGENTPRESS_AUTHOR_PASSWORD' ) : 'AgentPress2026!Author';

$admin_id  = agentpress_fixture_ensure_user( 'agentpress_admin', 'administrator', 'agentpress_admin@example.test', $admin_pass, 'AgentPress Admin' );
$author_id = agentpress_fixture_ensure_user( 'agentpress_author', 'author', 'agentpress_author@example.test', $author_pass, 'AgentPress Author' );

// Delete leftover synthetic test users from prior runs.
$test_users = $wpdb->get_results(
	"SELECT ID FROM {$wpdb->users} WHERE user_login LIKE 'ap0%' OR user_login LIKE 'agentpress_ap0%'",
	ARRAY_A
);
if ( ! empty( $test_users ) ) {
	foreach ( $test_users as $u ) {
		wp_delete_user( (int) $u['ID'] );
	}
}

// 5. Ensure canonical categories.
$cat_news          = agentpress_fixture_ensure_category( 'news', 'News', 'Latest news and industry updates' );
$cat_announcements = agentpress_fixture_ensure_category( 'announcements', 'Announcements', 'Company announcements and product launches' );
$cat_case_studies  = agentpress_fixture_ensure_category( 'case-studies', 'Case Studies', 'Client case studies and proven results' );

// 6. Ensure canonical pages.
$page_home    = agentpress_fixture_ensure_page( 'home', 'Home', 'Welcome to Acme Web & Digital Studio. We build fast, accessible websites and provide digital marketing services.', $admin_id );
$page_about   = agentpress_fixture_ensure_page( 'about', 'About Us', 'Acme Web & Digital Studio has been helping businesses grow online since 2018.', $admin_id );
$page_blog    = agentpress_fixture_ensure_page( 'blog', 'Blog', 'Read our latest articles on web technology, accessibility, and modern marketing.', $admin_id );
$page_contact = agentpress_fixture_ensure_page( 'contact', 'Contact', 'Get in touch with Acme Web & Digital Studio today for a free consultation.', $admin_id );

// Set front page and posts page.
update_option( 'page_on_front', $page_home );
update_option( 'page_for_posts', $page_blog );

// 7. Ensure canonical sample posts.
$post_welcoming = agentpress_fixture_ensure_post(
	'welcoming-our-new-team-members',
	'Welcoming Our New Team Members',
	'We are thrilled to welcome two new web strategists to Acme Web & Digital Studio.',
	$admin_id,
	$cat_announcements
);
$post_trends    = agentpress_fixture_ensure_post(
	'spring-2026-digital-trends',
	'Spring 2026 Digital Trends',
	'An overview of accessibility standards and human-agent collaboration in modern web development.',
	$author_id,
	$cat_news
);

// 8. Rebuild or Reconcile Classic Primary Navigation Menu.
$menu_obj = wp_get_nav_menu_object( 'Primary Navigation' );
if ( ! is_object( $menu_obj ) ) {
	$menu_id = wp_create_nav_menu( 'Primary Navigation' );
	agentpress_fixture_assert( ! is_wp_error( $menu_id ) && (int) $menu_id > 0, 'Could not create Primary Navigation menu.' );
	$menu_id = (int) $menu_id;
} else {
	$menu_id = (int) $menu_obj->term_id;
}

// Clean any stale synthetic menus from prior test runs (except canonical Primary Navigation).
$all_menus = wp_get_nav_menus( array( 'hide_empty' => false ) );
foreach ( $all_menus as $m ) {
	if ( (int) $m->term_id !== $menu_id && 0 === strpos( $m->name, 'AP021 ' ) ) {
		wp_delete_nav_menu( $m->term_id );
	}
}

// Query existing items in this menu to reuse stable IDs where possible.
$existing_items = wp_get_nav_menu_items(
	$menu_id,
	array(
		'post_status'            => 'any',
		'update_post_term_cache' => false,
	)
);
if ( false === $existing_items || ! is_array( $existing_items ) ) {
	$existing_items = array();
}

$page_to_item = array();
foreach ( $existing_items as $ei ) {
	if ( 'post_type' === $ei->type && 'page' === $ei->object ) {
		$page_to_item[ (int) $ei->object_id ] = (int) $ei->ID;
	}
}

$target_pages = array(
	'Home'    => array(
		'page' => $page_home,
		'pos'  => 1,
	),
	'About'   => array(
		'page' => $page_about,
		'pos'  => 2,
	),
	'Blog'    => array(
		'page' => $page_blog,
		'pos'  => 3,
	),
	'Contact' => array(
		'page' => $page_contact,
		'pos'  => 4,
	),
);

$canonical_item_ids = array();
foreach ( $target_pages as $label => $spec ) {
	$target_page_id   = $spec['page'];
	$target_pos       = $spec['pos'];
	$existing_item_id = isset( $page_to_item[ $target_page_id ] ) ? $page_to_item[ $target_page_id ] : 0;

	$item_result = wp_update_nav_menu_item(
		$menu_id,
		$existing_item_id,
		array(
			'menu-item-title'     => $label,
			'menu-item-object-id' => $target_page_id,
			'menu-item-object'    => 'page',
			'menu-item-type'      => 'post_type',
			'menu-item-status'    => 'publish',
			'menu-item-position'  => $target_pos,
			'menu-item-parent-id' => 0,
		)
	);
	agentpress_fixture_assert( ! is_wp_error( $item_result ) && (int) $item_result > 0, "Failed saving {$label} menu item." );
	$canonical_item_ids[ strtolower( $label ) ] = (int) $item_result;
}

// Delete any extraneous items that are not in the 4 canonical items (e.g. Services added by agent).
foreach ( $existing_items as $ei ) {
	if ( ! in_array( (int) $ei->ID, $canonical_item_ids, true ) ) {
		wp_delete_post( (int) $ei->ID, true );
	}
}

$item_home    = $canonical_item_ids['home'];
$item_about   = $canonical_item_ids['about'];
$item_blog    = $canonical_item_ids['blog'];
$item_contact = $canonical_item_ids['contact'];

// Assign menu to 'primary' theme location.
$locations            = get_theme_mod( 'nav_menu_locations', array() );
$locations['primary'] = $menu_id;
set_theme_mod( 'nav_menu_locations', $locations );

// 9. Purge AgentPress tables.
Migrator::migrate();
$wpdb->query( $wpdb->prepare( 'DELETE FROM %i', $wpdb->prefix . 'agentpress_changes' ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
$wpdb->query( $wpdb->prepare( 'DELETE FROM %i', $wpdb->prefix . 'agentpress_change_sets' ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
$wpdb->query( $wpdb->prepare( 'DELETE FROM %i', $wpdb->prefix . 'agentpress_audit_events' ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

// 10. Verify snapshot with ClassicMenuAdapter.
wp_set_current_user( $admin_id );
$adapter  = new ClassicMenuAdapter();
$snapshot = $adapter->snapshot( 'primary' );
if ( is_wp_error( $snapshot ) ) {
	fwrite( STDERR, 'SNAPSHOT FAILED: ' . $snapshot->get_error_code() . ' - ' . $snapshot->get_error_message() . "\n" );
	fwrite( STDERR, 'Registered menus: ' . wp_json_encode( get_registered_nav_menus() ) . "\n" );
	fwrite( STDERR, 'Nav locations: ' . wp_json_encode( get_nav_menu_locations() ) . "\n" );
	fwrite( STDERR, 'Current theme: ' . get_stylesheet() . "\n" );
	exit( 1 );
}
agentpress_fixture_assert( 'classic-menu' === $snapshot['adapter'], 'Adapter is not classic-menu.' );
agentpress_fixture_assert( 'primary' === $snapshot['location'], 'Location is not primary.' );
agentpress_fixture_assert( 4 === count( $snapshot['items'] ), 'Menu does not have exactly 4 items.' );
agentpress_fixture_assert( array( 'Home', 'About', 'Blog', 'Contact' ) === array_column( $snapshot['items'], 'label' ), 'Menu items do not match Home, About, Blog, Contact.' );

// 11. Compile machine-readable fixture manifest.
$manifest = array(
	'schema'        => 'agentpress-canonical-fixture-v1',
	'generated_at'  => gmdate( 'Y-m-d\TH:i:s\Z' ),
	'site'          => array(
		'title'       => get_option( 'blogname' ),
		'tagline'     => get_option( 'blogdescription' ),
		'home_page'   => $page_home,
		'blog_page'   => $page_blog,
		'theme'       => get_stylesheet(),
	),
	'users'         => array(
		'administrator' => array(
			'id'    => $admin_id,
			'login' => 'agentpress_admin',
			'email' => 'agentpress_admin@example.test',
		),
		'author'        => array(
			'id'    => $author_id,
			'login' => 'agentpress_author',
			'email' => 'agentpress_author@example.test',
		),
	),
	'categories'    => array(
		'news'          => $cat_news,
		'announcements' => $cat_announcements,
		'case_studies'  => $cat_case_studies,
	),
	'pages'         => array(
		'home'    => $page_home,
		'about'   => $page_about,
		'blog'    => $page_blog,
		'contact' => $page_contact,
	),
	'posts'         => array(
		'welcoming_team' => $post_welcoming,
		'spring_trends'  => $post_trends,
	),
	'navigation'    => array(
		'menu_id'    => $menu_id,
		'menu_name'  => 'Primary Navigation',
		'location'   => 'primary',
		'items'      => array(
			'home'    => (int) $item_home,
			'about'   => (int) $item_about,
			'blog'    => (int) $item_blog,
			'contact' => (int) $item_contact,
		),
		'state_hash' => $snapshot['state_hash'],
	),
	'agentpress_db' => array(
		'changes_count'     => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}agentpress_changes" ), // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		'change_sets_count' => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}agentpress_change_sets" ), // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		'audit_count'       => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}agentpress_audit_events" ), // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	),
);

// Output manifest delimiter and JSON.
echo "=== AGENTPRESS FIXTURE MANIFEST ===\n";
echo wp_json_encode( $manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) . "\n";
echo "=== END AGENTPRESS FIXTURE MANIFEST ===\n";
