<?php
/**
 * Per-template display settings.
 *
 * @package DecentCore
 */

namespace DecentCore\Builder;

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Core\Base\Document;

/**
 * The behaviour options a header or a footer template carries.
 *
 * Sticky and overlay belong to the template, not to the page it renders on: a
 * transparent header exists because that header was designed to sit over a
 * hero, and it should behave the same everywhere it is assigned. So they live
 * in Elementor's own document settings panel, beside the title, rather than in
 * a metabox the editor has to leave the canvas to reach.
 *
 * Registration and reading are deliberately different mechanisms. Controls are
 * registered against the Document, which only exists in the editor. Reading
 * happens on every front-end request, so it goes straight to the meta row
 * Elementor writes — building a settings model there would instantiate a whole
 * controls stack to answer six booleans.
 */
final class Display_Settings {

	/**
	 * Meta key Elementor stores document settings in.
	 */
	public const META = '_elementor_page_settings';

	/**
	 * Resolved settings, by template ID.
	 *
	 * @var array<int, array<string, mixed>>
	 */
	private static $cache = array();

	/**
	 * Attaches hooks.
	 *
	 * @return void
	 */
	public function register(): void {
		add_action( 'elementor/documents/register_controls', array( $this, 'register_controls' ) );
	}

	/**
	 * Adds the header or footer section to a template's document settings.
	 *
	 * The type is read from post meta, so switching a template's type in the
	 * metabox shows the other section on the next editor load rather than
	 * immediately. That is the cost of one source of truth for the type, and
	 * it is cheaper than two.
	 *
	 * @param Document $document Document being configured.
	 * @return void
	 */
	public function register_controls( $document ): void {
		if ( ! is_object( $document ) || ! method_exists( $document, 'get_main_id' ) ) {
			return;
		}

		$post_id = (int) $document->get_main_id();

		if ( Post_Type::NAME !== get_post_type( $post_id ) ) {
			return;
		}

		$type = Template_Type::of( $post_id );

		if ( Template_Type::HEADER === $type ) {
			$this->header_controls( $document );
			return;
		}

		if ( Template_Type::FOOTER === $type ) {
			$this->footer_controls( $document );
		}
	}

