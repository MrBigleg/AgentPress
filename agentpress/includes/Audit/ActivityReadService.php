<?php
/**
 * Visibility-filtered Activity reads.
 *
 * @package AgentPress
 */

namespace AgentPress\Audit;

// phpcs:disable Generic.Commenting.DocComment.MissingShort,Squiz.Commenting.FunctionComment.ParamCommentFullStop -- Concise internal service annotations remain type-complete.

use AgentPress\Errors\ErrorFactory;
use AgentPress\Results\ResultFactory;

/**
 * Returns sanitized audit metadata without stored argument payloads.
 */
final class ActivityReadService {
	/** @var \wpdb */
	private $wpdb;

	/** @param \wpdb|null $wpdb Optional database adapter. */
	public function __construct( $wpdb = null ) {
		$this->wpdb = $wpdb ?? $GLOBALS['wpdb'];
	}

	/**
	 * Return one stable newest-first page of visible events.
	 *
	 * @param array<string, mixed> $input Validated input.
	 * @return array<string, mixed>|\WP_Error
	 */
	public function execute( $input ) {
		$validated = $this->validate( $input );
		if ( is_wp_error( $validated ) ) {
			return $validated;
		}
		$page     = isset( $input['page'] ) ? (int) $input['page'] : 1;
		$per_page = isset( $input['per_page'] ) ? (int) $input['per_page'] : 50;
		$where    = array();
		$args     = array();
		if ( ! current_user_can( 'manage_options' ) ) {
			$where[] = 'user_id = %d';
			$args[]  = get_current_user_id();
		}
		if ( isset( $input['change_set_id'] ) ) {
			$where[] = 'change_set_id = %d';
			$args[]  = $input['change_set_id'];
		}
		if ( isset( $input['result'] ) ) {
			$where[] = 'result = %s';
			$args[]  = $input['result'];
		}
		$where_sql = empty( $where ) ? '1=1' : implode( ' AND ', $where );
		$table     = $this->wpdb->prefix . 'agentpress_audit_events';
		$count_sql = 'SELECT COUNT(*) FROM ' . $table . ' WHERE ' . $where_sql;
		$total     = (int) $this->wpdb->get_var( empty( $args ) ? $count_sql : $this->wpdb->prepare( $count_sql, $args ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$list_sql  = 'SELECT id, request_id, created_at, actor_type, user_id, ability, object_type, object_id, result, error_code, duration_ms, change_set_id, change_id FROM ' . $table . ' WHERE ' . $where_sql . ' ORDER BY id DESC LIMIT %d OFFSET %d';
		$rows      = $this->wpdb->get_results( $this->wpdb->prepare( $list_sql, array_merge( $args, array( $per_page, ( $page - 1 ) * $per_page ) ) ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$items     = array();
		foreach ( is_array( $rows ) ? $rows : array() as $row ) {
			$timestamp = strtotime( $row['created_at'] . ' UTC' );
			$items[]   = array(
				'id'            => (int) $row['id'],
				'request_id'    => (string) $row['request_id'],
				'created_gmt'   => false === $timestamp ? '1970-01-01T00:00:00Z' : gmdate( 'Y-m-d\TH:i:s\Z', $timestamp ),
				'actor_type'    => (string) $row['actor_type'],
				'user_id'       => (int) $row['user_id'],
				'ability'       => substr( (string) $row['ability'], 0, 100 ),
				'object_type'   => substr( (string) $row['object_type'], 0, 40 ),
				'object_id'     => (int) $row['object_id'],
				'result'        => (string) $row['result'],
				'error_code'    => substr( (string) $row['error_code'], 0, 64 ),
				'duration_ms'   => max( 0, (int) $row['duration_ms'] ),
				'change_set_id' => (int) $row['change_set_id'],
				'change_id'     => (int) $row['change_id'],
			);
		}
		return ResultFactory::success(
			array(
				'items'       => $items,
				'page'        => $page,
				'per_page'    => $per_page,
				'total'       => $total,
				'total_pages' => 0 === $total ? 0 : (int) ceil( $total / $per_page ),
			)
		);
	}

	/** @param array<string, mixed> $input Input. @return true|\WP_Error */
	private function validate( $input ) {
		$allowed = array( 'change_set_id', 'result', 'page', 'per_page' );
		if ( ! is_array( $input ) || array_diff( array_keys( $input ), $allowed ) ) {
			return ErrorFactory::make( 'AP_SCHEMA_INVALID' );
		}
		if ( isset( $input['change_set_id'] ) && ( ! is_int( $input['change_set_id'] ) || $input['change_set_id'] <= 0 ) ) {
			return ErrorFactory::make( 'AP_SCHEMA_INVALID' );
		}
		if ( isset( $input['result'] ) && ( ! is_string( $input['result'] ) || ! in_array( $input['result'], array( 'SUCCESS', 'DENIED', 'FAILED', 'PENDING', 'REJECTED', 'REPLAYED' ), true ) ) ) {
			return ErrorFactory::make( 'AP_SCHEMA_INVALID' );
		}
		if ( isset( $input['page'] ) && ( ! is_int( $input['page'] ) || $input['page'] < 1 ) ) {
			return ErrorFactory::make( 'AP_SCHEMA_INVALID' );
		}
		if ( isset( $input['per_page'] ) && ( ! is_int( $input['per_page'] ) || $input['per_page'] < 1 || $input['per_page'] > 100 ) ) {
			return ErrorFactory::make( 'AP_SCHEMA_INVALID' );
		}
		return true;
	}
}
// phpcs:enable Generic.Commenting.DocComment.MissingShort,Squiz.Commenting.FunctionComment.ParamCommentFullStop
