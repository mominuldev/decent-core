<?php
/**
 * Plugin List widget.
 *
 * @package PixelomaticCore
 */

namespace PixelomaticCore\Elementor\Widgets;

defined( 'ABSPATH' ) || exit;

use PixelomaticCore\Elementor\Base\Traits\Has_Query_Controls;
use PixelomaticCore\Elementor\Base\Traits\Has_Section_Head;
use PixelomaticCore\Elementor\Base\Traits\Has_Style_Controls;
use PixelomaticCore\Elementor\Base\Widget_Base;

/**
 * Products as wide rows: icon, body, price aside.
 *
 * Matches _reference/index.html's .plugin-item — icon span, .plugin-item__body
 * holding the title, description and rating line, and .plugin-item__aside
 * holding the price and button.
 */
final class Plugin_List extends Widget_Base {

	use Has_Section_Head;
	use Has_Query_Controls;
	use Has_Style_Controls;

	/**
	 * Widget slug.
	 *
	 * @return string
	 */
	public static function slug(): string {
		return 'plugin-list';
	}

	/**
	 * Registers controls.
	 *
	 * @return void
	 */
	protected function register_controls(): void {
		$this->start_controls_section( 'head', array( 'label' => __( 'Section head', 'pixelomatic-core' ) ) );
		$this->register_section_head_controls(
			__( 'The plugins we maintain in-house', 'pixelomatic-core' ),
			__( 'Our plugins', 'pixelomatic-core' )
		);
		$this->end_controls_section();

		$this->start_controls_section( 'query', array( 'label' => __( 'Products', 'pixelomatic-core' ) ) );
		$this->register_query_controls( 4 );
		$this->end_controls_section();

		$this->start_style_section( 'style_head', __( 'Section head', 'pixelomatic-core' ) );
		$this->register_section_head_style_controls();
		$this->end_controls_section();

		$this->start_style_section( 'style_rows', __( 'Rows', 'pixelomatic-core' ) );

		$this->register_box_style(
			'row',
			__( 'Row', 'pixelomatic-core' ),
			'{{WRAPPER}} .plugin-item',
			array( 'separator' => 'none' )
		);

		$this->register_icon_style( 'row_icon', __( 'Icon', 'pixelomatic-core' ), '{{WRAPPER}} .plugin-item__icon' );

		$this->register_text_style( 'row_title', __( 'Title', 'pixelomatic-core' ), '{{WRAPPER}} .plugin-item__body h3' );

		$this->register_link_style( 'row_title_link', __( 'Title link', 'pixelomatic-core' ), '{{WRAPPER}} .plugin-item__body h3 a' );

		$this->register_text_style( 'row_text', __( 'Description', 'pixelomatic-core' ), '{{WRAPPER}} .plugin-item__body > p' );

		$this->register_text_style(
			'row_rating',
			__( 'Rating line', 'pixelomatic-core' ),
			'{{WRAPPER}} .plugin-item .rating-line',
			array( 'spacing' => false )
		);

		$this->register_text_style(
			'row_stars',
			__( 'Stars', 'pixelomatic-core' ),
			'{{WRAPPER}} .plugin-item .stars',
			array( 'spacing' => false )
		);

		$this->register_gap_style( 'row_gap', __( 'Column gap', 'pixelomatic-core' ), '{{WRAPPER}} .plugin-item', 48 );

		$this->end_controls_section();

		$this->start_style_section( 'style_aside', __( 'Price and button', 'pixelomatic-core' ) );

		$this->register_text_style(
			'row_price',
			__( 'Price', 'pixelomatic-core' ),
			'{{WRAPPER}} .plugin-item__price',
			array( 'separator' => 'none' )
		);

		$this->register_button_style( 'row_button', __( 'Button', 'pixelomatic-core' ), '{{WRAPPER}} .plugin-item__aside .btn' );

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

		$this->register_gap_style( 'list_gap', __( 'Gap between rows', 'pixelomatic-core' ), '{{WRAPPER}} .plugin-list', 48 );

		$this->end_controls_section();
	}

	/**
	 * Maps a card accent onto a .plugin-item__icon modifier.
	 *
	 * @param string $accent Card accent name.
	 * @return string
	 */
	private static function icon_accent( string $accent ): string {
		$map = array(
			'blue'      => 'brand',
			'blue-dark' => 'brand',
			'green'     => 'green',
			'yellow'    => 'sun',
			'red'       => 'sun',
			'gray'      => 'ink',
		);

		return $map[ $accent ] ?? 'brand';
	}

	/**
	 * Renders the widget.
	 *
	 * @return void
	 */
	protected function render(): void {
		$query = $this->product_query();

		if ( ! $query->have_posts() ) {
			wp_reset_postdata();
			return;
		}

		$has_card = class_exists( '\Pixelomatic\Frontend\Card' );
		?>
		<section class="section">
			<div class="container section__inner section__inner--tight">
				<?php $this->render_section_head(); ?>

				<ul class="plugin-list">
					<?php
					while ( $query->have_posts() ) :
						$query->the_post();

						$id     = (int) get_the_ID();
						$rating = $has_card
							? \Pixelomatic\Frontend\Card::rating( $id )
							: array(
								'average' => 0.0,
								'count'   => 0,
							);
						// .plugin-item__icon uses a different vocabulary from the
						// card's accents — brand/green/sun/ink rather than
						// blue/green/yellow. An unmapped value produces a class
						// that does not exist, which renders as a white icon on
						// a white tile: invisible, and silent.
						$accent = $has_card ? self::icon_accent( \Pixelomatic\Frontend\Card::type( $id )['accent'] ) : 'brand';
						?>
						<li class="plugin-item">
							<span class="plugin-item__icon <?php echo esc_attr( 'plugin-item__icon--' . $accent ); ?>">
								<?php $this->icon( 'plug', 24, 1.5 ); ?>
							</span>

							<div class="plugin-item__body">
								<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
								<p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 24 ) ); ?></p>

								<?php // Explicitly gated on the theme, not just on the count: without it the count is 0, but relying on that is an invisible coupling. ?>
								<?php if ( $has_card && $rating['count'] > 0 ) : ?>
									<p class="rating-line">
										<span class="stars" aria-hidden="true">
											<?php echo esc_html( \Pixelomatic\Frontend\Card::stars( $rating['average'] ) ); ?>
										</span>
										<strong><?php echo esc_html( number_format_i18n( $rating['average'], 1 ) ); ?></strong>
										<span>
											<?php
											printf(
												/* translators: %s: review count. */
												esc_html( _n( '(%s review)', '(%s reviews)', $rating['count'], 'pixelomatic-core' ) ),
												esc_html( number_format_i18n( $rating['count'] ) )
											);
											?>
										</span>
									</p>
								<?php endif; ?>
							</div>

							<div class="plugin-item__aside">
								<?php if ( function_exists( 'edd_currency_filter' ) ) : ?>
									<p class="plugin-item__price">
										<?php esc_html_e( 'From', 'pixelomatic-core' ); ?>
										<strong><?php echo esc_html( (string) edd_currency_filter( edd_format_amount( (float) get_post_meta( $id, '_pixelomatic_price_min', true ) ) ) ); ?></strong>
									</p>
								<?php endif; ?>

								<a class="btn btn--primary btn--sm" href="<?php the_permalink(); ?>">
									<?php esc_html_e( 'View product', 'pixelomatic-core' ); ?>
								</a>
							</div>
						</li>
						<?php
					endwhile;

					wp_reset_postdata();
					?>
				</ul>
			</div>
		</section>
		<?php
	}
}
