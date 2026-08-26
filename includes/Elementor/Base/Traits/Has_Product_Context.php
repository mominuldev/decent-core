<?php
/**
 * Product context for single-product widgets.
 *
 * @package PixelomaticCore
 */

namespace PixelomaticCore\Elementor\Base\Traits;

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Plugin as Elementor_Plugin;

/**
 * Resolves the product a single-product widget renders, and renders the
 * theme's template part for it.
 *
 * Every widget on the product detail page is a delegate: the theme owns
 * template-parts/product/, the single-download template renders the same
 * files, and a widget that emitted its own markup would fork the page in two.
 * What each of those widgets does need is the same three things — the product
 * to render, the global post set up around the part, and the panel notice that
 * explains where the widget belongs — so they are written once here.
 *
 * Expects the widget to also use Has_Style_Controls.
 */
trait Has_Product_Context {

	/**
	 * Adds the notice every single-product widget shows in the panel.
	 *
	 * The heading is the sentence that decides whether the widget belongs on
	 * the page at all; where it renders, and whatever the widget adds, sit
	 * under it.
	 *
	 * @param string $extra Sentence particular to this widget.
	 * @return void
	 */
	protected function register_product_notice( string $extra = '' ): void {
		$this->register_notice(
			'notice',
			__( 'Renders the current product.', 'pixelomatic-core' ),
			array(
				__( 'Place it on a single-product template. Anywhere else it previews the most recent product in the editor and renders nothing on the front end.', 'pixelomatic-core' ),
				$extra,
			)
		);
	}

	/**
	 * Renders one of the theme's product template parts for the current product.
	 *
	 * @param string               $slug Part slug, relative to template-parts/product/.
	 * @param string|null          $name Part name, for the `part-name.php` variant.
	 * @param array<string, mixed> $args Arguments the part takes.
	 * @return void
	 */
	protected function render_product_part( string $slug, ?string $name = null, array $args = array() ): void {
		$this->with_product(
			static function () use ( $slug, $name, $args ): void {
				get_template_part( 'template-parts/product/' . $slug, $name, $args );
			}
		);
	}

	/**
	 * Adds the control that decides where a section's items come from.
	 *
	 * Authored is the default. A template applies to every product on the
	 * site, and the copy on it is written once in the panel; the product's own
	 * fields are the alternative for the sections where each product really
	 * does differ.
	 *
	 * @param string $product_label Label for the product-data option.
	 * @param string $id            Control id, for a widget with more than one.
	 * @return void
	 */
	protected function register_source_control( string $product_label, string $id = 'source' ): void {
		$this->add_control(
			$id,
			array(
				'label'   => __( 'Content', 'pixelomatic-core' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'custom',
				'options' => array(
					'custom'  => __( 'Written here', 'pixelomatic-core' ),
					'product' => $product_label,
				),
			)
		);
	}

	/**
	 * Whether a block's items are written in the panel.
	 *
	 * @param string $id Control id.
	 * @return bool
	 */
	protected function is_custom_source( string $id = 'source' ): bool {
		return 'product' !== (string) $this->get_settings_for_display( $id );
	}

	/**
	 * The section head as the theme's parts take it.
	 *
	 * @return array<string, string>
	 */
	protected function head_args(): array {
		$settings = $this->get_settings_for_display();

		return array(
			'eyebrow' => (string) ( $settings['eyebrow'] ?? '' ),
			'title'   => (string) ( $settings['title'] ?? '' ),
			'intro'   => (string) ( $settings['intro'] ?? '' ),
			'tag'     => (string) ( $settings['title_tag'] ?? 'h2' ),
		);
	}

	/**
	 * Splits a textarea into its non-empty lines.
	 *
	 * @param string $value Raw textarea value.
	 * @return string[]
	 */
	protected static function lines( string $value ): array {
		$lines = preg_split( '/\r\n|\r|\n/', $value );

		if ( ! is_array( $lines ) ) {
			return array();
		}

		$lines = array_map( 'trim', $lines );

		$filled = array_filter(
			$lines,
			static function ( string $line ): bool {
				return '' !== $line;
			}
		);

		return array_values( $filled );
	}

	/**
	 * Runs a callback with the widget's product as the global post.
	 *
	 * The template parts read the global post, so it is set for the render and
	 * restored straight after. Leaving it changed would corrupt every widget
	 * below this one on the page.
	 *
	 * @param callable $render Renders inside the product's post context.
	 * @return void
	 */
	protected function with_product( callable $render ): void {
		$id = $this->resolve_download();

		if ( ! $id ) {
			return;
		}

		global $post;
		$original = $post;

		$post = get_post( $id ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- Restored below.
		setup_postdata( $post );

		$render();

		$post = $original; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- Restoring.
		wp_reset_postdata();
	}

	/**
	 * Returns the sections the current product has, in page order.
	 *
	 * The theme's sections.php is the one place that decides which sections a
	 * product has; reading it here is what keeps a widget from rendering a
	 * heading for a section with nothing in it.
	 *
	 * @return array<string, array<string, mixed>> Section index, keyed by slug.
	 */
	protected function product_sections(): array {
		$file = locate_template( 'template-parts/product/sections.php' );

		if ( '' === $file ) {
			return array();
		}

		$sections = require $file;

		return is_array( $sections ) ? $sections : array();
	}

	/**
	 * Returns the download this widget should render.
	 *
	 * On a single-product view that is the queried product. In the editor
	 * there is no queried product, so it falls back to the most recent one and
	 * the canvas shows something real instead of an empty box.
	 *
	 * @return int Download ID, or 0.
	 */
	protected function resolve_download(): int {
		if ( is_singular( 'download' ) ) {
			return (int) get_queried_object_id();
		}

		$editor = Elementor_Plugin::instance()->editor;

		if ( ! $editor || ! $editor->is_edit_mode() ) {
			return 0;
		}

		$preview = get_posts(
			array(
				'post_type'      => 'download',
				'post_status'    => 'publish',
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'no_found_rows'  => true,
			)
		);

		return empty( $preview ) ? 0 : (int) $preview[0];
	}
}
