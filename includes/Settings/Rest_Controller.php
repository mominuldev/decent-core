<?php
/**
 * Settings REST endpoint.
 *
 * @package PixelomaticCore
 */

namespace PixelomaticCore\Settings;

defined( 'ABSPATH' ) || exit;

use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * GET and POST pixelomatic/v1/settings
 */
final class Rest_Controller {

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
			'/settings',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_settings' ),
					'permission_callback' => array( $this, 'can_manage' ),
				),
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'save_settings' ),
					'permission_callback' => array( $this, 'can_manage' ),
					// Derived from config/settings.php, so a field cannot be
					// exposed here without also declaring its type and bounds.
					'args'                => Schema::rest_args(),
				),
			)
		);
	}

	/**
	 * Capability check.
	 *
	 * @return bool
	 */
	public function can_manage(): bool {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Returns the current settings.
	 *
	 * @return WP_REST_Response
	 */
	public function get_settings(): WP_REST_Response {
		return new WP_REST_Response( Settings::all() );
	}

	/**
	 * Saves settings.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response
	 */
	public function save_settings( WP_REST_Request $request ): WP_REST_Response {
		// Only keys the schema knows are read. Settings::save() rejects the
		// rest rather than storing something undescribed.
		$input = array_intersect_key( (array) $request->get_json_params(), Schema::fields() );

		return new WP_REST_Response(
			array(
				'saved'    => true,
				'settings' => Settings::save( $input ),
			)
		);
	}
}
