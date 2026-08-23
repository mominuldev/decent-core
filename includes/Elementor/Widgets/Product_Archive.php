<?php
/**
 * Product Archive widget.
 *
 * @package PixelomaticCore
 */

namespace PixelomaticCore\Elementor\Widgets;

defined( 'ABSPATH' ) || exit;

use PixelomaticCore\Elementor\Base\Traits\Has_Product_Card_Style;
use PixelomaticCore\Elementor\Base\Traits\Has_Style_Controls;
use PixelomaticCore\Elementor\Base\Widget_Base;
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

	use Has_Style_Controls;
	use Has_Product_Card_Style;

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
		$this->start_controls_section( 'content', array( 'label' => __( 'Catalogue', 'pixelomatic-core' ) ) );

		$this->add_control(
			'show_filters',
			array(
				'label'   => __( 'Filter sidebar', 'pixelomatic-core' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);

		$this->add_control(
			'show_toolbar',
			array(
				'label'   => __( 'Toolbar', 'pixelomatic-core' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);

		$this->add_control(
			'notice',
			array(
				'type'            => Controls_Manager::RAW_HTML,
				'raw'             => esc_html__( 'Reads the main query, so filtering, sorting and pagination keep working through the URL. Place it on a product archive template.', 'pixelomatic-core' ),
				'content_classes' => 'elementor-descriptor',
			)
		);

		$this->end_controls_section();

		$this->start_style_section( 'style_card', __( 'Product cards', 'pixelomatic-core' ) );
		$this->register_product_card_style_controls();
		$this->end_controls_section();

		$this->start_style_section( 'style_grid', __( 'Grid', 'pixelomatic-core' ) );

		$this->add_responsive_control(
			'columns',
			array(
				'label'     => __( 'Columns', 'pixelomatic-core' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => array(
					''  => __( 'Theme default', 'pixelomatic-core' ),
					'1' => '1',
					'2' => '2',
					'3' => '3',
					'4' => '4',
				),
				'selectors' => array(
					'{{WRAPPER}} .catalog__grid' => 'grid-template-columns: repeat({{VALUE}}, minmax(0, 1fr));',
				),
			)
		);

		$this->register_gap_style( 'grid_gap', __( 'Gap', 'pixelomatic-core' ), '{{WRAPPER}} .catalog__grid', 56 );

		$this->add_responsive_control(
			'sidebar_width',
			array(
				'label'      => __( 'Sidebar width', 'pixelomatic-core' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min'  => 180,
						'max'  => 400,
						'step' => 10,
					),
				),
				// .catalog is a flex row, so the sidebar's width is its
				// flex-basis. Setting grid-template-columns here would be a
				// declaration the browser drops without a word.
				'selectors'  => array(
					'{{WRAPPER}} .catalog__sidebar' => 'flex-basis: {{SIZE}}{{UNIT}};',
				),
				'condition'  => array( 'show_filters' => 'yes' ),
			)
		);

		$this->end_controls_section();

		$this->start_style_section(
			'style_filters',
			__( 'Filters', 'pixelomatic-core' ),
			array( 'condition' => array( 'show_filters' => 'yes' ) )
		);

		$this->register_box_style(
			'filter_panel',
			__( 'Panel', 'pixelomatic-core' ),
			'{{WRAPPER}} .filter-panel',
			array( 'separator' => 'none' )
		);

		$this->register_text_style( 'filter_head', __( 'Panel heading', 'pixelomatic-core' ), '{{WRAPPER}} .filter-panel h3' );

		$this->register_text_style( 'filter_label', __( 'Field label', 'pixelomatic-core' ), '{{WRAPPER}} .catalog__sidebar .field-label' );

		$this->register_box_style(
			'filter_input',
			__( 'Search field', 'pixelomatic-core' ),
			'{{WRAPPER}} .catalog__sidebar .input',
			array( 'shadow' => false )
		);

		$this->register_text_style(
			'filter_input_text',
			__( 'Search field text', 'pixelomatic-core' ),
			'{{WRAPPER}} .catalog__sidebar .input',
			array(
				'heading' => false,
				'spacing' => false,
			)
		);

		$this->register_link_style( 'filter_option', __( 'Filter option', 'pixelomatic-core' ), '{{WRAPPER}} .filter-option' );

		$this->register_text_style(
			'filter_count',
			__( 'Option count', 'pixelomatic-core' ),
			'{{WRAPPER}} .filter-option__count',
			array( 'spacing' => false )
		);

		$this->end_controls_section();

		$this->start_style_section(
			'style_toolbar',
			__( 'Toolbar', 'pixelomatic-core' ),
			array( 'condition' => array( 'show_toolbar' => 'yes' ) )
		);

		$this->register_box_style(
			'toolbar',
			__( 'Bar', 'pixelomatic-core' ),
			'{{WRAPPER}} .toolbar',
			array( 'separator' => 'none' )
		);

		$this->register_text_style( 'toolbar_count', __( 'Result count', 'pixelomatic-core' ), '{{WRAPPER}} .toolbar__count' );

		$this->register_box_style(
			'toolbar_select',
			__( 'Sort field', 'pixelomatic-core' ),
			'{{WRAPPER}} .toolbar .select',
			array( 'shadow' => false )
		);

		$this->register_text_style(
			'toolbar_select_text',
			__( 'Sort field text', 'pixelomatic-core' ),
			'{{WRAPPER}} .toolbar .select',
			array(
				'heading' => false,
				'spacing' => false,
			)
		);

		$this->register_button_style( 'toolbar_button', __( 'Sort button', 'pixelomatic-core' ), '{{WRAPPER}} .toolbar .btn' );

		$this->end_controls_section();

		$this->start_style_section( 'style_pagination', __( 'Pagination', 'pixelomatic-core' ) );

		$this->register_link_style(
			'pagination_link',
			__( 'Page link', 'pixelomatic-core' ),
			'{{WRAPPER}} .pagination a',
			array( 'separator' => 'none' )
		);

		$this->register_text_style(
			'pagination_current',
			__( 'Current page', 'pixelomatic-core' ),
			'{{WRAPPER}} .pagination__current',
			array( 'spacing' => false )
		);

		// .pagination is a flex row, so its alignment is justify-content.
		$this->add_responsive_control(
			'pagination_align',
			array(
				'label'     => __( 'Alignment', 'pixelomatic-core' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => array(
					'flex-start' => array(
						'title' => __( 'Left', 'pixelomatic-core' ),
						'icon'  => 'eicon-text-align-left',
					),
					'center'     => array(
						'title' => __( 'Centre', 'pixelomatic-core' ),
						'icon'  => 'eicon-text-align-center',
					),
					'flex-end'   => array(
						'title' => __( 'Right', 'pixelomatic-core' ),
						'icon'  => 'eicon-text-align-right',
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .pagination' => 'justify-content: {{VALUE}};',
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
		if ( ! function_exists( 'pixelomatic_pagination' ) ) {
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

					<?php pixelomatic_pagination(); ?>

				<?php else : ?>
					<?php
					get_template_part(
						'template-parts/global/empty-state',
						null,
						array(
							'icon'  => 'search',
							'tag'   => 'h2',
							'title' => __( 'No products match those filters', 'pixelomatic-core' ),
							'text'  => __( 'Try widening the price range, or clear the filters to see the whole catalogue.', 'pixelomatic-core' ),
						)
					);
					?>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}
}
