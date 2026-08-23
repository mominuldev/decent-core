<?php
/**
 * Widget registry.
 *
 * @package PixelomaticCore
 */

namespace PixelomaticCore\Elementor;

defined( 'ABSPATH' ) || exit;

use PixelomaticCore\Settings\Settings;

/**
 * Reads config/widgets.php and decides what Elementor sees.
 */
final class Widget_Registry {

	/**
	 * The widget map.
	 *
	 * @var array<string, array<string, mixed>>|null
	 */
	private static $map = null;

	/**
	 * Settings key prefix for the per-widget toggles.
	 */
	private const TOGGLE_PREFIX = 'widget_';

	/**
	 * Returns the full widget map.
	 *
	 * @return array<string, array<string, mixed>>
	 */
	public static function map(): array {
		if ( null === self::$map ) {
			/**
			 * Filters the widget map.
			 *
			 * A third-party widget added here inherits the toggle, the asset
			 * pipeline and the settings UI without any further work.
			 *
			 * @since 1.0.0
			 *
			 * @param array<string, array<string, mixed>> $map Widget definitions.
			 */
			self::$map = apply_filters(
				'pixelomatic_core/widgets/map',
				require PIXELOMATIC_CORE_DIR . 'config/widgets.php'
			);
		}

		return self::$map;
	}

	/**
	 * Returns the widgets that should be registered on this request.
	 *
	 * A widget is skipped when it is switched off, or when a dependency it
	 * declares is missing — a product widget on a site without EDD would only
	 * be able to render an error.
	 *
	 * @return array<string, array<string, mixed>>
	 */
	public static function active(): array {
		return array_filter(
			self::map(),
			static function ( array $widget, string $slug ): bool {
				if ( ! self::is_enabled( $slug, $widget ) ) {
					return false;
				}

				foreach ( (array) ( $widget['requires'] ?? array() ) as $dependency ) {
					if ( ! self::dependency_met( (string) $dependency ) ) {
						return false;
					}
				}

				return true;
			},
			ARRAY_FILTER_USE_BOTH
		);
	}

	/**
	 * Whether a widget is switched on.
	 *
	 * @param string               $slug   Widget slug.
	 * @param array<string, mixed> $widget Widget definition.
	 * @return bool
	 */
	public static function is_enabled( string $slug, array $widget = array() ): bool {
		if ( empty( $widget ) ) {
			$widget = self::map()[ $slug ] ?? array();
		}

		$default = (bool) ( $widget['default'] ?? true );
		$stored  = Settings::get( self::TOGGLE_PREFIX . str_replace( '-', '_', $slug ), null );

		return null === $stored ? $default : (bool) $stored;
	}

	/**
	 * Whether a declared dependency is available.
	 *
	 * @param string $dependency Dependency key.
	 * @return bool
	 */
	private static function dependency_met( string $dependency ): bool {
		switch ( $dependency ) {
			case 'edd':
				return defined( 'EDD_VERSION' );

			case 'theme':
				return function_exists( 'pixelomatic_icon' );

			default:
				return true;
		}
	}

	/**
	 * Returns the settings key for a widget's toggle.
	 *
	 * @param string $slug Widget slug.
	 * @return string
	 */
	public static function toggle_key( string $slug ): string {
		return self::TOGGLE_PREFIX . str_replace( '-', '_', $slug );
	}

	/**
	 * Adds one boolean field per widget to the settings schema.
	 *
	 * The toggles could have been stored on the side, but then they would be
	 * the one thing writable without passing the schema's validation — and the
	 * REST endpoint would need a special case to accept them. Declaring them
	 * as ordinary fields means they are sanitised, bounded and exported by
	 * exactly the same code as everything else.
	 *
	 * @param array<string, array<string, mixed>> $fields Existing schema.
	 * @return array<string, array<string, mixed>>
	 */
	public static function extend_schema( array $fields ): array {
		foreach ( self::map() as $slug => $widget ) {
			$fields[ self::toggle_key( $slug ) ] = array(
				'tab'      => 'widgets',
				'type'     => 'boolean',
				'default'  => (bool) ( $widget['default'] ?? true ),
				'label'    => (string) ( $widget['title'] ?? $slug ),
				'help'     => '',
				'sanitize' => 'rest_sanitize_boolean',
			);
		}

		return $fields;
	}
}
