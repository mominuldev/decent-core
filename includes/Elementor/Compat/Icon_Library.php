<?php
/**
 * The design's icon set, as an Elementor icon library.
 *
 * The theme draws its icons from one server-side map of SVG path data — no
 * font, no sprite, no uploaded file. Elementor's Icons control knows about
 * Font Awesome and about SVGs a user uploads, and nothing else, so a widget
 * that wanted a design icon previously had to offer its own SELECT beside the
 * picker every other widget uses.
 *
 * This registers the map as a fourth tab in that picker. An editor gets the
 * design's icons where they expect to find icons, and every widget — ours,
 * Elementor's own Icon and Icon Box, a third party's — can use them.
 *
 * Three pieces make that work:
 *
 *   `icons`           the slug list, passed inline. Elementor's picker takes
 *                     either an inline list or a `fetchJson` URL, and inline
 *                     costs no request and cannot go stale against the map.
 *   `url`             a generated stylesheet, so the picker grid and the
 *                     panel's preview thumbnail can *draw* each icon. Editor
 *                     only — see stylesheet_url().
 *   `render_callback` what the front end actually prints. Elementor hands the
 *                     whole render over to us, so a design icon on a page is
 *                     the theme's own inline SVG, through the theme's own
 *                     wp_kses allow-list — the same markup the theme would
 *                     have printed itself, and no icon font request.
 *
 * @package PixelomaticCore
 */

namespace PixelomaticCore\Elementor\Compat;

defined( 'ABSPATH' ) || exit;

use PixelomaticCore\Assets\Filesystem;
use Pixelomatic\Frontend\Icons;

/**
 * Adds the theme's icon map to Elementor's icon picker.
 */
final class Icon_Library {

	/**
	 * The library key Elementor stores against each icon.
	 *
	 * Stable: changing it would orphan every icon already picked.
	 */
	public const LIBRARY = 'pixelomatic-icons';

	/**
	 * Class prefix for a single icon.
	 */
	private const PREFIX = 'pixelomatic-icon-';

	/**
	 * Base class every icon in the set carries.
	 *
	 * Set explicitly because Elementor's fallback — the prefix with its first
	 * hyphen removed — would produce `pixelomaticicon-` here.
	 */
	private const DISPLAY_PREFIX = 'pixelomatic-icon';

	/**
	 * Filesystem access for the generated stylesheet.
	 *
	 * @var Filesystem
	 */
	private $files;

	/**
	 * Constructor.
	 *
	 * @param Filesystem|null $files Filesystem, or a fresh one.
	 */
	public function __construct( ?Filesystem $files = null ) {
		$this->files = $files ?? new Filesystem();
	}

	/**
	 * Attaches hooks.
	 *
	 * @return void
	 */
	public function register(): void {
		add_filter( 'elementor/icons_manager/additional_tabs', array( $this, 'add_tab' ) );
	}

	/**
	 * Adds the tab.
	 *
	 * Silently does nothing without the theme: the map is the theme's, and a
	 * tab of icons that cannot be drawn is worse than no tab.
	 *
	 * @param array<string, array<string, mixed>> $tabs Registered tabs.
	 * @return array<string, array<string, mixed>>
	 */
	public function add_tab( $tabs ): array {
		$tabs  = (array) $tabs;
		$icons = self::slugs();

		if ( empty( $icons ) ) {
			return $tabs;
		}

		$tab = array(
			'name'            => self::LIBRARY,
			'label'           => __( 'Pixelomatic Icons', 'pixelomatic-core' ),
			'prefix'          => self::PREFIX,
			'displayPrefix'   => self::DISPLAY_PREFIX,
			'labelIcon'       => 'eicon-elementor-square',
			'ver'             => PIXELOMATIC_CORE_VERSION,
			'icons'           => $icons,
			'native'          => false,
			'render_callback' => array( __CLASS__, 'render' ),
		);

		// `url` only in the admin, and this is load-bearing rather than an
		// optimisation. Elementor collects the icon libraries a document uses
		// from its saved element data and enqueues the stylesheet of every one
		// that declares a `url` — it never consults render_callback first. So
		// a `url` here would put the picker's stylesheet on the front end,
		// where its `.pixelomatic-icon` rule (a mask over a currentColor fill, for
		// drawing an icon in a grid cell that has no SVG in it) would land on
		// the real inline SVG this library renders and mask it out of
		// existence. The editor needs the file; the front end must not see it.
		$url = $this->stylesheet_url( $icons );

		if ( '' !== $url ) {
			$tab['url'] = $url;
		}

		$tabs[ self::LIBRARY ] = $tab;

		return $tabs;
	}

	/**
	 * Renders one picked icon.
	 *
	 * Elementor calls this instead of printing `<i class="…">`, so a design
	 * icon on the front end is inline SVG and the generated stylesheet is
	 * never needed outside the editor.
	 *
	 * The slug is looked up in the theme's map and rendered only if it is
	 * already a key there, so nothing a stored value contains can reach the
	 * output — the same guarantee the theme's own icon API makes.
	 *
	 * @param array<string, mixed> $icon       Icon, as `value` and `library`.
	 * @param array<string, mixed> $attributes Attributes Elementor asked for.
	 * @param string               $tag        Tag Elementor would have used.
	 * @return string
	 */
	public static function render( $icon, $attributes = array(), $tag = 'i' ): string {
		unset( $tag );

		$slug = self::slug( (string) ( ( (array) $icon )['value'] ?? '' ) );

		if ( '' === $slug || ! class_exists( Icons::class ) || ! Icons::has( $slug ) ) {
			return '';
		}

		// Only what the caller asked for. The `pixelomatic-icon` / `pixelomatic-icon-<slug>`
		// pair Elementor stores is a *picker* class pair: it draws an icon as a
		// mask over a coloured box, for the grid cells and the panel thumbnail,
		// which contain no SVG. Stamping it on a real inline SVG means the
		// stylesheet masks that SVG instead of standing in for it.
		$classes = (array) ( $attributes['class'] ?? array() );

		return Icons::get(
			$slug,
			array(
				'class' => implode( ' ', array_filter( array_map( 'sanitize_html_class', $classes ) ) ),
				'label' => (string) ( $attributes['aria-label'] ?? '' ),
			)
		);
	}