	/**
	 * Registers the header section.
	 *
	 * @param Document $document Document being configured.
	 * @return void
	 */
	private function header_controls( $document ): void {
		$document->start_controls_section(
			'decent_header_options',
			array(
				'label' => __( 'Header behaviour', 'decent-core' ),
				'tab'   => Controls_Manager::TAB_SETTINGS,
			)
		);

		$document->add_control(
			'decent_header_overlay',
			array(
				'label'       => __( 'Overlay the first section', 'decent-core' ),
				'type'        => Controls_Manager::SWITCHER,
				'description' => __( 'Lifts the header out of the flow so the page starts underneath it. For headers designed to sit over a hero.', 'decent-core' ),
			)
		);

		$document->add_control(
			'decent_header_sticky',
			array(
				'label' => __( 'Stick to the top', 'decent-core' ),
				'type'  => Controls_Manager::SWITCHER,
			)
		);

		$document->add_control(
			'decent_header_sticky_offset',
			array(
				'label'     => __( 'Offset (px)', 'decent-core' ),
				'type'      => Controls_Manager::NUMBER,
				'min'       => 0,
				'max'       => 400,
				'step'      => 1,
				'default'   => 0,
				'condition' => array( 'decent_header_sticky' => 'yes' ),
			)
		);

		$document->add_control(
			'decent_header_sticky_mobile',
			array(
				'label'     => __( 'Stick on mobile', 'decent-core' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'yes',
				'condition' => array( 'decent_header_sticky' => 'yes' ),
			)
		);

		$document->add_control(
			'decent_header_sticky_shadow',
			array(
				'label'     => __( 'Shadow once stuck', 'decent-core' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'yes',
				'condition' => array( 'decent_header_sticky' => 'yes' ),
			)
		);

		$document->add_control(
			'decent_header_sticky_hide',
			array(
				'label'       => __( 'Hide when scrolling down', 'decent-core' ),
				'type'        => Controls_Manager::SWITCHER,
				'description' => __( 'Slides the header away as the visitor scrolls down and brings it back on the way up.', 'decent-core' ),
				'condition'   => array( 'decent_header_sticky' => 'yes' ),
			)
		);

		$document->end_controls_section();
	}

	/**
	 * Registers the footer section.
	 *
	 * @param Document $document Document being configured.
	 * @return void
	 */
	private function footer_controls( $document ): void {
		$document->start_controls_section(
			'decent_footer_options',
			array(
				'label' => __( 'Footer behaviour', 'decent-core' ),
				'tab'   => Controls_Manager::TAB_SETTINGS,
			)
		);

		$document->add_control(
			'decent_footer_bottom',
			array(
				'label'       => __( 'Hold the bottom of the viewport', 'decent-core' ),
				'type'        => Controls_Manager::SWITCHER,
				'description' => __( 'Keeps a short page from leaving a strip of background under the footer. The footer still scrolls with a long one.', 'decent-core' ),
			)
		);

		$document->end_controls_section();
	}

	/**
	 * Returns a template's settings, defaults filled in.
	 *
	 * @param int    $template_id Template ID.
	 * @param string $type        Template type.
	 * @return array<string, mixed>
	 */
	public static function for_template( int $template_id, string $type ): array {
		if ( isset( self::$cache[ $template_id ] ) ) {
			return self::$cache[ $template_id ];
		}

		$stored = get_post_meta( $template_id, self::META, true );
		$stored = is_array( $stored ) ? $stored : array();

		if ( Template_Type::FOOTER === $type ) {
			$settings = array(
				'bottom' => self::flag( $stored, 'decent_footer_bottom' ),
			);
		} else {
			$sticky = self::flag( $stored, 'decent_header_sticky' );

			$settings = array(
				'overlay'       => self::flag( $stored, 'decent_header_overlay' ),
				'sticky'        => $sticky,
				'sticky_mobile' => $sticky && self::flag( $stored, 'decent_header_sticky_mobile', true ),
				'shadow'        => $sticky && self::flag( $stored, 'decent_header_sticky_shadow', true ),
				'hide'          => $sticky && self::flag( $stored, 'decent_header_sticky_hide' ),
				'offset'        => $sticky ? self::offset( $stored ) : 0,
			);
		}

		/**
		 * Filters a template's display settings.
		 *
		 * @since 1.0.0
		 *
		 * @param array<string, mixed> $settings    Normalised settings.
		 * @param int                  $template_id Template ID.
		 * @param string               $type        Template type.
		 */
		$settings = (array) apply_filters( 'decent_core/builder/settings', $settings, $template_id, $type );

		self::$cache[ $template_id ] = $settings;

		return $settings;
	}

	/**
	 * Whether a header's settings give the script anything to do.
	 *
	 * CSS sticks the header on its own. The script exists for the two states
	 * CSS cannot observe — stuck and hidden — so a header that renders neither
	 * a shadow, a hide-on-scroll nor an overlay needs no script at all, and
	 * the renderer leaves off the data attribute that would summon one.
	 *
	 * @param array<string, mixed> $settings Header settings.
	 * @return bool
	 */
	public static function needs_script( array $settings ): bool {
		if ( empty( $settings['sticky'] ) ) {
			return false;
		}

		return ! empty( $settings['shadow'] ) || ! empty( $settings['hide'] ) || ! empty( $settings['overlay'] );
	}

	/**
	 * Reads a switcher.
	 *
	 * A control that has never been touched is absent from the meta, which is
	 * why the default cannot simply be false for every one of them.
	 *
	 * @param array<string, mixed> $stored   Stored settings.
	 * @param string               $key      Control ID.
	 * @param bool                 $fallback Value to use when the key is absent.
	 * @return bool
	 */
	private static function flag( array $stored, string $key, bool $fallback = false ): bool {
		if ( ! array_key_exists( $key, $stored ) ) {
			return $fallback;
		}

		return 'yes' === $stored[ $key ];
	}

	/**
	 * Reads the sticky offset, clamped to the control's own range.
	 *
	 * @param array<string, mixed> $stored Stored settings.
	 * @return int
	 */
	private static function offset( array $stored ): int {
		$offset = isset( $stored['decent_header_sticky_offset'] ) ? (int) $stored['decent_header_sticky_offset'] : 0;

		return max( 0, min( 400, $offset ) );
	}
}
