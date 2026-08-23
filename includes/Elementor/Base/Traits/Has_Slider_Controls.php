<?php
/**
 * Swiper slider controls.
 *
 * @package DecentCore
 */

namespace DecentCore\Elementor\Base\Traits;

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;

/**
 * The control set every Swiper-driven widget shares.
 *
 * One trait rather than one copy per slider, for the same reason
 * Has_Grid_Controls exists: the moment a second widget needs a carousel, the
 * two panels have to agree on what "Slides to show" means and produce the same
 * Swiper config from it. A widget passes its own defaults in; it does not
 * restate the controls.
 *
 * Swiper itself is Elementor's — it registers 8.4.5 under the `swiper` script
 * and style handles, so a widget declaring those in config/widgets.php costs
 * no bytes of ours. Everything this trait emits is a Swiper option, spelled
 * the way Swiper spells it, so slider_settings() can be handed straight to
 * `new Swiper()` with nothing translating in between.
 */
trait Has_Slider_Controls {

	/**
	 * The BEM block the shared carousel chrome is named under.
	 *
	 * Fixed rather than per-widget: the arrows, the rail and the count are one
	 * component with one stylesheet and one script, and a widget that renamed
	 * them would need its own copy of both.
	 */
	private const SLIDER_BLOCK = 'pix-carousel';

