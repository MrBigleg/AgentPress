<?php
/**
 * Context service unit tests.
 *
 * @package AgentPress
 */

use AgentPress\Context\ContextService;
use PHPUnit\Framework\TestCase;

/** Verifies the closed safe bootstrap mapper without WordPress globals. */
final class ContextServiceTest extends TestCase {
	/** @return void */
	public function test_returns_only_fixed_bounded_fields() {
		$effective = new class() {
			public function get() {
				$state = array( 'state' => 'automatic', 'reason' => '' );
				return array(
					'capabilities'  => array_fill_keys( array( 'read_site', 'read_content', 'create_post_draft', 'create_page_draft', 'edit_own_agentpress_draft', 'edit_other_draft', 'edit_published_content', 'publish_own_content', 'publish_others_content', 'list_terms', 'create_terms', 'assign_terms', 'read_navigation', 'modify_navigation', 'read_change_sets', 'read_activity' ), $state ),
					'blocked_areas' => array( 'users', 'plugins', 'themes', 'code', 'credentials', 'settings' ),
				);
			}
		};
		$user = (object) array(
			'ID'           => 14,
			'display_name' => '<b>Craig</b>' . str_repeat( 'x', 300 ),
			'roles'        => array( 'Administrator', 'unsafe role', 'administrator' ),
			'user_email'   => 'private@example.test',
			'allcaps'      => array( 'manage_options' => true ),
		);
		$site = array(
			'title'             => '<b>Example</b>',
			'home_url'          => 'https://example.test/',
			'language'          => 'en_US',
			'timezone'          => 'Asia/Bangkok',
			'wordpress_version' => '6.9',
			'path'              => 'C:\\private',
			'nonce'             => 'secret-nonce',
		);

		$result = ( new ContextService( $effective, static function () use ( $user ) { return $user; }, static function () { return true; }, static function () use ( $site ) { return $site; } ) )->execute();
		$json   = wp_json_encode( $result );

		$this->assertTrue( $result['ok'] );
		$this->assertSame( array( 'site', 'user', 'capabilities', 'blocked_areas' ), array_keys( $result['data'] ) );
		$this->assertSame( array( 'title', 'home_url', 'language', 'timezone', 'wordpress_version' ), array_keys( $result['data']['site'] ) );
		$this->assertSame( array( 'id', 'display_name', 'roles' ), array_keys( $result['data']['user'] ) );
		$this->assertSame( array( 'administrator', 'unsaferole' ), $result['data']['user']['roles'] );
		$this->assertLessThanOrEqual( 250, strlen( $result['data']['user']['display_name'] ) );
		$this->assertStringNotContainsString( 'private@example.test', $json );
		$this->assertStringNotContainsString( 'secret-nonce', $json );
		$this->assertCount( 16, $result['data']['capabilities'] );
	}

	/**
	 * @dataProvider denied_provider
	 * @param object $user     Candidate user.
	 * @param bool   $can_read Read authority.
	 * @return void
	 */
	public function test_denies_missing_identity_or_read( $user, $can_read ) {
		$service = new ContextService( new class() { public function get() { return array(); } }, static function () use ( $user ) { return $user; }, static function () use ( $can_read ) { return $can_read; }, static function () { return array(); } );
		$result  = $service->execute();

		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( 'AP_NOT_AUTHENTICATED', $result->get_error_code() );
	}

	/** @return array<string, array{object, bool}> */
	public function denied_provider() {
		return array(
			'logged out' => array( (object) array( 'ID' => 0 ), true ),
			'no read'    => array( (object) array( 'ID' => 4 ), false ),
		);
	}
}
