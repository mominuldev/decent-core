<?php
/**
 * Product version dynamic tag.
 *
 * @package DecentCore
 */

namespace DecentCore\Elementor\Dynamic_Tags;

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
		return 'decent-download-version';
	}

	/**
	 * Picker label.
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'Product version', 'decent-core' );
	}

	/**
	 * Returns the value.
	 *
	 * @param int $download_id Download ID.
	 * @return string
	 */
	protected function value_for( int $download_id ): string {
		if ( ! class_exists( '\\DecentThemes\\Integrations\\EDD\\EDD' ) ) {
			return '';
		}

		return \DecentThemes\Integrations\EDD\EDD::versions()->version( $download_id );
	}
}
