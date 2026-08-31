<?php
/**
 * AP-007 Safe Mode, discovery, and live-capability acceptance harness.
 *
 * @package AgentPress
 */

use AgentPress\Activation;
use AgentPress\Changes\ChangeRepository;
use AgentPress\Changes\ChangeSetRepository;
use AgentPress\Policy\CapabilityEnvelope;
use AgentPress\Policy\DiscoveryPolicy;
use AgentPress\Policy\ExecutionPolicy;
use AgentPress\WebMCP\AbilityMap;

if ( ! defined( 'ABSPATH' ) ) {
	throw new RuntimeException( 'WordPress must be loaded.' );
}

require_once ABSPATH . 'wp-admin/includes/user.php';

/** @param bool $condition Condition. @param string $message Failure. @return void */
function agentpress_ap007_assert( $condition, $message ) {
	if ( ! $condition ) {
		throw new RuntimeException( $message );
	}
}

/** @param mixed $result Policy result. @param string $label Label. @return array<string, string> */
function agentpress_ap007_allowed( $result, $label ) {
	agentpress_ap007_assert( is_array( $result ), $label . ' was denied: ' . ( is_wp_error( $result ) ? $result->get_error_code() : 'invalid result' ) );
	return $result;
}

/** @param mixed $result Policy result. @param string $label Label. @return void */
function agentpress_ap007_denied( $result, $label ) {
	agentpress_ap007_assert( is_wp_error( $result ), $label . ' unexpectedly passed.' );
}

/** @param string $login Login. @param string $role Role. @return int */
function agentpress_ap007_user( $login, $role ) {
	$existing = get_user_by( 'login', $login );
	if ( $existing ) {
		wp_delete_user( $existing->ID );
	}
	$user_id = wp_insert_user(
		array(
			'user_login' => $login,
			'user_pass'  => wp_generate_password( 32, true, true ),
			'user_email' => $login . '@example.test',
			'role'       => $role,
		)
	);
	agentpress_ap007_assert( ! is_wp_error( $user_id ), 'Could not create ' . $role . ' fixture.' );
	return (int) $user_id;
}

/** @param int $author_id Author. @param string $type Type. @param string $status Status. @param string $title Title. @return int */
function agentpress_ap007_post( $author_id, $type, $status, $title ) {
	$post_id = wp_insert_post(
		array(
			'post_author' => $author_id,
			'post_type'   => $type,
			'post_status' => $status,
			'post_title'  => $title,
		),
		true
	);
	agentpress_ap007_assert( ! is_wp_error( $post_id ), 'Could not create ' . $title . '.' );
	return (int) $post_id;
}

Activation::activate();
$set_repo    = new ChangeSetRepository();
$change_repo = new ChangeRepository();
$user_ids    = array();
$post_ids    = array();
$set_ids     = array();
$change_ids  = array();

