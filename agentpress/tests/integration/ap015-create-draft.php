<?php
/**
 * AP-015 create-draft runtime matrix.
 *
 * @package AgentPress
 */

use AgentPress\Content\DraftCreationService;
use AgentPress\Storage\Migrator;

/** @param bool $condition Condition. @param string $message Message. @return void */
function agentpress_ap015_assert( $condition, $message ) {
	if ( ! $condition ) {
		fwrite( STDERR, "AP-015 assertion failed: {$message}\n" );
		exit( 1 );
	}
}

/** @param string $role Role. @return int */
function agentpress_ap015_user( $role ) {
	$suffix  = strtolower( wp_generate_password( 8, false ) );
	$user_id = wp_create_user( 'ap015-' . $role . '-' . $suffix, wp_generate_password( 24 ), 'ap015-' . $role . '-' . $suffix . '@example.test' );
	agentpress_ap015_assert( ! is_wp_error( $user_id ), 'Could not create ' . $role . ' user.' );
	( new WP_User( $user_id ) )->set_role( $role );
	return (int) $user_id;
}

/** @param string $marker Marker. @return array<int> */
function agentpress_ap015_ids( $marker ) {
	return array_map(
		'intval',
		get_posts(
			array(
				'post_type'      => array( 'post', 'page' ),
				'post_status'    => 'any',
				's'              => $marker,
				'fields'         => 'ids',
				'posts_per_page' => -1,
			)
		)
	);
}

global $wpdb;
Migrator::migrate();
foreach ( array( 'agentpress_changes', 'agentpress_change_sets' ) as $suffix ) {
	$wpdb->query( $wpdb->prepare( 'DELETE FROM %i', $wpdb->prefix . $suffix ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
}

$users = array(
	'administrator' => agentpress_ap015_user( 'administrator' ),
	'editor'        => agentpress_ap015_user( 'editor' ),
	'author'        => agentpress_ap015_user( 'author' ),
	'subscriber'    => agentpress_ap015_user( 'subscriber' ),
);
$limited_page_user = agentpress_ap015_user( 'subscriber' );
$limited            = new WP_User( $limited_page_user );
foreach ( array( 'read', 'edit_pages', 'edit_others_pages', 'create_pages' ) as $capability ) {
	$limited->add_cap( $capability );
}

wp_set_current_user( $users['administrator'] );
$public_parent  = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => 'AP015 Public Parent' ) );
$private_parent = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'private', 'post_title' => 'AP015 Private Parent' ) );
$non_page       = wp_insert_post( array( 'post_type' => 'post', 'post_status' => 'publish', 'post_title' => 'AP015 Non Page' ) );
$fixture_ids    = array( $public_parent, $private_parent, $non_page );
$ability        = wp_get_ability( 'agentpress/create-draft' );
agentpress_ap015_assert( is_object( $ability ), 'Registered create-draft Ability missing.' );

$created_ids = array();
$role_cases  = array(
	'administrator' => array( 'post', 'page' ),
	'editor'        => array( 'post', 'page' ),
	'author'        => array( 'post' ),
);
foreach ( $role_cases as $role => $types ) {
	wp_set_current_user( $users[ $role ] );
	foreach ( $types as $type ) {
		$input = array(
			'post_type'       => $type,
			'title'           => 'AP015 ' . ucfirst( $role ) . ' ' . $type,
			'content'         => '<strong>allowed</strong><script>blocked()</script>',
			'excerpt'         => '<em>excerpt</em><script>blocked()</script>',
			'slug'            => 'ap015-' . $role . '-' . $type,
			'idempotency_key' => 'ap015-' . $role . '-' . $type,
		);
		if ( 'page' === $type ) {
			$input['parent_id'] = $public_parent;
		}
		agentpress_ap015_assert( true === $ability->check_permissions( $input ), $role . ' ' . $type . ' permission failed.' );
		$result = $ability->execute( $input );
		agentpress_ap015_assert( is_array( $result ) && true === $result['ok'], $role . ' ' . $type . ' execution failed.' );
		$data      = $result['data'];
		$post      = get_post( $data['content_id'] );
		$created_ids[] = (int) $post->ID;
		agentpress_ap015_assert( 'APPLIED' === $data['status'] && 'draft' === $data['post_status'] && false === $data['replayed'], 'First application result mismatch.' );
		agentpress_ap015_assert( 'draft' === $post->post_status && $type === $post->post_type && $users[ $role ] === (int) $post->post_author, 'Forced draft identity mismatch.' );
		agentpress_ap015_assert( ( 'page' === $type ? $public_parent : 0 ) === (int) $post->post_parent, 'Parent mismatch.' );
		agentpress_ap015_assert( 0 === strpos( $data['change_set_ref'], 'AP-' ) && 0 === strpos( $data['edit_url'], 'https://' ), 'Reference/edit URL mismatch.' );
		if ( 'author' === $role ) {
			agentpress_ap015_assert( false !== strpos( $post->post_content, '<strong>allowed</strong>' ) && false === strpos( $post->post_content, '<script' ), $role . ' KSES content mismatch.' );
			agentpress_ap015_assert( false !== strpos( $post->post_excerpt, '<em>excerpt</em>' ) && false === strpos( $post->post_excerpt, '<script' ), $role . ' KSES excerpt mismatch.' );
		} elseif ( current_user_can( 'unfiltered_html' ) ) {
			agentpress_ap015_assert( false !== strpos( $post->post_content, '<script>blocked()</script>' ), $role . ' unfiltered HTML capability was not followed.' );
		}

		$replay = $ability->execute( $input );
		agentpress_ap015_assert( is_array( $replay ) && true === $replay['ok'] && true === $replay['data']['replayed'] && $data['content_id'] === $replay['data']['content_id'] && $data['change_id'] === $replay['data']['change_id'], 'Identical replay mismatch.' );
	}
}

