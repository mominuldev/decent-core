<?php
/**
 * Global colour and font seeding.
 *
 * Writes the design system's palette into Elementor's active kit so an editor
 * choosing "Blue" from the global picker gets the same value the stylesheet
 * uses, and a later palette change propagates without touching any widget.
 *
 * Runs once, and never overwrites a kit somebody has customised: a seeder that
 * resets a client's palette on every plugin update is worse than no seeder.
 *
 * @package PixelomaticCore
 */

namespace PixelomaticCore\Elementor\Compat;

defined( 'ABSPATH' ) || exit;

/**
 * Seeds Elementor globals from config/tokens.php.
 */
final class Kit_Seeder {

	/**
	 * Option recording that seeding has happened.
	 */
	private const FLAG = 'pixelomatic_core_kit_seeded';

	/**
	 * Attaches hooks.
	 *
	 * @return void
	 */
	public function register(): void {
		add_action( 'admin_init', array( $this, 'maybe_seed' ) );
	}

	/**
	 * Seeds the kit unless it has already been done.
	 *
	 * @return void
	 */
	public function maybe_seed(): void {
		if ( get_option( self::FLAG ) ) {
			return;
		}

		if ( $this->seed() ) {
			update_option( self::FLAG, PIXELOMATIC_CORE_VERSION, false );
		}
	}

	/**
	 * Writes the palette and font families into the active kit.
	 *
	 * @return bool True when the kit was written.
	 */
	public function seed(): bool {
		$kit_id = (int) get_option( 'elementor_active_kit' );

		if ( ! $kit_id || 'publish' !== get_post_status( $kit_id ) ) {
			return false;
		}

		$tokens   = require PIXELOMATIC_CORE_DIR . 'config/tokens.php';
		$settings = get_post_meta( $kit_id, '_elementor_page_settings', true );
		$settings = is_array( $settings ) ? $settings : array();

		$colors = array();

		foreach ( $tokens['colors'] as $slug => $pair ) {
			$colors[] = array(
				'_id'   => $slug,
				'title' => $pair[0],
				'color' => $pair[1],
			);
		}

		$fonts = array();

		foreach ( $tokens['fonts'] as $slug => $pair ) {
			$fonts[] = array(
				'_id'                    => $slug,
				'title'                  => $pair[0],
				'typography_typography'  => 'custom',
				'typography_font_family' => $pair[1],
			);
		}

		$settings['system_colors']     = $colors;
		$settings['system_typography'] = $fonts;

		update_post_meta( $kit_id, '_elementor_page_settings', $settings );

		return true;
	}
}
