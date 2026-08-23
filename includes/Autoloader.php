<?php
/**
 * PSR-4 autoloader.
 *
 * Composer is a development dependency only; its vendor directory is never
 * shipped, so a site never needs `composer install` to run the plugin.
 *
 * @package PixelomaticCore
 */

namespace PixelomaticCore;

defined( 'ABSPATH' ) || exit;

/**
 * Maps PixelomaticCore\Foo\Bar to includes/Foo/Bar.php.
 */
final class Autoloader {

	/**
	 * Namespace prefix this autoloader answers for.
	 */
	private const PREFIX = 'PixelomaticCore\\';

	/**
	 * Registers the autoloader with SPL.
	 *
	 * @return void
	 */
	public static function register(): void {
		spl_autoload_register( array( __CLASS__, 'load' ) );
	}

	/**
	 * Resolves a class name to a file and requires it.
	 *
	 * @param string $class_name Fully qualified class name.
	 * @return void
	 */
	public static function load( string $class_name ): void {
		if ( 0 !== strpos( $class_name, self::PREFIX ) ) {
			return;
		}

		$relative = substr( $class_name, strlen( self::PREFIX ) );
		$path     = PIXELOMATIC_CORE_DIR . 'includes/' . str_replace( '\\', '/', $relative ) . '.php';

		// realpath() collapses any traversal before the comparison, so a class
		// name containing .. cannot escape includes/.
		$real = realpath( $path );
		$root = realpath( PIXELOMATIC_CORE_DIR . 'includes' );

		if ( false === $real || false === $root || 0 !== strpos( $real, $root ) ) {
			return;
		}

		require_once $real;
	}
}
