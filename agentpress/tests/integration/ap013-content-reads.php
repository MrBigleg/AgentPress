<?php
/**
 * AP-013 bounded permission-aware content-read matrix.
 *
 * @package AgentPress
 */

use AgentPress\Abilities\AbilityCatalog;
use AgentPress\Content\ContentReadService;
use AgentPress\Schemas\SchemaValidator;

/** @param bool $condition Condition. @param string $message Message. @return void */
function agentpress_ap013_assert( $condition, $message ) {
	if ( ! $condition ) {
		fwrite( STDERR, "AP-013 assertion failed: {$message}\n" );
		exit( 1 );
	}
}

/** @param string $login Login. @param string $role Role. @return int */
function agentpress_ap013_user( $login, $role ) {
	$existing = get_user_by( 'login', $login );
	$id       = $existing ? (int) $existing->ID : wp_create_user( $login, wp_generate_password( 24 ), $login . '@private.example.test' );
	$user     = new WP_User( $id );
	$user->set_role( $role );
	return $id;
}

/** @param string $title Title. @param string $status Status. @param int $author Author. @param array<string, mixed> $extra Extra fields. @return int */
function agentpress_ap013_post( $title, $status, $author, $extra = array() ) {
	return wp_insert_post(
		array_merge(
			array(
				'post_type'    => 'post',
				'post_status'  => $status,
				'post_title'   => $title,
				'post_excerpt' => 'Excerpt ' . $title,
				'post_author'  => $author,
			),
			$extra
		)
	);
}

register_post_type( 'ap013_secret', array( 'public' => true, 'label' => 'AP013 Secret' ) );
foreach ( get_posts( array( 'post_type' => array( 'post', 'page', 'ap013_secret' ), 'post_status' => 'any', 's' => 'AP013', 'posts_per_page' => -1 ) ) as $stale_post ) {
	wp_delete_post( $stale_post->ID, true );
}
foreach ( array( array( 'ap013-category', 'category' ), array( 'ap013-tag', 'post_tag' ) ) as $stale_term ) {
	$existing_term = get_term_by( 'slug', $stale_term[0], $stale_term[1] );
	if ( is_object( $existing_term ) ) {
		wp_delete_term( $existing_term->term_id, $stale_term[1] );
	}
}
foreach ( array( 'administrator', 'author', 'subscriber' ) as $role ) {
	$existing_user = get_user_by( 'login', 'agentpress_ap013_' . $role );
	if ( is_object( $existing_user ) ) {
		wp_delete_user( $existing_user->ID );
	}
}
$users = array(
	'administrator' => agentpress_ap013_user( 'agentpress_ap013_administrator', 'administrator' ),
	'author'        => agentpress_ap013_user( 'agentpress_ap013_author', 'author' ),
	'subscriber'    => agentpress_ap013_user( 'agentpress_ap013_subscriber', 'subscriber' ),
);
$category = wp_insert_term( 'AP013 Category', 'category', array( 'slug' => 'ap013-category' ) );
$tag      = wp_insert_term( 'AP013 Tag', 'post_tag', array( 'slug' => 'ap013-tag' ) );
agentpress_ap013_assert( ! is_wp_error( $category ) && ! is_wp_error( $tag ), 'Fixture terms failed.' );

$created         = array();
$public_alpha    = agentpress_ap013_post( 'AP013 Alpha', 'publish', $users['author'], array( 'post_content' => '<p>PUBLIC-CONTENT-AP013</p>' ) );
$public_bravo    = agentpress_ap013_post( 'AP013 Bravo', 'publish', $users['administrator'] );
$public_charlie  = agentpress_ap013_post( 'AP013 Charlie', 'publish', $users['author'] );
$author_draft    = agentpress_ap013_post( 'AP013 Author Draft', 'draft', $users['author'], array( 'post_content' => 'AUTHOR-DRAFT-CONTENT-AP013' ) );
$admin_draft     = agentpress_ap013_post( 'AP013 Admin Draft', 'draft', $users['administrator'], array( 'post_content' => 'PRIVATE-DRAFT-SENTINEL-AP013' ) );
$admin_private   = agentpress_ap013_post( 'AP013 Admin Private', 'private', $users['administrator'], array( 'post_content' => 'PRIVATE-POST-SENTINEL-AP013' ) );
$long_content    = agentpress_ap013_post( 'AP013 Long', 'publish', $users['administrator'], array( 'post_content' => str_repeat( 'L', 50010 ) ) );
$public_page     = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => 'AP013 Public Page', 'post_author' => $users['administrator'] ) );
$unsupported     = wp_insert_post( array( 'post_type' => 'ap013_secret', 'post_status' => 'publish', 'post_title' => 'AP013 Unsupported', 'post_author' => $users['administrator'] ) );
$created         = array( $public_alpha, $public_bravo, $public_charlie, $author_draft, $admin_draft, $admin_private, $long_content, $public_page, $unsupported );

