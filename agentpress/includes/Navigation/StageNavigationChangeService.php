<?php
/**
 * Permission-aware navigation staging service.
 *
 * @package AgentPress
 */

namespace AgentPress\Navigation;

use AgentPress\Changes\ChangeCoordinator;
use AgentPress\Changes\StateHasher;
use AgentPress\Errors\ErrorFactory;
use AgentPress\Results\ResultFactory;

/**
 * Stages one add, remove, or move against a classic menu as an immutable R2 proposal.
 */
final class StageNavigationChangeService {
	/**
	 * Change Set coordinator.
	 *
	 * @var ChangeCoordinator|object
	 */
	private $coordinator;

	/**
	 * Classic-menu adapter.
	 *
	 * @var ClassicMenuAdapter
	 */
	private $adapter;

	/**
	 * Canonical state hasher.
	 *
	 * @var StateHasher
	 */
	private $hasher;

	/**
	 * Construct the navigation staging service.
	 *
	 * @param ChangeCoordinator|object|null $coordinator Optional Change Set coordinator.
	 * @param ClassicMenuAdapter|null       $adapter     Optional classic-menu adapter.
	 * @param StateHasher|null              $hasher      Optional canonical state hasher.
	 */
	public function __construct( $coordinator = null, $adapter = null, $hasher = null ) {
		$this->coordinator = $coordinator ?? new ChangeCoordinator();
		$this->adapter     = $adapter ?? new ClassicMenuAdapter();
		$this->hasher      = $hasher ?? new StateHasher();
	}

	/**
	 * Stage one navigation operation without mutating live navigation.
	 *
	 * @param array<string, mixed> $input Validated Ability input.
	 * @return array<string, mixed>|\WP_Error
	 */
	public function execute( $input ) {
		if ( ! current_user_can( 'read' ) ) {
			return ErrorFactory::make( 'AP_NOT_AUTHENTICATED' );
		}
		if ( ! current_user_can( 'edit_theme_options' ) ) {
			return ErrorFactory::make( 'AP_PERMISSION_DENIED' );
		}

		$validated = $this->validate( $input );
		if ( is_wp_error( $validated ) ) {
			return $validated;
		}

		$location  = isset( $input['location'] ) ? (string) $input['location'] : 'primary';
		$operation = (string) $input['operation'];
		$item      = $input['item'];

		$snapshot = $this->adapter->snapshot( $location );
		if ( is_wp_error( $snapshot ) ) {
			return $snapshot;
		}
		$before = $snapshot['items'];

		$projected = $this->project( $operation, $before, $item );
		if ( is_wp_error( $projected ) ) {
			return $projected;
		}
		$after = $projected;

		$command = array(
			'actor_user_id'   => (int) get_current_user_id(),
			'ability'         => 'agentpress/stage-navigation-change',
			'operation'       => $operation,
			'object_type'     => 'nav_menu_item:' . $location,
			'object_id'       => 'remove' === $operation || 'move' === $operation ? max( 0, (int) $item['item_id'] ) : 0,
			'idempotency_key' => (string) $input['idempotency_key'],
			'before'          => $before,
			'after'           => $after,
		);
		if ( isset( $input['change_set_id'] ) ) {
			$command['change_set_id'] = (int) $input['change_set_id'];
		}

		$coordinated = $this->coordinator->stage( $command );
		if ( is_wp_error( $coordinated ) ) {
			return $coordinated;
		}

		$preview_hash = $this->hasher->state_hash(
			array(
				'adapter'   => 'classic-menu',
				'location'  => $location,
				'menu_id'   => (int) $snapshot['menu_id'],
				'menu_name' => (string) $snapshot['menu_name'],
				'items'     => $after,
			)
		);

		return ResultFactory::success(
			array(
				'status'         => 'PENDING_APPROVAL',
				'adapter'        => 'classic-menu',
				'location'       => $location,
				'operation'      => $operation,
				'before'         => $before,
				'after'          => $after,
				'state_hash'     => $preview_hash,
				'change_set_id'  => (int) $coordinated['change_set_id'],
				'change_set_ref' => 'AP-' . (int) $coordinated['change_set_id'],
				'change_id'      => (int) $coordinated['change_id'],
				'replayed'       => ! empty( $coordinated['replayed'] ),
				'expires_at'     => isset( $coordinated['expires_at'] ) ? (string) $coordinated['expires_at'] : '',
			)
		);
	}

