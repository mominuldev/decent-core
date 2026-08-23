<?php
/**
 * Download query controls.
 *
 * @package PixelomaticCore
 */

namespace PixelomaticCore\Elementor\Base\Traits;

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
 *
 * Every control past the first three is additive and defaults to a no-op, so
 * a widget that only ever called register_query_controls( 3 ) keeps the query
 * it always had.
 */
trait Has_Query_Controls {

	/**
	 * The sort keys a widget may offer.
	 *
	 * Public and static because it is the allow-list two places need: the
	 * control that renders it, and the REST endpoint that has to reject
	 * anything else before it reaches a query.
	 *
	 * @return array<string, string>
	 */
	public static function sort_options(): array {
		return array(
			'popular'    => __( 'Most popular', 'pixelomatic-core' ),
			'new'        => __( 'Newest first', 'pixelomatic-core' ),
			'price-asc'  => __( 'Price: low to high', 'pixelomatic-core' ),
			'price-desc' => __( 'Price: high to low', 'pixelomatic-core' ),
			'rating'     => __( 'Highest rated', 'pixelomatic-core' ),
			'title'      => __( 'Name: A to Z', 'pixelomatic-core' ),
		);
	}

	/**
	 * Registers the query controls.
	 *
	 * @param int  $default_count Default number of products.
	 * @param bool $advanced      Whether to register the second group — tags,
	 *                            offset and the current-product exclusion.
	 *                            These are off by default for widgets that
	 *                            show a fixed handful and would only be made
	 *                            confusing by an offset control.
	 * @return void
	 */
	protected function register_query_controls( int $default_count = 3, bool $advanced = true ): void {
		$this->add_control(
			'category',
			array(
				'label'       => __( 'Category', 'pixelomatic-core' ),
				'type'        => Controls_Manager::SELECT2,
				'multiple'    => true,
				'label_block' => true,
				'options'     => $this->term_options( 'download_category' ),
			)
		);

		$this->add_control(
			'orderby',
			array(
				'label'   => __( 'Order by', 'pixelomatic-core' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'popular',
				'options' => self::sort_options(),
			)
		);

		$this->add_control(
			'count',
			array(
				'label'   => __( 'Products', 'pixelomatic-core' ),
				'type'    => Controls_Manager::NUMBER,
				'default' => $default_count,
				'min'     => 1,
				'max'     => 24,
			)
		);

		if ( ! $advanced ) {
			return;
		}

		$this->add_control(
			'tag',
			array(
				'label'       => __( 'Tag', 'pixelomatic-core' ),
				'type'        => Controls_Manager::SELECT2,
				'multiple'    => true,
				'label_block' => true,
				'options'     => $this->term_options( 'download_tag' ),
				'separator'   => 'before',
			)
		);

		// A term picked in both boxes has to mean "in this category AND
		// carrying this tag" or the control pair is a lie. The relation is
		// therefore fixed at AND rather than exposed.
		$this->add_control(
			'offset',
			array(
				'label'       => __( 'Skip', 'pixelomatic-core' ),
				'type'        => Controls_Manager::NUMBER,
				'default'     => 0,
				'min'         => 0,
				'max'         => 100,
				'description' => __( 'Products to skip before the first one shown. Lets a second grid continue where the first stopped.', 'pixelomatic-core' ),
			)
		);

		$this->add_control(
			'exclude_current',
			array(
				'label'       => __( 'Hide the current product', 'pixelomatic-core' ),
				'type'        => Controls_Manager::SWITCHER,
				'default'     => '',
				'description' => __( 'Only does anything on a single product template.', 'pixelomatic-core' ),
			)
		);
	}

	/**
	 * Returns the terms of a taxonomy for a picker.
	 *
	 * @param string $taxonomy Taxonomy slug.
	 * @return array<int, string>
	 */
	private function term_options( string $taxonomy ): array {
		if ( ! taxonomy_exists( $taxonomy ) ) {
			return array();
		}

		$terms = get_terms(
			array(
				'taxonomy'   => $taxonomy,
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
	 * @param array<string, mixed> $overrides {
	 *     Optional. Runtime state that wins over the saved settings. This is
	 *     how the AJAX filter re-runs the widget's own query without the
	 *     client ever supplying a query argument: it names a category and a
	 *     sort key, both of which are checked against the widget's own
	 *     allow-list before they arrive here.
	 *
	 *     @type int    $category      Single term ID, replacing the saved set.
	 *     @type string $orderby       Sort key from self::sort_options().
	 *     @type int    $paged         Page number.
	 *     @type bool   $count_results Whether found_posts is needed.
	 * }
	 * @return WP_Query
	 */
	protected function product_query( array $overrides = array() ): WP_Query {
		$settings = $this->get_settings_for_display();
		$counting = ! empty( $overrides['count_results'] );

		$args = array(
			'post_type'      => 'download',
			'post_status'    => 'publish',
			'posts_per_page' => max( 1, min( 24, (int) ( $settings['count'] ?? 3 ) ) ),
			// found_posts costs a second query, so it is only asked for by the
			// callers that print a total or page through the results.
			'no_found_rows'  => ! $counting,
		);

		$offset = max( 0, min( 100, (int) ( $settings['offset'] ?? 0 ) ) );
		$paged  = max( 1, (int) ( $overrides['paged'] ?? 1 ) );

		if ( $paged > 1 ) {
			// offset and paged are mutually exclusive in WP_Query — passing
			// both silently drops the paging. Folding the offset into the
			// arithmetic keeps a skipped head working across every page.
			$args['offset'] = $offset + ( ( $paged - 1 ) * $args['posts_per_page'] );
		} elseif ( $offset > 0 ) {
			$args['offset'] = $offset;
		}

		$tax_query = array();

		$categories = isset( $overrides['category'] )
			? array_filter( array( absint( $overrides['category'] ) ) )
			: array_filter( array_map( 'absint', (array) ( $settings['category'] ?? array() ) ) );

		if ( ! empty( $categories ) ) {
			$tax_query[] = array(
				'taxonomy' => 'download_category',
				'field'    => 'term_id',
				'terms'    => $categories,
			);
		}

		$tags = array_filter( array_map( 'absint', (array) ( $settings['tag'] ?? array() ) ) );

		if ( ! empty( $tags ) ) {
			$tax_query[] = array(
				'taxonomy' => 'download_tag',
				'field'    => 'term_id',
				'terms'    => $tags,
			);
		}

		if ( ! empty( $tax_query ) ) {
			$tax_query['relation'] = 'AND';

			$args['tax_query'] = $tax_query; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- Indexed term IDs from a picker.
		}

		if ( 'yes' === ( $settings['exclude_current'] ?? '' ) ) {
			$current = (int) get_queried_object_id();

			if ( $current > 0 && 'download' === get_post_type( $current ) ) {
				$args['post__not_in'] = array( $current );
			}
		}

		$orderby = (string) ( $overrides['orderby'] ?? $settings['orderby'] ?? 'popular' );
		$orderby = isset( self::sort_options()[ $orderby ] ) ? $orderby : 'popular';

		// The theme owns the sort definitions; reusing them means a widget and
		// the catalogue cannot disagree about what a sort key means.
		if ( class_exists( '\Pixelomatic\Integrations\EDD\Query' ) ) {
			$modifiers = \Pixelomatic\Integrations\EDD\Query::modifiers(
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
