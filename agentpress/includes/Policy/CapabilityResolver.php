<?php
/**
 * Live WordPress capability resolution.
 *
 * @package AgentPress
 */

namespace AgentPress\Policy;

/**
 * Resolves current-user authority from registered object capability maps.
 */
final class CapabilityResolver {
	/**
	 * Current-user capability callback.
	 *
	 * @var callable
	 */
	private $can;

	/**
	 * Post-type object resolver.
	 *
	 * @var callable
	 */
	private $post_type_object;

	/**
	 * Taxonomy object resolver.
	 *
	 * @var callable
	 */
	private $taxonomy_object;

	/**
	 * Constructor.
	 *
	 * @param callable|null $can              Optional capability callback.
	 * @param callable|null $post_type_object Optional post-type resolver.
	 * @param callable|null $taxonomy_object  Optional taxonomy resolver.
	 */
	public function __construct( $can = null, $post_type_object = null, $taxonomy_object = null ) {
		$this->can              = $can ?? static function ( $capability, ...$args ) {
			return current_user_can( $capability, ...$args );
		};
		$this->post_type_object = $post_type_object ?? 'get_post_type_object';
		$this->taxonomy_object  = $taxonomy_object ?? 'get_taxonomy';
	}

	/**
	 * Check one exact current-user capability.
	 *
	 * @param string $capability Capability.
	 * @param mixed  ...$args    Optional object context.
	 * @return bool
	 */
	public function can( $capability, ...$args ) {
		return true === call_user_func_array( $this->can, array_merge( array( $capability ), $args ) );
	}

	/**
	 * Return whether the current user can read the site.
	 *
	 * @return bool
	 */
	public function can_read() {
		return $this->can( 'read' );
	}

	/**
	 * Return whether the current user can create this content type.
	 *
	 * @param string $post_type Post type.
	 * @return bool
	 */
	public function can_create( $post_type ) {
		return $this->can_post_type_capability( $post_type, 'create_posts' );
	}

	/**
	 * Return whether the current user can edit this content type.
	 *
	 * @param string $post_type Post type.
	 * @return bool
	 */
	public function can_edit_type( $post_type ) {
		return $this->can_post_type_capability( $post_type, 'edit_posts' );
	}

	/**
	 * Return whether the current user can edit others of this type.
	 *
	 * @param string $post_type Post type.
	 * @return bool
	 */
	public function can_edit_others( $post_type ) {
		return $this->can_post_type_capability( $post_type, 'edit_others_posts' );
	}

	/**
	 * Return whether the current user can edit published objects of this type.
	 *
	 * @param string $post_type Post type.
	 * @return bool
	 */
	public function can_edit_published( $post_type ) {
		return $this->can_post_type_capability( $post_type, 'edit_published_posts' );
	}

	/**
	 * Return whether the current user can publish this content type.
	 *
	 * @param string $post_type Post type.
	 * @return bool
	 */
	public function can_publish( $post_type ) {
		return $this->can_post_type_capability( $post_type, 'publish_posts' );
	}

	/**
	 * Return whether either fixed content type is editable.
	 *
	 * @return bool
	 */
	public function can_edit_any() {
		return $this->can_edit_type( 'post' ) || $this->can_edit_type( 'page' );
	}

	/**
	 * Return whether others' content is editable for either fixed type.
	 *
	 * @return bool
	 */
	public function can_edit_others_any() {
		return $this->can_edit_others( 'post' ) || $this->can_edit_others( 'page' );
	}

	/**
	 * Return whether published content is editable for either fixed type.
	 *
	 * @return bool
	 */
	public function can_edit_published_any() {
		return $this->can_edit_published( 'post' ) || $this->can_edit_published( 'page' );
	}

	/**
	 * Return whether either fixed content type is publishable.
	 *
	 * @return bool
	 */
	public function can_publish_any() {
		return $this->can_publish( 'post' ) || $this->can_publish( 'page' );
	}

	/**
	 * Return whether terms in the taxonomy are manageable.
	 *
	 * @param string $taxonomy Taxonomy.
	 * @return bool
	 */
	public function can_manage_terms( $taxonomy ) {
		return $this->can_taxonomy_capability( $taxonomy, 'manage_terms' );
	}

	/**
	 * Return whether terms in the taxonomy are assignable.
	 *
	 * @param string $taxonomy Taxonomy.
	 * @return bool
	 */
	public function can_assign_terms( $taxonomy ) {
		return $this->can_taxonomy_capability( $taxonomy, 'assign_terms' );
	}

	/**
	 * Return whether either fixed taxonomy is manageable.
	 *
	 * @return bool
	 */
	public function can_manage_any_terms() {
		return $this->can_manage_terms( 'category' ) || $this->can_manage_terms( 'post_tag' );
	}

	/**
	 * Return whether either fixed taxonomy is assignable.
	 *
	 * @return bool
	 */
	public function can_assign_any_terms() {
		return $this->can_assign_terms( 'category' ) || $this->can_assign_terms( 'post_tag' );
	}

	/**
	 * Return whether navigation can be managed.
	 *
	 * @return bool
	 */
	public function can_manage_navigation() {
		return $this->can( 'edit_theme_options' );
	}

	/**
	 * Resolve and check a fixed post/page primitive capability.
	 *
	 * @param string $post_type Post type.
	 * @param string $key       Capability property.
	 * @return bool
	 */
	private function can_post_type_capability( $post_type, $key ) {
		if ( ! in_array( $post_type, array( 'post', 'page' ), true ) ) {
			return false;
		}
		$object = call_user_func( $this->post_type_object, $post_type );
		if ( ! is_object( $object ) || ! isset( $object->cap->{$key} ) ) {
			return false;
		}

		return $this->can( $object->cap->{$key} );
	}

	/**
	 * Resolve and check a fixed category/tag capability.
	 *
	 * @param string $taxonomy Taxonomy.
	 * @param string $key      Capability property.
	 * @return bool
	 */
	private function can_taxonomy_capability( $taxonomy, $key ) {
		if ( ! in_array( $taxonomy, array( 'category', 'post_tag' ), true ) ) {
			return false;
		}
		$object = call_user_func( $this->taxonomy_object, $taxonomy );
		if ( ! is_object( $object ) || ! isset( $object->cap->{$key} ) ) {
			return false;
		}

		return $this->can( $object->cap->{$key} );
	}
}
