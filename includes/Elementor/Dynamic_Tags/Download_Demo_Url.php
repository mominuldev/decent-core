<?php
/**
 * Product demo URL dynamic tag.
 *
 * @package PixelomaticCore
 */

namespace PixelomaticCore\Elementor\Dynamic_Tags;

defined( 'ABSPATH' ) || exit;

/**
 * The live demo link, for a button or a link control.
 */
final class Download_Demo_Url extends Base_Tag {

	/**
	 * A URL fills link and URL controls, not text ones.
	 *
	 * @return string[]
	 */
	public function get_categories() {
		return array( 'url', 'text' );
	}

	/**
	 * Tag name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'pixelomatic-download-demo-url';
	}

	/**
	 * Picker label.
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'Product demo URL', 'pixelomatic-core' );
	}

	/**
	 * Returns the value.
	 *
	 * @param int $download_id Download ID.
	 * @return string
	 */
	protected function value_for( int $download_id ): string {
		return (string) get_post_meta( $download_id, '_pixelomatic_demo_url', true );
	}
}
