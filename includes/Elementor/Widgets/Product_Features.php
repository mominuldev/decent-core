<?php
/**
 * Product Features widget.
 *
 * @package PixelomaticCore
 */

namespace PixelomaticCore\Elementor\Widgets;

defined( 'ABSPATH' ) || exit;

use PixelomaticCore\Elementor\Base\Traits\Has_Product_Context;
use PixelomaticCore\Elementor\Base\Traits\Has_Section_Head;
use PixelomaticCore\Elementor\Base\Traits\Has_Style_Controls;
use PixelomaticCore\Elementor\Base\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Repeater;

/**
 * The current product's feature cards.
 *
 * A delegate, deliberately. The theme owns
 * template-parts/product/section-features.php and the single-product template
 * renders the same file, so placing this in a builder layout cannot produce
 * different markup from the template it replaces. The cards are the theme's
 * `.feature-card`, so they match the ones on the landing page.
 */
final class Product_Features extends Widget_Base {

	use Has_Style_Controls;
	use Has_Product_Context;
	use Has_Section_Head;

	/**
	 * Widget slug.
	 *
	 * @return string
	 */
	public static function slug(): string {
		return 'product-features';
	}

	/**
	 * Registers controls.
	 *
	 * @return void
	 */
	protected function register_controls(): void {
		$this->start_controls_section( 'content', array( 'label' => __( 'Features', 'pixelomatic-core' ) ) );

		$this->register_product_notice();

		$this->register_section_head_controls(
			__( 'Everything the product ships with', 'pixelomatic-core' ),
			__( 'Features', 'pixelomatic-core' ),
			__( 'No add-ons to buy and no demo content that only works on our server. Every layout below is in the download.', 'pixelomatic-core' )
		);

		$repeater = new Repeater();

		// A slug from the theme's set rather than Elementor's icon picker: the
		// card is the theme's, and template-parts/product/section-features.php
		// renders its icon through the theme's own map. A picked icon would
		// have to be rendered here instead, which is the fork this widget
		// exists to avoid.
		$repeater->add_control(
			'icon',
			array(
				'label'   => __( 'Icon', 'pixelomatic-core' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'check',
				'options' => self::icon_options(),
			)
		);

		$repeater->add_control(
			'title',
			array(
				'label'       => __( 'Title', 'pixelomatic-core' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Feature', 'pixelomatic-core' ),
				'label_block' => true,
			)
		);

		$repeater->add_control(
			'text',
			array(
				'label'       => __( 'Text', 'pixelomatic-core' ),
				'type'        => Controls_Manager::TEXTAREA,
				'rows'        => 3,
				'label_block' => true,
			)
		);

		$this->add_control(
			'features',
			array(
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'title_field' => '{{{ title }}}',
				'default'     => array(
					array(
						'icon'  => 'layout',
						'title' => __( '12 demo layouts', 'pixelomatic-core' ),
						'text'  => __( 'Import a complete site in one click, then swap sections without touching a template file.', 'pixelomatic-core' ),
					),
					array(
						'icon'  => 'grid',
						'title' => __( '32 block patterns', 'pixelomatic-core' ),
						'text'  => __( 'Patterns for hero, pricing, team and case-study sections, built on core blocks.', 'pixelomatic-core' ),
					),
					array(
						'icon'  => 'cart',
						'title' => __( 'WooCommerce ready', 'pixelomatic-core' ),
						'text'  => __( 'Shop, product, cart and checkout templates styled to match every screen.', 'pixelomatic-core' ),
					),
				),
			)
		);

		$this->end_controls_section();

		$this->start_style_section( 'style_head', __( 'Section head', 'pixelomatic-core' ) );

		$this->register_section_head_style_controls();

		$this->end_controls_section();

		$this->start_style_section( 'style_grid', __( 'Grid', 'pixelomatic-core' ) );

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
					'{{WRAPPER}} .feature-grid' => 'grid-template-columns: repeat({{VALUE}}, minmax(0, 1fr));',
				),
			)
		);

		$this->register_gap_style( 'grid_gap', __( 'Gap', 'pixelomatic-core' ), '{{WRAPPER}} .feature-grid', 56 );

		$this->register_box_style(
			'section',
			__( 'Section', 'pixelomatic-core' ),
			'{{WRAPPER}} .detail-section',
			array( 'shadow' => false )
		);

		$this->end_controls_section();

		$this->start_style_section( 'style_card', __( 'Card', 'pixelomatic-core' ) );

		$this->register_box_style(
			'card',
			__( 'Card', 'pixelomatic-core' ),
			'{{WRAPPER}} .feature-card',
			array( 'separator' => 'none' )
		);

		$this->register_icon_style(
			'card_icon',
			__( 'Icon', 'pixelomatic-core' ),
			'{{WRAPPER}} .feature-card__icon'
		);

		$this->register_text_style( 'card_title', __( 'Title', 'pixelomatic-core' ), '{{WRAPPER}} .feature-card__title' );

		$this->register_text_style(
			'card_text',
			__( 'Text', 'pixelomatic-core' ),
			'{{WRAPPER}} .feature-card p',
			array( 'spacing' => false )
		);

		$this->end_controls_section();
	}

	/**
	 * Renders the widget.
	 *
	 * @return void
	 */
	protected function render(): void {
		$args          = $this->head_args();
		$args['items'] = $this->items();

		$this->render_product_part( 'section', 'features', $args );
	}

	/**
	 * The cards as the theme's part takes them.
	 *
	 * @return array<int, array{icon:string, title:string, text:string}>
	 */
	private function items(): array {
		$rows  = (array) $this->get_settings_for_display( 'features' );
		$items = array();

		foreach ( $rows as $row ) {
			$row = (array) $row;

			if ( '' === trim( (string) ( $row['title'] ?? '' ) ) ) {
				continue;
			}

			$items[] = array(
				'icon'  => (string) ( $row['icon'] ?? 'check' ),
				'title' => (string) $row['title'],
				'text'  => (string) ( $row['text'] ?? '' ),
			);
		}

		return $items;
	}
}
