<?php
/**
 * AP-012 visible bounded site-structure matrix.
 *
 * @package AgentPress
 */

use AgentPress\Abilities\AbilityCatalog;
use AgentPress\Schemas\SchemaValidator;

/** @param bool $condition Condition. @param string $message Message. @return void */
function agentpress_ap012_assert( $condition, $message ) {
	if ( ! $condition ) {
		fwrite( STDERR, "AP-012 assertion failed: {$message}\n" );
		exit( 1 );
	}
}

/** @param string $login Login. @param string $role Role. @return int */
function agentpress_ap012_user( $login, $role ) {
	$existing = get_user_by( 'login', $login );
	$id       = $existing ? (int) $existing->ID : wp_create_user( $login, wp_generate_password( 24 ), $login . '@private.example.test' );
	$user     = new WP_User( $id );
	$user->set_role( $role );
	return $id;
}

register_nav_menu( 'primary', 'Synthetic AP-012 Primary' );
$user_ids = array(
	'administrator' => agentpress_ap012_user( 'agentpress_ap012_administrator', 'administrator' ),
	'author'        => agentpress_ap012_user( 'agentpress_ap012_author', 'author' ),
	'subscriber'    => agentpress_ap012_user( 'agentpress_ap012_subscriber', 'subscriber' ),
);
$ability   = wp_get_ability( 'agentpress/get-site-structure' );
$schema    = AbilityCatalog::all()['agentpress/get-site-structure']['output_schema'];
$validator = new SchemaValidator();
$baseline  = array();

foreach ( $user_ids as $role => $user_id ) {
	wp_set_current_user( $user_id );
	$result            = $ability->execute( array() );
	$baseline[ $role ] = $result['data']['content_counts'];
}

$created   = array();
$parent    = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => 'AP012 Parent', 'post_content' => 'PUBLIC-BODY-SENTINEL-AP012', 'post_author' => $user_ids['author'] ) );
$created[] = $parent;
$child     = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => 'AP012 Child', 'post_parent' => $parent, 'post_author' => $user_ids['author'] ) );
$created[] = $child;
for ( $index = 0; $index < 200; ++$index ) {
	$created[] = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => 'AP012 Public ' . $index, 'post_author' => $user_ids['author'] ) );
}
$private_page = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'private', 'post_title' => 'PRIVATE-TITLE-SENTINEL-AP012', 'post_content' => 'PRIVATE-BODY-SENTINEL-AP012', 'post_author' => $user_ids['author'] ) );
$draft_page   = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'draft', 'post_title' => 'AP012 Draft', 'post_author' => $user_ids['author'] ) );
$public_post  = wp_insert_post( array( 'post_type' => 'post', 'post_status' => 'publish', 'post_title' => 'AP012 Public Post', 'post_author' => $user_ids['author'] ) );
$private_post = wp_insert_post( array( 'post_type' => 'post', 'post_status' => 'private', 'post_title' => 'AP012 Private Post', 'post_author' => $user_ids['author'] ) );
$created      = array_merge( $created, array( $private_page, $draft_page, $public_post, $private_post ) );

$increments = array(
	'administrator' => array( 'page' => 204, 'post' => 2 ),
	'author'        => array( 'page' => 204, 'post' => 2 ),
	'subscriber'    => array( 'page' => 202, 'post' => 1 ),
);
$results = array();

foreach ( $user_ids as $role => $user_id ) {
	wp_set_current_user( $user_id );
	$result = $ability->execute( array() );
	agentpress_ap012_assert( is_array( $result ) && true === $result['ok'], $role . ' execution failed.' );
	agentpress_ap012_assert( true === $validator->validate_output( $result, $schema ), $role . ' schema mismatch.' );
	agentpress_ap012_assert( $baseline[ $role ]['page'] + $increments[ $role ]['page'] === $result['data']['content_counts']['page'], $role . ' page count mismatch.' );
	agentpress_ap012_assert( $baseline[ $role ]['post'] + $increments[ $role ]['post'] === $result['data']['content_counts']['post'], $role . ' post count mismatch.' );
	agentpress_ap012_assert( 200 === count( $result['data']['pages'] ) && true === $result['data']['truncated'], $role . ' cap/truncation mismatch.' );
	agentpress_ap012_assert( array( 'category', 'post_tag' ) === array_column( $result['data']['taxonomies'], 'name' ), $role . ' taxonomy allowlist mismatch.' );
	$locations = array_column( $result['data']['menu_locations'], null, 'location' );
	agentpress_ap012_assert( isset( $locations['primary'] ) && false === $locations['primary']['assigned'] && 0 === $locations['primary']['menu_id'], $role . ' menu-location summary mismatch.' );
	$results[ $role ] = $result;
}

$author_pages = array_column( $results['author']['data']['pages'], null, 'id' );
agentpress_ap012_assert( isset( $author_pages[ $parent ], $author_pages[ $child ] ) && $parent === $author_pages[ $child ]['parent_id'], 'Visible hierarchy mismatch.' );
$subscriber_json = wp_json_encode( $results['subscriber'] );
$all_json        = wp_json_encode( $results );
agentpress_ap012_assert( false === strpos( $subscriber_json, 'PRIVATE-TITLE-SENTINEL-AP012' ), 'Subscriber saw private page.' );
foreach ( array( 'PUBLIC-BODY-SENTINEL-AP012', 'PRIVATE-BODY-SENTINEL-AP012', 'destination_url', 'menu_item' ) as $sentinel ) {
	agentpress_ap012_assert( false === strpos( $all_json, $sentinel ), 'Full content or destination leaked: ' . $sentinel );
}

wp_set_current_user( 0 );
$denied = $ability->check_permissions( array() );
agentpress_ap012_assert( is_wp_error( $denied ) && 'AP_NOT_AUTHENTICATED' === $denied->get_error_code(), 'Logged-out permission did not fail closed.' );

foreach ( $created as $post_id ) {
	wp_delete_post( $post_id, true );
}
foreach ( $user_ids as $user_id ) {
	wp_delete_user( $user_id );
}
unregister_nav_menu( 'primary' );

echo wp_json_encode( array( 'roles' => 3, 'visible_page_cap' => 200, 'public_pages' => 202, 'private_pages_hidden_from_subscriber' => 2, 'taxonomies' => 2, 'menu_locations' => 1, 'schema_validations' => 3, 'content_destination_sentinels_absent' => 4, 'logged_out_denied' => true, 'target_mutations' => 0 ) ) . "\n";
