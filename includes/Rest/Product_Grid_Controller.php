<?php
/**
 * Product Grid filter endpoint.
 *
 * Returns *rendered card HTML*, not JSON to be templated client-side, for the
 * same reason the theme's catalogue endpoint does: the grid has exactly one
 * implementation and the AJAX response has to be the thing the page would have
 * rendered, not a second guess at it.
 *
 * The interesting part is what the request is allowed to say. It names a
 * document and a widget on it, and the settings are then read out of the
 * database — never out of the request. A caller cannot ask for a thousand
 * products, a different post type, a private category or a meta key, because
 * none of those are arguments. What is left is a category the widget already
 * put on a chip and a sort key from a fixed list, both re-checked here against
 * the widget's own allow-list before a query is built.
 *
 * @package PixelomaticCore
 */

namespace PixelomaticCore\Rest;

defined( 'ABSPATH' ) || exit;

use PixelomaticCore\Elementor\Widgets\Product_Grid;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * GET pixelomatic/v1/product-grid
 */
final class Product_Grid_Controller {

	/**
	 * REST namespace.
	 */
	private const NAMESPACE = 'pixelomatic/v1';

	/**
	 * Attaches hooks.
	 *
	 * @return void
	 */
	public function register(): void {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Registers the route.
	 *
	 * @return void
	 */
	public function register_routes(): void {
		register_rest_route(
			self::NAMESPACE,
			'/product-grid',
			array(
				'methods'             => WP_REST_Server::READABLE,
				// Public, and deliberately so: the response is exactly what an
				// anonymous visitor sees on the page. Every argument below is
				// a bounded scalar, and the widget settings that actually
				// shape the query are read from the post, not the request.
				'permission_callback' => '__return_true',
				'callback'            => array( $this, 'get_item' ),
				'args'                => array(
					'post'     => array(
						'type'              => 'integer',
						'required'          => true,
						'minimum'           => 1,
						'sanitize_callback' => 'absint',
					),
					'widget'   => array(
						'type'              => 'string',
						'required'          => true,
						// Elementor element IDs are short hex strings. Anything
						// else cannot match an element, so reject it here
						// rather than walking the document looking for it.
						'validate_callback' => static function ( $value ): bool {
							return 1 === preg_match( '/^[a-zA-Z0-9]{1,20}$/', (string) $value );
						},
					),
					'category' => array(
						'type'              => 'integer',
						'default'           => 0,
						'minimum'           => 0,
						'sanitize_callback' => 'absint',
					),
					'orderby'  => array(
						'type'    => 'string',
						'default' => 'popular',
						'enum'    => array_keys( Product_Grid::sort_options() ),
					),
					'page'     => array(
						'type'              => 'integer',
						'default'           => 1,
						'minimum'           => 1,
						'maximum'           => 100,
						'sanitize_callback' => 'absint',
					),
				),
			)
		);
	}

	/**
	 * Renders the grid for one filter state.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_item( WP_REST_Request $request ) {
		$limited = $this->rate_limit();

		if ( is_wp_error( $limited ) ) {
			return $limited;
		}

		$widget = $this->widget( (int) $request->get_param( 'post' ), (string) $request->get_param( 'widget' ) );

		if ( is_wp_error( $widget ) ) {
			return $widget;
		}

		$category = (int) $request->get_param( 'category' );

		// The chips are the allow-list. A category the widget never offered is
		// not a filter it has, whatever the request believes.
		if ( $category > 0 && ! in_array( $category, $widget->chip_category_ids(), true ) ) {
			return new WP_Error(
				'pixelomatic_core_unknown_category',
				__( 'That category is not one this grid filters by.', 'pixelomatic-core' ),
				array( 'status' => 400 )
			);
		}

		$result = $widget->grid_response(
			array(
				'category' => $category,
				'orderby'  => (string) $request->get_param( 'orderby' ),
				'page'     => (int) $request->get_param( 'page' ),
			)
		);

		return new WP_REST_Response( $result );
	}

	/**
	 * Rebuilds the widget from its saved element data.
	 *
	 * Going through Elementor's own element factory rather than instantiating
	 * Product_Grid directly is what makes get_settings_for_display() return the
	 * editor's values — defaults, responsive values and dynamic tags included.
	 * A hand-built instance would silently render every default.
	 *
	 * @param int    $post_id   Document post ID.
	 * @param string $widget_id Elementor element ID.
	 * @return Product_Grid|WP_Error
	 */
	private function widget( int $post_id, string $widget_id ) {
		$missing = new WP_Error(
			'pixelomatic_core_widget_not_found',
			__( 'That product grid is no longer on the page.', 'pixelomatic-core' ),
			array( 'status' => 404 )
		);

		if ( ! class_exists( '\Elementor\Plugin' ) || ! class_exists( '\Pixelomatic\Frontend\Card' ) ) {
			return $missing;
		}

		// Published only. An endpoint that renders out of a draft would be a
		// way to read a page before it is public, however little it leaks.
		if ( 'publish' !== get_post_status( $post_id ) || post_password_required( $post_id ) ) {
			return $missing;
		}

		$document = \Elementor\Plugin::instance()->documents->get( $post_id );

		if ( ! $document || ! $document->is_built_with_elementor() ) {
			return $missing;
		}

		$element = \Elementor\Utils::find_element_recursive( $document->get_elements_data(), $widget_id );

		if ( empty( $element ) || 'pixelomatic-product-grid' !== ( $element['widgetType'] ?? '' ) ) {
			return $missing;
		}

		// Dynamic tags and any other setting resolved against "the current
		// document" need that document to be the current one.
		\Elementor\Plugin::instance()->documents->switch_to_document( $document );

		$widget = \Elementor\Plugin::instance()->elements_manager->create_element_instance( $element );

		\Elementor\Plugin::instance()->documents->restore_document();

		if ( ! $widget instanceof Product_Grid ) {
			return $missing;
		}

		return $widget;
	}

	/**
	 * Throttles anonymous callers.
	 *
	 * This endpoint renders card HTML, so unlike most public reads it costs
	 * real work per request. The limit is deliberately generous — a visitor
	 * tapping through five chips fires five requests in as many seconds, and a
	 * limit that catches them is worse than no limit.
	 *
	 * Keyed on a hash of the address, never the address itself: storing raw
	 * IPs in the options table would be personal data collected for no reason.
	 *
	 * @return true|WP_Error
	 */
	private function rate_limit() {
		if ( is_user_logged_in() ) {
			return true;
		}

		/**
		 * Filters the per-minute request ceiling for the product grid endpoint.
		 *
		 * Return 0 to disable throttling, which is the right choice behind a
		 * CDN or a WAF that already does it.
		 *
		 * @since 1.0.0
		 *
		 * @param int $limit Requests per minute.
		 */
		$limit = (int) apply_filters( 'pixelomatic_core/rest/rate_limit', 60 );

		if ( $limit < 1 ) {
			return true;
		}

		$address = isset( $_SERVER['REMOTE_ADDR'] )
			? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) )
			: '';

		if ( '' === $address ) {
			return true;
		}

		$key   = 'pixelomatic_core_rl_' . substr( hash( 'sha256', $address . wp_salt() ), 0, 20 );
		$count = (int) get_transient( $key );

		if ( $count >= $limit ) {
			return new WP_Error(
				'pixelomatic_core_rate_limited',
				__( 'Too many requests. Try again in a moment.', 'pixelomatic-core' ),
				array( 'status' => 429 )
			);
		}

		// A fresh window each minute. Not a sliding window: the extra
		// precision is not worth the extra storage for a throttle this loose.
		set_transient( $key, $count + 1, MINUTE_IN_SECONDS );

		return true;
	}
}
