<?php
/**
 * Product Changelog widget.
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
 * The current product's releases, newest first.
 *
 * A delegate, deliberately. The theme owns
 * template-parts/product/section-changelog.php and the single-product template
 * renders the same file, so placing this in a builder layout cannot produce
 * different markup from the template it replaces.
 */
final class Product_Changelog extends Widget_Base {

	use Has_Style_Controls;
	use Has_Product_Context;
	use Has_Section_Head;

	/**
	 * Widget slug.
	 *
	 * @return string
	 */
	public static function slug(): string {
		return 'product-changelog';
	}

	/**
	 * Registers controls.
	 *
	 * @return void
	 */
	protected function register_controls(): void {
		$this->start_controls_section( 'content', array( 'label' => __( 'Changelog', 'pixelomatic-core' ) ) );

		$this->register_product_notice(
			__( 'The releases are what licensing recorded, so they are never written here — a changelog anyone can type is not a changelog. The heading and the link are yours.', 'pixelomatic-core' )
		);

		$this->register_section_head_controls(
			__( 'Shipped, not promised', 'pixelomatic-core' ),
			__( 'Changelog', 'pixelomatic-core' )
		);

		$this->add_control(
			'link_label',
			array(
				'label'       => __( 'Trailing link text', 'pixelomatic-core' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Full changelog', 'pixelomatic-core' ),
				'label_block' => true,
			)
		);

		$this->add_control(
			'link',
			array(
				'label'       => __( 'Trailing link', 'pixelomatic-core' ),
				'type'        => Controls_Manager::URL,
				'description' => __( 'Left empty, the product\'s own documentation URL is used.', 'pixelomatic-core' ),
				'options'     => false,
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

		$this->start_style_section( 'style_entry', __( 'Release', 'pixelomatic-core' ) );

		$this->register_box_style(
			'entry',
			__( 'Release', 'pixelomatic-core' ),
			'{{WRAPPER}} .changelog__entry',
			array(
				'separator' => 'none',
				'shadow'    => false,
			)
		);

		$this->register_text_style( 'version', __( 'Version', 'pixelomatic-core' ), '{{WRAPPER}} .changelog__version' );

		$this->register_text_style( 'date', __( 'Date', 'pixelomatic-core' ), '{{WRAPPER}} .changelog__date' );

		$this->register_box_style(
			'latest_badge',
			__( 'Latest badge', 'pixelomatic-core' ),
			'{{WRAPPER}} .changelog__version .badge',
			array( 'shadow' => false )
		);

		$this->register_gap_style( 'entries_gap', __( 'Gap between releases', 'pixelomatic-core' ), '{{WRAPPER}} .changelog', 64 );

		$this->end_controls_section();

		$this->start_style_section( 'style_items', __( 'Entries', 'pixelomatic-core' ) );

		$this->register_text_style(
			'items',
			__( 'Entry text', 'pixelomatic-core' ),
			'{{WRAPPER}} .changelog__items',
			array( 'separator' => 'none' )
		);

		$this->register_text_style(
			'kind',
			__( 'Kind badge', 'pixelomatic-core' ),
			'{{WRAPPER}} .changelog__kind',
			array( 'spacing' => false )
		);

		// One colour per kind: the badge is what tells a returning buyer
		// whether a release is a fix or a feature, so each keeps its own.
		$kinds = array(
			'new'      => __( 'New', 'pixelomatic-core' ),
			'improved' => __( 'Improved', 'pixelomatic-core' ),
			'fix'      => __( 'Fix', 'pixelomatic-core' ),
			'security' => __( 'Security', 'pixelomatic-core' ),
		);

		foreach ( $kinds as $kind => $label ) {
			$this->add_control(
				'kind_' . $kind . '_background',
				array(
					/* translators: %s: kind of change, such as New or Fix. */
					'label'     => sprintf( __( '%s background', 'pixelomatic-core' ), $label ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array(
						'{{WRAPPER}} .changelog__kind--' . $kind => 'background-color: {{VALUE}};',
					),
				)
			);
		}

		$this->end_controls_section();
	}

	/**
	 * Renders the widget.
	 *
	 * @return void
	 */
	protected function render(): void {
		$settings = $this->get_settings_for_display();
		$args     = $this->head_args();

		$args['label'] = (string) ( $settings['link_label'] ?? '' );
		$args['link']  = (string) ( $settings['link']['url'] ?? '' );

		$this->render_product_part( 'section', 'changelog', $args );
	}
}
