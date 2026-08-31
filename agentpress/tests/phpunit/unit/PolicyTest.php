<?php
/**
 * Safe Mode, discovery, capability-envelope, and execution-policy tests.
 *
 * @package AgentPress
 */

use AgentPress\Policy\CapabilityEnvelope;
use AgentPress\Policy\CapabilityResolver;
use AgentPress\Policy\DiscoveryPolicy;
use AgentPress\Policy\ExecutionPolicy;
use AgentPress\Policy\RiskClassifier;
use AgentPress\Policy\SafeModePolicy;
use PHPUnit\Framework\TestCase;

final class PolicyTest extends TestCase {
	/**
	 * @dataProvider fixedRiskCases
	 */
	public function test_fixed_risk_and_safe_mode( $ability, $risk, $mode ) {
		$classifier = new RiskClassifier();
		$policy     = new SafeModePolicy( $classifier );

		$this->assertSame( $risk, $classifier->classify( $ability ) );
		$this->assertSame( $mode, $policy->mode( $ability ) );
	}

	public function fixedRiskCases() {
		return array(
			'context read'     => array( 'agentpress/get-context', 'R0', 'automatic' ),
			'create draft'     => array( 'agentpress/create-draft', 'R1', 'automatic' ),
			'publish'          => array( 'agentpress/publish-content', 'R2', 'approval_required' ),
			'create term'      => array( 'agentpress/create-term', 'R2', 'approval_required' ),
			'navigation stage' => array( 'agentpress/stage-navigation-change', 'R2', 'approval_required' ),
			'unknown R3'       => array( 'agentpress/install-plugin', 'R3', 'blocked' ),
		);
	}

	public function test_agent_created_context_changes_update_and_assignment_risk() {
		$classifier = new RiskClassifier();
		$policy     = new SafeModePolicy( $classifier );
		$context    = array( 'agentpress_created_draft' => true );

		foreach ( array( 'agentpress/update-content', 'agentpress/assign-terms' ) as $ability ) {
			$this->assertSame( 'R2', $classifier->classify( $ability ) );
			$this->assertSame( 'approval_required', $policy->mode( $ability ) );
			$this->assertSame( 'R1', $classifier->classify( $ability, $context ) );
			$this->assertSame( 'automatic', $policy->mode( $ability, $context ) );
		}
	}

	public function test_discovery_and_envelope_follow_live_capability_changes() {
		$grants = array( 'read' => true, 'edit_posts' => false );
		$resolver = $this->resolver( $grants );
		$discovery = new DiscoveryPolicy( $resolver );
		$envelope  = new CapabilityEnvelope( $resolver );

		$this->assertFalse( $discovery->can_discover( 'agentpress/create-draft' ) );
		$this->assertSame( 'unavailable', $envelope->get()['capabilities']['create_post_draft']['state'] );

		$grants['edit_posts'] = true;
		$this->assertTrue( $discovery->can_discover( 'agentpress/create-draft' ) );
		$this->assertSame( 'automatic', $envelope->get()['capabilities']['create_post_draft']['state'] );
		$this->assertFalse( $discovery->can_discover( 'agentpress/install-plugin' ) );
		$this->assertSame( CapabilityEnvelope::BLOCKED_AREAS, $envelope->get()['blocked_areas'] );
		$this->assertCount( 16, $envelope->get()['capabilities'] );
	}

	public function test_execution_rechecks_object_and_policy_without_discovery() {
		$grants = array(
			'read'          => true,
			'edit_posts'    => true,
			'publish_posts' => true,
			'edit_post'     => true,
		);
		$posts = array(
			7 => (object) array( 'ID' => 7, 'post_type' => 'post', 'post_status' => 'draft' ),
			8 => (object) array( 'ID' => 8, 'post_type' => 'post', 'post_status' => 'draft' ),
		);
		$execution = new ExecutionPolicy(
			$this->resolver( $grants ),
			null,
			null,
			static function ( $id ) use ( $posts ) {
				return isset( $posts[ $id ] ) ? $posts[ $id ] : null;
			},
			static function ( $id ) {
				return 7 === $id;
			}
		);

		$this->assertSame( array( 'risk' => 'R1', 'mode' => 'automatic' ), $execution->evaluate( 'agentpress/update-content', array( 'content_id' => 7 ) ) );
		$this->assertSame( array( 'risk' => 'R2', 'mode' => 'approval_required' ), $execution->evaluate( 'agentpress/update-content', array( 'content_id' => 8 ) ) );
		$this->assertSame( 'AP_POLICY_BLOCKED', $execution->evaluate( 'agentpress/install-plugin' )->get_error_code() );

		$grants['read'] = false;
		$this->assertSame( 'AP_NOT_AUTHENTICATED', $execution->evaluate( 'agentpress/get-context' )->get_error_code() );
	}

	/**
	 * Build a resolver backed by one mutable primitive-capability map.
	 *
	 * @param array<string, bool> $grants Capability grants.
	 * @return CapabilityResolver
	 */
	private function resolver( &$grants ) {
		$post_type = static function ( $name ) {
			$suffix = 'page' === $name ? 'pages' : 'posts';
			return (object) array(
				'cap' => (object) array(
					'create_posts'         => 'edit_' . $suffix,
					'edit_posts'           => 'edit_' . $suffix,
					'edit_others_posts'    => 'edit_others_' . $suffix,
					'edit_published_posts' => 'edit_published_' . $suffix,
					'publish_posts'        => 'publish_' . $suffix,
				),
			);
		};
		$taxonomy = static function () {
			return (object) array(
				'cap' => (object) array(
					'manage_terms' => 'manage_categories',
					'assign_terms' => 'edit_posts',
				),
			);
		};

		return new CapabilityResolver(
			static function ( $capability ) use ( &$grants ) {
				return ! empty( $grants[ $capability ] );
			},
			$post_type,
			$taxonomy
		);
	}
}
