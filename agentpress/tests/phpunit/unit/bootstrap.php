<?php
/**
 * Unit test bootstrap.
 *
 * @package AgentPress
 */

require_once dirname( __DIR__, 3 ) . '/includes/Autoloader.php';

AgentPress\Autoloader::register();

if ( ! function_exists( '__' ) ) {
	function __( $text ) {
		return $text;
	}
}
if ( ! function_exists( 'esc_html' ) ) {
	function esc_html( $text ) {
		return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' );
	}
}
