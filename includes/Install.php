<?php
/**
 * Activation and deactivation.
 *
 * @package PixelomaticCore
 */

namespace PixelomaticCore;

defined( 'ABSPATH' ) || exit;

use PixelomaticCore\Settings\Schema;
use PixelomaticCore\Settings\Settings;

/**
 * Install-time routines.
 */
final class Install {

	/**
	 * Runs on activation.
	 *
	 * Writes defaults only for keys that are not already stored, so
	 * deactivating and reactivating never resets a configured site.
	 *
	 * @return void
	 */
	public static function activate(): void {
		$stored = get_option( Settings::OPTION, array() );
		$stored = is_array( $stored ) ? $stored : array();

		update_option( Settings::OPTION, array_merge( Schema::defaults(), $stored ), true );
		update_option( 'pixelomatic_core_version', PIXELOMATIC_CORE_VERSION, true );

		if ( ! get_option( 'pixelomatic_core_activated_at' ) ) {
			update_option( 'pixelomatic_core_activated_at', time(), false );
		}
	}

	/**
	 * Runs on deactivation.
	 *
	 * Deliberately does almost nothing. People deactivate plugins to
	 * troubleshoot, and a deactivation that discards configuration turns a
	 * five-minute diagnosis into a rebuild.
	 *
	 * @return void
	 */
	public static function deactivate(): void {
		wp_clear_scheduled_hook( 'pixelomatic_core_sweep_assets' );
	}
}
