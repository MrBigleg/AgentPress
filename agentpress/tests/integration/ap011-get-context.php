<?php
/**
 * AP-011 get-context runtime and privacy matrix.
 *
 * @package AgentPress
 */

use AgentPress\Abilities\AbilityCatalog;
use AgentPress\Policy\CapabilityEnvelope;
use AgentPress\Schemas\SchemaValidator;

/** @param bool $condition Condition. @param string $message Message. @return void */
function agentpress_ap011_assert( $condition, $message ) {
	if ( ! $condition ) {
		fwrite( STDERR, "AP-011 assertion failed: {$message}\n" );
		exit( 1 );
	}
}

/** @param string $login Login. @param string $role Role. @return int */
function agentpress_ap011_user( $login, $role ) {
	$existing = get_user_by( 'login', $login );
	$id       = $existing ? (int) $existing->ID : wp_create_user( $login, wp_generate_password( 24 ), $login . '@private.example.test' );
	$user     = new WP_User( $id );
	$user->set_role( $role );
	update_user_meta( $id, 'agentpress_private_path', 'C:\\private\\ap011' );
	update_user_meta( $id, 'session_tokens', array( 'private-cookie-sentinel' ) );
	return $id;
}

$https_home_filter = static function () {
	return 'https://agentpress-ap011.example.test/';
};
add_filter( 'home_url', $https_home_filter, 10, 4 );

$user_ids = array(
	'administrator' => agentpress_ap011_user( 'agentpress_ap011_administrator', 'administrator' ),
	'editor'        => agentpress_ap011_user( 'agentpress_ap011_editor', 'editor' ),
	'author'        => agentpress_ap011_user( 'agentpress_ap011_author', 'author' ),
	'subscriber'    => agentpress_ap011_user( 'agentpress_ap011_subscriber', 'subscriber' ),
);
$expected = array(
	'administrator' => array(),
	'editor'        => array( 'read_navigation', 'modify_navigation' ),
	'author'        => array( 'create_page_draft', 'edit_other_draft', 'publish_others_content', 'create_terms', 'read_navigation', 'modify_navigation' ),
	'subscriber'    => array( 'create_post_draft', 'create_page_draft', 'edit_own_agentpress_draft', 'edit_other_draft', 'edit_published_content', 'publish_own_content', 'publish_others_content', 'create_terms', 'assign_terms', 'read_navigation', 'modify_navigation' ),
);
$ability       = wp_get_ability( 'agentpress/get-context' );
$schema        = AbilityCatalog::all()['agentpress/get-context']['output_schema'];
$validator     = new SchemaValidator();
$operation_keys = array_keys( ( new CapabilityEnvelope() )->get()['capabilities'] );
$serializations = array();

foreach ( $user_ids as $role => $user_id ) {
	wp_set_current_user( $user_id );
	$result = $ability->execute( array() );
	$result_summary = is_wp_error( $result ) ? $result->get_error_code() . ': ' . $result->get_error_message() : wp_json_encode( $result );
	agentpress_ap011_assert( is_array( $result ) && true === $result['ok'], $role . ' execution failed: ' . $result_summary );
	agentpress_ap011_assert( true === $validator->validate_output( $result, $schema ), $role . ' output schema mismatch.' );
	agentpress_ap011_assert( $user_id === $result['data']['user']['id'] && array( $role ) === $result['data']['user']['roles'], $role . ' safe identity mismatch.' );
	agentpress_ap011_assert( $operation_keys === array_keys( $result['data']['capabilities'] ), $role . ' operation keys changed.' );
	foreach ( $result['data']['capabilities'] as $operation => $decision ) {
		$should_be_unavailable = in_array( $operation, $expected[ $role ], true );
		agentpress_ap011_assert( ( $should_be_unavailable ? 'unavailable' : ( in_array( $operation, array( 'edit_other_draft', 'edit_published_content', 'publish_own_content', 'publish_others_content', 'create_terms', 'modify_navigation' ), true ) ? 'approval_required' : 'automatic' ) ) === $decision['state'], $role . ' ' . $operation . ' state mismatch.' );
	}
	$serializations[] = wp_json_encode( $result );
}

$subscriber = get_user_by( 'id', $user_ids['subscriber'] );
$subscriber->add_cap( 'edit_posts' );
wp_set_current_user( 0 );
clean_user_cache( $subscriber->ID );
wp_set_current_user( $subscriber->ID );
$mutated = $ability->execute( array() );
agentpress_ap011_assert( 'automatic' === $mutated['data']['capabilities']['create_post_draft']['state'], 'Live capability mutation did not update context.' );
$subscriber->remove_cap( 'edit_posts' );

wp_set_current_user( 0 );
$denied = $ability->check_permissions( array() );
agentpress_ap011_assert( is_wp_error( $denied ) && 'AP_NOT_AUTHENTICATED' === $denied->get_error_code(), 'Logged-out Ability permission did not fail closed.' );

$json = implode( '', $serializations );
foreach ( array( '@private.example.test', 'manage_options', 'private-cookie-sentinel', 'C:\\private\\ap011', 'wp_rest', 'ABSPATH', 'DB_PASSWORD' ) as $sentinel ) {
	agentpress_ap011_assert( false === strpos( $json, $sentinel ), 'Private sentinel leaked: ' . $sentinel );
}

remove_filter( 'home_url', $https_home_filter, 10 );
foreach ( $user_ids as $user_id ) {
	wp_delete_user( $user_id );
}

echo wp_json_encode( array( 'roles' => count( $expected ), 'operations' => count( $operation_keys ), 'schema_validations' => count( $serializations ), 'capability_mutation' => true, 'private_sentinels_absent' => 7, 'logged_out_denied' => true, 'target_mutations' => 0 ) ) . "\n";