	/**
	 * Validate the direct-service closed boundary.
	 *
	 * @param array<string, mixed> $input Input.
	 * @return true|\WP_Error
	 */
	private function validate( $input ) {
		$allowed = array( 'location', 'operation', 'item', 'change_set_id', 'idempotency_key' );
		if ( ! is_array( $input ) || array_diff( array_keys( $input ), $allowed ) || ! isset( $input['operation'], $input['item'], $input['idempotency_key'] ) ) {
			return ErrorFactory::make( 'AP_SCHEMA_INVALID' );
		}
		if ( ! is_string( $input['operation'] ) || ! in_array( $input['operation'], array( 'add', 'remove', 'move' ), true ) ) {
			return ErrorFactory::make( 'AP_SCHEMA_INVALID' );
		}
		if ( isset( $input['location'] ) && ( ! is_string( $input['location'] ) || 1 !== preg_match( '/^[a-z0-9_-]{1,100}$/', $input['location'] ) ) ) {
			return ErrorFactory::make( 'AP_SCHEMA_INVALID' );
		}
		if ( isset( $input['location'] ) && '' === $input['location'] ) {
			return ErrorFactory::make( 'AP_SCHEMA_INVALID' );
		}
		if ( ! is_array( $input['item'] ) || empty( $input['item'] ) ) {
			return ErrorFactory::make( 'AP_SCHEMA_INVALID' );
		}
		if ( isset( $input['change_set_id'] ) && ( ! is_int( $input['change_set_id'] ) || $input['change_set_id'] <= 0 ) ) {
			return ErrorFactory::make( 'AP_SCHEMA_INVALID' );
		}
		if ( ! is_string( $input['idempotency_key'] ) || 1 !== preg_match( '/^[A-Za-z0-9._:-]{8,64}$/', $input['idempotency_key'] ) ) {
			return ErrorFactory::make( 'AP_SCHEMA_INVALID' );
		}
		return true;
	}

	/**
	 * Validate operation-specific fields and compute the proposed after state.
	 *
	 * @param string               $operation Operation.
	 * @param array<int, mixed>    $before    Current items.
	 * @param array<string, mixed> $item      Item input.
	 * @return array<int, mixed>|\WP_Error
	 */
	private function project( $operation, $before, $item ) {
		if ( 'add' === $operation ) {
			return $this->project_add( $before, $item );
		}
		if ( 'remove' === $operation ) {
			return $this->project_remove( $before, $item );
		}
		if ( 'move' === $operation ) {
			return $this->project_move( $before, $item );
		}
		return ErrorFactory::make( 'AP_SCHEMA_INVALID' );
	}

	/**
	 * Build the after state for an add operation.
	 *
	 * @param array<int, mixed>    $before Current items.
	 * @param array<string, mixed> $item   Item input.
	 * @return array<int, mixed>|\WP_Error
	 */
	private function project_add( $before, $item ) {
		if ( ! isset( $item['object_type'] ) || ! in_array( $item['object_type'], array( 'post', 'page', 'custom' ), true ) || ! isset( $item['position'] ) || ! is_int( $item['position'] ) || $item['position'] < 1 ) {
			return ErrorFactory::make( 'AP_SCHEMA_INVALID' );
		}
		$parent = isset( $item['parent_item_id'] ) ? max( 0, (int) $item['parent_item_id'] ) : 0;

		$type      = 'custom';
		$object    = 'custom';
		$url       = '';
		$label     = '';
		$object_id = 0;

		if ( 'custom' === $item['object_type'] ) {
			if ( ! isset( $item['url'] ) || ! is_string( $item['url'] ) || 1 !== preg_match( '/^https:\/\//', $item['url'] ) ) {
				return ErrorFactory::make( 'AP_SCHEMA_INVALID' );
			}
			if ( ! $this->is_same_origin( $item['url'] ) ) {
				return ErrorFactory::make( 'AP_POLICY_BLOCKED' );
			}
			if ( ! isset( $item['label'] ) || ! is_string( $item['label'] ) || 0 === strlen( trim( $item['label'] ) ) || strlen( $item['label'] ) > 200 ) {
				return ErrorFactory::make( 'AP_SCHEMA_INVALID' );
			}
			$type      = 'custom';
			$object    = 'custom';
			$url       = esc_url_raw( $item['url'] );
			$label     = sanitize_text_field( $item['label'] );
			$object_id = isset( $item['object_id'] ) ? max( 0, (int) $item['object_id'] ) : 0;
		} else {
			if ( ! isset( $item['object_id'] ) || ! is_int( $item['object_id'] ) || $item['object_id'] <= 0 ) {
				return ErrorFactory::make( 'AP_SCHEMA_INVALID' );
			}
			$post = get_post( $item['object_id'] );
			if ( ! is_object( $post ) || (string) $post->post_type !== $item['object_type'] ) {
				return ErrorFactory::make( 'AP_CONTENT_NOT_FOUND' );
			}
			if ( ! current_user_can( 'read_post', (int) $item['object_id'] ) ) {
				return ErrorFactory::make( 'AP_PERMISSION_DENIED' );
			}
			$permalink = get_permalink( (int) $item['object_id'] );
			if ( false === $permalink || ! is_string( $permalink ) ) {
				return ErrorFactory::make( 'AP_INTERNAL_ERROR' );
			}
			$type      = 'post_type';
			$object    = (string) $item['object_type'];
			$object_id = (int) $item['object_id'];
			$url       = esc_url_raw( $permalink );
			$label     = isset( $item['label'] ) && is_string( $item['label'] ) && '' !== $item['label'] ? sanitize_text_field( $item['label'] ) : sanitize_text_field( (string) $post->post_title );
		}

		$new_item = array(
			'item_id'        => $this->next_item_id( $before ),
			'parent_item_id' => $parent,
			'position'       => (int) $item['position'],
			'label'          => $label,
			'type'           => $type,
			'object'         => $object,
			'object_id'      => $object_id,
			'url'            => $url,
		);

		$after = $before;
		$slot  = min( max( (int) $item['position'] - 1, 0 ), count( $after ) );
		array_splice( $after, $slot, 0, array( $new_item ) );
		return $this->ordered( $after );
	}

