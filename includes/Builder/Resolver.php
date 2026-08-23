<?php
/**
 * Template resolution.
 *
 * @package PixelomaticCore
 */

namespace PixelomaticCore\Builder;

defined( 'ABSPATH' ) || exit;

use PixelomaticCore\Builder\Conditions\Manager;

/**
 * Decides which template applies to the current request.
 *
 * Builds the condition keys this request satisfies, looks each up in the
 * compiled map and keeps the highest-scoring hit. No query, no loop over
 * templates — the cost is a handful of array lookups against one autoloaded
 * option, whatever the site has.
 */
final class Resolver {

	/**
	 * Conditions manager.
	 *
	 * @var Manager
	 */
	private $conditions;

	/**
	 * Resolved template IDs for this request, by type.
	 *
	 * @var array<string, int>
	 */
	private $resolved = array();

	/**
	 * Constructor.
	 *
	 * @param Manager $conditions Conditions manager.
	 */
	public function __construct( Manager $conditions ) {
		$this->conditions = $conditions;
	}

	/**
	 * Returns the template ID for a type, or 0.
	 *
	 * @param string $type Template type.
	 * @return int
	 */
	public function resolve( string $type ): int {
		if ( isset( $this->resolved[ $type ] ) ) {
			return $this->resolved[ $type ];
		}

		$map = $this->conditions->map()[ $type ] ?? array();

		if ( empty( $map ) ) {
			$this->resolved[ $type ] = 0;
			return 0;
		}

		$keys       = $this->keys_for_request();
		$excluded   = $map['exclude'] ?? array();
		$best       = 0;
		$best_score = -1;

		foreach ( $keys as $key ) {
			if ( ! isset( $map[ $key ] ) ) {
				continue;
			}

			$candidate = $map[ $key ];

			// An exclusion on any key this request matches removes that
			// template outright, however specific its match was.
			if ( $this->is_excluded( (int) $candidate['id'], $keys, $excluded ) ) {
				continue;
			}

			if ( (int) $candidate['specificity'] > $best_score ) {
				$best       = (int) $candidate['id'];
				$best_score = (int) $candidate['specificity'];
			}
		}

		/**
		 * Filters the resolved template for a location.
		 *
		 * @since 1.0.0
		 *
		 * @param int    $best Template ID, or 0.
		 * @param string $type Template type.
		 */
		$best = (int) apply_filters( 'pixelomatic_core/builder/resolve', $best, $type );

		$this->resolved[ $type ] = $best;

		return $best;
	}

	/**
	 * Whether a template is excluded from this request.
	 *
	 * @param int                      $template_id Template ID.
	 * @param string[]                 $keys        Keys this request matches.
	 * @param array<string, list<int>> $excluded    Exclusion map.
	 * @return bool
	 */
	private function is_excluded( int $template_id, array $keys, array $excluded ): bool {
		foreach ( $keys as $key ) {
			if ( in_array( $template_id, (array) ( $excluded[ $key ] ?? array() ), true ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Builds every condition key the current request satisfies.
	 *
	 * @return string[]
	 */
	private function keys_for_request(): array {
		$keys = array( 'entire_site' );

		if ( is_front_page() ) {
			$keys[] = 'front_page';
		}

		if ( is_home() ) {
			$keys[] = 'blog';
		}

		if ( is_search() ) {
			$keys[] = 'search';
		}

		if ( is_404() ) {
			$keys[] = 'not_found';
		}

		if ( is_archive() ) {
			$keys[] = 'archive';
		}

		if ( is_singular() ) {
			$id        = (int) get_queried_object_id();
			$post_type = (string) get_post_type( $id );

			$keys[] = 'singular:any';
			$keys[] = 'singular:' . $id;
			$keys[] = 'post_type:any';

			$object = get_post_type_object( $post_type );

			if ( $object ) {
				$keys[] = 'post_type:' . $post_type;
			}

			if ( 'download' === $post_type ) {
				$keys[] = 'edd_downloads';
			}
		}

		if ( is_category() || is_tag() || is_tax() ) {
			$term = get_queried_object();

			$keys[] = 'taxonomy:any';
			$keys[] = 'term:any';

			if ( $term instanceof \WP_Term ) {
				$keys[] = 'term:' . (int) $term->term_id;
				$keys[] = 'taxonomy:' . $term->taxonomy;
			}
		}

		if ( is_post_type_archive( 'download' ) ) {
			$keys[] = 'edd_downloads';
		}

		if ( is_author() ) {
			$keys[] = 'author:any';
			$keys[] = 'author:' . (int) get_queried_object_id();
		}

		if ( function_exists( 'edd_is_checkout' ) && edd_is_checkout() ) {
			$keys[] = 'edd_checkout';
		}

		if ( class_exists( '\Pixelomatic\Integrations\EDD\Account' ) && \Pixelomatic\Integrations\EDD\Account::is_account_page() ) {
			$keys[] = 'edd_account';
		}

		return array_values( array_unique( $keys ) );
	}
}
