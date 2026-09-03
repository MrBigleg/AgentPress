<?php
/**
 * AP-027 deterministic challenge fixture and reset runtime acceptance matrix.
 *
 * @package AgentPress
 */

use AgentPress\Navigation\ClassicMenuAdapter;

/**
 * Assert condition for AP-027.
 *
 * @param bool   $condition Condition to assert.
 * @param string $message   Failure message.
 * @return void
 */
function agentpress_ap027_assert( $condition, $message ) {
	if ( ! $condition ) {
		fwrite( STDERR, "AP-027 assertion failed: {$message}\n" );
		exit( 1 );
	}
}

global $wpdb;

// 1. Verify Site Identity & Front Page settings.
agentpress_ap027_assert( 'Acme Web & Digital Studio' === html_entity_decode( (string) get_option( 'blogname' ), ENT_QUOTES ), 'Blog name mismatch: ' . get_option( 'blogname' ) );
agentpress_ap027_assert( 'Modern web design, SEO, and digital consulting' === get_option( 'blogdescription' ), 'Blog description mismatch.' );
agentpress_ap027_assert( 'page' === get_option( 'show_on_front' ), 'show_on_front is not page.' );

// 2. Verify Canonical Users & Role Capabilities.
$admin_user = get_user_by( 'login', 'agentpress_admin' );
agentpress_ap027_assert( $admin_user instanceof WP_User, 'agentpress_admin user not found.' );
agentpress_ap027_assert( in_array( 'administrator', $admin_user->roles, true ), 'agentpress_admin is not administrator.' );
agentpress_ap027_assert( user_can( $admin_user, 'manage_options' ), 'agentpress_admin missing manage_options.' );
agentpress_ap027_assert( user_can( $admin_user, 'edit_theme_options' ), 'agentpress_admin missing edit_theme_options.' );

$author_user = get_user_by( 'login', 'agentpress_author' );
agentpress_ap027_assert( $author_user instanceof WP_User, 'agentpress_author user not found.' );
agentpress_ap027_assert( in_array( 'author', $author_user->roles, true ), 'agentpress_author is not author.' );
agentpress_ap027_assert( user_can( $author_user, 'publish_posts' ), 'agentpress_author cannot publish_posts.' );
agentpress_ap027_assert( ! user_can( $author_user, 'edit_pages' ), 'agentpress_author unexpectedly can edit_pages.' );
agentpress_ap027_assert( ! user_can( $author_user, 'edit_theme_options' ), 'agentpress_author unexpectedly can edit_theme_options.' );
agentpress_ap027_assert( ! user_can( $author_user, 'manage_options' ), 'agentpress_author unexpectedly can manage_options.' );

// 3. Verify Categories.
$cat_news = get_term_by( 'slug', 'news', 'category' );
agentpress_ap027_assert( $cat_news && 'News' === $cat_news->name, 'news category missing or invalid.' );

$cat_announcements = get_term_by( 'slug', 'announcements', 'category' );
agentpress_ap027_assert( $cat_announcements && 'Announcements' === $cat_announcements->name, 'announcements category missing or invalid.' );

$cat_case_studies = get_term_by( 'slug', 'case-studies', 'category' );
agentpress_ap027_assert( $cat_case_studies && 'Case Studies' === $cat_case_studies->name, 'case-studies category missing or invalid.' );

// 4. Verify Canonical Pages.
$page_home = get_page_by_path( 'home', OBJECT, 'page' );
agentpress_ap027_assert( $page_home && 'publish' === $page_home->post_status, 'Home page missing or not published.' );
agentpress_ap027_assert( (int) get_option( 'page_on_front' ) === (int) $page_home->ID, 'page_on_front does not point to Home.' );

$page_about = get_page_by_path( 'about', OBJECT, 'page' );
agentpress_ap027_assert( $page_about && 'publish' === $page_about->post_status, 'About page missing or not published.' );

