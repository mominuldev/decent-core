<?php
/**
 * Canvas enforcement for builder templates.
 *
 * @package DecentCore
 */

namespace DecentCore\Builder;

defined( 'ABSPATH' ) || exit;

/**
 * Guarantees a builder template is composed, previewed and edited on a bare
 * canvas — no theme header, no theme footer, no container.
 *
 * Without this a header template is edited inside the ordinary page canvas,
 * which means the editor builds a header while looking at the theme's other
 * header, three hundred pixels further down. Worse, the preview iframe then
 * renders the resolved builder header too, so the template appears twice.
 *
 * Enforcement is deliberately belt and braces, because a single mechanism has
 * a single way to fail:
 *
 *   1. `_wp_page_template` is pinned to Elementor's canvas on every save, so
 *      Elementor's own page-templates module resolves canvas as well.
 *   2. The layout picker is reduced to that one entry, so the editor never
 *      offers a choice that this class would override anyway.
 *   3. `template_include` serves a canvas file regardless of what any of that
 *      stored, falling back through theme, Elementor and the plugin's own.
 */
final class Canvas {

	/**
	 * Elementor's canvas page template.
	 *
	 * The literal rather than the class constant: it is stored in post meta on
	 * every save, and a save must not depend on Elementor's module manager
	 * having booted.
	 */
	public const TEMPLATE = 'elementor_canvas';

	/**
	 * Attaches hooks.
	 *
	 * @return void
	 */
	public function register(): void {
		add_action( 'save_post_' . Post_Type::NAME, array( $this, 'pin_template' ), 10, 2 );
		add_filter( 'theme_' . Post_Type::NAME . '_templates', array( $this, 'only_canvas' ), 20 );

		// After Elementor's own template_include, which runs at the default
		// priority: whatever it resolved, a template renders on a canvas.
		add_filter( 'template_include', array( $this, 'template_include' ), 999 );
		add_filter( 'body_class', array( $this, 'body_class' ) );
	}

	/**
	 * Pins a template to the canvas layout when it is saved.
	 *
	 * @param int      $post_id Post ID.
	 * @param \WP_Post $post    Post object.
	 * @return void
	 */
	public function pin_template( $post_id, $post = null ): void {
		$post_id = (int) $post_id;

		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || wp_is_post_revision( $post_id ) ) {
			return;
		}

		if ( $post && Post_Type::NAME !== $post->post_type ) {
			return;
		}

		if ( self::TEMPLATE === get_post_meta( $post_id, '_wp_page_template', true ) ) {
			return;
		}

		update_post_meta( $post_id, '_wp_page_template', self::TEMPLATE );
	}

	/**
	 * Reduces the layout picker to the one layout that applies.
	 *
	 * @param array<string, string> $templates Page templates, as file => label.
	 * @return array<string, string>
	 */
	public function only_canvas( $templates ): array {
		$templates = (array) $templates;

		// Elementor's own label when Elementor supplied it, so the picker does
		// not rename a layout the editor already knows by another name.
		$label = isset( $templates[ self::TEMPLATE ] ) ? (string) $templates[ self::TEMPLATE ] : __( 'Canvas', 'decent-core' );

		return array( self::TEMPLATE => $label );
	}

	/**
	 * Serves a canvas for a template's own URL.
	 *
	 * That URL is what Elementor loads in the editor's preview iframe, so this
	 * filter is what the editing experience actually looks like.
	 *
	 * @param string $template Template path WordPress resolved.
	 * @return string
	 */
	public function template_include( $template ): string {
		if ( ! is_singular( Post_Type::NAME ) ) {
			return (string) $template;
		}

		return self::path( (string) $template );
	}

	/**
	 * Returns the canvas file to render with.
	 *
	 * The theme's canvas first: the theme owns the document shell, and its
	 * canvas is where the design tokens and the body classes come from.
	 * Elementor's is the next best thing, and the plugin's own is the floor —
	 * a header must still be editable under a theme that ships neither.
	 *
	 * @param string $fallback Template to keep when no canvas exists.
	 * @return string
	 */
	public static function path( string $fallback = '' ): string {
		$theme = locate_template( 'page-templates/canvas.php' );

		if ( $theme ) {
			return $theme;
		}

		if ( defined( 'ELEMENTOR_PATH' ) ) {
			$elementor = ELEMENTOR_PATH . 'modules/page-templates/templates/canvas.php';

			if ( file_exists( $elementor ) ) {
				return $elementor;
			}
		}

		$own = DECENT_CORE_DIR . 'templates/canvas.php';

		return file_exists( $own ) ? $own : $fallback;
	}

	/**
	 * Marks the canvas so a template can be styled while it is being built.
	 *
	 * @param string[] $classes Body classes.
	 * @return string[]
	 */
	public function body_class( $classes ): array {
		$classes = (array) $classes;

		if ( ! is_singular( Post_Type::NAME ) ) {
			return $classes;
		}

		$classes[] = 'is-builder-template';
		$classes[] = 'is-builder-' . Template_Type::of( (int) get_queried_object_id() );

		return $classes;
	}
}
