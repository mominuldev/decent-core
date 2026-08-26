<?php
/**
 * Product Section Nav widget.
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
 * The bar of anchor links to the sections below the product hero.
 *
 * A delegate, deliberately. The theme owns template-parts/product/section-nav.php
 * and the single-product template renders the same file, so placing this in a
 * builder layout cannot produce different markup from the template it
 * replaces. If the component changes, it changes in one place.
 *
 * Real anchor links rather than a tablist: the sections below are all
 * rendered, all crawlable and all reachable with JavaScript off.
 */
final class Product_Section_Nav extends Widget_Base {

	use Has_Style_Controls;
	use Has_Product_Context;

	/**
	 * Widget slug.
	 *
	 * @return string
	 */
	public static function slug(): string {
		return 'product-section-nav';
	}

	/**
	 * Registers controls.
	 *
	 * @return void
	 */
	protected function register_controls(): void {
		$this->start_controls_section( 'content', array( 'label' => __( 'Section nav', 'pixelomatic-core' ) ) );

		$this->register_product_notice(
			__( 'Nothing renders when there are fewer than two links.', 'pixelomatic-core' )
		);

		$this->register_source_control( __( 'The sections this product has', 'pixelomatic-core' ) );

		$repeater = new Repeater();

		$repeater->add_control(
			'label',
			array(
				'label'       => __( 'Label', 'pixelomatic-core' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Section', 'pixelomatic-core' ),
				'label_block' => true,
			)
		);

		// The anchor of the section widget below, which each of them prints
		// for itself: overview, features, specifications, changelog, reviews,
		// support. A link to a section that is not on the page scrolls
		// nowhere, so the two lists are worth keeping in step.
		$repeater->add_control(
			'anchor',
			array(
				'label'       => __( 'Anchor', 'pixelomatic-core' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => 'overview',
				'label_block' => true,
			)
		);

		$this->add_control(
			'items',
			array(
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'title_field' => '{{{ label }}}',
				'condition'   => array( 'source' => 'custom' ),
				'default'     => array(
					array(
						'label'  => __( 'Overview', 'pixelomatic-core' ),
						'anchor' => 'overview',
					),
					array(
						'label'  => __( 'Features', 'pixelomatic-core' ),
						'anchor' => 'features',
					),
					array(
						'label'  => __( 'Specifications', 'pixelomatic-core' ),
						'anchor' => 'specifications',
					),
					array(
						'label'  => __( 'Changelog', 'pixelomatic-core' ),
						'anchor' => 'changelog',
					),
					array(
						'label'  => __( 'Reviews', 'pixelomatic-core' ),
						'anchor' => 'reviews',
					),
					array(
						'label'  => __( 'Support', 'pixelomatic-core' ),
						'anchor' => 'support',
					),
				),
			)
		);

		$this->end_controls_section();

		$this->start_style_section( 'style_bar', __( 'Bar', 'pixelomatic-core' ) );

		$this->register_box_style(
			'bar',
			__( 'Bar', 'pixelomatic-core' ),
			'{{WRAPPER}} .section-nav',
			array(
				'separator' => 'none',
				'shadow'    => false,
			)
		);

		$this->register_gap_style( 'links_gap', __( 'Gap between links', 'pixelomatic-core' ), '{{WRAPPER}} .section-nav__inner', 64 );

		$this->register_alignment_flex_style( 'links_align', '{{WRAPPER}} .section-nav__inner', __( 'Alignment', 'pixelomatic-core' ) );

		$this->end_controls_section();

		$this->start_style_section( 'style_links', __( 'Links', 'pixelomatic-core' ) );

		$this->register_link_style(
			'link',
			__( 'Link', 'pixelomatic-core' ),
			'{{WRAPPER}} .section-nav__link'
		);

		// The current section is marked with aria-current rather than a class,
		// so the accessible state and the styled state cannot drift apart.
		$this->add_control(
			'link_color_current',
			array(
				'label'     => __( 'Current section colour', 'pixelomatic-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .section-nav__link[aria-current]' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'link_underline_current',
			array(
				'label'     => __( 'Current section underline', 'pixelomatic-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .section-nav__link[aria-current]' => 'border-bottom-color: {{VALUE}};',
				),
			)
		);

		$this->register_text_style(
			'link_count',
			__( 'Count', 'pixelomatic-core' ),
			'{{WRAPPER}} .section-nav__count',
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
		$args = array();

		if ( $this->is_custom_source() ) {
			$args['items'] = $this->items();
		}

		$this->render_product_part( 'section-nav', null, $args );
	}

	/**
	 * The links as the theme's part takes them.
	 *
	 * @return array<int, array{anchor:string, label:string}>
	 */
	private function items(): array {
		$rows  = (array) $this->get_settings_for_display( 'items' );
		$items = array();

		foreach ( $rows as $row ) {
			$row    = (array) $row;
			$label  = trim( (string) ( $row['label'] ?? '' ) );
			$anchor = trim( (string) ( $row['anchor'] ?? '' ) );

			if ( '' === $label || '' === $anchor ) {
				continue;
			}

			$items[] = array(
				'anchor' => ltrim( $anchor, '#' ),
				'label'  => $label,
			);
		}

		return $items;
	}
}
