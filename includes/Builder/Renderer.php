<?php
/**
 * Template rendering.
 *
 * @package DecentCore
 */

namespace DecentCore\Builder;

defined( 'ABSPATH' ) || exit;

use Elementor\Plugin as Elementor_Plugin;

/**
 * Renders a resolved template into the theme's header and footer slots.
 *
 * The theme exposes decentthemes/header/replaced and its footer twin, and
 * checks Elementor Pro's own location first. This hooks the middle tier, so a
 * site with Pro keeps Pro's header and never gets two.
 */
final class Renderer {

	/**
	 * Resolver.
	 *
	 * @var Resolver
	 */
	private $resolver;

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
		add_filter( 'decentthemes/header/replaced', array( $this, 'render_header' ) );
		add_filter( 'decentthemes/footer/replaced', array( $this, 'render_footer' ) );
	}

	/**
	 * Renders the header, if one is assigned here.
	 *
	 * @param bool $rendered Whether something already rendered it.
	 * @return bool
	 */
	public function render_header( bool $rendered ): bool {
		return $this->render_location( Template_Type::HEADER, $rendered );
	}

	/**
	 * Renders the footer, if one is assigned here.
	 *
	 * @param bool $rendered Whether something already rendered it.
	 * @return bool
	 */
	public function render_footer( bool $rendered ): bool {
		return $this->render_location( Template_Type::FOOTER, $rendered );
	}

	/**
	 * Renders a location.
	 *
	 * @param string $type     Template type.
	 * @param bool   $rendered Whether something already rendered it.
	 * @return bool
	 */
	private function render_location( string $type, bool $rendered ): bool {
		// Something earlier in the chain already owns this slot. Rendering
		// anyway is how a page ends up with two headers.
		if ( $rendered ) {
			return true;
		}

		$template_id = $this->resolver->resolve( $type );

		if ( ! $template_id ) {
			return false;
		}

		$markup = $this->content( $template_id );

		if ( '' === trim( $markup ) ) {
			// An empty template is almost certainly a mistake; falling through
			// to the theme's static part beats rendering nothing at all.
			return false;
		}

		list( $open, $close ) = self::landmark( $type );

		// The landmark lives here, not in the builder content: an editor
		// composing a header should not have to know that the page needs a
		// banner role, and replacing the static part must not silently remove
		// it. Wrapping by type means every tier produces the same structure.
		echo $open; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static markup from self::landmark().

		// Elementor has already escaped its own output, and running it through
		// wp_kses here would strip the very markup an editor built.
		echo $markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Elementor-generated builder content.

		echo $close; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static markup from self::landmark().

		return true;
	}

	/**
	 * Returns the wrapper a location's landmark needs.
	 *
	 * @param string $type Template type.
	 * @return array{0:string, 1:string} Opening and closing markup.
	 */
	private static function landmark( string $type ): array {
		if ( Template_Type::HEADER === $type ) {
			return array( '<header class="site-header site-header--builder">', '</header>' );
		}

		if ( Template_Type::FOOTER === $type ) {
			return array( '<footer class="site-footer site-footer--builder">', '</footer>' );
		}

		// A block is placed inside an existing landmark, so it adds none.
		return array( '', '' );
	}

	/**
	 * Returns a template's rendered builder content.
	 *
	 * @param int $template_id Template ID.
	 * @return string
	 */
	public function content( int $template_id ): string {
		if ( ! class_exists( '\Elementor\Plugin' ) ) {
			return '';
		}

		$frontend = Elementor_Plugin::instance()->frontend;

		if ( ! $frontend ) {
			return '';
		}

		// with_css so the template's own generated styles are printed even
		// though this post is not the queried object.
		return (string) $frontend->get_builder_content_for_display( $template_id, true );
	}
}
