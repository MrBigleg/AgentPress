<?php
/**
 * Canonical Change Set hashing.
 *
 * @package AgentPress
 */

namespace AgentPress\Changes;

/**
 * Produces stable hashes without persisting raw idempotency keys.
 */
final class StateHasher {
	/**
	 * Hash one semantic state after recursively sorting object keys.
	 *
	 * @param mixed $value Semantic value.
	 * @return string
	 */
	public function state_hash( $value ) {
		return hash( 'sha256', $this->canonical_json( $value ) );
	}

	/**
	 * Hash one immutable proposal contract.
	 *
	 * @param string $ability          Ability name.
	 * @param string $operation        Operation name.
	 * @param mixed  $after            Proposed after state.
	 * @param string $target_state_hash Current target hash.
	 * @return string
	 */
	public function proposal_hash( $ability, $operation, $after, $target_state_hash ) {
		return hash( 'sha256', $ability . $operation . $this->canonical_json( $after ) . $target_state_hash );
	}

	/**
	 * Hash the unique actor/Ability/raw-key scope.
	 *
	 * @param int    $user_id         Actor user ID.
	 * @param string $ability         Ability name.
	 * @param string $idempotency_key Raw validated key.
	 * @return string
	 */
	public function idempotency_scope( $user_id, $ability, $idempotency_key ) {
		return hash( 'sha256', (string) $user_id . $ability . $idempotency_key );
	}

	/**
	 * Hash the semantic request payload used to detect changed-key reuse.
	 *
	 * @param array<string, mixed> $payload Reviewed payload without raw key/session.
	 * @return string
	 */
	public function idempotency_hash( $payload ) {
		return $this->state_hash( $payload );
	}

	/**
	 * Encode recursively canonical JSON.
	 *
	 * @param mixed $value Semantic value.
	 * @return string
	 * @throws \InvalidArgumentException When encoding fails.
	 */
	public function canonical_json( $value ) {
		$canonical = $this->canonicalize( $value );
		$json      = wp_json_encode( $canonical, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
		if ( ! is_string( $json ) ) {
			throw new \InvalidArgumentException( 'AgentPress could not canonicalize state.' );
		}
		return $json;
	}

	/**
	 * Recursively sort associative keys while retaining list order.
	 *
	 * @param mixed $value Candidate value.
	 * @return mixed
	 */
	private function canonicalize( $value ) {
		if ( ! is_array( $value ) ) {
			return $value;
		}

		$is_list = empty( $value ) || array_keys( $value ) === range( 0, count( $value ) - 1 );
		if ( ! $is_list ) {
			ksort( $value, SORT_STRING );
		}
		foreach ( $value as $key => $item ) {
			$value[ $key ] = $this->canonicalize( $item );
		}
		return $value;
	}
}
