<?php
/**
 * Nav Menu widget.
 *
 * @package PixelomaticCore
 */

namespace PixelomaticCore\Elementor\Widgets;

defined( 'ABSPATH' ) || exit;

use PixelomaticCore\Elementor\Base\Traits\Has_Style_Controls;
use PixelomaticCore\Elementor\Base\Widget_Base;
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

	use Has_Style_Controls;

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
		$this->start_controls_section( 'content', array( 'label' => __( 'Menu', 'pixelomatic-core' ) ) );

		$menus = $this->menu_options();

		if ( empty( $menus ) ) {
			$this->add_control(
				'no_menus',
				array(
					'type'            => Controls_Manager::RAW_HTML,
					'raw'             => esc_html__( 'No menus exist yet. Create one under Appearance → Menus.', 'pixelomatic-core' ),
					'content_classes' => 'elementor-descriptor',
				)
			);
		} else {
			$this->add_control(
				'menu',
				array(
					'label'   => __( 'Menu', 'pixelomatic-core' ),
					'type'    => Controls_Manager::SELECT,
					'default' => (string) array_key_first( $menus ),
					'options' => $menus,
				)
			);
		}

		$this->add_control(
			'label',
			array(
				'label'       => __( 'Accessible name', 'pixelomatic-core' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Primary', 'pixelomatic-core' ),
				'description' => __( 'Distinguishes this navigation landmark from others on the page.', 'pixelomatic-core' ),
				'label_block' => true,
			)
		);

		$this->end_controls_section();

		$this->start_style_section( 'style_links', __( 'Menu items', 'pixelomatic-core' ) );

		$this->register_link_style(
			'link',
			__( 'Link', 'pixelomatic-core' ),
			'{{WRAPPER}} .main-nav a',
			array( 'separator' => 'none' )
		);

		// The walker mirrors aria-current onto the link, so the active item is
		// styleable without a class of its own.
		$this->add_control(
			'link_color_active',
			array(
				'label'     => __( 'Active colour', 'pixelomatic-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .main-nav a[aria-current]' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'link_padding',
			array(
				'label'      => __( 'Link padding', 'pixelomatic-core' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .main-nav > ul > li > a' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->register_gap_style( 'menu_gap', __( 'Gap between items', 'pixelomatic-core' ), '{{WRAPPER}} .main-nav > ul', 64 );

		$this->add_responsive_control(
			'menu_align',
			array(
				'label'     => __( 'Alignment', 'pixelomatic-core' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => array(
					'flex-start' => array(
						'title' => __( 'Left', 'pixelomatic-core' ),
						'icon'  => 'eicon-text-align-left',
					),
					'center'     => array(
						'title' => __( 'Centre', 'pixelomatic-core' ),
						'icon'  => 'eicon-text-align-center',
					),
					'flex-end'   => array(
						'title' => __( 'Right', 'pixelomatic-core' ),
						'icon'  => 'eicon-text-align-right',
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .main-nav > ul' => 'justify-content: {{VALUE}};',
				),
			)
		);

		$this->end_controls_section();

		$this->start_style_section( 'style_submenu', __( 'Submenu', 'pixelomatic-core' ) );

		$this->register_box_style(
			'submenu',
			__( 'Panel', 'pixelomatic-core' ),
			'{{WRAPPER}} .main-nav__sub',
			array( 'separator' => 'none' )
		);

		$this->register_link_style(
			'submenu_link',
			__( 'Link', 'pixelomatic-core' ),
			'{{WRAPPER}} .main-nav__sub a'
		);

		$this->add_control(
			'submenu_link_background_hover',
			array(
				'label'     => __( 'Link hover background', 'pixelomatic-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .main-nav__sub a:hover' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'submenu_link_padding',
			array(
				'label'      => __( 'Link padding', 'pixelomatic-core' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .main-nav__sub a' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
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
			'container_aria_label' => $this->text( 'label', __( 'Primary', 'pixelomatic-core' ) ),
			'menu_class'           => '',
			'menu_id'              => '',
			'depth'                => 2,
			'fallback_cb'          => false,
		);

		if ( class_exists( '\Pixelomatic\Frontend\Nav_Walker' ) ) {
			$args['walker'] = new \Pixelomatic\Frontend\Nav_Walker();
		}

		wp_nav_menu( $args );
	}
}
