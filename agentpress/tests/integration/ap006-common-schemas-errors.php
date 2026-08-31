<?php
/**
 * AP-006 common schema, result, and error acceptance harness.
 *
 * @package AgentPress
 */

use AgentPress\Errors\ErrorFactory;
use AgentPress\Results\ResultFactory;
use AgentPress\Schemas\CombinationRules;
use AgentPress\Schemas\SchemaBuilder;
use AgentPress\Schemas\SchemaValidator;

if ( ! defined( 'ABSPATH' ) ) {
	throw new RuntimeException( 'WordPress must be loaded.' );
}

/**
 * Assert one AP-006 condition.
 *
 * @param bool   $condition Condition.
 * @param string $message   Failure.
 * @return void
 */
function agentpress_ap006_assert( $condition, $message ) {
	if ( ! $condition ) {
		throw new RuntimeException( $message );
	}
}

/**
 * Assert one validation failure code.
 *
 * @param mixed  $result Expected WordPress error.
 * @param string $code   Expected code.
 * @param string $label  Fixture label.
 * @return void
 */
function agentpress_ap006_error( $result, $code, $label ) {
	agentpress_ap006_assert( is_wp_error( $result ), $label . ' unexpectedly validated.' );
	agentpress_ap006_assert( $code === $result->get_error_code(), $label . ' returned the wrong error code.' );
}

$validator = new SchemaValidator();
$input_schema = SchemaBuilder::closed_object(
	array(
		'title' => SchemaBuilder::string( 1, 5 ),
		'count' => SchemaBuilder::positive_integer(),
		'type'  => SchemaBuilder::enum( array( 'post', 'page' ) ),
		'ids'   => SchemaBuilder::positive_ids( 3 ),
	),
	array( 'title', 'count', 'type', 'ids' )
);
$valid_input = array(
	'title' => 'hello',
	'count' => 1,
	'type'  => 'post',
	'ids'   => array( 1, 2 ),
);

agentpress_ap006_assert( true === $validator->validate_input( $valid_input, $input_schema ), 'Valid common input failed.' );
agentpress_ap006_assert( true === $validator->validate_input( array(), SchemaBuilder::closed_object() ), 'Valid closed empty object failed.' );

$invalid_inputs = array(
	'unknown field'    => array_merge( $valid_input, array( 'extra' => true ) ),
	'wrong type'       => array_merge( $valid_input, array( 'count' => '1' ) ),
	'out of range'     => array_merge( $valid_input, array( 'count' => 0 ) ),
	'oversized string' => array_merge( $valid_input, array( 'title' => 'longer' ) ),
	'unsupported enum' => array_merge( $valid_input, array( 'type' => 'product' ) ),
	'duplicate IDs'    => array_merge( $valid_input, array( 'ids' => array( 1, 1 ) ) ),
	'too many IDs'     => array_merge( $valid_input, array( 'ids' => array( 1, 2, 3, 4 ) ) ),
	'non-positive ID'  => array_merge( $valid_input, array( 'ids' => array( 0 ) ) ),
);
foreach ( $invalid_inputs as $label => $input ) {
	agentpress_ap006_error( $validator->validate_input( $input, $input_schema ), 'AP_SCHEMA_INVALID', $label );
}

$update_schema = SchemaBuilder::closed_object(
	array(
		'content_id' => SchemaBuilder::positive_integer(),
		'title'      => SchemaBuilder::string( 1, 200 ),
		'content'    => SchemaBuilder::string( 0, 200000 ),
	),
	array( 'content_id' )
);
$update_rule = CombinationRules::at_least_one( array( 'title', 'content' ) );
agentpress_ap006_assert( true === $validator->validate_input( array( 'content_id' => 5, 'title' => 'New' ), $update_schema, array( $update_rule ) ), 'Valid at-least-one combination failed.' );
agentpress_ap006_error( $validator->validate_input( array( 'content_id' => 5 ), $update_schema, array( $update_rule ) ), 'AP_SCHEMA_INVALID', 'missing update field' );

$navigation_item = SchemaBuilder::closed_object(
	array(
		'item_id'     => SchemaBuilder::positive_integer(),
		'object_type' => SchemaBuilder::enum( array( 'post', 'page', 'custom' ) ),
		'object_id'   => SchemaBuilder::positive_integer(),
		'url'         => SchemaBuilder::string( 1, 2048 ),
		'label'       => SchemaBuilder::string( 1, 200 ),
		'position'    => SchemaBuilder::non_negative_integer(),
	)
);
$navigation_schema = SchemaBuilder::closed_object(
	array(
		'operation' => SchemaBuilder::enum( array( 'add', 'remove', 'move' ) ),
		'item'      => $navigation_item,
	),
	array( 'operation', 'item' )
);
$navigation_rule = CombinationRules::navigation_operation();
$valid_navigation = array(
	'operation' => 'add',
	'item'      => array( 'object_type' => 'custom', 'url' => 'https://example.test/', 'label' => 'Example', 'position' => 0 ),
);
agentpress_ap006_assert( true === $validator->validate_input( $valid_navigation, $navigation_schema, array( $navigation_rule ) ), 'Valid navigation combination failed.' );
$invalid_navigation = array(
	'custom missing label' => array( 'operation' => 'add', 'item' => array( 'object_type' => 'custom', 'url' => 'https://example.test/', 'position' => 0 ) ),
	'remove with extra'    => array( 'operation' => 'remove', 'item' => array( 'item_id' => 1, 'position' => 0 ) ),
	'move without position' => array( 'operation' => 'move', 'item' => array( 'item_id' => 1 ) ),
	'post without object'  => array( 'operation' => 'add', 'item' => array( 'object_type' => 'post', 'position' => 0 ) ),
);
foreach ( $invalid_navigation as $label => $input ) {
	agentpress_ap006_error( $validator->validate_input( $input, $navigation_schema, array( $navigation_rule ) ), 'AP_SCHEMA_INVALID', $label );
}

