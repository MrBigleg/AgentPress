<?php
/**
 * Coarse current-user discovery filtering.
 *
 * @package AgentPress
 */

namespace AgentPress\Policy;

use AgentPress\WebMCP\AbilityMap;

/**
 * Answers whether the current user could potentially use a fixed operation.
 */
final class DiscoveryPolicy {
	/**
	 * Live capability resolver.
	 *
	 * @var CapabilityResolver
	 */
	private $capabilities;

	/**
	 * Constructor.
	 *
	 * @param CapabilityResolver|null $capabilities Optional resolver.
	 */
	public function __construct( $capabilities = null ) {
		$this->capabilities = $capabilities ?? new CapabilityResolver();
	}

	/**
	 * Return the current coarse fixed-operation list.
	 *
	 * @return array<int, string>
	 */
	public function discoverable() {
		return array_values(
			array_filter(
				array_keys( AbilityMap::all() ),
				array( $this, 'can_discover' )
			)
		);
	}

	/**
	 * Return whether one fixed operation may be advertised.
	 *
	 * @param string $ability Ability name.
	 * @return bool
	 */
	public function can_discover( $ability ) {
		if ( ! AbilityMap::contains( $ability ) || ! $this->capabilities->can_read() ) {
			return false;
		}

		switch ( $ability ) {
			case 'agentpress/create-draft':
				return $this->capabilities->can_create( 'post' ) || $this->capabilities->can_create( 'page' );
			case 'agentpress/update-content':
				return $this->capabilities->can_edit_any();
			case 'agentpress/publish-content':
				return $this->capabilities->can_publish_any();
			case 'agentpress/create-term':
				return $this->capabilities->can_manage_any_terms();
			case 'agentpress/assign-terms':
				return $this->capabilities->can_edit_type( 'post' ) && $this->capabilities->can_assign_any_terms();
			case 'agentpress/get-navigation':
			case 'agentpress/stage-navigation-change':
				return $this->capabilities->can_manage_navigation();
			default:
				return true;
		}
	}
}