$page_blog = get_page_by_path( 'blog', OBJECT, 'page' );
agentpress_ap027_assert( $page_blog && 'publish' === $page_blog->post_status, 'Blog page missing or not published.' );
agentpress_ap027_assert( (int) get_option( 'page_for_posts' ) === (int) $page_blog->ID, 'page_for_posts does not point to Blog.' );

$page_contact = get_page_by_path( 'contact', OBJECT, 'page' );
agentpress_ap027_assert( $page_contact && 'publish' === $page_contact->post_status, 'Contact page missing or not published.' );

// Canonical Precondition: "Services" page must NOT exist.
$services_page = get_page_by_path( 'services', OBJECT, 'page' );
agentpress_ap027_assert( null === $services_page, 'Precondition violated: Services page already exists.' );

// 5. Verify Canonical Posts.
$post_welcoming = get_posts(
	array(
		'name'        => 'welcoming-our-new-team-members',
		'post_type'   => 'post',
		'post_status' => 'publish',
		'numberposts' => 1,
	)
);
agentpress_ap027_assert( ! empty( $post_welcoming ), 'Welcoming post missing.' );

$post_trends = get_posts(
	array(
		'name'        => 'spring-2026-digital-trends',
		'post_type'   => 'post',
		'post_status' => 'publish',
		'numberposts' => 1,
	)
);
agentpress_ap027_assert( ! empty( $post_trends ), 'Spring trends post missing.' );

// 6. Verify Classic Navigation via ClassicMenuAdapter.
wp_set_current_user( $admin_user->ID );
$adapter  = new ClassicMenuAdapter();
$snapshot = $adapter->snapshot( 'primary' );
agentpress_ap027_assert( ! is_wp_error( $snapshot ), 'ClassicMenuAdapter snapshot failed.' );
agentpress_ap027_assert( 'classic-menu' === $snapshot['adapter'], 'Adapter mismatch.' );
agentpress_ap027_assert( 'primary' === $snapshot['location'], 'Location mismatch.' );
agentpress_ap027_assert( 4 === count( $snapshot['items'] ), 'Menu items count mismatch.' );
agentpress_ap027_assert( array( 'Home', 'About', 'Blog', 'Contact' ) === array_column( $snapshot['items'], 'label' ), 'Menu items label order mismatch.' );
agentpress_ap027_assert( array( 1, 2, 3, 4 ) === array_column( $snapshot['items'], 'position' ), 'Positions mismatch.' );
agentpress_ap027_assert( array( 0, 0, 0, 0 ) === array_column( $snapshot['items'], 'parent_item_id' ), 'Parents mismatch.' );
agentpress_ap027_assert( array( (int) $page_home->ID, (int) $page_about->ID, (int) $page_blog->ID, (int) $page_contact->ID ) === array_column( $snapshot['items'], 'object_id' ), 'Item object IDs mismatch.' );
agentpress_ap027_assert( 64 === strlen( $snapshot['state_hash'] ), 'State hash is not 64 characters.' );

// Also verify Author can read the navigation snapshot (all 4 targets are readable).
wp_set_current_user( $author_user->ID );
$author_snapshot = $adapter->snapshot( 'primary' );
agentpress_ap027_assert( ! is_wp_error( $author_snapshot ), 'Author cannot read navigation snapshot.' );
agentpress_ap027_assert( $snapshot['state_hash'] === $author_snapshot['state_hash'], 'Author state hash does not match Admin.' );

// 7. Verify AgentPress Coordinator Database Tables are clean.
$changes_count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}agentpress_changes" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
$change_sets   = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}agentpress_change_sets" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
$audit_count   = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}agentpress_audit_events" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared

agentpress_ap027_assert( 0 === $changes_count, "agentpress_changes has {$changes_count} rows, expected 0." );
agentpress_ap027_assert( 0 === $change_sets, "agentpress_change_sets has {$change_sets} rows, expected 0." );
agentpress_ap027_assert( 0 === $audit_count, "agentpress_audit_events has {$audit_count} rows, expected 0." );

echo "AP-027 acceptance test PASSED: Deterministic challenge fixture verified.\n";
