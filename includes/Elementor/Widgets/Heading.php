<?php
/**
 * Heading widget.
 *
 * The eyebrow / title / intro block the design opens every section with, on
 * its own. Fourteen widgets already render one as their first child through
 * Has_Section_Head; this is the same block for the sections that are built out
 * of Elementor's own containers rather than one of those widgets, so a page
 * assembled by hand still opens with the design's heading instead of a
 * hand-styled text block.
 *
 * It renders no markup of its own: .pix-section-heading, .eyebrow, .section-title,
 * .section-intro, .link-arrow and the .section/.container band all come from
 * the theme, and the stylesheet beside this file is one rule of layout glue.
 *
 * @package DecentCore
 */

namespace DecentCore\Elementor\Widgets;

defined( 'ABSPATH' ) || exit;

use DecentCore\Elementor\Base\Traits\Has_Section_Head;
use DecentCore\Elementor\Base\Traits\Has_Style_Controls;
use DecentCore\Elementor\Base\Widget_Base;
use Elementor\Controls_Manager;

/**
 * A standalone section head.
 */
final class Heading extends Widget_Base {

	use Has_Section_Head;
	use Has_Style_Controls;

	/**
	 * The band modifiers the theme ships, and the classes each one adds.
	 *
	 * `none` is the default and is why this list is a map rather than a set of
	 * modifiers: dropped inside an Elementor container the widget should be
	 * the heading and nothing else, because the container already owns the
	 * background and the padding. A band is opt-in, for the case where the
	 * heading IS the section.
	 *
	 * @var array<string, string>
	 */
	private const BANDS = array(
		'none'  => '',
		'plain' => 'section',
		'alt'   => 'section section--alt',
		'dark'  => 'section section--dark',
	);

	/**
	 * Widget slug.
	 *
	 * @return string
	 */
	public static function slug(): string {
		return 'heading';
	}

	/**
	 * Registers the panel controls.
	 *
	 * @return void
	 */
	protected function register_controls(): void {
		$this->start_controls_section( 'content', array( 'label' => __( 'Content', 'decent-core' ) ) );

		$this->register_section_head_controls(
			__( 'Find the right stack, fast', 'decent-core' ),
			__( 'Categories', 'decent-core' )
		);

		$this->register_section_head_link_controls();

		$this->end_controls_section();

		$this->start_controls_section( 'layout', array( 'label' => __( 'Layout', 'decent-core' ) ) );

		$this->add_control(
			'band',
			array(
				'label'       => __( 'Band', 'decent-core' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => 'none',
				'description' => __( 'Wraps the heading in one of the theme’s section bands. Leave as None inside an Elementor container, which already owns the background and padding.', 'decent-core' ),
				'options'     => array(
					'none'  => __( 'None', 'decent-core' ),
					'plain' => __( 'Plain', 'decent-core' ),
					'alt'   => __( 'Alt — tinted', 'decent-core' ),
					'dark'  => __( 'Dark', 'decent-core' ),
				),
			)
		);

		$this->add_control(
			'band_border',
			array(
				'label'     => __( 'Band edges', 'decent-core' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => '',
				'options'   => array(
					''                         => __( 'None', 'decent-core' ),
					'section--bordered-top'    => __( 'Top', 'decent-core' ),
					'section--bordered-bottom' => __( 'Bottom', 'decent-core' ),
					'section--bordered'        => __( 'Both', 'decent-core' ),
				),
				'condition' => array( 'band!' => 'none' ),
			)
		);

		$this->add_control(
			'band_padding',
			array(
				'label'     => __( 'Band padding', 'decent-core' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'section__inner--tight',
				'options'   => array(
					'section__inner'        => __( 'Full', 'decent-core' ),
					'section__inner--tight' => __( 'Tight', 'decent-core' ),
				),
				'condition' => array( 'band!' => 'none' ),
			)
		);

		$this->add_control(
			'eyebrow_tone',
			array(
				'label'     => __( 'Eyebrow tone', 'decent-core' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => '',
				'options'   => array(
					''                => __( 'Brand', 'decent-core' ),
					'eyebrow--muted'  => __( 'Muted', 'decent-core' ),
					'eyebrow--yellow' => __( 'Yellow', 'decent-core' ),
				),
				'separator' => 'before',
				'condition' => array( 'eyebrow!' => '' ),
			)
		);

		$this->add_control(
			'intro_narrow',
			array(
				'label'       => __( 'Narrow intro', 'decent-core' ),
				'type'        => Controls_Manager::SWITCHER,
				'default'     => '',
				'description' => __( 'Holds the intro to a shorter measure.', 'decent-core' ),
				'condition'   => array( 'intro!' => '' ),
			)
		);

		$this->end_controls_section();

		$this->start_style_section( 'style_head', __( 'Head', 'decent-core' ) );

		// The eyebrow / title / intro group, shared with every other widget
		// that opens on a section head, so the controls an editor learns here
		// are the same ones everywhere else.
		$this->register_alignment_flex_style( 'text_align', '{{WRAPPER}} .pix-section-heading' );

		$this->end_controls_section();

		$this->start_style_section(
			'style_link',
			__( 'Trailing link', 'decent-core' ),
			array( 'condition' => array( 'head_link_label!' => '' ) )
		);

		$this->register_link_style(
			'head_link',
			__( 'Link', 'decent-core' ),
			'{{WRAPPER}} .pix-section-heading__aside',
			array( 'separator' => 'none' )
		);

		$this->end_controls_section();

		$this->start_style_section(
			'style_band',
			__( 'Band', 'decent-core' ),
			array( 'condition' => array( 'band!' => 'none' ) )
		);

		$this->register_box_style(
			'band',
			__( 'Band', 'decent-core' ),
			'{{WRAPPER}} .section',
			array(
				'heading' => false,
				'shadow'  => false,
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
		$band = (string) $this->get_settings_for_display( 'band' );
		$band = array_key_exists( $band, self::BANDS ) ? $band : 'none';

		if ( 'none' === $band ) {
			$this->render_head();
			return;
		}

		$classes = self::BANDS[ $band ];
		$border  = (string) $this->get_settings_for_display( 'band_border' );

		if ( in_array( $border, array( 'section--bordered', 'section--bordered-top', 'section--bordered-bottom' ), true ) ) {
			$classes .= ' ' . $border;
		}

		$padding = (string) $this->get_settings_for_display( 'band_padding' );
		$padding = 'section__inner' === $padding ? $padding : 'section__inner section__inner--tight';
		?>
		<div class="<?php echo esc_attr( $classes ); ?>">
			<?php $this->render_head(); ?>
		</div>
		<?php
	}

	/**
	 * Renders the head with the two tone options the theme offers.
	 *
	 * The markup itself stays in the trait, so this widget and the fourteen
	 * that embed a head cannot drift apart.
	 *
	 * @return void
	 */
	private function render_head(): void {
		$eyebrow = (string) $this->get_settings_for_display( 'eyebrow_tone' );

		$this->render_section_head(
			array(
				'eyebrow_class' => in_array( $eyebrow, array( 'eyebrow--muted', 'eyebrow--yellow' ), true ) ? $eyebrow : '',
				'intro_class'   => 'yes' === $this->get_settings_for_display( 'intro_narrow' ) ? 'section-intro--narrow' : '',
			)
		);
	}
}
