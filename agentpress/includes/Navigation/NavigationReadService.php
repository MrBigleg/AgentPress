<?php
/**
 * Permission-aware navigation read service.
 *
 * @package AgentPress
 */

namespace AgentPress\Navigation;

use AgentPress\Errors\ErrorFactory;
use AgentPress\Results\ResultFactory;

/** Implements the exact get-navigation read contract. */
final class NavigationReadService {
	/**
	 * Classic navigation adapter.
	 *
	 * @var ClassicMenuAdapter
	 */
	private $adapter;

	/**
	 * Construct the navigation read service.
	 *
	 * @param ClassicMenuAdapter|null $adapter Optional classic-menu adapter.
	 */
	public function __construct( $adapter = null ) {
		$this->adapter = $adapter ?? new ClassicMenuAdapter();
	}

	/**
	 * Return one registered classic-menu location.
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

		$location = isset( $input['location'] ) ? (string) $input['location'] : 'primary';
		$snapshot = $this->adapter->snapshot( $location );
		if ( is_wp_error( $snapshot ) ) {
			return $snapshot;
		}

		return ResultFactory::success( $snapshot );
	}
}
