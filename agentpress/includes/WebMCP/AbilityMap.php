<?php
/**
 * Fixed WordPress Ability to WebMCP tool-name map.
 *
 * @package AgentPress
 */

namespace AgentPress\WebMCP;

/**
 * Owns the only Ability allowlist exposed by the AgentPress transport.
 */
final class AbilityMap {
	/**
	 * Fixed collision-free mapping.
	 *
	 * @var array<string, string>
	 */
	private const MAP = array(
		'agentpress/get-context'             => 'agentpress_get_context',
		'agentpress/get-site-structure'      => 'agentpress_get_structure',
		'agentpress/list-content'            => 'agentpress_list_content',
		'agentpress/get-content'             => 'agentpress_get_content',
		'agentpress/create-draft'            => 'agentpress_create_draft',
		'agentpress/update-content'          => 'agentpress_update_content',
		'agentpress/publish-content'         => 'agentpress_stage_publish',
		'agentpress/list-terms'              => 'agentpress_list_terms',
		'agentpress/create-term'             => 'agentpress_stage_term',
		'agentpress/assign-terms'            => 'agentpress_assign_terms',
		'agentpress/get-navigation'          => 'agentpress_get_navigation',
		'agentpress/stage-navigation-change' => 'agentpress_stage_navigation',
		'agentpress/get-change-set'          => 'agentpress_get_change_set',
		'agentpress/list-change-sets'        => 'agentpress_list_change_sets',
		'agentpress/get-agent-activity'      => 'agentpress_get_activity',
	);

	/**
	 * Return the fixed map.
	 *
	 * @return array<string, string>
	 */
	public static function all() {
		return self::MAP;
	}

	/**
	 * Return whether an Ability is transport-allowlisted.
	 *
	 * @param string $ability_name Ability name.
	 * @return bool
	 */
	public static function contains( $ability_name ) {
		return isset( self::MAP[ $ability_name ] );
	}

	/**
	 * Resolve an exact WebMCP name.
	 *
	 * @param string $ability_name Ability name.
	 * @return string|null
	 */
	public static function tool_name( $ability_name ) {
		return self::MAP[ $ability_name ] ?? null;
	}

	/**
	 * Prevent direct construction.
	 */
	private function __construct() {}
}
