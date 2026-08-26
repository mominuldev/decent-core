<?php
/**
 * Template list table.
 *
 * @package PixelomaticCore
 */

namespace PixelomaticCore\Builder\Admin;

defined( 'ABSPATH' ) || exit;

use PixelomaticCore\Builder\Post_Type;
use PixelomaticCore\Builder\Template_Type;
use WP_Query;

/**
 * Splits the template list by type.
 *
 * A site with a dozen templates has headers, footers and blocks interleaved in
 * one list, and the only way to tell them apart is the Type column. The type
 * links sit where WordPress already puts its own status links, so the screen
 * stays a WordPress list table rather than becoming a dashboard.
 */
final class Templates_List {

	/**
	 * Query argument carrying the selected type.
	 */
	public const QUERY_VAR = 'pixelomatic_type';

	/**
	 * Attaches hooks.
	 *
	 * @return void
	 */
	public function register(): void {
		add_filter( 'views_edit-' . Post_Type::NAME, array( $this, 'views' ) );
		add_action( 'pre_get_posts', array( $this, 'filter_query' ) );
	}

	/**
	 * Adds a link per type beside the status links.
	 *
	 * @param array<string, string> $views Status links.
	 * @return array<string, string>
	 */
	public function views( $views ): array {
		$views   = (array) $views;
		$current = self::selected_type();
		$counts  = self::counts();

		if ( '' !== $current ) {
			// WordPress marks "All" current from the status alone, and would
			// otherwise show two current links at once.
			foreach ( $views as $key => $view ) {
				$views[ $key ] = str_replace(
					array( ' class="current"', ' aria-current="page"' ),
					'',
					(string) $view
				);
			}
		}

		$base = add_query_arg( 'post_type', Post_Type::NAME, admin_url( 'edit.php' ) );

		foreach ( Template_Type::all() as $type => $label ) {
			if ( empty( $counts[ $type ] ) ) {
				continue;
			}

			$views[ 'pixelomatic_' . $type ] = sprintf(
				'<a href="%1$s"%2$s>%3$s <span class="count">(%4$d)</span></a>',
				esc_url( add_query_arg( self::QUERY_VAR, $type, $base ) ),
				$current === $type ? ' class="current" aria-current="page"' : '',
				esc_html( $label ),
				(int) $counts[ $type ]
			);
		}

		return $views;
	}

	/**
	 * Narrows the list to the selected type.
	 *
	 * @param WP_Query $query Query being prepared.
	 * @return void
	 */
	public function filter_query( $query ): void {
		global $pagenow;

		if ( ! is_admin() || 'edit.php' !== $pagenow || ! $query instanceof WP_Query || ! $query->is_main_query() ) {
			return;
		}

		if ( Post_Type::NAME !== $query->get( 'post_type' ) ) {
			return;
		}

		$type = self::selected_type();

		if ( '' === $type ) {
			return;
		}

		$clause = array(
			array(
				'key'   => Template_Type::META,
				'value' => $type,
			),
		);

		// Header is what an absent type resolves to, so a template saved
		// before the meta existed belongs in the header list.
		if ( Template_Type::HEADER === $type ) {
			$clause = array(
				'relation' => 'OR',
				$clause[0],
				array(
					'key'     => Template_Type::META,
					'compare' => 'NOT EXISTS',
				),
			);
		}

		$query->set( 'meta_query', $clause ); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- One admin screen, over a handful of templates.
	}

	/**
	 * The type selected in the query string, or an empty string.
	 *
	 * @return string
	 */
	public static function selected_type(): string {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only navigation between views of the same list.
		$raw = isset( $_GET[ self::QUERY_VAR ] ) ? sanitize_key( wp_unslash( $_GET[ self::QUERY_VAR ] ) ) : '';

		return isset( Template_Type::all()[ $raw ] ) ? $raw : '';
	}

	/**
	 * Counts templates by type.
	 *
	 * One query for the IDs and one for their meta, rather than a query per
	 * type: three counts are not worth three round trips.
	 *
	 * @return array<string, int>
	 */
	private static function counts(): array {
		$ids = get_posts(
			array(
				'post_type'              => Post_Type::NAME,
				'post_status'            => array( 'publish', 'future', 'draft', 'pending', 'private' ),
				// phpcs:ignore WordPress.WP.PostsPerPage.posts_per_page_posts_per_page -- Templates are hand-authored; the ceiling is a guard, not a page size, and the query asks for IDs only.
				'posts_per_page'         => 200,
				'fields'                 => 'ids',
				'no_found_rows'          => true,
				'update_post_term_cache' => false,
			)
		);

		if ( empty( $ids ) ) {
			return array();
		}

		update_meta_cache( 'post', $ids );

		$counts = array();

		foreach ( $ids as $id ) {
			$type            = Template_Type::of( (int) $id );
			$counts[ $type ] = ( $counts[ $type ] ?? 0 ) + 1;
		}

		return $counts;
	}
}
