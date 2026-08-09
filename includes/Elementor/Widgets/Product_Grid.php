<?php
/**
 * Product Grid widget.
 *
 * @package DecentCore
 */

namespace DecentCore\Elementor\Widgets;

defined( 'ABSPATH' ) || exit;

use DecentCore\Elementor\Base\Traits\Has_Grid_Controls;
use DecentCore\Elementor\Base\Traits\Has_Query_Controls;
use DecentCore\Elementor\Base\Traits\Has_Section_Head;
use DecentCore\Elementor\Base\Widget_Base;
use Elementor\Controls_Manager;

/**
 * Products in a grid.
 *
 * Renders through the theme's Card::render(). That is the single most
 * important line in this widget: the card is implemented once, in the theme,
 * and the archive loop, the related row, the AJAX filter response and this
 * widget all call it. A widget that emitted its own .product-card markup
 * would fork the design system on day one — and bin/check.sh in the theme
 * fails the build if any file outside template-parts/product/ tries.
 */
final class Product_Grid extends Widget_Base {

	use Has_Section_Head;
	use Has_Grid_Controls;
	use Has_Query_Controls;

	/**
	 * Widget slug.
	 *
	 * @return string
	 */
	public static function slug(): string {
		return 'product-grid';
	}

	/**
	 * Registers controls.
	 *
	 * @return void
	 */
	protected function register_controls(): void {
		$this->start_controls_section( 'head', array( 'label' => __( 'Section head', 'decent-core' ) ) );
		$this->register_section_head_controls(
			__( 'The products our customers ship most', 'decent-core' ),
			__( 'Handpicked resources', 'decent-core' )
		);
		$this->end_controls_section();

		$this->start_controls_section( 'query', array( 'label' => __( 'Products', 'decent-core' ) ) );
		$this->register_query_controls( 3 );

		$this->add_control(
			'density',
			array(
				'label'   => __( 'Card size', 'decent-core' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'md',
				'options' => array(
					'sm' => __( 'Compact', 'decent-core' ),
					'md' => __( 'Standard', 'decent-core' ),
				),
			)
		);

		$this->add_control(
			'show_actions',
			array(
				'label'   => __( 'Show buttons', 'decent-core' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
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
		if ( ! class_exists( '\DecentThemes\Frontend\Card' ) ) {
			$this->render_unavailable();
			return;
		}

		$settings = $this->get_settings_for_display();
		$query    = $this->product_query();

		if ( ! $query->have_posts() ) {
			wp_reset_postdata();
			return;
		}
		?>
		<section class="section">
			<div class="container section__inner section__inner--tight">
				<?php $this->render_section_head(); ?>

				<ul class="product-grid decent-grid">
					<?php
					while ( $query->have_posts() ) :
						$query->the_post();

						\DecentThemes\Frontend\Card::render(
							(int) get_the_ID(),
							array(
								'density' => (string) ( $settings['density'] ?? 'md' ),
								'actions' => 'yes' === ( $settings['show_actions'] ?? 'yes' ),
								'context' => 'widget',
							)
						);
					endwhile;

					wp_reset_postdata();
					?>
				</ul>
			</div>
		</section>
		<?php
	}

	/**
	 * Explains why nothing rendered, in the editor only.
	 *
	 * On the front end an unavailable widget renders nothing at all — a
	 * visitor should never see a message about a missing theme.
	 *
	 * @return void
	 */
	private function render_unavailable(): void {
		if ( ! \Elementor\Plugin::instance()->editor->is_edit_mode() ) {
			return;
		}

		printf(
			'<p style="padding:16px;border:1px dashed #ced4da;color:#6c757d">%s</p>',
			esc_html__( 'Product Grid needs the Decent Themes theme, which owns the product card markup.', 'decent-core' )
		);
	}
}
