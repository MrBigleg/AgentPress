<?php
/**
 * Versioned AgentPress database migration.
 *
 * @package AgentPress
 */

namespace AgentPress\Storage;

/**
 * Owns the exact v0.1 table definitions and guarded upgrade path.
 */
final class Migrator {
	/** Database schema version. */
	public const DB_VERSION = '1';

	/** Database version option. */
	public const VERSION_OPTION = 'agentpress_db_version';

	/**
	 * Run an upgrade only when the stored version differs.
	 *
	 * @return void
	 */
	public static function maybe_migrate() {
		if ( self::DB_VERSION !== get_option( self::VERSION_OPTION ) ) {
			self::migrate();
		}
	}

	/**
	 * Apply all exact table definitions through dbDelta.
	 *
	 * @param \wpdb|null $wpdb Optional database adapter.
	 * @return array<int|string, string>
	 */
	public static function migrate( $wpdb = null ) {
		$wpdb = $wpdb ?? $GLOBALS['wpdb'];

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$tables  = self::table_names( $wpdb );
		$collate = $wpdb->get_charset_collate();
		$queries = array(
			"CREATE TABLE {$tables['change_sets']} (
				id bigint unsigned NOT NULL AUTO_INCREMENT,
				initiator_user_id bigint unsigned NOT NULL,
				title varchar(200) NOT NULL,
				request_summary text NOT NULL,
				source varchar(32) NOT NULL DEFAULT 'webmcp',
				source_session_hash char(64) NOT NULL DEFAULT '',
				status varchar(32) NOT NULL,
				created_at datetime NOT NULL,
				updated_at datetime NOT NULL,
				completed_at datetime NULL,
				PRIMARY KEY  (id),
				KEY initiator_status (initiator_user_id, status),
				KEY status_updated (status, updated_at)
			) $collate;",
			"CREATE TABLE {$tables['changes']} (
				id bigint unsigned NOT NULL AUTO_INCREMENT,
				change_set_id bigint unsigned NOT NULL,
				actor_user_id bigint unsigned NOT NULL,
				ability varchar(100) NOT NULL,
				risk_class char(2) NOT NULL,
				operation varchar(40) NOT NULL,
				object_type varchar(40) NOT NULL DEFAULT '',
				object_id bigint unsigned NOT NULL DEFAULT 0,
				before_json longtext NOT NULL,
				after_json longtext NOT NULL,
				target_state_hash char(64) NOT NULL DEFAULT '',
				proposal_hash char(64) NOT NULL DEFAULT '',
				idempotency_hash char(64) NOT NULL,
				idempotency_scope char(64) NOT NULL,
				status varchar(32) NOT NULL,
				error_code varchar(64) NOT NULL DEFAULT '',
				created_at datetime NOT NULL,
				expires_at datetime NULL,
				approved_by bigint unsigned NOT NULL DEFAULT 0,
				approved_at datetime NULL,
				rejected_by bigint unsigned NOT NULL DEFAULT 0,
				rejected_at datetime NULL,
				applied_at datetime NULL,
				PRIMARY KEY  (id),
				UNIQUE KEY idempotency_scope (idempotency_scope),
				KEY set_status (change_set_id, status),
				KEY object_lookup (object_type, object_id),
				KEY expires_status (status, expires_at)
			) $collate;",
			"CREATE TABLE {$tables['audit_events']} (
				id bigint unsigned NOT NULL AUTO_INCREMENT,
				request_id char(36) NOT NULL,
				actor_type varchar(16) NOT NULL,
				user_id bigint unsigned NOT NULL,
				change_set_id bigint unsigned NOT NULL DEFAULT 0,
				change_id bigint unsigned NOT NULL DEFAULT 0,
				ability varchar(100) NOT NULL,
				object_type varchar(40) NOT NULL DEFAULT '',
				object_id bigint unsigned NOT NULL DEFAULT 0,
				result varchar(20) NOT NULL,
				error_code varchar(64) NOT NULL DEFAULT '',
				arguments_sanitized longtext NOT NULL,
				duration_ms int unsigned NOT NULL DEFAULT 0,
				created_at datetime NOT NULL,
				PRIMARY KEY  (id),
				UNIQUE KEY request_id (request_id),
				KEY user_created (user_id, created_at),
				KEY set_created (change_set_id, created_at),
				KEY result_created (result, created_at)
			) $collate;",
		);

		$result = dbDelta( $queries );
		update_option( self::VERSION_OPTION, self::DB_VERSION, false );

		return $result;
	}

	/**
	 * Return exact site-local table names.
	 *
	 * @param \wpdb|null $wpdb Optional database adapter.
	 * @return array<string, string>
	 */
	public static function table_names( $wpdb = null ) {
		$wpdb = $wpdb ?? $GLOBALS['wpdb'];

		return array(
			'change_sets'  => $wpdb->prefix . 'agentpress_change_sets',
			'changes'      => $wpdb->prefix . 'agentpress_changes',
			'audit_events' => $wpdb->prefix . 'agentpress_audit_events',
		);
	}
}
