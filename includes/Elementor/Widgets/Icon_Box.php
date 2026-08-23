<?php
/**
 * Icon Box widget.
 *
 * One icon, one title, one line of copy and an optional link — the card the
 * design's CATALOGUE section is built out of, on its own.
 *
 * Category_Grid renders that same section from the taxonomy: eight cards, no
 * typing, no choice about what is in them. This is the hand-built counterpart,
 * for the sections that are not a taxonomy — a services row, a three-up of
 * promises, a single box in a container beside something else. One box per
 * widget, so an editor composes the row with Elementor's own grid container
 * rather than a repeater whose columns only this widget understands.
 *
 * Unlike most widgets here, the markup and the stylesheet beside this file are
 * the component: `.pix-icon-box` is not a theme class, so there is nothing to
 * delegate to. It is built out of the theme's tokens rather than its classes,
 * which is what keeps it inside the design system without forking a component
 * the theme already owns.
 *
 * The layout is one flex container with a gap. Icon on top is `column`, icon
 * on the left is `row` — the same two children either way, so nothing about
 * the markup or the spacing changes when an editor flips the direction, and
 * the direction can differ per breakpoint.
 *
 * @package DecentCore
 */

namespace DecentCore\Elementor\Widgets;

defined( 'ABSPATH' ) || exit;

use DecentCore\Elementor\Base\Traits\Has_Style_Controls;
use DecentCore\Elementor\Base\Widget_Base;
use Elementor\Controls_Manager;

/**
 * A single icon box.
 */
final class Icon_Box extends Widget_Base {

	use Has_Style_Controls;

	/**
	 * Widget slug.
	 *
	 * @return string
	 */
	public static function slug(): string {
		return 'icon-box';
	}

	/**
	 * Registers the panel controls.
	 *
	 * @return void
	 */
	protected function register_controls(): void {
		$this->register_content_controls();
		$this->register_layout_controls();
		$this->register_style_controls();
	}

