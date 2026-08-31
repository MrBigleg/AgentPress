<?php
/**
 * AP-004 WordPress runtime acceptance harness.
 *
 * Run with:
 * wp-env run cli wp eval-file wp-content/plugins/agentpress/tests/integration/ap004-rest-transport.php
 *
 * @package AgentPress
 */

use AgentPress\Rest\WebMCPRoutes;
use AgentPress\WebMCP\AbilityMap;

if ( ! defined( 'ABSPATH' ) ) {
	throw new RuntimeException( 'WordPress must be loaded.' );
}

/**
 * Assert a runtime condition.
 *
 * @param bool   $condition Condition.
 * @param string $message   Failure message.
 * @return void
 */
function agentpress_ap004_assert( $condition, $message ) {
	if ( ! $condition ) {
		throw new RuntimeException( $message );
	}
}

/**
 * Return the current site's origin.
 *
 * @return string
 */
function agentpress_ap004_origin() {
	$parts = wp_parse_url( home_url( '/' ) );
	$port  = isset( $parts['port'] ) ? ':' . (int) $parts['port'] : '';
	return $parts['scheme'] . '://' . $parts['host'] . $port;
}

/**
 * Dispatch one request and apply the same post-dispatch header filter as serving.
 *
 * @param string      $method         HTTP method.
 * @param string      $route          REST route.
 * @param string|null $nonce          REST nonce.
 * @param string      $body           Raw body.
 * @param string|null $origin         Origin header.
 * @param string|null $sec_fetch_site Sec-Fetch-Site header.
 * @return WP_REST_Response
 */
function agentpress_ap004_request( $method, $route, $nonce, $body = '', $origin = null, $sec_fetch_site = 'same-origin' ) {
	$request = new WP_REST_Request( $method, $route );
	$request->set_header( 'Content-Type', 'application/json' );
	$request->set_body( $body );

	if ( null !== $nonce ) {
		$request->set_header( 'X-WP-Nonce', $nonce );
	}
	if ( null !== $origin ) {
		$request->set_header( 'Origin', $origin );
	}
	if ( null !== $sec_fetch_site ) {
		$request->set_header( 'Sec-Fetch-Site', $sec_fetch_site );
	}

	$response = rest_ensure_response( rest_do_request( $request ) );
	return apply_filters( 'rest_post_dispatch', $response, rest_get_server(), $request );
}

/**
 * Assert a forbidden response and zero security-sensitive side effects.
 *
 * @param WP_REST_Response $response         REST response.
 * @param string           $expected_code    Expected code.
 * @param int              $execution_before Execution count before request.
 * @param int              $resolver_before  Resolver count before request.
 * @param int              $target_before    Target state before request.
 * @return void
 */
function agentpress_ap004_assert_forbidden( $response, $expected_code, $execution_before, $resolver_before, $target_before ) {
	$data = $response->get_data();
	agentpress_ap004_assert( isset( $data['ok'] ) && false === $data['ok'], 'Error response is not the common envelope.' );
	agentpress_ap004_assert( isset( $data['error']['code'] ) && $expected_code === $data['error']['code'], 'Unexpected error code: ' . wp_json_encode( $data ) );
	agentpress_ap004_assert( $execution_before === $GLOBALS['agentpress_ap004_execution_count'], $expected_code . ' reached Ability execution.' );
	agentpress_ap004_assert( $resolver_before === $GLOBALS['agentpress_ap004_resolver_count'], $expected_code . ' reached Ability resolution.' );
	agentpress_ap004_assert( $target_before === $GLOBALS['agentpress_ap004_target_state'], $expected_code . ' mutated target state.' );
	agentpress_ap004_assert( 'private, no-store' === $response->get_headers()['Cache-Control'], $expected_code . ' response is cacheable.' );
	agentpress_ap004_assert( 'Cookie' === $response->get_headers()['Vary'], $expected_code . ' response does not vary by cookie.' );
}

$GLOBALS['agentpress_ap004_execution_count'] = 0;
$GLOBALS['agentpress_ap004_resolver_count']  = 0;
$GLOBALS['agentpress_ap004_target_state']    = 0;

add_action(
	'agentpress_webmcp_before_ability_resolve',
	static function () {
		++$GLOBALS['agentpress_ap004_resolver_count'];
	}
);

$category_registry = WP_Ability_Categories_Registry::get_instance();
$ability_registry  = WP_Abilities_Registry::get_instance();

