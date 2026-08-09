<?php
/**
 * Product Hero widget.
 *
 * @package DecentCore
 */

namespace DecentCore\Elementor\Widgets;

defined( 'ABSPATH' ) || exit;

use DecentCore\Elementor\Base\Traits\Has_Style_Controls;
use DecentCore\Elementor\Base\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Plugin as Elementor_Plugin;

/**
 * Breadcrumb, badges, title and facts for the current product.
 *
 * A delegate, deliberately. The theme owns template-parts/product/hero.php
 * and the single-product template renders the same file, so placing this in a
 * builder layout cannot produce different markup from the template it
 * replaces. If the component changes, it changes in one place.
 */
final class Product_Hero extends Widget_Base {

	use Has_Style_Controls;

	/**
	 * Widget slug.
	 *
	 * @return string
	 */
	public static function slug(): string {
		return 'product-hero';
	}

	/**
	 * Registers controls.
	 *
	 * @return void
	 */
	protected function register_controls(): void {
		$this->start_controls_section( 'content', array( 'label' => __( 'Product hero', 'decent-core' ) ) );

		$this->add_control(
			'notice',
			array(
				'type'            => Controls_Manager::RAW_HTML,
				'raw'             => esc_html__( 'Renders the current product. Place it on a single-product template; elsewhere it previews the most recent product in the editor and renders nothing on the front end.', 'decent-core' ),
				'content_classes' => 'elementor-descriptor',
			)
		);

		$this->end_controls_section();

		$this->start_style_section( 'style_text', __( 'Text', 'decent-core' ) );

		$this->register_text_style(
			'hero_title',
			__( 'Title', 'decent-core' ),
			'{{WRAPPER}} .product-hero__title',
			array( 'separator' => 'none' )
		);

		$this->register_text_style( 'hero_text', __( 'Excerpt', 'decent-core' ), '{{WRAPPER}} .product-hero__text' );

		$this->register_text_style(
			'hero_facts',
			__( 'Facts list', 'decent-core' ),
			'{{WRAPPER}} .product-hero__facts',
			array( 'spacing' => false )
		);

		$this->register_text_style(
			'hero_stars',
			__( 'Stars', 'decent-core' ),
			'{{WRAPPER}} .product-hero__facts .stars',
			array( 'spacing' => false )
		);

		$this->register_link_style( 'hero_breadcrumb', __( 'Breadcrumb links', 'decent-core' ), '{{WRAPPER}} .breadcrumb a' );

		$this->end_controls_section();

		$this->start_style_section( 'style_badges', __( 'Badges', 'decent-core' ) );

		$this->register_box_style(
			'badge',
			__( 'Badge', 'decent-core' ),
			'{{WRAPPER}} .product-hero__badges .badge',
			array(
				'separator' => 'none',
				'shadow'    => false,
			)
		);

		$this->register_text_style(
			'badge_text',
			__( 'Badge text', 'decent-core' ),
			'{{WRAPPER}} .product-hero__badges .badge',
			array(
				'heading' => false,
				'spacing' => false,
			)
		);

		$this->register_gap_style( 'badges_gap', __( 'Gap', 'decent-core' ), '{{WRAPPER}} .product-hero__badges', 32 );

		$this->end_controls_section();

		$this->start_style_section( 'style_icon', __( 'Product icon', 'decent-core' ) );

		$this->register_icon_style(
			'hero_icon',
			__( 'Icon', 'decent-core' ),
			'{{WRAPPER}} .product-hero__icon',
			array( 'separator' => 'none' )
		);

		$this->end_controls_section();

		$this->start_style_section( 'style_band', __( 'Band', 'decent-core' ) );

		$this->register_box_style(
			'band',
			__( 'Band', 'decent-core' ),
			'{{WRAPPER}} .product-hero__inner',
			array(
				'heading' => false,
				'shadow'  => false,
			)
		);

		$this->register_gap_style( 'row_gap', __( 'Column gap', 'decent-core' ), '{{WRAPPER}} .product-hero__row', 64 );

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

		get_template_part( 'template-parts/product/hero' );

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
