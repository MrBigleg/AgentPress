<?php
/**
 * Create one AP-005 row before real plugin deactivation.
 *
 * @package AgentPress
 */

use AgentPress\Changes\ChangeSetRepository;

$repository = new ChangeSetRepository();
$id         = $repository->create(
	array(
		'initiator_user_id' => 1,
		'title'             => 'AP-005 deactivation sentinel',
		'request_summary'   => 'Synthetic lifecycle evidence only.',
		'status'            => 'OPEN',
	)
);

update_option( 'agentpress_ap005_deactivation_row', $id, false );
WP_CLI::success( 'Created deactivation sentinel row ' . $id . '.' );
