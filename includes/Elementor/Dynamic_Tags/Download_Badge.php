<?php
/**
 * Product badge dynamic tag.
 *
 * @package PixelomaticCore
 */

namespace PixelomaticCore\Elementor\Dynamic_Tags;

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
		return 'pixelomatic-download-badge';
	}

	/**
	 * Picker label.
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'Product badge', 'pixelomatic-core' );
	}

	/**
	 * Returns the value.
	 *
	 * @param int $download_id Download ID.
	 * @return string
	 */
	protected function value_for( int $download_id ): string {
		if ( ! class_exists( '\\Pixelomatic\\Frontend\\Card' ) ) {
			return '';
		}

		return \Pixelomatic\Frontend\Card::badge( $download_id );
	}
}
