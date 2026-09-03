<?php
/**
 * Read-only classic WordPress menu adapter.
 *
 * @package AgentPress
 */

namespace AgentPress\Navigation;

use AgentPress\Changes\StateHasher;
use AgentPress\Errors\ErrorFactory;

/** Produces one bounded semantic snapshot for a registered classic-menu location. */
final class ClassicMenuAdapter {
	/** Maximum accepted menu items. */
	private const ITEM_LIMIT = 200;

	/**
	 * Canonical state hasher.
	 *
	 * @var StateHasher
	 */
	private $hasher;

	/**
	 * Construct the classic-menu adapter.
	 *
	 * @param StateHasher|null $hasher Optional canonical state hasher.
	 */
	public function __construct( $hasher = null ) {
		$this->hasher = $hasher ?? new StateHasher();
	}

	/**
	 * Return the normalized state assigned to one registered location.
	 *
	 * @param string $location Registered theme location.
	 * @return array<string, mixed>|\WP_Error
	 */
	public function snapshot( $location ) {
		$location = (string) $location;
		if ( 1 !== preg_match( '/^[a-z0-9_-]{1,100}$/', $location ) ) {
			return ErrorFactory::make( 'AP_SCHEMA_INVALID' );
		}

		$registered = get_registered_nav_menus();
		if ( ! array_key_exists( $location, $registered ) ) {
			return ErrorFactory::make( 'AP_UNSUPPORTED_NAVIGATION' );
		}

		$locations = get_nav_menu_locations();
		$menu_id   = isset( $locations[ $location ] ) ? (int) $locations[ $location ] : 0;
		if ( $menu_id < 1 ) {
			return ErrorFactory::make( 'AP_NAVIGATION_NOT_FOUND' );
		}

		$menu = wp_get_nav_menu_object( $menu_id );
		if ( ! is_object( $menu ) || (int) $menu->term_id !== $menu_id ) {
			return ErrorFactory::make( 'AP_NAVIGATION_NOT_FOUND' );
		}

		$menu_items = wp_get_nav_menu_items(
			$menu_id,
			array(
				'post_status'            => 'publish',
				'update_post_term_cache' => false,
			)
		);
		if ( false === $menu_items || ! is_array( $menu_items ) ) {
			return ErrorFactory::make( 'AP_INTERNAL_ERROR' );
		}
		if ( count( $menu_items ) > self::ITEM_LIMIT ) {
			return ErrorFactory::make( 'AP_UNSUPPORTED_NAVIGATION' );
		}

		$items = array();
		foreach ( $menu_items as $menu_item ) {
			$item = $this->normalize_item( $menu_item );
			if ( is_wp_error( $item ) ) {
				return $item;
			}
			$items[] = $item;
		}

		usort(
			$items,
			static function ( $left, $right ) {
				$position = $left['position'] <=> $right['position'];
				return 0 !== $position ? $position : $left['item_id'] <=> $right['item_id'];
			}
		);

		$snapshot               = array(
			'adapter'   => 'classic-menu',
			'location'  => $location,
			'menu_id'   => $menu_id,
			'menu_name' => $this->bounded_text( $menu->name, 200 ),
			'items'     => $items,
		);
		$snapshot['state_hash'] = $this->hasher->state_hash( $snapshot );

		return $snapshot;
	}

	/**
	 * Normalize one WordPress navigation item without mutating it.
	 *
	 * @param mixed $menu_item Candidate menu item.
	 * @return array<string, mixed>|\WP_Error
	 */
	private function normalize_item( $menu_item ) {
		if ( ! is_object( $menu_item ) ) {
			return ErrorFactory::make( 'AP_INTERNAL_ERROR' );
		}

		$item_id   = isset( $menu_item->ID ) ? (int) $menu_item->ID : 0;
		$parent_id = isset( $menu_item->menu_item_parent ) ? (int) $menu_item->menu_item_parent : 0;
		$position  = isset( $menu_item->menu_order ) ? (int) $menu_item->menu_order : 0;
		$type      = isset( $menu_item->type ) ? (string) $menu_item->type : '';
		$object    = isset( $menu_item->object ) ? $this->bounded_raw( $menu_item->object, 100 ) : '';
		$object_id = isset( $menu_item->object_id ) ? (int) $menu_item->object_id : 0;
		$url       = isset( $menu_item->url ) ? $this->bounded_raw( $menu_item->url, 2048 ) : '';

		if ( $item_id < 1 || $parent_id < 0 || $position < 1 || ! in_array( $type, array( 'post_type', 'taxonomy', 'custom' ), true ) ) {
			return ErrorFactory::make( 'AP_UNSUPPORTED_NAVIGATION' );
		}
		if ( false === filter_var( $url, FILTER_VALIDATE_URL ) ) {
			return ErrorFactory::make( 'AP_UNSUPPORTED_NAVIGATION' );
		}

		if ( 'post_type' === $type ) {
			$post = $object_id > 0 ? get_post( $object_id ) : null;
			if ( ! is_object( $post ) || ! current_user_can( 'read_post', $object_id ) ) {
				return ErrorFactory::make( 'AP_PERMISSION_DENIED' );
			}
		}
		if ( 'taxonomy' === $type && ( $object_id < 1 || ! is_object( get_term( $object_id, $object ) ) ) ) {
			return ErrorFactory::make( 'AP_UNSUPPORTED_NAVIGATION' );
		}

		return array(
			'item_id'        => $item_id,
			'parent_item_id' => $parent_id,
			'position'       => $position,
			'label'          => $this->bounded_text( $menu_item->title ?? '', 200 ),
			'type'           => $type,
			'object'         => $object,
			'object_id'      => max( 0, $object_id ),
			'url'            => $url,
		);
	}

	/**
	 * Strip markup/control bytes and bound site-authored text.
	 *
	 * @param mixed $value Candidate text.
	 * @param int   $limit Maximum characters.
	 * @return string
	 */
	private function bounded_text( $value, $limit ) {
		return $this->bounded_raw( wp_strip_all_tags( (string) $value ), $limit );
	}

	/**
	 * Strip control bytes and bound one raw field.
	 *
	 * @param mixed $value Candidate value.
	 * @param int   $limit Maximum characters.
	 * @return string
	 */
	private function bounded_raw( $value, $limit ) {
		$value = preg_replace( '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', (string) $value );
		return function_exists( 'mb_substr' ) ? mb_substr( $value, 0, $limit ) : substr( $value, 0, $limit );
	}
}
