<?php
/**
 * WordPress Ability lifecycle registrar.
 *
 * @package AgentPress
 */

namespace AgentPress\Abilities;

use AgentPress\Content\ContentReadService;
use AgentPress\Content\DraftCreationService;
use AgentPress\Context\ContextService;
use AgentPress\Context\SiteStructureService;
use AgentPress\Errors\ErrorFactory;
use AgentPress\Navigation\NavigationReadService;
use AgentPress\Policy\ExecutionPolicy;
use AgentPress\Terms\TermReadService;
use AgentPress\Terms\TermAssignmentService;

/**
 * Registers the fixed category and catalog at the WordPress 6.9 lifecycle hooks.
 */
final class AbilityRegistrar {
	/**
	 * Exact execution policy.
	 *
	 * @var ExecutionPolicy
	 */
	private $policy;

	/**
	 * Narrow service dispatcher.
	 *
	 * @var callable
	 */
	private $executor;

	/**
	 * Constructor.
	 *
	 * @param ExecutionPolicy|null $policy   Optional exact execution policy.
	 * @param callable|null        $executor Optional service dispatcher.
	 */
	public function __construct( $policy = null, $executor = null ) {
		$this->policy = $policy ?? new ExecutionPolicy();
		if ( null === $executor ) {
			$context    = new ContextService();
			$structure  = new SiteStructureService();
			$content    = new ContentReadService();
			$drafts     = new DraftCreationService();
			$navigation = new NavigationReadService();
			$assignment = new TermAssignmentService();
			$terms      = new TermReadService();
			$executor   = static function ( $ability, $input ) use ( $context, $structure, $content, $drafts, $navigation, $assignment, $terms ) {
				if ( 'agentpress/get-context' === $ability ) {
					return $context->execute();
				}
				if ( 'agentpress/get-site-structure' === $ability ) {
					return $structure->execute();
				}
				if ( 'agentpress/list-content' === $ability ) {
					return $content->list_content( $input );
				}
				if ( 'agentpress/get-content' === $ability ) {
					return $content->get_content( $input );
				}
				if ( 'agentpress/create-draft' === $ability ) {
					return $drafts->execute( $input );
				}
				if ( 'agentpress/list-terms' === $ability ) {
					return $terms->execute( $input );
				}
				if ( 'agentpress/get-navigation' === $ability ) {
					return $navigation->execute( $input );
				}
				if ( 'agentpress/assign-terms' === $ability ) {
					return $assignment->execute( $input );
				}
				return ErrorFactory::make( 'AP_INTERNAL_ERROR' );
			};
		}
		$this->executor = $executor;
	}

	/**
	 * Register lifecycle hooks.
	 *
	 * @return void
	 */
	public function register_hooks() {
		add_action( 'wp_abilities_api_categories_init', array( $this, 'register_category' ) );
		add_action( 'wp_abilities_api_init', array( $this, 'register_abilities' ) );
	}

	/**
	 * Register the one fixed category.
	 *
	 * @return void
	 */
	public function register_category() {
		wp_register_ability_category( AbilityCatalog::CATEGORY, AbilityCatalog::category() );
	}

	/**
	 * Register exactly the 15 fixed Abilities.
	 *
	 * @return void
	 */
	public function register_abilities() {
		foreach ( AbilityCatalog::all() as $ability_name => $definition ) {
			$args                        = $definition;
			$args['category']            = AbilityCatalog::CATEGORY;
			$args['permission_callback'] = function ( $input = null ) use ( $ability_name ) {
				$context = is_array( $input ) ? $input : array();
				$result  = $this->policy->evaluate( $ability_name, $context );
				return is_wp_error( $result ) ? $result : true;
			};
			$args['execute_callback']    = function ( $input = null ) use ( $ability_name ) {
				return call_user_func( $this->executor, $ability_name, is_array( $input ) ? $input : array() );
			};
			wp_register_ability( $ability_name, $args );
		}
	}
}
