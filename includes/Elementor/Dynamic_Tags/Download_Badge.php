<?php
/**
 * Product badge dynamic tag.
 *
 * @package DecentCore
 */

namespace DecentCore\Elementor\Dynamic_Tags;

defined( 'ABSPATH' ) || exit;

/**
 * The automatic or overridden badge label.
 */
final class Download_Badge extends Base_Tag {

	/**
	 * Tag name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'decent-download-badge';
	}

	/**
	 * Picker label.
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'Product badge', 'decent-core' );
	}

	/**
	 * Returns the value.
	 *
	 * @param int $download_id Download ID.
	 * @return string
	 */
	protected function value_for( int $download_id ): string {
		if ( ! class_exists( '\\DecentThemes\\Frontend\\Card' ) ) {
			return '';
		}

		return \DecentThemes\Frontend\Card::badge( $download_id );
	}
}
