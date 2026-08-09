<?php
/**
 * Product Slider widget.
 *
 * @package DecentCore
 */

namespace DecentCore\Elementor\Widgets;

defined( 'ABSPATH' ) || exit;

use DecentCore\Elementor\Base\Traits\Has_Query_Controls;
use DecentCore\Elementor\Base\Traits\Has_Section_Head;
use DecentCore\Elementor\Base\Widget_Base;
use Elementor\Controls_Manager;

/**
 * Products in a horizontally scrollable track.
 *
 * The design system has no slider, so this is the specification written for
 * it: the track is CSS scroll-snap and the slides are the same product cards
 * as everywhere else. That means it works with no JavaScript at all — a touch
 * device can already swipe it, and a keyboard can already tab through it —
 * and the script only adds the previous and next buttons.
 *
 * Swiper is deliberately not a dependency. A carousel that needs 140 KB of
 * library to move three cards sideways is not worth the request.
 */
final class Product_Slider extends Widget_Base {

	use Has_Section_Head;
	use Has_Query_Controls;

	/**
	 * Widget slug.
	 *
	 * @return string
	 */
	public static function slug(): string {
		return 'product-slider';
	}

	/**
	 * Registers controls.
	 *
	 * @return void
	 */
	protected function register_controls(): void {
		$this->start_controls_section( 'head', array( 'label' => __( 'Section head', 'decent-core' ) ) );
		$this->register_section_head_controls(
			__( 'Recently released', 'decent-core' ),
			__( 'New in the catalogue', 'decent-core' )
		);
		$this->end_controls_section();

		$this->start_controls_section( 'query', array( 'label' => __( 'Products', 'decent-core' ) ) );
		$this->register_query_controls( 8 );
		$this->end_controls_section();

		$this->start_controls_section(
			'layout',
			array(
				'label' => __( 'Layout', 'decent-core' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_responsive_control(
			'slide_width',
			array(
				'label'          => __( 'Slide width', 'decent-core' ),
				'type'           => Controls_Manager::SLIDER,
				'default'        => array(
					'size' => 300,
					'unit' => 'px',
				),
				'mobile_default' => array(
					'size' => 260,
					'unit' => 'px',
				),
				'range'          => array(
					'px' => array(
						'min'  => 220,
						'max'  => 420,
						'step' => 20,
					),
				),
				'selectors'      => array(
					'{{WRAPPER}} .decent-slider__track > li' => 'flex: 0 0 {{SIZE}}{{UNIT}};',
				),
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
		if ( ! class_exists( '\DecentThemes\Frontend\Card' ) ) {
			return;
		}

		$query = $this->product_query();

		if ( ! $query->have_posts() ) {
			wp_reset_postdata();
			return;
		}

		$label = $this->text( 'title', __( 'Products', 'decent-core' ) );
		?>
		<section class="section">
			<div class="container section__inner section__inner--tight">
				<?php $this->render_section_head(); ?>

				<div class="decent-slider" data-slider>
					<ul class="decent-slider__track"
						tabindex="0"
						role="region"
						aria-label="<?php echo esc_attr( $label ); ?>">
						<?php
						while ( $query->have_posts() ) :
							$query->the_post();

							\DecentThemes\Frontend\Card::render(
								(int) get_the_ID(),
								array(
									'density' => 'sm',
									'context' => 'slider',
								)
							);
						endwhile;

						wp_reset_postdata();
						?>
					</ul>

					<?php // Injected by the script; absent without it, since a track that already scrolls needs no fallback buttons. ?>
					<div class="decent-slider__nav" data-slider-nav hidden>
						<button type="button" class="btn btn--secondary btn--sm" data-slider-prev>
							<span class="sr-only"><?php esc_html_e( 'Previous products', 'decent-core' ); ?></span>
							<span aria-hidden="true">&larr;</span>
						</button>
						<button type="button" class="btn btn--secondary btn--sm" data-slider-next>
							<span class="sr-only"><?php esc_html_e( 'Next products', 'decent-core' ); ?></span>
							<span aria-hidden="true">&rarr;</span>
						</button>
					</div>
				</div>
			</div>
		</section>
		<?php
	}
}
