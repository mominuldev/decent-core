<?php
/**
 * Minimal Decent Themes stubs for static analysis.
 *
 * The theme is an optional runtime dependency: widgets that need it declare
 * `'requires' => array( 'theme' )` and every other call site is guarded by
 * class_exists(). That keeps PHPStan quiet but also means the theme's API is
 * never actually checked, so a renamed method or a changed signature would
 * only show up on a live page.
 *
 * Declaring the surface the plugin touches — the icon map and the product
 * card — buys that check back. Same reasoning, and the same deliberate
 * smallness, as the Elementor stub beside this file.
 *
 * Never loaded at runtime. See phpstan.neon.dist -> scanFiles.
 *
 * @package DecentCore
 */

namespace DecentThemes\Frontend;

/**
 * The theme's inline SVG icon map.
 */
final class Icons {

	/**
	 * Returns an icon's SVG markup, or an empty string for an unknown slug.
	 *
	 * @param string               $slug Icon slug.
	 * @param array<string, mixed> $args size, stroke, class, label.
	 * @return string
	 */
	public static function get( string $slug, array $args = array() ): string {}

	/**
	 * Echoes an icon.
	 *
	 * @param string               $slug Icon slug.
	 * @param array<string, mixed> $args See get().
	 * @return void
	 */
	public static function render( string $slug, array $args = array() ): void {}

	/**
	 * Whether the slug exists in the map.
	 *
	 * @param string $slug Icon slug.
	 * @return bool
	 */
	public static function has( string $slug ): bool {}

	/**
	 * Every available slug.
	 *
	 * @return string[]
	 */
	public static function slugs(): array {}

	/**
	 * The wp_kses allow-list icon markup is printed through.
	 *
	 * @return array<string, array<string, bool>>
	 */
	public static function allowed_html(): array {}
}

/**
 * The theme's product card — the one implementation of .product-card markup.
 */
final class Card {

	/**
	 * Renders a card for a download.
	 *
	 * @param int                  $download_id Download ID.
	 * @param array<string, mixed> $args        density, badge, actions, rating,
	 *                                          context, heading, lcp.
	 * @return void
	 */
	public static function render( int $download_id, array $args = array() ): void {}

	/**
	 * Returns the badge for a download, or an empty string.
	 *
	 * @param int    $download_id Download ID.
	 * @param string $mode        'auto', 'none', or a literal label.
	 * @return string
	 */
	public static function badge( int $download_id, string $mode = 'auto' ): string {}

	/**
	 * Returns the product type label shown above the title.
	 *
	 * @param int $download_id Download ID.
	 * @return array{label: string, accent: string}
	 */
	public static function type( int $download_id ): array {}

	/**
	 * Returns the rating summary for a download.
	 *
	 * @param int $download_id Download ID.
	 * @return array{average: float, count: int}
	 */
	public static function rating( int $download_id ): array {}

	/**
	 * Returns the demo URL for a download, or an empty string.
	 *
	 * @param int $download_id Download ID.
	 * @return string
	 */
	public static function demo_url( int $download_id ): string {}

	/**
	 * Returns the sales count note shown beside the price.
	 *
	 * @param int $download_id Download ID.
	 * @return string
	 */
	public static function sales_note( int $download_id ): string {}

	/**
	 * Returns the initials for a display name.
	 *
	 * @param string $name Display name.
	 * @return string
	 */
	public static function initials( string $name ): string {}

	/**
	 * Returns the star glyphs for a rating.
	 *
	 * @param float $average Average rating, 0-5.
	 * @return string
	 */
	public static function stars( float $average ): string {}

	/**
	 * Whether the reviews provider has anything to show for this download.
	 *
	 * @param int $download_id Download ID.
	 * @return bool
	 */
	public static function has_reviews( int $download_id ): bool {}
}
