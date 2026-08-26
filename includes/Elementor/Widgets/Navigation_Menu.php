<?php
/**
 * Navigation Menu widget.
 *
 * A full site navigation: the menu itself, a burger and an off-canvas panel
 * for the widths the row does not fit. Ported from Genesis Core's
 * `de-navigation-menu`, which is where the feature list comes from — item
 * direction, hover styles, submenu trigger and animation, local scroll, and a
 * panel carrying a logo, social links and a call to action.
 *
 * It exists because the theme's own mobile navigation is an in-header
 * collapse: `.site-header__inner.is-open` is what reveals `.main-nav` below
 * 900px, and that element only exists inside the theme's static header. A
 * header built in Elementor has no such ancestor, so a Nav Menu widget there
 * is simply gone on a phone. This widget carries its own trigger and its own
 * panel and so does not depend on the shell it is dropped into.
 *
 * What it does not do is re-implement the menu. `wp_nav_menu()` renders
 * through the theme's `Nav_Walker`, so the markup — `.main-nav`,
 * `.main-nav__sub`, `.has-sub`, the chevron and the `aria-current` the walker
 * mirrors onto the link — is the same markup the static header produces, and
 * every colour, radius and shadow below is the theme's token. The port's own
 * classes are the parts the theme has no component for: the burger, the
 * panel and its chrome.
 *
 * Genesis spends most of its 2,283 lines restating Typography, Border and
 * Box_Shadow groups per element and writing a selector per control. Here the
 * behaviour switches are modifier classes the stylesheet already understands
 * (`pix-nav--hover-fill`, `pix-nav--bp-900`) and the style tab is
 * `Has_Style_Controls`, so the same surface is a fraction of the code and the
 * panel reads as six short sections rather than a wall.
 *
 * @package PixelomaticCore
 */

namespace PixelomaticCore\Elementor\Widgets;

defined( 'ABSPATH' ) || exit;

use PixelomaticCore\Elementor\Base\Traits\Has_Style_Controls;
use PixelomaticCore\Elementor\Base\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Repeater;

/**
 * The site navigation, with an off-canvas panel of its own.
 */
final class Navigation_Menu extends Widget_Base {

	use Has_Style_Controls;

	/**
	 * Wrapper tags an editor may choose from.
	 *
	 * `nav` is deliberately absent: the menu container is already the `nav`
	 * landmark, and nesting one inside another gives the page two landmarks
	 * with the same accessible name.
	 */
	private const WRAPPER_TAGS = array( 'div', 'header', 'aside', 'section' );

	/**
	 * Widget slug.
	 *
	 * @return string
	 */
	public static function slug(): string {
		return 'navigation-menu';
	}

	/**
	 * Registers the panel controls.
	 *
	 * @return void
	 */
	protected function register_controls(): void {
		$this->register_menu_section();
		$this->register_layout_section();
		$this->register_dropdown_section();
		$this->register_panel_section();
		$this->register_panel_footer_section();

		$this->register_menu_style_section();
		$this->register_submenu_style_section();
		$this->register_stuck_style_section();
		$this->register_burger_style_section();
		$this->register_panel_style_section();
		$this->register_panel_footer_style_section();
	}

