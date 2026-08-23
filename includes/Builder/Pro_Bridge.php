<?php
/**
 * Elementor Pro coexistence.
 *
 * @package PixelomaticCore
 */

namespace PixelomaticCore\Builder;

defined( 'ABSPATH' ) || exit;

/**
 * Yields to Elementor Pro's Theme Builder where it has a template assigned.
 *
 * The theme already checks elementor_theme_do_location() first, so this is
 * belt and braces for the case where Pro is installed but its location has not
 * rendered by the time our filter runs. Two headers on one page is the single
 * most common failure of a third-party header builder, and it is worth
 * defending twice.
 *
 * The question is asked of Pro's conditions manager rather than by rendering
 * the location into a discarded buffer. Resolution now happens at
 * wp_enqueue_scripts — early enough to get a header's assets into the head —
 * and a render with side effects that early is not something to do on a guess.
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

		add_filter( 'pixelomatic_core/builder/resolve', array( $this, 'defer_to_pro' ), 10, 2 );
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
		if ( ! $template_id ) {
			return 0;
		}

		$documents = self::pro_documents_for( $type );

		// Pro is there but its API is not the one we know. The theme checks
		// Pro's location before ours anyway, so the safe answer is to keep the
		// template and let that order decide.
		if ( null === $documents ) {
			return $template_id;
		}

		return empty( $documents ) ? $template_id : 0;
	}

	/**
	 * Asks Pro which documents it would render at a location.
	 *
	 * @param string $type Location type.
	 * @return array<int, mixed>|null Null when Pro cannot be asked.
	 */
	private static function pro_documents_for( string $type ) {
		/**
		 * Filters the Elementor Pro Theme Builder class the bridge asks.
		 *
		 * @since 1.0.0
		 *
		 * @param string $class_name Fully qualified class name.
		 */
		$class_name = (string) apply_filters( 'pixelomatic_core/builder/pro_module', 'ElementorPro\\Modules\\ThemeBuilder\\Module' );

		$factory = array( $class_name, 'instance' );

		if ( ! class_exists( $class_name ) || ! is_callable( $factory ) ) {
			return null;
		}

		$module = call_user_func( $factory );

		if ( ! is_object( $module ) || ! method_exists( $module, 'get_conditions_manager' ) ) {
			return null;
		}

		$conditions = $module->get_conditions_manager();

		if ( ! is_object( $conditions ) || ! method_exists( $conditions, 'get_documents_for_location' ) ) {
			return null;
		}

		return (array) $conditions->get_documents_for_location( $type );
	}
}
