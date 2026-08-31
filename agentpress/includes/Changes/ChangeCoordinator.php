<?php
/**
 * Change Set mutation and proposal coordination.
 *
 * @package AgentPress
 */

namespace AgentPress\Changes;

use AgentPress\Errors\ErrorFactory;

/**
 * Enforces durable intent, idempotency, staging, and parent-state reduction.
 */
final class ChangeCoordinator {
	/**
	 * Change Set repository.
	 *
	 * @var ChangeSetRepository|object
	 */
	private $sets;

	/**
	 * Change repository.
	 *
	 * @var ChangeRepository|object
	 */
	private $changes;

	/**
	 * Canonical state hasher.
	 *
	 * @var StateHasher
	 */
	private $hasher;

	/**
	 * Parent-state reducer.
	 *
	 * @var ChangeSetStateReducer
	 */
	private $reducer;

	/**
	 * Unix timestamp clock.
	 *
	 * @var callable
	 */
	private $clock;

	/**
	 * Constructor.
	 *
	 * @param ChangeSetRepository|object|null $sets    Optional Change Set repository.
	 * @param ChangeRepository|object|null    $changes Optional change repository.
	 * @param StateHasher|null                $hasher  Optional canonical hasher.
	 * @param ChangeSetStateReducer|null      $reducer Optional parent reducer.
	 * @param callable|null                   $clock   Optional Unix timestamp clock.
	 */
	public function __construct( $sets = null, $changes = null, $hasher = null, $reducer = null, $clock = null ) {
		$this->sets    = $sets ?? new ChangeSetRepository();
		$this->changes = $changes ?? new ChangeRepository();
		$this->hasher  = $hasher ?? new StateHasher();
		$this->reducer = $reducer ?? new ChangeSetStateReducer();
		$this->clock   = $clock ?? 'time';
	}

	/**
	 * Record and claim one R1 intent before invoking a trusted narrow mutator.
	 *
	 * @param array<string, mixed> $command Reviewed coordination command.
	 * @param callable             $mutator Trusted service callback.
	 * @return array<string, mixed>|\WP_Error
	 */
	public function apply( $command, $mutator ) {
		if ( ! isset( $command['request_id'] ) || ! is_string( $command['request_id'] ) ) {
			$command['request_id'] = strtolower( wp_generate_uuid4() );
		}
		$validated = $this->validate_command( $command );
		if ( is_wp_error( $validated ) ) {
			return $validated;
		}

		$identity = $this->identity( $command, 'R1' );
		$existing = $this->changes->find_by_idempotency_scope( $identity['scope'] );
		if ( is_array( $existing ) ) {
			return $this->replay_or_conflict( $existing, $identity['hash'], $command );
		}

		$set = $this->resolve_set( $command );
		if ( is_wp_error( $set ) ) {
			return $set;
		}
		$set_id = (int) $set['id'];

		$change_id = $this->create_change( $set_id, $command, 'R1', 'RECORDED', $identity );
		if ( is_wp_error( $change_id ) ) {
			$this->remove_empty_default_set( $set );
			$existing = $this->changes->find_by_idempotency_scope( $identity['scope'] );
			return is_array( $existing ) ? $this->replay_or_conflict( $existing, $identity['hash'], $command ) : $change_id;
		}

		try {
			$claimed = $this->changes->transition( $change_id, 'RECORDED', array( 'status' => 'APPLYING' ) );
		} catch ( \Throwable $throwable ) {
			$claimed = false;
		}
		if ( ! $claimed ) {
			$this->sync_set( $set_id );
			return $this->error( 'AP_STATE_CONFLICT', $command );
		}

		try {
			$mutation = call_user_func( $mutator );
		} catch ( \Throwable $throwable ) {
			$mutation = ErrorFactory::make( 'AP_INTERNAL_ERROR', array(), $this->request_id( $command ) );
		}

		if ( is_wp_error( $mutation ) ) {
			$error = ErrorFactory::normalize( $mutation, $this->request_id( $command ) );
			$this->fail_change( $change_id, $error->get_error_code() );
			$this->sync_set( $set_id );
			return $error;
		}

		$mutation = is_array( $mutation ) ? $mutation : array();
		$updates  = array(
			'status'     => 'APPLIED',
			'object_id'  => isset( $mutation['object_id'] ) ? max( 0, (int) $mutation['object_id'] ) : $this->object_id( $command ),
			'applied_at' => gmdate( 'Y-m-d H:i:s', call_user_func( $this->clock ) ),
		);
		if ( isset( $mutation['before'] ) && is_array( $mutation['before'] ) ) {
			$updates['before_json'] = $mutation['before'];
		}
		if ( isset( $mutation['after'] ) && is_array( $mutation['after'] ) ) {
			$updates['after_json'] = $mutation['after'];
		}

		try {
			$applied = $this->changes->transition( $change_id, 'APPLYING', $updates );
		} catch ( \Throwable $throwable ) {
			$applied = false;
		}
		if ( ! $applied ) {
			return $this->error( 'AP_INTERNAL_ERROR', $command );
		}

		$this->sync_set( $set_id );
		return $this->result( $set_id, $change_id, 'APPLIED', false, $mutation );
	}

