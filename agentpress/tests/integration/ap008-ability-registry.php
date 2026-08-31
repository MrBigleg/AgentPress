<?php
/**
 * AP-008 fixed Ability registry and REST/discovery acceptance harness.
 *
 * @package AgentPress
 */

use AgentPress\Abilities\AbilityCatalog;
use AgentPress\Policy\DiscoveryPolicy;
use AgentPress\Rest\WebMCPRoutes;
use AgentPress\WebMCP\AbilityMap;

if ( ! defined( 'ABSPATH' ) ) {
	throw new RuntimeException( 'WordPress must be loaded.' );
}

require_once ABSPATH . 'wp-admin/includes/user.php';

/**
 * Assert one AP-008 condition.
 *
 * @param bool   $condition Condition.
 * @param string $message   Failure message.
 * @return void
 */
function agentpress_ap008_assert( $condition, $message ) {
	if ( ! $condition ) {
		throw new RuntimeException( $message );
	}
}

/**
 * Create one synthetic user.
 *
 * @param string $login Login.
 * @param string $role  Role.
 * @return int
 */
function agentpress_ap008_user( $login, $role ) {
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
	agentpress_ap008_assert( ! is_wp_error( $user_id ), 'Could not create ' . $role . ' fixture.' );
	return (int) $user_id;
}

$user_ids = array();