wp_set_object_terms( $public_alpha, array( (int) $category['term_id'] ), 'category' );
wp_set_object_terms( $public_alpha, array( (int) $tag['term_id'] ), 'post_tag' );

$list      = wp_get_ability( 'agentpress/list-content' );
$get       = wp_get_ability( 'agentpress/get-content' );
$service   = new ContentReadService();
$catalog   = AbilityCatalog::all();
$validator = new SchemaValidator();
$snapshots = array();
foreach ( $created as $post_id ) {
	$post                  = get_post( $post_id );
	$snapshots[ $post_id ] = array( $post->post_status, $post->post_title, $post->post_content, $post->post_modified_gmt );
}

wp_set_current_user( $users['administrator'] );
$first_page = $list->execute( array( 'post_type' => 'post', 'status' => 'publish', 'search' => 'AP013', 'page' => 1, 'per_page' => 2, 'orderby' => 'title', 'order' => 'asc' ) );
$second_page = $list->execute( array( 'post_type' => 'post', 'status' => 'publish', 'search' => 'AP013', 'page' => 2, 'per_page' => 2, 'orderby' => 'title', 'order' => 'asc' ) );
agentpress_ap013_assert( true === $validator->validate_output( $first_page, $catalog['agentpress/list-content']['output_schema'] ), 'First list schema mismatch.' );
agentpress_ap013_assert( true === $validator->validate_output( $second_page, $catalog['agentpress/list-content']['output_schema'] ), 'Second list schema mismatch.' );
agentpress_ap013_assert( 4 === $first_page['data']['total'] && 2 === $first_page['data']['total_pages'], 'Published pagination totals mismatch.' );
agentpress_ap013_assert( array( $public_alpha, $public_bravo ) === array_column( $first_page['data']['items'], 'id' ), 'First deterministic page mismatch.' );
agentpress_ap013_assert( array( $public_charlie, $long_content ) === array_column( $second_page['data']['items'], 'id' ), 'Second deterministic page mismatch.' );

$taxonomy_result = $list->execute( array( 'post_type' => 'post', 'status' => 'publish', 'search' => 'AP013', 'taxonomy' => array( 'name' => 'category', 'term_ids' => array( (int) $category['term_id'] ) ) ) );
agentpress_ap013_assert( array( $public_alpha ) === array_column( $taxonomy_result['data']['items'], 'id' ), 'Taxonomy filter mismatch.' );
$author_result = $list->execute( array( 'post_type' => 'post', 'status' => 'publish', 'search' => 'AP013', 'author_id' => $users['author'], 'orderby' => 'title', 'order' => 'asc' ) );
agentpress_ap013_assert( array( $public_alpha, $public_charlie ) === array_column( $author_result['data']['items'], 'id' ), 'Author filter mismatch.' );
$page_result = $list->execute( array( 'post_type' => 'page', 'search' => 'AP013' ) );
agentpress_ap013_assert( array( $public_page ) === array_column( $page_result['data']['items'], 'id' ), 'Page-type filter mismatch.' );

$full = $get->execute( array( 'content_id' => $public_alpha ) );
agentpress_ap013_assert( true === $validator->validate_output( $full, $catalog['agentpress/get-content']['output_schema'] ), 'Full content schema mismatch.' );
agentpress_ap013_assert( '<p>PUBLIC-CONTENT-AP013</p>' === $full['data']['content'], 'Raw editable content mismatch.' );
agentpress_ap013_assert( array( 'category', 'post_tag' ) === array_column( $full['data']['terms'], 'taxonomy' ), 'Assigned term projection mismatch.' );
$long = $get->execute( array( 'content_id' => $long_content ) );
agentpress_ap013_assert( true === $long['data']['content_truncated'] && 50000 === strlen( $long['data']['content'] ), 'Content hard cap mismatch.' );
$unsupported_error = $get->check_permissions( array( 'content_id' => $unsupported ) );
agentpress_ap013_assert( is_wp_error( $unsupported_error ) && 'AP_UNSUPPORTED_POST_TYPE' === $unsupported_error->get_error_code(), 'Unsupported direct type did not fail closed.' );
$invalid_list = $service->list_content( array( 'post_type' => 'attachment' ) );
agentpress_ap013_assert( is_wp_error( $invalid_list ) && 'AP_UNSUPPORTED_POST_TYPE' === $invalid_list->get_error_code(), 'Unsupported list type did not fail closed.' );
$oversized = $validator->validate_input( array( 'per_page' => 101 ), $catalog['agentpress/list-content']['input_schema'] );
agentpress_ap013_assert( is_wp_error( $oversized ) && 'AP_SCHEMA_INVALID' === $oversized->get_error_code(), 'Oversized page did not fail schema.' );

