<?php
/**
 * Widget usage index.
 *
 * Records which Pixelomatic widgets a document uses, once, when it is saved.
 *
 * The alternative — working it out on the front end — means parsing
 * _elementor_data on every request, which is the thing the bundle is supposed
 * to be saving. Computing it at save time costs one walk per edit and turns
 * the front-end question into a single meta read.
 *
 * @package PixelomaticCore
 */

namespace PixelomaticCore\Assets;

defined( 'ABSPATH' ) || exit;

/**
 * Maps a post to the widget slugs it renders.
 */
final class Usage_Index {

	/**
	 * Post meta key.
	 */
	public const META = '_pixelomatic_widgets';

	/**
	 * Attaches hooks.
	 *
	 * @return void
	 */
	public function register(): void {
		// Fires for every Elementor document type, including the kit and the
		// builder templates added in M9.
		add_action( 'elementor/document/after_save', array( $this, 'reindex_document' ) );

		// A revision restore or an import writes _elementor_data directly
		// without going through the editor's save.
		add_action( 'updated_post_meta', array( $this, 'reindex_on_meta' ), 10, 3 );
		add_action( 'added_post_meta', array( $this, 'reindex_on_meta' ), 10, 3 );
	}

	/**
	 * Reindexes after an editor save.
	 *
	 * @param object $document Elementor document.
	 * @return void
	 */
	public function reindex_document( $document ): void {
		if ( ! is_object( $document ) || ! method_exists( $document, 'get_main_id' ) ) {
			return;
		}

		$this->reindex( (int) $document->get_main_id() );
	}

	/**
	 * Reindexes when _elementor_data is written outside the editor.
	 *
	 * @param int    $meta_id  Meta row ID.
	 * @param int    $post_id  Post ID.
	 * @param string $meta_key Meta key.
	 * @return void
	 */
	public function reindex_on_meta( int $meta_id, int $post_id, string $meta_key ): void {
		if ( '_elementor_data' !== $meta_key ) {
			return;
		}

		$this->reindex( $post_id );
	}

	/**
	 * Recomputes and stores the widget list for a post.
	 *
	 * @param int $post_id Post ID.
	 * @return string[] The slugs found.
	 */
	public function reindex( int $post_id ): array {
		if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
			return array();
		}

		$slugs = $this->parse( $post_id );

		if ( empty( $slugs ) ) {
			delete_post_meta( $post_id, self::META );
		} else {
			update_post_meta( $post_id, self::META, $slugs );
		}

		return $slugs;
	}

	/**
	 * Returns the widget slugs stored for a post.
	 *
	 * @param int $post_id Post ID.
	 * @return string[]
	 */
	public function for_post( int $post_id ): array {
		$stored = get_post_meta( $post_id, self::META, true );

		return is_array( $stored ) ? array_values( array_filter( array_map( 'strval', $stored ) ) ) : array();
	}

	/**
	 * How many posts use a widget.
	 *
	 * Answers the question the settings screen needs before letting somebody
	 * switch a widget off: turning one off does not hide it, it removes it,
	 * and a count is the difference between an informed decision and a
	 * surprise.
	 *
	 * @param string $slug Widget slug.
	 * @return int
	 */
	public function usage_count( string $slug ): int {
		$posts = get_posts(
			array(
				'post_type'              => 'any',
				'post_status'            => 'any',
				'posts_per_page'         => 100,
				'fields'                 => 'ids',
				'no_found_rows'          => true,
				'update_post_term_cache' => false,
				'meta_query'             => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Admin-only, and the result is cached by the caller.
					array(
						'key'     => self::META,
						'value'   => '"' . $slug . '"',
						'compare' => 'LIKE',
					),
				),
			)
		);

		return count( $posts );
	}

	/**
	 * Walks a document's data and collects Pixelomatic widget slugs.
	 *
	 * @param int $post_id Post ID.
	 * @return string[] Sorted, unique slugs.
	 */
	private function parse( int $post_id ): array {
		$raw = get_post_meta( $post_id, '_elementor_data', true );

		if ( empty( $raw ) ) {
			return array();
		}

		$data = is_string( $raw ) ? json_decode( $raw, true ) : $raw;

		if ( ! is_array( $data ) ) {
			return array();
		}

		$found = array();
		$this->walk( $data, $found );

		// Sorted so the same widget set always produces the same hash,
		// whatever order the editor happened to place them in.
		sort( $found );

		return array_values( array_unique( $found ) );
	}

	/**
	 * Recursively collects widget types.
	 *
	 * @param array<int|string, mixed> $elements Element tree.
	 * @param string[]                 $found    Accumulator, by reference.
	 * @return void
	 */
	private function walk( array $elements, array &$found ): void {
		foreach ( $elements as $element ) {
			if ( ! is_array( $element ) ) {
				continue;
			}

			$type = (string) ( $element['widgetType'] ?? '' );

			if ( 0 === strpos( $type, 'pixelomatic-' ) ) {
				$found[] = substr( $type, strlen( 'pixelomatic-' ) );
			}

			if ( ! empty( $element['elements'] ) && is_array( $element['elements'] ) ) {
				$this->walk( $element['elements'], $found );
			}
		}
	}
}