	/**
	 * Build the after state for a remove operation.
	 *
	 * @param array<int, mixed>    $before Current items.
	 * @param array<string, mixed> $item   Item input.
	 * @return array<int, mixed>|\WP_Error
	 */
	private function project_remove( $before, $item ) {
		$item_id = isset( $item['item_id'] ) ? (int) $item['item_id'] : 0;
		if ( $item_id < 1 ) {
			return ErrorFactory::make( 'AP_SCHEMA_INVALID' );
		}
		$index = $this->index_of( $before, $item_id );
		if ( null === $index ) {
			return ErrorFactory::make( 'AP_NAVIGATION_NOT_FOUND' );
		}
		foreach ( $before as $candidate ) {
			if ( isset( $candidate['parent_item_id'] ) && (int) $candidate['parent_item_id'] === $item_id ) {
				return ErrorFactory::make( 'AP_UNSUPPORTED_NAVIGATION' );
			}
		}
		$after = $before;
		array_splice( $after, $index, 1 );
		return $this->ordered( $after );
	}

	/**
	 * Build the after state for a move operation.
	 *
	 * @param array<int, mixed>    $before Current items.
	 * @param array<string, mixed> $item   Item input.
	 * @return array<int, mixed>|\WP_Error
	 */
	private function project_move( $before, $item ) {
		$item_id = isset( $item['item_id'] ) ? (int) $item['item_id'] : 0;
		if ( $item_id < 1 || ! isset( $item['position'] ) || ! is_int( $item['position'] ) || $item['position'] < 1 ) {
			return ErrorFactory::make( 'AP_SCHEMA_INVALID' );
		}
		$index = $this->index_of( $before, $item_id );
		if ( null === $index ) {
			return ErrorFactory::make( 'AP_NAVIGATION_NOT_FOUND' );
		}
		$moving                   = $before[ $index ];
		$moving['parent_item_id'] = isset( $item['parent_item_id'] ) ? max( 0, (int) $item['parent_item_id'] ) : 0;
		$moving['position']       = (int) $item['position'];

		$after = $before;
		array_splice( $after, $index, 1 );
		$slot = min( max( (int) $item['position'] - 1, 0 ), count( $after ) );
		array_splice( $after, $slot, 0, array( $moving ) );
		return $this->ordered( $after );
	}

	/**
	 * Renumber contiguous menu order from the current array order.
	 *
	 * @param array<int, mixed> $items Items.
	 * @return array<int, mixed>
	 */
	private function ordered( $items ) {
		foreach ( $items as $index => $item ) {
			$items[ $index ]['position'] = $index + 1;
		}
		return $items;
	}

	/**
	 * Locate the flat index of one item ID.
	 *
	 * @param array<int, mixed> $items Items.
	 * @param int               $item_id Item ID.
	 * @return int|null
	 */
	private function index_of( $items, $item_id ) {
		foreach ( $items as $index => $item ) {
			if ( isset( $item['item_id'] ) && (int) $item['item_id'] === $item_id ) {
				return $index;
			}
		}
		return null;
	}

	/**
	 * Compute a provisional positive ID for a not-yet-created item.
	 *
	 * @param array<int, mixed> $items Current items.
	 * @return int
	 */
	private function next_item_id( $items ) {
		$max = 0;
		foreach ( $items as $item ) {
			if ( isset( $item['item_id'] ) ) {
				$max = max( $max, (int) $item['item_id'] );
			}
		}
		return $max + 1;
	}

	/**
	 * Enforce the same-origin rule for custom absolute HTTPS URLs.
	 *
	 * @param string $url Absolute URL.
	 * @return bool
	 */
	private function is_same_origin( $url ) {
		$source = wp_parse_url( $url );
		$target = wp_parse_url( home_url( '/' ) );
		if ( ! is_array( $source ) || ! is_array( $target ) ) {
			return false;
		}
		$scheme = isset( $source['scheme'] ) ? strtolower( (string) $source['scheme'] ) : '';
		$host   = isset( $source['host'] ) ? strtolower( (string) $source['host'] ) : '';
		$port   = isset( $source['port'] ) ? (int) $source['port'] : ( 'https' === $scheme ? 443 : 80 );
		if ( 'https' !== $scheme || '' === $host ) {
			return false;
		}
		$target_scheme = isset( $target['scheme'] ) ? strtolower( (string) $target['scheme'] ) : '';
		$target_host   = isset( $target['host'] ) ? strtolower( (string) $target['host'] ) : '';
		$target_port   = isset( $target['port'] ) ? (int) $target['port'] : ( 'https' === $target_scheme ? 443 : 80 );

		return $scheme === $target_scheme && $host === $target_host && $port === $target_port;
	}
}
