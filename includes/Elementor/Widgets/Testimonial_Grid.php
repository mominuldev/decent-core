<?php
/**
 * Testimonial slider widget.
 *
 * @package DecentCore
 */

namespace DecentCore\Elementor\Widgets;

defined( 'ABSPATH' ) || exit;

use DecentCore\Elementor\Base\Traits\Has_Section_Head;
use DecentCore\Elementor\Base\Traits\Has_Slider_Controls;
use DecentCore\Elementor\Base\Traits\Has_Style_Controls;
use DecentCore\Elementor\Base\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Repeater;

/**
 * Customer reviews, as a slider.
 *
 * Swiper drives it, and Swiper here costs nothing: Elementor already ships
 * 8.4.5 and registers it as `swiper`, so declaring the handle reuses a file
 * the page has either loaded already or will load for the next carousel on
 * it. Bundling a second copy is what would be expensive.
 *
 * The slug is still `testimonial-grid`. That string is the widget's identity
 * in every page already built with it, and renaming it to match the new
 * layout would orphan all of them.
 *
 * Before Swiper runs — and if it never does — the track is a scroll-snap
 * container, so the reviews are readable and swipeable with no script at all.
 * The controls ship hidden and are revealed by the script that can drive them.
 */
final class Testimonial_Grid extends Widget_Base {

	use Has_Section_Head;
	use Has_Slider_Controls;
	use Has_Style_Controls;

	/**
	 * Widget slug.
	 *
	 * @return string
	 */
	public static function slug(): string {
		return 'testimonial-grid';
	}