foreach ( array_keys( AbilityMap::all() ) as $registered_agentpress_ability ) {
	if ( wp_has_ability( $registered_agentpress_ability ) ) {
		wp_unregister_ability( $registered_agentpress_ability );
	}
}

if ( ! $category_registry->is_registered( 'agentpress-test' ) ) {
	$category_registry->register(
		'agentpress-test',
		array(
			'label'       => 'AgentPress test',
			'description' => 'Synthetic AP-004 transport fixture.',
		)
	);
}

$ability_args = array(
	'label'               => 'Synthetic context',
	'description'         => 'Returns one synthetic context response.',
	'category'            => 'agentpress-test',
	'input_schema'        => array(
		'type'                 => 'object',
		'properties'           => array(),
		'additionalProperties' => false,
	),
	'output_schema'       => array(
		'type'                 => 'object',
		'properties'           => array(
			'ok'         => array( 'type' => 'boolean' ),
			'request_id' => array( 'type' => 'string' ),
			'data'       => array( 'type' => 'object' ),
		),
		'required'             => array( 'ok', 'request_id', 'data' ),
		'additionalProperties' => false,
	),
	'execute_callback'    => static function () {
		++$GLOBALS['agentpress_ap004_execution_count'];
		++$GLOBALS['agentpress_ap004_target_state'];
		return array(
			'ok'         => true,
			'request_id' => '00000000-0000-4000-8000-000000000004',
			'data'       => array(),
		);
	},
	'permission_callback' => static function () {
		return current_user_can( 'read' );
	},
	'meta'                => array(
		'annotations'  => array(
			'readOnlyHint'         => true,
			'untrustedContentHint' => false,
		),
		'show_in_rest' => false,
	),
);

$ability_registry->register( 'agentpress/get-context', $ability_args );
$ability_registry->register(
	'third-party/danger',
	array_merge(
		$ability_args,
		array(
			'label'            => 'Synthetic forbidden third-party Ability',
			'execute_callback' => static function () {
				++$GLOBALS['agentpress_ap004_execution_count'];
				++$GLOBALS['agentpress_ap004_target_state'];
				return array(
					'ok'         => true,
					'request_id' => '00000000-0000-4000-8000-000000000099',
					'data'       => array(),
				);
			},
		)
	)
);

$administrator = get_users(
	array(
		'role'   => 'administrator',
		'number' => 1,
	)
);
agentpress_ap004_assert( ! empty( $administrator ), 'No Administrator fixture exists.' );
wp_set_current_user( $administrator[0]->ID );

$server = rest_get_server();
$routes = $server->get_routes();
agentpress_ap004_assert( isset( $routes['/agentpress/v1/webmcp/tools'] ), 'Tools route is not registered.' );
agentpress_ap004_assert( isset( $routes['/agentpress/v1/webmcp/execute'] ), 'Execute route is not registered.' );

$origin = agentpress_ap004_origin();
$nonce  = wp_create_nonce( 'wp_rest' );

$discovery = agentpress_ap004_request( 'GET', '/agentpress/v1/webmcp/tools', $nonce, '', $origin );
agentpress_ap004_assert( 200 === $discovery->get_status(), 'Valid discovery failed.' );
agentpress_ap004_assert( 1 === count( $discovery->get_data()['tools'] ), 'Discovery did not expose exactly one allowlisted synthetic tool.' );
agentpress_ap004_assert( 'agentpress_get_context' === $discovery->get_data()['tools'][0]['name'], 'Discovery returned the wrong fixed tool name.' );
agentpress_ap004_assert( 'private, no-store' === $discovery->get_headers()['Cache-Control'], 'Discovery is cacheable.' );
agentpress_ap004_assert( 'Cookie' === $discovery->get_headers()['Vary'], 'Discovery does not vary by cookie.' );

$GLOBALS['agentpress_ap004_resolver_count'] = 0;
$valid = agentpress_ap004_request(
	'POST',
	'/agentpress/v1/webmcp/execute',
	$nonce,
	'{"input":{},"ability":"agentpress/get-context"}',
	$origin
);
agentpress_ap004_assert( 200 === $valid->get_status(), 'Valid execution failed: ' . wp_json_encode( $valid->get_data() ) );
agentpress_ap004_assert( true === $valid->get_data()['ok'], 'Valid execution returned the wrong result.' );
agentpress_ap004_assert( 1 === $GLOBALS['agentpress_ap004_execution_count'], 'Valid execution count is not one.' );
agentpress_ap004_assert( 1 === $GLOBALS['agentpress_ap004_resolver_count'], 'Valid resolver count is not one.' );
agentpress_ap004_assert( 1 === $GLOBALS['agentpress_ap004_target_state'], 'Valid target mutation count is not one.' );

