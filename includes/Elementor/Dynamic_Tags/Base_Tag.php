<?php
/**
 * Dynamic tag base.
 *
 * @package DecentCore
 */

namespace DecentCore\Elementor\Dynamic_Tags;

defined( 'ABSPATH' ) || exit;

use Elementor\Core\DynamicTags\Tag;

/**
 * Shared behaviour for the plugin's dynamic tags.
 *
 * Every tag resolves the same way: the queried product on a single-product
 * view, the most recent product in the editor so the canvas shows something
 * real, and nothing anywhere else. Duplicating that in thirteen tags would be
 * thirteen chances to get the editor fallback wrong.
 */
abstract class Base_Tag extends Tag {

	/**
	 * Tag group shown in the picker.
	 *
	 * @return string
	 */
	public function get_group() {
		return 'decent-product';
	}

	/**
	 * Categories this tag can fill.
	 *
	 * @return string[]
	 */
	public function get_categories() {
		return array( 'text' );
	}

	/**
	 * Returns the download this tag reads.
	 *
	 * @return int Download ID, or 0.
	 */
	protected function download_id(): int {
		if ( is_singular( 'download' ) ) {
			return (int) get_queried_object_id();
		}

		$editor = \Elementor\Plugin::instance()->editor;

		if ( ! $editor || ! $editor->is_edit_mode() ) {
			return 0;
		}

		$preview = get_posts(
			array(
				'post_type'      => 'download',
				'post_status'    => 'publish',
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'no_found_rows'  => true,
			)
		);

		return empty( $preview ) ? 0 : (int) $preview[0];
	}

	/**
	 * Renders the tag's value.
	 *
	 * @return void
	 */
	public function render() {
		$id = $this->download_id();

		if ( ! $id ) {
			return;
		}

		$value = $this->value_for( $id );

		if ( '' === $value ) {
			return;
		}

		// A dynamic tag fills a control that may itself be an attribute, so
		// the value is escaped as text and never trusted as markup.
		echo esc_html( $value );
	}

	/**
	 * Returns this tag's value for a download.
	 *
	 * @param int $download_id Download ID.
	 * @return string
	 */
	abstract protected function value_for( int $download_id ): string;
}