	/**
	 * The stored `value` for one icon in this set.
	 *
	 * Elementor stores the classes it would have printed, so a widget's
	 * default icon has to be written in that format. This is the one place
	 * that format is spelled out.
	 *
	 * @param string $slug Icon slug.
	 * @return string
	 */
	public static function class_names( string $slug ): string {
		return self::DISPLAY_PREFIX . ' ' . self::PREFIX . $slug;
	}

	/**
	 * Extracts the slug from a stored control value.
	 *
	 * Elementor stores the pair of classes it would have printed —
	 * "pixelomatic-icon pixelomatic-icon-shield-check" — so the slug is whichever token
	 * carries the per-icon prefix.
	 *
	 * @param string $value Stored value.
	 * @return string Slug, or an empty string.
	 */
	private static function slug( string $value ): string {
		$tokens = preg_split( '/\s+/', trim( $value ) );

		foreach ( is_array( $tokens ) ? $tokens : array() as $token ) {
			if ( 0 === strpos( $token, self::PREFIX ) ) {
				return substr( $token, strlen( self::PREFIX ) );
			}
		}

		return '';
	}

	/**
	 * Every slug in the theme's map.
	 *
	 * @return string[]
	 */
	private static function slugs(): array {
		if ( ! class_exists( Icons::class ) ) {
			return array();
		}

		return array_values( array_filter( Icons::slugs(), 'is_string' ) );
	}

	/**
	 * The URL of the picker stylesheet, or an empty string outside the admin.
	 *
	 * The name is a hash of the file's own contents, not of the slug list:
	 * everything that decides what an icon looks like has to be in that hash,
	 * and that includes how this class draws it. A hash over the slugs alone
	 * keeps serving yesterday's file after the generator is fixed, which is a
	 * bug that only shows up as "the icons stopped rendering" long after the
	 * change that caused it.
	 *
	 * Building the CSS to hash it is why this is gated on is_admin() as well:
	 * the front end has no use for the file, and no reason to pay for it.
	 *
	 * If the sweeper removes it, or uploads is read-only, the next editor load
	 * simply writes it again — and if it cannot, the picker falls back to
	 * labelled but undrawn cells rather than breaking.
	 *
	 * @param string[] $icons Slugs.
	 * @return string
	 */
	private function stylesheet_url( array $icons ): string {
		if ( ! is_admin() ) {
			return '';
		}

		$css  = self::stylesheet( $icons );
		$hash = substr( md5( $css ), 0, 12 );

		if ( ! $this->files->exists( $hash, 'css' ) ) {
			$this->files->write( $hash, 'css', $css );
		}

		return $this->files->url( $hash, 'css' );
	}

	/**
	 * Builds the picker stylesheet.
	 *
	 * Each icon is a mask rather than a background, so it takes the colour of
	 * whatever it sits in — the picker draws it dark on white and Elementor's
	 * "currently selected" preview draws it on a tinted chip, and one rule
	 * covers both. Masking also keeps the theme's line icons as line icons: a
	 * background-image of a stroked, unfilled SVG would be invisible.
	 *
	 * @param string[] $icons Slugs.
	 * @return string
	 */
	private static function stylesheet( array $icons ): string {
		$css = '.' . self::DISPLAY_PREFIX . '{display:inline-block;width:1em;height:1em;'
			. 'background-color:currentColor;'
			. '-webkit-mask-repeat:no-repeat;mask-repeat:no-repeat;'
			. '-webkit-mask-position:center;mask-position:center;'
			. '-webkit-mask-size:contain;mask-size:contain}';

		foreach ( $icons as $slug ) {
			$svg = Icons::get( $slug, array( 'size' => 24 ) );

			if ( '' === $svg ) {
				continue;
			}

			// Two edits, both because the SVG is about to stop being inline
			// markup and become a standalone image resource.
			//
			// The namespace is optional inside an HTML document and required
			// in a document of its own: without it the data URI is not parsed
			// as SVG at all and the mask comes out empty, which shows up as a
			// picker full of blank cells.
			//
			// currentColor has nothing to inherit from once decoded, so the
			// stroke is painted black. A mask reads alpha rather than colour,
			// so black here means only "opaque"; the icon still takes its
			// colour from the element, which is what the currentColor
			// background in the base rule above is for.
			$svg = str_replace(
				'<svg ',
				'<svg xmlns="http://www.w3.org/2000/svg" ',
				str_replace( 'currentColor', '#000', $svg )
			);

			$uri = 'data:image/svg+xml;charset=utf-8,' . rawurlencode( $svg );

			$css .= '.' . self::PREFIX . $slug . '{'
				. '-webkit-mask-image:url("' . $uri . '");'
				. 'mask-image:url("' . $uri . '")}';
		}

		return $css;
	}
}