try {
	$roles = array( 'administrator', 'editor', 'author', 'subscriber' );
	foreach ( $roles as $role ) {
		$user_ids[ $role ] = agentpress_ap007_user( 'agentpress_ap007_' . $role, $role );
	}

	wp_set_current_user( $user_ids['administrator'] );
	foreach ( $roles as $index => $role ) {
		$post_ids[ $role ] = agentpress_ap007_post( $user_ids[ $role ], 'post', 'draft', 'AP-007 ' . $role . ' agent draft' );
		$set_ids[ $role ]  = $set_repo->create(
			array(
				'initiator_user_id' => $user_ids[ $role ],
				'title'             => 'AP-007 ' . $role,
				'request_summary'   => 'Synthetic policy fixture',
				'status'            => 'WORKING',
			)
		);
		$change_ids[ $role ] = $change_repo->create(
			array(
				'change_set_id'     => $set_ids[ $role ],
				'actor_user_id'     => $user_ids[ $role ],
				'ability'           => 'agentpress/create-draft',
				'risk_class'        => 'R1',
				'operation'         => 'create',
				'object_type'       => 'post',
				'object_id'         => $post_ids[ $role ],
				'before_json'       => array(),
				'after_json'        => array( 'post_status' => 'draft' ),
				'idempotency_hash'  => hash( 'sha256', 'ap007-key-' . $index ),
				'idempotency_scope' => hash( 'sha256', 'ap007-scope-' . $index ),
				'status'            => 'APPLIED',
			)
		);
	}

	$post_ids['author_non_agent'] = agentpress_ap007_post( $user_ids['author'], 'post', 'draft', 'AP-007 author ordinary draft' );
	$post_ids['author_published'] = agentpress_ap007_post( $user_ids['author'], 'post', 'publish', 'AP-007 author published' );
	$post_ids['editor_owned']     = agentpress_ap007_post( $user_ids['editor'], 'post', 'draft', 'AP-007 editor ordinary draft' );
	update_post_meta( $post_ids['author_non_agent'], '_agentpress_created', '1' );

	$expected = array(
		'administrator' => array( true, true, true, true, true, true, true, true ),
		'editor'        => array( true, true, true, true, true, true, true, false ),
		'author'        => array( true, true, false, true, true, false, true, false ),
		'subscriber'    => array( true, false, false, false, false, false, false, false ),
	);

	foreach ( $expected as $role => $matrix ) {
		wp_set_current_user( $user_ids[ $role ] );
		$discovery = new DiscoveryPolicy();
		$execution = new ExecutionPolicy();
		$envelope  = (new CapabilityEnvelope())->get();

		agentpress_ap007_assert( $matrix[0] === $discovery->can_discover( 'agentpress/get-context' ), $role . ' read discovery mismatch.' );
		$post_create = $execution->evaluate( 'agentpress/create-draft', array( 'post_type' => 'post' ) );
		$page_create = $execution->evaluate( 'agentpress/create-draft', array( 'post_type' => 'page' ) );
		$edit_own    = $execution->evaluate( 'agentpress/update-content', array( 'content_id' => $post_ids[ $role ] ) );
		$publish_own = $execution->evaluate( 'agentpress/publish-content', array( 'content_id' => $post_ids[ $role ] ) );
		$create_term = $execution->evaluate( 'agentpress/create-term', array( 'taxonomy' => 'category' ) );
		$assign_term = $execution->evaluate( 'agentpress/assign-terms', array( 'content_id' => $post_ids[ $role ], 'taxonomy' => 'category' ) );
		$navigation  = $execution->evaluate( 'agentpress/stage-navigation-change' );

		foreach ( array( array( $post_create, $matrix[1], 'post create' ), array( $page_create, $matrix[2], 'page create' ), array( $edit_own, $matrix[3], 'own AgentPress draft edit' ), array( $publish_own, $matrix[4], 'own publish stage' ), array( $create_term, $matrix[5], 'category creation' ), array( $assign_term, $matrix[6], 'category assignment' ), array( $navigation, $matrix[7], 'navigation stage' ) ) as $control ) {
			if ( $control[1] ) {
				agentpress_ap007_allowed( $control[0], $role . ' ' . $control[2] );
			} else {
				agentpress_ap007_denied( $control[0], $role . ' ' . $control[2] );
			}
		}

		agentpress_ap007_assert( ( $matrix[1] ? 'automatic' : 'unavailable' ) === $envelope['capabilities']['create_post_draft']['state'], $role . ' post envelope mismatch.' );
		agentpress_ap007_assert( ( $matrix[2] ? 'automatic' : 'unavailable' ) === $envelope['capabilities']['create_page_draft']['state'], $role . ' page envelope mismatch.' );
		agentpress_ap007_assert( CapabilityEnvelope::BLOCKED_AREAS === $envelope['blocked_areas'], $role . ' blocked areas changed.' );
		agentpress_ap007_assert( 16 === count( $envelope['capabilities'] ), $role . ' envelope key count mismatch.' );
	}

	wp_set_current_user( $user_ids['author'] );
	$author_policy = new ExecutionPolicy();
	agentpress_ap007_assert( array( 'risk' => 'R1', 'mode' => 'automatic' ) === agentpress_ap007_allowed( $author_policy->evaluate( 'agentpress/update-content', array( 'content_id' => $post_ids['author'] ) ), 'author applied draft' ), 'Applied draft was not automatic.' );
	agentpress_ap007_assert( array( 'risk' => 'R2', 'mode' => 'approval_required' ) === agentpress_ap007_allowed( $author_policy->evaluate( 'agentpress/update-content', array( 'content_id' => $post_ids['author_non_agent'] ) ), 'author ordinary draft' ), 'Post metadata incorrectly granted R1 authority.' );
	agentpress_ap007_assert( array( 'risk' => 'R2', 'mode' => 'approval_required' ) === agentpress_ap007_allowed( $author_policy->evaluate( 'agentpress/update-content', array( 'content_id' => $post_ids['author_published'] ) ), 'author published content' ), 'Published edit was not staged.' );
	agentpress_ap007_denied( $author_policy->evaluate( 'agentpress/update-content', array( 'content_id' => $post_ids['editor_owned'] ) ), 'author editing editor-owned draft' );

	$subscriber = get_user_by( 'id', $user_ids['subscriber'] );
	wp_set_current_user( $subscriber->ID );
	$before_mutation = (new CapabilityEnvelope())->get();
	agentpress_ap007_assert( 'unavailable' === $before_mutation['capabilities']['create_post_draft']['state'], 'Subscriber unexpectedly created posts before mutation.' );
	$subscriber->add_cap( 'edit_posts' );
	$subscriber->add_cap( 'publish_posts' );
	wp_set_current_user( 0 );
	clean_user_cache( $subscriber->ID );
	wp_set_current_user( $subscriber->ID );
	$mutated_discovery = new DiscoveryPolicy();
	$mutated_execution = new ExecutionPolicy();
	$after_mutation    = (new CapabilityEnvelope())->get();
	agentpress_ap007_assert( $mutated_discovery->can_discover( 'agentpress/create-draft' ), 'Capability-mutated subscriber discovery did not expand.' );
	agentpress_ap007_assert( 'automatic' === $after_mutation['capabilities']['create_post_draft']['state'], 'Capability-mutated subscriber envelope did not expand.' );
	agentpress_ap007_allowed( $mutated_execution->evaluate( 'agentpress/create-draft', array( 'post_type' => 'post' ) ), 'capability-mutated subscriber post create' );
	agentpress_ap007_allowed( $mutated_execution->evaluate( 'agentpress/publish-content', array( 'content_id' => $post_ids['subscriber'] ) ), 'capability-mutated subscriber publish stage' );

	wp_set_current_user( 0 );
	agentpress_ap007_assert( array() === (new DiscoveryPolicy())->discoverable(), 'Logged-out discovery returned tools.' );
	$logged_out_envelope = (new CapabilityEnvelope())->get();
	foreach ( $logged_out_envelope['capabilities'] as $operation ) {
		agentpress_ap007_assert( 'unavailable' === $operation['state'], 'Logged-out envelope granted an operation.' );
	}
	agentpress_ap007_assert( 'AP_NOT_AUTHENTICATED' === (new ExecutionPolicy())->evaluate( 'agentpress/get-context' )->get_error_code(), 'Logged-out execution returned the wrong error.' );

	wp_set_current_user( $user_ids['administrator'] );
	$admin_discovery = (new DiscoveryPolicy())->discoverable();
	agentpress_ap007_assert( 15 === count( AbilityMap::all() ), 'Fixed v0.1 map is not exactly 15 operations.' );
	foreach ( array( 'agentpress/manage-users', 'agentpress/install-plugin', 'agentpress/edit-theme', 'agentpress/edit-code', 'agentpress/manage-settings', 'agentpress/execute-sql', 'agentpress/execute-shell' ) as $forbidden ) {
		agentpress_ap007_assert( ! AbilityMap::contains( $forbidden ), $forbidden . ' exists in the fixed map.' );
		agentpress_ap007_assert( ! in_array( $forbidden, $admin_discovery, true ), $forbidden . ' was discovered.' );
		agentpress_ap007_assert( 'AP_POLICY_BLOCKED' === (new ExecutionPolicy())->evaluate( $forbidden )->get_error_code(), $forbidden . ' route guess did not fail closed.' );
	}

	WP_CLI::success(
		wp_json_encode(
			array(
				'default_roles'            => count( $expected ),
				'logged_out'              => true,
				'capability_mutation'      => true,
				'capability_operations'    => 16,
				'fixed_abilities'          => count( AbilityMap::all() ),
				'forbidden_route_guesses' => 7,
				'object_specific_controls' => 4,
			)
		)
	);
} finally {
	wp_set_current_user( $user_ids['administrator'] ?? 0 );
	foreach ( array_reverse( $change_ids ) as $change_id ) {
		$change_repo->delete( $change_id );
	}
	foreach ( array_reverse( $set_ids ) as $set_id ) {
		$set_repo->delete( $set_id );
	}
	foreach ( array_unique( $post_ids ) as $post_id ) {
		wp_delete_post( $post_id, true );
	}
	foreach ( array_reverse( $user_ids ) as $user_id ) {
		wp_delete_user( $user_id );
	}
	wp_set_current_user( 0 );
}