	/**
	 * The Content tab.
	 *
	 * @return void
	 */
	private function register_content_controls(): void {
		$this->start_controls_section( 'content', array( 'label' => __( 'Content', 'decent-core' ) ) );

		// Elementor's own picker. Icon_Library adds the theme's set to it as a
		// "Decent Icons" tab and renders a pick from that tab as the theme's
		// inline SVG, so the design's icons are the default and cost no
		// request — while Font Awesome and an uploaded SVG stay available for
		// the case the design does not cover.
		$this->add_control(
			'icon',
			array(
				'label'   => __( 'Icon', 'decent-core' ),
				'type'    => Controls_Manager::ICONS,
				'default' => self::design_icon( 'layout' ),
			)
		);

		$this->add_control(
			'title',
			array(
				'label'       => __( 'Title', 'decent-core' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'WordPress Themes', 'decent-core' ),
				'label_block' => true,
			)
		);

		$this->add_control(
			'title_tag',
			array(
				'label'       => __( 'Title tag', 'decent-core' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => 'h3',
				'description' => __( 'The box is usually one of several inside a section, so it sits a level below that section’s own heading.', 'decent-core' ),
				'options'     => array(
					'h2'   => 'H2',
					'h3'   => 'H3',
					'h4'   => 'H4',
					'h5'   => 'H5',
					'h6'   => 'H6',
					'p'    => __( 'Paragraph', 'decent-core' ),
					'span' => __( 'Span', 'decent-core' ),
				),
			)
		);

		$this->add_control(
			'text',
			array(
				'label'       => __( 'Text', 'decent-core' ),
				'type'        => Controls_Manager::TEXTAREA,
				'rows'        => 3,
				'default'     => __( '14 products · from $49', 'decent-core' ),
				'label_block' => true,
			)
		);

		$this->add_control(
			'link_label',
			array(
				'label'       => __( 'Link', 'decent-core' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Browse', 'decent-core' ),
				'description' => __( 'Sits at the end of the box. Needs a URL to render.', 'decent-core' ),
				'label_block' => true,
				'separator'   => 'before',
			)
		);

		$this->add_control(
			'link_url',
			array(
				'label'     => __( 'Link URL', 'decent-core' ),
				'type'      => Controls_Manager::URL,
				'condition' => array( 'link_label!' => '' ),
			)
		);

		$this->add_control(
			'link_icon',
			array(
				'label'     => __( 'Link icon', 'decent-core' ),
				'type'      => Controls_Manager::ICONS,
				'default'   => self::design_icon( 'arrow-right' ),
				'condition' => array( 'link_label!' => '' ),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * The Layout section, on the Style tab.
	 *
	 * Icon position is responsive because it is the one thing about this box
	 * that reliably has to change on a narrow screen: three boxes side by side
	 * with the icon on the left become one column where the icon on top reads
	 * better, and that is a breakpoint decision, not a content one.
	 *
	 * @return void
	 */
	private function register_layout_controls(): void {
		$this->start_style_section( 'layout', __( 'Layout', 'decent-core' ) );

		$this->add_responsive_control(
			'icon_position',
			array(
				'label'     => __( 'Icon position', 'decent-core' ),
				'type'      => Controls_Manager::CHOOSE,
				'default'   => 'column',
				'options'   => array(
					'column' => array(
						'title' => __( 'Top', 'decent-core' ),
						'icon'  => 'eicon-v-align-top',
					),
					'row'    => array(
						'title' => __( 'Left', 'decent-core' ),
						'icon'  => 'eicon-h-align-left',
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .pix-icon-box' => 'flex-direction: {{VALUE}};',
				),
			)
		);

		// `start` / `end` rather than `flex-start` / `flex-end` so one value
		// drives both properties: text-align takes the logical keywords, and
		// align-items takes them too. That also makes the box right-to-left
		// correct for free, which left/right would not be.
		$this->add_responsive_control(
			'align',
			array(
				'label'     => __( 'Alignment', 'decent-core' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => array(
					'start'  => array(
						'title' => __( 'Left', 'decent-core' ),
						'icon'  => 'eicon-text-align-left',
					),
					'center' => array(
						'title' => __( 'Centre', 'decent-core' ),
						'icon'  => 'eicon-text-align-center',
					),
					'end'    => array(
						'title' => __( 'Right', 'decent-core' ),
						'icon'  => 'eicon-text-align-right',
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .pix-icon-box'          => 'text-align: {{VALUE}}; align-items: {{VALUE}};',
					'{{WRAPPER}} .pix-icon-box__content' => 'align-items: {{VALUE}};',
					'{{WRAPPER}} .pix-icon-box__body'    => 'align-items: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'icon_align',
			array(
				'label'       => __( 'Icon alignment', 'decent-core' ),
				'type'        => Controls_Manager::CHOOSE,
				'description' => __( 'Overrides the alignment above for the icon alone. Across the box with the icon on top, down it with the icon on the left.', 'decent-core' ),
				'options'     => array(
					'start'  => array(
						'title' => __( 'Start', 'decent-core' ),
						'icon'  => 'eicon-align-start-h',
					),
					'center' => array(
						'title' => __( 'Centre', 'decent-core' ),
						'icon'  => 'eicon-align-center-h',
					),
					'end'    => array(
						'title' => __( 'End', 'decent-core' ),
						'icon'  => 'eicon-align-end-h',
					),
				),
				'selectors'   => array(
					'{{WRAPPER}} .pix-icon-box__icon' => 'align-self: {{VALUE}};',
				),
			)
		);

		$this->register_gap_style(
			'gap',
			__( 'Icon and content gap', 'decent-core' ),
			'{{WRAPPER}} .pix-icon-box',
			80
		);

		$this->register_gap_style(
			'content_gap',
			__( 'Content and link gap', 'decent-core' ),
			'{{WRAPPER}} .pix-icon-box__content',
			64
		);

		$this->register_gap_style(
			'body_gap',
			__( 'Title and text gap', 'decent-core' ),
			'{{WRAPPER}} .pix-icon-box__body',
			40
		);

		$this->add_responsive_control(
			'content_width',
			array(
				'label'       => __( 'Content width', 'decent-core' ),
				'type'        => Controls_Manager::SLIDER,
				'size_units'  => array( '%', 'px' ),
				'description' => __( 'Holds the copy to a shorter measure. Leave empty to fill the box.', 'decent-core' ),
				'range'       => array(
					'%'  => array(
						'min'  => 20,
						'max'  => 100,
						'step' => 1,
					),
					'px' => array(
						'min'  => 120,
						'max'  => 720,
						'step' => 4,
					),
				),
				'selectors'   => array(
					'{{WRAPPER}} .pix-icon-box__content' => 'max-width: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * The rest of the Style tab: one section per thing the box renders.
	 *
	 * @return void
	 */
	private function register_style_controls(): void {
		$this->start_style_section( 'style_box', __( 'Box', 'decent-core' ) );

		$this->register_box_style(
			'box',
			__( 'Box', 'decent-core' ),
			'{{WRAPPER}} .pix-icon-box',
			array(
				'heading'   => false,
				'margin'    => true,
				'separator' => 'none',
			)
		);

		$this->add_control(
			'box_border_color_hover',
			array(
				'label'     => __( 'Hover border colour', 'decent-core' ),
				'type'      => Controls_Manager::COLOR,
				'separator' => 'before',
				'selectors' => array(
					'{{WRAPPER}} .pix-icon-box:hover' => 'border-color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_section();

		$this->start_style_section(
			'style_icon',
			__( 'Icon', 'decent-core' ),
			array( 'condition' => array( 'icon[value]!' => '' ) )
		);

		$this->add_control(
			'icon_color',
			array(
				'label'     => __( 'Icon colour', 'decent-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pix-icon-box__icon' => 'color: {{VALUE}};',
				),
			)
		);

		// One size control for three kinds of icon. The tile carries the size
		// as `font-size` and the stylesheet draws the glyph at 1em, so the
		// theme's SVG, an uploaded SVG and a Font Awesome glyph — which has no
		// width or height to set, only a font-size — all follow it.
		$this->add_responsive_control(
			'icon_size',
			array(
				'label'      => __( 'Icon size', 'decent-core' ),
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
					'{{WRAPPER}} .pix-icon-box__icon' => 'font-size: {{SIZE}}{{UNIT}};',
				),
			)
		);

		// The tile is a fixed square in the design, not padding around a glyph,
		// so its size is its own control: an editor changing the glyph size
		// should not have to re-derive the padding to keep the tile square.
		$this->add_responsive_control(
			'icon_tile',
			array(
				'label'      => __( 'Tile size', 'decent-core' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min'  => 24,
						'max'  => 120,
						'step' => 2,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .pix-icon-box__icon' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->register_box_style(
			'icon_tile',
			__( 'Tile', 'decent-core' ),
			'{{WRAPPER}} .pix-icon-box__icon',
			array(
				'heading' => false,
				'shadow'  => false,
			)
		);

		$this->end_controls_section();

		$this->start_style_section(
			'style_title',
			__( 'Title', 'decent-core' ),
			array( 'condition' => array( 'title!' => '' ) )
		);

		$this->register_text_style(
			'title',
			__( 'Title', 'decent-core' ),
			'{{WRAPPER}} .pix-icon-box__title',
			array(
				'heading'   => false,
				'spacing'   => false,
				'separator' => 'none',
			)
		);

		$this->end_controls_section();

		$this->start_style_section(
			'style_text',
			__( 'Text', 'decent-core' ),
			array( 'condition' => array( 'text!' => '' ) )
		);

		$this->register_text_style(
			'text',
			__( 'Text', 'decent-core' ),
			'{{WRAPPER}} .pix-icon-box__text',
			array(
				'heading'   => false,
				'spacing'   => false,
				'separator' => 'none',
			)
		);

		$this->end_controls_section();

		$this->start_style_section(
			'style_link',
			__( 'Link', 'decent-core' ),
			array( 'condition' => array( 'link_label!' => '' ) )
		);

		$this->register_link_style(
			'link',
			__( 'Link', 'decent-core' ),
			'{{WRAPPER}} .pix-icon-box__link',
			array(
				'heading'   => false,
				'separator' => 'none',
			)
		);

		$this->register_gap_style(
			'link_gap',
			__( 'Label and icon gap', 'decent-core' ),
			'{{WRAPPER}} .pix-icon-box__link',
			24
		);

		$this->add_responsive_control(
			'link_icon_size',
			array(
				'label'      => __( 'Link icon size', 'decent-core' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min'  => 10,
						'max'  => 32,
						'step' => 1,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .pix-icon-box__link-icon' => 'font-size: {{SIZE}}{{UNIT}};',
				),
				'condition'  => array( 'link_icon[value]!' => '' ),
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
		$title = $this->text( 'title' );
		$text  = $this->text( 'text' );
		$link  = $this->link();
		$icon  = $this->has_picked_icon( 'icon' );

		if ( '' === $title && '' === $text && null === $link && ! $icon ) {
			return;
		}

		// The direction is a control, so the modifier is only the resting
		// state — the responsive control above overrides it per breakpoint.
		// It is in the markup so the box reads correctly outside Elementor's
		// generated CSS, and so a template can style the two shapes apart.
		$direction = 'row' === $this->get_settings_for_display( 'icon_position' ) ? 'left' : 'top';
		?>
		<div class="pix-icon-box pix-icon-box--icon-<?php echo esc_attr( $direction ); ?>">
			<?php if ( $icon ) : ?>
				<span class="pix-icon-box__icon">
					<?php $this->render_picked_icon( 'icon' ); ?>
				</span>
			<?php endif; ?>

			<div class="pix-icon-box__content">
				<?php if ( '' !== $title || '' !== $text ) : ?>
					<div class="pix-icon-box__body">
						<?php $this->render_heading( $title, (string) $this->get_settings_for_display( 'title_tag' ), 'pix-icon-box__title' ); ?>

						<?php if ( '' !== $text ) : ?>
							<p class="pix-icon-box__text"><?php echo esc_html( $text ); ?></p>
						<?php endif; ?>
					</div>
				<?php endif; ?>

				<?php if ( null !== $link ) : ?>
					<a class="pix-icon-box__link" href="<?php echo esc_url( $link['url'] ); ?>"<?php echo $link['target']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Fixed attribute string. ?>>
						<span class="pix-icon-box__link-label"><?php echo esc_html( $link['label'] ); ?></span>
						<?php if ( $link['icon'] ) : ?>
							<?php // Decoration: the label already says where the link goes. ?>
							<span class="pix-icon-box__link-icon">
								<?php $this->render_picked_icon( 'link_icon' ); ?>
							</span>
						<?php endif; ?>
					</a>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Resolves the trailing link, or null when there is none to render.
	 *
	 * @return array{label: string, url: string, target: string, icon: bool}|null
	 */
	private function link(): ?array {
		$label = $this->text( 'link_label' );
		$url   = (array) ( $this->get_settings_for_display( 'link_url' ) ?? array() );
		$href  = (string) ( $url['url'] ?? '' );

		if ( '' === $label || '' === $href ) {
			return null;
		}

		return array(
			'label'  => $label,
			'url'    => $href,
			'target' => ! empty( $url['is_external'] ) ? ' target="_blank" rel="noopener noreferrer"' : '',
			'icon'   => $this->has_picked_icon( 'link_icon' ),
		);
	}
}
