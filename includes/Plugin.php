<?php
/**
 * Plugin bootstrap.
 *
 * @package PixelomaticCore
 */

namespace PixelomaticCore;

defined( 'ABSPATH' ) || exit;

use PixelomaticCore\Admin\Admin_Page;
use PixelomaticCore\Assets\Bundler;
use PixelomaticCore\Builder\Builder;
use PixelomaticCore\Builder\Pro_Bridge;
use PixelomaticCore\Assets\Garbage_Collector;
use PixelomaticCore\Assets\Usage_Index;
use PixelomaticCore\Elementor\Manager;
use PixelomaticCore\Rest\Product_Grid_Controller;
use PixelomaticCore\Rest\Tools_Controller;
use PixelomaticCore\Settings\Rest_Controller;
use PixelomaticCore\Settings\Settings;

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
		load_plugin_textdomain( 'pixelomatic-core', false, dirname( plugin_basename( PIXELOMATIC_CORE_FILE ) ) . '/languages' );

		Cli\Commands::register();

		// Widget toggles are ordinary schema fields; see extend_schema().
		// Registered before anything reads the schema.
		add_filter(
			'pixelomatic_core/settings/schema',
			array( Elementor\Widget_Registry::class, 'extend_schema' )
		);

		$requirements = new Requirements();

		// The settings screen boots regardless. A site owner whose Elementor
		// is missing still needs somewhere to read why the plugin is idle.
		$this->container->get( Admin_Page::class )->register();
		$this->container->get( Rest_Controller::class )->register();
		$this->container->get( Tools_Controller::class )->register();

		if ( ! $requirements->met() ) {
			$requirements->notice();
			return;
		}

		$this->container->get( Manager::class )->register();

		// The Product Grid's filter bar. Registered with the widgets rather
		// than with the settings endpoints above, because without Elementor
		// there is no document to read a widget's settings out of.
		$this->container->get( Product_Grid_Controller::class )->register();

		// Asset pipeline. Usage_Index records what a document uses at save
		// time; Bundler collapses that set on the front end and steps aside
		// when it cannot.
		$this->container->get( Usage_Index::class )->register();
		$this->container->get( Bundler::class )->register();
		$this->container->get( Garbage_Collector::class )->register();

		// Modules are switchable, and a module that is off costs nothing: its
		// classes are never loaded and its hooks never attached.
		if ( Settings::enabled( Builder::key() ) ) {
			$this->container->get( Builder::class )->register();
			$this->container->get( Pro_Bridge::class )->register();
		}

		/**
		 * Fires once the plugin has booted.
		 *
		 * @since 1.0.0
		 *
		 * @param Plugin $plugin Plugin instance.
		 */
		do_action( 'pixelomatic_core/booted', $this );
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
