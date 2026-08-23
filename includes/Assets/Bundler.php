<?php
/**
 * Asset bundler.
 *
 * Layer 2 of the asset strategy. Layer 1 — each widget declaring its handles
 * through get_style_depends() — is the guaranteed floor: it is exact, it needs
 * no invalidation, and it works whatever happens here. This layer collapses
 * that set into a single file to save requests, and steps aside the moment it
 * cannot.
 *
 * The mechanism is deliberately not a dequeue: when a bundle covers a widget's
 * handle, that handle is re-registered with no source of its own and a
 * dependency on the bundle. Elementor then enqueues it exactly as before, the
 * bundle comes along, and the handle prints nothing. A widget that renders
 * without being in the index — a shortcode, a template call — keeps its real
 * file and still works.
 *
 * @package PixelomaticCore
 */

namespace PixelomaticCore\Assets;

defined( 'ABSPATH' ) || exit;

use PixelomaticCore\Elementor\Widget_Registry;
use PixelomaticCore\Settings\Settings;

/**
 * Builds and enqueues combined widget assets.
 */
final class Bundler {

	/**
	 * Bundle handle prefix.
	 */
	private const HANDLE = 'pixelomatic-core-bundle';

	/**
	 * Filesystem access.
	 *
	 * @var Filesystem
	 */
	private $files;

	/**
	 * Usage index.
	 *
	 * @var Usage_Index
	 */
	private $index;

	/**
	 * Constructor.
	 *
	 * @param Filesystem  $files Filesystem.
	 * @param Usage_Index $index Usage index.
	 */
	public function __construct( Filesystem $files, Usage_Index $index ) {
		$this->files = $files;
		$this->index = $index;
	}

	/**
	 * Attaches hooks.
	 *
	 * @return void
	 */
	public function register(): void {
		// Late, so every widget handle Manager registered already exists.
		add_action( 'wp_enqueue_scripts', array( $this, 'maybe_bundle' ), 20 );
	}

	/**
	 * Builds and enqueues the bundle for this request, if appropriate.
	 *
	 * @return void
	 */
	public function maybe_bundle(): void {
		if ( ! $this->should_bundle() ) {
			return;
		}

		$slugs = $this->slugs_for_request();

		if ( count( $slugs ) < 2 ) {
			// One widget is already one request. Bundling it would only add a
			// second copy of the same bytes under a different name.
			return;
		}

		$this->bundle( 'css', $slugs );
		$this->bundle( 'js', $slugs );
	}

