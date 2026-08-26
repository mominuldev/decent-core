<?php
/**
 * Product Buy Box widget.
 *
 * @package PixelomaticCore
 */

namespace PixelomaticCore\Elementor\Widgets;

defined( 'ABSPATH' ) || exit;

use PixelomaticCore\Elementor\Base\Traits\Has_Product_Context;
use PixelomaticCore\Elementor\Base\Traits\Has_Style_Controls;
use PixelomaticCore\Elementor\Base\Widget_Base;

/**
 * The purchase panel, wrapping EDD's own form.
 *
 * A delegate, deliberately. The theme owns template-parts/product/buy-box.php
 * and the single-product template renders the same file, so placing this in a
 * builder layout cannot produce different markup from the template it
 * replaces. If the component changes, it changes in one place.
 */
final class Product_Buy_Box extends Widget_Base {

	use Has_Style_Controls;
	use Has_Product_Context;

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
		$this->start_controls_section( 'content', array( 'label' => __( 'Buy box', 'pixelomatic-core' ) ) );

		$this->register_product_notice();

		$this->end_controls_section();

		$this->start_style_section( 'style_card', __( 'Panel', 'pixelomatic-core' ) );

		$this->register_box_style(
			'card',
			__( 'Panel', 'pixelomatic-core' ),
			'{{WRAPPER}} .buy-card',
			array( 'separator' => 'none' )
		);

		$this->register_text_style( 'price', __( 'Price', 'pixelomatic-core' ), '{{WRAPPER}} .buy-card__price .price-now' );

		$this->register_text_style(
			'price_note',
			__( 'Price note', 'pixelomatic-core' ),
			'{{WRAPPER}} .buy-card__price .price-note'
		);

		$this->register_text_style( 'terms', __( 'Terms line', 'pixelomatic-core' ), '{{WRAPPER}} .buy-card__terms' );

		$this->end_controls_section();

		$this->start_style_section( 'style_options', __( 'Licence options', 'pixelomatic-core' ) );

		// The licence radios are the theme's own .license-option, rendered into
		// EDD's form through edd_purchase_link_top. EDD's form markup around
		// them stays EDD's to change, so nothing here reaches into it.
		$this->register_box_style(
			'option',
			__( 'Option row', 'pixelomatic-core' ),
			'{{WRAPPER}} .license-option',
			array(
				'separator' => 'none',
				'shadow'    => false,
			)
		);

		$this->register_text_style( 'option_name', __( 'Option name', 'pixelomatic-core' ), '{{WRAPPER}} .license-option__name' );

		$this->register_text_style( 'option_price', __( 'Option price', 'pixelomatic-core' ), '{{WRAPPER}} .license-option__price' );

		$this->register_text_style(
			'option_desc',
			__( 'Option description', 'pixelomatic-core' ),
			'{{WRAPPER}} .license-option__desc',
			array( 'spacing' => false )
		);

		$this->end_controls_section();

		$this->start_style_section( 'style_buttons', __( 'Buttons', 'pixelomatic-core' ) );

		$this->register_button_style(
			'buy_button',
			__( 'Purchase button', 'pixelomatic-core' ),
			'{{WRAPPER}} .buy-actions .btn--primary',
			array( 'separator' => 'none' )
		);

		$this->register_button_style(
			'demo_button',
			__( 'Demo button', 'pixelomatic-core' ),
			'{{WRAPPER}} .buy-actions .btn--secondary'
		);

		$this->register_text_style(
			'demo_note',
			__( 'Demo note', 'pixelomatic-core' ),
			'{{WRAPPER}} .demo-note',
			array( 'spacing' => false )
		);

		$this->register_gap_style( 'actions_gap', __( 'Gap between actions', 'pixelomatic-core' ), '{{WRAPPER}} .buy-actions', 40 );

		$this->end_controls_section();
	}

	/**
	 * Renders the widget.
	 *
	 * @return void
	 */
	protected function render(): void {
		$this->render_product_part( 'buy-box' );
	}
}