	/**
	 * Store one immutable expiring R2 proposal without target mutation.
	 *
	 * @param array<string, mixed> $command Reviewed coordination command.
	 * @return array<string, mixed>|\WP_Error
	 */
	public function stage( $command ) {
		if ( ! isset( $command['request_id'] ) || ! is_string( $command['request_id'] ) ) {
			$command['request_id'] = strtolower( wp_generate_uuid4() );
		}
		$validated = $this->validate_command( $command );
		if ( is_wp_error( $validated ) ) {
			return $validated;
		}

		$identity = $this->identity( $command, 'R2' );
		$existing = $this->changes->find_by_idempotency_scope( $identity['scope'] );
		if ( is_array( $existing ) ) {
			return $this->replay_or_conflict( $existing, $identity['hash'], $command );
		}

		$set = $this->resolve_set( $command );
		if ( is_wp_error( $set ) ) {
			return $set;
		}
		$set_id = (int) $set['id'];

		$before                        = $command['before'];
		$after                         = $command['after'];
		$target_hash                   = $this->hasher->state_hash( $before );
		$identity['target_state_hash'] = $target_hash;
		$identity['proposal_hash']     = $this->hasher->proposal_hash( $command['ability'], $command['operation'], $after, $target_hash );
		$identity['expires_at']        = gmdate( 'Y-m-d H:i:s', call_user_func( $this->clock ) + 86400 );

		$change_id = $this->create_change( $set_id, $command, 'R2', 'PENDING_APPROVAL', $identity );
		if ( is_wp_error( $change_id ) ) {
			$this->remove_empty_default_set( $set );
			$existing = $this->changes->find_by_idempotency_scope( $identity['scope'] );
			return is_array( $existing ) ? $this->replay_or_conflict( $existing, $identity['hash'], $command ) : $change_id;
		}

		$this->sync_set( $set_id );
		return $this->result( $set_id, $change_id, 'PENDING_APPROVAL', false, array( 'expires_at' => $identity['expires_at'] ) );
	}

	/**
	 * Validate the fixed coordinator command.
	 *
	 * @param array<string, mixed> $command Command.
	 * @return true|\WP_Error
	 */
	private function validate_command( $command ) {
		$required = array( 'actor_user_id', 'ability', 'operation', 'idempotency_key', 'before', 'after' );
		foreach ( $required as $field ) {
			if ( ! array_key_exists( $field, $command ) ) {
				return ErrorFactory::make( 'AP_SCHEMA_INVALID' );
			}
		}
		if (
			(int) $command['actor_user_id'] <= 0 ||
			! is_string( $command['ability'] ) ||
			! is_string( $command['operation'] ) ||
			! is_string( $command['idempotency_key'] ) ||
			1 !== preg_match( '/^[A-Za-z0-9._:-]{8,64}$/', $command['idempotency_key'] ) ||
			! is_array( $command['before'] ) ||
			! is_array( $command['after'] )
		) {
			return ErrorFactory::make( 'AP_SCHEMA_INVALID' );
		}
		return true;
	}