wp_set_current_user( $users['author'] );
$author_input   = array( 'post_type' => 'post', 'status' => 'any', 'search' => 'AP013', 'per_page' => 100, 'orderby' => 'title', 'order' => 'asc' );
$author_visible = $list->execute( $author_input );
agentpress_ap013_assert( is_array( $author_visible ), 'Author list failed: ' . ( is_wp_error( $author_visible ) ? $author_visible->get_error_code() : gettype( $author_visible ) ) );
$author_ids     = array_column( $author_visible['data']['items'], 'id' );
agentpress_ap013_assert( in_array( $author_draft, $author_ids, true ) && ! in_array( $admin_draft, $author_ids, true ) && ! in_array( $admin_private, $author_ids, true ), 'Author list authority mismatch.' );
$own_draft = $get->execute( array( 'content_id' => $author_draft ) );
agentpress_ap013_assert( is_array( $own_draft ) && true === $own_draft['ok'], 'Author could not read own draft.' );
$other_draft = $get->check_permissions( array( 'content_id' => $admin_draft ) );
agentpress_ap013_assert( is_wp_error( $other_draft ) && 'AP_PERMISSION_DENIED' === $other_draft->get_error_code(), 'Author read another user draft by direct ID.' );

wp_set_current_user( $users['subscriber'] );
$subscriber_visible = $list->execute( array( 'post_type' => 'post', 'status' => 'any', 'search' => 'AP013', 'per_page' => 100, 'orderby' => 'title', 'order' => 'asc' ) );
agentpress_ap013_assert( is_array( $subscriber_visible ), 'Subscriber list failed: ' . ( is_wp_error( $subscriber_visible ) ? $subscriber_visible->get_error_code() : gettype( $subscriber_visible ) ) );
$subscriber_json    = wp_json_encode( $subscriber_visible );
agentpress_ap013_assert( 4 === $subscriber_visible['data']['total'], 'Subscriber visible total mismatch.' );
agentpress_ap013_assert( false === strpos( $subscriber_json, 'PRIVATE-DRAFT-SENTINEL-AP013' ) && false === strpos( $subscriber_json, 'PRIVATE-POST-SENTINEL-AP013' ), 'Private sentinel crossed list boundary.' );
$subscriber_public = $get->execute( array( 'content_id' => $public_alpha ) );
agentpress_ap013_assert( is_array( $subscriber_public ) && true === $subscriber_public['ok'], 'Subscriber could not read public post.' );
$subscriber_private = $get->check_permissions( array( 'content_id' => $admin_private ) );
agentpress_ap013_assert( is_wp_error( $subscriber_private ) && 'AP_PERMISSION_DENIED' === $subscriber_private->get_error_code(), 'Subscriber read private content.' );

wp_set_current_user( 0 );
$logged_out = $list->check_permissions( array() );
agentpress_ap013_assert( is_wp_error( $logged_out ) && 'AP_NOT_AUTHENTICATED' === $logged_out->get_error_code(), 'Logged-out list permission did not fail closed.' );

$target_mutations = 0;
foreach ( $snapshots as $post_id => $snapshot ) {
	$post = get_post( $post_id );
	if ( ! is_object( $post ) || $snapshot !== array( $post->post_status, $post->post_title, $post->post_content, $post->post_modified_gmt ) ) {
		++$target_mutations;
	}
}
agentpress_ap013_assert( 0 === $target_mutations, 'Read services mutated fixture targets.' );

foreach ( $created as $post_id ) {
	wp_delete_post( $post_id, true );
}
wp_delete_term( (int) $category['term_id'], 'category' );
wp_delete_term( (int) $tag['term_id'], 'post_tag' );
foreach ( $users as $user_id ) {
	wp_delete_user( $user_id );
}
unregister_post_type( 'ap013_secret' );

echo wp_json_encode( array( 'roles' => 3, 'deterministic_pages' => 2, 'list_schema_validations' => 2, 'get_schema_validations' => 1, 'filters' => 4, 'direct_id_denials' => 2, 'unsupported_or_oversized_denials' => 3, 'content_limit' => 50000, 'private_sentinels_absent' => 2, 'logged_out_denied' => true, 'target_mutations' => $target_mutations ) ) . "\n";
