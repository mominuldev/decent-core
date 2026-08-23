<?php
/**
 * Plugin Name:       Pixelomatic Core
 * Plugin URI:        https://pixelomatic.com/pixelomatic-core
 * Description:       Elementor widgets, header and footer builder, and Easy Digital Downloads extensions for the Pixelomatic marketplace.
 * Version:           1.0.0-alpha
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Pixelomatic
 * Author URI:        https://pixelomatic.com/
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       pixelomatic-core
 * Domain Path:       /languages
 *
 * @package PixelomaticCore
 */

defined( 'ABSPATH' ) || exit;

define( 'PIXELOMATIC_CORE_VERSION', '1.0.0-alpha' );
define( 'PIXELOMATIC_CORE_FILE', __FILE__ );
define( 'PIXELOMATIC_CORE_DIR', plugin_dir_path( __FILE__ ) );
define( 'PIXELOMATIC_CORE_URL', plugin_dir_url( __FILE__ ) );

require_once PIXELOMATIC_CORE_DIR . 'includes/Autoloader.php';

PixelomaticCore\Autoloader::register();

/*
 * Boot on plugins_loaded, not immediately: Requirements needs to see whether
 * Elementor and EDD loaded, and both are plugins that may load after this one
 * depending on activation order.
 */
add_action(
	'plugins_loaded',
	static function (): void {
		PixelomaticCore\Plugin::instance()->run();
	},
	5
);

register_activation_hook( __FILE__, array( PixelomaticCore\Install::class, 'activate' ) );
register_deactivation_hook( __FILE__, array( PixelomaticCore\Install::class, 'deactivate' ) );
