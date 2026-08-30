<?php
/**
 * Runtime compatibility guard.
 *
 * @package AgentPress
 */

namespace AgentPress;

/**
 * Evaluates the supported WordPress and PHP runtime boundary.
 */
final class Compatibility {
	const MINIMUM_WORDPRESS = '6.9';
	const MINIMUM_PHP       = '8.0';

	/**
	 * Current WordPress version.
	 *
	 * @var string
	 */
	private $wordpress_version;

	/**
	 * Current PHP version.
	 *
	 * @var string
	 */
	private $php_version;

	/**
	 * Store the runtime versions to evaluate.
	 *
	 * @param string $wordpress_version Current WordPress version.
	 * @param string $php_version Current PHP version.
	 */
	public function __construct( $wordpress_version, $php_version ) {
		$this->wordpress_version = (string) $wordpress_version;
		$this->php_version       = (string) $php_version;
	}

	/**
	 * Whether both runtime requirements are satisfied.
	 *
	 * @return bool
	 */
	public function is_supported() {
		return version_compare( $this->wordpress_version, self::MINIMUM_WORDPRESS, '>=' )
			&& version_compare( $this->php_version, self::MINIMUM_PHP, '>=' );
	}

	/**
	 * Return one bounded explanation for the current incompatibility.
	 *
	 * @return string
	 */
	public function get_message() {
		if ( version_compare( $this->php_version, self::MINIMUM_PHP, '<' ) ) {
			return sprintf(
				/* translators: 1: required PHP version, 2: current PHP version. */
				__( 'AgentPress requires PHP %1$s or later. This site is running PHP %2$s, so AgentPress has not started.', 'agentpress' ),
				self::MINIMUM_PHP,
				$this->php_version
			);
		}

		return sprintf(
			/* translators: 1: required WordPress version, 2: current WordPress version. */
			__( 'AgentPress requires WordPress %1$s or later. This site is running WordPress %2$s, so AgentPress has not started.', 'agentpress' ),
			self::MINIMUM_WORDPRESS,
			$this->wordpress_version
		);
	}

	/**
	 * Render exactly one compatibility notice.
	 *
	 * @return void
	 */
	public function render_admin_notice() {
		printf(
			'<div class="notice notice-error"><p>%s</p></div>',
			esc_html( $this->get_message() )
		);
	}
}
