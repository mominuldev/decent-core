<?php
/**
 * Breadcrumb widget.
 *
 * @package DecentCore
 */

namespace DecentCore\Elementor\Widgets;

defined( 'ABSPATH' ) || exit;

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
