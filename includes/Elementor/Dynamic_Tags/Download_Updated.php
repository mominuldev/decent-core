<?php
/**
 * Product last updated dynamic tag.
 *
 * @package PixelomaticCore
 */

namespace PixelomaticCore\Elementor\Dynamic_Tags;

defined( 'ABSPATH' ) || exit;

/**
 * When the product was last updated.
 */
final class Download_Updated extends Base_Tag {

	/**
	 * Tag name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'pixelomatic-download-updated';
	}

	/**
	 * Picker label.
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'Product last updated', 'pixelomatic-core' );
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

		$timestamp = \Pixelomatic\Integrations\EDD\EDD::versions()->updated_at( $download_id );

		return $timestamp > 0 ? (string) wp_date( get_option( 'date_format' ), $timestamp ) : '';
	}
}
