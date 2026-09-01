<?php
/**
 * AP-014 bounded permission-aware term-read matrix.
 *
 * @package AgentPress
 */

use AgentPress\Abilities\AbilityCatalog;
use AgentPress\Schemas\SchemaValidator;
use AgentPress\Terms\TermReadService;

/** @param bool $condition Condition. @param string $message Message. @return void */
function agentpress_ap014_assert( $condition, $message ) {
	if ( ! $condition ) {
		fwrite( STDERR, "AP-014 assertion failed: {$message}\n" );
		exit( 1 );
	}
}

/** @param string $login Login. @param string $role Role. @return int */
function agentpress_ap014_user( $login, $role ) {
	$existing = get_user_by( 'login', $login );
	$id       = $existing ? (int) $existing->ID : wp_create_user( $login, wp_generate_password( 24 ), $login . '@private.example.test' );
	$user     = new WP_User( $id );
	$user->set_role( $role );
	return $id;
}

register_taxonomy( 'ap014_custom', 'post', array( 'public' => true, 'label' => 'AP014 Custom' ) );
foreach ( array( 'category', 'post_tag', 'ap014_custom' ) as $taxonomy ) {
	foreach ( get_terms( array( 'taxonomy' => $taxonomy, 'hide_empty' => false, 'search' => 'AP014' ) ) as $stale_term ) {
		wp_delete_term( $stale_term->term_id, $taxonomy );
	}
}
foreach ( array( 'administrator', 'author', 'subscriber' ) as $role ) {
	$existing_user = get_user_by( 'login', 'agentpress_ap014_' . $role );
	if ( is_object( $existing_user ) ) {
		wp_delete_user( $existing_user->ID );
	}
}
$stale_posts = get_posts( array( 'post_type' => 'post', 'post_status' => 'any', 's' => 'AP014', 'posts_per_page' => -1 ) );
foreach ( $stale_posts as $stale_post ) {
	wp_delete_post( $stale_post->ID, true );
}

$users = array(
	'administrator' => agentpress_ap014_user( 'agentpress_ap014_administrator', 'administrator' ),
	'author'        => agentpress_ap014_user( 'agentpress_ap014_author', 'author' ),
	'subscriber'    => agentpress_ap014_user( 'agentpress_ap014_subscriber', 'subscriber' ),
);
$parent = wp_insert_term( 'AP014 Category Alpha', 'category', array( 'slug' => 'ap014-category-alpha', 'description' => '<b>Parent description</b>' ) );
$child  = wp_insert_term( 'AP014 Category Bravo', 'category', array( 'slug' => 'ap014-category-bravo', 'description' => 'Child description', 'parent' => (int) $parent['term_id'] ) );
$other  = wp_insert_term( 'AP014 Category Charlie', 'category', array( 'slug' => 'ap014-category-charlie' ) );
$tag_a  = wp_insert_term( 'AP014 Tag Alpha', 'post_tag', array( 'slug' => 'ap014-tag-alpha' ) );
$tag_b  = wp_insert_term( 'AP014 Tag Bravo', 'post_tag', array( 'slug' => 'ap014-tag-bravo' ) );
$custom = wp_insert_term( 'AP014 Custom Alpha', 'ap014_custom', array( 'slug' => 'ap014-custom-alpha' ) );
foreach ( array( $parent, $child, $other, $tag_a, $tag_b, $custom ) as $term_result ) {
	agentpress_ap014_assert( ! is_wp_error( $term_result ), 'Fixture term creation failed.' );
}
$post_id = wp_insert_post( array( 'post_type' => 'post', 'post_status' => 'publish', 'post_title' => 'AP014 Assigned Post', 'post_author' => $users['author'] ) );
wp_set_object_terms( $post_id, array( (int) $child['term_id'] ), 'category' );
wp_set_object_terms( $post_id, array( (int) $tag_a['term_id'] ), 'post_tag' );

$ability   = wp_get_ability( 'agentpress/list-terms' );
$service   = new TermReadService();
$catalog   = AbilityCatalog::all();
$schema    = $catalog['agentpress/list-terms']['output_schema'];
$validator = new SchemaValidator();
$term_ids  = array( (int) $parent['term_id'], (int) $child['term_id'], (int) $other['term_id'], (int) $tag_a['term_id'], (int) $tag_b['term_id'] );
$snapshots = array();
foreach ( $term_ids as $term_id ) {
	$term                  = get_term( $term_id );
	$snapshots[ $term_id ] = array( $term->name, $term->slug, $term->description, (int) $term->parent, (int) $term->count );
}
$relationship_before = wp_get_object_terms( $post_id, array( 'category', 'post_tag' ), array( 'fields' => 'tt_ids' ) );

$role_results = array();
foreach ( $users as $role => $user_id ) {
	wp_set_current_user( $user_id );
	$result = $ability->execute( array( 'taxonomy' => 'category', 'search' => 'AP014', 'hide_empty' => false, 'page' => 1, 'per_page' => 2 ) );
	agentpress_ap014_assert( is_array( $result ) && true === $validator->validate_output( $result, $schema ), $role . ' category schema mismatch.' );
	agentpress_ap014_assert( 3 === $result['data']['total'] && 2 === $result['data']['total_pages'], $role . ' category totals mismatch.' );
	agentpress_ap014_assert( array( (int) $parent['term_id'], (int) $child['term_id'] ) === array_column( $result['data']['items'], 'term_id' ), $role . ' deterministic first page mismatch.' );
	agentpress_ap014_assert( array( 'term_id', 'taxonomy', 'name', 'slug', 'description', 'parent_id', 'count' ) === array_keys( $result['data']['items'][0] ), $role . ' term field allowlist mismatch.' );
	$role_results[ $role ] = $result;
}

