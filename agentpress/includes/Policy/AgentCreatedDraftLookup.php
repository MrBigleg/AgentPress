<?php
/**
 * Durable AgentPress-created draft authority lookup.
 *
 * @package AgentPress
 */

namespace AgentPress\Policy;

/**
 * Uses applied Change records rather than mutable post metadata.
 */
final class AgentCreatedDraftLookup {
	/**
	 * WordPress database adapter.
	 *
	 * @var \wpdb
	 */
	private $wpdb;

	/**
	 * Constructor.
	 *
	 * @param \wpdb|null $wpdb Optional database adapter.
	 */
	public function __construct( $wpdb = null ) {
		$this->wpdb = $wpdb ?? $GLOBALS['wpdb'];
	}

	/**
	 * Return whether a successful create-draft change owns this object.
	 *
	 * @param int $post_id Post ID.
	 * @return bool
	 */
	public function contains( $post_id ) {
		$query = $this->wpdb->prepare(
			'SELECT id FROM %i WHERE ability = %s AND object_id = %d AND status = %s LIMIT 1',
			$this->wpdb->prefix . 'agentpress_changes',
			'agentpress/create-draft',
			$post_id,
			'APPLIED'
		);

		return null !== $this->wpdb->get_var( $query ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	}
}
