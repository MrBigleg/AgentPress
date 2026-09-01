<?php
/**
 * Bounded permission-aware WordPress content reads.
 *
 * @package AgentPress
 */

namespace AgentPress\Content;

use AgentPress\Errors\ErrorFactory;
use AgentPress\Results\ResultFactory;

/** Implements the exact list-content and get-content read contracts. */
final class ContentReadService {
	/** Candidate query batch size. */
	private const BATCH_SIZE = 200;

	/** Maximum raw content characters. */
	private const CONTENT_LIMIT = 50000;

	/** Supported post statuses. */
	private const STATUSES = array( 'publish', 'draft', 'pending', 'private' );

	/**
	 * Return one deterministic page of readable posts or pages.
	 *
	 * @param array<string, mixed> $input Validated Ability input.
	 * @return array<string, mixed>|\WP_Error
	 */
	public function list_content( $input ) {
		if ( ! current_user_can( 'read' ) ) {
			return ErrorFactory::make( 'AP_NOT_AUTHENTICATED' );
		}

		$post_type = isset( $input['post_type'] ) ? (string) $input['post_type'] : 'post';
		if ( ! in_array( $post_type, array( 'post', 'page' ), true ) ) {
			return ErrorFactory::make( 'AP_UNSUPPORTED_POST_TYPE' );
		}
		if ( isset( $input['taxonomy']['name'] ) && ! in_array( $input['taxonomy']['name'], array( 'category', 'post_tag' ), true ) ) {
			return ErrorFactory::make( 'AP_UNSUPPORTED_TAXONOMY' );
		}

		$page       = isset( $input['page'] ) ? (int) $input['page'] : 1;
		$per_page   = isset( $input['per_page'] ) ? (int) $input['per_page'] : 20;
		$status     = isset( $input['status'] ) ? (string) $input['status'] : 'any';
		$orderby    = isset( $input['orderby'] ) ? (string) $input['orderby'] : 'modified';
		$order      = isset( $input['order'] ) ? strtolower( (string) $input['order'] ) : 'desc';
		$valid_page = $page >= 1 && $per_page >= 1 && $per_page <= 100;
		if ( ! $valid_page || ! in_array( $status, array_merge( self::STATUSES, array( 'any' ) ), true ) || ! in_array( $orderby, array( 'modified', 'date', 'title' ), true ) || ! in_array( $order, array( 'asc', 'desc' ), true ) ) {
			return ErrorFactory::make( 'AP_SCHEMA_INVALID' );
		}

		$offset     = ( $page - 1 ) * $per_page;
		$total      = 0;
		$items      = array();
		$query_page = 1;
		$query_args = $this->query_args( $input, $post_type, $status, $orderby, $order );

		do {
			$query_args['paged'] = $query_page;
			$query               = new \WP_Query( $query_args );
			$ids                 = array_map( 'intval', $query->posts );
			$batch_count         = count( $ids );
			foreach ( $ids as $id ) {
				if ( ! current_user_can( 'read_post', $id ) ) {
					continue;
				}
				if ( $total >= $offset && count( $items ) < $per_page ) {
					$post = get_post( $id );
					if ( is_object( $post ) ) {
						$items[] = $this->summary( $post );
					}
				}
				++$total;
			}
			++$query_page;
		} while ( self::BATCH_SIZE === $batch_count );

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

	/**
	 * Return one readable post or page with bounded raw editable fields.
	 *
	 * @param array<string, mixed> $input Validated Ability input.
	 * @return array<string, mixed>|\WP_Error
	 */
	public function get_content( $input ) {
		if ( ! current_user_can( 'read' ) ) {
			return ErrorFactory::make( 'AP_NOT_AUTHENTICATED' );
		}
		$content_id = isset( $input['content_id'] ) ? (int) $input['content_id'] : 0;
		if ( $content_id < 1 ) {
			return ErrorFactory::make( 'AP_SCHEMA_INVALID' );
		}
		$post = get_post( $content_id );
		if ( ! is_object( $post ) || ! in_array( $post->post_status, self::STATUSES, true ) ) {
			return ErrorFactory::make( 'AP_CONTENT_NOT_FOUND' );
		}
		if ( ! in_array( $post->post_type, array( 'post', 'page' ), true ) ) {
			return ErrorFactory::make( 'AP_UNSUPPORTED_POST_TYPE' );
		}
		if ( ! current_user_can( 'read_post', $content_id ) ) {
			return ErrorFactory::make( 'AP_PERMISSION_DENIED' );
		}

		$content        = (string) $post->post_content;
		$content_length = $this->text_length( $content );

		return ResultFactory::success(
			array(
				'id'                => (int) $post->ID,
				'type'              => (string) $post->post_type,
				'title'             => $this->bounded_raw( $post->post_title, 200 ),
				'content'           => $this->bounded_raw( $content, self::CONTENT_LIMIT ),
				'content_truncated' => $content_length > self::CONTENT_LIMIT,
				'excerpt'           => $this->bounded_raw( $post->post_excerpt, 5000 ),
				'slug'              => $this->bounded_raw( $post->post_name, 200 ),
				'status'            => (string) $post->post_status,
				'author_id'         => (int) $post->post_author,
				'parent_id'         => (int) $post->post_parent,
				'modified_gmt'      => $this->modified_gmt( $post ),
				'terms'             => $this->terms( $post ),
			)
		);
	}

	/**
	 * Construct a bounded candidate-ID query.
	 *
	 * @param array<string, mixed> $input     Validated input.
	 * @param string               $post_type Post type.
	 * @param string               $status    Status filter.
	 * @param string               $orderby   Primary ordering.
	 * @param string               $order     Direction.
	 * @return array<string, mixed>
	 */
	private function query_args( $input, $post_type, $status, $orderby, $order ) {
		$direction = strtoupper( $order );
		$args      = array(
			'post_type'              => $post_type,
			'post_status'            => 'any' === $status ? self::STATUSES : array( $status ),
			'fields'                 => 'ids',
			'posts_per_page'         => self::BATCH_SIZE,
			'orderby'                => array(
				$orderby => $direction,
				'ID'     => $direction,
			),
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		);
		if ( isset( $input['search'] ) ) {
			$args['s'] = (string) $input['search'];
		}
		if ( isset( $input['author_id'] ) ) {
			$args['author'] = (int) $input['author_id'];
		}
		if ( isset( $input['taxonomy'] ) ) {
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- Fixed, bounded term filter required by the Ability contract.
			$args['tax_query'] = array(
				array(
					'taxonomy' => (string) $input['taxonomy']['name'],
					'field'    => 'term_id',
					'terms'    => array_map( 'intval', $input['taxonomy']['term_ids'] ),
					'operator' => 'IN',
				),
			);
		}
		return $args;
	}

	/**
	 * Project one bounded content summary.
	 *
	 * @param \WP_Post $post Post object.
	 * @return array<string, mixed>
	 */
	private function summary( $post ) {
		return array(
			'id'           => (int) $post->ID,
			'title'        => $this->bounded_text( $post->post_title, 200 ),
			'slug'         => $this->bounded_raw( $post->post_name, 200 ),
			'type'         => (string) $post->post_type,
			'status'       => (string) $post->post_status,
			'modified_gmt' => $this->modified_gmt( $post ),
			'author_id'    => (int) $post->post_author,
			'excerpt'      => $this->bounded_text( $post->post_excerpt, 5000 ),
		);
	}

	/**
	 * Project fixed visible category and tag assignments.
	 *
	 * @param \WP_Post $post Post object.
	 * @return array<int, array<string, mixed>>
	 */
	private function terms( $post ) {
		$output = array();
		foreach ( array( 'category', 'post_tag' ) as $taxonomy ) {
			$terms = get_the_terms( $post, $taxonomy );
			if ( ! is_array( $terms ) ) {
				continue;
			}
			usort(
				$terms,
				static function ( $left, $right ) {
					return (int) $left->term_id <=> (int) $right->term_id;
				}
			);
			foreach ( $terms as $term ) {
				$output[] = array(
					'taxonomy' => $taxonomy,
					'term_id'  => (int) $term->term_id,
					'name'     => $this->bounded_text( $term->name, 200 ),
					'slug'     => $this->bounded_raw( $term->slug, 200 ),
				);
				if ( 100 === count( $output ) ) {
					break 2;
				}
			}
		}
		return $output;
	}

	/**
	 * Normalize WordPress modified time to RFC3339 UTC.
	 *
	 * @param \WP_Post $post Post object.
	 * @return string
	 */
	private function modified_gmt( $post ) {
		$modified_gmt = (string) $post->post_modified_gmt;
		if ( '' === $modified_gmt || 0 === strpos( $modified_gmt, '0000-00-00' ) ) {
			$modified_gmt = get_gmt_from_date( (string) $post->post_modified );
		}
		$timestamp = strtotime( $modified_gmt . ' UTC' );
		return gmdate( 'Y-m-d\TH:i:s\Z', false === $timestamp ? 0 : $timestamp );
	}

	/**
	 * Strip markup and bound site-authored text.
	 *
	 * @param mixed $value Candidate text.
	 * @param int   $limit Maximum characters.
	 * @return string
	 */
	private function bounded_text( $value, $limit ) {
		return $this->bounded_raw( wp_strip_all_tags( (string) $value ), $limit );
	}

	/**
	 * Remove control bytes and bound raw editable text.
	 *
	 * @param mixed $value Candidate text.
	 * @param int   $limit Maximum characters.
	 * @return string
	 */
	private function bounded_raw( $value, $limit ) {
		$value = preg_replace( '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', (string) $value );
		return function_exists( 'mb_substr' ) ? mb_substr( $value, 0, $limit ) : substr( $value, 0, $limit );
	}

	/**
	 * Count characters using the available runtime.
	 *
	 * @param string $value Text to measure.
	 * @return int
	 */
	private function text_length( $value ) {
		return function_exists( 'mb_strlen' ) ? mb_strlen( $value ) : strlen( $value );
	}
}
