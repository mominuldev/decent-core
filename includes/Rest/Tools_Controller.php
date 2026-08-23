<?php
/**
 * Maintenance endpoints.
 *
 * @package PixelomaticCore
 */

namespace PixelomaticCore\Rest;

defined( 'ABSPATH' ) || exit;

use PixelomaticCore\Assets\Filesystem;
use PixelomaticCore\Builder\Conditions\Manager as Conditions;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * POST pixelomatic/v1/tools/{action}
 *
 * Every one of these changes site state, so they are POST rather than GET —
 * a maintenance action behind a GET is one prefetch away from firing itself.
 */
final class Tools_Controller {

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
			'/tools/(?P<action>[a-z-]+)',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'run' ),
				'permission_callback' => static function (): bool {
					return current_user_can( 'manage_options' );
				},
				'args'                => array(
					'action' => array(
						'type' => 'string',
						'enum' => array( 'flush-assets', 'recompile-conditions', 'clear-cache' ),
					),
				),
			)
		);
	}

	/**
	 * Runs a maintenance action.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response
	 */
	public function run( WP_REST_Request $request ): WP_REST_Response {
		$action = (string) $request->get_param( 'action' );

		switch ( $action ) {
			case 'flush-assets':
				$removed = ( new Filesystem() )->purge();

				return new WP_REST_Response(
					array(
						'message' => sprintf(
							/* translators: %d: number of files. */
							_n( '%d bundle removed. They rebuild on the next page view.', '%d bundles removed. They rebuild on the next page view.', $removed, 'pixelomatic-core' ),
							$removed
						),
					)
				);

			case 'recompile-conditions':
				$map = ( new Conditions() )->recompile();

				return new WP_REST_Response(
					array(
						'message' => sprintf(
							/* translators: %d: number of locations. */
							_n( 'Conditions recompiled for %d location.', 'Conditions recompiled for %d locations.', count( $map ), 'pixelomatic-core' ),
							count( $map )
						),
					)
				);

			case 'clear-cache':
			default:
				// Elementor caches compiled CSS per post; a token or template
				// change leaves those files stale.
				if ( class_exists( '\Elementor\Plugin' ) ) {
					$elementor = \Elementor\Plugin::instance();

					if ( isset( $elementor->files_manager ) ) {
						$elementor->files_manager->clear_cache();
					}
				}

				wp_cache_flush();

				return new WP_REST_Response(
					array( 'message' => __( 'Caches cleared.', 'pixelomatic-core' ) )
				);
		}
	}
}
