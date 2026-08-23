<?php
/**
 * Product Tabs widget.
 *
 * @package PixelomaticCore
 */

namespace PixelomaticCore\Elementor\Widgets;

defined( 'ABSPATH' ) || exit;

use PixelomaticCore\Elementor\Base\Traits\Has_Style_Controls;
use PixelomaticCore\Elementor\Base\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Plugin as Elementor_Plugin;

/**
 * Description, changelog and reviews for the current product.
 *
 * A delegate, deliberately. The theme owns template-parts/product/tabs.php
 * and the single-product template renders the same file, so placing this in a
 * builder layout cannot produce different markup from the template it
 * replaces. If the component changes, it changes in one place.
 */
final class Product_Tabs extends Widget_Base {

	use Has_Style_Controls;

	/**
	 * Widget slug.
	 *
	 * @return string
	 */
	public static function slug(): string {
		return 'product-tabs';
	}

	/**
	 * Registers controls.
	 *
	 * @return void
	 */
	protected function register_controls(): void {
		$this->start_controls_section( 'content', array( 'label' => __( 'Product tabs', 'pixelomatic-core' ) ) );

		$this->add_control(
			'notice',
			array(
				'type'            => Controls_Manager::RAW_HTML,
				'raw'             => esc_html__( 'Renders the current product. Place it on a single-product template; elsewhere it previews the most recent product in the editor and renders nothing on the front end.', 'pixelomatic-core' ),
				'content_classes' => 'elementor-descriptor',
			)
		);

		$this->end_controls_section();

		$this->start_style_section( 'style_tabs', __( 'Tabs', 'pixelomatic-core' ) );

		$this->register_button_style(
			'tab',
			__( 'Tab', 'pixelomatic-core' ),
			'{{WRAPPER}} .detail-tabs button',
			array( 'separator' => 'none' )
		);

		// The selected tab is marked with aria-selected rather than a class, so
		// the accessible state and the styled state cannot drift apart.
		$this->add_control(
			'tab_color_active',
			array(
				'label'     => __( 'Selected tab colour', 'pixelomatic-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .detail-tabs button[aria-selected="true"]' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'tab_background_active',
			array(
				'label'     => __( 'Selected tab background', 'pixelomatic-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .detail-tabs button[aria-selected="true"]' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->register_gap_style( 'tabs_gap', __( 'Gap between tabs', 'pixelomatic-core' ), '{{WRAPPER}} .detail-tabs', 40 );

		$this->end_controls_section();

		$this->start_style_section( 'style_panels', __( 'Panels', 'pixelomatic-core' ) );

		$this->register_box_style(
			'panel',
			__( 'Panel', 'pixelomatic-core' ),
			'{{WRAPPER}} .tab-panel',
			array(
				'separator' => 'none',
				'shadow'    => false,
			)
		);

		$this->register_text_style( 'panel_text', __( 'Body text', 'pixelomatic-core' ), '{{WRAPPER}} .tab-panel .prose' );

		$this->register_text_style(
			'panel_heading',
			__( 'Headings', 'pixelomatic-core' ),
			'{{WRAPPER}} .tab-panel h2, {{WRAPPER}} .tab-panel h3',
			array( 'spacing' => false )
		);

		$this->register_link_style( 'panel_link', __( 'Links', 'pixelomatic-core' ), '{{WRAPPER}} .tab-panel .prose a' );

		$this->end_controls_section();

		$this->start_style_section( 'style_changelog', __( 'Changelog', 'pixelomatic-core' ) );

		$this->register_box_style(
			'changelog_entry',
			__( 'Entry', 'pixelomatic-core' ),
			'{{WRAPPER}} .changelog__entry',
			array(
				'separator' => 'none',
				'shadow'    => false,
			)
		);

		$this->register_text_style( 'changelog_version', __( 'Version', 'pixelomatic-core' ), '{{WRAPPER}} .changelog__version' );

		$this->register_text_style( 'changelog_date', __( 'Date', 'pixelomatic-core' ), '{{WRAPPER}} .changelog__date' );

		$this->register_text_style(
			'changelog_items',
			__( 'Items', 'pixelomatic-core' ),
			'{{WRAPPER}} .changelog__items',
			array( 'spacing' => false )
		);

		$this->end_controls_section();

		$this->start_style_section( 'style_reviews', __( 'Reviews', 'pixelomatic-core' ) );

		$this->register_text_style(
			'review_score',
			__( 'Average score', 'pixelomatic-core' ),
			'{{WRAPPER}} .pix-rating-summary__score',
			array( 'separator' => 'none' )
		);

		$this->register_text_style( 'review_stars', __( 'Stars', 'pixelomatic-core' ), '{{WRAPPER}} .stars' );

		$this->add_control(
			'review_bar_color',
			array(
				'label'     => __( 'Rating bar colour', 'pixelomatic-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .rating-bars__fill' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->register_box_style(
			'review_card',
			__( 'Review', 'pixelomatic-core' ),
			'{{WRAPPER}} .detail-review',
			array( 'shadow' => false )
		);

		$this->register_text_style( 'review_quote', __( 'Review text', 'pixelomatic-core' ), '{{WRAPPER}} .detail-review__quote' );

		$this->register_text_style(
			'review_meta',
			__( 'Review meta', 'pixelomatic-core' ),
			'{{WRAPPER}} .detail-review__meta, {{WRAPPER}} .detail-review__date',
			array( 'spacing' => false )
		);

		$this->end_controls_section();
	}

	/**
	 * Renders the widget.
	 *
	 * @return void
	 */
	protected function render(): void {
		$id = $this->resolve_download();

		if ( ! $id ) {
			return;
		}

		// The template part reads the global post, so it is set for the render
		// and restored straight after. Leaving it changed would corrupt every
		// widget below this one on the page.
		global $post;
		$original = $post;

		$post = get_post( $id ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- Restored below.
		setup_postdata( $post );

		get_template_part( 'template-parts/product/tabs' );

		$post = $original; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- Restoring.
		wp_reset_postdata();
	}

	/**
	 * Returns the download this widget should render.
	 *
	 * On a single-product view that is the queried product. In the editor
	 * there is no queried product, so it falls back to the most recent one and
	 * the canvas shows something real instead of an empty box.
	 *
	 * @return int Download ID, or 0.
	 */
	private function resolve_download(): int {
		if ( is_singular( 'download' ) ) {
			return (int) get_queried_object_id();
		}

		$editor = Elementor_Plugin::instance()->editor;

		if ( ! $editor || ! $editor->is_edit_mode() ) {
			return 0;
		}

		$preview = get_posts(
			array(
				'post_type'      => 'download',
				'post_status'    => 'publish',
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'no_found_rows'  => true,
			)
		);

		return empty( $preview ) ? 0 : (int) $preview[0];
	}
}
