<?php
/**
 * AP-017 assign-terms runtime matrix.
 *
 * @package AgentPress
 */

use AgentPress\Content\DraftCreationService;
use AgentPress\Storage\Migrator;
use AgentPress\Terms\TermAssignmentService;

/** @param bool $condition Condition. @param string $message Message. @return void */
function agentpress_ap017_assert( $condition, $message ) {
	if ( ! $condition ) {
		fwrite( STDERR, "AP-017 assertion failed: {$message}\n" );
		exit( 1 );
	}
}

/** @param string $role Role. @return int */
function agentpress_ap017_user( $role ) {
	$suffix  = strtolower( wp_generate_password( 8, false ) );
	$user_id = wp_create_user( 'ap017-' . $role . '-' . $suffix, wp_generate_password( 24 ), 'ap017-' . $role . '-' . $suffix . '@example.test' );
	agentpress_ap017_assert( ! is_wp_error( $user_id ), 'Could not create ' . $role . ' user.' );
	( new WP_User( $user_id ) )->set_role( $role );
	return (int) $user_id;
}

/** @param int $post_id Post ID. @param string $taxonomy Taxonomy. @return array<int> */
function agentpress_ap017_terms( $post_id, $taxonomy ) {
	$ids = wp_get_object_terms( $post_id, $taxonomy, array( 'fields' => 'ids' ) );
	agentpress_ap017_assert( ! is_wp_error( $ids ), 'Could not read target terms.' );
	$ids = array_values( array_map( 'intval', $ids ) );
	sort( $ids, SORT_NUMERIC );
	return $ids;
}

global $wpdb;
Migrator::migrate();
foreach ( array( 'agentpress_changes', 'agentpress_change_sets' ) as $suffix ) {
	$wpdb->query( $wpdb->prepare( 'DELETE FROM %i', $wpdb->prefix . $suffix ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
}

$users = array(
	'administrator' => agentpress_ap017_user( 'administrator' ),
	'author'        => agentpress_ap017_user( 'author' ),
	'subscriber'    => agentpress_ap017_user( 'subscriber' ),
);
wp_set_current_user( $users['administrator'] );
$term_suffix = strtolower( wp_generate_password( 8, false ) );
$category_a  = wp_insert_term( 'AP017 Category A ' . $term_suffix, 'category', array( 'slug' => 'ap017-category-a-' . $term_suffix ) );
$category_b  = wp_insert_term( 'AP017 Category B ' . $term_suffix, 'category', array( 'slug' => 'ap017-category-b-' . $term_suffix ) );
$tag_a       = wp_insert_term( 'AP017 Tag A ' . $term_suffix, 'post_tag', array( 'slug' => 'ap017-tag-a-' . $term_suffix ) );
agentpress_ap017_assert( ! is_wp_error( $category_a ) && ! is_wp_error( $category_b ) && ! is_wp_error( $tag_a ), 'Term fixtures failed.' );
$category_a = (int) $category_a['term_id'];
$category_b = (int) $category_b['term_id'];
$tag_a      = (int) $tag_a['term_id'];

$draft_service = new DraftCreationService();
$admin_draft   = $draft_service->execute( array( 'post_type' => 'post', 'title' => 'AP017 Admin Agent Draft', 'idempotency_key' => 'ap017-admin-create' ) );
agentpress_ap017_assert( is_array( $admin_draft ), 'Administrator AgentPress draft creation failed.' );
$admin_draft_id = (int) $admin_draft['data']['content_id'];
$ordinary_id    = wp_insert_post( array( 'post_type' => 'post', 'post_status' => 'draft', 'post_title' => 'AP017 Ordinary Draft', 'post_author' => $users['administrator'] ) );
$published_id   = wp_insert_post( array( 'post_type' => 'post', 'post_status' => 'publish', 'post_title' => 'AP017 Published', 'post_author' => $users['administrator'] ) );
$page_id        = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'draft', 'post_title' => 'AP017 Page', 'post_author' => $users['administrator'] ) );

$ability = wp_get_ability( 'agentpress/assign-terms' );
agentpress_ap017_assert( is_object( $ability ), 'Registered assign-terms Ability missing.' );
$replace_input = array( 'content_id' => $admin_draft_id, 'taxonomy' => 'category', 'term_ids' => array( $category_a ), 'mode' => 'replace', 'idempotency_key' => 'ap017-admin-replace' );
agentpress_ap017_assert( true === $ability->check_permissions( $replace_input ), 'Administrator canonical permission failed.' );
$replace = $ability->execute( $replace_input );
agentpress_ap017_assert( is_array( $replace ) && true === $replace['ok'] && 'APPLIED' === $replace['data']['status'] && false === $replace['data']['approval_required'], 'Canonical replace failed.' );
agentpress_ap017_assert( array( $category_a ) === agentpress_ap017_terms( $admin_draft_id, 'category' ), 'Canonical replace target mismatch.' );

$append_input = array( 'content_id' => $admin_draft_id, 'taxonomy' => 'category', 'term_ids' => array( $category_b ), 'mode' => 'append', 'idempotency_key' => 'ap017-admin-append' );
$append       = $ability->execute( $append_input );
agentpress_ap017_assert( is_array( $append ) && array( $category_a, $category_b ) === $append['data']['term_ids'], 'Canonical append result mismatch.' );
agentpress_ap017_assert( array( $category_a, $category_b ) === agentpress_ap017_terms( $admin_draft_id, 'category' ), 'Canonical append target mismatch.' );
$append_replay = $ability->execute( $append_input );
agentpress_ap017_assert( is_array( $append_replay ) && true === $append_replay['data']['replayed'] && $append['data']['change_id'] === $append_replay['data']['change_id'], 'Identical append did not replay.' );