	/**
	 * Registers the slider controls.
	 *
	 * @param array<string, mixed> $defaults Per-widget defaults, merged over
	 *                                       the ones below. A widget whose
	 *                                       design is a three-up row passes
	 *                                       slides_to_show => 3.
	 * @return void
	 */
	protected function register_slider_controls( array $defaults = array() ): void {
		$defaults = array_merge(
			array(
				'slides_to_show'        => '3',
				'slides_to_show_tablet' => '2',
				'slides_to_show_mobile' => '1',
				'space_between'         => 24,
				'space_between_mobile'  => 14,
				'speed'                 => 600,
				'loop'                  => '',
				'autoplay'              => '',
				'autoplay_delay'        => 6,
				'autoplay_interaction'  => '',
				'pause_on_mouse_enter'  => 'yes',
				'allow_touch_move'      => 'yes',
				'mousewheel'            => '',
				'enable_grid'           => '',
				'grid_rows'             => 1,
				'navigation'            => 'yes',
				'pagination'            => 'yes',
				'pagination_type'       => 'scrollbar',
				'direction'             => 'ltr',
			),
			$defaults
		);

		$this->add_responsive_control(
			'slides_to_show',
			array(
				'label'          => __( 'Slides to show', 'decent-core' ),
				'type'           => Controls_Manager::SELECT,
				'default'        => (string) $defaults['slides_to_show'],
				'tablet_default' => (string) $defaults['slides_to_show_tablet'],
				'mobile_default' => (string) $defaults['slides_to_show_mobile'],
				'options'        => array(
					'auto' => __( 'Auto', 'decent-core' ),
					'1'    => '1',
					'1.15' => __( '1 and a peek', 'decent-core' ),
					'2'    => '2',
					'2.15' => __( '2 and a peek', 'decent-core' ),
					'3'    => '3',
					'4'    => '4',
					'5'    => '5',
					'6'    => '6',
				),
				// A fractional count is how the design tells a visitor the row
				// scrolls: the sliver of the next card is the affordance. A
				// whole number on a phone produces a card that looks like the
				// only card.
				'description'    => __( '“And a peek” leaves part of the next slide showing, which is what tells a visitor the row scrolls.', 'decent-core' ),
			)
		);

		$this->add_responsive_control(
			'space_between',
			array(
				'label'          => __( 'Gap', 'decent-core' ),
				'type'           => Controls_Manager::SLIDER,
				'size_units'     => array( 'px' ),
				'default'        => array(
					'size' => (int) $defaults['space_between'],
					'unit' => 'px',
				),
				'mobile_default' => array(
					'size' => (int) $defaults['space_between_mobile'],
					'unit' => 'px',
				),
				'range'          => array(
					'px' => array(
						'min'  => 0,
						'max'  => 64,
						'step' => 2,
					),
				),
			)
		);

		$this->add_control(
			'speed',
			array(
				'label'       => __( 'Animation speed', 'decent-core' ),
				'type'        => Controls_Manager::NUMBER,
				'default'     => (int) $defaults['speed'],
				'min'         => 100,
				'max'         => 3000,
				'step'        => 50,
				'description' => __( 'Milliseconds per slide transition.', 'decent-core' ),
			)
		);

		$this->add_control(
			'loop',
			array(
				'label'   => __( 'Loop', 'decent-core' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => (string) $defaults['loop'],
			)
		);

		$this->add_control(
			'allow_touch_move',
			array(
				'label'   => __( 'Drag to scroll', 'decent-core' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => (string) $defaults['allow_touch_move'],
			)
		);

		$this->add_control(
			'mousewheel',
			array(
				'label'       => __( 'Mousewheel', 'decent-core' ),
				'type'        => Controls_Manager::SWITCHER,
				'default'     => (string) $defaults['mousewheel'],
				'description' => __( 'Lets the wheel move the slider. Turn Loop off if you use it — the two fight over the edges.', 'decent-core' ),
			)
		);

		$this->add_control(
			'autoplay',
			array(
				'label'     => __( 'Autoplay', 'decent-core' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => (string) $defaults['autoplay'],
				'separator' => 'before',
			)
		);

		$this->add_control(
			'autoplay_delay',
			array(
				'label'     => __( 'Seconds per slide', 'decent-core' ),
				'type'      => Controls_Manager::NUMBER,
				'default'   => (int) $defaults['autoplay_delay'],
				'min'       => 1,
				'max'       => 30,
				'condition' => array( 'autoplay' => 'yes' ),
			)
		);

		$this->add_control(
			'pause_on_mouse_enter',
			array(
				'label'     => __( 'Pause on hover', 'decent-core' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => (string) $defaults['pause_on_mouse_enter'],
				'condition' => array( 'autoplay' => 'yes' ),
			)
		);

		$this->add_control(
			'autoplay_interaction',
			array(
				'label'       => __( 'Stop after interaction', 'decent-core' ),
				'type'        => Controls_Manager::SWITCHER,
				'default'     => (string) $defaults['autoplay_interaction'],
				'description' => __( 'Leaves autoplay off for good once a visitor takes over.', 'decent-core' ),
				'condition'   => array( 'autoplay' => 'yes' ),
			)
		);

		$this->add_control(
			'enable_grid',
			array(
				'label'     => __( 'Multiple rows', 'decent-core' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => (string) $defaults['enable_grid'],
				'separator' => 'before',
			)
		);

		$this->add_control(
			'grid_rows',
			array(
				'label'     => __( 'Rows', 'decent-core' ),
				'type'      => Controls_Manager::NUMBER,
				'default'   => (int) $defaults['grid_rows'],
				'min'       => 1,
				'max'       => 4,
				'condition' => array( 'enable_grid' => 'yes' ),
			)
		);

		$this->add_control(
			'navigation',
			array(
				'label'     => __( 'Arrows', 'decent-core' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => (string) $defaults['navigation'],
				'separator' => 'before',
			)
		);

		// Left empty these fall back to the theme's own chevrons, which is the
		// design's arrow and costs no request. The picker is here for the
		// editor who wants something else, not as the normal path.
		$this->add_control(
			'navigation_previous_icon',
			array(
				'label'       => __( 'Previous icon', 'decent-core' ),
				'type'        => Controls_Manager::ICONS,
				'skin'        => 'inline',
				'label_block' => false,
				'condition'   => array( 'navigation' => 'yes' ),
			)
		);

		$this->add_control(
			'navigation_next_icon',
			array(
				'label'       => __( 'Next icon', 'decent-core' ),
				'type'        => Controls_Manager::ICONS,
				'skin'        => 'inline',
				'label_block' => false,
				'condition'   => array( 'navigation' => 'yes' ),
			)
		);

		$this->add_control(
			'pagination',
			array(
				'label'     => __( 'Pagination', 'decent-core' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => (string) $defaults['pagination'],
				'separator' => 'before',
			)
		);

		$this->add_control(
			'pagination_type',
			array(
				'label'     => __( 'Pagination type', 'decent-core' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => (string) $defaults['pagination_type'],
				'options'   => array(
					'scrollbar'   => __( 'Drag rail', 'decent-core' ),
					'bullets'     => __( 'Bullets', 'decent-core' ),
					'fraction'    => __( 'Fraction', 'decent-core' ),
					'progressbar' => __( 'Progress bar', 'decent-core' ),
				),
				'condition' => array( 'pagination' => 'yes' ),
			)
		);

		$this->add_control(
			'show_count',
			array(
				'label'       => __( 'Slide count', 'decent-core' ),
				'type'        => Controls_Manager::SWITCHER,
				'default'     => (string) $defaults['show_count'],
				'description' => __( 'The “Showing 1–3 of 6” line beside the pagination.', 'decent-core' ),
				'condition'   => array( 'pagination' => 'yes' ),
			)
		);

		$this->add_control(
			'direction',
			array(
				'label'       => __( 'Direction', 'decent-core' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => (string) $defaults['direction'],
				'options'     => array(
					'ltr' => __( 'Left to right', 'decent-core' ),
					'rtl' => __( 'Right to left', 'decent-core' ),
				),
				'description' => __( 'Which end the slider starts from. Independent of the site language.', 'decent-core' ),
				'separator'   => 'before',
			)
		);
	}

	/**
	 * Registers the carousel's Style-tab controls.
	 *
	 * Paired with register_slider_controls(): one call registers what the
	 * slider does, the other how its chrome looks. Every selector targets the
	 * shared block, so a widget gets the whole panel without naming a class.
	 *
	 * @return void
	 */
	protected function register_slider_style_controls(): void {
		$block = '{{WRAPPER}} .' . self::SLIDER_BLOCK;

		$this->add_responsive_control(
			'arrow_size',
			array(
				'label'      => __( 'Arrow button size', 'decent-core' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min'  => 32,
						'max'  => 72,
						'step' => 2,
					),
				),
				'selectors'  => array(
					$block . '__nav' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				),
				'condition'  => array( 'navigation' => 'yes' ),
			)
		);

		$this->add_control(
			'arrow_radius',
			array(
				'label'      => __( 'Arrow corner', 'decent-core' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%' ),
				'range'      => array(
					'px' => array(
						'min'  => 0,
						'max'  => 40,
						'step' => 1,
					),
					'%'  => array(
						'min' => 0,
						'max' => 50,
					),
				),
				'selectors'  => array(
					$block . '__nav' => 'border-radius: {{SIZE}}{{UNIT}};',
				),
				'condition'  => array( 'navigation' => 'yes' ),
			)
		);

		$this->start_controls_tabs( 'arrow_states', array( 'condition' => array( 'navigation' => 'yes' ) ) );

		$this->start_controls_tab( 'arrow_normal', array( 'label' => __( 'Normal', 'decent-core' ) ) );

		$this->add_control(
			'arrow_color',
			array(
				'label'     => __( 'Icon', 'decent-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( $block . '__nav' => 'color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'arrow_background',
			array(
				'label'     => __( 'Background', 'decent-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( $block . '__nav' => 'background: {{VALUE}}; border-color: {{VALUE}};' ),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab( 'arrow_hover', array( 'label' => __( 'Hover', 'decent-core' ) ) );

		// :not() the disabled modifier, or the hover colour repaints a button
		// that is telling the visitor there is nowhere left to go.
		$hover = $block . '__nav:hover:not(.' . self::SLIDER_BLOCK . '__nav--disabled)';

		$this->add_control(
			'arrow_hover_color',
			array(
				'label'     => __( 'Icon', 'decent-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( $hover => 'color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'arrow_hover_background',
			array(
				'label'     => __( 'Background', 'decent-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( $hover => 'background: {{VALUE}}; border-color: {{VALUE}};' ),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab( 'arrow_disabled', array( 'label' => __( 'At the end', 'decent-core' ) ) );

		$disabled = $block . '__nav--disabled';

		$this->add_control(
			'arrow_disabled_color',
			array(
				'label'     => __( 'Icon', 'decent-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( $disabled => 'color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'arrow_disabled_background',
			array(
				'label'     => __( 'Background', 'decent-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( $disabled => 'background: {{VALUE}};' ),
			)
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->add_control(
			'pagination_heading',
			array(
				'label'     => __( 'Pagination', 'decent-core' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => array( 'pagination' => 'yes' ),
			)
		);

		// The rail, the active bullet and the progress fill are the same idea
		// wearing three hats, so one colour drives all of them.
		$this->add_control(
			'pagination_color',
			array(
				'label'     => __( 'Indicator', 'decent-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					$block . '__rail .swiper-scrollbar-drag'                  => 'background: {{VALUE}};',
					$block . '__pagination .swiper-pagination-bullet-active'  => 'background: {{VALUE}};',
					$block . '__pagination .swiper-pagination-progressbar-fill' => 'background: {{VALUE}};',
				),
				'condition' => array( 'pagination' => 'yes' ),
			)
		);

		$this->add_control(
			'pagination_track_color',
			array(
				'label'     => __( 'Track', 'decent-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					$block . '__rail'                    => 'background: {{VALUE}};',
					$block . '__pagination .swiper-pagination-bullet' => 'background: {{VALUE}};',
					$block . '__pagination--progressbar' => 'background: {{VALUE}};',
				),
				'condition' => array( 'pagination' => 'yes' ),
			)
		);

		$this->register_text_style(
			'slider_count',
			__( 'Count', 'decent-core' ),
			$block . '__count',
			array( 'spacing' => false )
		);

		$this->add_responsive_control(
			'controls_gap',
			array(
				'label'      => __( 'Space above the controls', 'decent-core' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min'  => 0,
						'max'  => 80,
						'step' => 2,
					),
				),
				'selectors'  => array(
					$block . '__controls' => 'margin-top: {{SIZE}}{{UNIT}};',
				),
				'separator'  => 'before',
			)
		);
	}

	/**
	 * Builds the Swiper configuration.
	 *
	 * Every key here is Swiper's own, so the script hands the result straight
	 * to `new Swiper()`. Nothing translates in between, which means a Swiper
	 * option can be exposed by adding a control and nothing else.
	 *
	 * @param string $scope Selector prefix for elements Swiper has to find by
	 *                      string — the arrows, the pagination and the rail.
	 * @return array<string, mixed>
	 */
	protected function slider_settings( string $scope ): array {
		$settings = $this->get_settings_for_display();

		$config = array(
			'speed'          => max( 100, min( 3000, (int) ( $settings['speed'] ?? 600 ) ) ),
			'loop'           => 'yes' === ( $settings['loop'] ?? '' ),
			'allowTouchMove' => 'yes' === ( $settings['allow_touch_move'] ?? 'yes' ),
			'watchOverflow'  => true,
			'a11y'           => array( 'enabled' => true ),
		);

		// The base is the narrowest screen's values; everything wider arrives
		// through breakpoints below, because Swiper's are min-width.
		$per_view = $this->slider_breakpoint_values( 'slides_to_show' );
		$gap      = $this->slider_breakpoint_values( 'space_between' );

		$config['slidesPerView'] = $this->per_view_value( (string) array_shift( $per_view ) );
		$config['spaceBetween']  = (int) array_shift( $gap );

		$breakpoints = array();

		foreach ( $per_view as $width => $value ) {
			$breakpoints[ $width ]['slidesPerView'] = $this->per_view_value( (string) $value );
		}

		foreach ( $gap as $width => $value ) {
			$breakpoints[ $width ]['spaceBetween'] = (int) $value;
		}

		if ( ! empty( $breakpoints ) ) {
			ksort( $breakpoints );

			$config['breakpoints'] = $breakpoints;
		}

		if ( 'yes' === ( $settings['autoplay'] ?? '' ) ) {
			$config['autoplay'] = array(
				'delay'                => max( 1, min( 30, (int) ( $settings['autoplay_delay'] ?? 6 ) ) ) * 1000,
				'pauseOnMouseEnter'    => 'yes' === ( $settings['pause_on_mouse_enter'] ?? 'yes' ),
				'disableOnInteraction' => 'yes' === ( $settings['autoplay_interaction'] ?? '' ),
			);
		}

		if ( 'yes' === ( $settings['mousewheel'] ?? '' ) ) {
			$config['mousewheel'] = array( 'releaseOnEdges' => true );
		}

		if ( 'yes' === ( $settings['enable_grid'] ?? '' ) ) {
			$config['grid'] = array(
				'rows' => max( 1, min( 4, (int) ( $settings['grid_rows'] ?? 1 ) ) ),
				'fill' => 'row',
			);
		}

		if ( 'yes' === ( $settings['navigation'] ?? 'yes' ) ) {
			$config['navigation'] = array(
				'prevEl'        => $scope . ' [data-prev]',
				'nextEl'        => $scope . ' [data-next]',
				'disabledClass' => self::SLIDER_BLOCK . '__nav--disabled',
			);
		}

		if ( 'yes' === ( $settings['pagination'] ?? 'yes' ) ) {
			$type = (string) ( $settings['pagination_type'] ?? 'scrollbar' );

			// The drag rail is Swiper's scrollbar module, not its pagination
			// one. Both are "pagination" to an editor, so the control offers
			// them together and the split happens here.
			if ( 'scrollbar' === $type ) {
				$config['scrollbar'] = array(
					'el'        => $scope . ' [data-rail]',
					'draggable' => true,
				);
			} else {
				$config['pagination'] = array(
					'el'        => $scope . ' [data-pagination]',
					'type'      => $type,
					'clickable' => true,
				);
			}
		}

		return $config;
	}

	/**
	 * Maps a responsive control onto Swiper's min-width breakpoints.
	 *
	 * Elementor's breakpoints are max-width and Swiper's are min-width, so the
	 * two are not the same numbers and cannot be passed straight across. The
	 * narrowest Elementor breakpoint becomes Swiper's base, and every wider
	 * one becomes a `previous + 1` key — which is what makes a widget set to
	 * "2 on tablet" show 2 between the mobile and tablet ceilings rather than
	 * above the tablet one.
	 *
	 * Reading them from Elementor rather than hard-coding also means the kit
	 * stays the single source of truth. Compat\Breakpoints already syncs it to
	 * the theme's stylesheet, so all three agree by construction.
	 *
	 * @param string $key Control id.
	 * @return array<int|string, mixed> Base value first, then width => value.
	 */
	private function slider_breakpoint_values( string $key ): array {
		$settings = $this->get_settings_for_display();

		$read = static function ( string $suffix ) use ( $settings, $key ) {
			$value = $settings[ '' === $suffix ? $key : $key . '_' . $suffix ] ?? '';

			// A slider control is either a plain value or a SLIDER's array.
			if ( is_array( $value ) ) {
				$value = $value['size'] ?? '';
			}

			// ?? has already turned a null setting into '', so an empty string
			// is the only "not set" this can see.
			return '' === $value ? null : $value;
		};

		$points = $this->active_breakpoints();

		// Desktop is the control with no suffix, and applies above the widest
		// breakpoint. Every narrower step falls back to the next wider one, so
		// an unset tablet value behaves the way the panel says it does.
		$fallback = $read( '' );
		$values   = array( '' => $fallback );

		foreach ( array_reverse( array_keys( $points ) ) as $name ) {
			$fallback        = $read( $name ) ?? $fallback;
			$values[ $name ] = $fallback;
		}

		$names  = array_keys( $points );
		$result = array();

		// Narrowest breakpoint's value is Swiper's base.
		$narrowest = array_shift( $names );
		$result[]  = $values[ $narrowest ];
		$previous  = $points[ $narrowest ];

		foreach ( $names as $name ) {
			$result[ $previous + 1 ] = $values[ $name ];
			$previous                = $points[ $name ];
		}

		// Anything wider than the last breakpoint is the desktop value.
		$result[ $previous + 1 ] = $values[''];

		return $result;
	}

	/**
	 * Elementor's active breakpoints as name => max-width, narrowest first.
	 *
	 * @return array<string, int>
	 */
	private function active_breakpoints(): array {
		$points = array();

		if ( class_exists( '\Elementor\Plugin' ) ) {
			$manager = \Elementor\Plugin::instance()->breakpoints;

			if ( $manager ) {
				foreach ( $manager->get_active_breakpoints() as $name => $breakpoint ) {
					$points[ (string) $name ] = (int) $breakpoint->get_value();
				}
			}
		}

		// Without Elementor's manager, the theme's own two steps. Same numbers
		// Compat\Breakpoints writes into the kit, so the fallback and the real
		// thing cannot disagree.
		if ( empty( $points ) ) {
			$points = array(
				'mobile' => 560,
				'tablet' => 900,
			);
		}

		asort( $points );

		return $points;
	}

	/**
	 * Normalises a slides-to-show value for Swiper.
	 *
	 * @param string $value Control value.
	 * @return float|string
	 */
	private function per_view_value( string $value ) {
		return 'auto' === $value ? 'auto' : max( 1.0, min( 8.0, (float) $value ) );
	}

	/**
	 * Opens the carousel: wrapper, viewport and track.
	 *
	 * A widget calls this, echoes its slides, then calls render_slider_end().
	 * It never writes the shell itself — the shell is where the Swiper hooks
	 * live (`data-rail`, `data-prev`, the options JSON), and a widget that
	 * hand-rolled it would be a second copy of that contract to keep in step.
	 *
	 * @param array<string, string> $args {
	 *     Optional.
	 *
	 *     @type string $class Extra class on the wrapper, for a widget that
	 *                         needs to style its own carousel differently.
	 * }
	 * @return void
	 */
	protected function render_slider_start( array $args = array() ): void {
		$settings = $this->get_settings_for_display();
		$extra    = trim( (string) ( $args['class'] ?? '' ) );
		$rtl      = 'rtl' === ( $settings['direction'] ?? 'ltr' );

		// Swiper finds the arrows, the rail and the pagination by selector, so
		// they have to be addressable from outside this widget's markup. The
		// element class Elementor puts on the wrapper is the only id that is
		// unique per instance and present in both places.
		$scope = '.elementor-element-' . $this->get_id();
		?>
		<div class="<?php echo esc_attr( trim( self::SLIDER_BLOCK . ' ' . $extra ) ); ?>"
			data-pix-carousel
			data-options="<?php echo esc_attr( (string) wp_json_encode( $this->slider_settings( $scope ) ) ); ?>">

			<div class="swiper <?php echo esc_attr( self::SLIDER_BLOCK ); ?>__viewport"<?php echo $rtl ? ' dir="rtl"' : ''; ?>>
				<div class="swiper-wrapper <?php echo esc_attr( self::SLIDER_BLOCK ); ?>__track">
		<?php
	}

	/**
	 * Closes the carousel and renders its controls.
	 *
	 * The controls ship hidden and are revealed by the script that can drive
	 * them, so a page whose JavaScript never arrives shows a scrollable row
	 * rather than two buttons that do nothing.
	 *
	 * @param array<string, string> $args {
	 *     Optional.
	 *
	 *     @type string $prev_label Accessible name for the previous button.
	 *     @type string $next_label Accessible name for the next button.
	 *     @type bool   $count      Whether to print the "1–3 of 6" line.
	 * }
	 * @return void
	 */
	protected function render_slider_end( array $args = array() ): void {
		$settings   = $this->get_settings_for_display();
		$block      = self::SLIDER_BLOCK;
		$arrows     = 'yes' === ( $settings['navigation'] ?? 'yes' );
		$pagination = 'yes' === ( $settings['pagination'] ?? 'yes' );
		$type       = (string) ( $settings['pagination_type'] ?? 'scrollbar' );
		$count      = ( $args['count'] ?? true ) && 'yes' === ( $settings['show_count'] ?? 'yes' );

		$prev = (string) ( $args['prev_label'] ?? __( 'Previous', 'decent-core' ) );
		$next = (string) ( $args['next_label'] ?? __( 'Next', 'decent-core' ) );

		// Both forms of the count line. The script fills in the numbers and
		// picks between them; it never composes a sentence of its own, so the
		// strings stay here where they can be translated.
		/* translators: 1: first slide shown, 2: last slide shown, 3: total slides. */
		$count_long = __( 'Showing %1$s–%2$s of %3$s', 'decent-core' );
		/* translators: 1: slide shown, 2: unused in this form, 3: total slides. */
		$count_short = __( '%1$s of %3$s', 'decent-core' );
		?>
				</div>
			</div>

			<?php if ( $arrows || $pagination || $count ) : ?>
				<div class="<?php echo esc_attr( $block ); ?>__controls" data-controls hidden>
					<div class="<?php echo esc_attr( $block ); ?>__progress">
						<?php if ( $pagination && 'scrollbar' === $type ) : ?>
							<div class="swiper-scrollbar <?php echo esc_attr( $block ); ?>__rail" data-rail></div>
						<?php elseif ( $pagination ) : ?>
							<?php
							// One element, three looks: Swiper writes bullets, a
							// fraction or a progress bar into it depending on the
							// type it was given.
							?>
							<div class="swiper-pagination <?php echo esc_attr( $block ); ?>__pagination <?php echo esc_attr( $block ); ?>__pagination--<?php echo esc_attr( $type ); ?>" data-pagination></div>
						<?php endif; ?>

						<?php if ( $count ) : ?>
							<p class="<?php echo esc_attr( $block ); ?>__count"
								data-count
								aria-live="polite"
								data-template="<?php echo esc_attr( $count_long ); ?>"
								data-template-short="<?php echo esc_attr( $count_short ); ?>"></p>
						<?php endif; ?>
					</div>

					<?php if ( $arrows ) : ?>
						<div class="<?php echo esc_attr( $block ); ?>__arrows">
							<button type="button" class="<?php echo esc_attr( $block ); ?>__nav <?php echo esc_attr( $block ); ?>__nav--prev" data-prev aria-label="<?php echo esc_attr( $prev ); ?>">
								<?php $this->render_slider_arrow( 'navigation_previous_icon', 'chevron-left' ); ?>
							</button>
							<button type="button" class="<?php echo esc_attr( $block ); ?>__nav <?php echo esc_attr( $block ); ?>__nav--next" data-next aria-label="<?php echo esc_attr( $next ); ?>">
								<?php $this->render_slider_arrow( 'navigation_next_icon', 'chevron-right' ); ?>
							</button>
						</div>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Renders one arrow glyph.
	 *
	 * The picker wins when an editor has used it; otherwise the theme's own
	 * chevron, which is the design's arrow and costs no request.
	 *
	 * @param string $key      Icon control id.
	 * @param string $fallback Theme icon slug.
	 * @return void
	 */
	private function render_slider_arrow( string $key, string $fallback ): void {
		if ( $this->has_picked_icon( $key ) ) {
			$this->render_picked_icon( $key );
			return;
		}

		$this->icon( $fallback, 20, 1.8 );
	}
}
