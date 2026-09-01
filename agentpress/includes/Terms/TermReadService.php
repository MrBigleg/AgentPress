<?php
/**
 * Bounded permission-aware WordPress term reads.
 *
 * @package AgentPress
 */

namespace AgentPress\Terms;

use AgentPress\Errors\ErrorFactory;
use AgentPress\Results\ResultFactory;

/** Implements the exact list-terms read contract. */
final class TermReadService {
	/**
	 * Return one deterministic page of categories or tags.
	 *
	 * @param array<string, mixed> $input Validated Ability input.
	 * @return array<string, mixed>|\WP_Error
	 */
	public function execute( $input ) {
		if ( ! current_user_can( 'read' ) ) {
			return ErrorFactory::make( 'AP_NOT_AUTHENTICATED' );
		}
		$taxonomy = isset( $input['taxonomy'] ) ? (string) $input['taxonomy'] : '';
		if ( ! in_array( $taxonomy, array( 'category', 'post_tag' ), true ) ) {
			return ErrorFactory::make( 'AP_UNSUPPORTED_TAXONOMY' );
		}
		$taxonomy_object = get_taxonomy( $taxonomy );
		if ( ! is_object( $taxonomy_object ) || empty( $taxonomy_object->public ) ) {
			return ErrorFactory::make( 'AP_UNSUPPORTED_TAXONOMY' );
		}

		$page       = isset( $input['page'] ) ? (int) $input['page'] : 1;
		$per_page   = isset( $input['per_page'] ) ? (int) $input['per_page'] : 20;
		$hide_empty = isset( $input['hide_empty'] ) ? $input['hide_empty'] : false;
		$search     = isset( $input['search'] ) ? (string) $input['search'] : '';
		if ( $page < 1 || $per_page < 1 || $per_page > 100 || ! is_bool( $hide_empty ) || strlen( $search ) > 200 ) {
			return ErrorFactory::make( 'AP_SCHEMA_INVALID' );
		}

		$args = array(
			'taxonomy'     => $taxonomy,
			'hide_empty'   => $hide_empty,
			'hierarchical' => false,
			'orderby'      => 'term_id',
			'order'        => 'ASC',
		);
		if ( '' !== $search ) {
			$args['search'] = $search;
		}

		$count_args           = $args;
		$count_args['fields'] = 'count';
		$total                = get_terms( $count_args );
		if ( is_wp_error( $total ) ) {
			return ErrorFactory::make( 'AP_INTERNAL_ERROR' );
		}

		$args['number'] = $per_page;
		$args['offset'] = ( $page - 1 ) * $per_page;
		$terms          = get_terms( $args );
		if ( is_wp_error( $terms ) ) {
			return ErrorFactory::make( 'AP_INTERNAL_ERROR' );
		}

		$items = array();
		foreach ( $terms as $term ) {
			$items[] = array(
				'term_id'     => (int) $term->term_id,
				'taxonomy'    => $taxonomy,
				'name'        => $this->bounded_text( $term->name, 200 ),
				'slug'        => $this->bounded_raw( $term->slug, 200 ),
				'description' => $this->bounded_text( $term->description, 5000 ),
				'parent_id'   => (int) $term->parent,
				'count'       => max( 0, (int) $term->count ),
			);
		}

		$total = max( 0, (int) $total );
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
	 * Strip markup and bound site-authored term text.
	 *
	 * @param mixed $value Candidate text.
	 * @param int   $limit Maximum characters.
	 * @return string
	 */
	private function bounded_text( $value, $limit ) {
		return $this->bounded_raw( wp_strip_all_tags( (string) $value ), $limit );
	}

	/**
	 * Remove control bytes and bound raw term text.
	 *
	 * @param mixed $value Candidate text.
	 * @param int   $limit Maximum characters.
	 * @return string
	 */
	private function bounded_raw( $value, $limit ) {
		$value = preg_replace( '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', (string) $value );
		return function_exists( 'mb_substr' ) ? mb_substr( $value, 0, $limit ) : substr( $value, 0, $limit );
	}
}
