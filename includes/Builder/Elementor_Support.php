<?php
/**
 * Elementor support for builder templates.
 *
 * @package DecentCore
 */

namespace DecentCore\Builder;

defined( 'ABSPATH' ) || exit;

/**
 * Makes decent_template a first-class Elementor document, and keeps it out of
 * everywhere a header has no business being.
 *
 * The canvas itself lives in Canvas: a template is edited, previewed and
 * rendered on a bare page, and that is enforced in one place.
 */
final class Elementor_Support {

	/**
	 * Attaches hooks.
	 *
	 * @return void
	 */
	public function register(): void {
		add_filter( 'elementor/pro/utils/get_public_post_types', array( $this, 'allow_post_type' ) );

		// Publicly queryable so the editor's preview resolves, but it is not
		// content: keep it out of sitemaps and out of search engines.
		add_filter( 'wp_sitemaps_post_types', array( $this, 'hide_from_sitemap' ) );
		add_filter( 'wp_robots', array( $this, 'no_index' ) );

		// A template is not a document with a next and a previous, and it must
		// never be the canonical URL of anything.
		add_filter( 'get_canonical_url', array( $this, 'no_canonical' ), 10, 2 );
	}

	/**
	 * Adds the template post type to Elementor's list.
	 *
	 * @param array<string, string> $types Post types.
	 * @return array<string, string>
	 */
	public function allow_post_type( $types ): array {
		$types                    = (array) $types;
		$types[ Post_Type::NAME ] = __( 'Templates', 'decent-core' );

		return $types;
	}

	/**
	 * Removes templates from the sitemap.
	 *
	 * @param array<string, mixed> $post_types Post types.
	 * @return array<string, mixed>
	 */
	public function hide_from_sitemap( $post_types ): array {
		$post_types = (array) $post_types;

		unset( $post_types[ Post_Type::NAME ] );

		return $post_types;
	}

	/**
	 * Tells robots not to index a template preview.
	 *
	 * @param array<string, mixed> $robots Robots directives.
	 * @return array<string, mixed>
	 */
	public function no_index( $robots ): array {
		$robots = (array) $robots;

		if ( is_singular( Post_Type::NAME ) ) {
			$robots['noindex']  = true;
			$robots['nofollow'] = true;
		}

		return $robots;
	}

	/**
	 * Drops the canonical link from a template preview.
	 *
	 * @param string   $url  Canonical URL.
	 * @param \WP_Post $post Post.
	 * @return string
	 */
	public function no_canonical( $url, $post ): string {
		if ( $post && Post_Type::NAME === $post->post_type ) {
			return '';
		}

		return (string) $url;
	}
}
