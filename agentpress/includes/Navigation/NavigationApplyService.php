<?php
/**
 * Classic-menu navigation apply service.
 *
 * @package AgentPress
 */

namespace AgentPress\Navigation;

use AgentPress\Errors\ErrorFactory;

/**
 * Applies an approved navigation staging change to the live classic menu.
 */
final class NavigationApplyService {
	/**
	 * Classic-menu adapter.
	 *
	 * @var ClassicMenuAdapter
	 */
	private $adapter;

	/**
	 * Construct the navigation apply service.
	 *
	 * @param ClassicMenuAdapter|null $adapter Optional classic-menu adapter.
	 */
	public function __construct( $adapter = null ) {
		$this->adapter = $adapter ?? new ClassicMenuAdapter();
	}

	/**
	 * Apply one approved navigation operation to the live menu.
	 *
	 * @param array<string, mixed> $change Durable change row.
	 * @return array<string, mixed>|\WP_Error
	 */
	public function apply( $change ) {
		$location = $this->location( $change );
		if ( is_wp_error( $location ) ) {
			return $location;
		}
		$menu_id = $this->menu_id( $location );
		if ( $menu_id < 1 ) {
			return ErrorFactory::make( 'AP_NAVIGATION_NOT_FOUND' );
		}

		$operation = isset( $change['operation'] ) ? (string) $change['operation'] : '';
		$pinned    = 0;
		$position  = 1;

		if ( 'add' === $operation ) {
			$created = $this->apply_add( $menu_id, $change );
			if ( is_wp_error( $created ) ) {
				return $created;
			}
			$pinned   = (int) $created['object_id'];
			$position = (int) $created['position'];
		} elseif ( 'remove' === $operation ) {
			$removed = $this->apply_remove( $change );
			if ( is_wp_error( $removed ) ) {
				return $removed;
			}
		} elseif ( 'move' === $operation ) {
			$moved = $this->apply_move( $change );
			if ( is_wp_error( $moved ) ) {
				return $moved;
			}
			$pinned   = (int) $moved['object_id'];
			$position = (int) $moved['position'];
		} else {
			return ErrorFactory::make( 'AP_UNSUPPORTED_NAVIGATION' );
		}

		$this->reorder( $menu_id, $pinned, $position );

		$snapshot = $this->adapter->snapshot( $location );
		if ( is_wp_error( $snapshot ) ) {
			return $snapshot;
		}

		return array(
			'object_id'  => $pinned,
			'after'      => $snapshot['items'],
			'state_hash' => $snapshot['state_hash'],
		);
	}

	/**
	 * Create the staged item and return its real ID and target position.
	 *
	 * @param int                  $menu_id Menu ID.
	 * @param array<string, mixed> $change  Change row.
	 * @return array<string, int>|\WP_Error
	 */
	private function apply_add( $menu_id, $change ) {
		$after      = is_array( $change['after_json'] ) ? $change['after_json'] : array();
		$before     = is_array( $change['before_json'] ) ? $change['before_json'] : array();
		$before_ids = array();
		foreach ( $before as $item ) {
			$before_ids[] = isset( $item['item_id'] ) ? (int) $item['item_id'] : 0;
		}
		$target = null;
		foreach ( $after as $item ) {
			if ( isset( $item['item_id'] ) && ! in_array( (int) $item['item_id'], $before_ids, true ) ) {
				$target = $item;
				break;
			}
		}
		if ( null === $target ) {
			return ErrorFactory::make( 'AP_INTERNAL_ERROR' );
		}
		$id = $this->create_item( $menu_id, $target );
		if ( is_wp_error( $id ) ) {
			return $id;
		}
		return array(
			'object_id' => (int) $id,
			'position'  => isset( $target['position'] ) ? (int) $target['position'] : 1,
		);
	}

	/**
	 * Remove the staged item.
	 *
	 * @param array<string, mixed> $change Change row.
	 * @return int|\WP_Error
	 */
	private function apply_remove( $change ) {
		$item_id = isset( $change['object_id'] ) ? (int) $change['object_id'] : 0;
		if ( $item_id < 1 ) {
			return ErrorFactory::make( 'AP_NAVIGATION_NOT_FOUND' );
		}
		$deleted = wp_delete_post( $item_id, true );
		if ( false === $deleted ) {
			return ErrorFactory::make( 'AP_NAVIGATION_NOT_FOUND' );
		}
		return $item_id;
	}

	/**
	 * Move the staged item to its new parent and position.
	 *
	 * @param array<string, mixed> $change Change row.
	 * @return array<string, int>|\WP_Error
	 */
	private function apply_move( $change ) {
		$item_id = isset( $change['object_id'] ) ? (int) $change['object_id'] : 0;
		if ( $item_id < 1 ) {
			return ErrorFactory::make( 'AP_NAVIGATION_NOT_FOUND' );
		}
		$target = $this->find_after_item( $change, $item_id );
		if ( null === $target ) {
			return ErrorFactory::make( 'AP_NAVIGATION_NOT_FOUND' );
		}
		update_post_meta(
			$item_id,
			'_menu_item_menu_item_parent',
			isset( $target['parent_item_id'] ) ? (int) $target['parent_item_id'] : 0
		);
		return array(
			'object_id' => $item_id,
			'position'  => isset( $target['position'] ) ? (int) $target['position'] : 1,
		);
	}

