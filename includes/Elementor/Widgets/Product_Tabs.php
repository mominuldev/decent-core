<?php
/**
 * Product Sections widget.
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
 * Every section the current product has, in page order.
 *
 * A delegate, deliberately. The theme owns template-parts/product/ and its
 * sections.php decides which sections a product has; this widget renders the
 * same parts in the same order as the single-product template, so a builder
 * layout cannot produce a different page body from the template it replaces.
 *
 * The slug is still `product-tabs` — it is the widget's identity on every page
 * already built with it. The component behind it is not a tab strip any more:
 * the theme replaced hidden tab panels with real sections and an anchor bar,
 * because a tab panel is hidden content and a section is content with a
 * shortcut to it. The title moved with that change; the slug cannot.
 *
 * Product Overview, Features, Specifications, Changelog, Reviews and Support
 * render the same sections one at a time, for layouts that want something of
 * their own between them.
 */
final class Product_Tabs extends Widget_Base {

	use Has_Style_Controls;
	use Has_Product_Context;

	/**
	 * Widget slug.
	 *
	 * @return string
	 */
	public static function slug(): string {
		return 'product-tabs';
	}

	/**
	 * Registers controls.
	 *
	 * @return void
	 */
	protected function register_controls(): void {
		$this->start_controls_section( 'content', array( 'label' => __( 'Product sections', 'pixelomatic-core' ) ) );

		$this->register_product_notice(
			__( 'Renders the sections the product itself carries — its overview, its changelog and its reviews — and skips the ones with nothing in them. Features, specifications and support are written in the panel, so place those widgets where you want them.', 'pixelomatic-core' )
		);

		$this->end_controls_section();

		$this->start_style_section( 'style_section', __( 'Sections', 'pixelomatic-core' ) );

		$this->register_box_style(
			'section',
			__( 'Section', 'pixelomatic-core' ),
			'{{WRAPPER}} .detail-section',
			array(
				'separator' => 'none',
				'shadow'    => false,
			)
		);

		$this->register_section_head_style_controls();

		$this->end_controls_section();

		$this->start_style_section( 'style_body', __( 'Overview', 'pixelomatic-core' ) );

		$this->register_text_style(
			'body',
			__( 'Body text', 'pixelomatic-core' ),
			'{{WRAPPER}} .prose',
			array( 'separator' => 'none' )
		);

		$this->register_text_style(
			'body_heading',
			__( 'Headings', 'pixelomatic-core' ),
			'{{WRAPPER}} .prose h2, {{WRAPPER}} .prose h3',
			array( 'spacing' => false )
		);

		$this->register_link_style( 'body_link', __( 'Links', 'pixelomatic-core' ), '{{WRAPPER}} .prose a' );

		$this->end_controls_section();

		$this->start_style_section( 'style_cards', __( 'Feature and spec cards', 'pixelomatic-core' ) );

		$this->register_box_style(
			'feature_card',
			__( 'Feature card', 'pixelomatic-core' ),
			'{{WRAPPER}} .feature-card',
			array( 'separator' => 'none' )
		);

		$this->register_text_style( 'feature_title', __( 'Feature title', 'pixelomatic-core' ), '{{WRAPPER}} .feature-card__title' );

		$this->register_box_style(
			'spec_card',
			__( 'Spec card', 'pixelomatic-core' ),
			'{{WRAPPER}} .spec-card'
		);

		$this->register_text_style( 'spec_title', __( 'Spec group title', 'pixelomatic-core' ), '{{WRAPPER}} .spec-card__title' );

		$this->register_text_style(
			'spec_row',
			__( 'Spec rows', 'pixelomatic-core' ),
			'{{WRAPPER}} .spec-card__row dt, {{WRAPPER}} .spec-card__row dd',
			array( 'spacing' => false )
		);

		$this->end_controls_section();

		$this->start_style_section( 'style_changelog', __( 'Changelog', 'pixelomatic-core' ) );

		$this->register_box_style(
			'changelog_entry',
			__( 'Release', 'pixelomatic-core' ),
			'{{WRAPPER}} .changelog__entry',
			array(
				'separator' => 'none',
				'shadow'    => false,
			)
		);

		$this->register_text_style( 'changelog_version', __( 'Version', 'pixelomatic-core' ), '{{WRAPPER}} .changelog__version' );

		$this->register_text_style( 'changelog_date', __( 'Date', 'pixelomatic-core' ), '{{WRAPPER}} .changelog__date' );

		$this->register_text_style(
			'changelog_items',
			__( 'Entries', 'pixelomatic-core' ),
			'{{WRAPPER}} .changelog__items',
			array( 'spacing' => false )
		);

		$this->end_controls_section();

		$this->start_style_section( 'style_reviews', __( 'Reviews', 'pixelomatic-core' ) );

		$this->register_text_style(
			'review_score',
			__( 'Average score', 'pixelomatic-core' ),
			'{{WRAPPER}} .rating-summary__score',
			array( 'separator' => 'none' )
		);

		$this->register_text_style( 'review_stars', __( 'Stars', 'pixelomatic-core' ), '{{WRAPPER}} .stars' );

		$this->add_control(
			'review_bar_color',
			array(
				'label'     => __( 'Rating bar colour', 'pixelomatic-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .rating-bars__fill' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->register_box_style(
			'review_card',
			__( 'Review', 'pixelomatic-core' ),
			'{{WRAPPER}} .detail-review',
			array( 'shadow' => false )
		);

		$this->register_text_style( 'review_quote', __( 'Review text', 'pixelomatic-core' ), '{{WRAPPER}} .detail-review__quote' );

		$this->register_text_style(
			'review_meta',
			__( 'Review meta', 'pixelomatic-core' ),
			'{{WRAPPER}} .detail-review__author, {{WRAPPER}} .detail-review__date',
			array( 'spacing' => false )
		);

		$this->end_controls_section();

		$this->start_style_section( 'style_faq', __( 'Support', 'pixelomatic-core' ) );

		$this->register_text_style(
			'faq_question',
			__( 'Question', 'pixelomatic-core' ),
			'{{WRAPPER}} .accordion__trigger',
			array( 'separator' => 'none' )
		);

		$this->register_text_style( 'faq_answer', __( 'Answer', 'pixelomatic-core' ), '{{WRAPPER}} .accordion__panel-inner' );

		$this->register_link_style( 'faq_link', __( 'Ticket link', 'pixelomatic-core' ), '{{WRAPPER}} .faq-layout__link' );

		$this->end_controls_section();
	}

	/**
	 * Renders the widget.
	 *
	 * @return void
	 */
	protected function render(): void {
		$this->with_product(
			function (): void {
				foreach ( array_keys( $this->product_sections() ) as $key ) {
					// One template part per section, named for its key.
					// sections.php has already established that each one has
					// content, so no part here has to re-check.
					get_template_part( 'template-parts/product/section', (string) $key );
				}
			}
		);
	}
}
