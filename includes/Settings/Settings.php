<?php
/**
 * Settings storage.
 *
 * One autoloaded option holds everything, including the widget toggles: one
 * row and one query rather than option-table sprawl.
 *
 * @package DecentCore
 */

namespace DecentCore\Settings;

defined( 'ABSPATH' ) || exit;

/**
 * Typed read and write access to the plugin's settings.
 */
final class Settings {

	/**
	 * Option name.
	 */
	public const OPTION = 'decent_core_settings';

	/**
	 * Cached values.
	 *
	 * @var array<string, mixed>|null
	 */
	private static $values = null;

	/**
	 * Returns every setting, with defaults filled in.
	 *
	 * @return array<string, mixed>
	 */
	public static function all(): array {
		if ( null === self::$values ) {
			$stored = get_option( self::OPTION, array() );
			$stored = is_array( $stored ) ? $stored : array();

			self::$values = array_merge( Schema::defaults(), $stored );
		}

		return self::$values;
	}

	/**
	 * Returns one setting.
	 *
	 * @param string $key     Field key.
	 * @param mixed  $default Returned when the key is not in the schema.
	 * @return mixed
	 */
	public static function get( string $key, $default = null ) {
		$values = self::all();

		return array_key_exists( $key, $values ) ? $values[ $key ] : $default;
	}

	/**
	 * Returns a setting as a boolean.
	 *
	 * @param string $key Field key.
	 * @return bool
	 */
	public static function enabled( string $key ): bool {
		return (bool) self::get( $key, false );
	}

	/**
	 * Returns a setting as an integer.
	 *
	 * @param string $key Field key.
	 * @return int
	 */
	public static function int( string $key ): int {
		return (int) self::get( $key, 0 );
	}

	/**
	 * Writes a set of settings, rejecting anything the schema does not know.
	 *
	 * @param array<string, mixed> $input Raw values.
	 * @return array<string, mixed> The stored values.
	 */
	public static function save( array $input ): array {
		$current = self::all();

		foreach ( $input as $key => $value ) {
			$clean = Schema::sanitize( (string) $key, $value );

			// Unknown keys are rejected, not ignored: silently dropping them
			// makes a typo in a settings import look like it worked.
			if ( null === $clean ) {
				continue;
			}

			$current[ $key ] = $clean;
		}

		update_option( self::OPTION, $current, true );

		self::$values = $current;

		/**
		 * Fires after settings are written.
		 *
		 * @since 1.0.0
		 *
		 * @param array<string, mixed> $current Stored settings.
		 */
		do_action( 'decent_core/settings/saved', $current );

		return $current;
	}

	/**
	 * Clears the in-memory cache. For tests and for after a direct option write.
	 *
	 * @return void
	 */
	public static function flush(): void {
		self::$values = null;
	}
}