	/**
	 * Build idempotency scope and payload hashes.
	 *
	 * @param array<string, mixed> $command Command.
	 * @param string               $risk    Risk class.
	 * @return array<string, string>
	 */
	private function identity( $command, $risk ) {
		$scope_payload = array(
			'actor_user_id' => (int) $command['actor_user_id'],
			'ability'       => $command['ability'],
			'risk_class'    => $risk,
			'operation'     => $command['operation'],
			'object_type'   => isset( $command['object_type'] ) ? (string) $command['object_type'] : '',
			'object_id'     => $this->object_id( $command ),
			'before'        => $command['before'],
			'after'         => $command['after'],
		);
		return array(
			'scope' => $this->hasher->idempotency_scope( (int) $command['actor_user_id'], $command['ability'], $command['idempotency_key'] ),
			'hash'  => $this->hasher->idempotency_hash( $scope_payload ),
		);
	}

	/**
	 * Resolve or create the target Change Set.
	 *
	 * @param array<string, mixed> $command Command.
	 * @return array<string, mixed>|\WP_Error
	 */
	private function resolve_set( $command ) {
		if ( ! empty( $command['change_set_id'] ) ) {
			$set = $this->sets->find( (int) $command['change_set_id'] );
			if ( ! is_array( $set ) || (int) $set['initiator_user_id'] !== (int) $command['actor_user_id'] || in_array( $set['status'], array( 'COMPLETED', 'REJECTED', 'FAILED' ), true ) ) {
				return ErrorFactory::make( 'AP_CHANGE_NOT_FOUND' );
			}
			$set['_created_default'] = false;
			return $set;
		}

		try {
			$id = $this->sets->create(
				array(
					'initiator_user_id'   => (int) $command['actor_user_id'],
					'title'               => isset( $command['change_set_title'] ) ? substr( (string) $command['change_set_title'], 0, 200 ) : 'AgentPress changes',
					'request_summary'     => isset( $command['request_summary'] ) ? substr( (string) $command['request_summary'], 0, 1000 ) : '',
					'source'              => 'webmcp',
					'source_session_hash' => empty( $command['source_session'] ) ? '' : hash( 'sha256', (string) $command['source_session'] ),
					'status'              => 'OPEN',
				)
			);
		} catch ( \Throwable $throwable ) {
			return ErrorFactory::make( 'AP_INTERNAL_ERROR' );
		}
		$set = $this->sets->find( $id );
		if ( ! is_array( $set ) ) {
			return ErrorFactory::make( 'AP_INTERNAL_ERROR' );
		}
		$set['_created_default'] = true;
		return $set;
	}

	/**
	 * Persist one child change.
	 *
	 * @param int                   $set_id   Set ID.
	 * @param array<string, mixed>  $command  Command.
	 * @param string                $risk     Risk class.
	 * @param string                $status   Initial status.
	 * @param array<string, string> $identity Hashes and proposal metadata.
	 * @return int|\WP_Error
	 */
	private function create_change( $set_id, $command, $risk, $status, $identity ) {
		$record = array(
			'change_set_id'     => $set_id,
			'actor_user_id'     => (int) $command['actor_user_id'],
			'ability'           => $command['ability'],
			'risk_class'        => $risk,
			'operation'         => $command['operation'],
			'object_type'       => isset( $command['object_type'] ) ? (string) $command['object_type'] : '',
			'object_id'         => $this->object_id( $command ),
			'before_json'       => $command['before'],
			'after_json'        => $command['after'],
			'idempotency_hash'  => $identity['hash'],
			'idempotency_scope' => $identity['scope'],
			'status'            => $status,
		);
		foreach ( array( 'target_state_hash', 'proposal_hash', 'expires_at' ) as $field ) {
			if ( isset( $identity[ $field ] ) ) {
				$record[ $field ] = $identity[ $field ];
			}
		}
		try {
			return $this->changes->create( $record );
		} catch ( \Throwable $throwable ) {
			return ErrorFactory::make( 'AP_INTERNAL_ERROR', array(), $this->request_id( $command ) );
		}
	}

