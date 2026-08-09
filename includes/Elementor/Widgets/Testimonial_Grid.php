<?php
/**
 * Testimonial Grid widget.
 *
 * @package DecentCore
 */

namespace DecentCore\Elementor\Widgets;

defined( 'ABSPATH' ) || exit;

use DecentCore\Elementor\Base\Traits\Has_Grid_Controls;
use DecentCore\Elementor\Base\Traits\Has_Section_Head;
use DecentCore\Elementor\Base\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Repeater;

/**
 * Customer quotes.
 */
final class Testimonial_Grid extends Widget_Base {

	use Has_Section_Head;
	use Has_Grid_Controls;

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
				),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'layout',
			array(
				'label' => __( 'Layout', 'decent-core' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);
		$this->register_grid_controls( 3 );
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
		<section class="section section--alt section--bordered">
			<div class="container section__inner section__inner--tight">
				<?php $this->render_section_head(); ?>

				<ul class="review-grid decent-grid">
					<?php foreach ( $reviews as $review ) : ?>
						<?php $rating = max( 1, min( 5, (int) ( $review['rating'] ?? 5 ) ) ); ?>
						<li class="review-card">
							<p class="stars" aria-hidden="true"><?php echo esc_html( str_repeat( '★', $rating ) . str_repeat( '☆', 5 - $rating ) ); ?></p>
							<span class="sr-only">
								<?php
								printf(
									/* translators: %d: star rating out of five. */
									esc_html__( '%d out of 5', 'decent-core' ),
									(int) $rating
								);
								?>
							</span>

							<blockquote class="review-card__quote"><?php echo esc_html( (string) ( $review['quote'] ?? '' ) ); ?></blockquote>

							<div class="review-card__author">
								<p class="review-card__name"><?php echo esc_html( (string) ( $review['name'] ?? '' ) ); ?></p>
								<?php if ( ! empty( $review['role'] ) ) : ?>
									<p class="review-card__role"><?php echo esc_html( (string) $review['role'] ); ?></p>
								<?php endif; ?>
							</div>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		</section>
		<?php
	}
}
