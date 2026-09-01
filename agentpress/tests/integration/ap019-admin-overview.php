<?php
/**
 * AP-019 wp-admin shell runtime matrix.
 *
 * @package AgentPress
 */

use AgentPress\Admin\AdminPage;
use AgentPress\Context\ContextService;
use AgentPress\Rest\WebMCPRoutes;

/** @param bool $condition Condition. @param string $message Message. @return void */
function agentpress_ap019_assert( $condition, $message ) {
	if ( ! $condition ) {
		fwrite( STDERR, "AP-019 assertion failed: {$message}\n" );
		exit( 1 );
	}
}

/** @param string $role Role. @return int */
function agentpress_ap019_user( $role ) {
	$suffix  = strtolower( wp_generate_password( 8, false ) );
	$user_id = wp_create_user( 'ap019-' . $role . '-' . $suffix, wp_generate_password( 24 ), 'ap019-' . $role . '-' . $suffix . '@example.test' );
	agentpress_ap019_assert( ! is_wp_error( $user_id ), 'Could not create ' . $role . ' user.' );
	( new WP_User( $user_id ) )->set_role( $role );
	return (int) $user_id;
}

$users = array(
	'administrator' => agentpress_ap019_user( 'administrator' ),
	'author'        => agentpress_ap019_user( 'author' ),
	'subscriber'    => agentpress_ap019_user( 'subscriber' ),
);
$page   = new AdminPage();
$routes = new WebMCPRoutes();
$counts = array();

foreach ( $users as $role => $user_id ) {
	wp_set_current_user( $user_id );
	agentpress_ap019_assert( current_user_can( 'read' ), $role . ' cannot access the page.' );
	$context = ( new ContextService() )->execute();
	$tools   = $routes->default_definitions();
	agentpress_ap019_assert( is_array( $context ) && true === $context['ok'], $role . ' context failed.' );
	agentpress_ap019_assert( 6 === count( $context['data']['blocked_areas'] ), $role . ' blocked boundary mismatch.' );
	$counts[ $role ] = count( $tools );
}
agentpress_ap019_assert( $counts['administrator'] >= $counts['author'] && $counts['author'] >= $counts['subscriber'], 'Tool counts are not capability-ordered.' );

wp_set_current_user( $users['administrator'] );
$page->enqueue_assets( 'dashboard_page' );
agentpress_ap019_assert( ! wp_style_is( 'agentpress-admin', 'enqueued' ), 'Style leaked to another admin screen.' );
$page->enqueue_assets( AdminPage::HOOK_SUFFIX );
agentpress_ap019_assert( wp_style_is( 'agentpress-admin', 'enqueued' ), 'Style did not enqueue on AgentPress.' );
$modules = wp_script_modules()->get_queue();
agentpress_ap019_assert( in_array( 'agentpress-admin', $modules, true ), 'Script module did not enqueue on AgentPress.' );

ob_start();
$page->render();
$html = ob_get_clean();
agentpress_ap019_assert( false !== strpos( $html, 'id="agentpress-admin"' ) && false !== strpos( $html, 'id="agentpress-admin-settings"' ), 'Application root/settings missing.' );
agentpress_ap019_assert( false !== strpos( $html, '"context":{"ok":true' ), 'Live context was not bootstrapped into the page.' );
agentpress_ap019_assert( false === stripos( $html, 'chatbot' ), 'Chatbot language reached the shell.' );
agentpress_ap019_assert( false === strpos( $html, wp_get_current_user()->user_email ), 'Private email leaked into shell.' );

wp_set_current_user( 0 );
agentpress_ap019_assert( ! current_user_can( 'read' ), 'Logged-out page capability passed.' );

foreach ( $users as $user_id ) {
	wp_delete_user( $user_id );
}

echo wp_json_encode( array( 'roles' => 3, 'tool_counts' => $counts, 'blocked_areas' => 6, 'offscreen_asset_leaks' => 0, 'page_assets' => 2, 'private_email_leaks' => 0, 'chatbots' => 0, 'logged_out_denied' => true ) ) . "\n";
