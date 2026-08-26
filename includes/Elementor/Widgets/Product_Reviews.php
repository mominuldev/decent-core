<?php
/**
 * Product Reviews widget.
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

/**
 * The current product's rating summary and its latest reviews.
 *
 * A delegate, deliberately. The theme owns
 * template-parts/product/section-reviews.php and the single-product template
 * renders the same file, so placing this in a builder layout cannot produce
 * different markup from the template it replaces. The theme reads reviews
 * through its own provider, so this renders the same whether they come from
 * EDD Reviews or from comments.
 */
final class Product_Reviews extends Widget_Base {

	use Has_Style_Controls;
	use Has_Product_Context;
	use Has_Section_Head;

	/**
	 * Widget slug.
	 *
	 * @return string
	 */
	public static function slug(): string {
		return 'product-reviews';
	}

	/**
	 * Registers controls.
	 *
	 * @return void
	 */
	protected function register_controls(): void {
		$this->start_controls_section( 'content', array( 'label' => __( 'Reviews', 'pixelomatic-core' ) ) );

		$this->register_product_notice(
			__( 'The scores and the reviews are what buyers wrote, so they are never written here. Nothing renders until the product has its first one.', 'pixelomatic-core' )
		);

		$this->register_section_head_controls( '', __( 'Reviews', 'pixelomatic-core' ) );

		$this->update_control(
			'title',
			array( 'description' => __( 'Left empty, the heading counts the reviews — "412 verified purchases, 98% positive".', 'pixelomatic-core' ) )
		);

		$this->add_control(
			'number',
			array(
				'label'   => __( 'Reviews to show', 'pixelomatic-core' ),
				'type'    => Controls_Manager::NUMBER,
				'min'     => 1,
				'max'     => 12,
				'default' => 4,
			)
		);

		$this->end_controls_section();

		$this->start_style_section( 'style_head', __( 'Section head', 'pixelomatic-core' ) );

		$this->register_section_head_style_controls();

		$this->register_box_style(
			'section',
			__( 'Section', 'pixelomatic-core' ),
			'{{WRAPPER}} .detail-section',
			array( 'shadow' => false )
		);

		$this->end_controls_section();

		$this->start_style_section( 'style_summary', __( 'Rating summary', 'pixelomatic-core' ) );

		$this->register_box_style(
			'summary',
			__( 'Card', 'pixelomatic-core' ),
			'{{WRAPPER}} .rating-summary',
			array( 'separator' => 'none' )
		);

		$this->register_text_style( 'score', __( 'Average score', 'pixelomatic-core' ), '{{WRAPPER}} .rating-summary__score' );

		$this->register_text_style( 'summary_stars', __( 'Stars', 'pixelomatic-core' ), '{{WRAPPER}} .rating-summary__stars' );

		$this->register_text_style( 'summary_count', __( 'Count line', 'pixelomatic-core' ), '{{WRAPPER}} .rating-summary__count' );

		$this->add_control(
			'bar_color',
			array(
				'label'     => __( 'Rating bar colour', 'pixelomatic-core' ),
				'type'      => Controls_Manager::COLOR,
				'separator' => 'before',
				'selectors' => array(
					'{{WRAPPER}} .rating-bars__fill' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'bar_track_color',
			array(
				'label'     => __( 'Rating bar track', 'pixelomatic-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .rating-bars__track' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_section();

		$this->start_style_section( 'style_reviews', __( 'Reviews', 'pixelomatic-core' ) );

		$this->add_responsive_control(
			'columns',
			array(
				'label'     => __( 'Columns', 'pixelomatic-core' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => array(
					''  => __( 'Theme default', 'pixelomatic-core' ),
					'1' => '1',
					'2' => '2',
				),
				'selectors' => array(
					'{{WRAPPER}} .review-grid' => 'grid-template-columns: repeat({{VALUE}}, minmax(0, 1fr));',
				),
			)
		);

		$this->register_gap_style( 'reviews_gap', __( 'Gap', 'pixelomatic-core' ), '{{WRAPPER}} .review-grid', 48 );

		$this->register_box_style(
			'review',
			__( 'Review', 'pixelomatic-core' ),
			'{{WRAPPER}} .detail-review',
			array( 'shadow' => false )
		);

		$this->register_text_style( 'review_stars', __( 'Stars', 'pixelomatic-core' ), '{{WRAPPER}} .detail-review .stars' );

		$this->register_text_style( 'review_quote', __( 'Review text', 'pixelomatic-core' ), '{{WRAPPER}} .detail-review__quote' );

		$this->register_text_style(
			'review_meta',
			__( 'Author and date', 'pixelomatic-core' ),
			'{{WRAPPER}} .detail-review__author, {{WRAPPER}} .detail-review__date',
			array( 'spacing' => false )
		);

		$this->register_box_style(
			'avatar',
			__( 'Avatar', 'pixelomatic-core' ),
			'{{WRAPPER}} .detail-review .avatar',
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
		$args = $this->head_args();

		$args['number'] = (int) $this->get_settings_for_display( 'number' );

		$this->render_product_part( 'section', 'reviews', $args );
	}
}
