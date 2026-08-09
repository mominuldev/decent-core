<?php
/**
 * Base widget.
 *
 * Every Decent Core widget extends this. It reads the widget map so no widget
 * hand-maintains its own name, title, icon, category or asset dependencies —
 * those live in config/widgets.php and are declared once.
 *
 * @package DecentCore
 */

namespace DecentCore\Elementor\Base;

defined( 'ABSPATH' ) || exit;

use DecentCore\Elementor\Widget_Registry;
use Elementor\Widget_Base as Elementor_Widget_Base;

/**
 * Shared widget behaviour.
 */
abstract class Widget_Base extends Elementor_Widget_Base {

	/**
	 * The widget's slug in config/widgets.php.
	 *
	 * @return string
	 */
	abstract public static function slug(): string;

	/**
	 * Returns this widget's definition from the map.
	 *
	 * @return array<string, mixed>
	 */
	protected function definition(): array {
		return Widget_Registry::map()[ static::slug() ] ?? array();
	}

	/**
	 * Elementor widget name.
	 *
	 * Prefixed so it cannot collide with another plugin's widget, and stable —
	 * changing it would orphan every page already using the widget.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'decent-' . static::slug();
	}

	/**
	 * Panel title.
	 *
	 * @return string
	 */
	public function get_title() {
		return (string) ( $this->definition()['title'] ?? static::slug() );
	}

	/**
	 * Panel icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		return (string) ( $this->definition()['icon'] ?? 'eicon-square' );
	}

	/**
	 * Panel categories.
	 *
	 * @return string[]
	 */
	public function get_categories() {
		return array( (string) ( $this->definition()['category'] ?? 'decent-content' ) );
	}

	/**
	 * Panel search keywords.
	 *
	 * @return string[]
	 */
	public function get_keywords() {
		return (array) ( $this->definition()['keywords'] ?? array() );
	}

	/**
	 * Style handles this widget needs.
	 *
	 * Declared in the map, so Elementor enqueues them only where the widget
	 * actually renders. This is the guaranteed floor beneath the bundler.
	 *
	 * @return string[]
	 */
	public function get_style_depends() {
		return array_map(
			static function ( string $handle ): string {
				return 'decent-core-' . $handle;
			},
			(array) ( $this->definition()['styles'] ?? array() )
		);
	}

	/**
	 * Script handles this widget needs.
	 *
	 * @return string[]
	 */
	public function get_script_depends() {
		return array_map(
			static function ( string $handle ): string {
				return 'decent-core-' . $handle;
			},
			(array) ( $this->definition()['scripts'] ?? array() )
		);
	}

	/**
	 * Elementor sanitises control values on save; this escapes again on render.
	 *
	 * Two independent layers, neither of which is trusted to be the only one.
	 *
	 * @param string $key      Setting key.
	 * @param string $fallback Value when the setting is empty.
	 * @return string
	 */
	protected function text( string $key, string $fallback = '' ): string {
		$value = (string) ( $this->get_settings_for_display( $key ) ?? '' );

		return '' !== $value ? $value : $fallback;
	}

	/**
	 * Renders a heading at a caller-chosen level, from an allow-list.
	 *
	 * @param string $text  Heading text.
	 * @param string $tag   Requested tag.
	 * @param string $class CSS class.
	 * @return void
	 */
	protected function render_heading( string $text, string $tag, string $class ): void {
		if ( '' === $text ) {
			return;
		}

		$tag = in_array( $tag, array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'span', 'div' ), true ) ? $tag : 'h2';

		printf(
			'<%1$s class="%2$s">%3$s</%1$s>',
			esc_html( $tag ),
			esc_attr( $class ),
			esc_html( $text )
		);
	}
}
