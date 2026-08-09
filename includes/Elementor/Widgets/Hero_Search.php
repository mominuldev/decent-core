<?php
/**
 * Hero Search widget.
 *
 * The first widget, and the one that proves the pipeline end to end: it takes
 * its name, title, icon, category and asset handles from config/widgets.php,
 * it reuses the theme's own .hero-search component rather than inventing
 * markup, and its stylesheet contains only what the theme does not already
 * provide.
 *
 * @package DecentCore
 */

namespace DecentCore\Elementor\Widgets;

defined( 'ABSPATH' ) || exit;

use DecentCore\Elementor\Base\Traits\Has_Style_Controls;
use DecentCore\Elementor\Base\Widget_Base;
use Elementor\Controls_Manager;

/**
 * A search-led hero.
 */
final class Hero_Search extends Widget_Base {

	use Has_Style_Controls;

	/**
	 * Widget slug.
	 *
	 * @return string
	 */
	public static function slug(): string {
		return 'hero-search';
	}

	/**
	 * Registers the panel controls.
	 *
	 * @return void
	 */
	protected function register_controls(): void {
		$this->start_controls_section(
			'content',
			array( 'label' => __( 'Content', 'decent-core' ) )
		);

		$this->add_control(
			'pill',
			array(
				'label'       => __( 'Eyebrow pill', 'decent-core' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Trusted by 25,400+ developers, agencies and startups', 'decent-core' ),
				'label_block' => true,
			)
		);

		$this->add_control(
			'heading',
			array(
				'label'       => __( 'Heading', 'decent-core' ),
				'type'        => Controls_Manager::TEXTAREA,
				'rows'        => 2,
				'default'     => __( 'We build professional themes, plugins and templates', 'decent-core' ),
				'label_block' => true,
			)
		);

		$this->add_control(
			'heading_tag',
			array(
				'label'   => __( 'Heading tag', 'decent-core' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'h1',
				// Constrained, not free text: one h1 per page is a structural
				// rule, and an editor should be able to pick the right level
				// without being able to inject an element.
				'options' => array(
					'h1' => 'H1',
					'h2' => 'H2',
					'h3' => 'H3',
					'p'  => __( 'Paragraph', 'decent-core' ),
				),
			)
		);

		$this->add_control(
			'text',
			array(
				'label'       => __( 'Supporting text', 'decent-core' ),
				'type'        => Controls_Manager::TEXTAREA,
				'rows'        => 3,
				'default'     => __( 'One-time price, lifetime updates, and documentation with every release.', 'decent-core' ),
				'label_block' => true,
			)
		);

		$this->add_control(
			'placeholder',
			array(
				'label'       => __( 'Search placeholder', 'decent-core' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Search products…', 'decent-core' ),
				'label_block' => true,
			)
		);

		$this->add_control(
			'button_label',
			array(
				'label'   => __( 'Button label', 'decent-core' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'Search', 'decent-core' ),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'style',
			array(
				'label' => __( 'Style', 'decent-core' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'align',
			array(
				'label'     => __( 'Alignment', 'decent-core' ),
				'type'      => Controls_Manager::CHOOSE,
				'default'   => 'center',
				'options'   => array(
					'left'   => array(
						'title' => __( 'Left', 'decent-core' ),
						'icon'  => 'eicon-text-align-left',
					),
					'center' => array(
						'title' => __( 'Center', 'decent-core' ),
						'icon'  => 'eicon-text-align-center',
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .hero__inner' => 'text-align: {{VALUE}};',
				),
			)
		);

		// The eyebrow, heading and supporting text. `heading_color` and
		// `heading_typography` keep the ids they were first published under —
		// renaming them would drop the values off every page already using the
		// widget.
		$this->register_text_style( 'pill', __( 'Eyebrow pill', 'decent-core' ), '{{WRAPPER}} .pill' );

		$this->add_control(
			'pill_dot_color',
			array(
				'label'     => __( 'Pill dot colour', 'decent-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pill__dot' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->register_text_style( 'heading', __( 'Heading', 'decent-core' ), '{{WRAPPER}} .hero__title' );

		$this->register_text_style(
			'text',
			__( 'Supporting text', 'decent-core' ),
			'{{WRAPPER}} .hero__text',
			array( 'spacing' => false )
		);

		$this->end_controls_section();

		$this->start_style_section( 'style_form', __( 'Search form', 'decent-core' ) );

		$this->register_box_style(
			'field',
			__( 'Field', 'decent-core' ),
			'{{WRAPPER}} .hero-search__input',
			array(
				'separator' => 'none',
				'shadow'    => false,
			)
		);

		$this->register_text_style(
			'field_text',
			__( 'Field text', 'decent-core' ),
			'{{WRAPPER}} .hero-search__input',
			array(
				'heading' => false,
				'spacing' => false,
			)
		);

		$this->add_control(
			'field_placeholder_color',
			array(
				'label'     => __( 'Placeholder colour', 'decent-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .hero-search__input::placeholder' => 'color: {{VALUE}};',
				),
			)
		);

		$this->register_button_style( 'search_button', __( 'Button', 'decent-core' ), '{{WRAPPER}} .hero-search .btn' );

		$this->register_gap_style( 'form_gap', __( 'Field and button gap', 'decent-core' ), '{{WRAPPER}} .hero-search', 32 );

		$this->end_controls_section();

		$this->start_style_section( 'style_band', __( 'Hero band', 'decent-core' ) );

		$this->register_box_style(
			'band',
			__( 'Band', 'decent-core' ),
			'{{WRAPPER}} .hero',
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
		$pill        = $this->text( 'pill' );
		$heading     = $this->text( 'heading' );
		$text        = $this->text( 'text' );
		$placeholder = $this->text( 'placeholder', __( 'Search…', 'decent-core' ) );
		$button      = $this->text( 'button_label', __( 'Search', 'decent-core' ) );
		$tag         = (string) $this->get_settings_for_display( 'heading_tag' );

		// The form posts to the catalogue when EDD is present and to the site
		// search otherwise, so the widget is never a dead end.
		$action = defined( 'EDD_VERSION' ) && get_post_type_archive_link( 'download' )
			? (string) get_post_type_archive_link( 'download' )
			: home_url( '/' );

		$field = defined( 'EDD_VERSION' ) ? 'q' : 's';
		$id    = 'decent-hero-search-' . $this->get_id();
		?>
		<section class="hero hero--search">
			<div class="container hero__inner">
				<?php if ( '' !== $pill ) : ?>
					<p class="pill"><span class="pill__dot"></span><?php echo esc_html( $pill ); ?></p>
				<?php endif; ?>

				<?php $this->render_heading( $heading, $tag, 'hero__title' ); ?>

				<?php if ( '' !== $text ) : ?>
					<p class="hero__text"><?php echo esc_html( $text ); ?></p>
				<?php endif; ?>

				<form class="hero-search" role="search" method="get" action="<?php echo esc_url( $action ); ?>">
					<label class="sr-only" for="<?php echo esc_attr( $id ); ?>">
						<?php echo esc_html( $button ); ?>
					</label>

					<input class="hero-search__input"
						id="<?php echo esc_attr( $id ); ?>"
						type="search"
						name="<?php echo esc_attr( $field ); ?>"
						placeholder="<?php echo esc_attr( $placeholder ); ?>">

					<button class="btn btn--primary" type="submit"><?php echo esc_html( $button ); ?></button>
				</form>
			</div>
		</section>
		<?php
	}
}
