<?php
/**
 * Environment requirements.
 *
 * Every unmet requirement produces an admin notice and a no-op plugin. None of
 * them produce a fatal: a site owner who updates PHP or deactivates Elementor
 * must still be able to reach wp-admin to put it back.
 *
 * @package DecentCore
 */

namespace DecentCore;

defined( 'ABSPATH' ) || exit;

/**
 * Checks the plugin's dependencies.
 */
final class Requirements {

	/**
	 * Minimum PHP version.
	 */
	public const PHP = '7.4';

	/**
	 * Minimum WordPress version.
	 */
	public const WP = '6.0';

	/**
	 * Minimum Elementor version.
	 *
	 * Elementor 4.x is the current major. Every API this plugin uses was
	 * verified present in 4.2; the floor stays at 3.18 because nothing here
	 * depends on a 4.x-only API and dropping 3.x users would be gratuitous.
	 */
	public const ELEMENTOR = '3.18';

	/**
	 * Unmet requirements, as human-readable strings.
	 *
	 * @var string[]
	 */
	private $failures = array();

	/**
	 * Runs the checks.
	 *
	 * @return bool True when everything is satisfied.
	 */
	public function met(): bool {
		$this->failures = array();

		if ( version_compare( PHP_VERSION, self::PHP, '<' ) ) {
			/* translators: 1: required PHP version, 2: current PHP version. */
			$this->failures[] = sprintf( __( 'PHP %1$s or later (running %2$s)', 'decent-core' ), self::PHP, PHP_VERSION );
		}

		if ( version_compare( get_bloginfo( 'version' ), self::WP, '<' ) ) {
			/* translators: %s: required WordPress version. */
			$this->failures[] = sprintf( __( 'WordPress %s or later', 'decent-core' ), self::WP );
		}

		if ( ! did_action( 'elementor/loaded' ) && ! defined( 'ELEMENTOR_VERSION' ) ) {
			$this->failures[] = __( 'Elementor, active', 'decent-core' );
		} elseif ( defined( 'ELEMENTOR_VERSION' ) && version_compare( ELEMENTOR_VERSION, self::ELEMENTOR, '<' ) ) {
			/* translators: %s: required Elementor version. */
			$this->failures[] = sprintf( __( 'Elementor %s or later', 'decent-core' ), self::ELEMENTOR );
		}

		return empty( $this->failures );
	}

	/**
	 * Returns the unmet requirements.
	 *
	 * @return string[]
	 */
	public function failures(): array {
		return $this->failures;
	}

	/**
	 * Shows one dismissible notice listing what is missing.
	 *
	 * @return void
	 */
	public function notice(): void {
		if ( empty( $this->failures ) || ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		$failures = $this->failures;

		add_action(
			'admin_notices',
			static function () use ( $failures ): void {
				echo '<div class="notice notice-warning"><p><strong>';
				esc_html_e( 'Decent Core is inactive.', 'decent-core' );
				echo '</strong> ';
				esc_html_e( 'It needs:', 'decent-core' );
				echo '</p><ul style="list-style:disc;margin-left:20px">';

				foreach ( $failures as $item ) {
					echo '<li>' . esc_html( $item ) . '</li>';
				}

				echo '</ul></div>';
			}
		);
	}
}