	/**
	 * Registers controls.
	 *
	 * @return void
	 */
	protected function register_controls(): void {
		$this->start_controls_section( 'head', array( 'label' => __( 'Section head', 'decent-core' ) ) );
		$this->register_section_head_controls(
			__( '24,000 purchases, 98% positive', 'decent-core' ),
			__( 'Customer reviews', 'decent-core' )
		);
		$this->end_controls_section();

		$this->start_controls_section( 'summary', array( 'label' => __( 'Rating summary', 'decent-core' ) ) );

		$this->add_control(
			'show_summary',
			array(
				'label'       => __( 'Summary card', 'decent-core' ),
				'type'        => Controls_Manager::SWITCHER,
				'default'     => 'yes',
				'description' => __( 'Sits beside the head. Hidden on phones, where the design drops it.', 'decent-core' ),
			)
		);

		$this->add_control(
			'summary_score',
			array(
				'label'     => __( 'Score', 'decent-core' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => '4.9',
				'condition' => array( 'show_summary' => 'yes' ),
			)
		);

		$this->add_control(
			'summary_stars',
			array(
				'label'     => __( 'Stars', 'decent-core' ),
				'type'      => Controls_Manager::NUMBER,
				'default'   => 5,
				'min'       => 1,
				'max'       => 5,
				'condition' => array( 'show_summary' => 'yes' ),
			)
		);

		$this->add_control(
			'summary_note',
			array(
				'label'       => __( 'Note', 'decent-core' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( '2,840 verified reviews', 'decent-core' ),
				'label_block' => true,
				'condition'   => array( 'show_summary' => 'yes' ),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section( 'items', array( 'label' => __( 'Reviews', 'decent-core' ) ) );

		$repeater = new Repeater();

		$repeater->add_control(
			'quote',
			array(
				'label'       => __( 'Quote', 'decent-core' ),
				'type'        => Controls_Manager::TEXTAREA,
				'rows'        => 4,
				'label_block' => true,
			)
		);

		$repeater->add_control(
			'name',
			array(
				'label'   => __( 'Name', 'decent-core' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'Customer', 'decent-core' ),
			)
		);

		$repeater->add_control(
			'role',
			array(
				'label'       => __( 'Role', 'decent-core' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
			)
		);

		$repeater->add_control(
			'rating',
			array(
				'label'   => __( 'Rating', 'decent-core' ),
				'type'    => Controls_Manager::NUMBER,
				'default' => 5,
				'min'     => 1,
				'max'     => 5,
			)
		);

		$repeater->add_control(
			'initials',
			array(
				'label'       => __( 'Avatar initials', 'decent-core' ),
				'type'        => Controls_Manager::TEXT,
				'description' => __( 'Taken from the name when empty.', 'decent-core' ),
			)
		);

		$this->add_control(
			'reviews',
			array(
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'title_field' => '{{{ name }}}',
				'default'     => array(
					array(
						'quote'  => __( 'The documentation alone saved us a week. Every hook we needed was already there and named sensibly.', 'decent-core' ),
						'name'   => 'Priya Raman',
						'role'   => __( 'Lead developer, Northwind Studio', 'decent-core' ),
						'rating' => 5,
					),
					array(
						'quote'  => __( 'We shipped a client site on the theme in four days. The update path has not broken anything since.', 'decent-core' ),
						'name'   => 'Tom Beckett',
						'role'   => __( 'Founder, Baseline Agency', 'decent-core' ),
						'rating' => 5,
					),
					array(
						'quote'  => __( 'A CSS question sent at 7am came back with a working example, not a link to the docs. That is why we bought two more licences.', 'decent-core' ),
						'name'   => 'Sofia Lindqvist',
						'role'   => __( 'Design lead, Norrsken Studio', 'decent-core' ),
						'rating' => 5,
					),
					array(
						'quote'  => __( 'We stayed on version 2 for eleven months, then upgraded in an afternoon. The migration notes listed every breaking change.', 'decent-core' ),
						'name'   => 'Daniel Okonkwo',
						'role'   => __( 'Freelance developer', 'decent-core' ),
						'rating' => 5,
					),
				),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section( 'slider', array( 'label' => __( 'Slider', 'decent-core' ) ) );

		// The shared control set, with this design's numbers as the defaults:
		// three across at 1440, two at the tablet step, and one and a peek on
		// a phone, gapped 24 and 14. An editor changes any of them; a widget
		// dropped with no configuration already matches the mockup.
		$this->register_slider_controls(
			array(
				'slides_to_show'        => '3',
				'slides_to_show_tablet' => '2',
				'slides_to_show_mobile' => '1.15',
				'space_between'         => 24,
				'space_between_mobile'  => 14,
				'pagination_type'       => 'scrollbar',
			)
		);

		$this->end_controls_section();

		$this->start_style_section( 'style_head', __( 'Section head', 'decent-core' ) );
		$this->register_section_head_style_controls();
		$this->end_controls_section();

		$this->start_style_section(
			'style_summary',
			__( 'Rating summary', 'decent-core' ),
			array( 'condition' => array( 'show_summary' => 'yes' ) )
		);

		$this->register_box_style(
			'summary',
			__( 'Card', 'decent-core' ),
			'{{WRAPPER}} .pix-rating-summary',
			array( 'separator' => 'none' )
		);

		$this->register_text_style( 'summary_score', __( 'Score', 'decent-core' ), '{{WRAPPER}} .pix-rating-summary__score' );

		$this->register_text_style(
			'summary_note',
			__( 'Note', 'decent-core' ),
			'{{WRAPPER}} .pix-rating-summary__note',
			array( 'spacing' => false )
		);

		$this->end_controls_section();

		$this->start_style_section( 'style_card', __( 'Review cards', 'decent-core' ) );

		$this->register_box_style(
			'card',
			__( 'Card', 'decent-core' ),
			'{{WRAPPER}} .pix-review-card',
			array( 'separator' => 'none' )
		);

		$this->register_text_style( 'card_stars', __( 'Stars', 'decent-core' ), '{{WRAPPER}} .pix-review-card__stars' );

		$this->register_text_style( 'card_quote', __( 'Quote', 'decent-core' ), '{{WRAPPER}} .pix-review-card__quote' );

		$this->register_text_style( 'card_name', __( 'Name', 'decent-core' ), '{{WRAPPER}} .pix-review-card__name' );

		$this->register_text_style(
			'card_role',
			__( 'Role', 'decent-core' ),
			'{{WRAPPER}} .pix-review-card__role',
			array( 'spacing' => false )
		);

		$this->end_controls_section();

		$this->start_style_section( 'style_controls', __( 'Slider controls', 'decent-core' ) );
		$this->register_slider_style_controls();
		$this->end_controls_section();

		$this->start_style_section( 'style_band', __( 'Band', 'decent-core' ) );

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
		$reviews = (array) ( $this->get_settings_for_display( 'reviews' ) ?? array() );

		if ( empty( $reviews ) ) {
			return;
		}
		?>
		<section class="section pix-reviews">
			<div class="container section__inner">
				<div class="pix-reviews__head">
					<div class="pix-reviews__copy">
						<?php $this->render_section_head(); ?>
					</div>
					<?php $this->render_summary(); ?>
				</div>

				<?php
				// The carousel shell, its controls and its Swiper config all
				// come from Has_Slider_Controls. This widget supplies slides.
				$this->render_slider_start( array( 'class' => 'pix-reviews__slider' ) );

				foreach ( $reviews as $review ) {
					$this->render_review( (array) $review );
				}

				$this->render_slider_end(
					array(
						'prev_label' => __( 'Previous reviews', 'decent-core' ),
						'next_label' => __( 'Next reviews', 'decent-core' ),
					)
				);
				?>
			</div>
		</section>
		<?php
	}

	/**
	 * Renders one review card.
	 *
	 * @param array<string, mixed> $review Repeater row.
	 * @return void
	 */
	private function render_review( array $review ): void {
		$rating   = max( 1, min( 5, (int) ( $review['rating'] ?? 5 ) ) );
		$name     = (string) ( $review['name'] ?? '' );
		$role     = (string) ( $review['role'] ?? '' );
		$initials = (string) ( $review['initials'] ?? '' );
		$initials = '' !== $initials ? $initials : $this->initials( $name );
		?>
		<div class="swiper-slide pix-review-card">
			<p class="pix-review-card__stars" aria-hidden="true"><?php echo esc_html( str_repeat( '★', $rating ) . str_repeat( '☆', 5 - $rating ) ); ?></p>
			<span class="sr-only">
				<?php
				printf(
					/* translators: %d: star rating out of five. */
					esc_html__( '%d out of 5', 'decent-core' ),
					(int) $rating
				);
				?>
			</span>

			<blockquote class="pix-review-card__quote"><?php echo esc_html( (string) ( $review['quote'] ?? '' ) ); ?></blockquote>

			<div class="pix-review-card__author">
				<?php if ( '' !== $initials ) : ?>
					<span class="pix-review-card__avatar" aria-hidden="true"><?php echo esc_html( $initials ); ?></span>
				<?php endif; ?>

				<div class="pix-review-card__who">
					<p class="pix-review-card__name"><?php echo esc_html( $name ); ?></p>
					<?php if ( '' !== $role ) : ?>
						<p class="pix-review-card__role"><?php echo esc_html( $role ); ?></p>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Renders the rating summary beside the head.
	 *
	 * @return void
	 */
	private function render_summary(): void {
		$settings = $this->get_settings_for_display();

		if ( 'yes' !== ( $settings['show_summary'] ?? 'yes' ) ) {
			return;
		}

		$score = $this->text( 'summary_score' );
		$note  = $this->text( 'summary_note' );
		$stars = max( 1, min( 5, (int) ( $settings['summary_stars'] ?? 5 ) ) );

		if ( '' === $score && '' === $note ) {
			return;
		}
		?>
		<div class="pix-rating-summary">
			<?php if ( '' !== $score ) : ?>
				<p class="pix-rating-summary__score"><?php echo esc_html( $score ); ?></p>
			<?php endif; ?>

			<p class="pix-rating-summary__stars" aria-hidden="true">
				<?php echo esc_html( str_repeat( '★', $stars ) . str_repeat( '☆', 5 - $stars ) ); ?>
			</p>

			<?php if ( '' !== $note ) : ?>
				<p class="pix-rating-summary__note"><?php echo esc_html( $note ); ?></p>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Initials for an avatar, from a display name.
	 *
	 * Multibyte-aware: a name is a name, and truncating one with substr()
	 * produces a broken character rather than a letter.
	 *
	 * @param string $name Display name.
	 * @return string
	 */
	private function initials( string $name ): string {
		if ( class_exists( '\DecentThemes\Frontend\Card' ) ) {
			return \DecentThemes\Frontend\Card::initials( $name );
		}

		$split = preg_split( '/\s+/', trim( $name ) );
		$parts = array_values( array_filter( is_array( $split ) ? $split : array() ) );

		if ( empty( $parts ) ) {
			return '';
		}

		$first = mb_substr( $parts[0], 0, 1 );
		$last  = count( $parts ) > 1 ? mb_substr( (string) end( $parts ), 0, 1 ) : '';

		return mb_strtoupper( $first . $last );
	}
}
