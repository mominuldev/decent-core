<?php
/**
 * Copyright widget.
 *
 * @package DecentCore
 */

namespace DecentCore\Elementor\Widgets;

defined( 'ABSPATH' ) || exit;

use DecentCore\Elementor\Base\Widget_Base;
use Elementor\Controls_Manager;

/**
 * A copyright line whose year does not go stale.
 */
final class Copyright extends Widget_Base {

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
		$this->start_controls_section( 'content', array( 'label' => __( 'Copyright', 'decent-core' ) ) );

		$this->add_control(
			'text',
			array(
				'label'       => __( 'Text', 'decent-core' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( '&copy; {year} {site}. All rights reserved.', 'decent-core' ),
				'description' => __( '{year} and {site} are replaced when the page renders, so a hard-coded year cannot go stale in January.', 'decent-core' ),
				'label_block' => true,
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
