<?php
/**
 * WP-CLI commands.
 *
 * @package PixelomaticCore
 */

namespace PixelomaticCore\Cli;

defined( 'ABSPATH' ) || exit;

use PixelomaticCore\Elementor\Compat\Breakpoints;
use PixelomaticCore\Elementor\Compat\Kit_Seeder;
use WP_CLI;

/**
 * `wp pixelomatic-core <command>`
 */
final class Commands {

	/**
	 * Registers the commands with WP-CLI.
	 *
	 * @return void
	 */
	public static function register(): void {
		if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
			return;
		}

		WP_CLI::add_command( 'pixelomatic-core tokens', array( __CLASS__, 'tokens' ) );
		WP_CLI::add_command( 'pixelomatic-core kit', array( __CLASS__, 'kit' ) );
	}

	/**
	 * Verifies the token mirror against the theme's stylesheet.
	 *
	 * The mirror in config/tokens.php duplicates values from the theme's base.css.
	 * That duplication is deliberate — Elementor needs them as PHP — but it is
	 * also the kind of thing that rots silently. This turns the drift into a
	 * build failure.
	 *
	 * ## EXAMPLES
	 *
	 *     wp pixelomatic-core tokens verify
	 *
	 * @param string[] $args Positional arguments.
	 * @return void
	 */
	public static function tokens( array $args ): void {
		if ( 'verify' !== ( $args[0] ?? '' ) ) {
			WP_CLI::error( 'Usage: wp pixelomatic-core tokens verify' );
		}

		$css = get_template_directory() . '/assets/css/base.css';

		if ( ! is_readable( $css ) ) {
			WP_CLI::error( sprintf( 'Cannot read the theme stylesheet at %s', $css ) );
		}

		$source = (string) file_get_contents( $css ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Local theme file.
		$tokens = require PIXELOMATIC_CORE_DIR . 'config/tokens.php';

		// Map the mirror's slugs onto the custom property names in base.css.
		$map = array(
			'blue'     => '--blue',
			'blue-600' => '--blue-600',
			'ink'      => '--ink',
			'muted'    => '--muted',
			'green'    => '--green',
			'yellow'   => '--yellow',
			'red'      => '--red',
			'border'   => '--border',
			'gray-50'  => '--gray-50',
			'white'    => '--white',
		);

		$drift = array();

		foreach ( $map as $slug => $property ) {
			if ( ! preg_match( '/' . preg_quote( $property, '/' ) . ':\s*(#[0-9A-Fa-f]{3,8})\s*;/', $source, $m ) ) {
				$drift[] = sprintf( '%s: %s is not defined in base.css', $slug, $property );
				continue;
			}

			$in_css = strtoupper( $m[1] );
			$in_php = strtoupper( (string) ( $tokens['colors'][ $slug ][1] ?? '' ) );

			if ( $in_css !== $in_php ) {
				$drift[] = sprintf( '%s: base.css has %s, tokens.php has %s', $slug, $in_css, $in_php );
			}
		}

		if ( ! empty( $drift ) ) {
			foreach ( $drift as $line ) {
				WP_CLI::log( '  ' . $line );
			}

			WP_CLI::error( sprintf( '%d token(s) have drifted.', count( $drift ) ) );
		}

		WP_CLI::success( sprintf( '%d tokens match base.css.', count( $map ) ) );
	}

	/**
	 * Re-seeds Elementor's globals and breakpoints from the design system.
	 *
	 * ## EXAMPLES
	 *
	 *     wp pixelomatic-core kit seed
	 *
	 * @param string[] $args Positional arguments.
	 * @return void
	 */
	public static function kit( array $args ): void {
		if ( 'seed' !== ( $args[0] ?? '' ) ) {
			WP_CLI::error( 'Usage: wp pixelomatic-core kit seed' );
		}

		$seeded = ( new Kit_Seeder() )->seed();
		$synced = ( new Breakpoints() )->sync();

		WP_CLI::log( '  globals:     ' . ( $seeded ? 'written' : 'skipped' ) );
		WP_CLI::log( '  breakpoints: ' . ( $synced ? 'written' : 'skipped' ) );

		if ( ! $seeded && ! $synced ) {
			WP_CLI::warning( 'Nothing was written. Is there an active Elementor kit?' );
			return;
		}

		WP_CLI::success( 'Kit re-seeded from config/tokens.php.' );
	}
}
