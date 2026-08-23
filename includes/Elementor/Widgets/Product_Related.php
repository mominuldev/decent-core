<?php
/**
 * Related Products widget.
 *
 * @package PixelomaticCore
 */

namespace PixelomaticCore\Elementor\Widgets;

defined( 'ABSPATH' ) || exit;

use PixelomaticCore\Elementor\Base\Traits\Has_Product_Card_Style;
use PixelomaticCore\Elementor\Base\Traits\Has_Style_Controls;
use PixelomaticCore\Elementor\Base\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Plugin as Elementor_Plugin;

/**
 * Products sharing the current product's primary category.
 *
 * A delegate, deliberately. The theme owns template-parts/product/related.php
 * and the single-product template renders the same file, so placing this in a
 * builder layout cannot produce different markup from the template it
 * replaces. If the component changes, it changes in one place.
 */
final class Product_Related extends Widget_Base {

	use Has_Style_Controls;
	use Has_Product_Card_Style;

	/**
	 * Widget slug.
	 *
	 * @return string
	 */
	public static function slug(): string {
		return 'product-related';
	}

	/**
	 * Registers controls.
	 *
	 * @return void
	 */
	protected function register_controls(): void {
		$this->start_controls_section( 'content', array( 'label' => __( 'Related products', 'pixelomatic-core' ) ) );

		$this->add_control(
			'notice',
			array(
				'type'            => Controls_Manager::RAW_HTML,
				'raw'             => esc_html__( 'Renders the current product. Place it on a single-product template; elsewhere it previews the most recent product in the editor and renders nothing on the front end.', 'pixelomatic-core' ),
				'content_classes' => 'elementor-descriptor',
			)
		);

		$this->end_controls_section();

		$this->start_style_section( 'style_head', __( 'Section head', 'pixelomatic-core' ) );
		$this->register_section_head_style_controls();
		$this->end_controls_section();

		$this->start_style_section( 'style_card', __( 'Product cards', 'pixelomatic-core' ) );
		$this->register_product_card_style_controls();
		$this->end_controls_section();

		$this->start_style_section( 'style_layout', __( 'Layout', 'pixelomatic-core' ) );

		$this->add_responsive_control(
			'columns',
			array(
				'label'     => __( 'Columns', 'pixelomatic-core' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => array(
					''  => __( 'Theme default', 'pixelomatic-core' ),
					'1' => '1',
					'2' => '2',
					'3' => '3',
					'4' => '4',
				),
				'selectors' => array(
					'{{WRAPPER}} .product-grid' => 'grid-template-columns: repeat({{VALUE}}, minmax(0, 1fr));',
				),
			)
		);

		$this->register_gap_style( 'grid_gap', __( 'Gap', 'pixelomatic-core' ), '{{WRAPPER}} .product-grid', 56 );

		$this->register_box_style(
			'band',
			__( 'Band', 'pixelomatic-core' ),
			'{{WRAPPER}} .section',
			array( 'shadow' => false )
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

		get_template_part( 'template-parts/product/related' );

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
