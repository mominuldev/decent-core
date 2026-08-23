<?php
/**
 * Product price dynamic tag.
 *
 * @package PixelomaticCore
 */

namespace PixelomaticCore\Elementor\Dynamic_Tags;

defined( 'ABSPATH' ) || exit;

/**
 * The lowest price, or the licence range.
 */
final class Download_Price extends Base_Tag {

	/**
	 * Tag name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'pixelomatic-download-price';
	}

	/**
	 * Picker label.
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'Product price', 'pixelomatic-core' );
	}

	/**
	 * Returns the value.
	 *
	 * @param int $download_id Download ID.
	 * @return string
	 */
	protected function value_for( int $download_id ): string {
		if ( ! function_exists( 'edd_currency_filter' ) ) {
			return '';
		}

		$min = (float) get_post_meta( $download_id, '_pixelomatic_price_min', true );
		$max = (float) get_post_meta( $download_id, '_pixelomatic_price_max', true );

		$low = (string) edd_currency_filter( edd_format_amount( $min ) );

		if ( $max <= $min ) {
			return $low;
		}

		return sprintf(
			/* translators: 1: lowest price, 2: highest price. */
			__( '%1$s – %2$s', 'pixelomatic-core' ),
			$low,
			(string) edd_currency_filter( edd_format_amount( $max ) )
		);
	}
}