	/**
	 * Create one menu item from a normalized after item.
	 *
	 * @param int                  $menu_id Menu ID.
	 * @param array<string, mixed> $item    Normalized item.
	 * @return int|\WP_Error
	 */
	private function create_item( $menu_id, $item ) {
		$data = array(
			'menu-item-title'     => isset( $item['label'] ) ? (string) $item['label'] : '',
			'menu-item-position'  => isset( $item['position'] ) ? (int) $item['position'] : 0,
			'menu-item-parent-id' => isset( $item['parent_item_id'] ) ? (int) $item['parent_item_id'] : 0,
			'menu-item-status'    => 'publish',
		);
		if ( 'custom' === $item['type'] ) {
			$data['menu-item-type'] = 'custom';
			$data['menu-item-url']  = isset( $item['url'] ) ? (string) $item['url'] : '';
		} else {
			$data['menu-item-type']      = 'post_type';
			$data['menu-item-object']    = isset( $item['object'] ) ? (string) $item['object'] : 'page';
			$data['menu-item-object-id'] = isset( $item['object_id'] ) ? (int) $item['object_id'] : 0;
		}
		$item_id = wp_update_nav_menu_item( $menu_id, 0, $data );
		if ( is_wp_error( $item_id ) ) {
			return ErrorFactory::make( 'AP_INTERNAL_ERROR' );
		}
		return (int) $item_id;
	}

	/**
	 * Assign contiguous menu order, inserting the pinned item at its target slot.
	 *
	 * @param int $menu_id  Menu ID.
	 * @param int $pinned   Item ID to place at the target position, or 0.
	 * @param int $position Target 1-based position, or 1.
	 * @return void
	 */
	private function reorder( $menu_id, $pinned, $position ) {
		$items = wp_get_nav_menu_items(
			$menu_id,
			array(
				'post_status'            => 'publish',
				'update_post_term_cache' => false,
			)
		);
		if ( false === $items || ! is_array( $items ) ) {
			return;
		}
		$ordered = array();
		foreach ( $items as $item ) {
			if ( $pinned > 0 && (int) $item->ID === (int) $pinned ) {
				continue;
			}
			$ordered[] = (int) $item->ID;
		}
		if ( $pinned > 0 ) {
			$slot = min( max( (int) $position - 1, 0 ), count( $ordered ) );
			array_splice( $ordered, $slot, 0, array( (int) $pinned ) );
		}
		foreach ( $ordered as $index => $id ) {
			$this->set_menu_order( $id, $index + 1 );
		}
	}

	/**
	 * Set one menu item order without resetting its type or object mapping.
	 *
	 * @param int $item_id  Item ID.
	 * @param int $position Menu order.
	 * @return void
	 */
	private function set_menu_order( $item_id, $position ) {
		if ( $item_id < 1 ) {
			return;
		}
		wp_update_post(
			array(
				'ID'         => $item_id,
				'menu_order' => (int) $position,
			)
		);
	}

	/**
	 * Find the normalized after item for a target ID.
	 *
	 * @param array<string, mixed> $change  Change row.
	 * @param int                  $item_id Item ID.
	 * @return array<string, mixed>|null
	 */
	private function find_after_item( $change, $item_id ) {
		$after = is_array( $change['after_json'] ) ? $change['after_json'] : array();
		foreach ( $after as $item ) {
			if ( isset( $item['item_id'] ) && (int) $item['item_id'] === $item_id ) {
				return $item;
			}
		}
		return null;
	}

	/**
	 * Read the menu ID assigned to one location.
	 *
	 * @param string $location Registered location.
	 * @return int
	 */
	private function menu_id( $location ) {
		$locations = get_nav_menu_locations();
		return isset( $locations[ $location ] ) ? (int) $locations[ $location ] : 0;
	}

	/**
	 * Parse the navigation location from the change object type.
	 *
	 * @param array<string, mixed> $change Change row.
	 * @return string|\WP_Error
	 */
	private function location( $change ) {
		$object_type = isset( $change['object_type'] ) ? (string) $change['object_type'] : '';
		if ( 0 !== strpos( $object_type, 'nav_menu_item:' ) ) {
			return ErrorFactory::make( 'AP_UNSUPPORTED_NAVIGATION' );
		}
		$location = substr( $object_type, strlen( 'nav_menu_item:' ) );
		if ( '' === $location || 1 !== preg_match( '/^[a-z0-9_-]{1,100}$/', $location ) ) {
			return ErrorFactory::make( 'AP_UNSUPPORTED_NAVIGATION' );
		}
		return $location;
	}
}
