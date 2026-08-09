<?php
/**
 * Settings schema reader and validator.
 *
 * @package DecentCore
 */

namespace DecentCore\Settings;

defined( 'ABSPATH' ) || exit;

/**
 * Loads config/settings.php and validates values against it.
 */
final class Schema {

	/**
	 * Field definitions, loaded once per request.
	 *
	 * @var array<string, array<string, mixed>>|null
	 */
	private static $fields = null;

	/**
	 * Tab labels, in display order.
	 *
	 * @return array<string, string>
	 */
	public static function tabs(): array {
		return array(
			'general'     => __( 'General', 'decent-core' ),
			'widgets'     => __( 'Widgets', 'decent-core' ),
			'extensions'  => __( 'Extensions', 'decent-core' ),
			'edd'         => __( 'Catalogue', 'decent-core' ),
			'performance' => __( 'Performance', 'decent-core' ),
			'tools'       => __( 'Tools', 'decent-core' ),
		);
	}

	/**
	 * Returns every field definition.
	 *
	 * @return array<string, array<string, mixed>>
	 */
	public static function fields(): array {
		if ( null === self::$fields ) {
			/**
			 * Filters the settings schema.
			 *
			 * Every entry must declare `type`, `default` and `sanitize`, or it
			 * will be rejected on save.
			 *
			 * @since 1.0.0
			 *
			 * @param array<string, array<string, mixed>> $fields Field definitions.
			 */
			self::$fields = apply_filters(
				'decent_core/settings/schema',
				require DECENT_CORE_DIR . 'config/settings.php'
			);
		}

		return self::$fields;
	}

	/**
	 * Returns the fields belonging to a tab.
	 *
	 * @param string $tab Tab key.
	 * @return array<string, array<string, mixed>>
	 */
	public static function fields_for( string $tab ): array {
		return array_filter(
			self::fields(),
			static function ( array $field ) use ( $tab ): bool {
				return ( $field['tab'] ?? '' ) === $tab;
			}
		);
	}

	/**
	 * Returns the default value for every field.
	 *
	 * @return array<string, mixed>
	 */
	public static function defaults(): array {
		$defaults = array();

		foreach ( self::fields() as $key => $field ) {
			$defaults[ $key ] = $field['default'];
		}

		return $defaults;
	}

	/**
	 * Coerces one value to its declared type and range.
	 *
	 * Unknown keys return null so the caller can reject them outright rather
	 * than storing something the schema does not describe.
	 *
	 * @param string $key   Field key.
	 * @param mixed  $value Raw value.
	 * @return mixed|null Null when the key is unknown.
	 */
	public static function sanitize( string $key, $value ) {
		$fields = self::fields();

		if ( ! isset( $fields[ $key ] ) ) {
			return null;
		}

		$field = $fields[ $key ];
		$clean = call_user_func( $field['sanitize'], $value );

		switch ( $field['type'] ) {
			case 'boolean':
				return (bool) $clean;

			case 'integer':
				$clean = (int) $clean;

				if ( isset( $field['min'] ) ) {
					$clean = max( (int) $field['min'], $clean );
				}

				if ( isset( $field['max'] ) ) {
					$clean = min( (int) $field['max'], $clean );
				}

				return $clean;

			case 'string':
			default:
				$clean = (string) $clean;

				// An allow-list is not a suggestion: anything outside it falls
				// back to the default rather than being stored.
				if ( isset( $field['allowed'] ) && ! in_array( $clean, (array) $field['allowed'], true ) ) {
					return $field['default'];
				}

				return $clean;
		}
	}

	/**
	 * Builds the REST argument schema from the field definitions.
	 *
	 * @return array<string, array<string, mixed>>
	 */
	public static function rest_args(): array {
		$args = array();

		foreach ( self::fields() as $key => $field ) {
			$arg = array(
				'type'     => $field['type'],
				'required' => false,
			);

			if ( isset( $field['allowed'] ) ) {
				$arg['enum'] = $field['allowed'];
			}

			if ( isset( $field['min'] ) ) {
				$arg['minimum'] = (int) $field['min'];
			}

			if ( isset( $field['max'] ) ) {
				$arg['maximum'] = (int) $field['max'];
			}

			$args[ $key ] = $arg;
		}

		return $args;
	}
}
