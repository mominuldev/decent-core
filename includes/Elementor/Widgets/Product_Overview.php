<?php
/**
 * Product Overview widget.
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
 * The product's own post content, as the Overview section.
 *
 * A delegate, deliberately. The theme owns
 * template-parts/product/section-overview.php and the single-product template
 * renders the same file, so placing this in a builder layout cannot produce
 * different markup from the template it replaces.
 */
final class Product_Overview extends Widget_Base {

	use Has_Style_Controls;
	use Has_Product_Context;

	/**
	 * Widget slug.
	 *
	 * @return string
	 */
	public static function slug(): string {
		return 'product-overview';
	}

	/**
	 * Registers controls.
	 *
	 * @return void
	 */
	protected function register_controls(): void {
		$this->start_controls_section( 'content', array( 'label' => __( 'Overview', 'pixelomatic-core' ) ) );

		$this->register_product_notice(
			__( 'The section carries the anchor the Overview link in the section nav points at.', 'pixelomatic-core' )
		);

		$this->register_source_control( __( 'The product\'s own content', 'pixelomatic-core' ) );

		$this->add_control(
			'eyebrow',
			array(
				'label'       => __( 'Eyebrow', 'pixelomatic-core' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Overview', 'pixelomatic-core' ),
				'label_block' => true,
			)
		);

		$this->add_control(
			'text',
			array(
				'label'     => __( 'Copy', 'pixelomatic-core' ),
				'type'      => Controls_Manager::WYSIWYG,
				'default'   => '<p>' . esc_html__( 'Multipurpose agency theme with 32 block patterns and a 98 Lighthouse score.', 'pixelomatic-core' ) . '</p>',
				'condition' => array( 'source' => 'custom' ),
			)
		);

		$this->end_controls_section();

		$this->start_style_section( 'style_section', __( 'Section', 'pixelomatic-core' ) );

		$this->register_box_style(
			'section',
			__( 'Section', 'pixelomatic-core' ),
			'{{WRAPPER}} .detail-section',
			array(
				'separator' => 'none',
				'shadow'    => false,
			)
		);

		$this->register_text_style( 'eyebrow', __( 'Eyebrow', 'pixelomatic-core' ), '{{WRAPPER}} .eyebrow' );

		$this->end_controls_section();

		$this->start_style_section( 'style_body', __( 'Body', 'pixelomatic-core' ) );

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
	}

	/**
	 * Renders the widget.
	 *
	 * @return void
	 */
	protected function render(): void {
		$settings = $this->get_settings_for_display();
		$args     = array( 'eyebrow' => (string) ( $settings['eyebrow'] ?? '' ) );

		if ( $this->is_custom_source() ) {
			// The part tells authored copy from the post's content by the
			// difference between a string and null, so an emptied editor still
			// means "this copy", not "fall back to the product".
			$args['content'] = (string) ( $settings['text'] ?? '' );
		}

		$this->render_product_part( 'section', 'overview', $args );
	}
}
