<?php
/**
 * Product Buy Box widget.
 *
 * @package DecentCore
 */

namespace DecentCore\Elementor\Widgets;

defined( 'ABSPATH' ) || exit;

use DecentCore\Elementor\Base\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Plugin as Elementor_Plugin;

/**
 * The purchase panel, wrapping EDD's own form.
 *
 * A delegate, deliberately. The theme owns template-parts/product/buy-box.php
 * and the single-product template renders the same file, so placing this in a
 * builder layout cannot produce different markup from the template it
 * replaces. If the component changes, it changes in one place.
 */
final class Product_Buy_Box extends Widget_Base {

	/**
	 * Widget slug.
	 *
	 * @return string
	 */
	public static function slug(): string {
		return 'product-buy-box';
	}

	/**
	 * Registers controls.
	 *
	 * @return void
	 */
	protected function register_controls(): void {
		$this->start_controls_section( 'content', array( 'label' => __( 'Buy box', 'decent-core' ) ) );

		$this->add_control(
			'notice',
			array(
				'type'            => Controls_Manager::RAW_HTML,
				'raw'             => esc_html__( 'Renders the current product. Place it on a single-product template; elsewhere it previews the most recent product in the editor and renders nothing on the front end.', 'decent-core' ),
				'content_classes' => 'elementor-descriptor',
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Renders the widget.
	 *
	 * @return void
	 */
	protected function render(): void {
		$id = $this->resolve_download();

		if ( ! $id ) {
			return;
		}

		// The template part reads the global post, so it is set for the render
		// and restored straight after. Leaving it changed would corrupt every
		// widget below this one on the page.
		global $post;
		$original = $post;

		$post = get_post( $id ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- Restored below.
		setup_postdata( $post );

		get_template_part( 'template-parts/product/buy-box' );

		$post = $original; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- Restoring.
		wp_reset_postdata();
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
	private function resolve_download(): int {
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
