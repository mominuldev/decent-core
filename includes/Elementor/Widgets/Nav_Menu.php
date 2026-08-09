<?php
/**
 * Nav Menu widget.
 *
 * @package DecentCore
 */

namespace DecentCore\Elementor\Widgets;

defined( 'ABSPATH' ) || exit;

use DecentCore\Elementor\Base\Widget_Base;
use Elementor\Controls_Manager;

/**
 * A WordPress menu, rendered with the theme's walker.
 *
 * Using the theme's Nav_Walker rather than Elementor's own markup keeps the
 * header's navigation identical whether it came from a builder template or the
 * static fallback — including the aria-current the walker mirrors onto the
 * link, and the submenu that flattens rather than disappearing on mobile.
 */
final class Nav_Menu extends Widget_Base {

	/**
	 * Widget slug.
	 *
	 * @return string
	 */
	public static function slug(): string {
		return 'nav-menu';
	}

	/**
	 * Registers controls.
	 *
	 * @return void
	 */
	protected function register_controls(): void {
		$this->start_controls_section( 'content', array( 'label' => __( 'Menu', 'decent-core' ) ) );

		$menus = $this->menu_options();

		if ( empty( $menus ) ) {
			$this->add_control(
				'no_menus',
				array(
					'type'            => Controls_Manager::RAW_HTML,
					'raw'             => esc_html__( 'No menus exist yet. Create one under Appearance → Menus.', 'decent-core' ),
					'content_classes' => 'elementor-descriptor',
				)
			);
		} else {
			$this->add_control(
				'menu',
				array(
					'label'   => __( 'Menu', 'decent-core' ),
					'type'    => Controls_Manager::SELECT,
					'default' => (string) array_key_first( $menus ),
					'options' => $menus,
				)
			);
		}

		$this->add_control(
			'label',
			array(
				'label'       => __( 'Accessible name', 'decent-core' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Primary', 'decent-core' ),
				'description' => __( 'Distinguishes this navigation landmark from others on the page.', 'decent-core' ),
				'label_block' => true,
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Returns the available menus.
	 *
	 * @return array<int, string>
	 */
	private function menu_options(): array {
		$menus   = wp_get_nav_menus();
		$options = array();

		foreach ( $menus as $menu ) {
			$options[ $menu->term_id ] = $menu->name;
		}

		return $options;
	}

	/**
	 * Renders the widget.
	 *
	 * @return void
	 */
	protected function render(): void {
		$menu = absint( $this->get_settings_for_display( 'menu' ) );

		if ( ! $menu ) {
			return;
		}

		$args = array(
			'menu'                 => $menu,
			'container'            => 'nav',
			'container_class'      => 'main-nav',
			'container_aria_label' => $this->text( 'label', __( 'Primary', 'decent-core' ) ),
			'menu_class'           => '',
			'menu_id'              => '',
			'depth'                => 2,
			'fallback_cb'          => false,
		);

		if ( class_exists( '\DecentThemes\Frontend\Nav_Walker' ) ) {
			$args['walker'] = new \DecentThemes\Frontend\Nav_Walker();
		}

		wp_nav_menu( $args );
	}
}
