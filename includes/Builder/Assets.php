<?php
/**
 * Front-end assets for builder templates.
 *
 * @package PixelomaticCore
 */

namespace PixelomaticCore\Builder;

defined( 'ABSPATH' ) || exit;

use PixelomaticCore\Assets\Usage_Index;
use PixelomaticCore\Elementor\Widget_Registry;

/**
 * Loads what a resolved header or footer needs, in the head, before it renders.
 *
 * Timing is the whole point of this class. A builder header renders from
 * inside header.php, long after wp_head has printed — so anything it enqueues
 * for itself lands in the footer, and the visitor watches an unstyled header
 * for a frame. Resolving the template early and enqueueing on its behalf costs
 * one option lookup and one meta read, and it is the difference between a
 * header that appears and a header that appears twice.
 */
final class Assets {

	/**
	 * Style and script handle.
	 */
	public const HANDLE = 'pixelomatic-core-builder';

	/**
	 * Resolver.
	 *
	 * @var Resolver
	 */
	private $resolver;

	/**
	 * Widget usage index.
	 *
	 * @var Usage_Index
	 */
	private $index;

	/**
	 * Templates rendering on this request, as type => ID.
	 *
	 * @var array<string, int>|null
	 */
	private $templates = null;

	/**
	 * Constructor.
	 *
	 * @param Resolver    $resolver Resolver.
	 * @param Usage_Index $index    Widget usage index.
	 */
	public function __construct( Resolver $resolver, Usage_Index $index ) {
		$this->resolver = $resolver;
		$this->index    = $index;
	}

	/**
	 * Attaches hooks.
	 *
	 * @return void
	 */
	public function register(): void {
		// After Elementor registers its handles at 5, before the bundler
		// collapses ours at 20.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ), 10 );
		add_filter( 'pixelomatic_core/assets/request_slugs', array( $this, 'template_widgets' ) );
		add_filter( 'body_class', array( $this, 'body_class' ) );
	}

	/**
	 * Enqueues everything the resolved templates need.
	 *
	 * @return void
	 */
	public function enqueue(): void {
		$templates = $this->templates();

		if ( empty( $templates ) ) {
			return;
		}

		wp_enqueue_style(
			self::HANDLE,
			PIXELOMATIC_CORE_URL . 'assets/builder/style.css',
			array(),
			PIXELOMATIC_CORE_VERSION
		);

		foreach ( $templates as $template_id ) {
			$this->enqueue_template( (int) $template_id );
		}

		if ( $this->needs_script() ) {
			wp_enqueue_script(
				self::HANDLE,
				PIXELOMATIC_CORE_URL . 'assets/builder/script.js',
				array(),
				PIXELOMATIC_CORE_VERSION,
				true
			);
		}
	}

	/**
	 * Enqueues one template's own generated CSS and widget assets.
	 *
	 * @param int $template_id Template ID.
	 * @return void
	 */
	private function enqueue_template( int $template_id ): void {
		// The per-document stylesheet Elementor writes for the template. Left
		// to itself, get_builder_content_for_display() enqueues this from
		// inside the header, which is to say into the footer.
		if ( class_exists( '\Elementor\Core\Files\CSS\Post' ) ) {
			\Elementor\Core\Files\CSS\Post::create( $template_id )->enqueue();
		}

		$map = Widget_Registry::map();

		foreach ( $this->index->for_post( $template_id ) as $slug ) {
			foreach ( (array) ( $map[ $slug ]['styles'] ?? array() ) as $handle ) {
				wp_enqueue_style( 'pixelomatic-core-' . $handle );
			}

			foreach ( (array) ( $map[ $slug ]['scripts'] ?? array() ) as $handle ) {
				wp_enqueue_script( 'pixelomatic-core-' . $handle );
			}
		}
	}

	/**
	 * Adds the widgets a header or footer uses to the page's bundle.
	 *
	 * Without this the bundler sees only the queried post, and every widget in
	 * the header — the menu, the logo, the search — stays a request of its own
	 * on every page of the site.
	 *
	 * @param string[] $slugs Widget slugs on this request.
	 * @return string[]
	 */
	public function template_widgets( $slugs ): array {
		$slugs = (array) $slugs;

		foreach ( $this->templates() as $template_id ) {
			$slugs = array_merge( $slugs, $this->index->for_post( (int) $template_id ) );
		}

		return array_values( array_unique( array_map( 'strval', $slugs ) ) );
	}

	/**
	 * Adds a body class for the layout options that need one.
	 *
	 * A footer that holds the bottom of the viewport is a property of the page
	 * wrapper, not of the footer, so the class has to be on the body.
	 *
	 * @param string[] $classes Body classes.
	 * @return string[]
	 */
	public function body_class( $classes ): array {
		$classes   = (array) $classes;
		$templates = $this->templates();

		if ( isset( $templates[ Template_Type::HEADER ] ) ) {
			$classes[] = 'has-builder-header';

			$header = Display_Settings::for_template( $templates[ Template_Type::HEADER ], Template_Type::HEADER );

			if ( ! empty( $header['overlay'] ) ) {
				$classes[] = 'has-overlay-header';
			}
		}

		if ( isset( $templates[ Template_Type::FOOTER ] ) ) {
			$classes[] = 'has-builder-footer';

			$footer = Display_Settings::for_template( $templates[ Template_Type::FOOTER ], Template_Type::FOOTER );

			if ( ! empty( $footer['bottom'] ) ) {
				$classes[] = 'has-bottom-footer';
			}
		}

		return $classes;
	}

	/**
	 * Whether any resolved template has behaviour that needs the script.
	 *
	 * @return bool
	 */
	private function needs_script(): bool {
		$templates = $this->templates();

		if ( ! isset( $templates[ Template_Type::HEADER ] ) ) {
			return false;
		}

		return Display_Settings::needs_script(
			Display_Settings::for_template( $templates[ Template_Type::HEADER ], Template_Type::HEADER )
		);
	}

	/**
	 * The templates rendering on this request, resolved once.
	 *
	 * @return array<string, int>
	 */
	private function templates(): array {
		if ( null !== $this->templates ) {
			return $this->templates;
		}

		$this->templates = array();

		// A template previewing itself on the canvas is the template, not a
		// page that needs one.
		if ( is_singular( Post_Type::NAME ) ) {
			return $this->templates;
		}

		foreach ( array( Template_Type::HEADER, Template_Type::FOOTER ) as $type ) {
			$template_id = $this->resolver->resolve( $type );

			if ( $template_id ) {
				$this->templates[ $type ] = $template_id;
			}
		}

		return $this->templates;
	}
}
