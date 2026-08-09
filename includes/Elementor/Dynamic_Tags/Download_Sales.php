<?php
/**
 * Product sales dynamic tag.
 *
 * @package DecentCore
 */

namespace DecentCore\Elementor\Dynamic_Tags;

defined( 'ABSPATH' ) || exit;

/**
 * How many times the product has sold.
 */
final class Download_Sales extends Base_Tag {

	/**
	 * Tag name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'decent-download-sales';
	}

	/**
	 * Picker label.
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'Product sales', 'decent-core' );
	}

	/**
	 * Returns the value.
	 *
	 * @param int $download_id Download ID.
	 * @return string
	 */
	protected function value_for( int $download_id ): string {
		if ( ! function_exists( 'edd_get_download_sales_stats' ) ) {
			return '';
		}

		$sales = (int) edd_get_download_sales_stats( $download_id );

		return $sales > 0 ? number_format_i18n( $sales ) : '';
	}
}
