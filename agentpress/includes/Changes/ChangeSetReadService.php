<?php
/**
 * Visibility-filtered Change Set reads.
 *
 * @package AgentPress
 */

namespace AgentPress\Changes;

// phpcs:disable Generic.Commenting.DocComment.MissingShort,Squiz.Commenting.FunctionComment.ParamCommentFullStop,Squiz.Commenting.FunctionComment.MissingParamTag -- Concise internal service annotations remain type-complete.

use AgentPress\Errors\ErrorFactory;
use AgentPress\Results\ResultFactory;

/**
 * Returns bounded Change Set lists and semantic detail.
 */
final class ChangeSetReadService {
	/** @var \wpdb */
	private $wpdb;

	/** @var ChangeSetRepository */
	private $sets;

	/** @var ChangeRepository */
	private $changes;

	/** @param \wpdb|null $wpdb Optional database adapter. */
	public function __construct( $wpdb = null ) {
		$this->wpdb    = $wpdb ?? $GLOBALS['wpdb'];
		$this->sets    = new ChangeSetRepository( $this->wpdb );
		$this->changes = new ChangeRepository( $this->wpdb );
	}

	/**
	 * Return one visible Change Set with bounded semantic children.
	 *
	 * @param array<string, mixed> $input Validated input.
	 * @return array<string, mixed>|\WP_Error
	 */
	public function get( $input ) {
		if ( ! is_array( $input ) || array( 'change_set_id' ) !== array_keys( $input ) || ! is_int( $input['change_set_id'] ) || $input['change_set_id'] <= 0 ) {
			return ErrorFactory::make( 'AP_SCHEMA_INVALID' );
		}
		$set = $this->visible_set( (int) $input['change_set_id'] );
		if ( is_wp_error( $set ) ) {
			return $set;
		}

		$query = $this->wpdb->prepare(
			'SELECT id FROM %i WHERE change_set_id = %d ORDER BY id ASC LIMIT 501',
			$this->wpdb->prefix . 'agentpress_changes',
			$set['id']
		);
		$ids   = array_map( 'intval', $this->wpdb->get_col( $query ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		if ( count( $ids ) > 500 ) {
			return ErrorFactory::make( 'AP_INTERNAL_ERROR' );
		}
		$items = array();
		foreach ( $ids as $id ) {
			$change = $this->changes->find( $id );
			if ( is_array( $change ) ) {
				$items[] = $this->change_item( $change );
			}
		}

		return ResultFactory::success(
			array(
				'id'                => (int) $set['id'],
				'reference'         => 'AP-' . (int) $set['id'],
				'title'             => $this->text( $set['title'], 200 ),
				'request_summary'   => $this->text( $set['request_summary'], 5000 ),
				'initiator_user_id' => (int) $set['initiator_user_id'],
				'status'            => (string) $set['status'],
				'created_gmt'       => $this->date( $set['created_at'] ),
				'updated_gmt'       => $this->date( $set['updated_at'] ),
				'changes'           => $items,
			)
		);
	}

	/**
	 * Return one stable newest-first page of visible Change Sets.
	 *
	 * @param array<string, mixed> $input Validated input.
	 * @return array<string, mixed>|\WP_Error
	 */
	public function listing( $input ) {
		$validated = $this->validate_list( $input );
		if ( is_wp_error( $validated ) ) {
			return $validated;
		}
		$page     = isset( $input['page'] ) ? (int) $input['page'] : 1;
		$per_page = isset( $input['per_page'] ) ? (int) $input['per_page'] : 20;
		$where    = array();
		$args     = array();
		if ( ! current_user_can( 'manage_options' ) ) {
			$where[] = 's.initiator_user_id = %d';
			$args[]  = get_current_user_id();
		}
		if ( isset( $input['status'] ) ) {
			$where[] = 's.status = %s';
			$args[]  = $input['status'];
		}
		$where_sql = empty( $where ) ? '1=1' : implode( ' AND ', $where );
		$count_sql = 'SELECT COUNT(*) FROM ' . $this->wpdb->prefix . 'agentpress_change_sets s WHERE ' . $where_sql;
		$total     = (int) $this->wpdb->get_var( empty( $args ) ? $count_sql : $this->wpdb->prepare( $count_sql, $args ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		$list_args = array_merge( $args, array( $per_page, ( $page - 1 ) * $per_page ) );
		$list_sql  = 'SELECT s.id, s.initiator_user_id, s.title, s.status, s.created_at, s.updated_at, COUNT(c.id) change_count, SUM(CASE WHEN c.status = \'PENDING_APPROVAL\' THEN 1 ELSE 0 END) pending_count FROM ' . $this->wpdb->prefix . 'agentpress_change_sets s LEFT JOIN ' . $this->wpdb->prefix . 'agentpress_changes c ON c.change_set_id = s.id WHERE ' . $where_sql . ' GROUP BY s.id ORDER BY s.id DESC LIMIT %d OFFSET %d';
		$rows      = $this->wpdb->get_results( $this->wpdb->prepare( $list_sql, $list_args ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$items     = array();
		foreach ( is_array( $rows ) ? $rows : array() as $row ) {
			$items[] = array(
				'id'                => (int) $row['id'],
				'reference'         => 'AP-' . (int) $row['id'],
				'title'             => $this->text( $row['title'], 200 ),
				'initiator_user_id' => (int) $row['initiator_user_id'],
				'status'            => (string) $row['status'],
				'change_count'      => (int) $row['change_count'],
				'pending_count'     => (int) $row['pending_count'],
				'created_gmt'       => $this->date( $row['created_at'] ),
				'updated_gmt'       => $this->date( $row['updated_at'] ),
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

	/** @param int $id Set ID. @return array<string, mixed>|\WP_Error */
	private function visible_set( $id ) {
		$set = $this->sets->find( $id );
		if ( ! is_array( $set ) || ( ! current_user_can( 'manage_options' ) && get_current_user_id() !== (int) $set['initiator_user_id'] ) ) {
			return ErrorFactory::make( 'AP_CHANGE_NOT_FOUND' );
		}
		return $set;
	}

	/** @param array<string, mixed> $input Input. @return true|\WP_Error */
	private function validate_list( $input ) {
		$allowed  = array( 'status', 'page', 'per_page' );
		$statuses = array( 'OPEN', 'WORKING', 'READY_FOR_REVIEW', 'PARTIALLY_APPROVED', 'COMPLETED', 'REJECTED', 'FAILED' );
		if ( ! is_array( $input ) || array_diff( array_keys( $input ), $allowed ) || ( isset( $input['status'] ) && ( ! is_string( $input['status'] ) || ! in_array( $input['status'], $statuses, true ) ) ) ) {
			return ErrorFactory::make( 'AP_SCHEMA_INVALID' );
		}
		if ( isset( $input['page'] ) && ( ! is_int( $input['page'] ) || $input['page'] < 1 ) ) {
			return ErrorFactory::make( 'AP_SCHEMA_INVALID' );
		}
		if ( isset( $input['per_page'] ) && ( ! is_int( $input['per_page'] ) || $input['per_page'] < 1 || $input['per_page'] > 50 ) ) {
			return ErrorFactory::make( 'AP_SCHEMA_INVALID' );
		}
		return true;
	}

	/** @param array<string, mixed> $change Change row. @return array<string, mixed> */
	private function change_item( $change ) {
		return array(
			'id'              => (int) $change['id'],
			'reference'       => 'AP-C-' . (int) $change['id'],
			'ability'         => (string) $change['ability'],
			'risk_class'      => (string) $change['risk_class'],
			'operation'       => (string) $change['operation'],
			'object_type'     => (string) $change['object_type'],
			'object_id'       => (int) $change['object_id'],
			'status'          => (string) $change['status'],
			'semantic_before' => $this->semantic( $change['before_json'] ),
			'semantic_after'  => $this->semantic( $change['after_json'] ),
			'created_gmt'     => $this->date( $change['created_at'] ),
			'applied_gmt'     => $this->date( $change['applied_at'], true ),
			'expires_gmt'     => $this->date( $change['expires_at'], true ),
		);
	}

	/** Maximum semantic nesting depth before truncation. */
	private const MAX_SEMANTIC_DEPTH = 8;

	/** @param mixed $value Semantic value. @return string */
	private function semantic( $value ) {
		$normalized = $this->semantic_value( $value, '', 0 );
		$json       = wp_json_encode( $normalized, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
		return $this->text( is_string( $json ) ? $json : '', 5000, false );
	}

	/** @param mixed $value Value. @param string $key Parent key. @param int $depth Current depth. @return mixed */
	private function semantic_value( $value, $key, $depth ) {
		if ( $depth > self::MAX_SEMANTIC_DEPTH ) {
			return array();
		}
		if ( is_array( $value ) ) {
			$result = array();
			foreach ( array_slice( $value, 0, 200, true ) as $child_key => $child ) {
				$result[ $child_key ] = $this->semantic_value( $child, (string) $child_key, $depth + 1 );
			}
			return $result;
		}
		if ( is_string( $value ) ) {
			if ( in_array( $key, array( 'content', 'post_content' ), true ) || strlen( $value ) > 500 ) {
				return array(
					'bytes'   => strlen( $value ),
					'sha256'  => hash( 'sha256', $value ),
					'preview' => $this->text( $value, 200 ),
				);
			}
			return $this->text( $value, 500 );
		}
		return is_scalar( $value ) || null === $value ? $value : '';
	}

	/** @param mixed $value Date. @param bool $empty_allowed Allow empty. @return string */
	private function date( $value, $empty_allowed = false ) {
		if ( ! is_string( $value ) || '' === $value || '0000-00-00 00:00:00' === $value ) {
			return $empty_allowed ? '' : '1970-01-01T00:00:00Z';
		}
		$timestamp = strtotime( $value . ' UTC' );
		return false === $timestamp ? ( $empty_allowed ? '' : '1970-01-01T00:00:00Z' ) : gmdate( 'Y-m-d\TH:i:s\Z', $timestamp );
	}

	/** @param mixed $value Text. @param int $limit Character limit. @param bool $strip Whether to strip markup. @return string */
	private function text( $value, $limit, $strip = true ) {
		$value = is_string( $value ) ? $value : '';
		$value = $strip ? sanitize_textarea_field( wp_strip_all_tags( $value ) ) : $value;
		return function_exists( 'mb_substr' ) ? mb_substr( $value, 0, $limit ) : substr( $value, 0, $limit );
	}
}
// phpcs:enable Generic.Commenting.DocComment.MissingShort,Squiz.Commenting.FunctionComment.ParamCommentFullStop,Squiz.Commenting.FunctionComment.MissingParamTag
