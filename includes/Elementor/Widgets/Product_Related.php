<?php
/**
 * Related Products widget.
 *
 * @package PixelomaticCore
 */

namespace PixelomaticCore\Elementor\Widgets;

defined( 'ABSPATH' ) || exit;

use PixelomaticCore\Elementor\Base\Traits\Has_Product_Card_Style;
use PixelomaticCore\Elementor\Base\Traits\Has_Product_Context;
use PixelomaticCore\Elementor\Base\Traits\Has_Style_Controls;
use PixelomaticCore\Elementor\Base\Widget_Base;
use Elementor\Controls_Manager;

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
	use Has_Product_Context;
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

		$this->register_product_notice(
			__( 'The cards are products sharing this one\'s category, so they are never written here.', 'pixelomatic-core' )
		);

		$this->add_control(
			'eyebrow',
			array(
				'label'       => __( 'Eyebrow', 'pixelomatic-core' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'More like this', 'pixelomatic-core' ),
				'label_block' => true,
			)
		);

		$this->add_control(
			'title',
			array(
				'label'       => __( 'Title', 'pixelomatic-core' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Related products', 'pixelomatic-core' ),
				'label_block' => true,
			)
		);

		$this->add_control(
			'count',
			array(
				'label'   => __( 'Products to show', 'pixelomatic-core' ),
				'type'    => Controls_Manager::NUMBER,
				'min'     => 1,
				'max'     => 6,
				'default' => 3,
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
		$settings = $this->get_settings_for_display();

		$this->render_product_part(
			'related',
			null,
			array(
				'eyebrow' => (string) ( $settings['eyebrow'] ?? '' ),
				'title'   => (string) ( $settings['title'] ?? '' ),
				'count'   => (int) ( $settings['count'] ?? 3 ),
			)
		);
	}
}
