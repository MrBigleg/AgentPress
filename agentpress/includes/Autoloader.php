<?php
/**
 * AgentPress namespace autoloader.
 *
 * @package AgentPress
 */

namespace AgentPress;

/**
 * Loads classes in the AgentPress namespace from the includes directory.
 */
final class Autoloader {
	/**
	 * Register the project autoloader.
	 *
	 * @return void
	 */
	public static function register() {
		spl_autoload_register( array( __CLASS__, 'autoload' ) );
	}

	/**
	 * Load an AgentPress class from the includes directory.
	 *
	 * @param string $class Fully qualified class name.
	 * @return void
	 */
	public static function autoload( $class ) {
		$prefix = __NAMESPACE__ . '\\';

		if ( 0 !== strpos( $class, $prefix ) ) {
			return;
		}

		$relative_class = substr( $class, strlen( $prefix ) );
		$relative_path  = str_replace( '\\', DIRECTORY_SEPARATOR, $relative_class ) . '.php';
		$file           = __DIR__ . DIRECTORY_SEPARATOR . $relative_path;

		if ( is_readable( $file ) ) {
			require_once $file;
		}
	}
}
