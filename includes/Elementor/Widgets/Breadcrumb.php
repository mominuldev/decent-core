<?php
/**
 * Breadcrumb widget.
 *
 * @package DecentCore
 */

namespace DecentCore\Elementor\Widgets;

defined( 'ABSPATH' ) || exit;

use DecentCore\Elementor\Base\Traits\Has_Style_Controls;
use DecentCore\Elementor\Base\Widget_Base;
use Elementor\Controls_Manager;

/**
 * The theme's breadcrumb trail, placeable in a builder layout.
 *
 * Delegates entirely: the trail and its BreadcrumbList JSON-LD are built from
 * one array in the theme, and duplicating that here would be a second source
 * of truth for the same navigation.
 */
final class Breadcrumb extends Widget_Base {

	use Has_Style_Controls;

	/**
	 * Widget slug.
	 *
	 * @return string
	 */
	public static function slug(): string {
		return 'breadcrumb';
	}

	/**
	 * Registers controls.
	 *
	 * @return void
	 */
	protected function register_controls(): void {
		$this->start_controls_section( 'content', array( 'label' => __( 'Breadcrumb', 'decent-core' ) ) );

		$this->add_control(
			'schema',
			array(
				'label'       => __( 'Include structured data', 'decent-core' ),
				'type'        => Controls_Manager::SWITCHER,
				'default'     => 'yes',
				'description' => __( 'Turn off if another plugin already outputs BreadcrumbList markup — two copies on one page is worse than none.', 'decent-core' ),
			)
		);

		$this->end_controls_section();

		$this->start_style_section( 'style_trail', __( 'Trail', 'decent-core' ) );

		$this->register_text_style(
			'trail',
			__( 'Trail', 'decent-core' ),
			'{{WRAPPER}} .breadcrumb',
			array( 'separator' => 'none' )
		);

		$this->register_link_style( 'crumb_link', __( 'Links', 'decent-core' ), '{{WRAPPER}} .breadcrumb a' );

		$this->register_text_style(
			'crumb_current',
			__( 'Current page', 'decent-core' ),
			'{{WRAPPER}} .breadcrumb [aria-current]',
			array( 'spacing' => false )
		);

		$this->register_text_style(
			'crumb_separator',
			__( 'Separator', 'decent-core' ),
			'{{WRAPPER}} .breadcrumb [aria-hidden]',
			array( 'spacing' => false )
		);

		$this->register_gap_style( 'crumb_gap', __( 'Gap', 'decent-core' ), '{{WRAPPER}} .breadcrumb', 32 );

		// .breadcrumb is a flex row, so its alignment is justify-content.
		$this->add_responsive_control(
			'trail_align',
			array(
				'label'     => __( 'Alignment', 'decent-core' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => array(
					'flex-start' => array(
						'title' => __( 'Left', 'decent-core' ),
						'icon'  => 'eicon-text-align-left',
					),
					'center'     => array(
						'title' => __( 'Centre', 'decent-core' ),
						'icon'  => 'eicon-text-align-center',
					),
					'flex-end'   => array(
						'title' => __( 'Right', 'decent-core' ),
						'icon'  => 'eicon-text-align-right',
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .breadcrumb' => 'justify-content: {{VALUE}};',
				),
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
		if ( ! function_exists( 'decent_breadcrumbs' ) ) {
			return;
		}

		decent_breadcrumbs( array( 'schema' => 'yes' === (string) $this->get_settings_for_display( 'schema' ) ) );
	}
}
