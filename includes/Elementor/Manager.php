<?php
/**
 * Elementor integration manager.
 *
 * @package DecentCore
 */

namespace DecentCore\Elementor;

defined( 'ABSPATH' ) || exit;

use DecentCore\Contracts\Module;
use DecentCore\Elementor\Compat\Breakpoints;
use DecentCore\Elementor\Compat\Kit_Seeder;
use Elementor\Elements_Manager;
use Elementor\Widgets_Manager;

/**
 * Registers categories, widgets and asset handles with Elementor.
 */
final class Manager implements Module {

	/**
	 * Settings key. Always on: without Elementor there is no plugin.
	 *
	 * @return string
	 */
	public static function key(): string {
		return '';
	}

	/**
	 * Attaches hooks.
	 *
	 * @return void
	 */
	public function register(): void {
		add_action( 'elementor/elements/categories_registered', array( $this, 'register_categories' ) );
		add_action( 'elementor/widgets/register', array( $this, 'register_widgets' ) );
		add_action( 'elementor/frontend/after_register_styles', array( $this, 'register_assets' ) );
		add_action( 'elementor/dynamic_tags/register', array( $this, 'register_tags' ) );
		add_action( 'elementor/editor/after_enqueue_styles', array( $this, 'enqueue_editor_assets' ) );

		( new Breakpoints() )->register();
		( new Kit_Seeder() )->register();
	}

	/**
	 * The panel categories, as slug => label, in display order.
	 *
	 * The settings screen groups its widget list by these same four, so the
	 * place a widget lives on the settings screen is the place it lives in the
	 * editor panel. Two lists that disagree about where a widget sits are worse
	 * than either one on its own.
	 *
	 * Labels are unprefixed. The "Decent — " prefix belongs to the editor
	 * panel, where the category sits among every other plugin's; on our own
	 * settings screen it is noise on all four rows.
	 *
	 * @return array<string, string>
	 */
	public static function categories(): array {
		return array(
			'decent-layout'   => __( 'Layout', 'decent-core' ),
			'decent-products' => __( 'Products', 'decent-core' ),
			'decent-header'   => __( 'Header & Footer', 'decent-core' ),
			'decent-content'  => __( 'Content', 'decent-core' ),
		);
	}

	/**
	 * Registers the panel categories.
	 *
	 * @param Elements_Manager $elements Elements manager.
	 * @return void
	 */
	public function register_categories( Elements_Manager $elements ): void {
		foreach ( self::categories() as $slug => $title ) {
			$elements->add_category(
				$slug,
				array(
					/* translators: %s: category name, for example "Layout". */
					'title' => sprintf( __( 'Decent — %s', 'decent-core' ), $title ),
					'icon'  => 'eicon-elementor-square',
				)
			);
		}
	}

	/**
	 * Registers every active widget.
	 *
	 * @param Widgets_Manager $widgets Widgets manager.
	 * @return void
	 */
	public function register_widgets( Widgets_Manager $widgets ): void {
		foreach ( Widget_Registry::active() as $slug => $widget ) {
			$class_name = $widget['class'] ?? '';

			if ( ! is_string( $class_name ) || ! class_exists( $class_name ) ) {
				continue;
			}

			$widgets->register( new $class_name() );
		}
	}

	/**
	 * Registers the product dynamic tags.
	 *
	 * Gated on EDD, because every one of them reads a download. Registering
	 * them without it would put eight tags in the picker that can only ever
	 * resolve to an empty string.
	 *
	 * @param object $tags Elementor's dynamic tags manager.
	 * @return void
	 */
	public function register_tags( $tags ): void {
		if ( ! defined( 'EDD_VERSION' ) || ! method_exists( $tags, 'register' ) ) {
			return;
		}

		if ( method_exists( $tags, 'register_group' ) ) {
			$tags->register_group(
				'decent-product',
				array( 'title' => __( 'Decent — Product', 'decent-core' ) )
			);
		}

		$classes = array(
			Dynamic_Tags\Download_Price::class,
			Dynamic_Tags\Download_Rating::class,
			Dynamic_Tags\Download_Reviews::class,
			Dynamic_Tags\Download_Sales::class,
			Dynamic_Tags\Download_Version::class,
			Dynamic_Tags\Download_Updated::class,
			Dynamic_Tags\Download_Badge::class,
			Dynamic_Tags\Download_Demo_Url::class,
		);

		foreach ( $classes as $class_name ) {
			$tags->register( new $class_name() );
		}
	}

	/**
	 * Registers a style and script handle per widget.
	 *
	 * Registered, not enqueued: Elementor enqueues them through
	 * get_style_depends() only where the widget actually renders. This is the
	 * guaranteed floor beneath the uploads bundler.
	 *
	 * @return void
	 */
	public function register_assets(): void {
		foreach ( Widget_Registry::map() as $slug => $widget ) {
			foreach ( (array) ( $widget['styles'] ?? array() ) as $handle ) {
				$path = 'assets/widgets/' . $handle . '/style.css';

				if ( ! file_exists( DECENT_CORE_DIR . $path ) ) {
					continue;
				}

				wp_register_style(
					'decent-core-' . $handle,
					DECENT_CORE_URL . $path,
					array(),
					DECENT_CORE_VERSION
				);
			}

			foreach ( (array) ( $widget['scripts'] ?? array() ) as $handle ) {
				$path = 'assets/widgets/' . $handle . '/script.js';

				if ( ! file_exists( DECENT_CORE_DIR . $path ) ) {
					continue;
				}

				wp_register_script(
					'decent-core-' . $handle,
					DECENT_CORE_URL . $path,
					array(),
					DECENT_CORE_VERSION,
					true
				);
			}
		}
	}

	/**
	 * Loads every widget asset in the editor.
	 *
	 * The editor has to style a widget the moment it is dropped, before any
	 * render pass has told us it is on the page. Conditional loading is a
	 * front-end optimisation and would only produce unstyled previews here.
	 *
	 * @return void
	 */
	public function enqueue_editor_assets(): void {
		foreach ( Widget_Registry::map() as $widget ) {
			foreach ( (array) ( $widget['styles'] ?? array() ) as $handle ) {
				wp_enqueue_style( 'decent-core-' . $handle );
			}
		}
	}
}
