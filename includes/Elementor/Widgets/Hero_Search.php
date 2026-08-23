<?php
/**
 * Hero widget.
 *
 * The landing hero from the Pixelomatic redesign: an announcement pill, a
 * two-tone headline, a pair of calls to action, a social-proof row, a row of
 * stack chips that float at the edges on desktop and re-flow into a "built
 * for" row on small screens, and a browser-framed product visual.
 *
 * It keeps the `hero-search` slug, its config entry and every control id it
 * was first published under — the search form the widget used to lead with is
 * still here, behind a switcher, so an editor who wants it back gets it with
 * one click rather than a different widget.
 *
 * As with every widget here: .hero, .hero__inner, .hero__title, .hero__text,
 * .badge, .btn, .browser, .stars, .rating-line, .tag and .built-for come from
 * the theme. What this widget adds are the pieces the theme has no component
 * for, and those are namespaced .pix-hero__* so ownership is never in
 * question.
 *
 * @package DecentCore
 */

namespace DecentCore\Elementor\Widgets;

defined( 'ABSPATH' ) || exit;

use DecentCore\Elementor\Base\Traits\Has_Style_Controls;
use DecentCore\Elementor\Base\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Repeater;

/**
 * The landing hero.
 */
final class Hero_Search extends Widget_Base {

	use Has_Style_Controls;

	/**
	 * Avatar tones, applied by position.
	 *
	 * The design alternates brand and ink across the stack so no two adjacent
	 * discs share a fill. Cycling in PHP rather than :nth-child keeps the rule
	 * out of the stylesheet, where it would have to be repeated per count.
	 *
	 * @var string[]
	 */
	private const AVATAR_TONES = array( 'brand', 'ink', 'brand-dark', 'ink-soft', 'brand-deep' );

