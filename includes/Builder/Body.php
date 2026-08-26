<?php
/**
 * Page body replacement.
 *
 * @package PixelomaticCore
 */

namespace PixelomaticCore\Builder;

defined( 'ABSPATH' ) || exit;

use Elementor\Plugin as Elementor_Plugin;

/**
 * Hands a singular view's body to a builder template.
 *
 * The header and footer are replaced from inside the theme's own parts,
 * because the theme asks for them there. A body has no such slot: the theme
 * chooses a template file and renders the loop into it. So this is the one
 * location resolved by swapping `template_include` for a file of the plugin's
 * own — templates/single.php, which opens the theme's header, renders the
 * template's builder content in place of the loop, and closes the theme's
 * footer.
 *
 * What that buys is the product detail page in the editor without Elementor
 * Pro: assign a Single template to "Products and the catalogue" and every
 * download renders the layout built in Elementor, with the theme's header,
 * footer and design tokens still around it. With Pro installed, Pro's own
 * `single` location wins — Pro_Bridge answers the resolve filter with 0 and
 * this class never swaps the template.
 *
 * The theme's static single-product template stays exactly where it was. It is
 * what renders until a template is published with a rule that matches, and
 * what renders again the moment that template is unpublished.
 */
final class Body {

	/**
	 * Resolver.
	 *
	 * @var Resolver
	 */
	private $resolver;

	/**
	 * The template this request swapped in, for templates/single.php to render.
	 *
	 * @var int
	 */
	private static $template_id = 0;

	/**
	 * Constructor.
	 *
	 * @param Resolver $resolver Resolver.
	 */
	public function __construct( Resolver $resolver ) {
		$this->resolver = $resolver;
	}

	/**
	 * Attaches hooks.
	 *
	 * @return void
	 */
	public function register(): void {
		// After Elementor's own template_include at the default priority, and
		// well before Canvas at 999 — which only ever acts on a template's own
		// URL, so the two never contend for the same request.
		add_filter( 'template_include', array( $this, 'template_include' ), 100 );
		add_filter( 'body_class', array( $this, 'body_class' ) );
	}

	/**
	 * Swaps in the builder body when one is assigned to this request.
	 *
	 * @param string $template Template path WordPress resolved.
	 * @return string
	 */
	public function template_include( $template ): string {
		$template = (string) $template;

		if ( ! self::applies() ) {
			return $template;
		}

		$template_id = $this->resolver->resolve( Template_Type::SINGLE );

		if ( ! $template_id ) {
			return $template;
		}

		$own = PIXELOMATIC_CORE_DIR . 'templates/single.php';

		if ( ! file_exists( $own ) ) {
			return $template;
		}

		self::$template_id = $template_id;

		return $own;
	}

	/**
	 * Marks a page whose body came from a template.
	 *
	 * @param string[] $classes Body classes.
	 * @return string[]
	 */
	public function body_class( $classes ): array {
		$classes = (array) $classes;

		if ( self::resolved() ) {
			$classes[] = 'has-builder-single';
		}

		return $classes;
	}

	/**
	 * Returns the template this request is rendering, or 0.
	 *
	 * @return int
	 */
	public static function resolved(): int {
		return self::$template_id;
	}

	/**
	 * Whether this request is one a body template may take over.
	 *
	 * @return bool
	 */
	private static function applies(): bool {
		// A template's own URL is the canvas the editor previews in. Replacing
		// its body with a template would mean editing a layout while looking
		// at whichever layout the conditions resolved.
		if ( is_singular( Post_Type::NAME ) ) {
			return false;
		}

		if ( ! is_singular() || is_embed() ) {
			return false;
		}

		// Editing the post itself in Elementor: the preview frame must show
		// that post's own content, not the template assigned to it.
		if ( class_exists( '\Elementor\Plugin' ) ) {
			$preview = Elementor_Plugin::instance()->preview;

			if ( $preview && $preview->is_preview_mode() ) {
				return false;
			}
		}

		return true;
	}
}
