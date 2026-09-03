<?php
/**
 * AP-016 update-content runtime matrix.
 *
 * @package AgentPress
 */

use AgentPress\Content\ContentUpdateService;
use AgentPress\Content\DraftCreationService;
use AgentPress\Storage\Migrator;

/**
 * Assert one AP-016 condition.
 *
 * @param bool   $condition Condition.
 * @param string $message   Message.
 * @return void
 */
function agentpress_ap016_assert( $condition, $message ) {
	if ( ! $condition ) {
		fwrite( STDERR, "AP-016 assertion failed: {$message}\n" );
		exit( 1 );
	}
}

/**
 * Create one synthetic role fixture.
 *
 * @param string $role Role.
 * @return int
 */
function agentpress_ap016_user( $role ) {
	$suffix  = strtolower( wp_generate_password( 8, false ) );
	$user_id = wp_create_user( 'ap016-' . $role . '-' . $suffix, wp_generate_password( 24 ), 'ap016-' . $role . '-' . $suffix . '@example.test' );
	agentpress_ap016_assert( ! is_wp_error( $user_id ), 'Could not create ' . $role . ' user.' );
	( new WP_User( $user_id ) )->set_role( $role );
	return (int) $user_id;
}

global $wpdb;
Migrator::migrate();
foreach ( array( 'agentpress_changes', 'agentpress_change_sets' ) as $suffix ) {
	$wpdb->query( $wpdb->prepare( 'DELETE FROM %i', $wpdb->prefix . $suffix ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
}

$users   = array(
	'administrator' => agentpress_ap016_user( 'administrator' ),
	'author'        => agentpress_ap016_user( 'author' ),
	'subscriber'    => agentpress_ap016_user( 'subscriber' ),
);
$drafts  = new DraftCreationService();
$service = new ContentUpdateService();

wp_set_current_user( $users['administrator'] );
$parent_id = wp_insert_post(
	array(
		'post_type'   => 'page',
		'post_status' => 'publish',
		'post_title'  => 'AP016 Parent',
	)
);
$created   = $drafts->execute(
	array(
		'post_type'       => 'page',
		'title'           => 'AP016 Agent Draft',
		'idempotency_key' => 'ap016-create-page',
	)
);
agentpress_ap016_assert( is_array( $created ), 'AgentPress page fixture failed.' );
$agent_id = (int) $created['data']['content_id'];
$ability  = wp_get_ability( 'agentpress/update-content' );
agentpress_ap016_assert( is_object( $ability ), 'Registered update-content Ability missing.' );

$apply_input = array(
	'content_id'      => $agent_id,
	'title'           => 'AP016 Updated Title',
	'content'         => '<strong>allowed</strong><script>admin-allowed()</script>',
	'excerpt'         => '<em>excerpt</em>',
	'slug'            => 'ap016-updated-page',
	'parent_id'       => $parent_id,
	'idempotency_key' => 'ap016-apply-update',
);
agentpress_ap016_assert( true === $ability->check_permissions( $apply_input ), 'Administrator update permission failed.' );
$applied = $ability->execute( $apply_input );
$updated_post = get_post( $agent_id );
agentpress_ap016_assert( is_array( $applied ) && true === $applied['ok'] && 'APPLIED' === $applied['data']['status'] && false === $applied['data']['approval_required'] && '' === $applied['data']['expires_at'], 'AgentPress draft was not applied.' );
agentpress_ap016_assert( 'AP016 Updated Title' === $updated_post->post_title && 'ap016-updated-page' === $updated_post->post_name && (int) $updated_post->post_parent === $parent_id, 'Applied fields mismatch.' );
$replay = $ability->execute( $apply_input );
agentpress_ap016_assert( is_array( $replay ) && true === $replay['data']['replayed'] && $applied['data']['change_id'] === $replay['data']['change_id'], 'Applied request did not replay.' );

$ordinary_id  = wp_insert_post(
	array(
		'post_type'   => 'post',
		'post_status' => 'draft',
		'post_title'  => 'AP016 Ordinary Draft',
		'post_author' => $users['administrator'],
	)
);
$published_id = wp_insert_post(
	array(
		'post_type'   => 'post',
		'post_status' => 'publish',
		'post_title'  => 'AP016 Published',
		'post_author' => $users['administrator'],
	)
);
foreach ( array( $ordinary_id, $published_id ) as $target_id ) {
	$before = get_post( $target_id )->post_title;
	$input  = array(
		'content_id'      => $target_id,
		'title'           => $before . ' Proposed',
		'idempotency_key' => 'ap016-stage-' . $target_id,
	);
	$result = $ability->execute( $input );
	agentpress_ap016_assert( is_array( $result ) && 'PENDING_APPROVAL' === $result['data']['status'] && true === $result['data']['approval_required'] && '' !== $result['data']['expires_at'], 'Ordinary/published target did not stage.' );
	agentpress_ap016_assert( get_post( $target_id )->post_title === $before, 'Staging mutated target.' );
	$stored = $wpdb->get_row( $wpdb->prepare( 'SELECT before_json, after_json, target_state_hash, proposal_hash FROM %i WHERE id = %d', $wpdb->prefix . 'agentpress_changes', $result['data']['change_id'] ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	agentpress_ap016_assert( is_array( $stored ) && 64 === strlen( $stored['target_state_hash'] ) && 64 === strlen( $stored['proposal_hash'] ) && false !== strpos( $stored['after_json'], 'Proposed' ), 'Immutable staged proposal is incomplete.' );
}

$empty          = $service->execute(
	array(
		'content_id'      => $agent_id,
		'idempotency_key' => 'ap016-empty-patch',
	)
);
$status_field   = $service->execute(
	array(
		'content_id'      => $agent_id,
		'status'          => 'publish',
		'title'           => 'Denied',
		'idempotency_key' => 'ap016-status-field',
	)
);
$post_parent    = $service->execute(
	array(
		'content_id'      => $ordinary_id,
		'parent_id'       => 0,
		'idempotency_key' => 'ap016-post-parent',
	)
);
$missing_parent = $service->execute(
	array(
		'content_id'      => $agent_id,
		'parent_id'       => 999999999,
		'idempotency_key' => 'ap016-missing-parent',
	)
);
agentpress_ap016_assert( is_wp_error( $empty ) && 'AP_SCHEMA_INVALID' === $empty->get_error_code(), 'Empty patch passed.' );
agentpress_ap016_assert( is_wp_error( $status_field ) && 'AP_SCHEMA_INVALID' === $status_field->get_error_code(), 'Status field passed.' );
agentpress_ap016_assert( is_wp_error( $post_parent ) && 'AP_SCHEMA_INVALID' === $post_parent->get_error_code(), 'Post parent passed.' );
agentpress_ap016_assert( is_wp_error( $missing_parent ) && 'AP_CONTENT_NOT_FOUND' === $missing_parent->get_error_code(), 'Missing parent passed.' );

wp_set_current_user( $users['author'] );
$author_created = $drafts->execute(
	array(
		'post_type'       => 'post',
		'title'           => 'AP016 Author Draft',
		'idempotency_key' => 'ap016-author-create',
	)
);
agentpress_ap016_assert( is_array( $author_created ), 'Author fixture failed.' );
$author_id     = (int) $author_created['data']['content_id'];
$author_result = $ability->execute(
	array(
		'content_id'      => $author_id,
		'content'         => '<strong>safe</strong><script>blocked()</script>',
		'idempotency_key' => 'ap016-author-update',
	)
);
agentpress_ap016_assert( is_array( $author_result ) && 'APPLIED' === $author_result['data']['status'] && false === strpos( get_post( $author_id )->post_content, '<script' ), 'Author own-draft KSES update failed.' );
$other_input = array(
	'content_id'      => $agent_id,
	'title'           => 'Forbidden',
	'idempotency_key' => 'ap016-author-other',
);
agentpress_ap016_assert( is_wp_error( $ability->check_permissions( $other_input ) ), 'Author could update another user target.' );
wp_set_current_user( $users['subscriber'] );
agentpress_ap016_assert( is_wp_error( $ability->check_permissions( $other_input ) ), 'Subscriber permission passed.' );
wp_set_current_user( 0 );
agentpress_ap016_assert( is_wp_error( $ability->check_permissions( $other_input ) ), 'Logged-out permission passed.' );

wp_set_current_user( $users['administrator'] );
foreach ( array( $agent_id, $author_id, $ordinary_id, $published_id, $parent_id ) as $cleanup_post_id ) {
	wp_delete_post( $cleanup_post_id, true );
}
foreach ( $users as $user_id ) {
	wp_delete_user( $user_id );
}
$applied_count = (int) $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM %i WHERE ability = %s AND status = %s', $wpdb->prefix . 'agentpress_changes', 'agentpress/update-content', 'APPLIED' ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
$pending_count = (int) $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM %i WHERE ability = %s AND status = %s', $wpdb->prefix . 'agentpress_changes', 'agentpress/update-content', 'PENDING_APPROVAL' ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
foreach ( array( 'agentpress_changes', 'agentpress_change_sets' ) as $suffix ) {
	$wpdb->query( $wpdb->prepare( 'DELETE FROM %i', $wpdb->prefix . $suffix ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
}

echo wp_json_encode(
	array(
		'r1_applied'              => $applied_count,
		'r2_staged'               => $pending_count,
		'staged_target_mutations' => 0,
		'schema_parent_denials'   => 4,
		'role_denials'            => 3,
		'idempotent_replays'      => 1,
	)
) . "\n";
