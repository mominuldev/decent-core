<?php
/**
 * Table of Contents widget.
 *
 * @package PixelomaticCore
 */

namespace PixelomaticCore\Elementor\Widgets;

defined( 'ABSPATH' ) || exit;

use PixelomaticCore\Elementor\Base\Traits\Has_Style_Controls;
use PixelomaticCore\Elementor\Base\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Repeater;

/**
 * The contents rail beside a long document.
 *
 * Its own `pix-` markup rather than the theme's `.article-toc`. That component
 * belongs to the blog article layout, is built server-side out of the post
 * content by Pixelomatic\Frontend\Article::prepare(), and has no number
 * column. This rail is the design's numbered one, and the pages it sits beside
 * — terms, licence, privacy — are Elementor documents whose headings live in
 * other widgets rather than in post_content, so there is nothing on the server
 * to read.
 *
 * That is what the two sources are for:
 *
 *   headings  the list is built in the browser from the headings already on
 *             the page. The rail ships `hidden` and the script reveals it once
 *             it has something to show, which is the plugin's standing rule
 *             for a control only JavaScript can drive: a page with no matching
 *             heading renders nothing rather than an empty box.
 *   custom    real anchor links, written in the panel and printed by PHP. No
 *             script needed for the links to work — only for the marker.
 *
 * Either way the current entry is marked with `aria-current`, so the
 * accessible state and the styled state cannot drift apart.
 */
final class Table_Of_Contents extends Widget_Base {

	use Has_Style_Controls;

	/**
	 * The heading levels a list may be built from.
	 */
	private const LEVELS = array( 'h2', 'h3', 'h4', 'h5', 'h6' );

	/**
	 * Widget slug.
	 *
	 * @return string
	 */
	public static function slug(): string {
		return 'table-of-contents';
	}

