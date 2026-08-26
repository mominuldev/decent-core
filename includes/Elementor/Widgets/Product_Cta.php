<?php
/**
 * Product CTA widget.
 *
 * @package PixelomaticCore
 */

namespace PixelomaticCore\Elementor\Widgets;

defined( 'ABSPATH' ) || exit;

use PixelomaticCore\Elementor\Base\Traits\Has_Product_Context;
use PixelomaticCore\Elementor\Base\Traits\Has_Style_Controls;
use PixelomaticCore\Elementor\Base\Widget_Base;
use Elementor\Controls_Manager;

/**
 * The closing call to action for the current product.
 *
 * A delegate, deliberately. The theme owns
 * template-parts/product/section-cta.php and the single-product template
 * renders the same file, so placing this in a builder layout cannot produce
 * different markup from the template it replaces.
 *
 * The buy button carries the price and points back at the hero's `#buy`
 * anchor, because a visitor who has scrolled this far has left the buy card
 * well behind. Place a Product Hero above it, or the anchor has nothing to
 * scroll to.
 */
final class Product_Cta extends Widget_Base {

	use Has_Style_Controls;
	use Has_Product_Context;

	/**
	 * Widget slug.
	 *
	 * @return string
	 */
	public static function slug(): string {
		return 'product-cta';
	}

	/**
	 * Registers controls.
	 *
	 * @return void
	 */
	protected function register_controls(): void {
		$this->start_controls_section( 'content', array( 'label' => __( 'Product CTA', 'pixelomatic-core' ) ) );

		$this->register_product_notice(
			__( 'Only the price is the product\'s. Everything else on this band is written here.', 'pixelomatic-core' )
		);

		$this->add_control(
			'title',
			array(
				'label'       => __( 'Title', 'pixelomatic-core' ),
				'type'        => Controls_Manager::TEXTAREA,
				'rows'        => 2,
				'default'     => __( 'Walk the demo before you spend a penny', 'pixelomatic-core' ),
				'label_block' => true,
			)
		);

		$this->add_control(
			'text',
			array(
				'label'       => __( 'Text', 'pixelomatic-core' ),
				'type'        => Controls_Manager::TEXTAREA,
				'rows'        => 3,
				'default'     => __( 'Every layout on the demo site ships in the download. One-time payment, lifetime updates, 30 days to change your mind.', 'pixelomatic-core' ),
				'label_block' => true,
			)
		);

		$this->add_control(
			'buy_label',
			array(
				'label'       => __( 'Buy button', 'pixelomatic-core' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Buy now', 'pixelomatic-core' ),
				'separator'   => 'before',
				'label_block' => true,
			)
		);

		$this->add_control(
			'buy_link',
			array(
				'label'       => __( 'Buy button target', 'pixelomatic-core' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '#buy',
				'description' => __( 'The hero\'s anchor by default, so the button scrolls back to the buy card.', 'pixelomatic-core' ),
				'label_block' => true,
			)
		);

		$this->add_control(
			'price',
			array(
				'label'        => __( 'Show the price in the button', 'pixelomatic-core' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'demo_label',
			array(
				'label'       => __( 'Demo button', 'pixelomatic-core' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Open live demo', 'pixelomatic-core' ),
				'separator'   => 'before',
				'description' => __( 'Cleared, the second button is left off.', 'pixelomatic-core' ),
				'label_block' => true,
			)
		);

		$this->add_control(
			'demo',
			array(
				'label'       => __( 'Demo link', 'pixelomatic-core' ),
				'type'        => Controls_Manager::URL,
				'description' => __( 'Left empty, the product\'s own demo URL is used.', 'pixelomatic-core' ),
				'options'     => false,
			)
		);

		$this->end_controls_section();

		$this->start_style_section( 'style_band', __( 'Band', 'pixelomatic-core' ) );

		$this->register_box_style(
			'band',
			__( 'Band', 'pixelomatic-core' ),
			'{{WRAPPER}} .detail-cta',
			array(
				'heading'   => false,
				'separator' => 'none',
				'shadow'    => false,
			)
		);

		$this->register_gap_style( 'inner_gap', __( 'Gap between text and actions', 'pixelomatic-core' ), '{{WRAPPER}} .detail-cta__inner', 96 );

		$this->end_controls_section();

		$this->start_style_section( 'style_text', __( 'Text', 'pixelomatic-core' ) );

		$this->register_text_style(
			'title',
			__( 'Title', 'pixelomatic-core' ),
			'{{WRAPPER}} .detail-cta__title',
			array( 'separator' => 'none' )
		);

		$this->register_text_style(
			'text',
			__( 'Text', 'pixelomatic-core' ),
			'{{WRAPPER}} .detail-cta__text',
			array( 'spacing' => false )
		);

		$this->end_controls_section();

		$this->start_style_section( 'style_actions', __( 'Actions', 'pixelomatic-core' ) );

		$this->register_button_style(
			'buy',
			__( 'Buy button', 'pixelomatic-core' ),
			'{{WRAPPER}} .detail-cta__actions .btn--primary',
			array( 'separator' => 'none' )
		);

		$this->register_text_style(
			'price',
			__( 'Price in the button', 'pixelomatic-core' ),
			'{{WRAPPER}} .detail-cta__price',
			array( 'spacing' => false )
		);

		$this->register_button_style(
			'demo',
			__( 'Demo button', 'pixelomatic-core' ),
			'{{WRAPPER}} .detail-cta__actions .btn--white'
		);

		$this->register_gap_style( 'actions_gap', __( 'Gap between buttons', 'pixelomatic-core' ), '{{WRAPPER}} .detail-cta__actions', 40 );

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
			'section-cta',
			null,
			array(
				'title'      => (string) ( $settings['title'] ?? '' ),
				'text'       => (string) ( $settings['text'] ?? '' ),
				'buy_label'  => (string) ( $settings['buy_label'] ?? '' ),
				'buy_link'   => (string) ( $settings['buy_link'] ?? '#buy' ),
				'demo_label' => (string) ( $settings['demo_label'] ?? '' ),
				'demo'       => (string) ( $settings['demo']['url'] ?? '' ),
				'price'      => 'yes' === (string) ( $settings['price'] ?? 'yes' ),
			)
		);
	}
}
