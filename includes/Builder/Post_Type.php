<?php
/**
 * Builder template post type.
 *
 * @package DecentCore
 */

namespace DecentCore\Builder;

defined( 'ABSPATH' ) || exit;

/**
 * Registers decent_template.
 *
 * Not public: a header is not a page and must never be reachable at a URL of
 * its own, indexed, or returned by a search. It is show_in_rest because
 * Elementor's editor talks to the REST API.
 */
final class Post_Type {

	/**
	 * Post type name.
	 */
	public const NAME = 'decent_template';

	/**
	 * Attaches hooks.
	 *
	 * @return void
	 */
	public function register(): void {
		add_action( 'init', array( $this, 'register_post_type' ) );
	}

	/**
	 * Registers the post type.
	 *
	 * @return void
	 */
	public function register_post_type(): void {
		register_post_type(
			self::NAME,
			array(
				'labels'              => array(
					'name'          => __( 'Templates', 'decent-core' ),
					'singular_name' => __( 'Template', 'decent-core' ),
					'add_new_item'  => __( 'Add template', 'decent-core' ),
					'edit_item'     => __( 'Edit template', 'decent-core' ),
					'search_items'  => __( 'Search templates', 'decent-core' ),
					'not_found'     => __( 'No templates yet.', 'decent-core' ),
				),
				'public'              => false,
				'show_ui'             => true,
				'show_in_menu'        => 'decent-core',
				'show_in_rest'        => true,
				'rewrite'             => false,
				'query_var'           => false,
				'exclude_from_search' => true,
				'publicly_queryable'  => false,
				'has_archive'         => false,
				'hierarchical'        => false,
				'supports'            => array( 'title', 'editor', 'revisions', 'author', 'elementor' ),
				'capability_type'     => 'post',
				'map_meta_cap'        => true,
				'capabilities'        => array(
					// Editing a header changes every page on the site, so it
					// sits behind the same capability as switching themes
					// rather than the one for writing a post.
					'edit_posts'             => 'edit_theme_options',
					'edit_others_posts'      => 'edit_theme_options',
					'publish_posts'          => 'edit_theme_options',
					'delete_posts'           => 'edit_theme_options',
					'read_private_posts'     => 'edit_theme_options',
					'edit_published_posts'   => 'edit_theme_options',
					'delete_published_posts' => 'edit_theme_options',
				),
			)
		);

		register_post_meta(
			self::NAME,
			Template_Type::META,
			array(
				'type'              => 'string',
				'single'            => true,
				'default'           => Template_Type::HEADER,
				'sanitize_callback' => array( Template_Type::class, 'sanitize' ),
				'show_in_rest'      => false,
				'auth_callback'     => static function () {
					return current_user_can( 'edit_theme_options' );
				},
			)
		);
	}
}
