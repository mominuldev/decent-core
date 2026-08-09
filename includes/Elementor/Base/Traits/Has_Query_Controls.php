<?php
/**
 * Download query controls.
 *
 * @package DecentCore
 */

namespace DecentCore\Elementor\Base\Traits;

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use WP_Query;

/**
 * The product query every EDD widget needs.
 *
 * The editor picks from enums; no widget setting ever becomes a meta key, an
 * orderby column or a SQL fragment. Sorting reuses the theme's own catalogue
 * mapping where it is available, so a widget and the catalogue agree on what
 * "most popular" means.
 */
trait Has_Query_Controls {

	/**
	 * Registers the query controls.
	 *
	 * @param int $default_count Default number of products.
	 * @return void
	 */
	protected function register_query_controls( int $default_count = 3 ): void {
		$this->add_control(
			'category',
			array(
				'label'       => __( 'Category', 'decent-core' ),
				'type'        => Controls_Manager::SELECT2,
				'multiple'    => true,
				'label_block' => true,
				'options'     => $this->category_options(),
			)
		);

		$this->add_control(
			'orderby',
			array(
				'label'   => __( 'Order by', 'decent-core' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'popular',
				'options' => array(
					'popular'    => __( 'Most popular', 'decent-core' ),
					'new'        => __( 'Newest first', 'decent-core' ),
					'price-asc'  => __( 'Price: low to high', 'decent-core' ),
					'price-desc' => __( 'Price: high to low', 'decent-core' ),
					'rating'     => __( 'Highest rated', 'decent-core' ),
					'title'      => __( 'Name: A to Z', 'decent-core' ),
				),
			)
		);

		$this->add_control(
			'count',
			array(
				'label'   => __( 'Products', 'decent-core' ),
				'type'    => Controls_Manager::NUMBER,
				'default' => $default_count,
				'min'     => 1,
				'max'     => 24,
			)
		);
	}

	/**
	 * Returns the download categories for the picker.
	 *
	 * @return array<int, string>
	 */
	private function category_options(): array {
		if ( ! taxonomy_exists( 'download_category' ) ) {
			return array();
		}

		$terms = get_terms(
			array(
				'taxonomy'   => 'download_category',
				'hide_empty' => false,
			)
		);

		if ( is_wp_error( $terms ) ) {
			return array();
		}

		$options = array();

		foreach ( $terms as $term ) {
			$options[ $term->term_id ] = $term->name;
		}

		return $options;
	}

	/**
	 * Builds and runs the product query.
	 *
	 * @return WP_Query
	 */
	protected function product_query(): WP_Query {
		$settings = $this->get_settings_for_display();

		$args = array(
			'post_type'      => 'download',
			'post_status'    => 'publish',
			'posts_per_page' => max( 1, min( 24, (int) ( $settings['count'] ?? 3 ) ) ),
			'no_found_rows'  => true,
		);

		$categories = array_map( 'absint', (array) ( $settings['category'] ?? array() ) );
		$categories = array_filter( $categories );

		if ( ! empty( $categories ) ) {
			$args['tax_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- Indexed term IDs from a picker.
				array(
					'taxonomy' => 'download_category',
					'field'    => 'term_id',
					'terms'    => $categories,
				),
			);
		}

		$orderby = (string) ( $settings['orderby'] ?? 'popular' );

		// The theme owns the sort definitions; reusing them means a widget and
		// the catalogue cannot disagree about what a sort key means.
		if ( class_exists( '\DecentThemes\Integrations\EDD\Query' ) ) {
			$modifiers = \DecentThemes\Integrations\EDD\Query::modifiers(
				array(
					'sort'   => $orderby,
					'band'   => '',
					'rating' => 0.0,
					'search' => '',
				)
			);

			unset( $modifiers['posts_per_page'] );
			$args = array_merge( $args, $modifiers );
		}

		return new WP_Query( $args );
	}
}