	/**
	 * Content tab: which menu, and what to call it.
	 *
	 * @return void
	 */
	private function register_menu_section(): void {
		$this->start_controls_section( 'menu_section', array( 'label' => __( 'Menu', 'pixelomatic-core' ) ) );

		$menus = $this->menu_options();

		if ( empty( $menus ) ) {
			$this->register_notice(
				'no_menus',
				__( 'No menus exist yet.', 'pixelomatic-core' ),
				array( __( 'Create one under Appearance → Menus, then reload the editor.', 'pixelomatic-core' ) ),
				'warning'
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

		$this->add_control(
			'wrapper_tag',
			array(
				'label'   => __( 'Wrapper tag', 'pixelomatic-core' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'div',
				'options' => array(
					'div'     => 'div',
					'header'  => 'header',
					'aside'   => 'aside',
					'section' => 'section',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Content tab: how the row behaves and where it folds.
	 *
	 * @return void
	 */
	private function register_layout_section(): void {
		$this->start_controls_section( 'layout_section', array( 'label' => __( 'Layout', 'pixelomatic-core' ) ) );

		$this->add_control(
			'direction',
			array(
				'label'   => __( 'Direction', 'pixelomatic-core' ),
				'type'    => Controls_Manager::CHOOSE,
				'default' => 'inline',
				'toggle'  => false,
				'options' => array(
					'inline' => array(
						'title' => __( 'Row', 'pixelomatic-core' ),
						'icon'  => 'eicon-ellipsis-h',
					),
					'block'  => array(
						'title' => __( 'Column', 'pixelomatic-core' ),
						'icon'  => 'eicon-ellipsis-v',
					),
				),
			)
		);

		// The theme's own breakpoints, and the same ones Compat\Breakpoints
		// syncs into Elementor's kit — so "Tablet" is 900px in the panel, in
		// the stylesheet and here. `always` is Genesis's "hide the menu and
		// open it from the trigger": a burger at every width.
		$this->add_control(
			'breakpoint',
			array(
				'label'       => __( 'Collapse below', 'pixelomatic-core' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => '900',
				'description' => __( 'Under this width the menu becomes a burger and the off-canvas panel.', 'pixelomatic-core' ),
				'options'     => array(
					'1180'   => __( 'Laptop (1180px)', 'pixelomatic-core' ),
					'1024'   => __( 'Tablet extra (1024px)', 'pixelomatic-core' ),
					'900'    => __( 'Tablet (900px)', 'pixelomatic-core' ),
					'768'    => __( 'Mobile extra (768px)', 'pixelomatic-core' ),
					'560'    => __( 'Mobile (560px)', 'pixelomatic-core' ),
					'always' => __( 'Always — burger at every width', 'pixelomatic-core' ),
				),
			)
		);

		$this->add_control(
			'hover_style',
			array(
				'label'   => __( 'Hover style', 'pixelomatic-core' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'default',
				'options' => array(
					'default'   => __( 'Theme default (underline)', 'pixelomatic-core' ),
					'underline' => __( 'Underline grow', 'pixelomatic-core' ),
					'fill'      => __( 'Fill', 'pixelomatic-core' ),
					'fade'      => __( 'Fade the others', 'pixelomatic-core' ),
				),
			)
		);

		$this->add_control(
			'separator_items',
			array(
				'label'        => __( 'Separator between items', 'pixelomatic-core' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'local_scroll',
			array(
				'label'        => __( 'Local scroll', 'pixelomatic-core' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'description'  => __( 'Menu items pointing at an #anchor on this page scroll to it and mark themselves current.', 'pixelomatic-core' ),
				'separator'    => 'before',
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Content tab: the submenu.
	 *
	 * @return void
	 */
	private function register_dropdown_section(): void {
		$this->start_controls_section( 'dropdown_section', array( 'label' => __( 'Dropdown', 'pixelomatic-core' ) ) );

		$this->add_control(
			'submenu_trigger',
			array(
				'label'       => __( 'Opens on', 'pixelomatic-core' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => 'hover',
				'description' => __( 'Inside the panel a submenu always opens on tap, whichever this is.', 'pixelomatic-core' ),
				'options'     => array(
					'hover' => __( 'Hover', 'pixelomatic-core' ),
					'click' => __( 'Click', 'pixelomatic-core' ),
				),
			)
		);

		$this->add_control(
			'submenu_animation',
			array(
				'label'   => __( 'Animation', 'pixelomatic-core' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'fade-up',
				'options' => array(
					'none'      => __( 'None', 'pixelomatic-core' ),
					'fade'      => __( 'Fade in', 'pixelomatic-core' ),
					'fade-up'   => __( 'Fade in up', 'pixelomatic-core' ),
					'fade-down' => __( 'Fade in down', 'pixelomatic-core' ),
				),
			)
		);

		$this->add_control(
			'submenu_link_style',
			array(
				'label'   => __( 'Link style', 'pixelomatic-core' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'default',
				'options' => array(
					'default'   => __( 'Theme default (fill)', 'pixelomatic-core' ),
					'border'    => __( 'Leading border', 'pixelomatic-core' ),
					'underline' => __( 'Underline', 'pixelomatic-core' ),
					'indent'    => __( 'Indent', 'pixelomatic-core' ),
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Content tab: the off-canvas panel and what opens it.
	 *
	 * @return void
	 */
	private function register_panel_section(): void {
		$this->start_controls_section( 'panel_section', array( 'label' => __( 'Off-canvas panel', 'pixelomatic-core' ) ) );

		$this->add_control(
			'panel_side',
			array(
				'label'   => __( 'Opens from', 'pixelomatic-core' ),
				'type'    => Controls_Manager::CHOOSE,
				'default' => 'right',
				'toggle'  => false,
				'options' => array(
					'left'  => array(
						'title' => __( 'Left', 'pixelomatic-core' ),
						'icon'  => 'eicon-h-align-left',
					),
					'right' => array(
						'title' => __( 'Right', 'pixelomatic-core' ),
						'icon'  => 'eicon-h-align-right',
					),
				),
			)
		);

		// Genesis ships this as a class the render always adds, which leaves a
		// published page with the panel held open. Here it is read only in the
		// editor, so styling the panel cannot escape onto the site.
		$this->add_control(
			'panel_preview',
			array(
				'label'        => __( 'Hold open while editing', 'pixelomatic-core' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'description'  => __( 'Editor only — the panel opens on its trigger on the site whatever this says.', 'pixelomatic-core' ),
			)
		);

		$this->add_control(
			'burger_heading',
			array(
				'label'     => __( 'Trigger', 'pixelomatic-core' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_control(
			'burger_icon',
			array(
				'label'   => __( 'Icon', 'pixelomatic-core' ),
				'type'    => Controls_Manager::ICONS,
				'default' => self::design_icon( 'menu' ),
			)
		);

		$this->add_control(
			'burger_label',
			array(
				'label'       => __( 'Accessible name', 'pixelomatic-core' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Menu', 'pixelomatic-core' ),
				'label_block' => true,
			)
		);

		$this->register_alignment_flex_style( 'burger_align', '{{WRAPPER}} .pix-nav', __( 'Alignment', 'pixelomatic-core' ) );

		$this->add_control(
			'panel_head_heading',
			array(
				'label'     => __( 'Panel header', 'pixelomatic-core' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_control(
			'panel_logo',
			array(
				'label'       => __( 'Logo', 'pixelomatic-core' ),
				'type'        => Controls_Manager::MEDIA,
				'description' => __( 'Shown at the top of the panel. Left empty, the panel opens with just its close button.', 'pixelomatic-core' ),
			)
		);

		$this->add_control(
			'panel_logo_alt',
			array(
				'label'       => __( 'Logo alt text', 'pixelomatic-core' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
				'condition'   => array( 'panel_logo[url]!' => '' ),
			)
		);

		$this->add_control(
			'close_icon',
			array(
				'label'   => __( 'Close icon', 'pixelomatic-core' ),
				'type'    => Controls_Manager::ICONS,
				'default' => self::design_icon( 'close' ),
			)
		);

		$this->add_control(
			'close_label',
			array(
				'label'       => __( 'Close accessible name', 'pixelomatic-core' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Close menu', 'pixelomatic-core' ),
				'label_block' => true,
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Content tab: what sits under the menu inside the panel.
	 *
	 * @return void
	 */
	private function register_panel_footer_section(): void {
		$this->start_controls_section( 'panel_footer_section', array( 'label' => __( 'Panel footer', 'pixelomatic-core' ) ) );

		$repeater = new Repeater();

		$repeater->add_control(
			'social_icon',
			array(
				'label'   => __( 'Icon', 'pixelomatic-core' ),
				'type'    => Controls_Manager::ICONS,
				'default' => self::design_icon( 'link' ),
			)
		);

		$repeater->add_control(
			'social_link',
			array(
				'label'       => __( 'Link', 'pixelomatic-core' ),
				'type'        => Controls_Manager::URL,
				'label_block' => true,
			)
		);

		// An icon-only link has no accessible name at all without this, and
		// "link" read eight times is what a screen reader gets instead.
		$repeater->add_control(
			'social_label',
			array(
				'label'       => __( 'Accessible name', 'pixelomatic-core' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => __( 'Twitter', 'pixelomatic-core' ),
				'label_block' => true,
			)
		);

		$this->add_control(
			'social',
			array(
				'label'       => __( 'Social links', 'pixelomatic-core' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'title_field' => '{{{ social_label }}}',
				'default'     => array(),
			)
		);

		$this->add_control(
			'show_cta',
			array(
				'label'        => __( 'Call to action', 'pixelomatic-core' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'separator'    => 'before',
			)
		);

		$this->add_control(
			'cta_text',
			array(
				'label'       => __( 'Text', 'pixelomatic-core' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Get started', 'pixelomatic-core' ),
				'label_block' => true,
				'condition'   => array( 'show_cta' => 'yes' ),
			)
		);

		$this->add_control(
			'cta_link',
			array(
				'label'       => __( 'Link', 'pixelomatic-core' ),
				'type'        => Controls_Manager::URL,
				'default'     => array( 'url' => '#' ),
				'label_block' => true,
				'condition'   => array( 'show_cta' => 'yes' ),
			)
		);

		$this->add_control(
			'cta_icon',
			array(
				'label'     => __( 'Icon', 'pixelomatic-core' ),
				'type'      => Controls_Manager::ICONS,
				'condition' => array( 'show_cta' => 'yes' ),
			)
		);

		// The theme's own `.btn--*` set, same list the Button widget offers —
		// the panel's action is the site's button, not a second one.
		$this->add_control(
			'cta_style',
			array(
				'label'     => __( 'Style', 'pixelomatic-core' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'primary',
				'options'   => array(
					'primary'       => __( 'Primary', 'pixelomatic-core' ),
					'gradient'      => __( 'Gradient', 'pixelomatic-core' ),
					'secondary'     => __( 'Secondary', 'pixelomatic-core' ),
					'dark'          => __( 'Dark', 'pixelomatic-core' ),
					'outline-light' => __( 'Outline (on dark)', 'pixelomatic-core' ),
					'white'         => __( 'White (on colour)', 'pixelomatic-core' ),
					'ghost'         => __( 'Ghost', 'pixelomatic-core' ),
					'danger'        => __( 'Danger', 'pixelomatic-core' ),
				),
				'condition' => array( 'show_cta' => 'yes' ),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Style tab: the menu items themselves.
	 *
	 * One section covers both presentations. The panel holds the same list —
	 * there is only ever one `wp_nav_menu()` call — so a colour set here is
	 * the colour in the row and in the drawer, which is what an editor means
	 * by "the menu links".
	 *
	 * @return void
	 */
	private function register_menu_style_section(): void {
		$this->start_style_section( 'style_menu', __( 'Menu items', 'pixelomatic-core' ) );

		$this->register_link_style(
			'link',
			__( 'Link', 'pixelomatic-core' ),
			'{{WRAPPER}} .pix-nav__menu > ul > li > a',
			array( 'separator' => 'none' )
		);

		// The walker mirrors aria-current onto the link, and local scroll sets
		// aria-current="location" on the section being read — so both kinds of
		// "you are here" are one control.
		$this->add_control(
			'link_color_active',
			array(
				'label'     => __( 'Current colour', 'pixelomatic-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pix-nav__menu a[aria-current]' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'hover_accent',
			array(
				'label'       => __( 'Hover accent', 'pixelomatic-core' ),
				'type'        => Controls_Manager::COLOR,
				'description' => __( 'The underline or fill the hover style draws.', 'pixelomatic-core' ),
				'selectors'   => array(
					'{{WRAPPER}} .pix-nav' => '--pix-nav-accent: {{VALUE}};',
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
					'{{WRAPPER}} .pix-nav__menu > ul > li > a' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->register_gap_style( 'menu_gap', __( 'Gap between items', 'pixelomatic-core' ), '{{WRAPPER}} .pix-nav__menu > ul', 64 );

		$this->register_alignment_flex_style( 'menu_align', '{{WRAPPER}} .pix-nav__menu > ul', __( 'Alignment', 'pixelomatic-core' ) );

		$this->add_control(
			'separator_color',
			array(
				'label'     => __( 'Separator colour', 'pixelomatic-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pix-nav' => '--pix-nav-separator: {{VALUE}};',
				),
				'condition' => array( 'separator_items' => 'yes' ),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Style tab: the dropdown panel and its links.
	 *
	 * @return void
	 */
	private function register_submenu_style_section(): void {
		$this->start_style_section( 'style_submenu', __( 'Dropdown', 'pixelomatic-core' ) );

		$this->register_box_style(
			'submenu',
			__( 'Panel', 'pixelomatic-core' ),
			'{{WRAPPER}} .pix-nav__menu .main-nav__sub',
			array( 'separator' => 'none' )
		);

		$this->add_responsive_control(
			'submenu_width',
			array(
				'label'      => __( 'Minimum width', 'pixelomatic-core' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 140,
						'max' => 480,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .pix-nav__menu .main-nav__sub' => 'min-width: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->register_link_style(
			'submenu_link',
			__( 'Link', 'pixelomatic-core' ),
			'{{WRAPPER}} .pix-nav__menu .main-nav__sub a'
		);

		$this->add_control(
			'submenu_link_background_hover',
			array(
				'label'     => __( 'Link hover background', 'pixelomatic-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pix-nav__menu .main-nav__sub a:hover' => 'background-color: {{VALUE}};',
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
					'{{WRAPPER}} .pix-nav__menu .main-nav__sub a' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Style tab: the menu once the header has stuck.
	 *
	 * Genesis carries two whole sections for this. Here the stuck state is
	 * already a modifier the builder's renderer prints on the theme's own
	 * header block, so the same capability is three colours keyed on an
	 * ancestor — and it is inert on a header that does not stick.
	 *
	 * @return void
	 */
	private function register_stuck_style_section(): void {
		$this->start_style_section( 'style_stuck', __( 'When the header is stuck', 'pixelomatic-core' ) );

		$this->add_control(
			'stuck_link_color',
			array(
				'label'     => __( 'Link colour', 'pixelomatic-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'.site-header--stuck {{WRAPPER}} .pix-nav__menu > ul > li > a' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'stuck_link_color_active',
			array(
				'label'     => __( 'Current colour', 'pixelomatic-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'.site-header--stuck {{WRAPPER}} .pix-nav__menu a[aria-current]' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'stuck_burger_color',
			array(
				'label'     => __( 'Trigger colour', 'pixelomatic-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'.site-header--stuck {{WRAPPER}} .pix-nav__burger' => 'color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Style tab: the burger.
	 *
	 * @return void
	 */
	private function register_burger_style_section(): void {
		$this->start_style_section( 'style_burger', __( 'Trigger', 'pixelomatic-core' ) );

		$this->register_icon_style(
			'burger',
			__( 'Trigger', 'pixelomatic-core' ),
			'{{WRAPPER}} .pix-nav__burger',
			array( 'separator' => 'none' )
		);

		$this->add_control(
			'burger_color_hover',
			array(
				'label'     => __( 'Hover colour', 'pixelomatic-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pix-nav__burger:hover' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			array(
				'name'     => 'burger_border',
				'selector' => '{{WRAPPER}} .pix-nav__burger',
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Style tab: the panel itself.
	 *
	 * @return void
	 */
	private function register_panel_style_section(): void {
		$this->start_style_section( 'style_panel', __( 'Panel', 'pixelomatic-core' ) );

		$this->register_box_style(
			'panel',
			__( 'Panel', 'pixelomatic-core' ),
			'{{WRAPPER}} .pix-nav__panel',
			array( 'separator' => 'none' )
		);

		$this->add_responsive_control(
			'panel_width',
			array(
				'label'      => __( 'Width', 'pixelomatic-core' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'vw' ),
				'range'      => array(
					'px' => array(
						'min' => 240,
						'max' => 640,
					),
					'vw' => array(
						'min' => 40,
						'max' => 100,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .pix-nav' => '--pix-nav-panel-width: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'scrim_color',
			array(
				'label'     => __( 'Overlay colour', 'pixelomatic-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pix-nav' => '--pix-nav-scrim: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'panel_logo_width',
			array(
				'label'      => __( 'Logo width', 'pixelomatic-core' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%' ),
				'range'      => array(
					'px' => array(
						'min' => 40,
						'max' => 320,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .pix-nav__logo' => 'width: {{SIZE}}{{UNIT}};',
				),
				'separator'  => 'before',
			)
		);

		$this->register_icon_style(
			'close',
			__( 'Close button', 'pixelomatic-core' ),
			'{{WRAPPER}} .pix-nav__close'
		);

		$this->end_controls_section();
	}

	/**
	 * Style tab: the panel's footer.
	 *
	 * @return void
	 */
	private function register_panel_footer_style_section(): void {
		$this->start_style_section( 'style_panel_footer', __( 'Panel footer', 'pixelomatic-core' ) );

		$this->register_icon_style(
			'social',
			__( 'Social links', 'pixelomatic-core' ),
			'{{WRAPPER}} .pix-nav__social a',
			array( 'separator' => 'none' )
		);

		$this->add_control(
			'social_color_hover',
			array(
				'label'     => __( 'Hover colour', 'pixelomatic-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pix-nav__social a:hover' => 'color: {{VALUE}};',
				),
			)
		);

		$this->register_gap_style( 'social_gap', __( 'Gap', 'pixelomatic-core' ), '{{WRAPPER}} .pix-nav__social', 32 );

		$this->register_button_style(
			'cta',
			__( 'Call to action', 'pixelomatic-core' ),
			'{{WRAPPER}} .pix-nav__cta'
		);

		$this->end_controls_section();
	}

	/**
	 * The collapse width, as the class and the data attribute both spell it.
	 *
	 * CSS reads the class and script.js reads the attribute, so they are one
	 * accessor rather than two chances to disagree. Anything unrecognised —
	 * a page saved against a breakpoint that has since been renamed — falls
	 * back to the theme's own nav collapse point.
	 *
	 * @param array<string, mixed> $settings Display settings.
	 * @return string
	 */
	private function breakpoint( array $settings ): string {
		$value = (string) ( $settings['breakpoint'] ?? '900' );

		return in_array( $value, array( '1180', '1024', '900', '768', '560', 'always' ), true ) ? $value : '900';
	}

	/**
	 * The menus available to choose from.
	 *
	 * @return array<int, string>
	 */
	private function menu_options(): array {
		$options = array();

		foreach ( wp_get_nav_menus() as $menu ) {
			$options[ $menu->term_id ] = $menu->name;
		}

		return $options;
	}

	/**
	 * The behaviour switches, as classes the stylesheet reads.
	 *
	 * Every one of these is a modifier rather than a generated rule: the
	 * hover styles, the animations and the collapse width are written once in
	 * style.scss, so a page with four of these widgets ships one copy of them
	 * instead of four.
	 *
	 * @param array<string, mixed> $settings Display settings.
	 * @return array<int, string>
	 */
	private function root_classes( array $settings ): array {
		$classes = array(
			'pix-nav',
			'pix-nav--' . ( 'block' === ( $settings['direction'] ?? 'inline' ) ? 'block' : 'inline' ),
			'pix-nav--bp-' . $this->breakpoint( $settings ),
			'pix-nav--hover-' . sanitize_html_class( (string) ( $settings['hover_style'] ?? 'default' ) ),
			'pix-nav--sub-' . sanitize_html_class( (string) ( $settings['submenu_animation'] ?? 'fade-up' ) ),
			'pix-nav--sublink-' . sanitize_html_class( (string) ( $settings['submenu_link_style'] ?? 'default' ) ),
			'pix-nav--trigger-' . ( 'click' === ( $settings['submenu_trigger'] ?? 'hover' ) ? 'click' : 'hover' ),
			'pix-nav--from-' . ( 'left' === ( $settings['panel_side'] ?? 'right' ) ? 'left' : 'right' ),
		);

		if ( 'yes' === ( $settings['separator_items'] ?? '' ) ) {
			$classes[] = 'pix-nav--separator';
		}

		// Only ever in the editor, so a published page cannot ship a panel
		// held open by a setting nobody remembers switching on.
		if ( 'yes' === ( $settings['panel_preview'] ?? '' ) && self::is_editing() ) {
			$classes[] = 'pix-nav--ready';
			$classes[] = 'pix-nav--open';
		}

		return $classes;
	}

	/**
	 * Renders the widget.
	 *
	 * @return void
	 */
	protected function render(): void {
		$settings = $this->get_settings_for_display();

		if ( ! absint( $settings['menu'] ?? 0 ) ) {
			return;
		}

		$tag      = (string) ( $settings['wrapper_tag'] ?? 'div' );
		$tag      = in_array( $tag, self::WRAPPER_TAGS, true ) ? $tag : 'div';
		$panel_id = 'pix-nav-panel-' . $this->get_id();
		?>
		<<?php echo esc_html( $tag ); ?>
			class="<?php echo esc_attr( implode( ' ', $this->root_classes( $settings ) ) ); ?>"
			data-pix-nav
			data-breakpoint="<?php echo esc_attr( $this->breakpoint( $settings ) ); ?>"
			data-local-scroll="<?php echo 'yes' === ( $settings['local_scroll'] ?? '' ) ? 'yes' : 'no'; ?>"
		>
			<?php
			$this->render_burger( $panel_id );
			$this->render_panel( $settings, $panel_id );
			?>
		</<?php echo esc_html( $tag ); ?>>
		<?php
	}

	/**
	 * The trigger, and the overlay that closes the panel behind it.
	 *
	 * Both are inert without JavaScript, so both are hidden until script.js
	 * has put `pix-nav--ready` on the root. Until then — and for good, if the
	 * script never arrives — the menu is a plain list in the page, stacked at
	 * the collapse width rather than folded behind a button nothing opens.
	 *
	 * @param string $panel_id Panel element id.
	 * @return void
	 */
	private function render_burger( string $panel_id ): void {
		?>
		<button
			class="pix-nav__burger"
			type="button"
			data-pix-nav-open
			aria-expanded="false"
			aria-controls="<?php echo esc_attr( $panel_id ); ?>"
			aria-label="<?php echo esc_attr( $this->text( 'burger_label', __( 'Menu', 'pixelomatic-core' ) ) ); ?>"
		>
			<?php $this->render_picked_icon( 'burger_icon' ); ?>
		</button>
		<div class="pix-nav__scrim" data-pix-nav-close></div>
		<?php
	}

	/**
	 * The panel: the menu, and the chrome that only appears once it is one.
	 *
	 * Above the collapse width the panel is `display: contents` — it draws no
	 * box at all, so a background or a padding set for the drawer cannot leak
	 * onto the desktop row, and the menu sits in the widget's own flow.
	 *
	 * @param array<string, mixed> $settings Display settings.
	 * @param string               $panel_id Panel element id.
	 * @return void
	 */
	private function render_panel( array $settings, string $panel_id ): void {
		?>
		<div class="pix-nav__panel" id="<?php echo esc_attr( $panel_id ); ?>" data-pix-nav-panel>
			<div class="pix-nav__head">
				<?php $this->render_logo( $settings ); ?>
				<button
					class="pix-nav__close"
					type="button"
					data-pix-nav-close
					aria-label="<?php echo esc_attr( $this->text( 'close_label', __( 'Close menu', 'pixelomatic-core' ) ) ); ?>"
				>
					<?php $this->render_picked_icon( 'close_icon' ); ?>
				</button>
			</div>

			<?php
			$this->render_menu( $settings );
			$this->render_foot( $settings );
			?>
		</div>
		<?php
	}

	/**
	 * The panel's logo, when one is set.
	 *
	 * @param array<string, mixed> $settings Display settings.
	 * @return void
	 */
	private function render_logo( array $settings ): void {
		$logo = (array) ( $settings['panel_logo'] ?? array() );
		$url  = (string) ( $logo['url'] ?? '' );

		if ( '' === $url ) {
			return;
		}
		?>
		<img
			class="pix-nav__logo"
			src="<?php echo esc_url( $url ); ?>"
			alt="<?php echo esc_attr( $this->text( 'panel_logo_alt' ) ); ?>"
		>
		<?php
	}

	/**
	 * The menu.
	 *
	 * The theme's walker is what makes this widget and the theme's static
	 * header the same navigation: `.main-nav__sub`, `.has-sub`, the chevron
	 * and the `aria-current` on the current link all come from there rather
	 * than from markup this plugin would then own a second copy of.
	 *
	 * @param array<string, mixed> $settings Display settings.
	 * @return void
	 */
	private function render_menu( array $settings ): void {
		$args = array(
			'menu'                 => absint( $settings['menu'] ?? 0 ),
			'container'            => 'nav',
			'container_class'      => 'main-nav pix-nav__menu',
			'container_aria_label' => $this->text( 'label', __( 'Primary', 'pixelomatic-core' ) ),
			'menu_class'           => '',
			'menu_id'              => '',
			'depth'                => 2,
			'fallback_cb'          => false,
		);

		if ( class_exists( '\\Pixelomatic\\Frontend\\Nav_Walker' ) ) {
			$args['walker'] = new \Pixelomatic\Frontend\Nav_Walker();
		}

		wp_nav_menu( $args );
	}

	/**
	 * The panel's footer: social links and one call to action.
	 *
	 * @param array<string, mixed> $settings Display settings.
	 * @return void
	 */
	private function render_foot( array $settings ): void {
		$social = is_array( $settings['social'] ?? null ) ? $settings['social'] : array();
		$cta    = 'yes' === ( $settings['show_cta'] ?? '' ) && '' !== $this->text( 'cta_text' );

		if ( array() === $social && ! $cta ) {
			return;
		}
		?>
		<div class="pix-nav__foot">
			<?php if ( array() !== $social ) : ?>
				<ul class="pix-nav__social">
					<?php foreach ( $social as $item ) : ?>
						<?php
						$link = (array) ( $item['social_link'] ?? array() );
						$url  = (string) ( $link['url'] ?? '' );

						if ( '' === $url || ! $this->has_icon_value( $item['social_icon'] ?? null ) ) {
							continue;
						}
						?>
						<li>
							<a
								href="<?php echo esc_url( $url ); ?>"
								aria-label="<?php echo esc_attr( (string) ( $item['social_label'] ?? '' ) ); ?>"
								<?php echo $this->link_attributes( $link ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Built and escaped in link_attributes(). ?>
							>
								<?php $this->render_icon_value( $item['social_icon'] ?? null ); ?>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>

			<?php $this->render_cta( $settings ); ?>
		</div>
		<?php
	}

	/**
	 * The panel's call to action — the theme's own button.
	 *
	 * @param array<string, mixed> $settings Display settings.
	 * @return void
	 */
	private function render_cta( array $settings ): void {
		if ( 'yes' !== ( $settings['show_cta'] ?? '' ) ) {
			return;
		}

		$text = $this->text( 'cta_text' );
		$link = (array) ( $settings['cta_link'] ?? array() );
		$url  = (string) ( $link['url'] ?? '' );

		if ( '' === $text || '' === $url ) {
			return;
		}

		$classes = array( 'btn', 'btn--' . sanitize_html_class( (string) ( $settings['cta_style'] ?? 'primary' ) ), 'pix-nav__cta' );
		?>
		<a
			class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>"
			href="<?php echo esc_url( $url ); ?>"
			<?php echo $this->link_attributes( $link ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Built and escaped in link_attributes(). ?>
		>
			<?php echo esc_html( $text ); ?>
			<?php if ( $this->has_picked_icon( 'cta_icon' ) ) : ?>
				<span class="pix-button__icon"><?php $this->render_picked_icon( 'cta_icon' ); ?></span>
			<?php endif; ?>
		</a>
		<?php
	}

	/**
	 * Whether the widget is being rendered inside the editor.
	 *
	 * @return bool
	 */
	private static function is_editing(): bool {
		if ( ! class_exists( '\\Elementor\\Plugin' ) ) {
			return false;
		}

		$editor = \Elementor\Plugin::instance()->editor ?? null;

		return $editor && $editor->is_edit_mode();
	}
}