$forbidden_cases = array(
	array( 'missing nonce', 'AP_NONCE_INVALID', get_current_user_id(), null, '{"ability":"agentpress/get-context","input":{}}', $origin, 'same-origin' ),
	array( 'wrong nonce', 'AP_NONCE_INVALID', get_current_user_id(), 'wrong', '{"ability":"agentpress/get-context","input":{}}', $origin, 'same-origin' ),
	array( 'logged out', 'AP_NOT_AUTHENTICATED', 0, $nonce, '{"ability":"agentpress/get-context","input":{}}', $origin, 'same-origin' ),
	array( 'third party', 'AP_PERMISSION_DENIED', $administrator[0]->ID, $nonce, '{"ability":"third-party/danger","input":{}}', $origin, 'same-origin' ),
	array( 'unknown', 'AP_PERMISSION_DENIED', $administrator[0]->ID, $nonce, '{"ability":"agentpress/not-real","input":{}}', $origin, 'same-origin' ),
	array( 'foreign origin', 'AP_POLICY_BLOCKED', $administrator[0]->ID, $nonce, '{"ability":"agentpress/get-context","input":{}}', 'https://attacker.example', 'cross-site' ),
	array( 'cross-site metadata', 'AP_POLICY_BLOCKED', $administrator[0]->ID, $nonce, '{"ability":"agentpress/get-context","input":{}}', null, 'cross-site' ),
	array( 'malformed JSON', 'AP_SCHEMA_INVALID', $administrator[0]->ID, $nonce, '{broken', $origin, 'same-origin' ),
	array( 'extra top-level field', 'AP_SCHEMA_INVALID', $administrator[0]->ID, $nonce, '{"ability":"agentpress/get-context","input":{},"extra":true}', $origin, 'same-origin' ),
	array( 'oversized default body', 'AP_SCHEMA_INVALID', $administrator[0]->ID, $nonce, wp_json_encode( array( 'ability' => 'agentpress/get-context', 'input' => array( 'padding' => str_repeat( 'x', 110000 ) ) ) ), $origin, 'same-origin' ),
	array( 'oversized absolute body', 'AP_SCHEMA_INVALID', $administrator[0]->ID, $nonce, wp_json_encode( array( 'ability' => 'agentpress/create-draft', 'input' => array( 'padding' => str_repeat( 'x', 307201 ) ) ) ), $origin, 'same-origin' ),
);

foreach ( $forbidden_cases as $case ) {
	list( $label, $code, $user_id, $case_nonce, $body, $case_origin, $fetch_site ) = $case;
	wp_set_current_user( $user_id );
	$execution_before = $GLOBALS['agentpress_ap004_execution_count'];
	$resolver_before  = $GLOBALS['agentpress_ap004_resolver_count'];
	$target_before    = $GLOBALS['agentpress_ap004_target_state'];
	$response         = agentpress_ap004_request(
		'POST',
		'/agentpress/v1/webmcp/execute',
		$case_nonce,
		$body,
		$case_origin,
		$fetch_site
	);
	agentpress_ap004_assert_forbidden( $response, $code, $execution_before, $resolver_before, $target_before );
	WP_CLI::log( 'PASS forbidden control: ' . $label . ' -> ' . $code );
}

$rate_filter = static function ( $limit, $bucket ) {
	return 'tools' === $bucket ? 1 : $limit;
};
add_filter( 'agentpress_webmcp_rate_limit', $rate_filter, 10, 2 );
wp_set_current_user( $administrator[0]->ID );
$execution_before = $GLOBALS['agentpress_ap004_execution_count'];
$resolver_before  = $GLOBALS['agentpress_ap004_resolver_count'];
$target_before    = $GLOBALS['agentpress_ap004_target_state'];
$rate_response    = agentpress_ap004_request( 'GET', '/agentpress/v1/webmcp/tools', $nonce, '', $origin );
agentpress_ap004_assert_forbidden( $rate_response, 'AP_RATE_LIMITED', $execution_before, $resolver_before, $target_before );
agentpress_ap004_assert( '60' === $rate_response->get_headers()['Retry-After'], 'Rate-limit response omitted Retry-After.' );
remove_filter( 'agentpress_webmcp_rate_limit', $rate_filter, 10 );
WP_CLI::log( 'PASS forbidden control: per-user discovery rate -> AP_RATE_LIMITED' );

