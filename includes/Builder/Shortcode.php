<?php
/**
 * Block template shortcode.
 *
 * @package DecentCore
 */

namespace DecentCore\Builder;

defined( 'ABSPATH' ) || exit;

/**
 * Places a block template anywhere a shortcode can go.
 *
 * Headers and footers replace a slot the theme already has. A block has no
 * slot of its own, so it needs somewhere to be asked for: a widget, a content
 * editor, a template part in a child theme. One shortcode covers all three,
 * and `decent_core/builder/block` covers the code path.
 */
final class Shortcode {

	/**
	 * Shortcode tag.
	 */
	public const TAG = 'decent_template';

	/**
	 * Attaches hooks.
	 *
	 * @return void
	 */
	public function register(): void {
		add_shortcode( self::TAG, array( $this, 'render' ) );
		add_action( 'decent_core/builder/block', array( $this, 'output' ) );
	}

	/**
	 * Renders a template by ID.
	 *
	 * @param array<string, string>|string $atts Shortcode attributes.
	 * @return string
	 */
	public function render( $atts ): string {
		$atts = shortcode_atts( array( 'id' => '0' ), (array) $atts, self::TAG );

		return self::markup( absint( $atts['id'] ) );
	}

	/**
	 * Echoes a template, for use as an action.
	 *
	 * @param int $template_id Template ID.
	 * @return void
	 */
	public function output( $template_id = 0 ): void {
		// Elementor has already escaped its own output; wp_kses here would
		// strip the markup an editor built.
		echo self::markup( absint( $template_id ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Elementor-generated builder content.
	}

	/**
	 * Returns a published template's markup, or an empty string.
	 *
	 * @param int $template_id Template ID.
	 * @return string
	 */
	private static function markup( int $template_id ): string {
		if ( ! $template_id ) {
			return '';
		}

		$post = get_post( $template_id );

		// Only a published template of ours, so the shortcode can never be
		// pointed at a draft, a private post or somebody else's post type.
		if ( ! $post || Post_Type::NAME !== $post->post_type || 'publish' !== $post->post_status ) {
			return '';
		}

		return Renderer::content( $template_id );
	}
}
