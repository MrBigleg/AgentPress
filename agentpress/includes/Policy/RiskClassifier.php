<?php
/**
 * AgentPress v0.1 risk classification.
 *
 * @package AgentPress
 */

namespace AgentPress\Policy;

use AgentPress\WebMCP\AbilityMap;

/**
 * Classifies only fixed v0.1 operations and denies unknown work as R3.
 */
final class RiskClassifier {
	/**
	 * Classify one operation in its current target context.
	 *
	 * @param string               $ability Ability name.
	 * @param array<string, mixed> $context Target context.
	 * @return string
	 */
	public function classify( $ability, $context = array() ) {
		if ( ! AbilityMap::contains( $ability ) ) {
			return 'R3';
		}

		if ( in_array( $ability, array( 'agentpress/update-content', 'agentpress/assign-terms' ), true ) ) {
			return ! empty( $context['agentpress_created_draft'] ) ? 'R1' : 'R2';
		}

		if ( 'agentpress/create-draft' === $ability ) {
			return 'R1';
		}

		if ( in_array( $ability, array( 'agentpress/publish-content', 'agentpress/create-term', 'agentpress/stage-navigation-change' ), true ) ) {
			return 'R2';
		}

		return 'R0';
	}
}
