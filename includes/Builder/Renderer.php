<?php
/**
 * Template rendering.
 *
 * @package PixelomaticCore
 */

namespace PixelomaticCore\Builder;

defined( 'ABSPATH' ) || exit;

use Elementor\Plugin as Elementor_Plugin;

/**
 * Renders a resolved template into the theme's header and footer slots.
 *
 * The theme exposes pixelomatic/header/replaced and its footer twin, and
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
	 * Templates already rendered on this request, by ID.
	 *
	 * @var array<int, bool>
	 */
	private static $rendering = array();

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
		add_filter( 'pixelomatic/header/replaced', array( $this, 'render_header' ) );
		add_filter( 'pixelomatic/footer/replaced', array( $this, 'render_footer' ) );
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

		$markup = self::content( $template_id );

		if ( '' === trim( $markup ) ) {
			// An empty template is almost certainly a mistake; falling through
			// to the theme's static part beats rendering nothing at all.
			return false;
		}

		list( $open, $close ) = self::landmark( $type, $template_id );

		// The landmark lives here, not in the builder content: an editor
		// composing a header should not have to know that the page needs a
		// banner role, and replacing the static part must not silently remove
		// it. Wrapping by type means every tier produces the same structure.
		echo $open; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Built and escaped by self::landmark().

		// Elementor has already escaped its own output, and running it through
		// wp_kses here would strip the very markup an editor built.
		echo $markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Elementor-generated builder content.

		echo $close; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static markup from self::landmark().

		return true;
	}

	/**
	 * Returns the wrapper a location's landmark needs.
	 *
	 * @param string $type        Template type.
	 * @param int    $template_id Template ID.
	 * @return array{0:string, 1:string} Opening and closing markup.
	 */
	private static function landmark( string $type, int $template_id ): array {
		if ( Template_Type::HEADER === $type ) {
			return array( '<header ' . self::attributes( self::header_attributes( $template_id ), $type, $template_id ) . '>', '</header>' );
		}

		if ( Template_Type::FOOTER === $type ) {
			return array( '<footer ' . self::attributes( self::footer_attributes( $template_id ), $type, $template_id ) . '>', '</footer>' );
		}

		// A block is placed inside an existing landmark, so it adds none.
		return array( '', '' );
	}

	/**
	 * Builds the header element's attributes.
	 *
	 * The behaviour options become classes, because CSS does the sticking and
	 * the overlaying on its own, and one data attribute, because the script
	 * that adds the stuck and hidden states needs values rather than classes.
	 *
	 * @param int $template_id Template ID.
	 * @return array<string, string>
	 */
	private static function header_attributes( int $template_id ): array {
		$settings = Display_Settings::for_template( $template_id, Template_Type::HEADER );
		$classes  = array( 'site-header', 'site-header--builder' );

		if ( ! empty( $settings['overlay'] ) ) {
			$classes[] = 'site-header--overlay';
		}

		if ( ! empty( $settings['sticky'] ) ) {
			$classes[] = 'site-header--sticky';

			if ( ! empty( $settings['sticky_mobile'] ) ) {
				$classes[] = 'site-header--sticky-mobile';
			}

			if ( ! empty( $settings['shadow'] ) ) {
				$classes[] = 'site-header--shadow';
			}

			if ( ! empty( $settings['hide'] ) ) {
				$classes[] = 'site-header--hide-on-scroll';
			}
		}

		$attributes = array(
			'id'    => 'masthead',
			'class' => implode( ' ', $classes ),
		);

		// Only a header with a state to keep gives the script something to
		// bind to. Without this attribute the script finds nothing and
		// returns, which is what happens on most pages of most sites.
		if ( Display_Settings::needs_script( $settings ) ) {
			$attributes['data-pixelomatic-header'] = (string) wp_json_encode(
				array(
					'offset'  => (int) $settings['offset'],
					'hide'    => (bool) $settings['hide'],
					'mobile'  => (bool) $settings['sticky_mobile'],
					'overlay' => (bool) $settings['overlay'],
					'sticky'  => (bool) $settings['sticky'],
				)
			);
		}

		if ( ! empty( $settings['offset'] ) ) {
			$attributes['style'] = '--pixelomatic-sticky-offset:' . (int) $settings['offset'] . 'px';
		}

		return $attributes;
	}

	/**
	 * Builds the footer element's attributes.
	 *
	 * @param int $template_id Template ID.
	 * @return array<string, string>
	 */
	private static function footer_attributes( int $template_id ): array {
		$settings = Display_Settings::for_template( $template_id, Template_Type::FOOTER );
		$classes  = array( 'site-footer', 'site-footer--builder' );

		if ( ! empty( $settings['bottom'] ) ) {
			$classes[] = 'site-footer--bottom';
		}

		return array(
			'id'    => 'colophon',
			'class' => implode( ' ', $classes ),
		);
	}

	/**
	 * Renders an attribute map, escaped.
	 *
	 * @param array<string, string> $attributes  Attribute map.
	 * @param string                $type        Template type.
	 * @param int                   $template_id Template ID.
	 * @return string
	 */
	private static function attributes( array $attributes, string $type, int $template_id ): string {
		/**
		 * Filters the attributes of a rendered header or footer.
		 *
		 * @since 1.0.0
		 *
		 * @param array<string, string> $attributes  Attribute map.
		 * @param string                $type        Template type.
		 * @param int                   $template_id Template ID.
		 */
		$attributes = (array) apply_filters( 'pixelomatic_core/builder/attributes', $attributes, $type, $template_id );

		$parts = array();

		foreach ( $attributes as $name => $value ) {
			if ( null === $value || false === $value || '' === $value ) {
				continue;
			}

			$parts[] = sprintf( '%s="%s"', esc_attr( (string) $name ), esc_attr( (string) $value ) );
		}

		return implode( ' ', $parts );
	}

	/**
	 * Returns a template's rendered builder content.
	 *
	 * @param int $template_id Template ID.
	 * @return string
	 */
	public static function content( int $template_id ): string {
		if ( ! class_exists( '\Elementor\Plugin' ) ) {
			return '';
		}

		// A template that contains a shortcode for itself would otherwise
		// recurse until the request runs out of memory.
		if ( isset( self::$rendering[ $template_id ] ) ) {
			return '';
		}

		$frontend = Elementor_Plugin::instance()->frontend;

		if ( ! $frontend ) {
			return '';
		}

		self::$rendering[ $template_id ] = true;

		// with_css so the template's own generated styles are printed even
		// though this post is not the queried object.
		$markup = (string) $frontend->get_builder_content_for_display( $template_id, true );

		unset( self::$rendering[ $template_id ] );

		return $markup;
	}
}
