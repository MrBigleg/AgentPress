<?php
/**
 * Fixed AgentPress Safe Mode policy.
 *
 * @package AgentPress
 */

namespace AgentPress\Policy;

/**
 * Converts contextual risk into the one v0.1 execution mode.
 */
final class SafeModePolicy {
	/**
	 * Risk classifier.
	 *
	 * @var RiskClassifier
	 */
	private $classifier;

	/**
	 * Constructor.
	 *
	 * @param RiskClassifier|null $classifier Optional classifier.
	 */
	public function __construct( $classifier = null ) {
		$this->classifier = $classifier ?? new RiskClassifier();
	}

	/**
	 * Return automatic, approval_required, or blocked.
	 *
	 * @param string               $ability Ability name.
	 * @param array<string, mixed> $context Target context.
	 * @return string
	 */
	public function mode( $ability, $context = array() ) {
		$risk = $this->classifier->classify( $ability, $context );
		if ( 'R3' === $risk ) {
			return 'blocked';
		}
		if ( 'R2' === $risk ) {
			return 'approval_required';
		}

		return 'automatic';
	}
}
