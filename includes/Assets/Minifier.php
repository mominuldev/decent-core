<?php
/**
 * Conservative minifier.
 *
 * Whitespace and comments only. No renaming, no restructuring, no AST.
 *
 * Worth being honest about what this is for: the widget assets Gulp produces
 * are already minified by cssnano and terser, so on a normal production build
 * this is close to a no-op. It earns its place on a development build, and on
 * the join seams between concatenated files.
 *
 * An AST minifier in PHP would be a large dependency and a real source of
 * "worked locally, broke in production". Sites wanting more should use a
 * dedicated optimisation plugin; these bundles are ordinary files and work
 * fine with one.
 *
 * @package DecentCore
 */

namespace DecentCore\Assets;

defined( 'ABSPATH' ) || exit;

/**
 * Strips comments and redundant whitespace.
 */
final class Minifier {

	/**
	 * Minifies a string.
	 *
	 * @param string $source    Source text.
	 * @param string $extension 'css' or 'js'.
	 * @return string
	 */
	public static function run( string $source, string $extension ): string {
		return 'css' === $extension ? self::css( $source ) : self::js( $source );
	}

	/**
	 * Minifies CSS.
	 *
	 * @param string $css Source.
	 * @return string
	 */
	private static function css( string $css ): string {
		// Comments, but not /*! … */ licence headers.
		$css = (string) preg_replace( '#/\*(?!!)[^*]*\*+([^/*][^*]*\*+)*/#', '', $css );

		$css = (string) preg_replace( '/\s+/', ' ', $css );
		$css = (string) preg_replace( '/\s*([{}:;,>~+])\s*/', '$1', $css );
		$css = (string) preg_replace( '/;}/', '}', $css );

		return trim( $css );
	}

	/**
	 * Minifies JavaScript.
	 *
	 * Deliberately timid. Line comments are only stripped when the line has no
	 * quote or slash on it, because telling a comment from a string or a regex
	 * literal needs a parser, and guessing wrong silently breaks the file.
	 *
	 * @param string $js Source.
	 * @return string
	 */
	private static function js( string $js ): string {
		$out = array();

		$lines = preg_split( '/\R/', $js );

		if ( ! is_array( $lines ) ) {
			return trim( $js );
		}

		foreach ( $lines as $line ) {
			$trimmed = trim( $line );

			if ( '' === $trimmed ) {
				continue;
			}

			// Whole-line comment with nothing that could be a string or regex.
			if ( 0 === strpos( $trimmed, '//' ) && ! preg_match( '#["\'`/]#', substr( $trimmed, 2 ) ) ) {
				continue;
			}

			$out[] = $trimmed;
		}

		// Block comments are safe to remove only when they contain no quote,
		// for the same reason.
		$joined = implode( "\n", $out );

		return (string) preg_replace( '#/\*(?!!)[^"\'`]*?\*/#s', '', $joined );
	}
}
