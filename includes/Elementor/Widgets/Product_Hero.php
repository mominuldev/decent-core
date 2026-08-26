<?php
/**
 * Product Hero widget.
 *
 * @package PixelomaticCore
 */

namespace PixelomaticCore\Elementor\Widgets;

defined( 'ABSPATH' ) || exit;

use PixelomaticCore\Elementor\Base\Traits\Has_Product_Context;
use PixelomaticCore\Elementor\Base\Traits\Has_Style_Controls;
use PixelomaticCore\Elementor\Base\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Repeater;

/**
 * Breadcrumb, gallery and buy column for the current product.
 *
 * A delegate, deliberately. The theme owns template-parts/product/hero.php
 * and the single-product template renders the same file, so placing this in a
 * builder layout cannot produce different markup from the template it
 * replaces. If the component changes, it changes in one place.
 *
 * The whole hero, not just its left column: the title and the rating live
 * inside the buy card rather than in a band above it, so the part renders the
 * gallery, the headline figures, the "built for" tags, the "in the box" list
 * and the buy card together. Product Buy Box exists for layouts that want the
 * card on its own.
 */
final class Product_Hero extends Widget_Base {

	use Has_Style_Controls;
	use Has_Product_Context;

	/**
	 * Widget slug.
	 *
	 * @return string
	 */
	public static function slug(): string {
		return 'product-hero';
	}

