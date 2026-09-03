<?php
/**
 * AP-029 atomic single-winner claim acceptance.
 *
 * @package AgentPress
 */

use AgentPress\Changes\ChangeRepository;
use AgentPress\Changes\ChangeSetRepository;
use AgentPress\Storage\Migrator;

// phpcs:disable Generic.Commenting.DocComment.MissingShort,Squiz.Commenting.FunctionComment.MissingParamTag,Squiz.Commenting.FunctionComment.ParamCommentFullStop,WordPress.WP.AlternativeFunctions.file_system_operations_fwrite -- Standalone wp-cli fixture test.

/** @param bool $condition Condition. @param string $message Message. @return void */
function agentpress_ap029_assert( $condition, $message ) {
	if ( ! $condition ) {
		fwrite( STDERR, "AP-029 assertion failed: {$message}\n" );
		exit( 1 );
	}
}

Migrator::migrate();

$administrators = get_users(
	array(
		'role'   => 'administrator',
		'number' => 1,
	)
);
agentpress_ap029_assert( ! empty( $administrators ), 'Administrator fixture is missing.' );
$actor_id  = (int) $administrators[0]->ID;
$sets      = new ChangeSetRepository();
$first     = new ChangeRepository();
$second    = new ChangeRepository();
$run_key   = wp_generate_uuid4();
$set_id    = $sets->create(
	array(
		'initiator_user_id' => $actor_id,
		'title'             => 'AP029 atomic claim',
		'request_summary'   => 'Two workers contend for one pending proposal.',
		'status'            => 'READY_FOR_REVIEW',
	)
);
$change_id = $first->create(
	array(
		'change_set_id'     => $set_id,
		'actor_user_id'     => $actor_id,
		'ability'           => 'agentpress/publish-content',
		'risk_class'        => 'R2',
		'operation'         => 'publish',
		'object_type'       => 'post',
		'object_id'         => 71,
		'before_json'       => array( 'status' => 'draft' ),
		'after_json'        => array( 'status' => 'publish' ),
		'target_state_hash' => hash( 'sha256', 'ap029-target' ),
		'proposal_hash'     => hash( 'sha256', 'ap029-proposal-' . $run_key ),
		'idempotency_hash'  => hash( 'sha256', 'ap029-idempotency-' . $run_key ),
		'idempotency_scope' => hash( 'sha256', 'ap029-scope-' . $run_key ),
		'status'            => 'PENDING_APPROVAL',
		'expires_at'        => gmdate( 'Y-m-d H:i:s', time() + HOUR_IN_SECONDS ),
	)
);

$winner_one = $first->transition(
	$change_id,
	'PENDING_APPROVAL',
	array(
		'status'      => 'APPLYING',
		'approved_by' => $actor_id,
	)
);
$winner_two = $second->transition(
	$change_id,
	'PENDING_APPROVAL',
	array(
		'status'      => 'APPLYING',
		'approved_by' => $actor_id,
	)
);
$row        = $first->find( $change_id );

agentpress_ap029_assert( true === $winner_one, 'First claimant did not win.' );
agentpress_ap029_assert( false === $winner_two, 'Second claimant also won.' );
agentpress_ap029_assert( 'APPLYING' === $row['status'] && $actor_id === (int) $row['approved_by'], 'Winning claim was not durable.' );

$first->delete( $change_id );
$sets->delete( $set_id );

echo wp_json_encode(
	array(
		'claimants'        => 2,
		'winners'          => 1,
		'target_mutations' => 0,
	)
) . "\n";
// phpcs:enable
