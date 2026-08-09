<?php
/**
 * Product Archive widget.
 *
 * @package DecentCore
 */

namespace DecentCore\Elementor\Widgets;

defined( 'ABSPATH' ) || exit;

use DecentCore\Elementor\Base\Widget_Base;
use Elementor\Controls_Manager;

/**
 * The catalogue: filter sidebar, toolbar, grid and pagination.
 *
 * Renders the theme's own catalogue parts against the main query, so the
 * server-side filtering, the URL contract and the AJAX layer all keep working
 * exactly as they do on archive-download.php. A widget that re-queried would
 * quietly break the "every filter is a shareable URL" guarantee.
 */
final class Product_Archive extends Widget_Base {

	/**
	 * Widget slug.
	 *
	 * @return string
	 */
	public static function slug(): string {
		return 'product-archive';
	}

	/**
	 * Registers controls.
	 *
	 * @return void
	 */
	protected function register_controls(): void {
		$this->start_controls_section( 'content', array( 'label' => __( 'Catalogue', 'decent-core' ) ) );

		$this->add_control(
			'show_filters',
			array(
				'label'   => __( 'Filter sidebar', 'decent-core' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);

		$this->add_control(
			'show_toolbar',
			array(
				'label'   => __( 'Toolbar', 'decent-core' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);

		$this->add_control(
			'notice',
			array(
				'type'            => Controls_Manager::RAW_HTML,
				'raw'             => esc_html__( 'Reads the main query, so filtering, sorting and pagination keep working through the URL. Place it on a product archive template.', 'decent-core' ),
				'content_classes' => 'elementor-descriptor',
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
		if ( ! function_exists( 'decent_pagination' ) ) {
			return;
		}

		$settings = $this->get_settings_for_display();
		$filters  = 'yes' === ( $settings['show_filters'] ?? 'yes' );
		?>
		<div class="container catalog">
			<?php
			if ( $filters ) {
				get_template_part( 'template-parts/product/filters' );
			}
			?>

			<div class="catalog__main">
				<?php
				if ( 'yes' === ( $settings['show_toolbar'] ?? 'yes' ) ) {
					get_template_part( 'template-parts/product/toolbar' );
				}
				?>

				<?php if ( have_posts() ) : ?>
					<?php
					get_template_part(
						'template-parts/product/grid',
						null,
						array(
							'density'   => 'sm',
							'lcp_first' => true,
						)
					);
					?>

					<?php decent_pagination(); ?>

				<?php else : ?>
					<?php
					get_template_part(
						'template-parts/global/empty-state',
						null,
						array(
							'icon'  => 'search',
							'tag'   => 'h2',
							'title' => __( 'No products match those filters', 'decent-core' ),
							'text'  => __( 'Try widening the price range, or clear the filters to see the whole catalogue.', 'decent-core' ),
						)
					);
					?>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}
}
