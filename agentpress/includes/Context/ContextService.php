<?php
/**
 * Safe AgentPress bootstrap context service.
 *
 * @package AgentPress
 */

namespace AgentPress\Context;

use AgentPress\Errors\ErrorFactory;
use AgentPress\Policy\CapabilityEnvelope;
use AgentPress\Results\ResultFactory;

/** Builds the fixed safe bootstrap snapshot. */
final class ContextService {
	/**
	 * Effective operation envelope.
	 *
	 * @var CapabilityEnvelope|object
	 */
	private $envelope;
	/**
	 * Current-user resolver.
	 *
	 * @var callable
	 */
	private $current_user;
	/**
	 * Read-authority resolver.
	 *
	 * @var callable
	 */
	private $can_read;
	/**
	 * Fixed safe-site resolver.
	 *
	 * @var callable
	 */
	private $site;

	/**
	 * Constructor.
	 *
	 * @param CapabilityEnvelope|object|null $envelope     Optional operation envelope.
	 * @param callable|null                  $current_user Optional current-user resolver.
	 * @param callable|null                  $can_read     Optional read-capability resolver.
	 * @param callable|null                  $site         Optional safe site resolver.
	 */
	public function __construct( $envelope = null, $current_user = null, $can_read = null, $site = null ) {
		$this->envelope     = $envelope ?? new CapabilityEnvelope();
		$this->current_user = $current_user ?? 'wp_get_current_user';
		$this->can_read     = $can_read ?? static function () {
			return current_user_can( 'read' );
		};
		$this->site         = $site ?? array( self::class, 'resolve_site' );
	}

	/**
	 * Return one successful safe bootstrap envelope.
	 *
	 * @return array<string, mixed>|\WP_Error
	 */
	public function execute() {
		$user = call_user_func( $this->current_user );
		if ( ! is_object( $user ) || empty( $user->ID ) || true !== call_user_func( $this->can_read ) ) {
			return ErrorFactory::make( 'AP_NOT_AUTHENTICATED' );
		}
		$site      = call_user_func( $this->site );
		$effective = $this->envelope->get();
		if ( ! is_array( $site ) || ! is_array( $effective ) || ! isset( $effective['capabilities'], $effective['blocked_areas'] ) ) {
			return ErrorFactory::make( 'AP_INTERNAL_ERROR' );
		}
		return ResultFactory::success(
			array(
				'site'          => $this->normalize_site( $site ),
				'user'          => array(
					'id'           => (int) $user->ID,
					'display_name' => $this->bounded_text( $user->display_name ?? '', 250 ),
					'roles'        => $this->normalize_roles( is_array( $user->roles ?? null ) ? $user->roles : array() ),
				),
				'capabilities'  => $effective['capabilities'],
				'blocked_areas' => $effective['blocked_areas'],
			)
		);
	}

	/**
	 * Resolve only the five fixed safe site fields.
	 *
	 * @return array<string, string>
	 */
	public static function resolve_site() {
		return array(
			'title'             => get_bloginfo( 'name' ),
			'home_url'          => home_url( '/' ),
			'language'          => determine_locale(),
			'timezone'          => wp_timezone_string(),
			'wordpress_version' => get_bloginfo( 'version' ),
		);
	}

	/**
	 * Normalize the fixed site shape.
	 *
	 * @param array<string, mixed> $site Candidate site.
	 * @return array<string, string>
	 */
	private function normalize_site( $site ) {
		return array(
			'title'             => $this->bounded_text( $site['title'] ?? '', 200 ),
			'home_url'          => substr( (string) ( $site['home_url'] ?? '' ), 0, 2048 ),
			'language'          => $this->bounded_text( $site['language'] ?? '', 20 ),
			'timezone'          => $this->bounded_text( $site['timezone'] ?? '', 64 ),
			'wordpress_version' => $this->bounded_text( $site['wordpress_version'] ?? '', 20 ),
		);
	}

	/**
	 * Normalize role slugs without reading raw capabilities.
	 *
	 * @param array<int, mixed> $roles Candidate roles.
	 * @return array<int, string>
	 */
	private function normalize_roles( $roles ) {
		$normalized = array();
		foreach ( array_slice( $roles, 0, 20 ) as $role ) {
			$role = preg_replace( '/[^a-z0-9_-]/', '', strtolower( (string) $role ) );
			if ( is_string( $role ) && '' !== $role && strlen( $role ) <= 64 ) {
				$normalized[] = $role;
			}
		}
		return array_values( array_unique( $normalized ) );
	}

	/**
	 * Strip unsafe markup/control bytes and bound text.
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
