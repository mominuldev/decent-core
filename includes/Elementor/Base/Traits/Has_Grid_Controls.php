<?php
/**
 * Grid controls.
 *
 * @package DecentCore
 */

namespace DecentCore\Elementor\Base\Traits;

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;

/**
 * Responsive column count and gap.
 *
 * Registered with add_responsive_control so the values land on the same five
 * breakpoints the theme stylesheet uses — Compat\Breakpoints is what makes
 * "tablet" mean the same width in both places.
 */
trait Has_Grid_Controls {

	/**
	 * Registers the grid controls.
	 *
	 * @param int $default Desktop column count.
	 * @return void
	 */
	protected function register_grid_controls( int $default = 3 ): void {
		$this->add_responsive_control(
			'columns',
			array(
				'label'          => __( 'Columns', 'decent-core' ),
				'type'           => Controls_Manager::SELECT,
				'default'        => (string) $default,
				'tablet_default' => '2',
				'mobile_default' => '1',
				'options'        => array(
					'1' => '1',
					'2' => '2',
					'3' => '3',
					'4' => '4',
				),
				'selectors'      => array(
					'{{WRAPPER}} .decent-grid' => 'grid-template-columns: repeat({{VALUE}}, minmax(0, 1fr));',
				),
			)
		);

		$this->add_responsive_control(
			'gap',
			array(
				'label'     => __( 'Gap', 'decent-core' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   => array(
					'size' => 24,
					'unit' => 'px',
				),
				// Constrained to the 8px scale: an editor should not be able
				// to produce a 23px gutter in a system built on multiples.
				'range'     => array(
					'px' => array(
						'min'  => 8,
						'max'  => 56,
						'step' => 8,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .decent-grid' => 'gap: {{SIZE}}{{UNIT}};',
				),
			)
		);
	}
}
