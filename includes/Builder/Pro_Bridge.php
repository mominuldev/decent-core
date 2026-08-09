<?php
/**
 * Elementor Pro coexistence.
 *
 * @package DecentCore
 */

namespace DecentCore\Builder;

defined( 'ABSPATH' ) || exit;

/**
 * Yields to Elementor Pro's Theme Builder where it has a template assigned.
 *
 * The theme already checks elementor_theme_do_location() first, so this is
 * belt and braces for the case where Pro is installed but its location has not
 * rendered by the time our filter runs. Two headers on one page is the single
 * most common failure of a third-party header builder, and it is worth
 * defending twice.
 */
final class Pro_Bridge {

	/**
	 * Attaches hooks.
	 *
	 * @return void
	 */
	public function register(): void {
		if ( ! self::pro_active() ) {
			return;
		}

		add_filter( 'decent_core/builder/resolve', array( $this, 'defer_to_pro' ), 10, 2 );
	}

	/**
	 * Whether Elementor Pro's Theme Builder is available.
	 *
	 * @return bool
	 */
	public static function pro_active(): bool {
		return function_exists( 'elementor_theme_do_location' );
	}

	/**
	 * Returns 0 when Pro already owns this location.
	 *
	 * @param int    $template_id Resolved template.
	 * @param string $type        Location type.
	 * @return int
	 */
	public function defer_to_pro( int $template_id, string $type ): int {
		if ( ! function_exists( 'elementor_theme_do_location' ) ) {
			return $template_id;
		}

		// Ask Pro whether it has a template for this location without letting
		// it print: if it does, ours stands down.
		ob_start();
		$handled = elementor_theme_do_location( $type );
		ob_end_clean();

		return $handled ? 0 : $template_id;
	}
}