$changed_append             = $append_input;
$changed_append['term_ids'] = array( $category_a );
$conflict                   = $ability->execute( $changed_append );
agentpress_ap017_assert( is_wp_error( $conflict ) && 'AP_STATE_CONFLICT' === $conflict->get_error_code(), 'Changed replay did not conflict.' );
agentpress_ap017_assert( array( $category_a, $category_b ) === agentpress_ap017_terms( $admin_draft_id, 'category' ), 'Conflict mutated target.' );

$before_invalid = agentpress_ap017_terms( $admin_draft_id, 'category' );
$mixed          = ( new TermAssignmentService() )->execute( array( 'content_id' => $admin_draft_id, 'taxonomy' => 'category', 'term_ids' => array( $category_a, $tag_a ), 'idempotency_key' => 'ap017-mixed-term' ) );
agentpress_ap017_assert( is_wp_error( $mixed ) && 'AP_TERM_NOT_FOUND' === $mixed->get_error_code(), 'Mixed taxonomy IDs did not fail.' );
agentpress_ap017_assert( $before_invalid === agentpress_ap017_terms( $admin_draft_id, 'category' ), 'Mixed taxonomy IDs partially mutated.' );

foreach ( array( $ordinary_id, $published_id ) as $target_id ) {
	wp_set_object_terms( $target_id, array( $category_a ), 'category', false );
	$stage_input = array( 'content_id' => $target_id, 'taxonomy' => 'category', 'term_ids' => array( $category_b ), 'idempotency_key' => 'ap017-stage-' . $target_id );
	$staged      = $ability->execute( $stage_input );
	agentpress_ap017_assert( is_array( $staged ) && 'PENDING_APPROVAL' === $staged['data']['status'] && true === $staged['data']['approval_required'] && '' !== $staged['data']['expires_at'], 'Ordinary/published target did not stage.' );
	agentpress_ap017_assert( array( $category_a ) === agentpress_ap017_terms( $target_id, 'category' ), 'Staged target mutated.' );
}

wp_set_current_user( $users['author'] );
$author_draft = $draft_service->execute( array( 'post_type' => 'post', 'title' => 'AP017 Author Agent Draft', 'idempotency_key' => 'ap017-author-create' ) );
agentpress_ap017_assert( is_array( $author_draft ), 'Author AgentPress draft creation failed.' );
$author_draft_id = (int) $author_draft['data']['content_id'];
$author_input    = array( 'content_id' => $author_draft_id, 'taxonomy' => 'category', 'term_ids' => array( $category_a ), 'idempotency_key' => 'ap017-author-own' );
$author_result   = $ability->execute( $author_input );
agentpress_ap017_assert( is_array( $author_result ) && 'APPLIED' === $author_result['data']['status'] && array( $category_a ) === agentpress_ap017_terms( $author_draft_id, 'category' ), 'Author own-draft assignment failed.' );
$other_input = array( 'content_id' => $admin_draft_id, 'taxonomy' => 'category', 'term_ids' => array( $category_a ), 'idempotency_key' => 'ap017-author-other' );
agentpress_ap017_assert( is_wp_error( $ability->check_permissions( $other_input ) ), 'Author other-draft permission passed.' );

wp_set_current_user( $users['subscriber'] );
agentpress_ap017_assert( is_wp_error( $ability->check_permissions( $author_input ) ), 'Subscriber permission passed.' );
wp_set_current_user( 0 );
agentpress_ap017_assert( is_wp_error( $ability->check_permissions( $author_input ) ), 'Logged-out permission passed.' );
wp_set_current_user( $users['administrator'] );
$page_input = array( 'content_id' => $page_id, 'taxonomy' => 'category', 'term_ids' => array( $category_a ), 'idempotency_key' => 'ap017-page-denial' );
agentpress_ap017_assert( is_wp_error( $ability->check_permissions( $page_input ) ), 'Page target permission passed.' );

$applied = (int) $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM %i WHERE ability = %s AND status = %s', $wpdb->prefix . 'agentpress_changes', 'agentpress/assign-terms', 'APPLIED' ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
$pending = (int) $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM %i WHERE ability = %s AND status = %s', $wpdb->prefix . 'agentpress_changes', 'agentpress/assign-terms', 'PENDING_APPROVAL' ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
agentpress_ap017_assert( 3 === $applied && 2 === $pending, 'Durable assignment states mismatch.' );

foreach ( array( $admin_draft_id, $author_draft_id, $ordinary_id, $published_id, $page_id ) as $post_id ) {
	wp_delete_post( $post_id, true );
}
foreach ( array( $category_a, $category_b ) as $term_id ) {
	wp_delete_term( $term_id, 'category' );
}
wp_delete_term( $tag_a, 'post_tag' );
foreach ( $users as $user_id ) {
	wp_delete_user( $user_id );
}
foreach ( array( 'agentpress_changes', 'agentpress_change_sets' ) as $suffix ) {
	$wpdb->query( $wpdb->prepare( 'DELETE FROM %i', $wpdb->prefix . $suffix ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
}

echo wp_json_encode( array( 'r1_applied' => $applied, 'r2_staged' => $pending, 'append_replace' => 2, 'idempotent_replays' => 1, 'conflicts' => 1, 'atomic_invalid_denials' => 1, 'role_target_denials' => 4, 'staged_target_mutations' => 0, 'unauthorized_mutations' => 0 ) ) . "\n";
