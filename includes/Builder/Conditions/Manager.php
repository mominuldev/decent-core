<?php
/**
 * Display conditions.
 *
 * @package PixelomaticCore
 */

namespace PixelomaticCore\Builder\Conditions;

defined( 'ABSPATH' ) || exit;

use PixelomaticCore\Builder\Post_Type;
use PixelomaticCore\Builder\Template_Type;

/**
 * Stores conditions and compiles them into one lookup table.
 *
 * The naive design runs a WP_Query over every template on every request and
 * evaluates each one's rules in a loop. At thirty templates that is a
 * measurable cost on every page load, forever.
 *
 * Instead the rules are compiled once, when a template is saved, into a single
 * autoloaded option keyed by the condition strings a request could match. The
 * Resolver then builds the handful of keys this request satisfies and takes
 * the most specific hit — an array lookup, no query.
 */
final class Manager {

	/**
	 * Option holding the compiled map.
	 */
	public const OPTION = 'pixelomatic_core_conditions_map';

	/**
	 * Post meta holding a template's raw rules.
	 */
	public const META = '_pixelomatic_conditions';

	/**
	 * Attaches hooks.
	 *
	 * @return void
	 */
	public function register(): void {
		// Wrapped: recompile() returns the map for the Tools button and tests,
		// and an action callback must not return anything.
		add_action(
			'save_post_' . Post_Type::NAME,
			function (): void {
				$this->recompile();
			},
			20
		);
		add_action( 'deleted_post', array( $this, 'recompile_on_delete' ), 20, 2 );
		add_action( 'trashed_post', array( $this, 'recompile_on_delete' ), 20 );
		add_action( 'untrashed_post', array( $this, 'recompile_on_delete' ), 20 );
	}

	/**
	 * Recompiles when a template is deleted or its status changes.
	 *
	 * @param int           $post_id Post ID.
	 * @param \WP_Post|null $post    Post object, when the hook supplies one.
	 * @return void
	 */
	public function recompile_on_delete( $post_id, $post = null ): void {
		if ( Post_Type::NAME !== get_post_type( (int) $post_id ) && ! ( $post && Post_Type::NAME === $post->post_type ) ) {
			return;
		}

		$this->recompile();
	}

	/**
	 * Rebuilds the compiled map from every published template.
	 *
	 * @return array<string, array<string, array{id:int, specificity:int}>>
	 */
	public function recompile(): array {
		$templates = get_posts(
			array(
				'post_type'              => Post_Type::NAME,
				'post_status'            => 'publish',
				// phpcs:ignore WordPress.WP.PostsPerPage.posts_per_page_posts_per_page -- Every published template has to be compiled into the map; the ceiling is a guard, not a page size.
				'posts_per_page'         => 200,
				'no_found_rows'          => true,
				'update_post_term_cache' => false,
			)
		);

		$map = array();

		foreach ( $templates as $template ) {
			$type  = Template_Type::of( (int) $template->ID );
			$rules = self::rules_for( (int) $template->ID );

			foreach ( $rules as $rule ) {
				$key = Specificity::key( $rule );

				if ( '' === $key ) {
					continue;
				}

				$score   = Specificity::score( $rule );
				$exclude = ! empty( $rule['exclude'] );

				if ( $exclude ) {
					$map[ $type ]['exclude'][ $key ][] = (int) $template->ID;
					continue;
				}

				$current = $map[ $type ][ $key ] ?? null;

				// Ties go to the template saved most recently, which is what
				// somebody assigning a second header to the same place expects.
				if ( null === $current || $score >= $current['specificity'] ) {
					$map[ $type ][ $key ] = array(
						'id'          => (int) $template->ID,
						'specificity' => $score,
					);
				}
			}
		}

		update_option( self::OPTION, $map, true );

		return $map;
	}

	/**
	 * Returns the compiled map, building it if it is missing.
	 *
	 * @return array<string, mixed>
	 */
	public function map(): array {
		$map = get_option( self::OPTION, null );

		if ( ! is_array( $map ) ) {
			$map = $this->recompile();
		}

		return $map;
	}

	/**
	 * Returns a template's raw rules.
	 *
	 * @param int $post_id Template ID.
	 * @return array<int, array<string, mixed>>
	 */
	public static function rules_for( int $post_id ): array {
		$stored = get_post_meta( $post_id, self::META, true );

		if ( ! is_array( $stored ) || empty( $stored ) ) {
			// A template with no rules applies nowhere. Defaulting to
			// "everywhere" would let a half-finished header take over the site
			// the moment it is published.
			return array();
		}

		return $stored;
	}

	/**
	 * Validates and stores a template's rules.
	 *
	 * @param int                              $post_id Template ID.
	 * @param array<int, array<string, mixed>> $rules   Raw rules.
	 * @return void
	 */
	public static function save_rules( int $post_id, array $rules ): void {
		$clean = array();

		foreach ( $rules as $rule ) {
			if ( ! is_array( $rule ) ) {
				continue;
			}

			$type = sanitize_key( (string) ( $rule['type'] ?? '' ) );

			if ( ! Specificity::is_known( $type ) ) {
				continue;
			}

			$clean[] = array(
				'type'    => $type,
				'object'  => absint( $rule['object'] ?? 0 ),
				'exclude' => ! empty( $rule['exclude'] ),
			);
		}

		if ( empty( $clean ) ) {
			delete_post_meta( $post_id, self::META );
			return;
		}

		update_post_meta( $post_id, self::META, $clean );
	}
}
