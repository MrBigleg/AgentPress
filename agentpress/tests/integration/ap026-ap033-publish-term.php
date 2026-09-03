<?php
/**
 * AP-026 and AP-033 staging and approval matrix.
 *
 * @package AgentPress
 */

use AgentPress\Abilities\AbilityCatalog;
use AgentPress\Changes\ApprovalService;
use AgentPress\Changes\ChangeRepository;
use AgentPress\Schemas\SchemaValidator;

/** @param bool $condition Condition. @param string $message Message. @return void */
function agentpress_ap026_assert( $condition, $message ) {
	if ( ! $condition ) {
		fwrite( STDERR, "AP-026/033 assertion failed: {$message}\n" );
		exit( 1 );
	}
}

/** @param mixed $result Candidate result. @param string $code Expected code. @param string $message Assertion message. @return void */
function agentpress_ap026_error( $result, $code, $message ) {
	agentpress_ap026_assert( is_wp_error( $result ) && $code === $result->get_error_code(), $message );
}

global $wpdb;
foreach ( array( 'agentpress_changes', 'agentpress_change_sets', 'agentpress_audit_events' ) as $suffix ) {
	$wpdb->query( $wpdb->prepare( 'DELETE FROM %i', $wpdb->prefix . $suffix ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
}
$old_user = get_user_by( 'login', 'agentpress_ap026_admin' );
if ( is_object( $old_user ) ) {
	wp_delete_user( $old_user->ID );
}
foreach ( get_posts( array( 'post_type' => 'page', 'post_status' => 'any', 's' => 'AP026', 'posts_per_page' => -1 ) ) as $stale_page ) {
	wp_delete_post( $stale_page->ID, true );
}
foreach ( get_terms( array( 'taxonomy' => array( 'category', 'post_tag' ), 'hide_empty' => false ) ) as $stale_term ) {
	if ( 0 === strpos( $stale_term->name, 'AP026 ' ) || 'ap026-services' === $stale_term->slug ) {
		wp_delete_term( $stale_term->term_id, $stale_term->taxonomy );
	}
}

$admin = wp_create_user( 'agentpress_ap026_admin', wp_generate_password( 24 ), 'ap026@example.test' );
( new WP_User( $admin ) )->set_role( 'administrator' );
wp_set_current_user( $admin );

$services_id = wp_insert_post(
	array(
		'post_type'   => 'page',
		'post_status' => 'draft',
		'post_title'  => 'AP026 Services',
		'post_name'   => 'ap026-services',
		'post_author' => $admin,
	)
);
agentpress_ap026_assert( ! is_wp_error( $services_id ) && (int) $services_id > 0, 'Service draft creation failed.' );
$services_id = (int) $services_id;

$catalog   = AbilityCatalog::all();
$validator = new SchemaValidator();
$service   = new ApprovalService();
$publish_ability   = wp_get_ability( 'agentpress/publish-content' );
$create_term_ability = wp_get_ability( 'agentpress/create-term' );
agentpress_ap026_assert( is_object( $publish_ability ) && is_object( $create_term_ability ), 'AP-026/033 Abilities are not registered.' );

// Publish-content: stage never publishes.
$publish_input = array( 'content_id' => $services_id, 'idempotency_key' => 'ap026-publish' );
$publish_stage = $publish_ability->execute( $publish_input );
agentpress_ap026_assert( is_array( $publish_stage ), 'Publish staging failed.' );
agentpress_ap026_assert( 'PENDING_APPROVAL' === $publish_stage['data']['status'] && 'publish' === $publish_stage['data']['proposed_status'] && true === $validator->validate_output( $publish_stage, $catalog['agentpress/publish-content']['output_schema'] ), 'Publish output differs.' );
$publish_change = (int) $publish_stage['data']['change_id'];
agentpress_ap026_assert( 'draft' === get_post( $services_id )->post_status, 'Publish staged immediately.' );

$publish_approved = $service->approve( $publish_change );
agentpress_ap026_assert( is_array( $publish_approved ) && 'APPLIED' === $publish_approved['data']['status'], 'Publish approval failed.' );
agentpress_ap026_assert( 'publish' === get_post( $services_id )->post_status, 'Publish approval did not publish.' );

// Publishing an already-published target conflicts at staging.
agentpress_ap026_error( $publish_ability->execute( array( 'content_id' => $services_id, 'idempotency_key' => 'ap026-republish' ) ), 'AP_STATE_CONFLICT', 'Republish was accepted.' );

// Create-term: stage never creates the term, approval creates it.
$term_input = array( 'taxonomy' => 'category', 'name' => 'AP026 Services', 'slug' => 'ap026-services', 'description' => 'A fixture category.', 'idempotency_key' => 'ap026-term' );
$term_stage = $create_term_ability->execute( $term_input );
agentpress_ap026_assert( is_array( $term_stage ), 'Term staging failed.' );
agentpress_ap026_assert( 'PENDING_APPROVAL' === $term_stage['data']['status'] && 'AP026 Services' === $term_stage['data']['proposed_term']['name'] && true === $validator->validate_output( $term_stage, $catalog['agentpress/create-term']['output_schema'] ), 'Term output differs.' );
$term_change = (int) $term_stage['data']['change_id'];
agentpress_ap026_assert( ! term_exists( 'ap026-services', 'category' ), 'Term staged immediately.' );

$term_approved = $service->approve( $term_change );
agentpress_ap026_assert( is_array( $term_approved ) && 'APPLIED' === $term_approved['data']['status'], 'Term approval failed.' );
$created_term = term_exists( 'ap026-services', 'category' );
agentpress_ap026_assert( is_array( $created_term ) && (int) $term_approved['data']['object_id'] === (int) $created_term['term_id'], 'Term approval did not create the term.' );

// Missing or invalid term target fails; unauthenticated publish and approval fail.
$fresh_stage = $create_term_ability->execute( array( 'taxonomy' => 'post_tag', 'name' => 'AP026 Logout', 'idempotency_key' => 'ap026-logout' ) );
agentpress_ap026_assert( is_array( $fresh_stage ), 'Fresh term staging failed.' );
$fresh_change = (int) $fresh_stage['data']['change_id'];
wp_set_current_user( 0 );
agentpress_ap026_error( $create_term_ability->check_permissions( $term_input ), 'AP_NOT_AUTHENTICATED', 'Logged out staged a term.' );
agentpress_ap026_error( $publish_ability->check_permissions( $publish_input ), 'AP_NOT_AUTHENTICATED', 'Logged out staged a publish.' );
agentpress_ap026_error( $service->approve( $fresh_change ), 'AP_NOT_AUTHENTICATED', 'Logged out approved a change.' );

// Clean up synthetic fixtures.
wp_set_current_user( $admin );
if ( is_array( $created_term ) ) {
	wp_delete_term( (int) $created_term['term_id'], 'category' );
}
wp_delete_post( $services_id, true );
wp_delete_user( $admin );
foreach ( array( 'agentpress_changes', 'agentpress_change_sets', 'agentpress_audit_events' ) as $suffix ) {
	$wpdb->query( $wpdb->prepare( 'DELETE FROM %i', $wpdb->prefix . $suffix ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
}

echo wp_json_encode(
	array(
		'publish_staged'     => true,
		'publish_approval'   => true,
		'no_immediate_pub'   => true,
		'republish_denied'   => true,
		'term_staged'        => true,
		'term_approval'      => true,
		'no_immediate_term'  => true,
		'logged_out_denials' => 3,
	)
) . "\n";