wp_set_current_user( $users['author'] );
$author_page = array( 'post_type' => 'page', 'title' => 'AP015 Author Page', 'idempotency_key' => 'ap015-author-page' );
agentpress_ap015_assert( is_wp_error( $ability->check_permissions( $author_page ) ), 'Author page permission passed.' );
wp_set_current_user( $users['subscriber'] );
$subscriber_post = array( 'post_type' => 'post', 'title' => 'AP015 Subscriber Post', 'idempotency_key' => 'ap015-subscriber-post' );
agentpress_ap015_assert( is_wp_error( $ability->check_permissions( $subscriber_post ) ), 'Subscriber permission passed.' );
wp_set_current_user( 0 );
agentpress_ap015_assert( is_wp_error( $ability->check_permissions( $subscriber_post ) ), 'Logged-out permission passed.' );

wp_set_current_user( $users['administrator'] );
$service        = new DraftCreationService();
$negative_count = count( agentpress_ap015_ids( 'AP015 Negative' ) );
$negative_cases = array(
	array( 'post_type' => 'post', 'title' => 'AP015 Negative Status', 'status' => 'publish', 'idempotency_key' => 'ap015-bad-status' ),
	array( 'post_type' => 'post', 'title' => 'AP015 Negative Unknown', 'unknown' => true, 'idempotency_key' => 'ap015-bad-unknown' ),
	array( 'post_type' => 'attachment', 'title' => 'AP015 Negative Type', 'idempotency_key' => 'ap015-bad-type' ),
	array( 'post_type' => 'post', 'title' => 'AP015 Negative Parent', 'parent_id' => $public_parent, 'idempotency_key' => 'ap015-post-parent' ),
	array( 'post_type' => 'page', 'title' => 'AP015 Negative Nonpage', 'parent_id' => $non_page, 'idempotency_key' => 'ap015-nonpage-parent' ),
);
foreach ( $negative_cases as $case ) {
	$denied = $service->execute( $case );
	agentpress_ap015_assert( is_wp_error( $denied ), 'Negative direct-service case succeeded.' );
}

wp_set_current_user( $limited_page_user );
$unreadable_parent = $service->execute( array( 'post_type' => 'page', 'title' => 'AP015 Negative Private Parent', 'parent_id' => $private_parent, 'idempotency_key' => 'ap015-private-parent' ) );
agentpress_ap015_assert( is_wp_error( $unreadable_parent ) && 'AP_PERMISSION_DENIED' === $unreadable_parent->get_error_code(), 'Unreadable parent succeeded or returned wrong error.' );
agentpress_ap015_assert( $negative_count === count( agentpress_ap015_ids( 'AP015 Negative' ) ), 'Denied case mutated content.' );

wp_set_current_user( $users['administrator'] );
$conflict_input = array( 'post_type' => 'post', 'title' => 'AP015 Conflict Original', 'idempotency_key' => 'ap015-conflict-key' );
$conflict_first = $service->execute( $conflict_input );
agentpress_ap015_assert( is_array( $conflict_first ), 'Conflict fixture first mutation failed.' );
$created_ids[]          = (int) $conflict_first['data']['content_id'];
$conflict_input['title'] = 'AP015 Conflict Changed';
$conflict = $service->execute( $conflict_input );
agentpress_ap015_assert( is_wp_error( $conflict ) && 'AP_STATE_CONFLICT' === $conflict->get_error_code(), 'Changed replay did not conflict.' );
agentpress_ap015_assert( 1 === count( agentpress_ap015_ids( 'AP015 Conflict' ) ), 'Changed replay created a second post.' );

$change_rows = (int) $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM %i WHERE ability = %s AND status = %s', $wpdb->prefix . 'agentpress_changes', 'agentpress/create-draft', 'APPLIED' ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
$set_rows    = (int) $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM %i', $wpdb->prefix . 'agentpress_change_sets' ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
agentpress_ap015_assert( count( $created_ids ) === $change_rows && $change_rows === $set_rows, 'Durable intent/result counts mismatch.' );

foreach ( array_merge( $created_ids, $fixture_ids ) as $post_id ) {
	wp_delete_post( $post_id, true );
}
foreach ( $users as $user_id ) {
	wp_delete_user( $user_id );
}
wp_delete_user( $limited_page_user );
foreach ( array( 'agentpress_changes', 'agentpress_change_sets' ) as $suffix ) {
	$wpdb->query( $wpdb->prepare( 'DELETE FROM %i', $wpdb->prefix . $suffix ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
}

echo wp_json_encode( array( 'roles_applied' => 5, 'role_denials' => 3, 'schema_type_parent_denials' => 6, 'kses_capability_controls' => 3, 'idempotent_replays' => 5, 'conflicts' => 1, 'applied_changes' => $change_rows, 'target_mutations_on_denial' => 0 ) ) . "\n";
