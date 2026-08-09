<?php
/**
 * Template types.
 *
 * @package DecentCore
 */

namespace DecentCore\Builder;

defined( 'ABSPATH' ) || exit;

/**
 * The kinds of template the builder can produce.
 */
final class Template_Type {

	/**
	 * Meta key holding a template's type.
	 */
	public const META = '_decent_template_type';

	/**
	 * Replaces the site header.
	 */
	public const HEADER = 'header';

	/**
	 * Replaces the site footer.
	 */
	public const FOOTER = 'footer';

	/**
	 * A reusable block, placed by hook rather than by replacement.
	 */
	public const BLOCK = 'block';

	/**
	 * Returns every type with its label.
	 *
	 * @return array<string, string>
	 */
	public static function all(): array {
		return array(
			self::HEADER => __( 'Header', 'decent-core' ),
			self::FOOTER => __( 'Footer', 'decent-core' ),
			self::BLOCK  => __( 'Block', 'decent-core' ),
		);
	}

	/**
	 * Coerces a value to a known type.
	 *
	 * @param mixed $value Raw value.
	 * @return string
	 */
	public static function sanitize( $value ): string {
		$value = sanitize_key( (string) $value );

		return isset( self::all()[ $value ] ) ? $value : self::HEADER;
	}

	/**
	 * Returns a template's type.
	 *
	 * @param int $post_id Template ID.
	 * @return string
	 */
	public static function of( int $post_id ): string {
		return self::sanitize( get_post_meta( $post_id, self::META, true ) );
	}
}