	/**
	 * Registers controls.
	 *
	 * @return void
	 */
	protected function register_controls(): void {
		$this->start_controls_section( 'content', array( 'label' => __( 'Product hero', 'pixelomatic-core' ) ) );

		$this->register_product_notice(
			__( 'Carries the #buy anchor the closing call to action scrolls back to. The gallery, the price, the licences and the rating are the product\'s own and are never written here.', 'pixelomatic-core' )
		);

		foreach ( self::blocks() as $block => $label ) {
			$this->add_control(
				$block,
				array(
					'label'        => $label,
					'type'         => Controls_Manager::SWITCHER,
					'default'      => 'yes',
					'return_value' => 'yes',
				)
			);
		}

		$this->end_controls_section();

		$this->start_controls_section(
			'stats_content',
			array(
				'label'     => __( 'Headline figures', 'pixelomatic-core' ),
				'condition' => array( 'stats' => 'yes' ),
			)
		);

		$repeater = new Repeater();

		$repeater->add_control(
			'value',
			array(
				'label'   => __( 'Figure', 'pixelomatic-core' ),
				'type'    => Controls_Manager::TEXT,
				'default' => '12',
			)
		);

		$repeater->add_control(
			'label',
			array(
				'label'       => __( 'Label', 'pixelomatic-core' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'demo layouts', 'pixelomatic-core' ),
				'label_block' => true,
			)
		);

		$this->add_control(
			'stats_items',
			array(
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'title_field' => '{{{ value }}} {{{ label }}}',
				'default'     => array(
					array(
						'value' => '12',
						'label' => __( 'demo layouts', 'pixelomatic-core' ),
					),
					array(
						'value' => '32',
						'label' => __( 'block patterns', 'pixelomatic-core' ),
					),
					array(
						'value' => '98',
						'label' => __( 'Lighthouse score', 'pixelomatic-core' ),
					),
					array(
						'value' => 'AA',
						'label' => __( 'WCAG 2.2 level', 'pixelomatic-core' ),
					),
				),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'built_for_content',
			array(
				'label'     => __( 'Built for', 'pixelomatic-core' ),
				'condition' => array( 'built_for' => 'yes' ),
			)
		);

		$this->add_control(
			'built_for_label',
			array(
				'label'       => __( 'Label', 'pixelomatic-core' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Built for', 'pixelomatic-core' ),
				'label_block' => true,
			)
		);

		$this->add_control(
			'built_for_items',
			array(
				'label'       => __( 'Tags', 'pixelomatic-core' ),
				'type'        => Controls_Manager::TEXTAREA,
				'rows'        => 5,
				'description' => __( 'One tag per line.', 'pixelomatic-core' ),
				'default'     => "WordPress 6.4+\nGutenberg\nElementor\nWooCommerce\nACF Pro",
				'label_block' => true,
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'box_content',
			array(
				'label'     => __( 'In the box', 'pixelomatic-core' ),
				'condition' => array( 'in_the_box' => 'yes' ),
			)
		);

		$this->add_control(
			'box_title',
			array(
				'label'       => __( 'Title', 'pixelomatic-core' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'In the box', 'pixelomatic-core' ),
				'label_block' => true,
			)
		);

		$this->add_control(
			'box_items',
			array(
				'label'       => __( 'Lines', 'pixelomatic-core' ),
				'type'        => Controls_Manager::TEXTAREA,
				'rows'        => 6,
				'description' => __( 'One line per tick.', 'pixelomatic-core' ),
				'default'     => "12 demo layouts with one-click import\n32 Gutenberg block patterns\nWooCommerce shop templates\nFigma source file included\nChild theme and starter content\n6 months of priority support",
				'label_block' => true,
			)
		);

		$this->end_controls_section();

		$this->start_style_section( 'style_band', __( 'Band', 'pixelomatic-core' ) );

		$this->register_box_style(
			'band',
			__( 'Band', 'pixelomatic-core' ),
			'{{WRAPPER}} .section',
			array(
				'heading'   => false,
				'separator' => 'none',
				'shadow'    => false,
			)
		);

		$this->register_gap_style( 'row_gap', __( 'Column gap', 'pixelomatic-core' ), '{{WRAPPER}} .detail-hero__row', 96 );

		$this->register_link_style( 'breadcrumb', __( 'Breadcrumb links', 'pixelomatic-core' ), '{{WRAPPER}} .breadcrumb a' );

		$this->end_controls_section();

		$this->start_style_section( 'style_gallery', __( 'Gallery', 'pixelomatic-core' ) );

		$this->register_box_style(
			'shot',
			__( 'Stage', 'pixelomatic-core' ),
			'{{WRAPPER}} .detail-shot',
			array( 'separator' => 'none' )
		);

		$this->register_box_style(
			'shot_badge',
			__( 'Badge', 'pixelomatic-core' ),
			'{{WRAPPER}} .detail-shot__badge',
			array( 'shadow' => false )
		);

		$this->register_text_style(
			'shot_badge_text',
			__( 'Badge text', 'pixelomatic-core' ),
			'{{WRAPPER}} .detail-shot__badge',
			array(
				'heading' => false,
				'spacing' => false,
			)
		);

		$this->register_box_style(
			'thumb',
			__( 'Thumbnail', 'pixelomatic-core' ),
			'{{WRAPPER}} .gallery-thumbs__frame',
			array( 'shadow' => false )
		);

		$this->register_gap_style( 'thumbs_gap', __( 'Gap between thumbnails', 'pixelomatic-core' ), '{{WRAPPER}} .gallery-thumbs', 32 );

		$this->end_controls_section();

		$this->start_style_section( 'style_stats', __( 'Headline figures', 'pixelomatic-core' ) );

		$this->register_box_style(
			'stats',
			__( 'Strip', 'pixelomatic-core' ),
			'{{WRAPPER}} .stat-strip',
			array( 'separator' => 'none' )
		);

		$this->register_text_style( 'stat_value', __( 'Figure', 'pixelomatic-core' ), '{{WRAPPER}} .stat-strip__item strong' );

		$this->register_text_style(
			'stat_label',
			__( 'Label', 'pixelomatic-core' ),
			'{{WRAPPER}} .stat-strip__item span',
			array( 'spacing' => false )
		);

		$this->end_controls_section();

		$this->start_style_section( 'style_tags', __( 'Built for', 'pixelomatic-core' ) );

		$this->register_text_style(
			'built_for_label',
			__( 'Label', 'pixelomatic-core' ),
			'{{WRAPPER}} .built-for__label',
			array( 'separator' => 'none' )
		);

		$this->register_box_style(
			'tag',
			__( 'Tag', 'pixelomatic-core' ),
			'{{WRAPPER}} .built-for .tag',
			array( 'shadow' => false )
		);

		$this->register_text_style(
			'tag_text',
			__( 'Tag text', 'pixelomatic-core' ),
			'{{WRAPPER}} .built-for .tag',
			array(
				'heading' => false,
				'spacing' => false,
			)
		);

		$this->register_gap_style( 'tags_gap', __( 'Gap between tags', 'pixelomatic-core' ), '{{WRAPPER}} .built-for .tag-list', 24 );

		$this->end_controls_section();

		$this->start_style_section( 'style_box', __( 'In the box', 'pixelomatic-core' ) );

		$this->register_box_style(
			'box',
			__( 'Card', 'pixelomatic-core' ),
			'{{WRAPPER}} .box-list',
			array( 'separator' => 'none' )
		);

		$this->register_text_style( 'box_title', __( 'Title', 'pixelomatic-core' ), '{{WRAPPER}} .box-list__title' );

		$this->register_text_style(
			'box_items',
			__( 'Items', 'pixelomatic-core' ),
			'{{WRAPPER}} .box-list__items',
			array( 'spacing' => false )
		);

		$this->add_control(
			'box_tick_color',
			array(
				'label'     => __( 'Tick colour', 'pixelomatic-core' ),
				'type'      => Controls_Manager::COLOR,
				'separator' => 'before',
				'selectors' => array(
					'{{WRAPPER}} .box-list__items .tick' => 'color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_section();

		$this->start_style_section( 'style_buy', __( 'Buy card', 'pixelomatic-core' ) );

		$this->register_box_style(
			'buy_card',
			__( 'Card', 'pixelomatic-core' ),
			'{{WRAPPER}} .buy-card',
			array( 'separator' => 'none' )
		);

		$this->register_text_style( 'buy_type', __( 'Product type', 'pixelomatic-core' ), '{{WRAPPER}} .buy-card__type' );

		$this->register_text_style( 'buy_title', __( 'Title', 'pixelomatic-core' ), '{{WRAPPER}} .buy-card__title' );

		$this->register_text_style( 'buy_stars', __( 'Rating line', 'pixelomatic-core' ), '{{WRAPPER}} .rating-line' );

		$this->register_text_style( 'buy_excerpt', __( 'Excerpt', 'pixelomatic-core' ), '{{WRAPPER}} .buy-card__excerpt' );

		$this->register_button_style(
			'buy_button',
			__( 'Buy button', 'pixelomatic-core' ),
			'{{WRAPPER}} .buy-actions .btn--primary'
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
		$args     = array();

		foreach ( array_keys( self::blocks() ) as $block ) {
			$args[ $block ] = 'yes' === (string) ( $settings[ $block ] ?? 'yes' );
		}

		$args['stats_args']     = $this->stats_args();
		$args['built_for_args'] = $this->built_for_args();
		$args['box_args']       = $this->box_args();

		// The band and the anchor come from the single-product template rather
		// than from hero.php, so a builder page has to supply them itself or
		// the hero loses its background and the closing call to action's "Buy
		// now" scrolls to nothing. The classes are the theme's own, printed
		// here exactly as single-download.php prints them.
		echo '<section class="section section--alt section--bordered-bottom" id="buy">';

		$this->render_product_part( 'hero', null, $args );

		echo '</section>';
	}

	/**
	 * The blocks of the hero that can be switched off, and their labels.
	 *
	 * @return array<string, string>
	 */
	private static function blocks(): array {
		return array(
			'breadcrumb' => __( 'Breadcrumb', 'pixelomatic-core' ),
			'gallery'    => __( 'Gallery', 'pixelomatic-core' ),
			'stats'      => __( 'Headline figures', 'pixelomatic-core' ),
			'built_for'  => __( 'Built for', 'pixelomatic-core' ),
			'in_the_box' => __( 'In the box', 'pixelomatic-core' ),
			'buy_box'    => __( 'Buy card', 'pixelomatic-core' ),
		);
	}

	/**
	 * Arguments for the headline figures.
	 *
	 * @return array<string, mixed>
	 */
	private function stats_args(): array {
		$items = array();

		foreach ( (array) $this->get_settings_for_display( 'stats_items' ) as $row ) {
			$row   = (array) $row;
			$value = trim( (string) ( $row['value'] ?? '' ) );

			if ( '' === $value ) {
				continue;
			}

			$items[] = array(
				'value' => $value,
				'label' => (string) ( $row['label'] ?? '' ),
			);
		}

		return array( 'items' => $items );
	}

	/**
	 * Arguments for the compatibility tags.
	 *
	 * @return array<string, mixed>
	 */
	private function built_for_args(): array {
		return array(
			'label' => (string) $this->get_settings_for_display( 'built_for_label' ),
			'items' => self::lines( (string) $this->get_settings_for_display( 'built_for_items' ) ),
		);
	}

	/**
	 * Arguments for the "in the box" card.
	 *
	 * @return array<string, mixed>
	 */
	private function box_args(): array {
		return array(
			'title' => (string) $this->get_settings_for_display( 'box_title' ),
			'items' => self::lines( (string) $this->get_settings_for_display( 'box_items' ) ),
		);
	}
}
