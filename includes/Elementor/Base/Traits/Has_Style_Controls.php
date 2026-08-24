<?php
/**
 * Style controls.
 *
 * @package PixelomaticCore
 */

namespace PixelomaticCore\Elementor\Base\Traits;

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;

/**
 * The Style-tab vocabulary every widget draws on.
 *
 * Twenty-nine widgets need colour, typography, spacing and box controls for
 * the fields they render. Written out per widget that is several thousand
 * lines of near-identical array literals and twenty-nine chances for a control
 * id to collide with its own selector. Written once it is five helpers, and a
 * widget's Style tab reads as a list of the things it renders.
 *
 * Two rules hold throughout:
 *
 * 1. **No defaults.** Every control ships empty, so a widget that has never
 *    been styled emits no CSS at all and keeps the theme's own design system
 *    intact. An editor opts in per property; nothing opts in for them.
 * 2. **Selectors target the theme's classes.** Widgets render the theme's
 *    markup, so the selectors here name the same classes the stylesheet does.
 *    They are scoped by {{WRAPPER}}, so styling one instance never reaches
 *    another instance of the same widget on the same page.
 */
trait Has_Style_Controls {

	/**
	 * Opens a section on the Style tab.
	 *
	 * @param string               $id    Section id.
	 * @param string               $label Section label.
	 * @param array<string, mixed> $args  Extra section arguments, such as a condition.
	 * @return void
	 */
	protected function start_style_section( string $id, string $label, array $args = array() ): void {
		$this->start_controls_section(
			$id,
			array_merge(
				array(
					'label' => $label,
					'tab'   => Controls_Manager::TAB_STYLE,
				),
				$args
			)
		);
	}

