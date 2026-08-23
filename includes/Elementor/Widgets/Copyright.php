<?php
/**
 * Copyright widget.
 *
 * @package PixelomaticCore
 */

namespace PixelomaticCore\Elementor\Widgets;

defined( 'ABSPATH' ) || exit;

use PixelomaticCore\Elementor\Base\Traits\Has_Style_Controls;
use PixelomaticCore\Elementor\Base\Widget_Base;
use Elementor\Controls_Manager;

/**
 * A copyright line whose year does not go stale.
 */
final class Copyright extends Widget_Base {

	use Has_Style_Controls;

	/**
	 * Widget slug.
	 *
	 * @return string
	 */
	public static function slug(): string {
		return 'copyright';
	}

	/**
	 * Registers controls.
	 *
	 * @return void
	 */
	protected function register_controls(): void {
		$this->start_controls_section( 'content', array( 'label' => __( 'Copyright', 'pixelomatic-core' ) ) );

		$this->add_control(
			'text',
			array(
				'label'       => __( 'Text', 'pixelomatic-core' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( '&copy; {year} {site}. All rights reserved.', 'pixelomatic-core' ),
				'description' => __( '{year} and {site} are replaced when the page renders, so a hard-coded year cannot go stale in January.', 'pixelomatic-core' ),
				'label_block' => true,
			)
		);

		$this->end_controls_section();

		$this->start_style_section( 'style_text', __( 'Copyright', 'pixelomatic-core' ) );

		$this->register_text_style(
			'copyright',
			__( 'Text', 'pixelomatic-core' ),
			'{{WRAPPER}} .footer-copyright',
			array(
				'separator' => 'none',
				'align'     => true,
				'spacing'   => false,
			)
		);

		// The text runs through wp_kses_post, so it may carry a link.
		$this->register_link_style( 'copyright_link', __( 'Links', 'pixelomatic-core' ), '{{WRAPPER}} .footer-copyright a' );

		$this->register_box_style(
			'copyright_box',
			__( 'Box', 'pixelomatic-core' ),
			'{{WRAPPER}} .footer-copyright',
			array(
				'shadow' => false,
				'margin' => true,
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Renders the widget.
	 *
	 * @return void
	 */
	protected function render(): void {
		$text = $this->text( 'text' );

		if ( '' === $text ) {
			return;
		}

		$text = strtr(
			$text,
			array(
				// wp_date, not gmdate: the year should turn over on the site's
				// own new year, not UTC's.
				'{year}' => wp_date( 'Y' ),
				'{site}' => get_bloginfo( 'name' ),
			)
		);

		printf( '<p class="footer-copyright">%s</p>', wp_kses_post( $text ) );
	}
}