	/**
	 * Widget slug.
	 *
	 * Unchanged: the slug is the widget's identity in config/widgets.php, in
	 * the `decent-hero-search` Elementor name and in every page already built
	 * with it.
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
		$this->register_content_controls();
		$this->register_proof_controls();
		$this->register_stack_controls();
		$this->register_visual_controls();
		$this->register_style_sections();
	}

	/**
	 * Headline, copy, buttons and the optional search form.
	 *
	 * @return void
	 */
	private function register_content_controls(): void {
		$this->start_controls_section(
			'content',
			array( 'label' => __( 'Content', 'decent-core' ) )
		);

		// `pill` keeps its id from the search-led version of this widget: it
		// was the eyebrow above the headline then and it is the announcement
		// above the headline now, so every page already using it carries its
		// text straight across.
		$this->add_control(
			'pill',
			array(
				'label'       => __( 'Announcement', 'decent-core' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Aperture Pro 3.2 — 4 new demo layouts', 'decent-core' ),
				'label_block' => true,
			)
		);

		$this->add_control(
			'pill_badge',
			array(
				'label'       => __( 'Announcement badge', 'decent-core' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'New', 'decent-core' ),
				'description' => __( 'Leave empty to drop the badge.', 'decent-core' ),
				'condition'   => array( 'pill!' => '' ),
			)
		);

		$this->add_control(
			'pill_url',
			array(
				'label'     => __( 'Announcement link', 'decent-core' ),
				'type'      => Controls_Manager::URL,
				'condition' => array( 'pill!' => '' ),
			)
		);

		$this->add_control(
			'heading',
			array(
				'label'       => __( 'Heading', 'decent-core' ),
				'type'        => Controls_Manager::TEXTAREA,
				'rows'        => 2,
				'default'     => __( 'Beautiful themes.', 'decent-core' ),
				'label_block' => true,
			)
		);

		$this->add_control(
			'heading_accent',
			array(
				'label'       => __( 'Heading accent line', 'decent-core' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Serious code.', 'decent-core' ),
				'description' => __( 'Second line of the headline, in the brand colour.', 'decent-core' ),
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
				'default'     => __( '42 WordPress themes, plugins and React templates. Designed in-house, reviewed line by line, and supported by the people who wrote them — one-time price, lifetime updates.', 'decent-core' ),
				'label_block' => true,
			)
		);

		$this->add_control(
			'primary_label',
			array(
				'label'     => __( 'Primary button', 'decent-core' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => __( 'Browse all products', 'decent-core' ),
				'separator' => 'before',
			)
		);

		$this->add_control(
			'primary_url',
			array(
				'label'     => __( 'Primary link', 'decent-core' ),
				'type'      => Controls_Manager::URL,
				'condition' => array( 'primary_label!' => '' ),
			)
		);

		$this->add_control(
			'secondary_label',
			array(
				'label'   => __( 'Secondary button', 'decent-core' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'Watch a live demo', 'decent-core' ),
			)
		);

		$this->add_control(
			'secondary_url',
			array(
				'label'     => __( 'Secondary link', 'decent-core' ),
				'type'      => Controls_Manager::URL,
				'condition' => array( 'secondary_label!' => '' ),
			)
		);

		$this->add_control(
			'secondary_play',
			array(
				'label'     => __( 'Play glyph on the secondary button', 'decent-core' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'yes',
				'condition' => array( 'secondary_label!' => '' ),
			)
		);

		// Off by default: the redesign leads with two buttons, not a field.
		// The control ids below are the ones the search-led version used, so
		// a page that switches the form back on keeps its saved placeholder
		// and button label.
		$this->add_control(
			'show_search',
			array(
				'label'       => __( 'Show the search form', 'decent-core' ),
				'type'        => Controls_Manager::SWITCHER,
				'default'     => '',
				'description' => __( 'Adds the search field below the buttons.', 'decent-core' ),
				'separator'   => 'before',
			)
		);

		$this->add_control(
			'placeholder',
			array(
				'label'       => __( 'Search placeholder', 'decent-core' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Search products…', 'decent-core' ),
				'label_block' => true,
				'condition'   => array( 'show_search' => 'yes' ),
			)
		);

		$this->add_control(
			'button_label',
			array(
				'label'     => __( 'Search button label', 'decent-core' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => __( 'Search', 'decent-core' ),
				'condition' => array( 'show_search' => 'yes' ),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * The avatar, star and rating row under the buttons.
	 *
	 * @return void
	 */
	private function register_proof_controls(): void {
		$this->start_controls_section(
			'content_proof',
			array( 'label' => __( 'Social proof', 'decent-core' ) )
		);

		$this->add_control(
			'show_proof',
			array(
				'label'   => __( 'Show social proof', 'decent-core' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);

		$avatars = new Repeater();

		$avatars->add_control(
			'initials',
			array(
				'label'       => __( 'Initials', 'decent-core' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => 'RM',
				'description' => __( 'Two letters. Used only when no image is set.', 'decent-core' ),
			)
		);

		$avatars->add_control(
			'image',
			array(
				'label' => __( 'Photo', 'decent-core' ),
				'type'  => Controls_Manager::MEDIA,
			)
		);

		$this->add_control(
			'avatars',
			array(
				'label'       => __( 'Avatars', 'decent-core' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $avatars->get_controls(),
				'title_field' => '{{{ initials }}}',
				'default'     => array(
					array( 'initials' => 'RM' ),
					array( 'initials' => 'TB' ),
					array( 'initials' => 'AN' ),
					array( 'initials' => 'JS' ),
					array( 'initials' => 'KP' ),
				),
				'condition'   => array( 'show_proof' => 'yes' ),
			)
		);

		$this->add_control(
			'stars',
			array(
				'label'     => __( 'Stars', 'decent-core' ),
				'type'      => Controls_Manager::NUMBER,
				'min'       => 0,
				'max'       => 5,
				'step'      => 1,
				'default'   => 5,
				'condition' => array( 'show_proof' => 'yes' ),
			)
		);

		$this->add_control(
			'proof_score',
			array(
				'label'     => __( 'Score', 'decent-core' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => '4.9',
				'condition' => array( 'show_proof' => 'yes' ),
			)
		);

		$this->add_control(
			'proof_text',
			array(
				'label'       => __( 'Rating text', 'decent-core' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'from 25,400 developers and agencies', 'decent-core' ),
				'label_block' => true,
				'condition'   => array( 'show_proof' => 'yes' ),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * The stack chips.
	 *
	 * @return void
	 */
	private function register_stack_controls(): void {
		$this->start_controls_section(
			'content_stack',
			array( 'label' => __( 'Stack chips', 'decent-core' ) )
		);

		$this->add_control(
			'stack_notice',
			array(
				'type'            => Controls_Manager::RAW_HTML,
				'raw'             => esc_html__( 'Chips float at the edges of the hero on desktop and collapse into a “built for” row below 900px.', 'decent-core' ),
				'content_classes' => 'elementor-descriptor',
			)
		);

		$stack = new Repeater();

		$stack->add_control(
			'label',
			array(
				'label'       => __( 'Name', 'decent-core' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => 'WordPress',
				'label_block' => true,
			)
		);

		// A logo is a third-party mark, so the plugin ships none: an editor
		// points at their own file. Without one the chip falls back to a
		// monogram, which is what the design itself does for Laravel.
		$stack->add_control(
			'image',
			array(
				'label'       => __( 'Logo', 'decent-core' ),
				'type'        => Controls_Manager::MEDIA,
				'description' => __( 'Falls back to the first letter of the name.', 'decent-core' ),
			)
		);

		$this->add_control(
			'stack',
			array(
				'label'       => __( 'Chips', 'decent-core' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $stack->get_controls(),
				'title_field' => '{{{ label }}}',
				'default'     => array(
					array( 'label' => 'WordPress' ),
					array( 'label' => 'Elementor' ),
					array( 'label' => 'Next.js' ),
					array( 'label' => 'React' ),
					array( 'label' => 'Laravel' ),
					array( 'label' => 'Tailwind' ),
				),
			)
		);

		$this->add_control(
			'stack_label',
			array(
				'label'       => __( 'Small-screen label', 'decent-core' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Built for', 'decent-core' ),
				'label_block' => true,
			)
		);

		$this->end_controls_section();
	}

	/**
	 * The browser-framed visual and its two decorative layers.
	 *
	 * @return void
	 */
	private function register_visual_controls(): void {
		$this->start_controls_section(
			'content_visual',
			array( 'label' => __( 'Visual', 'decent-core' ) )
		);

		$this->add_control(
			'image',
			array(
				'label'       => __( 'Screenshot', 'decent-core' ),
				'type'        => Controls_Manager::MEDIA,
				'description' => __( 'Shown inside the browser frame. This is the page’s LCP image.', 'decent-core' ),
			)
		);

		$this->add_control(
			'preview_url',
			array(
				'label'     => __( 'Address bar', 'decent-core' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => 'aperture-pro.demo',
				'condition' => array( 'image[id]!' => '' ),
			)
		);

		$this->add_control(
			'preview_badge',
			array(
				'label'     => __( 'Chrome badge', 'decent-core' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => __( 'Live demo', 'decent-core' ),
				'condition' => array( 'image[id]!' => '' ),
			)
		);

		$this->add_control(
			'image_mobile',
			array(
				'label'       => __( 'Phone screenshot', 'decent-core' ),
				'type'        => Controls_Manager::MEDIA,
				'description' => __( 'Optional. Adds the tilted phone beside the browser on wide screens.', 'decent-core' ),
				'separator'   => 'before',
			)
		);

		$stats = new Repeater();

		$stats->add_control(
			'value',
			array(
				'label'   => __( 'Figure', 'decent-core' ),
				'type'    => Controls_Manager::TEXT,
				'default' => '1,284',
			)
		);

		$stats->add_control(
			'label',
			array(
				'label'       => __( 'Label', 'decent-core' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Downloads', 'decent-core' ),
				'label_block' => true,
			)
		);

		// The tilted card on the left of the design. Empty means no card, so
		// a hero with nothing worth putting in it simply does not draw one.
		$this->add_control(
			'aside_stats',
			array(
				'label'       => __( 'Side card figures', 'decent-core' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $stats->get_controls(),
				'title_field' => '{{{ value }}}',
				'default'     => array(),
				'separator'   => 'before',
			)
		);

		$this->add_control(
			'aside_title',
			array(
				'label'   => __( 'Side card title', 'decent-core' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'Your licences', 'decent-core' ),
			)
		);

		$this->add_control(
			'aside_note',
			array(
				'label'       => __( 'Side card caption', 'decent-core' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Updates delivered — last 12 weeks', 'decent-core' ),
				'label_block' => true,
			)
		);

		$this->end_controls_section();
	}

	/**
	 * The Style tab.
	 *
	 * Control ids are the ones this widget first shipped with wherever the
	 * element still exists — renaming one drops its value off every page
	 * already using the widget.
	 *
	 * @return void
	 */
	private function register_style_sections(): void {
		$this->start_style_section( 'style', __( 'Text', 'decent-core' ) );

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

		$this->register_text_style( 'pill', __( 'Announcement', 'decent-core' ), '{{WRAPPER}} .pix-hero__announce' );

		$this->register_box_style(
			'pill_box',
			__( 'Announcement box', 'decent-core' ),
			'{{WRAPPER}} .pix-hero__announce'
		);

		$this->register_text_style( 'heading', __( 'Heading', 'decent-core' ), '{{WRAPPER}} .hero__title' );

		$this->add_control(
			'heading_accent_color',
			array(
				'label'     => __( 'Accent line colour', 'decent-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pix-hero__accent' => 'color: {{VALUE}};',
				),
			)
		);

		$this->register_text_style(
			'text',
			__( 'Supporting text', 'decent-core' ),
			'{{WRAPPER}} .hero__text',
			array( 'spacing' => false )
		);

		$this->end_controls_section();

		$this->start_style_section( 'style_actions', __( 'Buttons', 'decent-core' ) );

		$this->register_button_style(
			'primary_button',
			__( 'Primary', 'decent-core' ),
			'{{WRAPPER}} .pix-hero__actions .btn--primary'
		);

		$this->register_button_style(
			'secondary_button',
			__( 'Secondary', 'decent-core' ),
			'{{WRAPPER}} .pix-hero__actions .btn--secondary'
		);

		$this->register_gap_style( 'actions_gap', __( 'Gap', 'decent-core' ), '{{WRAPPER}} .pix-hero__actions', 32 );

		$this->end_controls_section();

		$this->start_style_section(
			'style_proof',
			__( 'Social proof', 'decent-core' ),
			array( 'condition' => array( 'show_proof' => 'yes' ) )
		);

		$this->add_control(
			'avatar_size',
			array(
				'label'      => __( 'Avatar size', 'decent-core' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min'  => 24,
						'max'  => 48,
						'step' => 2,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .pix-hero__proof' => '--decent-avatar: {{SIZE}}px;',
				),
			)
		);

		$this->add_control(
			'star_color',
			array(
				'label'     => __( 'Star colour', 'decent-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pix-hero__proof .stars' => 'color: {{VALUE}};',
				),
			)
		);

		$this->register_text_style(
			'proof',
			__( 'Rating text', 'decent-core' ),
			'{{WRAPPER}} .pix-hero__proof .rating-line',
			array( 'spacing' => false )
		);

		$this->end_controls_section();

		$this->start_style_section( 'style_stack', __( 'Stack chips', 'decent-core' ) );

		$this->add_control(
			'stack_size',
			array(
				'label'      => __( 'Chip size', 'decent-core' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min'  => 48,
						'max'  => 88,
						'step' => 2,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .pix-hero' => '--decent-chip: {{SIZE}}px;',
				),
			)
		);

		$this->register_box_style(
			'stack_chip',
			__( 'Chip', 'decent-core' ),
			'{{WRAPPER}} .pix-hero__chip',
			array( 'margin' => false )
		);

		$this->end_controls_section();

		$this->start_style_section( 'style_visual', __( 'Visual', 'decent-core' ) );

		$this->register_box_style(
			'preview',
			__( 'Browser frame', 'decent-core' ),
			'{{WRAPPER}} .pix-hero__preview'
		);

		$this->add_responsive_control(
			'visual_offset',
			array(
				'label'      => __( 'Space above', 'decent-core' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min'  => 0,
						'max'  => 160,
						'step' => 4,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .pix-hero__visual' => 'margin-top: {{SIZE}}px;',
				),
			)
		);

		$this->end_controls_section();

		$this->start_style_section(
			'style_form',
			__( 'Search form', 'decent-core' ),
			array( 'condition' => array( 'show_search' => 'yes' ) )
		);

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
		?>
		<section class="hero hero--search pix-hero">
			<div class="container hero__inner pix-hero__inner">
				<?php
				$this->render_announcement();

				$this->render_headline();

				$text = $this->text( 'text' );

				if ( '' !== $text ) {
					printf( '<p class="hero__text">%s</p>', esc_html( $text ) );
				}

				$this->render_actions();
				$this->render_search_form();
				$this->render_proof();
				$this->render_stack();
				$this->render_visual();
				?>
			</div>
		</section>
		<?php
	}

	/**
	 * The announcement pill above the headline.
	 *
	 * A link when one is set and a paragraph when one is not, rather than an
	 * anchor with no href — the arrow is only honest if there is somewhere to
	 * go, so it is drawn only in the link case.
	 *
	 * @return void
	 */
	private function render_announcement(): void {
		$text = $this->text( 'pill' );

		if ( '' === $text ) {
			return;
		}

		$badge = $this->text( 'pill_badge' );
		$link  = (array) ( $this->get_settings_for_display( 'pill_url' ) ?? array() );
		$url   = (string) ( $link['url'] ?? '' );

		if ( '' === $url ) {
			echo '<p class="pix-hero__announce">';
		} else {
			printf(
				'<a class="pix-hero__announce pix-hero__announce--link" href="%1$s"%2$s>',
				esc_url( $url ),
				! empty( $link['is_external'] ) ? ' target="_blank" rel="noopener noreferrer"' : ''
			);
		}

		if ( '' !== $badge ) {
			printf(
				'<span class="badge badge--blue pix-hero__announce-badge">%s</span>',
				esc_html( $badge )
			);
		}

		printf( '<span class="pix-hero__announce-text">%s</span>', esc_html( $text ) );

		if ( '' === $url ) {
			echo '</p>';
			return;
		}

		echo '<span class="pix-hero__announce-arrow">';
		$this->icon( 'arrow-right', 15, 1.8 );
		echo '</span></a>';
	}

	/**
	 * The two-tone headline.
	 *
	 * The accent line is a span inside the heading rather than a second
	 * heading: it is one sentence broken across two lines in the design, and
	 * two headings would put a second entry in the document outline.
	 *
	 * @return void
	 */
	private function render_headline(): void {
		$heading = $this->text( 'heading' );
		$accent  = $this->text( 'heading_accent' );

		if ( '' === $heading && '' === $accent ) {
			return;
		}

		$tag = (string) $this->get_settings_for_display( 'heading_tag' );

		if ( '' === $accent ) {
			$this->render_heading( $heading, $tag, 'hero__title' );
			return;
		}

		$tag = in_array( $tag, array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'span', 'div' ), true ) ? $tag : 'h2';

		printf(
			'<%1$s class="hero__title pix-hero__title">%2$s<span class="pix-hero__accent">%3$s</span></%1$s>',
			esc_html( $tag ),
			'' !== $heading ? esc_html( $heading ) . ' ' : '',
			esc_html( $accent )
		);
	}

	/**
	 * The button pair.
	 *
	 * @return void
	 */
	private function render_actions(): void {
		$primary   = $this->link_parts( 'primary_label', 'primary_url' );
		$secondary = $this->link_parts( 'secondary_label', 'secondary_url' );

		if ( null === $primary && null === $secondary ) {
			return;
		}

		$play = 'yes' === $this->get_settings_for_display( 'secondary_play' );
		?>
		<div class="btn-row btn-row--center hero__actions pix-hero__actions">
			<?php
			if ( null !== $primary ) {
				printf(
					'<a class="btn btn--primary btn--lg pix-hero__cta" href="%1$s"%2$s>%3$s</a>',
					esc_url( $primary['url'] ),
					$primary['target'], // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Fixed attribute string.
					esc_html( $primary['label'] )
				);
			}

			if ( null !== $secondary ) {
				printf(
					'<a class="btn btn--secondary btn--lg" href="%1$s"%2$s>%3$s%4$s</a>',
					esc_url( $secondary['url'] ),
					$secondary['target'], // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Fixed attribute string.
					$play ? '<span class="pix-hero__play" aria-hidden="true"></span>' : '',
					esc_html( $secondary['label'] )
				);
			}
			?>
		</div>
		<?php
	}

	/**
	 * The optional search form.
	 *
	 * Unchanged from the search-led version of this widget, down to the id
	 * scheme, so switching it back on restores exactly what was there before.
	 *
	 * @return void
	 */
	private function render_search_form(): void {
		if ( 'yes' !== $this->get_settings_for_display( 'show_search' ) ) {
			return;
		}

		$placeholder = $this->text( 'placeholder', __( 'Search…', 'decent-core' ) );
		$button      = $this->text( 'button_label', __( 'Search', 'decent-core' ) );

		// The form posts to the catalogue when EDD is present and to the site
		// search otherwise, so the widget is never a dead end.
		$action = defined( 'EDD_VERSION' ) && get_post_type_archive_link( 'download' )
			? (string) get_post_type_archive_link( 'download' )
			: home_url( '/' );

		$field = defined( 'EDD_VERSION' ) ? 'q' : 's';
		$id    = 'decent-hero-search-' . $this->get_id();
		?>
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
		<?php
	}

	/**
	 * Avatars, stars and the rating line.
	 *
	 * The stars are decorative: the same rating is written out in the line
	 * beside them, so repeating it to a screen reader as five glyphs would be
	 * noise.
	 *
	 * @return void
	 */
	private function render_proof(): void {
		if ( 'yes' !== $this->get_settings_for_display( 'show_proof' ) ) {
			return;
		}

		$avatars = (array) ( $this->get_settings_for_display( 'avatars' ) ?? array() );
		$stars   = max( 0, min( 5, (int) $this->get_settings_for_display( 'stars' ) ) );
		$score   = $this->text( 'proof_score' );
		$text    = $this->text( 'proof_text' );

		if ( empty( $avatars ) && 0 === $stars && '' === $score && '' === $text ) {
			return;
		}
		?>
		<div class="pix-hero__proof">
			<?php if ( ! empty( $avatars ) ) : ?>
				<ul class="pix-hero__avatars" aria-hidden="true">
					<?php foreach ( array_values( $avatars ) as $index => $avatar ) : ?>
						<?php
						$image    = (array) ( $avatar['image'] ?? array() );
						$image_id = (int) ( $image['id'] ?? 0 );
						$tone     = self::AVATAR_TONES[ $index % count( self::AVATAR_TONES ) ];
						?>
						<li class="pix-hero__avatar pix-hero__avatar--<?php echo esc_attr( $tone ); ?>">
							<?php
							if ( $image_id > 0 ) {
								echo wp_get_attachment_image(
									$image_id,
									'thumbnail',
									false,
									array(
										'alt'      => '',
										'loading'  => 'lazy',
										'decoding' => 'async',
									)
								);
							} else {
								echo esc_html( mb_substr( (string) ( $avatar['initials'] ?? '' ), 0, 2 ) );
							}
							?>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>

			<?php if ( $stars > 0 ) : ?>
				<p class="stars pix-hero__stars" aria-hidden="true"><?php echo esc_html( str_repeat( '★', $stars ) ); ?></p>
			<?php endif; ?>

			<?php if ( '' !== $score || '' !== $text ) : ?>
				<p class="rating-line">
					<?php if ( '' !== $score ) : ?>
						<strong><?php echo esc_html( $score ); ?></strong>
					<?php endif; ?>
					<?php echo esc_html( $text ); ?>
				</p>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * The stack chips.
	 *
	 * One list, two presentations: absolutely positioned at the hero's edges
	 * on desktop, a centred "built for" row below 900px. Rendering it once and
	 * letting CSS decide is what keeps the two in step.
	 *
	 * @return void
	 */
	private function render_stack(): void {
		$stack = (array) ( $this->get_settings_for_display( 'stack' ) ?? array() );

		if ( empty( $stack ) ) {
			return;
		}

		$label = $this->text( 'stack_label' );
		$id    = 'decent-hero-stack-' . $this->get_id();

		// The label is hidden on desktop, where the chips are decoration at
		// the hero's edges, so the list is named by it only when there is one
		// to name it with.
		$labelled = '' !== $label ? sprintf( ' aria-labelledby="%s"', esc_attr( $id ) ) : '';
		?>
		<div class="pix-hero__stack">
			<?php if ( '' !== $label ) : ?>
				<p class="pix-hero__stack-label" id="<?php echo esc_attr( $id ); ?>">
					<?php echo esc_html( $label ); ?>
				</p>
			<?php endif; ?>

			<ul class="pix-hero__chips"<?php echo $labelled; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Built above from an escaped id. ?>>
				<?php foreach ( $stack as $item ) : ?>
					<?php
					$name     = (string) ( $item['label'] ?? '' );
					$image    = (array) ( $item['image'] ?? array() );
					$image_id = (int) ( $image['id'] ?? 0 );
					?>
					<li class="pix-hero__chip">
						<?php
						if ( $image_id > 0 ) {
							echo wp_get_attachment_image(
								$image_id,
								'thumbnail',
								false,
								array(
									'class'    => 'pix-hero__chip-logo',
									'alt'      => $name,
									'loading'  => 'lazy',
									'decoding' => 'async',
								)
							);
						} else {
							printf(
								'<span class="pix-hero__chip-mark" aria-hidden="true">%s</span>',
								esc_html( mb_substr( $name, 0, 1 ) )
							);
						}
						?>
						<span class="sr-only"><?php echo esc_html( $name ); ?></span>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
		<?php
	}

	/**
	 * The browser frame and its two decorative layers.
	 *
	 * @return void
	 */
	private function render_visual(): void {
		if ( ! class_exists( '\DecentThemes\Frontend\Media' ) ) {
			return;
		}

		$image    = (array) ( $this->get_settings_for_display( 'image' ) ?? array() );
		$image_id = (int) ( $image['id'] ?? 0 );

		$phone    = (array) ( $this->get_settings_for_display( 'image_mobile' ) ?? array() );
		$phone_id = (int) ( $phone['id'] ?? 0 );

		$stats = (array) ( $this->get_settings_for_display( 'aside_stats' ) ?? array() );

		if ( 0 === $image_id ) {
			return;
		}
		?>
		<div class="pix-hero__visual">
			<?php $this->render_aside( $stats ); ?>

			<div class="browser pix-hero__preview">
				<div class="browser__bar">
					<span class="browser__dot browser__dot--red"></span>
					<span class="browser__dot browser__dot--yellow"></span>
					<span class="browser__dot browser__dot--green"></span>
					<?php
					$url   = $this->text( 'preview_url' );
					$badge = $this->text( 'preview_badge' );

					if ( '' !== $url ) {
						printf( '<span class="browser__url">%s</span>', esc_html( $url ) );
					}

					if ( '' !== $badge ) {
						printf(
							'<span class="badge badge--green browser__live">%s</span>',
							esc_html( $badge )
						);
					}
					?>
				</div>
				<?php
				// The hero image is the page's LCP candidate.
				\DecentThemes\Frontend\Media::render(
					$image_id,
					array(
						'frame' => 'hero',
						'lcp'   => true,
					)
				);
				?>
			</div>

			<?php if ( $phone_id > 0 ) : ?>
				<div class="pix-hero__phone" aria-hidden="true">
					<span class="pix-hero__phone-notch"></span>
					<?php
					\DecentThemes\Frontend\Media::render(
						$phone_id,
						array(
							'frame' => 'sm',
							'class' => 'pix-hero__phone-screen',
						)
					);
					?>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * The tilted stat card on the left of the visual.
	 *
	 * @param array<int, array<string, mixed>> $stats Figure and label pairs.
	 * @return void
	 */
	private function render_aside( array $stats ): void {
		if ( empty( $stats ) ) {
			return;
		}

		$title = $this->text( 'aside_title' );
		$note  = $this->text( 'aside_note' );
		?>
		<div class="pix-hero__aside">
			<?php if ( '' !== $title ) : ?>
				<p class="pix-hero__aside-title"><?php echo esc_html( $title ); ?></p>
			<?php endif; ?>

			<dl class="pix-hero__aside-stats">
				<?php foreach ( $stats as $stat ) : ?>
					<div>
						<dt><?php echo esc_html( (string) ( $stat['label'] ?? '' ) ); ?></dt>
						<dd><?php echo esc_html( (string) ( $stat['value'] ?? '' ) ); ?></dd>
					</div>
				<?php endforeach; ?>
			</dl>

			<?php
			// Twelve bars, purely decorative — the caption below says what
			// they stand for and the figures above carry the real numbers.
			?>
			<div class="pix-hero__aside-chart" aria-hidden="true">
				<?php for ( $bar = 0; $bar < 12; $bar++ ) : ?>
					<span></span>
				<?php endfor; ?>
			</div>

			<?php if ( '' !== $note ) : ?>
				<p class="pix-hero__aside-note"><?php echo esc_html( $note ); ?></p>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Resolves a label and URL pair, or null when either is missing.
	 *
	 * @param string $label_key Label setting key.
	 * @param string $url_key   URL setting key.
	 * @return array{label: string, url: string, target: string}|null
	 */
	private function link_parts( string $label_key, string $url_key ): ?array {
		$label = $this->text( $label_key );
		$link  = (array) ( $this->get_settings_for_display( $url_key ) ?? array() );
		$url   = (string) ( $link['url'] ?? '' );

		if ( '' === $label || '' === $url ) {
			return null;
		}

		return array(
			'label'  => $label,
			'url'    => $url,
			'target' => ! empty( $link['is_external'] ) ? ' target="_blank" rel="noopener noreferrer"' : '',
		);
	}
}