	/**
	 * Registers the style controls for a section head.
	 *
	 * Lives here rather than beside the section head's content controls so the
	 * widgets that delegate to a theme template — and so render a section head
	 * without registering one — can style it too.
	 *
	 * @param string $selector Section head selector, scoped to {{WRAPPER}}.
	 * @return void
	 */
	protected function register_section_head_style_controls( string $selector = '{{WRAPPER}} .pix-section-heading' ): void {
		$this->register_alignment_head_style( 'head_align', $selector );
		$this->register_alignment_flex_style( 'text_align', $selector, __( 'Block alignment', 'pixelomatic-core' ) );

		$this->register_text_style(
			'head_eyebrow',
			__( 'Eyebrow', 'pixelomatic-core' ),
			$selector . ' .eyebrow'
		);

		$this->register_text_style(
			'head_title',
			__( 'Title', 'pixelomatic-core' ),
			$selector . ' .section-title'
		);

		$this->register_text_style(
			'head_intro',
			__( 'Intro', 'pixelomatic-core' ),
			$selector . ' .section-intro',
			array( 'spacing' => false )
		);

		$this->add_responsive_control(
			'head_spacing',
			array(
				'label'      => __( 'Space below the head', 'pixelomatic-core' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'separator'  => 'before',
				'range'      => array(
					'px' => array(
						'min'  => 0,
						'max'  => 96,
						'step' => 4,
					),
				),
				'selectors'  => array( $selector => 'margin-bottom: {{SIZE}}{{UNIT}};' ),
			)
		);
	}

	/**
	 * Registers colour, typography and spacing for one piece of text.
	 *
	 * @param string               $prefix   Control id prefix, unique per widget.
	 * @param string               $label    Heading shown above the group.
	 * @param string               $selector CSS selector, already scoped to {{WRAPPER}}.
	 * @param array<string, mixed> $args     heading, spacing, align, align_selector, separator.
	 * @return void
	 */
	protected function register_text_style( string $prefix, string $label, string $selector, array $args = array() ): void {
		if ( false !== ( $args['heading'] ?? true ) ) {
			$this->style_heading( $prefix, $label, $args );
		}

		$this->add_control(
			$prefix . '_color',
			array(
				'label'     => __( 'Colour', 'pixelomatic-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( $selector => 'color: {{VALUE}};' ),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => $prefix . '_typography',
				'selector' => $selector,
			)
		);

		if ( ! empty( $args['align'] ) ) {
			$this->register_alignment_style(
				$prefix . '_align',
				(string) ( $args['align_selector'] ?? $selector )
			);
		}

		if ( false !== ( $args['spacing'] ?? true ) ) {
			$this->add_responsive_control(
				$prefix . '_spacing',
				array(
					'label'      => __( 'Spacing below', 'pixelomatic-core' ),
					'type'       => Controls_Manager::SLIDER,
					'size_units' => array( 'px', 'em' ),
					'range'      => array(
						'px' => array(
							'min'  => 0,
							'max'  => 80,
							'step' => 2,
						),
						'em' => array(
							'min'  => 0,
							'max'  => 5,
							'step' => 0.1,
						),
					),
					'selectors'  => array( $selector => 'margin-bottom: {{SIZE}}{{UNIT}};' ),
				)
			);
		}
	}

	/**
	 * Registers background, border, radius, shadow and spacing for a box.
	 *
	 * @param string               $prefix   Control id prefix, unique per widget.
	 * @param string               $label    Heading shown above the group.
	 * @param string               $selector CSS selector, already scoped to {{WRAPPER}}.
	 * @param array<string, mixed> $args     heading, shadow, margin, separator.
	 * @return void
	 */
	protected function register_box_style( string $prefix, string $label, string $selector, array $args = array() ): void {
		if ( false !== ( $args['heading'] ?? true ) ) {
			$this->style_heading( $prefix, $label, $args );
		}

		$this->add_control(
			$prefix . '_background',
			array(
				'label'     => __( 'Background', 'pixelomatic-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( $selector => 'background-color: {{VALUE}};' ),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => $prefix . '_border',
				'selector' => $selector,
			)
		);

		$this->add_responsive_control(
			$prefix . '_radius',
			array(
				'label'      => __( 'Border radius', 'pixelomatic-core' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					$selector => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		if ( false !== ( $args['shadow'] ?? true ) ) {
			$this->add_group_control(
				Group_Control_Box_Shadow::get_type(),
				array(
					'name'     => $prefix . '_shadow',
					'selector' => $selector,
				)
			);
		}

		$this->add_responsive_control(
			$prefix . '_padding',
			array(
				'label'      => __( 'Padding', 'pixelomatic-core' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					$selector => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		if ( ! empty( $args['margin'] ) ) {
			$this->add_responsive_control(
				$prefix . '_margin',
				array(
					'label'      => __( 'Margin', 'pixelomatic-core' ),
					'type'       => Controls_Manager::DIMENSIONS,
					'size_units' => array( 'px', 'em', '%' ),
					'selectors'  => array(
						$selector => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					),
				)
			);
		}
	}

	/**
	 * Registers colour, size and tile styling for an icon.
	 *
	 * The colour lands on the wrapper rather than the SVG: the theme's icons
	 * are stroked with currentColor and carry no fill, which is the same
	 * contract the theme's own icon-colour classes rely on. Setting `fill`
	 * here would produce a solid blob where the design has a line drawing.
	 *
	 * @param string               $prefix   Control id prefix, unique per widget.
	 * @param string               $label    Heading shown above the group.
	 * @param string               $selector Icon wrapper selector, scoped to {{WRAPPER}}.
	 * @param array<string, mixed> $args     heading, svg_selector, box, separator.
	 * @return void
	 */
	protected function register_icon_style( string $prefix, string $label, string $selector, array $args = array() ): void {
		if ( false !== ( $args['heading'] ?? true ) ) {
			$this->style_heading( $prefix, $label, $args );
		}

		$svg = (string) ( $args['svg_selector'] ?? $selector . ' svg' );

		$this->add_control(
			$prefix . '_color',
			array(
				'label'     => __( 'Icon colour', 'pixelomatic-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( $selector => 'color: {{VALUE}};' ),
			)
		);

		$this->add_responsive_control(
			$prefix . '_size',
			array(
				'label'      => __( 'Icon size', 'pixelomatic-core' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min'  => 12,
						'max'  => 64,
						'step' => 1,
					),
				),
				'selectors'  => array(
					$svg => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				),
			)
		);

		if ( false === ( $args['box'] ?? true ) ) {
			return;
		}

		$this->add_control(
			$prefix . '_background',
			array(
				'label'     => __( 'Icon background', 'pixelomatic-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( $selector => 'background-color: {{VALUE}};' ),
			)
		);

		$this->add_responsive_control(
			$prefix . '_radius',
			array(
				'label'      => __( 'Icon radius', 'pixelomatic-core' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					$selector => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			$prefix . '_padding',
			array(
				'label'      => __( 'Icon padding', 'pixelomatic-core' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array(
					$selector => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);
	}

	/**
	 * Registers a button's resting and hover styling.
	 *
	 * @param string               $prefix   Control id prefix, unique per widget.
	 * @param string               $label    Heading shown above the group.
	 * @param string               $selector Button selector, scoped to {{WRAPPER}}.
	 * @param array<string, mixed> $args     heading, hover_selector, separator.
	 * @return void
	 */
	protected function register_button_style( string $prefix, string $label, string $selector, array $args = array() ): void {
		if ( false !== ( $args['heading'] ?? true ) ) {
			$this->style_heading( $prefix, $label, $args );
		}

		$hover = (string) ( $args['hover_selector'] ?? $selector . ':hover, ' . $selector . ':focus-visible' );

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => $prefix . '_typography',
				'selector' => $selector,
			)
		);

		$this->start_controls_tabs( $prefix . '_tabs' );

		$this->start_controls_tab(
			$prefix . '_tab_normal',
			array( 'label' => __( 'Normal', 'pixelomatic-core' ) )
		);

		$this->add_control(
			$prefix . '_color',
			array(
				'label'     => __( 'Text colour', 'pixelomatic-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( $selector => 'color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			$prefix . '_background',
			array(
				'label'     => __( 'Background', 'pixelomatic-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( $selector => 'background-color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			$prefix . '_border_color',
			array(
				'label'     => __( 'Border colour', 'pixelomatic-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( $selector => 'border-color: {{VALUE}};' ),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			$prefix . '_tab_hover',
			array( 'label' => __( 'Hover', 'pixelomatic-core' ) )
		);

		$this->add_control(
			$prefix . '_color_hover',
			array(
				'label'     => __( 'Text colour', 'pixelomatic-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( $hover => 'color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			$prefix . '_background_hover',
			array(
				'label'     => __( 'Background', 'pixelomatic-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( $hover => 'background-color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			$prefix . '_border_color_hover',
			array(
				'label'     => __( 'Border colour', 'pixelomatic-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( $hover => 'border-color: {{VALUE}};' ),
			)
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => $prefix . '_border',
				'selector' => $selector,
			)
		);

		$this->add_responsive_control(
			$prefix . '_radius',
			array(
				'label'      => __( 'Border radius', 'pixelomatic-core' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					$selector => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			$prefix . '_padding',
			array(
				'label'      => __( 'Padding', 'pixelomatic-core' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array(
					$selector => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);
	}

	/**
	 * Registers typography and both link colours.
	 *
	 * @param string               $prefix   Control id prefix, unique per widget.
	 * @param string               $label    Heading shown above the group.
	 * @param string               $selector Link selector, scoped to {{WRAPPER}}.
	 * @param array<string, mixed> $args     heading, hover_selector, separator.
	 * @return void
	 */
	protected function register_link_style( string $prefix, string $label, string $selector, array $args = array() ): void {
		if ( false !== ( $args['heading'] ?? true ) ) {
			$this->style_heading( $prefix, $label, $args );
		}

		$hover = (string) ( $args['hover_selector'] ?? $selector . ':hover, ' . $selector . ':focus-visible' );

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => $prefix . '_typography',
				'selector' => $selector,
			)
		);

		$this->add_control(
			$prefix . '_color',
			array(
				'label'     => __( 'Colour', 'pixelomatic-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( $selector => 'color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			$prefix . '_color_hover',
			array(
				'label'     => __( 'Hover colour', 'pixelomatic-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( $hover => 'color: {{VALUE}};' ),
			)
		);
	}

	/**
	 * Registers a text alignment control.
	 *
	 * @param string $id       Control id.
	 * @param string $selector Selector the alignment applies to.
	 * @param string $label    Optional label.
	 * @return void
	 */
	protected function register_alignment_style( string $id, string $selector, string $label = '' ): void {
		$this->add_responsive_control(
			$id,
			array(
				'label'     => '' !== $label ? $label : __( 'Alignment', 'pixelomatic-core' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => array(
					'left'   => array(
						'title' => __( 'Left', 'pixelomatic-core' ),
						'icon'  => 'eicon-text-align-left',
					),
					'center' => array(
						'title' => __( 'Centre', 'pixelomatic-core' ),
						'icon'  => 'eicon-text-align-center',
					),
					'right'  => array(
						'title' => __( 'Right', 'pixelomatic-core' ),
						'icon'  => 'eicon-text-align-right',
					),
				),
				'selectors' => array( $selector => 'text-align: {{VALUE}};' ),
			)
		);
	}

	/**
	 * Registers the alignment control for a section head.
	 *
	 * A section head is a flex row: the text column and, when the widget has
	 * one, the trailing link beside it. `text-align` alone centres the words
	 * inside a column that is still sitting on the left, and `justify-content`
	 * alone moves the column without touching the words in it — which is why
	 * "centre" read as "nothing happened" whenever only one of the two was
	 * set. One control writes both, through a dictionary because the two
	 * properties do not share a vocabulary.
	 *
	 * @param string $id       Control id.
	 * @param string $selector Selector the alignment applies to.
	 * @param string $label    Optional label.
	 * @return void
	 */
	protected function register_alignment_head_style( string $id, string $selector, string $label = '' ): void {
		$this->add_responsive_control(
			$id,
			array(
				'label'                => '' !== $label ? $label : __( 'Alignment', 'pixelomatic-core' ),
				'type'                 => Controls_Manager::CHOOSE,
				'options'              => array(
					'left'   => array(
						'title' => __( 'Left', 'pixelomatic-core' ),
						'icon'  => 'eicon-text-align-left',
					),
					'center' => array(
						'title' => __( 'Centre', 'pixelomatic-core' ),
						'icon'  => 'eicon-text-align-center',
					),
					'right'  => array(
						'title' => __( 'Right', 'pixelomatic-core' ),
						'icon'  => 'eicon-text-align-right',
					),
				),
				'selectors_dictionary' => array(
					'left'   => 'text-align: left; justify-content: flex-start;',
					'center' => 'text-align: center; justify-content: center;',
					'right'  => 'text-align: right; justify-content: flex-end;',
				),
				'selectors'            => array( $selector => '{{VALUE}}' ),
			)
		);
	}

	/**
	 * Registers a text alignment control.
	 *
	 * @param string $id       Control id.
	 * @param string $selector Selector the alignment applies to.
	 * @param string $label    Optional label.
	 * @return void
	 */
	protected function register_alignment_flex_style( string $id, string $selector, string $label = '' ): void {
		$this->add_responsive_control(
			$id,
			array(
				'label'     => '' !== $label ? $label : __( 'Alignment', 'pixelomatic-core' ),
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
				'selectors' => array( $selector => 'justify-content: {{VALUE}};' ),
			)
		);
	}

	/**
	 * Registers a gap control for a flex or grid container.
	 *
	 * @param string $id       Control id.
	 * @param string $label    Control label.
	 * @param string $selector Container selector.
	 * @param int    $max      Largest gap offered.
	 * @return void
	 */
	protected function register_gap_style( string $id, string $label, string $selector, int $max = 64 ): void {
		$this->add_responsive_control(
			$id,
			array(
				'label'      => $label,
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min'  => 0,
						'max'  => $max,
						'step' => 2,
					),
				),
				'selectors'  => array( $selector => 'gap: {{SIZE}}{{UNIT}};' ),
			)
		);
	}

	/**
	 * Prints the heading that opens a control group.
	 *
	 * @param string               $prefix Control id prefix.
	 * @param string               $label  Heading text.
	 * @param array<string, mixed> $args   separator.
	 * @return void
	 */
	private function style_heading( string $prefix, string $label, array $args = array() ): void {
		$this->add_control(
			$prefix . '_heading',
			array(
				'label'     => $label,
				'type'      => Controls_Manager::HEADING,
				'separator' => (string) ( $args['separator'] ?? 'before' ),
			)
		);
	}
}
