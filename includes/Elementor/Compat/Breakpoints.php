<?php
/**
 * Breakpoint sync.
 *
 * Elementor's defaults are not the theme's. If they disagree, an editor's
 * "tablet" view is a different width from the one the stylesheet responds to,
 * so widget overrides and theme CSS fight each other at widths neither was
 * designed for. That class of bug is nearly impossible to diagnose after the
 * fact, which is why this runs on activation rather than being documented as
 * a manual step.
 *
 * @package DecentCore
 */

namespace DecentCore\Elementor\Compat;

defined( 'ABSPATH' ) || exit;

use DecentCore\Settings\Settings;

/**
 * Aligns Elementor's active breakpoints with the theme's.
 */
final class Breakpoints {

	/**
	 * Attaches hooks.
	 *
	 * @return void
	 */
	public function register(): void {
		add_action( 'admin_init', array( $this, 'maybe_sync' ) );
	}

	/**
	 * Syncs the breakpoints once, unless the setting is off.
	 *
	 * @return void
	 */
	public function maybe_sync(): void {
		if ( ! Settings::enabled( 'sync_breakpoints' ) ) {
			return;
		}

		if ( 'done' === get_option( 'decent_core_breakpoints_synced' ) ) {
			return;
		}

		$this->sync();

		update_option( 'decent_core_breakpoints_synced', 'done', false );
	}

	/**
	 * Writes the breakpoint values into Elementor's active kit.
	 *
	 * @return bool True when the values were written.
	 */
	public function sync(): bool {
		if ( ! class_exists( '\Elementor\Plugin' ) ) {
			return false;
		}

		$tokens = require DECENT_CORE_DIR . 'config/tokens.php';
		$kit_id = (int) get_option( 'elementor_active_kit' );

		if ( ! $kit_id ) {
			return false;
		}

		$settings = get_post_meta( $kit_id, '_elementor_page_settings', true );
		$settings = is_array( $settings ) ? $settings : array();

		$active = array();

		foreach ( $tokens['breakpoints'] as $name => $value ) {
			$settings[ 'viewport_' . $name ] = (int) $value;
			$active[]                        = $name;
		}

		$settings['active_breakpoints'] = array_map(
			static function ( string $name ): string {
				return 'viewport_' . $name;
			},
			$active
		);

		update_post_meta( $kit_id, '_elementor_page_settings', $settings );

		// Elementor caches compiled CSS per kit; stale files would still carry
		// the old breakpoints. The class_exists() at the top of this method is
		// the only guard needed — a method_exists() on Plugin::instance() as
		// well is unreachable belt-and-braces.
		$plugin = \Elementor\Plugin::instance();

		if ( isset( $plugin->files_manager ) ) {
			$plugin->files_manager->clear_cache();
		}

		return true;
	}
}