	/**
	 * Builds one bundle and rewires the handles it covers.
	 *
	 * @param string   $extension 'css' or 'js'.
	 * @param string[] $slugs     Widget slugs on this page.
	 * @return void
	 */
	private function bundle( string $extension, array $slugs ): void {
		$sources = $this->sources( $extension, $slugs );

		if ( count( $sources ) < 2 ) {
			return;
		}

		$hash = $this->hash( $extension, array_keys( $sources ) );

		if ( ! $this->files->exists( $hash, $extension ) && ! $this->write( $hash, $extension, $sources ) ) {
			// Could not write. Layer 1 stands; the page is correct, just with
			// more requests than it could have had.
			return;
		}

		$handle = self::HANDLE . '-' . $extension;
		$url    = $this->files->url( $hash, $extension );

		// The bundle inherits what its parts depended on. Without this the
		// concatenation quietly drops every dependency the handles declared —
		// a widget script that needs Swiper or elementor-frontend would load
		// before either of them, and only on pages carrying enough widgets to
		// trigger bundling, which is the hardest kind of bug to catch.
		$deps = $this->inherited_deps( $extension, array_keys( $sources ) );

		if ( 'css' === $extension ) {
			wp_enqueue_style( $handle, $url, $deps, null ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion -- Filename is content-hashed, so a version query would only defeat caching.
		} else {
			wp_enqueue_script( $handle, $url, $deps, null, true ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion -- As above.
		}

		$this->absorb( $extension, array_keys( $sources ), $handle );
	}

	/**
	 * Collects the dependencies of the handles a bundle is about to replace.
	 *
	 * Handles inside the bundle are dropped from the result: they are about to
	 * become aliases of it, and a bundle that depended on its own aliases
	 * would be a dependency cycle WordPress resolves by printing nothing.
	 *
	 * @param string   $extension 'css' or 'js'.
	 * @param string[] $handles   Handles the bundle contains.
	 * @return string[]
	 */
	private function inherited_deps( string $extension, array $handles ): array {
		$registry = 'css' === $extension ? wp_styles() : wp_scripts();
		$deps     = array();

		foreach ( $handles as $handle ) {
			$registered = $registry->registered[ $handle ] ?? null;

			if ( ! $registered ) {
				continue;
			}

			foreach ( (array) $registered->deps as $dep ) {
				if ( ! in_array( $dep, $handles, true ) ) {
					$deps[] = (string) $dep;
				}
			}
		}

		return array_values( array_unique( $deps ) );
	}

	/**
	 * Re-registers the covered handles as empty aliases of the bundle.
	 *
	 * @param string   $extension 'css' or 'js'.
	 * @param string[] $handles   Handles the bundle contains.
	 * @param string   $bundle    Bundle handle.
	 * @return void
	 */
	private function absorb( string $extension, array $handles, string $bundle ): void {
		foreach ( $handles as $handle ) {
			if ( 'css' === $extension ) {
				wp_deregister_style( $handle );
				wp_register_style( $handle, false, array( $bundle ), null ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion -- Alias with no file of its own.
			} else {
				wp_deregister_script( $handle );
				wp_register_script( $handle, false, array( $bundle ), null, true ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion -- As above.
			}
		}
	}

	/**
	 * Concatenates and writes a bundle.
	 *
	 * @param string                $hash      Bundle hash.
	 * @param string                $extension File extension.
	 * @param array<string, string> $sources   Handle => absolute path.
	 * @return bool
	 */
	private function write( string $hash, string $extension, array $sources ): bool {
		$parts = array();

		foreach ( $sources as $handle => $path ) {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Local plugin asset.
			$contents = file_get_contents( $path );

			if ( false === $contents ) {
				continue;
			}

			$parts[] = '/* ' . $handle . ' */';
			$parts[] = trim( $contents );
		}

		if ( empty( $parts ) ) {
			return false;
		}

		$joined = implode( "\n", $parts );

		if ( Settings::enabled( 'assets_minify' ) ) {
			$joined = Minifier::run( $joined, $extension );
		}

		return $this->files->write( $hash, $extension, $joined );
	}

	/**
	 * Resolves widget slugs to registered, existing asset files.
	 *
	 * @param string   $extension 'css' or 'js'.
	 * @param string[] $slugs     Widget slugs.
	 * @return array<string, string> Handle => absolute path, in a stable order.
	 */
	private function sources( string $extension, array $slugs ): array {
		$key   = 'css' === $extension ? 'styles' : 'scripts';
		$file  = 'css' === $extension ? 'style.css' : 'script.js';
		$map   = Widget_Registry::map();
		$found = array();

		foreach ( $slugs as $slug ) {
			foreach ( (array) ( $map[ $slug ][ '' . $key ] ?? array() ) as $asset ) {
				$path = PIXELOMATIC_CORE_DIR . 'assets/widgets/' . $asset . '/' . $file;

				if ( file_exists( $path ) ) {
					$found[ 'pixelomatic-core-' . $asset ] = $path;
				}
			}
		}

		ksort( $found );

		return $found;
	}

	/**
	 * Builds the content hash for a bundle.
	 *
	 * The plugin version is in the hash, so an update produces a new filename
	 * and there is no cache to purge. File mtimes join it only in development,
	 * where an edited widget must show up without a version bump.
	 *
	 * @param string   $extension File extension.
	 * @param string[] $handles   Handles in the bundle.
	 * @return string
	 */
	private function hash( string $extension, array $handles ): string {
		$parts = array( $extension, PIXELOMATIC_CORE_VERSION, (string) Settings::enabled( 'assets_minify' ) );
		$parts = array_merge( $parts, $handles );

		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			foreach ( $this->sources( $extension, $this->slugs_for_request() ) as $path ) {
				$parts[] = (string) filemtime( $path );
			}
		}

		return substr( sha1( implode( '|', $parts ) ), 0, 20 );
	}

	/**
	 * The widget slugs rendered on this request.
	 *
	 * @return string[]
	 */
	private function slugs_for_request(): array {
		$post_id = (int) get_queried_object_id();
		$slugs   = ( $post_id && is_singular() ) ? $this->index->for_post( $post_id ) : array();

		/**
		 * Filters the widget slugs the bundle is built from.
		 *
		 * The queried post is not the only thing on the page. The header and
		 * footer builder adds the widgets its resolved templates use, which is
		 * what keeps a menu in the header from being a request of its own on
		 * every page of the site.
		 *
		 * @since 1.0.0
		 *
		 * @param string[] $slugs   Widget slugs.
		 * @param int      $post_id Queried post ID, or 0.
		 */
		$slugs = (array) apply_filters( 'pixelomatic_core/assets/request_slugs', $slugs, $post_id );

		return array_values( array_unique( array_map( 'strval', $slugs ) ) );
	}

	/**
	 * Whether bundling applies to this request.
	 *
	 * @return bool
	 */
	private function should_bundle(): bool {
		if ( ! Settings::enabled( 'assets_bundling' ) || ! $this->files->is_writable() ) {
			return false;
		}

		// In the editor and its preview frame every widget asset is loaded up
		// front, because a widget must be styled the moment it is dropped —
		// before any index knows it is on the page.
		if ( class_exists( '\Elementor\Plugin' ) ) {
			$elementor = \Elementor\Plugin::instance();

			if ( isset( $elementor->preview ) && method_exists( $elementor->preview, 'is_preview_mode' ) && $elementor->preview->is_preview_mode() ) {
				return false;
			}

			if ( isset( $elementor->editor ) && method_exists( $elementor->editor, 'is_edit_mode' ) && $elementor->editor->is_edit_mode() ) {
				return false;
			}
		}

		/**
		 * Filters whether the bundle is used on this request.
		 *
		 * @since 1.0.0
		 *
		 * @param bool $bundle Whether to bundle.
		 */
		return (bool) apply_filters( 'pixelomatic_core/assets/should_bundle', true );
	}
}
