<?php
/**
 * Condition keys and specificity.
 *
 * @package DecentCore
 */

namespace DecentCore\Builder\Conditions;

defined( 'ABSPATH' ) || exit;

/**
 * Translates a rule into a lookup key and a precedence score.
 *
 * The scores are what make "a header for the whole site, except a different
 * one on this page" work without anyone writing an order by hand: the more
 * specific rule simply scores higher.
 */
final class Specificity {

	/**
	 * Rule types and their scores.
	 *
	 * @return array<string, int>
	 */
	public static function types(): array {
		return array(
			'entire_site'   => 1,
			'front_page'    => 20,
			'blog'          => 20,
			'search'        => 20,
			'not_found'     => 20,
			'archive'       => 10,
			'post_type'     => 12,
			'taxonomy'      => 18,
			'term'          => 24,
			'author'        => 22,
			'singular'      => 30,
			'edd_downloads' => 14,
			'edd_checkout'  => 26,
			'edd_account'   => 26,
		);
	}

	/**
	 * Whether a rule type is known.
	 *
	 * @param string $type Rule type.
	 * @return bool
	 */
	public static function is_known( string $type ): bool {
		return isset( self::types()[ $type ] );
	}

	/**
	 * Returns the score for a rule.
	 *
	 * @param array<string, mixed> $rule Rule.
	 * @return int
	 */
	public static function score( array $rule ): int {
		$type = (string) ( $rule['type'] ?? '' );

		return self::types()[ $type ] ?? 0;
	}

	/**
	 * Returns the lookup key for a rule.
	 *
	 * Object-scoped rules carry their ID in the key, so the Resolver can build
	 * the exact strings this request satisfies and look each one up directly.
	 *
	 * @param array<string, mixed> $rule Rule.
	 * @return string Empty when the rule is unusable.
	 */
	public static function key( array $rule ): string {
		$type   = (string) ( $rule['type'] ?? '' );
		$object = absint( $rule['object'] ?? 0 );

		if ( ! self::is_known( $type ) ) {
			return '';
		}

		$scoped = array( 'singular', 'term', 'author', 'post_type', 'taxonomy' );

		if ( in_array( $type, $scoped, true ) ) {
			// A scoped rule with no object means "all of them", which is a
			// different and less specific key.
			return $object > 0 ? $type . ':' . $object : $type . ':any';
		}

		return $type;
	}
}
