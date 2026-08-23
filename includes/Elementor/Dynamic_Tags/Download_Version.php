<?php
/**
 * Product version dynamic tag.
 *
 * @package PixelomaticCore
 */

namespace PixelomaticCore\Elementor\Dynamic_Tags;

defined( 'ABSPATH' ) || exit;

/**
 * The current version, from Software Licensing or theme meta.
 */
final class Download_Version extends Base_Tag {

	/**
	 * Tag name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'pixelomatic-download-version';
	}

	/**
	 * Picker label.
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'Product version', 'pixelomatic-core' );
	}

	/**
	 * Returns the value.
	 *
	 * @param int $download_id Download ID.
	 * @return string
	 */
	protected function value_for( int $download_id ): string {
		if ( ! class_exists( '\\Pixelomatic\\Integrations\\EDD\\EDD' ) ) {
			return '';
		}

		return \Pixelomatic\Integrations\EDD\EDD::versions()->version( $download_id );
	}
}
