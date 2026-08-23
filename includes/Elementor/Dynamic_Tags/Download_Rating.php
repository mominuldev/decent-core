<?php
/**
 * Product rating dynamic tag.
 *
 * @package PixelomaticCore
 */

namespace PixelomaticCore\Elementor\Dynamic_Tags;

defined( 'ABSPATH' ) || exit;

/**
 * The average rating, to one decimal.
 */
final class Download_Rating extends Base_Tag {

	/**
	 * Tag name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'pixelomatic-download-rating';
	}

	/**
	 * Picker label.
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'Product rating', 'pixelomatic-core' );
	}

	/**
	 * Returns the value.
	 *
	 * @param int $download_id Download ID.
	 * @return string
	 */
	protected function value_for( int $download_id ): string {
		$average = (float) get_post_meta( $download_id, '_pixelomatic_rating_avg', true );

		return $average > 0 ? number_format_i18n( $average, 1 ) : '';
	}
}
