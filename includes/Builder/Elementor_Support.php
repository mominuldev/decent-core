<?php
/**
 * Elementor support for builder templates.
 *
 * @package DecentCore
 */

namespace DecentCore\Builder;

defined( 'ABSPATH' ) || exit;

/**
 * Makes decent_template editable in Elementor, and editable sensibly.
 *
 * A header edited inside the normal page canvas inherits the theme's header
 * and footer, so an editor builds a header while looking at another one. These
 * templates therefore edit on a bare canvas.
 */
final class Elementor_Support {

	/**
	 * Attaches hooks.
	 *
	 * @return void
	 */
	public function register(): void {
		add_filter( 'elementor/pro/utils/get_public_post_types', array( $this, 'allow_post_type' ) );
		add_filter( 'template_include', array( $this, 'use_canvas' ), 999 );
		add_filter( 'body_class', array( $this, 'body_class' ) );

		// Publicly queryable so the editor's preview resolves, but it is not
		// content: keep it out of sitemaps and out of search engines.
		add_filter( 'wp_sitemaps_post_types', array( $this, 'hide_from_sitemap' ) );
		add_filter( 'wp_robots', array( $this, 'no_index' ) );
	}

	/**
	 * Adds the template post type to Elementor's list.
	 *
	 * @param array<string, string> $types Post types.
	 * @return array<string, string>
	 */
	public function allow_post_type( array $types ): array {
		$types[ Post_Type::NAME ] = __( 'Templates', 'decent-core' );

		return $types;
	}

	/**
	 * Renders a template on a bare canvas when previewed directly.
	 *
	 * @param string $template Template path.
	 * @return string
	 */
	public function use_canvas( string $template ): string {
		if ( ! is_singular( Post_Type::NAME ) ) {
			return $template;
		}

		$canvas = locate_template( 'page-templates/canvas.php' );

		return $canvas ? $canvas : $template;
	}

	/**
	 * Removes templates from the sitemap.
	 *
	 * @param array<string, mixed> $post_types Post types.
	 * @return array<string, mixed>
	 */
	public function hide_from_sitemap( array $post_types ): array {
		unset( $post_types[ Post_Type::NAME ] );

		return $post_types;
	}

	/**
	 * Tells robots not to index a template preview.
	 *
	 * @param array<string, mixed> $robots Robots directives.
	 * @return array<string, mixed>
	 */
	public function no_index( array $robots ): array {
		if ( is_singular( Post_Type::NAME ) ) {
			$robots['noindex']  = true;
			$robots['nofollow'] = true;
		}

		return $robots;
	}

	/**
	 * Marks the canvas so styles can target it.
	 *
	 * @param string[] $classes Body classes.
	 * @return string[]
	 */
	public function body_class( array $classes ): array {
		if ( is_singular( Post_Type::NAME ) ) {
			$classes[] = 'is-builder-template';
		}

		return $classes;
	}
}
