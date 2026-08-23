<?php
/**
 * Product review count dynamic tag.
 *
 * @package PixelomaticCore
 */

namespace PixelomaticCore\Elementor\Dynamic_Tags;

defined( 'ABSPATH' ) || exit;

/**
 * How many reviews the product has.
 */
final class Download_Reviews extends Base_Tag {

	/**
	 * Tag name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'pixelomatic-download-reviews';
	}

	/**
	 * Picker label.
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'Product review count', 'pixelomatic-core' );
	}

	/**
	 * Returns the value.
	 *
	 * @param int $download_id Download ID.
	 * @return string
	 */
	protected function value_for( int $download_id ): string {
		$count = (int) get_post_meta( $download_id, '_pixelomatic_rating_count', true );

		return $count > 0 ? number_format_i18n( $count ) : '';
	}
}