$ability_rate_filter = static function ( $limit, $bucket ) {
	return 'ability_agentpress/get-context' === $bucket ? 1 : $limit;
};
add_filter( 'agentpress_webmcp_rate_limit', $ability_rate_filter, 10, 2 );
$execution_before = $GLOBALS['agentpress_ap004_execution_count'];
$resolver_before  = $GLOBALS['agentpress_ap004_resolver_count'];
$target_before    = $GLOBALS['agentpress_ap004_target_state'];
$rate_response    = agentpress_ap004_request( 'POST', '/agentpress/v1/webmcp/execute', $nonce, '{"ability":"agentpress/get-context","input":{}}', $origin );
agentpress_ap004_assert_forbidden( $rate_response, 'AP_RATE_LIMITED', $execution_before, $resolver_before, $target_before );
agentpress_ap004_assert( '60' === $rate_response->get_headers()['Retry-After'], 'Per-Ability rate response omitted Retry-After.' );
remove_filter( 'agentpress_webmcp_rate_limit', $ability_rate_filter, 10 );
WP_CLI::log( 'PASS forbidden control: per-user Ability rate -> AP_RATE_LIMITED' );

$total_rate_filter = static function ( $limit, $bucket ) {
	return 'execute-total' === $bucket ? 1 : $limit;
};
add_filter( 'agentpress_webmcp_rate_limit', $total_rate_filter, 10, 2 );
$execution_before = $GLOBALS['agentpress_ap004_execution_count'];
$resolver_before  = $GLOBALS['agentpress_ap004_resolver_count'];
$target_before    = $GLOBALS['agentpress_ap004_target_state'];
$rate_response    = agentpress_ap004_request( 'POST', '/agentpress/v1/webmcp/execute', $nonce, '{"ability":"agentpress/get-context","input":{}}', $origin );
agentpress_ap004_assert_forbidden( $rate_response, 'AP_RATE_LIMITED', $execution_before, $resolver_before, $target_before );
agentpress_ap004_assert( '60' === $rate_response->get_headers()['Retry-After'], 'Total execution rate response omitted Retry-After.' );
remove_filter( 'agentpress_webmcp_rate_limit', $total_rate_filter, 10 );
WP_CLI::log( 'PASS forbidden control: per-user total execution rate -> AP_RATE_LIMITED' );

wp_set_current_user( $administrator[0]->ID );
$transport     = new WebMCPRoutes();
$fresh_payload = $transport->get_refreshed_nonce( $origin, 'same-origin' );
agentpress_ap004_assert( is_array( $fresh_payload ) && 1 === wp_verify_nonce( $fresh_payload['nonce'], 'wp_rest' ), 'Signed-in nonce refresh failed.' );

wp_set_current_user( 0 );
$logged_out_refresh = $transport->get_refreshed_nonce( $origin, 'same-origin' );
agentpress_ap004_assert( is_wp_error( $logged_out_refresh ) && 'AP_NOT_AUTHENTICATED' === $logged_out_refresh->get_error_code(), 'Logged-out nonce refresh did not fail closed.' );

wp_set_current_user( $administrator[0]->ID );
$cross_origin_refresh = $transport->get_refreshed_nonce( 'https://attacker.example', 'cross-site' );
agentpress_ap004_assert( is_wp_error( $cross_origin_refresh ) && 'AP_POLICY_BLOCKED' === $cross_origin_refresh->get_error_code(), 'Cross-origin nonce refresh did not fail closed.' );

wp_unregister_ability( 'agentpress/get-context' );
wp_unregister_ability( 'third-party/danger' );
wp_set_current_user( 0 );

WP_CLI::success(
	wp_json_encode(
		array(
			'valid_execution_count' => $GLOBALS['agentpress_ap004_execution_count'],
			'resolver_count'        => $GLOBALS['agentpress_ap004_resolver_count'],
			'target_state'          => $GLOBALS['agentpress_ap004_target_state'],
			'forbidden_controls'    => count( $forbidden_cases ) + 3,
			'private_headers'       => true,
			'nonce_refresh'         => true,
		)
	)
);
