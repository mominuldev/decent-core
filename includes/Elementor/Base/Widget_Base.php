<?php
/**
 * Base widget.
 *
 * Every Pixelomatic Core widget extends this. It reads the widget map so no widget
 * hand-maintains its own name, title, icon, category or asset dependencies —
 * those live in config/widgets.php and are declared once.
 *
 * @package PixelomaticCore
 */

namespace PixelomaticCore\Elementor\Base;

defined( 'ABSPATH' ) || exit;

use PixelomaticCore\Elementor\Compat\Icon_Library;
use PixelomaticCore\Elementor\Widget_Registry;
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
		return 'pixelomatic-' . static::slug();
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
		return array( (string) ( $this->definition()['category'] ?? 'pixelomatic-content' ) );
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
				return 'pixelomatic-core-' . $handle;
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
				return 'pixelomatic-core-' . $handle;
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
	 * Renders an icon from the theme's SVG map.
	 *
	 * Widgets never embed SVG of their own. The theme owns the icon set, its
	 * stroke conventions and the wp_kses allow-list it is printed through, so
	 * a widget cannot introduce an off-system icon, an icon font or a sprite
	 * request. Under a third-party theme this renders nothing rather than
	 * inventing a substitute.
	 *
	 * @param string $slug   Icon slug.
	 * @param int    $size   Pixel size.
	 * @param float  $stroke Stroke width.
	 * @return void
	 */
	protected function icon( string $slug, int $size = 22, float $stroke = 1.6 ): void {
		if ( ! function_exists( 'pixelomatic_icon' ) ) {
			return;
		}

		pixelomatic_icon(
			$slug,
			array(
				'size'   => $size,
				'stroke' => $stroke,
			)
		);
	}

	/**
	 * Renders an icon chosen through Elementor's Icons control.
	 *
	 * The theme's own set is one of the tabs in that picker — Icon_Library
	 * registers it, and renders a pick from it as the theme's inline SVG, so
	 * the common case still costs no request and still goes through the
	 * theme's stroke conventions. What is new is that an editor may instead
	 * pick a Font Awesome glyph or an uploaded SVG, and this has to print
	 * those safely too.
	 *
	 * Everything is passed through wp_kses on the way out, whatever produced
	 * it. Elementor sanitises an uploaded SVG on upload; this is the second,
	 * independent layer, on the same principle as text().
	 *
	 * @param string                $key        Setting key holding the icon.
	 * @param array<string, string> $attributes Extra attributes for the icon.
	 * @return void
	 */
	protected function render_picked_icon( string $key, array $attributes = array() ): void {
		$this->render_icon_value( $this->get_settings_for_display( $key ), $attributes );
	}

	/**
	 * Renders an icon from a value rather than a settings key.
	 *
	 * A repeater row is a plain array, not a setting, so its icon cannot be
	 * reached by key. Everything else routes through here too, so the legacy
	 * handling below applies uniformly.
	 *
	 * @param mixed                 $value      Icon control value.
	 * @param array<string, string> $attributes Extra attributes for the icon.
	 * @return void
	 */
	protected function render_icon_value( $value, array $attributes = array() ): void {
		$icon = self::icon_value( $value );

		if ( array() === $icon || ! class_exists( '\\Elementor\\Icons_Manager' ) ) {
			return;
		}

		$html = \Elementor\Icons_Manager::try_get_icon_html(
			$icon,
			array_merge( array( 'aria-hidden' => 'true' ), $attributes )
		);

		if ( ! is_string( $html ) || '' === $html ) {
			return;
		}

		echo wp_kses( $html, self::allowed_icon_html() );
	}

	/**
	 * Whether an icon control holds anything to render.
	 *
	 * @param string $key Setting key.
	 * @return bool
	 */
	protected function has_picked_icon( string $key ): bool {
		return array() !== self::icon_value( $this->get_settings_for_display( $key ) );
	}

	/**
	 * Whether an icon value holds anything to render.
	 *
	 * @param mixed $value Icon control value.
	 * @return bool
	 */
	protected function has_icon_value( $value ): bool {
		return array() !== self::icon_value( $value );
	}

	/**
	 * Normalises whatever is stored against an icon control.
	 *
	 * These controls were selects of theme icon slugs before they were icon
	 * pickers, so a page saved by an earlier version has a bare string where
	 * Elementor now expects `value` and `library`. A string is exactly the
	 * slug the Pixelomatic Icons library is keyed by, so it converts cleanly and an
	 * existing page keeps the icon it was built with — without a migration
	 * step, and without a second control kept around to hold the old value.
	 *
	 * @param mixed $value Stored value.
	 * @return array<string, mixed> Elementor icon, or an empty array.
	 */
	private static function icon_value( $value ): array {
		if ( is_array( $value ) ) {
			return empty( $value['value'] ) ? array() : $value;
		}

		if ( is_string( $value ) && '' !== $value ) {
			return self::design_icon( $value );
		}

		return array();
	}

	/**
	 * The stored value for one icon from the design's set.
	 *
	 * Widgets default their icon controls to a design icon, and Elementor
	 * stores those as the pair of classes it would otherwise have printed.
	 * That format belongs to Icon_Library, which is the only place it is
	 * spelled out.
	 *
	 * @param string $slug Icon slug in the theme's map.
	 * @return array{value: string, library: string}
	 */
	protected static function design_icon( string $slug ): array {
		return array(
			'value'   => Icon_Library::class_names( $slug ),
			'library' => Icon_Library::LIBRARY,
		);
	}

	/**
	 * The wp_kses allow-list icon markup is printed through.
	 *
	 * Deliberately narrow, and the same shape as the theme's: geometry and
	 * presentation only. No script, no style, no href, no foreignObject, no
	 * event attributes — so nothing that survives this can execute.
	 *
	 * @return array<string, array<string, bool>>
	 */
	private static function allowed_icon_html(): array {
		$shape = array(
			'fill'             => true,
			'fill-rule'        => true,
			'clip-rule'        => true,
			'opacity'          => true,
			'stroke'           => true,
			'stroke-width'     => true,
			'stroke-linecap'   => true,
			'stroke-linejoin'  => true,
			'stroke-dasharray' => true,
			'transform'        => true,
			'class'            => true,
		);

		$attributes = static function ( array $extra ) use ( $shape ): array {
			return array_merge( $shape, array_fill_keys( $extra, true ) );
		};

		return array(
			'svg'      => $attributes(
				array(
					'width',
					'height',
					'viewbox',
					'xmlns',
					'xmlns:xlink',
					'role',
					'aria-hidden',
					'aria-label',
					'aria-labelledby',
					'focusable',
					'preserveaspectratio',
				)
			),
			'g'        => $attributes( array() ),
			'title'    => array( 'id' => true ),
			'desc'     => array( 'id' => true ),
			'defs'     => array(),
			'path'     => $attributes( array( 'd' ) ),
			'circle'   => $attributes( array( 'cx', 'cy', 'r' ) ),
			'ellipse'  => $attributes( array( 'cx', 'cy', 'rx', 'ry' ) ),
			'rect'     => $attributes( array( 'x', 'y', 'width', 'height', 'rx', 'ry' ) ),
			'line'     => $attributes( array( 'x1', 'y1', 'x2', 'y2' ) ),
			'polyline' => $attributes( array( 'points' ) ),
			'polygon'  => $attributes( array( 'points' ) ),
			'i'        => array(
				'class'       => true,
				'aria-hidden' => true,
				'aria-label'  => true,
			),
			'span'     => array(
				'class'       => true,
				'aria-hidden' => true,
				'aria-label'  => true,
			),
		);
	}

	/**
	 * The icon slugs available to a picker.
	 *
	 * @return array<string, string>
	 */
	protected static function icon_options(): array {
		$slugs = class_exists( '\\Pixelomatic\\Frontend\\Icons' )
			? \Pixelomatic\Frontend\Icons::slugs()
			: array( 'check', 'star', 'shield', 'refresh', 'file-text', 'bolt', 'tag', 'cart' );

		$options = array();

		foreach ( $slugs as $slug ) {
			$options[ $slug ] = ucwords( str_replace( '-', ' ', $slug ) );
		}

		return $options;
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