	/**
	 * Replay an identical request or reject changed-key reuse.
	 *
	 * @param array<string, mixed> $existing Existing row.
	 * @param string               $hash     Payload hash.
	 * @param array<string, mixed> $command  Command.
	 * @return array<string, mixed>|\WP_Error
	 */
	private function replay_or_conflict( $existing, $hash, $command ) {
		if ( $existing['idempotency_hash'] !== $hash ) {
			return $this->error( 'AP_STATE_CONFLICT', $command );
		}
		return $this->result(
			(int) $existing['change_set_id'],
			(int) $existing['id'],
			$existing['status'],
			true,
			array(
				'expires_at' => (string) $existing['expires_at'],
				'object_id'  => (int) $existing['object_id'],
			)
		);
	}

	/**
	 * Mark a claimed change failed when possible.
	 *
	 * @param int    $change_id  Change ID.
	 * @param string $error_code Error code.
	 * @return void
	 */
	private function fail_change( $change_id, $error_code ) {
		try {
			$this->changes->transition(
				$change_id,
				'APPLYING',
				array(
					'status'     => 'FAILED',
					'error_code' => $error_code,
				)
			);
		} catch ( \Throwable $throwable ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
			// The durable APPLYING row remains visible for recovery.
		}
	}

	/**
	 * Synchronize the parent state from its children.
	 *
	 * @param int $set_id Set ID.
	 * @return void
	 */
	private function sync_set( $set_id ) {
		$state = $this->reducer->reduce( $this->changes->statuses_for_set( $set_id ) );
		$this->sets->update(
			$set_id,
			array(
				'status'       => $state,
				'completed_at' => in_array( $state, array( 'COMPLETED', 'REJECTED', 'FAILED' ), true ) ? gmdate( 'Y-m-d H:i:s', call_user_func( $this->clock ) ) : null,
			)
		);
	}

	/**
	 * Remove a default set left empty by change creation failure.
	 *
	 * @param array<string, mixed> $set Set.
	 * @return void
	 */
	private function remove_empty_default_set( $set ) {
		if ( ! empty( $set['_created_default'] ) ) {
			try {
				$this->sets->delete( (int) $set['id'] );
			} catch ( \Throwable $throwable ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
				// An empty diagnostic set is safer than hiding the original failure.
			}
		}
	}

	/**
	 * Build the stable coordinator result.
	 *
	 * @param int                  $set_id    Set ID.
	 * @param int                  $change_id Change ID.
	 * @param string               $status    Change status.
	 * @param bool                 $replayed  Whether this is a replay.
	 * @param array<string, mixed> $extra     Mutation or proposal data.
	 * @return array<string, mixed>
	 */
	private function result( $set_id, $change_id, $status, $replayed, $extra ) {
		return array_merge(
			array(
				'status'        => $status,
				'change_set_id' => $set_id,
				'change_id'     => $change_id,
				'replayed'      => $replayed,
			),
			$extra
		);
	}

	/**
	 * Create a correlated public error.
	 *
	 * @param string               $code    Error code.
	 * @param array<string, mixed> $command Command.
	 * @return \WP_Error
	 */
	private function error( $code, $command ) {
		return ErrorFactory::make( $code, array(), $this->request_id( $command ) );
	}

	/**
	 * Return the command correlation ID.
	 *
	 * @param array<string, mixed> $command Command.
	 * @return string
	 */
	private function request_id( $command ) {
		return isset( $command['request_id'] ) && is_string( $command['request_id'] ) ? $command['request_id'] : strtolower( wp_generate_uuid4() );
	}

	/**
	 * Normalize the optional object ID.
	 *
	 * @param array<string, mixed> $command Command.
	 * @return int
	 */
	private function object_id( $command ) {
		return isset( $command['object_id'] ) ? max( 0, (int) $command['object_id'] ) : 0;
	}
}
