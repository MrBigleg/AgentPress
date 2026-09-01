<?php
/**
 * Bounded visible WordPress site structure.
 *
 * @package AgentPress
 */

namespace AgentPress\Context;

use AgentPress\Errors\ErrorFactory;
use AgentPress\Results\ResultFactory;

/**
 * Builds the exact page/count/taxonomy/menu-location structural snapshot.
 */
final class SiteStructureService {
	/** Hard returned-page limit. */
	private const PAGE_LIMIT = 200;

	/** Query batch size. */
	private const BATCH_SIZE = 200;

	/**
	 * Return the current user's bounded visible site structure.
	 *
	 * @return array<string, mixed>|\WP_Error
	 */
	public function execute() {
		if ( ! current_user_can( 'read' ) ) {
			return ErrorFactory::make( 'AP_NOT_AUTHENTICATED' );
		}

		$visible_pages = $this->visible_ids( 'page', self::PAGE_LIMIT + 1 );
		$page_ids      = array_slice( $visible_pages['sample'], 0, self::PAGE_LIMIT );
		$returned      = array_fill_keys( $page_ids, true );
		$pages         = array();

		foreach ( $page_ids as $page_id ) {
			$page = get_post( $page_id );
			if ( ! is_object( $page ) ) {
				continue;
			}
			$parent_id = (int) $page->post_parent;
			$pages[]   = array(
				'id'        => (int) $page->ID,
				'title'     => $this->bounded_text( get_the_title( $page ), 200 ),
				'slug'      => $this->bounded_text( $page->post_name, 200 ),
				'parent_id' => isset( $returned[ $parent_id ] ) ? $parent_id : 0,
				'status'    => (string) $page->post_status,
			);
		}

		$posts = $this->visible_ids( 'post', 0 );

		return ResultFactory::success(
			array(
				'pages'          => $pages,
				'content_counts' => array(
					'post' => $posts['count'],
					'page' => $visible_pages['count'],
				),
				'taxonomies'     => $this->taxonomies(),
				'menu_locations' => $this->menu_locations(),
				'truncated'      => $visible_pages['count'] > self::PAGE_LIMIT,
			)
		);
	}

	/**
	 * Count all readable IDs while retaining only a bounded sample.
	 *
	 * @param string $post_type Fixed post type.
	 * @param int    $sample_cap Maximum sampled IDs; zero keeps none.
	 * @return array{count:int, sample:array<int, int>}
	 */
	private function visible_ids( $post_type, $sample_cap ) {
		$count  = 0;
		$sample = array();
		$page   = 1;

		do {
			$query        = new \WP_Query(
				array(
					'post_type'              => $post_type,
					'post_status'            => array( 'publish', 'draft', 'pending', 'private' ),
					'fields'                 => 'ids',
					'posts_per_page'         => self::BATCH_SIZE,
					'paged'                  => $page,
					'orderby'                => 'ID',
					'order'                  => 'ASC',
					'no_found_rows'          => true,
					'update_post_meta_cache' => false,
					'update_post_term_cache' => false,
				)
			);
			$ids          = array_map( 'intval', $query->posts );
			$batch_count  = count( $ids );
			$sample_count = count( $sample );
			foreach ( $ids as $id ) {
				if ( current_user_can( 'read_post', $id ) ) {
					++$count;
					if ( $sample_count < $sample_cap ) {
						$sample[] = $id;
						++$sample_count;
					}
				}
			}
			++$page;
		} while ( self::BATCH_SIZE === $batch_count );

		return array(
			'count'  => $count,
			'sample' => $sample,
		);
	}

	/**
	 * Return only category/tag definitions visible in WordPress.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	private function taxonomies() {
		$output = array();
		foreach ( array( 'category', 'post_tag' ) as $name ) {
			$taxonomy = get_taxonomy( $name );
			if ( ! is_object( $taxonomy ) || empty( $taxonomy->public ) ) {
				continue;
			}
			$types    = array_values( array_intersect( array( 'post', 'page' ), (array) $taxonomy->object_type ) );
			$output[] = array(
				'name'         => $name,
				'label'        => $this->bounded_text( $taxonomy->label, 200 ),
				'object_types' => $types,
			);
		}
		return $output;
	}

	/**
	 * Return registered classic menu locations without any destinations.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	private function menu_locations() {
		$registered = get_registered_nav_menus();
		$assigned   = get_nav_menu_locations();
		$output     = array();

		ksort( $registered, SORT_STRING );
		foreach ( array_slice( $registered, 0, 100, true ) as $location => $description ) {
			$menu_id  = isset( $assigned[ $location ] ) ? max( 0, (int) $assigned[ $location ] ) : 0;
			$output[] = array(
				'location'    => substr( preg_replace( '/[^a-z0-9_-]/', '', strtolower( (string) $location ) ), 0, 100 ),
				'description' => $this->bounded_text( $description, 200 ),
				'assigned'    => $menu_id > 0,
				'menu_id'     => $menu_id,
			);
		}
		return $output;
	}

	/**
	 * Strip markup/control bytes and bound site-authored text.
	 *
	 * @param mixed $value Candidate text.
	 * @param int   $limit Maximum characters.
	 * @return string
	 */
	private function bounded_text( $value, $limit ) {
		$value = trim( preg_replace( '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', wp_strip_all_tags( (string) $value ) ) );
		return function_exists( 'mb_substr' ) ? mb_substr( $value, 0, $limit ) : substr( $value, 0, $limit );
	}
}
