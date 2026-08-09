<?php
/**
 * Product Rank List widget.
 *
 * @package DecentCore
 */

namespace DecentCore\Elementor\Widgets;

defined( 'ABSPATH' ) || exit;

use DecentCore\Elementor\Base\Traits\Has_Query_Controls;
use DecentCore\Elementor\Base\Traits\Has_Section_Head;
use DecentCore\Elementor\Base\Widget_Base;

/**
 * The numbered best-seller table from the design.
 */
final class Product_Rank_List extends Widget_Base {

	use Has_Section_Head;
	use Has_Query_Controls;

	/**
	 * Widget slug.
	 *
	 * @return string
	 */
	public static function slug(): string {
		return 'product-rank-list';
	}

	/**
	 * Registers controls.
	 *
	 * @return void
	 */
	protected function register_controls(): void {
		$this->start_controls_section( 'head', array( 'label' => __( 'Section head', 'decent-core' ) ) );
		$this->register_section_head_controls(
			__( 'What teams bought most this quarter', 'decent-core' ),
			__( 'Best sellers', 'decent-core' )
		);
		$this->end_controls_section();

		$this->start_controls_section( 'query', array( 'label' => __( 'Products', 'decent-core' ) ) );
		$this->register_query_controls( 5 );
		$this->end_controls_section();
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

		$rank = 0;
		?>
		<section class="section">
			<div class="container section__inner section__inner--tight">
				<?php $this->render_section_head(); ?>

				<ul class="rank-list">
					<?php
					while ( $query->have_posts() ) :
						$query->the_post();
						++$rank;

						$id     = (int) get_the_ID();
						$rating = class_exists( '\DecentThemes\Frontend\Card' )
							? \DecentThemes\Frontend\Card::rating( $id )
							: array(
								'average' => 0.0,
								'count'   => 0,
							);
						$sales  = function_exists( 'edd_get_download_sales_stats' )
							? (int) edd_get_download_sales_stats( $id )
							: 0;
						?>
						<li>
							<span class="rank-list__rank"><?php echo esc_html( str_pad( (string) $rank, 2, '0', STR_PAD_LEFT ) ); ?></span>

							<div class="rank-list__thumb" aria-hidden="true">
								<?php echo esc_html( mb_strtoupper( mb_substr( wp_strip_all_tags( get_the_title() ), 0, 2 ) ) ); ?>
							</div>

							<div>
								<div class="rank-list__head">
									<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
								</div>
								<?php $terms = get_the_terms( $id, 'download_category' ); ?>
								<?php if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) : ?>
									<p class="rank-list__sub"><?php echo esc_html( $terms[0]->name ); ?></p>
								<?php endif; ?>
							</div>

							<?php if ( $sales > 0 ) : ?>
								<p class="rank-list__stat">
									<strong><?php echo esc_html( number_format_i18n( $sales ) ); ?></strong>
									<?php esc_html_e( 'sales', 'decent-core' ); ?>
								</p>
							<?php endif; ?>

							<?php if ( $rating['count'] > 0 ) : ?>
								<p class="rank-list__stat">
									<span class="stars" aria-hidden="true">&#9733;</span>
									<strong><?php echo esc_html( number_format_i18n( $rating['average'], 1 ) ); ?></strong>
								</p>
							<?php endif; ?>

							<div class="rank-list__buy">
								<?php if ( function_exists( 'edd_price' ) ) : ?>
									<span><?php echo esc_html( (string) edd_currency_filter( edd_format_amount( (float) get_post_meta( $id, '_decent_price_min', true ) ) ) ); ?></span>
								<?php endif; ?>
								<a href="<?php the_permalink(); ?>"><?php esc_html_e( 'View', 'decent-core' ); ?></a>
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