	/**
	 * Registers controls.
	 *
	 * @return void
	 */
	protected function register_controls(): void {
		$this->start_controls_section( 'content', array( 'label' => __( 'Contents', 'pixelomatic-core' ) ) );

		$this->add_control(
			'label',
			array(
				'label'       => __( 'Label', 'pixelomatic-core' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Contents', 'pixelomatic-core' ),
				'label_block' => true,
				'description' => __( 'Leave empty to render the list on its own.', 'pixelomatic-core' ),
			)
		);

		$this->add_control(
			'source',
			array(
				'label'   => __( 'Build the list from', 'pixelomatic-core' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'headings',
				'options' => array(
					'headings' => __( 'The headings on this page', 'pixelomatic-core' ),
					'custom'   => __( 'A list I write', 'pixelomatic-core' ),
				),
			)
		);

		$this->register_notice(
			'toc_source',
			__( 'A list built from headings is built in the browser.', 'pixelomatic-core' ),
			array(
				__( 'The script finds the headings, gives each one an id and reveals the rail. Until it has found one the rail is hidden, so a page with no matching heading renders nothing rather than an empty box.', 'pixelomatic-core' ),
				__( 'Write the list yourself where the anchors have to be certain — sections whose ids you set, or a page this widget is not on.', 'pixelomatic-core' ),
			)
		);

		$this->add_control(
			'levels',
			array(
				'label'     => __( 'Heading levels', 'pixelomatic-core' ),
				'type'      => Controls_Manager::SELECT2,
				'multiple'  => true,
				'default'   => array( 'h2' ),
				'options'   => array(
					'h2' => __( 'H2', 'pixelomatic-core' ),
					'h3' => __( 'H3', 'pixelomatic-core' ),
					'h4' => __( 'H4', 'pixelomatic-core' ),
					'h5' => __( 'H5', 'pixelomatic-core' ),
					'h6' => __( 'H6', 'pixelomatic-core' ),
				),
				'condition' => array( 'source' => 'headings' ),
			)
		);

		$this->add_control(
			'scope',
			array(
				'label'       => __( 'Look inside', 'pixelomatic-core' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => '#content',
				'label_block' => true,
				'description' => __( 'A CSS selector. Leave empty to search the page\'s main content area.', 'pixelomatic-core' ),
				'condition'   => array( 'source' => 'headings' ),
			)
		);

		$repeater = new Repeater();

		$repeater->add_control(
			'label',
			array(
				'label'       => __( 'Label', 'pixelomatic-core' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Section', 'pixelomatic-core' ),
				'label_block' => true,
			)
		);

		$repeater->add_control(
			'anchor',
			array(
				'label'       => __( 'Anchor', 'pixelomatic-core' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
				'description' => __( 'The id of the section this links to, without the #.', 'pixelomatic-core' ),
			)
		);

		$this->add_control(
			'items',
			array(
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'title_field' => '{{{ label }}}',
				'condition'   => array( 'source' => 'custom' ),
				'default'     => array(
					array(
						'label'  => __( 'Who we are', 'pixelomatic-core' ),
						'anchor' => 'who-we-are',
					),
					array(
						'label'  => __( 'Your account', 'pixelomatic-core' ),
						'anchor' => 'your-account',
					),
					array(
						'label'  => __( 'Orders, prices and tax', 'pixelomatic-core' ),
						'anchor' => 'orders-prices-and-tax',
					),
				),
			)
		);

		$this->add_control(
			'numbers',
			array(
				'label'        => __( 'Number the entries', 'pixelomatic-core' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
				'separator'    => 'before',
			)
		);

		// Applied by the script, to the headings rather than to the rail: a
		// jump lands the heading at the very top of the viewport, which is
		// underneath a sticky header. Ships empty, so nothing is written to
		// another widget's element unless an editor asks for it.
		$this->add_control(
			'scroll_offset',
			array(
				'label'       => __( 'Scroll offset', 'pixelomatic-core' ),
				'type'        => Controls_Manager::SLIDER,
				'size_units'  => array( 'px' ),
				'range'       => array(
					'px' => array(
						'min'  => 0,
						'max'  => 240,
						'step' => 2,
					),
				),
				'description' => __( 'How far above the heading a jump stops — the height of a sticky header, usually.', 'pixelomatic-core' ),
			)
		);

		$this->end_controls_section();

		$this->start_style_section( 'style_rail', __( 'Rail', 'pixelomatic-core' ) );

		$this->register_box_style(
			'rail',
			__( 'Rail', 'pixelomatic-core' ),
			'{{WRAPPER}} .pix-toc',
			array(
				'separator' => 'none',
				'shadow'    => false,
			)
		);

		$this->register_gap_style( 'rail_gap', __( 'Space below the label', 'pixelomatic-core' ), '{{WRAPPER}} .pix-toc', 48 );

		// The stylesheet sticks the rail, as the theme's article rail is
		// stuck. This is how an editor unsticks it, and it ships empty like
		// every other style control — nothing is written until it is set.
		$this->add_responsive_control(
			'rail_position',
			array(
				'label'     => __( 'Position', 'pixelomatic-core' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => array(
					''       => __( 'Sticky (default)', 'pixelomatic-core' ),
					'static' => __( 'Scrolls with the page', 'pixelomatic-core' ),
				),
				'selectors' => array( '{{WRAPPER}} .pix-toc' => 'position: {{VALUE}};' ),
			)
		);

		$this->add_responsive_control(
			'rail_top',
			array(
				'label'      => __( 'Sticks below', 'pixelomatic-core' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min'  => 0,
						'max'  => 240,
						'step' => 2,
					),
				),
				'condition'  => array( 'rail_position' => '' ),
				'selectors'  => array( '{{WRAPPER}} .pix-toc' => 'top: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->end_controls_section();

		$this->start_style_section( 'style_label', __( 'Label', 'pixelomatic-core' ) );

		$this->register_text_style(
			'label',
			__( 'Label', 'pixelomatic-core' ),
			'{{WRAPPER}} .pix-toc__label',
			array(
				'heading' => false,
				'spacing' => false,
			)
		);

		$this->end_controls_section();

		$this->start_style_section( 'style_entries', __( 'Entries', 'pixelomatic-core' ) );

		$this->register_link_style(
			'entry',
			__( 'Entry', 'pixelomatic-core' ),
			'{{WRAPPER}} .pix-toc__text',
			array(
				'separator'      => 'none',
				'hover_selector' => '{{WRAPPER}} .pix-toc__link:hover .pix-toc__text, {{WRAPPER}} .pix-toc__link:focus-visible .pix-toc__text',
			)
		);

		$this->add_control(
			'entry_color_current',
			array(
				'label'     => __( 'Current entry colour', 'pixelomatic-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pix-toc__link[aria-current] .pix-toc__text' => 'color: {{VALUE}};',
				),
			)
		);

		$this->register_text_style(
			'number',
			__( 'Number', 'pixelomatic-core' ),
			'{{WRAPPER}} .pix-toc__number',
			array( 'spacing' => false )
		);

		$this->style_heading( 'bar', __( 'Bar', 'pixelomatic-core' ) );

		$this->add_control(
			'bar_color',
			array(
				'label'     => __( 'Colour', 'pixelomatic-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .pix-toc__link::before' => 'background: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'bar_color_current',
			array(
				'label'     => __( 'Current entry colour', 'pixelomatic-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .pix-toc__link[aria-current]::before' => 'background: {{VALUE}};' ),
			)
		);

		$this->add_responsive_control(
			'bar_height',
			array(
				'label'      => __( 'Height', 'pixelomatic-core' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min'  => 0,
						'max'  => 48,
						'step' => 1,
					),
				),
				'selectors'  => array( '{{WRAPPER}} .pix-toc__link::before' => 'height: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->style_heading( 'row', __( 'Row', 'pixelomatic-core' ) );

		$this->add_responsive_control(
			'row_padding',
			array(
				'label'      => __( 'Padding', 'pixelomatic-core' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .pix-toc__link' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->register_gap_style( 'row_gap', __( 'Gap inside a row', 'pixelomatic-core' ), '{{WRAPPER}} .pix-toc__link', 40 );

		$this->end_controls_section();
	}

	/**
	 * Renders the widget.
	 *
	 * @return void
	 */
	protected function render(): void {
		$custom  = 'custom' === (string) $this->get_settings_for_display( 'source' );
		$numbers = 'yes' === (string) $this->get_settings_for_display( 'numbers' );
		$label   = trim( $this->text( 'label' ) );
		$items   = $custom ? $this->items() : array();
		$offset  = $this->scroll_offset();
		$editing = self::is_editing();

		// A written list with nothing in it is an empty box on the page. A
		// list built from headings is empty until the script has run, which is
		// what `hidden` is for.
		if ( $custom && array() === $items ) {
			return;
		}

		$title = '' !== $label ? $label : __( 'Contents', 'pixelomatic-core' );
		?>
		<nav class="pix-toc" aria-label="<?php echo esc_attr( $title ); ?>" data-pix-toc
			<?php if ( ! $custom ) : ?>
				data-toc-levels="<?php echo esc_attr( implode( ',', $this->levels() ) ); ?>"
				<?php if ( '' !== $this->scope() ) : ?>
					data-toc-scope="<?php echo esc_attr( $this->scope() ); ?>"
				<?php endif; ?>
				<?php echo $numbers ? 'data-toc-numbers' : ''; ?>
				<?php echo $editing ? '' : 'hidden'; ?>
			<?php endif; ?>
			<?php if ( '' !== $offset ) : ?>
				data-toc-offset="<?php echo esc_attr( $offset ); ?>"
			<?php endif; ?>
		>
			<?php if ( '' !== $label ) : ?>
				<p class="pix-toc__label"><?php echo esc_html( $label ); ?></p>
			<?php endif; ?>

			<ul class="pix-toc__list" data-toc-list>
				<?php foreach ( $items as $index => $item ) : ?>
					<li class="pix-toc__item">
						<a class="pix-toc__link" href="#<?php echo esc_attr( $item['anchor'] ); ?>" <?php echo 0 === $index ? 'aria-current="true"' : ''; ?>>
							<?php if ( $numbers ) : ?>
								<span class="pix-toc__number" aria-hidden="true"><?php echo esc_html( (string) ( $index + 1 ) ); ?></span>
							<?php endif; ?>
							<span class="pix-toc__text"><?php echo esc_html( $item['label'] ); ?></span>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>

			<?php if ( ! $custom && $editing ) : ?>
				<p class="pix-toc__empty" data-toc-empty>
					<?php esc_html_e( 'No heading of the chosen level is on this page yet. The rail stays hidden until there is one.', 'pixelomatic-core' ); ?>
				</p>
			<?php endif; ?>
		</nav>
		<?php
	}

	/**
	 * The written list, as rows the markup can print.
	 *
	 * @return array<int, array{anchor:string, label:string}>
	 */
	private function items(): array {
		$rows  = (array) $this->get_settings_for_display( 'items' );
		$items = array();

		foreach ( $rows as $row ) {
			$row    = (array) $row;
			$label  = trim( (string) ( $row['label'] ?? '' ) );
			$anchor = trim( (string) ( $row['anchor'] ?? '' ) );

			if ( '' === $label || '' === $anchor ) {
				continue;
			}

			$items[] = array(
				'anchor' => ltrim( $anchor, '#' ),
				'label'  => $label,
			);
		}

		return $items;
	}

	/**
	 * The heading levels to look for, checked against the allow-list.
	 *
	 * The script checks them again — this is the layer that keeps anything but
	 * a heading tag out of the selector it builds.
	 *
	 * @return string[]
	 */
	private function levels(): array {
		$chosen = array_filter(
			array_map(
				static function ( $level ): string {
					return strtolower( trim( (string) $level ) );
				},
				(array) $this->get_settings_for_display( 'levels' )
			),
			static function ( string $level ): bool {
				return in_array( $level, self::LEVELS, true );
			}
		);

		return array() === $chosen ? array( 'h2' ) : array_values( array_unique( $chosen ) );
	}

	/**
	 * The selector the script searches inside, or an empty string.
	 *
	 * @return string
	 */
	private function scope(): string {
		return trim( (string) $this->get_settings_for_display( 'scope' ) );
	}

	/**
	 * The scroll offset in pixels, or an empty string when unset.
	 *
	 * @return string
	 */
	private function scroll_offset(): string {
		$value = $this->get_settings_for_display( 'scroll_offset' );
		$size  = is_array( $value ) ? ( $value['size'] ?? '' ) : '';

		return is_numeric( $size ) ? (string) absint( $size ) : '';
	}

	/**
	 * Whether the widget is being rendered inside the editor.
	 *
	 * @return bool
	 */
	private static function is_editing(): bool {
		if ( ! class_exists( '\\Elementor\\Plugin' ) ) {
			return false;
		}

		$editor = \Elementor\Plugin::instance()->editor ?? null;

		return $editor && $editor->is_edit_mode();
	}
}
