<?php
/**
 * Product Slider widget.
 *
 * @package PixelomaticCore
 */

namespace PixelomaticCore\Elementor\Widgets;

defined( 'ABSPATH' ) || exit;

use PixelomaticCore\Elementor\Base\Traits\Has_Product_Card_Style;
use PixelomaticCore\Elementor\Base\Traits\Has_Query_Controls;
use PixelomaticCore\Elementor\Base\Traits\Has_Section_Head;
use PixelomaticCore\Elementor\Base\Traits\Has_Slider_Controls;
use PixelomaticCore\Elementor\Base\Traits\Has_Style_Controls;
use PixelomaticCore\Elementor\Base\Widget_Base;
use Elementor\Controls_Manager;

/**
 * Products in a carousel.
 *
 * The slides are the same product cards as everywhere else; the carousel
 * around them is the shared pix-carousel, so this widget declares no slider
 * markup, no slider controls and no script of its own — Has_Slider_Controls
 * supplies all three.
 *
 * It used to be a hand-rolled scroll-snap track on the grounds that Swiper was
 * not worth 140 KB. That reasoning no longer holds: Elementor ships Swiper
 * 8.4.5 and registers it whether we use it or not, so the library is already
 * on the page. What the hand-rolled version cost instead was a second slider
 * implementation to keep in step with the first.
 *
 * The scroll-snap fallback survives the move — the track is still a snapping
 * row until Swiper takes over, so a page with no JavaScript still swipes.
 */
final class Product_Slider extends Widget_Base {

	use Has_Section_Head;
	use Has_Slider_Controls;
	use Has_Query_Controls;
	use Has_Style_Controls;
	use Has_Product_Card_Style;

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
		$this->start_controls_section( 'head', array( 'label' => __( 'Section head', 'pixelomatic-core' ) ) );
		$this->register_section_head_controls(
			__( 'Recently released', 'pixelomatic-core' ),
			__( 'New in the catalogue', 'pixelomatic-core' )
		);
		$this->end_controls_section();

		$this->start_controls_section( 'query', array( 'label' => __( 'Products', 'pixelomatic-core' ) ) );
		$this->register_query_controls( 8 );
		$this->end_controls_section();

		$this->start_controls_section( 'slider', array( 'label' => __( 'Slider', 'pixelomatic-core' ) ) );

		// Four across at 1440 with a peek on a phone: a product card is
		// narrower than a review card, so the row carries one more.
		$this->register_slider_controls(
			array(
				'slides_to_show'        => '4',
				'slides_to_show_tablet' => '3',
				'slides_to_show_mobile' => '1.3',
				'space_between'         => 20,
				'space_between_mobile'  => 14,
			)
		);

		$this->end_controls_section();

		$this->start_style_section( 'style_head', __( 'Section head', 'pixelomatic-core' ) );
		$this->register_section_head_style_controls();
		$this->end_controls_section();

		$this->start_style_section( 'style_card', __( 'Product cards', 'pixelomatic-core' ) );
		$this->register_product_card_style_controls();
		$this->end_controls_section();

		$this->start_style_section( 'style_nav', __( 'Slider controls', 'pixelomatic-core' ) );
		$this->register_slider_style_controls();
		$this->end_controls_section();

		$this->start_style_section( 'style_band', __( 'Band', 'pixelomatic-core' ) );

		$this->register_box_style(
			'band',
			__( 'Band', 'pixelomatic-core' ),
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
		if ( ! class_exists( '\Pixelomatic\Frontend\Card' ) ) {
			return;
		}

		$query = $this->product_query();

		if ( ! $query->have_posts() ) {
			wp_reset_postdata();
			return;
		}

		$label = $this->text( 'title', __( 'Products', 'pixelomatic-core' ) );
		?>
		<section class="section">
			<div class="container section__inner">
				<?php $this->render_section_head(); ?>

				<?php
				// The carousel shell, its controls and its Swiper config all
				// come from Has_Slider_Controls. This widget supplies slides.
				$this->render_slider_start();

				while ( $query->have_posts() ) :
					$query->the_post();

					// The slide wrapper is the widget's, the card inside it is
					// the theme's. Swiper finds slides by `.swiper-slide` and
					// nothing else, so a card echoed straight into the track is
					// a track with no slides — and the theme's card template
					// cannot carry the class, because the same card renders in
					// grids and lists that are not sliders.
					echo '<div class="swiper-slide pix-carousel__slide">';

					\Pixelomatic\Frontend\Card::render(
						(int) get_the_ID(),
						array(
							'density' => 'sm',
							'context' => 'slider',
						)
					);

					echo '</div>';
				endwhile;

				wp_reset_postdata();

				$this->render_slider_end(
					array(
						'prev_label' => __( 'Previous products', 'pixelomatic-core' ),
						'next_label' => __( 'Next products', 'pixelomatic-core' ),
					)
				);
				?>
			</div>
		</section>
		<?php
	}
}