try {
	$user_ids['administrator'] = agentpress_ap008_user( 'agentpress_ap008_administrator', 'administrator' );
	$user_ids['subscriber']    = agentpress_ap008_user( 'agentpress_ap008_subscriber', 'subscriber' );

	$catalog            = AbilityCatalog::all();
	$registered         = wp_get_abilities();
	$agentpress_runtime = array_intersect_key( $registered, AbilityMap::all() );
	$category           = wp_get_ability_category( AbilityCatalog::CATEGORY );

	agentpress_ap008_assert( 15 === count( $catalog ), 'Catalog count is not 15.' );
	agentpress_ap008_assert( 15 === count( $agentpress_runtime ), 'Runtime AgentPress Ability count is not 15.' );
	agentpress_ap008_assert( array_keys( AbilityMap::all() ) === array_keys( $agentpress_runtime ), 'Runtime names or order differ from the fixed map.' );
	agentpress_ap008_assert( is_object( $category ), 'AgentPress category is not registered.' );
	agentpress_ap008_assert( 'AgentPress' === $category->get_label(), 'AgentPress category label changed.' );

	foreach ( $catalog as $ability_name => $expected ) {
		$ability = $agentpress_runtime[ $ability_name ];
		$meta    = $ability->get_meta();
		agentpress_ap008_assert( $expected['label'] === $ability->get_label(), $ability_name . ' label mismatch.' );
		agentpress_ap008_assert( $expected['description'] === $ability->get_description(), $ability_name . ' description mismatch.' );
		agentpress_ap008_assert( AbilityCatalog::CATEGORY === $ability->get_category(), $ability_name . ' category mismatch.' );
		agentpress_ap008_assert( wp_json_encode( $expected['input_schema'] ) === wp_json_encode( $ability->get_input_schema() ), $ability_name . ' input schema mismatch.' );
		agentpress_ap008_assert( wp_json_encode( $expected['output_schema'] ) === wp_json_encode( $ability->get_output_schema() ), $ability_name . ' output schema mismatch.' );
		agentpress_ap008_assert( false === $meta['show_in_rest'], $ability_name . ' exposed native REST.' );
		agentpress_ap008_assert( isset( $meta['annotations']['readonly'], $meta['annotations']['readOnlyHint'], $meta['annotations']['untrustedContentHint'] ), $ability_name . ' annotations missing.' );
		agentpress_ap008_assert( wp_json_encode( $expected['meta'] ) === wp_json_encode( $meta ), $ability_name . ' metadata or annotation mismatch.' );
	}

	wp_set_current_user( $user_ids['administrator'] );
	agentpress_ap008_assert( true === wp_get_ability( 'agentpress/get-context' )->check_permissions( array() ), 'Administrator get-context permission callback failed.' );
	agentpress_ap008_assert( true === wp_get_ability( 'agentpress/create-draft' )->check_permissions( array( 'post_type' => 'post', 'title' => 'Synthetic', 'idempotency_key' => 'ap008-admin-key' ) ), 'Administrator create-draft permission callback failed.' );
	$missing_target = wp_get_ability( 'agentpress/get-content' )->check_permissions( array( 'content_id' => PHP_INT_MAX ) );
	agentpress_ap008_assert( is_wp_error( $missing_target ) && 'AP_CONTENT_NOT_FOUND' === $missing_target->get_error_code(), 'Object-specific callback did not fail closed.' );

	$post_count_before = (int) $GLOBALS['wpdb']->get_var( "SELECT COUNT(*) FROM {$GLOBALS['wpdb']->posts}" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$https_home_filter = static function () {
		return 'https://agentpress-ap008.example.test/';
	};
	add_filter( 'home_url', $https_home_filter, 10, 4 );
	$context_result    = wp_get_ability( 'agentpress/get-context' )->execute( array() );
	remove_filter( 'home_url', $https_home_filter, 10 );
	agentpress_ap008_assert( is_array( $context_result ) && true === $context_result['ok'], 'Implemented get-context callback failed.' );

	$rest_list     = rest_do_request( new WP_REST_Request( 'GET', '/wp-abilities/v1/abilities' ) );
	$listed_names  = array();
	$rest_list_data = $rest_list->get_data();
	if ( is_array( $rest_list_data ) ) {
		foreach ( $rest_list_data as $listed ) {
			if ( is_array( $listed ) && isset( $listed['name'] ) ) {
				$listed_names[] = $listed['name'];
			}
		}
	}
	agentpress_ap008_assert( array() === array_values( array_intersect( array_keys( AbilityMap::all() ), $listed_names ) ), 'Native REST listed an AgentPress Ability.' );

	$native_read = rest_do_request( new WP_REST_Request( 'GET', '/wp-abilities/v1/abilities/agentpress/get-context/run' ) );
	$native_write = rest_do_request( new WP_REST_Request( 'POST', '/wp-abilities/v1/abilities/agentpress/create-draft/run' ) );
	agentpress_ap008_assert( 404 === $native_read->get_status() && 'rest_ability_not_found' === $native_read->get_data()['code'], 'Native REST read route reached AgentPress.' );
	agentpress_ap008_assert( 404 === $native_write->get_status() && 'rest_ability_not_found' === $native_write->get_data()['code'], 'Native REST write route reached AgentPress.' );
	agentpress_ap008_assert( $post_count_before === (int) $GLOBALS['wpdb']->get_var( "SELECT COUNT(*) FROM {$GLOBALS['wpdb']->posts}" ), 'Native REST or placeholder execution mutated posts.' ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared

	$admin_definitions = ( new WebMCPRoutes() )->default_definitions();
	agentpress_ap008_assert( 15 === count( $admin_definitions ), 'Administrator bridge discovery did not return 15 tools.' );
	foreach ( $admin_definitions as $definition ) {
		agentpress_ap008_assert( AbilityMap::tool_name( $definition['ability'] ) === $definition['name'], $definition['ability'] . ' bridge name mismatch.' );
		agentpress_ap008_assert( array( 'readOnlyHint', 'untrustedContentHint' ) === array_keys( $definition['annotations'] ), $definition['ability'] . ' leaked core annotations.' );
	}

	wp_set_current_user( $user_ids['subscriber'] );
	$subscriber_expected = ( new DiscoveryPolicy() )->discoverable();
	$subscriber_actual   = array_column( ( new WebMCPRoutes() )->default_definitions(), 'ability' );
	agentpress_ap008_assert( $subscriber_expected === $subscriber_actual, 'Subscriber bridge discovery exceeded AP-007 policy.' );
	agentpress_ap008_assert( is_wp_error( wp_get_ability( 'agentpress/create-draft' )->check_permissions( array( 'post_type' => 'post', 'title' => 'Denied', 'idempotency_key' => 'ap008-denied-key' ) ) ), 'Subscriber create-draft permission callback passed.' );

	wp_set_current_user( 0 );
	agentpress_ap008_assert( array() === ( new WebMCPRoutes() )->default_definitions(), 'Logged-out bridge discovery returned tools.' );

	foreach ( array( 'agentpress/manage-users', 'agentpress/install-plugin', 'agentpress/edit-theme', 'agentpress/edit-code', 'agentpress/manage-settings', 'agentpress/execute-sql', 'agentpress/execute-shell' ) as $forbidden ) {
		agentpress_ap008_assert( ! wp_has_ability( $forbidden ), $forbidden . ' was registered.' );
	}

	WP_CLI::success(
		wp_json_encode(
			array(
				'registered_abilities'       => count( $agentpress_runtime ),
				'category'                   => AbilityCatalog::CATEGORY,
				'native_rest_listed'         => 0,
				'native_rest_blocked_routes' => 2,
				'admin_bridge_tools'         => count( $admin_definitions ),
				'subscriber_bridge_tools'    => count( $subscriber_actual ),
				'logged_out_bridge_tools'    => 0,
				'forbidden_absent'           => 7,
				'unauthorized_mutations'     => 0,
			)
		)
	);
} finally {
	wp_set_current_user( 0 );
	foreach ( $user_ids as $user_id ) {
		wp_delete_user( $user_id );
	}
}
