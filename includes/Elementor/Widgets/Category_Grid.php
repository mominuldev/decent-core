<?php
/**
 * Category Grid widget.
 *
 * @package DecentCore
 */

namespace DecentCore\Elementor\Widgets;

defined( 'ABSPATH' ) || exit;

use DecentCore\Elementor\Base\Traits\Has_Grid_Controls;
use DecentCore\Elementor\Base\Traits\Has_Section_Head;
use DecentCore\Elementor\Base\Widget_Base;
use Elementor\Controls_Manager;

/**
 * Product categories, read from the taxonomy rather than typed in twice.
 *
 * The icon and accent come from term meta, so adding a category in the admin
 * puts it on the page with the right colour without anyone editing a widget.
 */
final class Category_Grid extends Widget_Base {

	use Has_Section_Head;
	use Has_Grid_Controls;

	/**
	 * Widget slug.
	 *
	 * @return string
	 */
	public static function slug(): string {
		return 'category-grid';
	}

	/**
	 * Registers controls.
	 *
	 * @return void
	 */
	protected function register_controls(): void {
		$this->start_controls_section( 'head', array( 'label' => __( 'Section head', 'decent-core' ) ) );
		$this->register_section_head_controls(
			__( 'Find the right stack, fast', 'decent-core' ),
			__( 'Categories', 'decent-core' )
		);
		$this->end_controls_section();

		$this->start_controls_section( 'query', array( 'label' => __( 'Categories', 'decent-core' ) ) );

		$this->add_control(
			'count',
			array(
				'label'   => __( 'How many', 'decent-core' ),
				'type'    => Controls_Manager::NUMBER,
				'default' => 8,
				'min'     => 1,
				'max'     => 24,
			)
		);

		$this->add_control(
			'style',
			array(
				'label'   => __( 'Style', 'decent-core' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'card',
				'options' => array(
					'card'  => __( 'Cards', 'decent-core' ),
					'strip' => __( 'Compact strip', 'decent-core' ),
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
		$this->register_grid_controls( 4 );
		$this->end_controls_section();
	}

	/**
	 * Renders the widget.
	 *
	 * @return void
	 */
	protected function render(): void {
		if ( ! taxonomy_exists( 'download_category' ) ) {
			return;
		}

		$settings = $this->get_settings_for_display();

		$terms = get_terms(
			array(
				'taxonomy'   => 'download_category',
				'hide_empty' => true,
				'number'     => max( 1, min( 24, (int) ( $settings['count'] ?? 8 ) ) ),
				'orderby'    => 'count',
				'order'      => 'DESC',
			)
		);

		if ( is_wp_error( $terms ) || empty( $terms ) ) {
			return;
		}

		$strip = 'strip' === ( $settings['style'] ?? 'card' );
		?>
		<section class="section">
			<div class="container section__inner section__inner--tight">
				<?php $this->render_section_head(); ?>

				<ul class="<?php echo $strip ? 'cat-strip' : 'category-grid'; ?> decent-grid">
					<?php foreach ( $terms as $term ) : ?>
						<?php
						$accent = (string) get_term_meta( $term->term_id, '_decent_cat_accent', true );
						$accent = in_array( $accent, array( 'blue', 'blue-dark', 'green', 'yellow', 'red', 'gray' ), true ) ? $accent : 'blue';
						$icon   = (string) get_term_meta( $term->term_id, '_decent_cat_icon', true );
						$icon   = '' !== $icon ? $icon : 'window';
						$blurb  = (string) get_term_meta( $term->term_id, '_decent_cat_blurb', true );
						?>
						<?php $link = (string) get_term_link( $term ); ?>

						<?php if ( $strip ) : ?>
							<?php // The compact strip IS an anchor; the card is not. ?>
							<li class="cat-strip__item">
								<a href="<?php echo esc_url( $link ); ?>">
									<span class="cat-strip__icon <?php echo esc_attr( 'cat-strip__icon--' . $accent ); ?>">
										<?php $this->icon( $icon, 22, 1.6 ); ?>
									</span>
									<h3><?php echo esc_html( $term->name ); ?></h3>
									<p class="meta">
										<?php
										printf(
											/* translators: %s: product count. */
											esc_html( _n( '%s PRODUCT', '%s PRODUCTS', (int) $term->count, 'decent-core' ) ),
											esc_html( number_format_i18n( (int) $term->count ) )
										);
										?>
									</p>
								</a>
							</li>
						<?php else : ?>
							<li class="category-card">
								<span class="category-card__icon <?php echo esc_attr( 'category-card__icon--' . $accent ); ?>">
									<?php $this->icon( $icon, 20, 1.5 ); ?>
								</span>

								<h3><?php echo esc_html( $term->name ); ?></h3>

								<?php if ( '' !== $blurb ) : ?>
									<p><?php echo esc_html( $blurb ); ?></p>
								<?php endif; ?>

								<div class="category-card__foot">
									<span class="meta">
										<?php
										printf(
											/* translators: %s: product count. */
											esc_html( _n( '%s product', '%s products', (int) $term->count, 'decent-core' ) ),
											esc_html( number_format_i18n( (int) $term->count ) )
										);
										?>
									</span>
									<a class="link-arrow" href="<?php echo esc_url( $link ); ?>">
										<?php esc_html_e( 'Browse', 'decent-core' ); ?>
									</a>
								</div>
							</li>
						<?php endif; ?>
					<?php endforeach; ?>
				</ul>
			</div>
		</section>
		<?php
	}
}
