<?php
/**
 * Product Specifications widget.
 *
 * @package PixelomaticCore
 */

namespace PixelomaticCore\Elementor\Widgets;

defined( 'ABSPATH' ) || exit;

use PixelomaticCore\Elementor\Base\Traits\Has_Product_Context;
use PixelomaticCore\Elementor\Base\Traits\Has_Section_Head;
use PixelomaticCore\Elementor\Base\Traits\Has_Style_Controls;
use PixelomaticCore\Elementor\Base\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Repeater;

/**
 * The current product's technical detail, grouped into spec cards.
 *
 * A delegate, deliberately. The theme owns
 * template-parts/product/section-specifications.php and the single-product
 * template renders the same file, so placing this in a builder layout cannot
 * produce different markup from the template it replaces.
 */
final class Product_Specifications extends Widget_Base {

	use Has_Style_Controls;
	use Has_Product_Context;
	use Has_Section_Head;

	/**
	 * Widget slug.
	 *
	 * @return string
	 */
	public static function slug(): string {
		return 'product-specifications';
	}

	/**
	 * Registers controls.
	 *
	 * @return void
	 */
	protected function register_controls(): void {
		$this->start_controls_section( 'content', array( 'label' => __( 'Specifications', 'pixelomatic-core' ) ) );

		$this->register_product_notice();

		$this->register_section_head_controls(
			__( 'Technical detail, before you buy', 'pixelomatic-core' ),
			__( 'Specifications', 'pixelomatic-core' )
		);

		$repeater = new Repeater();

		$repeater->add_control(
			'title',
			array(
				'label'       => __( 'Card title', 'pixelomatic-core' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Details', 'pixelomatic-core' ),
				'label_block' => true,
			)
		);

		// One row per line rather than a repeater inside a repeater, which
		// Elementor has no control for. `Label | Value` is also how the
		// product's own Specifications field is written, so the two sources
		// are typed the same way.
		$repeater->add_control(
			'rows',
			array(
				'label'       => __( 'Rows', 'pixelomatic-core' ),
				'type'        => Controls_Manager::TEXTAREA,
				'rows'        => 6,
				'description' => __( 'One row per line, as <code>Label | Value</code>. Add <code>| mono</code> to set a value in the monospaced face, the way version numbers are shown.', 'pixelomatic-core' ),
				'label_block' => true,
			)
		);

		$this->add_control(
			'specs',
			array(
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'title_field' => '{{{ title }}}',
				'default'     => array(
					array(
						'title' => __( 'The product', 'pixelomatic-core' ),
						'rows'  => __( "Product type | WordPress theme\nCurrent version | 3.2.0 | mono\nFirst released | March 2024\nLast update | 21 August 2026", 'pixelomatic-core' ),
					),
					array(
						'title' => __( 'Compatibility', 'pixelomatic-core' ),
						'rows'  => __( "WordPress | 6.4+ | mono\nPHP | 8.1 – 8.4 | mono\nMultisite | Supported\nBrowsers | Two latest of each", 'pixelomatic-core' ),
					),
				),
			)
		);

		$this->end_controls_section();

		$this->start_style_section( 'style_head', __( 'Section head', 'pixelomatic-core' ) );

		$this->register_section_head_style_controls();

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
				),
				'selectors' => array(
					'{{WRAPPER}} .spec-grid' => 'grid-template-columns: repeat({{VALUE}}, minmax(0, 1fr));',
				),
			)
		);

		$this->register_gap_style( 'grid_gap', __( 'Gap', 'pixelomatic-core' ), '{{WRAPPER}} .spec-grid', 56 );

		$this->register_box_style(
			'section',
			__( 'Section', 'pixelomatic-core' ),
			'{{WRAPPER}} .detail-section',
			array( 'shadow' => false )
		);

		$this->end_controls_section();

		$this->start_style_section( 'style_card', __( 'Card', 'pixelomatic-core' ) );

		$this->register_box_style(
			'card',
			__( 'Card', 'pixelomatic-core' ),
			'{{WRAPPER}} .spec-card',
			array( 'separator' => 'none' )
		);

		$this->register_text_style( 'card_title', __( 'Group title', 'pixelomatic-core' ), '{{WRAPPER}} .spec-card__title' );

		$this->register_text_style( 'row_label', __( 'Row label', 'pixelomatic-core' ), '{{WRAPPER}} .spec-card__row dt' );

		$this->register_text_style(
			'row_value',
			__( 'Row value', 'pixelomatic-core' ),
			'{{WRAPPER}} .spec-card__row dd',
			array( 'spacing' => false )
		);

		$this->add_control(
			'row_divider',
			array(
				'label'     => __( 'Row divider colour', 'pixelomatic-core' ),
				'type'      => Controls_Manager::COLOR,
				'separator' => 'before',
				'selectors' => array(
					'{{WRAPPER}} .spec-card__row' => 'border-color: {{VALUE}};',
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
		$args           = $this->head_args();
		$args['groups'] = $this->groups();

		$this->render_product_part( 'section', 'specifications', $args );
	}

	/**
	 * The cards as the theme's part takes them.
	 *
	 * @return array<int, array{title:string, rows:array<int, array{label:string, value:string, mono:bool}>}>
	 */
	private function groups(): array {
		$cards  = (array) $this->get_settings_for_display( 'specs' );
		$groups = array();

		foreach ( $cards as $card ) {
			$card = (array) $card;
			$rows = array();

			foreach ( self::lines( (string) ( $card['rows'] ?? '' ) ) as $line ) {
				$parts = array_map( 'trim', explode( '|', $line ) );
				$label = (string) array_shift( $parts );

				// Only the literal word ends a row as a flag. A value that
				// happens to contain a pipe — "8.1 | 8.2" — keeps both halves.
				$mono = ! empty( $parts ) && 'mono' === strtolower( (string) end( $parts ) );

				if ( $mono ) {
					array_pop( $parts );
				}

				$value = implode( ' | ', $parts );

				if ( '' === $label ) {
					continue;
				}

				$rows[] = array(
					'label' => $label,
					'value' => $value,
					'mono'  => $mono,
				);
			}

			if ( empty( $rows ) ) {
				continue;
			}

			$groups[] = array(
				'title' => (string) ( $card['title'] ?? '' ),
				'rows'  => $rows,
			);
		}

		return $groups;
	}
}
