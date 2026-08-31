<?php
/**
 * Safe effective AgentPress capability envelope.
 *
 * @package AgentPress
 */

namespace AgentPress\Policy;

/**
 * Reports operation states without exposing raw WordPress capabilities.
 */
final class CapabilityEnvelope {
	/** Fixed v0.1 blocked areas. */
	public const BLOCKED_AREAS = array( 'users', 'plugins', 'themes', 'code', 'credentials', 'settings' );

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
	 * Return the closed 16-operation envelope and blocked areas.
	 *
	 * @return array<string, mixed>
	 */
	public function get() {
		$can_read         = $this->capabilities->can_read();
		$can_edit_any     = $this->capabilities->can_edit_any();
		$can_edit_others  = $this->capabilities->can_edit_others_any();
		$can_edit_publish = $this->capabilities->can_edit_published_any();
		$can_publish      = $this->capabilities->can_publish_any();
		$can_assign_terms = $this->capabilities->can_edit_type( 'post' ) && $this->capabilities->can_assign_any_terms();
		$can_navigation   = $this->capabilities->can_manage_navigation();

		return array(
			'capabilities'  => array(
				'read_site'                 => $this->state( $can_read, 'automatic', 'Read access is required.' ),
				'read_content'              => $this->state( $can_read, 'automatic', 'Read access is required.' ),
				'create_post_draft'         => $this->state( $can_read && $this->capabilities->can_create( 'post' ), 'automatic', 'Post creation is unavailable.' ),
				'create_page_draft'         => $this->state( $can_read && $this->capabilities->can_create( 'page' ), 'automatic', 'Page creation is unavailable.' ),
				'edit_own_agentpress_draft' => $this->state( $can_read && $can_edit_any, 'automatic', 'Draft editing is unavailable.' ),
				'edit_other_draft'          => $this->state( $can_read && $can_edit_others, 'approval_required', 'Editing other drafts is unavailable.' ),
				'edit_published_content'    => $this->state( $can_read && $can_edit_publish, 'approval_required', 'Published-content editing is unavailable.' ),
				'publish_own_content'       => $this->state( $can_read && $can_publish, 'approval_required', 'Publishing is unavailable.' ),
				'publish_others_content'    => $this->state( $can_read && $can_publish && $can_edit_others, 'approval_required', 'Publishing others content is unavailable.' ),
				'list_terms'                => $this->state( $can_read, 'automatic', 'Term reading is unavailable.' ),
				'create_terms'              => $this->state( $can_read && $this->capabilities->can_manage_any_terms(), 'approval_required', 'Term creation is unavailable.' ),
				'assign_terms'              => $this->state( $can_read && $can_assign_terms, 'automatic', 'Term assignment is unavailable.' ),
				'read_navigation'           => $this->state( $can_read && $can_navigation, 'automatic', 'Navigation access is unavailable.' ),
				'modify_navigation'         => $this->state( $can_read && $can_navigation, 'approval_required', 'Navigation changes are unavailable.' ),
				'read_change_sets'          => $this->state( $can_read, 'automatic', 'Change Set access is unavailable.' ),
				'read_activity'             => $this->state( $can_read, 'automatic', 'Activity access is unavailable.' ),
			),
			'blocked_areas' => self::BLOCKED_AREAS,
		);
	}

	/**
	 * Build one stable capability state.
	 *
	 * @param bool   $available Available.
	 * @param string $state     Available state.
	 * @param string $reason    Unavailable reason.
	 * @return array<string, string>
	 */
	private function state( $available, $state, $reason ) {
		return array(
			'state'  => $available ? $state : 'unavailable',
			'reason' => $available ? '' : $reason,
		);
	}
}