wp_set_current_user( $users['administrator'] );
$second_page = $ability->execute( array( 'taxonomy' => 'category', 'search' => 'AP014', 'hide_empty' => false, 'page' => 2, 'per_page' => 2 ) );
agentpress_ap014_assert( array( (int) $other['term_id'] ) === array_column( $second_page['data']['items'], 'term_id' ), 'Deterministic second page mismatch.' );
$category_nonempty = $ability->execute( array( 'taxonomy' => 'category', 'search' => 'AP014', 'hide_empty' => true ) );
agentpress_ap014_assert( 1 === $category_nonempty['data']['total'] && array( (int) $child['term_id'] ) === array_column( $category_nonempty['data']['items'], 'term_id' ), 'Category hide-empty mismatch.' );
$tag_all = $ability->execute( array( 'taxonomy' => 'post_tag', 'search' => 'AP014', 'hide_empty' => false, 'per_page' => 1 ) );
agentpress_ap014_assert( 2 === $tag_all['data']['total'] && 2 === $tag_all['data']['total_pages'] && array( (int) $tag_a['term_id'] ) === array_column( $tag_all['data']['items'], 'term_id' ), 'Tag pagination mismatch.' );
$tag_nonempty = $ability->execute( array( 'taxonomy' => 'post_tag', 'search' => 'AP014', 'hide_empty' => true ) );
agentpress_ap014_assert( 1 === $tag_nonempty['data']['total'] && array( (int) $tag_a['term_id'] ) === array_column( $tag_nonempty['data']['items'], 'term_id' ), 'Tag hide-empty mismatch.' );
$search = $ability->execute( array( 'taxonomy' => 'category', 'search' => 'Bravo' ) );
agentpress_ap014_assert( array( (int) $child['term_id'] ) === array_column( $search['data']['items'], 'term_id' ), 'Search mismatch.' );
agentpress_ap014_assert( 'Parent description' === $role_results['administrator']['data']['items'][0]['description'], 'Description normalization mismatch.' );
agentpress_ap014_assert( (int) $parent['term_id'] === $role_results['administrator']['data']['items'][1]['parent_id'], 'Hierarchy projection mismatch.' );

$unsupported_policy = $ability->check_permissions( array( 'taxonomy' => 'ap014_custom' ) );
agentpress_ap014_assert( is_wp_error( $unsupported_policy ) && 'AP_UNSUPPORTED_TAXONOMY' === $unsupported_policy->get_error_code(), 'Custom taxonomy policy did not fail closed.' );
$unsupported_service = $service->execute( array( 'taxonomy' => 'ap014_custom' ) );
agentpress_ap014_assert( is_wp_error( $unsupported_service ) && 'AP_UNSUPPORTED_TAXONOMY' === $unsupported_service->get_error_code(), 'Custom taxonomy service did not fail closed.' );
$oversized = $validator->validate_input( array( 'taxonomy' => 'category', 'per_page' => 101 ), $catalog['agentpress/list-terms']['input_schema'] );
agentpress_ap014_assert( is_wp_error( $oversized ) && 'AP_SCHEMA_INVALID' === $oversized->get_error_code(), 'Oversized page did not fail schema.' );

wp_set_current_user( 0 );
$logged_out = $ability->check_permissions( array( 'taxonomy' => 'category' ) );
agentpress_ap014_assert( is_wp_error( $logged_out ) && 'AP_NOT_AUTHENTICATED' === $logged_out->get_error_code(), 'Logged-out permission did not fail closed.' );

$target_mutations = 0;
foreach ( $snapshots as $term_id => $snapshot ) {
	$term = get_term( $term_id );
	if ( ! is_object( $term ) || $snapshot !== array( $term->name, $term->slug, $term->description, (int) $term->parent, (int) $term->count ) ) {
		++$target_mutations;
	}
}
$relationship_after = wp_get_object_terms( $post_id, array( 'category', 'post_tag' ), array( 'fields' => 'tt_ids' ) );
if ( $relationship_before !== $relationship_after ) {
	++$target_mutations;
}
agentpress_ap014_assert( 0 === $target_mutations, 'Term reads mutated fixture state.' );

wp_delete_post( $post_id, true );
foreach ( array( array( $parent, 'category' ), array( $child, 'category' ), array( $other, 'category' ), array( $tag_a, 'post_tag' ), array( $tag_b, 'post_tag' ), array( $custom, 'ap014_custom' ) ) as $term_fixture ) {
	wp_delete_term( (int) $term_fixture[0]['term_id'], $term_fixture[1] );
}
foreach ( $users as $user_id ) {
	wp_delete_user( $user_id );
}
unregister_taxonomy( 'ap014_custom' );

echo wp_json_encode( array( 'roles' => 3, 'schema_validations' => 3, 'category_terms' => 3, 'tag_terms' => 2, 'deterministic_pages' => 2, 'search_controls' => 1, 'hide_empty_controls' => 2, 'unsupported_or_oversized_denials' => 3, 'logged_out_denied' => true, 'target_mutations' => $target_mutations ) ) . "\n";
