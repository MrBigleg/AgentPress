<?php
/**
 * Compatibility guard tests.
 *
 * @package AgentPress
 */

use AgentPress\Compatibility;
use PHPUnit\Framework\TestCase;

final class CompatibilityTest extends TestCase {
	/**
	 * @dataProvider supportedVersions
	 */
	public function test_supported_versions_boot( $wordpress_version, $php_version ) {
		$compatibility = new Compatibility( $wordpress_version, $php_version );

		$this->assertTrue( $compatibility->is_supported() );
	}

	public function supportedVersions() {
		return array(
			'minimums' => array( '6.9', '8.0' ),
			'patches'  => array( '6.9.3', '8.0.30' ),
			'future'   => array( '7.0', '8.4' ),
		);
	}

	/**
	 * @dataProvider unsupportedVersions
	 */
	public function test_unsupported_versions_fail_closed( $wordpress_version, $php_version, $expected_fragment ) {
		$compatibility = new Compatibility( $wordpress_version, $php_version );

		$this->assertFalse( $compatibility->is_supported() );
		$this->assertStringContainsString( $expected_fragment, $compatibility->get_message() );
	}

	public function unsupportedVersions() {
		return array(
			'wordpress' => array( '6.8.9', '8.0', 'WordPress 6.9' ),
			'php'       => array( '6.9', '7.4.33', 'PHP 8.0' ),
			'both'      => array( '6.8', '7.4', 'PHP 8.0' ),
		);
	}

	public function test_notice_renders_exactly_one_error_container() {
		$compatibility = new Compatibility( '6.8', '8.0' );

		ob_start();
		$compatibility->render_admin_notice();
		$output = ob_get_clean();

		$this->assertSame( 1, substr_count( $output, 'notice notice-error' ) );
		$this->assertSame( 1, substr_count( $output, '<p>' ) );
		$this->assertStringContainsString( 'AgentPress requires WordPress 6.9 or later.', $output );
	}
}