$output_schema = SchemaBuilder::success_envelope(
	SchemaBuilder::closed_object(
		array( 'id' => SchemaBuilder::positive_integer() ),
		array( 'id' )
	)
);
$valid_output = ResultFactory::success( array( 'id' => 7 ), '00000000-0000-4000-8000-000000000012' );
agentpress_ap006_assert( true === $validator->validate_output( $valid_output, $output_schema ), 'Valid success output failed.' );
$invalid_outputs = array(
	'missing data'     => array( 'ok' => true, 'request_id' => '00000000-0000-4000-8000-000000000012' ),
	'invalid request'  => array( 'ok' => true, 'request_id' => 'invalid', 'data' => array( 'id' => 7 ) ),
	'unknown property' => array_merge( $valid_output, array( 'extra' => true ) ),
	'invalid data'     => ResultFactory::success( array( 'id' => 0 ), '00000000-0000-4000-8000-000000000012' ),
);
foreach ( $invalid_outputs as $label => $output ) {
	agentpress_ap006_error( $validator->validate_output( $output, $output_schema ), 'AP_INTERNAL_ERROR', $label );
}

$expected_errors = array(
	'AP_NOT_AUTHENTICATED'      => array( 401, false ),
	'AP_NONCE_INVALID'          => array( 403, true ),
	'AP_PERMISSION_DENIED'      => array( 403, false ),
	'AP_POLICY_BLOCKED'         => array( 403, false ),
	'AP_APPROVAL_REQUIRED'      => array( 409, false ),
	'AP_SCHEMA_INVALID'         => array( 400, false ),
	'AP_CONTENT_NOT_FOUND'      => array( 404, false ),
	'AP_TERM_NOT_FOUND'         => array( 404, false ),
	'AP_CHANGE_NOT_FOUND'       => array( 404, false ),
	'AP_NAVIGATION_NOT_FOUND'   => array( 404, false ),
	'AP_STATE_CONFLICT'         => array( 409, false ),
	'AP_CHANGE_EXPIRED'         => array( 410, false ),
	'AP_RATE_LIMITED'           => array( 429, true ),
	'AP_UNSUPPORTED_POST_TYPE'  => array( 422, false ),
	'AP_UNSUPPORTED_TAXONOMY'   => array( 422, false ),
	'AP_UNSUPPORTED_NAVIGATION' => array( 422, false ),
	'AP_INTERNAL_ERROR'         => array( 500, false ),
);
agentpress_ap006_assert( array_keys( $expected_errors ) === ErrorFactory::codes(), 'Declared error code order/set mismatch.' );
foreach ( $expected_errors as $code => $contract ) {
	$response = ErrorFactory::response( ErrorFactory::make( $code ), '00000000-0000-4000-8000-000000000012' );
	$body     = $response['body'];
	agentpress_ap006_assert( $contract[0] === $response['status'], $code . ' HTTP status mismatch.' );
	agentpress_ap006_assert( array( 'ok', 'request_id', 'error' ) === array_keys( $body ), $code . ' envelope keys mismatch.' );
	agentpress_ap006_assert( array( 'code', 'message', 'retryable', 'details' ) === array_keys( $body['error'] ), $code . ' error keys mismatch.' );
	agentpress_ap006_assert( $code === $body['error']['code'] && $contract[1] === $body['error']['retryable'], $code . ' mapping mismatch.' );
	agentpress_ap006_assert( $body['error']['details'] instanceof stdClass, $code . ' empty details are not an object.' );
}

$unsafe_error = new WP_Error(
	'AP_DATABASE_EXPLODED',
	'SQL failed password=hunter2',
	array(
		'details' => array(
			'authorization' => 'Bearer private',
			'safe_reason'   => str_repeat( 'x', 400 ),
		),
	)
);
$unsafe_response = ErrorFactory::response( $unsafe_error, '00000000-0000-4000-8000-000000000012' );
$safe_error      = $unsafe_response['body']['error'];
agentpress_ap006_assert( 'AP_INTERNAL_ERROR' === $safe_error['code'], 'Unknown code did not normalize to internal.' );
agentpress_ap006_assert( false === strpos( $safe_error['message'], 'SQL' ), 'Unsafe upstream message leaked.' );
agentpress_ap006_assert( ! isset( $safe_error['details']['authorization'] ), 'Secret-like detail leaked.' );
agentpress_ap006_assert( ErrorFactory::MAX_DETAIL_STRING_BYTES === strlen( $safe_error['details']['safe_reason'] ), 'Safe detail string was not bounded.' );
agentpress_ap006_assert( ErrorFactory::MAX_DETAILS_BYTES >= strlen( wp_json_encode( $safe_error['details'] ) ), 'Safe details exceed byte bound.' );

WP_CLI::success(
	wp_json_encode(
		array(
			'invalid_input_classes' => count( $invalid_inputs ) + 1 + count( $invalid_navigation ),
			'invalid_outputs'       => count( $invalid_outputs ),
			'declared_errors'       => count( $expected_errors ),
			'unsafe_detail_bound'   => ErrorFactory::MAX_DETAILS_BYTES,
		)
	)
);
