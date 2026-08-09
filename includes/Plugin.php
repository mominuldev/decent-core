<?php
/**
 * Plugin bootstrap.
 *
 * @package DecentCore
 */

namespace DecentCore;

defined( 'ABSPATH' ) || exit;

use DecentCore\Admin\Admin_Page;
use DecentCore\Elementor\Manager;
use DecentCore\Settings\Rest_Controller;

/**
 * Boots the plugin.
 */
final class Plugin {

	/**
	 * Singleton instance.
	 *
	 * @var Plugin|null
	 */
	private static $instance = null;

	/**
	 * Service container.
	 *
	 * @var Container
	 */
	private $container;

	/**
	 * Private constructor: use instance().
	 */
	private function __construct() {
		$this->container = new Container();
	}

	/**
	 * Returns the singleton.
	 *
	 * @return Plugin
	 */
	public static function instance(): Plugin {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Boots everything the environment allows.
	 *
	 * @return void
	 */
	public function run(): void {
		load_plugin_textdomain( 'decent-core', false, dirname( plugin_basename( DECENT_CORE_FILE ) ) . '/languages' );

		Cli\Commands::register();

		$requirements = new Requirements();

		// The settings screen boots regardless. A site owner whose Elementor
		// is missing still needs somewhere to read why the plugin is idle.
		$this->container->get( Admin_Page::class )->register();
		$this->container->get( Rest_Controller::class )->register();

		if ( ! $requirements->met() ) {
			$requirements->notice();
			return;
		}

		$this->container->get( Manager::class )->register();

		/**
		 * Fires once the plugin has booted.
		 *
		 * @since 1.0.0
		 *
		 * @param Plugin $plugin Plugin instance.
		 */
		do_action( 'decent_core/booted', $this );
	}

	/**
	 * Returns the container.
	 *
	 * @return Container
	 */
	public function container(): Container {
		return $this->container;
	}

	/**
	 * Prevents cloning.
	 *
	 * @return void
	 */
	private function __clone() {}
}
