<?php
/**
 * Why Buy Here widget.
 *
 * The landing's trust band, from the Pixelomatic `Landing — Desktop 1440`
 * board: six tiles on a dark ground, laid out four columns wide so that two of
 * them can span half the row. The asymmetry is the point — the design gives the
 * two claims it can prove (a release history, a release checklist) the room to
 * show the proof, and lets the two figures beside them read as figures rather
 * than as more paragraphs.
 *
 * A tile is one of two shapes. A `feature` leads with a title; a `stat` leads
 * with a figure and its label. Either can carry a detail block underneath —
 * a release list, a two-up checklist or a row of chips — parsed from one
 * textarea, a line per row, pipes between the columns. That is the same
 * line-based convention the theme parses its product-page meta with
 * (`Integrations\EDD\Product_Content`), and it is here for the same reason: a
 * nested repeater is not a control Elementor has, and three more repeaters
 * would be three more panels for what is a short list of short strings.
 *
 * `.section`, `.section--dark`, `.container`, `.section__inner`,
 * `.pix-section-heading`, `.eyebrow`, `.section-title`, `.section-intro` and
 * `.link-arrow` are the theme's. Everything named `pix-why__*` is this
 * widget's, and is built from the theme's tokens rather than from its classes,
 * because the theme has no component for a tile of this shape.
 *
 * @package PixelomaticCore
 */

namespace PixelomaticCore\Elementor\Widgets;

defined( 'ABSPATH' ) || exit;

use PixelomaticCore\Elementor\Base\Traits\Has_Section_Head;
use PixelomaticCore\Elementor\Base\Traits\Has_Style_Controls;
use PixelomaticCore\Elementor\Base\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Repeater;

/**
 * The six-tile trust band.
 */
final class Why_Buy extends Widget_Base {

	use Has_Section_Head;
	use Has_Style_Controls;

	/**
	 * Widget slug.
	 *
	 * @return string
	 */
	public static function slug(): string {
		return 'why-buy';
	}

	/**
	 * Registers the panel controls.
	 *
	 * @return void
	 */
	protected function register_controls(): void {
		$this->register_head_controls();
		$this->register_tile_controls();
		$this->register_style_controls();
	}

	/**
	 * The section head, and the licence link that sits opposite it.
	 *
	 * @return void
	 */
	private function register_head_controls(): void {
		$this->start_controls_section( 'head', array( 'label' => __( 'Section head', 'pixelomatic-core' ) ) );

		$this->register_section_head_controls(
			__( 'Buy once. Ship with confidence.', 'pixelomatic-core' ),
			__( 'Why buy here', 'pixelomatic-core' )
		);

		// The trait defaults a title and an eyebrow but not an intro, because
		// most sections that use it do not have one. This one does, and it is
		// the sentence that turns six tiles into a set rather than a list.
		$this->update_control(
			'intro',
			array(
				'default' => __( 'No marketplace queue and no third-party seller between you and the code. Every product here is ours — so all six promises below cover all of them.', 'pixelomatic-core' ),
			)
		);

		// Label only. The trailing link renders once it has a URL, and the URL
		// is the store's own licence page — a default pointing at nothing would
		// ship a dead control on every site that installs this.
		$this->register_section_head_link_controls( __( 'Read the licence', 'pixelomatic-core' ) );

		$this->end_controls_section();
	}

	/**
	 * The tiles.
	 *
	 * @return void
	 */
	private function register_tile_controls(): void {
		// Not 'tiles': a section and the controls inside it share one stack, so
		// the repeater below cannot take the name its own section already has.
		$this->start_controls_section( 'tiles_section', array( 'label' => __( 'Tiles', 'pixelomatic-core' ) ) );

		$this->add_control(
			'tile_tag',
			array(
				'label'       => __( 'Tile title tag', 'pixelomatic-core' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => 'h3',
				'description' => __( 'Every tile sits under the section’s own heading, so it is normally one level below it.', 'pixelomatic-core' ),
				'options'     => array(
					'h3'   => 'H3',
					'h4'   => 'H4',
					'h5'   => 'H5',
					'p'    => __( 'Paragraph', 'pixelomatic-core' ),
					'span' => __( 'Span', 'pixelomatic-core' ),
				),
			)
		);

		$this->add_control( 'tiles', $this->tiles_control() );

		$this->end_controls_section();
	}

	/**
	 * The repeater definition, with the design's own six tiles as its default.
	 *
	 * @return array<string, mixed>
	 */
	private function tiles_control(): array {
		$repeater = new Repeater();

		$repeater->add_control(
			'layout',
			array(
				'label'   => __( 'Leads with', 'pixelomatic-core' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'feature',
				'options' => array(
					'feature' => __( 'A title', 'pixelomatic-core' ),
					'stat'    => __( 'A figure', 'pixelomatic-core' ),
				),
			)
		);

		$repeater->add_control(
			'wide',
			array(
				'label'       => __( 'Double width', 'pixelomatic-core' ),
				'type'        => Controls_Manager::SWITCHER,
				'description' => __( 'Spans two of the four columns. The design uses it for the two tiles that carry a list.', 'pixelomatic-core' ),
			)
		);

		$repeater->add_control(
			'accent',
			array(
				'label'       => __( 'Brand fill', 'pixelomatic-core' ),
				'type'        => Controls_Manager::SWITCHER,
				'description' => __( 'The warmer, blue-washed tile. The design uses it once, on the promise the section is arguing for.', 'pixelomatic-core' ),
			)
		);

		$repeater->add_control(
			'icon',
			array(
				'label'   => __( 'Icon', 'pixelomatic-core' ),
				'type'    => Controls_Manager::ICONS,
				'default' => self::design_icon( 'check' ),
			)
		);

		$repeater->add_control(
			'title',
			array(
				'label'       => __( 'Title', 'pixelomatic-core' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
				'condition'   => array( 'layout' => 'feature' ),
			)
		);

		$repeater->add_control(
			'figure',
			array(
				'label'       => __( 'Figure', 'pixelomatic-core' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
				'condition'   => array( 'layout' => 'stat' ),
			)
		);

		$repeater->add_control(
			'figure_label',
			array(
				'label'       => __( 'Figure label', 'pixelomatic-core' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
				'condition'   => array( 'layout' => 'stat' ),
			)
		);

		$repeater->add_control(
			'text',
			array(
				'label'       => __( 'Text', 'pixelomatic-core' ),
				'type'        => Controls_Manager::TEXTAREA,
				'rows'        => 3,
				'label_block' => true,
			)
		);

		$repeater->add_control(
			'detail',
			array(
				'label'     => __( 'Detail block', 'pixelomatic-core' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => '',
				'options'   => array(
					''         => __( 'None', 'pixelomatic-core' ),
					'releases' => __( 'Release list', 'pixelomatic-core' ),
					'checks'   => __( 'Checklist', 'pixelomatic-core' ),
					'chips'    => __( 'Chips', 'pixelomatic-core' ),
				),
				'separator' => 'before',
			)
		);

		$repeater->add_control(
			'lines',
			array(
				'label'       => __( 'Detail lines', 'pixelomatic-core' ),
				'type'        => Controls_Manager::TEXTAREA,
				'rows'        => 5,
				'label_block' => true,
				'description' => __( 'One per line. A release is <code>version | what changed | when</code>, a check is <code>label | note</code>, a chip is just its label.', 'pixelomatic-core' ),
				'condition'   => array( 'detail!' => '' ),
			)
		);

		return array(
			'type'        => Controls_Manager::REPEATER,
			'fields'      => $repeater->get_controls(),
			'title_field' => '{{{ title || figure }}}',
			'default'     => $this->default_tiles(),
		);
	}

	/**
	 * The six tiles the design ships with.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	private function default_tiles(): array {
		return array(
			array(
				'layout' => 'feature',
				'wide'   => 'yes',
				'accent' => 'yes',
				'icon'   => self::design_icon( 'refresh' ),
				'title'  => __( 'Lifetime updates', 'pixelomatic-core' ),
				'text'   => __( 'Buy version 3 today and version 7 is still yours. No renewal, no upgrade invoice.', 'pixelomatic-core' ),
				'detail' => 'releases',
				'lines'  => __( "v3.2.0 | New: dark mode tokens | shipped 2 days ago\nv3.1.4 | Fix: PHP 8.4 deprecations | 6 weeks ago\nv3.0.0 | Rebuilt for the block editor | March 2026", 'pixelomatic-core' ),
			),
			array(
				'layout'       => 'stat',
				'icon'         => self::design_icon( 'clock' ),
				'figure'       => __( '4 hrs', 'pixelomatic-core' ),
				'figure_label' => __( 'median first reply', 'pixelomatic-core' ),
				'text'         => __( 'From the developer who wrote the product, weekdays. No outsourced tier one.', 'pixelomatic-core' ),
			),
			array(
				'layout'       => 'stat',
				'icon'         => self::design_icon( 'bolt' ),
				'figure'       => __( '14×', 'pixelomatic-core' ),
				'figure_label' => __( 'updates a year, per product', 'pixelomatic-core' ),
				'text'         => __( 'We track WordPress, Laravel and Next.js releases centrally, so support lands in weeks.', 'pixelomatic-core' ),
			),
			array(
				'layout' => 'feature',
				'icon'   => self::design_icon( 'lock' ),
				'title'  => __( 'Checkout you already trust', 'pixelomatic-core' ),
				'text'   => __( 'Stripe and PayPal, PCI compliant, with a proper VAT invoice in your inbox before the download finishes.', 'pixelomatic-core' ),
				'detail' => 'chips',
				'lines'  => __( "Stripe\nPayPal\nVAT", 'pixelomatic-core' ),
			),
			array(
				'layout' => 'feature',
				'icon'   => self::design_icon( 'book' ),
				'title'  => __( 'Docs written the same week', 'pixelomatic-core' ),
				'text'   => __( 'Install guide, API reference and a short walkthrough ship with every product — and get rewritten when it changes.', 'pixelomatic-core' ),
				'detail' => 'chips',
				'lines'  => __( "Install\nAPI\nVideo", 'pixelomatic-core' ),
			),
			array(
				'layout' => 'feature',
				'wide'   => 'yes',
				'icon'   => self::design_icon( 'shield-check' ),
				'title'  => __( 'Every release passes the same four checks', 'pixelomatic-core' ),
				'text'   => __( 'Nothing reaches the store on a deadline. If a check fails, the release waits.', 'pixelomatic-core' ),
				'detail' => 'checks',
				'lines'  => __( "Coding standards | PHPCS, ESLint\nAccessibility | WCAG 2.2 AA\nPerformance budget | Lighthouse 95+\nFramework versions | Tested on current", 'pixelomatic-core' ),
			),
		);
	}

	/**
	 * The Style tab.
	 *
	 * @return void
	 */
	private function register_style_controls(): void {
		$this->start_style_section( 'style_head', __( 'Section head', 'pixelomatic-core' ) );
		$this->register_section_head_style_controls();
		$this->register_button_style(
			'head_link',
			__( 'Licence link', 'pixelomatic-core' ),
			'{{WRAPPER}} .pix-section-heading__aside'
		);
		$this->end_controls_section();

		$this->start_style_section( 'style_grid', __( 'Grid', 'pixelomatic-core' ) );
		$this->register_gap_style( 'grid_gap', __( 'Gap', 'pixelomatic-core' ), '{{WRAPPER}} .pix-why__grid', 48 );
		$this->end_controls_section();

		$this->start_style_section( 'style_tile', __( 'Tiles', 'pixelomatic-core' ) );

		$this->register_box_style(
			'tile',
			__( 'Tile', 'pixelomatic-core' ),
			'{{WRAPPER}} .pix-why__tile',
			array( 'separator' => 'none' )
		);

		$this->register_icon_style(
			'tile_icon',
			__( 'Icon', 'pixelomatic-core' ),
			'{{WRAPPER}} .pix-why__icon',
			array( 'svg_selector' => '{{WRAPPER}} .pix-why__icon svg' )
		);

		$this->register_text_style( 'tile_title', __( 'Title', 'pixelomatic-core' ), '{{WRAPPER}} .pix-why__title' );

		$this->register_text_style( 'tile_figure', __( 'Figure', 'pixelomatic-core' ), '{{WRAPPER}} .pix-why__figure' );

		$this->register_text_style(
			'tile_text',
			__( 'Text', 'pixelomatic-core' ),
			'{{WRAPPER}} .pix-why__text',
			array( 'spacing' => false )
		);

		$this->end_controls_section();

		$this->start_style_section( 'style_band', __( 'Band', 'pixelomatic-core' ) );

		$this->register_box_style(
			'band',
			__( 'Band', 'pixelomatic-core' ),
			'{{WRAPPER}} .section',
			array(
				'heading' => false,
				'shadow'  => false,
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Renders the widget.
	 *
	 * @return void
	 */
	protected function render(): void {
		$tiles = (array) ( $this->get_settings_for_display( 'tiles' ) ?? array() );
		$tag   = (string) $this->get_settings_for_display( 'tile_tag' );
		?>
		<section class="section section--dark pix-why">
			<div class="container section__inner section__inner--tight pix-why__inner">
				<?php $this->render_section_head( array( 'eyebrow_class' => 'pix-why__eyebrow' ) ); ?>

				<?php if ( array() !== $tiles ) : ?>
					<ul class="pix-why__grid">
						<?php foreach ( $tiles as $tile ) : ?>
							<?php $this->render_tile( (array) $tile, $tag ); ?>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</div>
		</section>
		<?php
	}

	/**
	 * Renders one tile.
	 *
	 * The wide feature tile puts its icon beside the title, because a 24px
	 * heading over half a row leaves a hole under a stacked icon. Everything
	 * else stacks, and pushes its body to the foot of the tile so a row of
	 * tiles agrees on where the figures and the titles sit.
	 *
	 * @param array<string, mixed> $tile One repeater row.
	 * @param string               $tag  Heading tag for the title.
	 * @return void
	 */
	private function render_tile( array $tile, string $tag ): void {
		$layout = 'stat' === ( $tile['layout'] ?? 'feature' ) ? 'stat' : 'feature';
		$wide   = 'yes' === ( $tile['wide'] ?? '' );
		$text   = (string) ( $tile['text'] ?? '' );
		$inline = $wide && 'feature' === $layout;

		$classes = 'pix-why__tile pix-why__tile--' . $layout;

		if ( $wide ) {
			$classes .= ' pix-why__tile--wide';
		}

		if ( 'yes' === ( $tile['accent'] ?? '' ) ) {
			$classes .= ' pix-why__tile--accent';
		}

		// Only the wide feature tile runs its icon and title on one line; the
		// modifier is what the stylesheet switches the layout on, so it is
		// derived here rather than asked for in the panel.
		if ( $inline ) {
			$classes .= ' pix-why__tile--inline';
		}
		?>
		<li class="<?php echo esc_attr( $classes ); ?>">
			<?php if ( $inline ) : ?>
				<div class="pix-why__lead">
					<?php $this->render_tile_icon( $tile ); ?>
					<?php $this->render_heading( (string) ( $tile['title'] ?? '' ), $tag, 'pix-why__title' ); ?>
				</div>

				<?php if ( '' !== $text ) : ?>
					<p class="pix-why__text"><?php echo esc_html( $text ); ?></p>
				<?php endif; ?>

				<?php $this->render_detail( $tile ); ?>
			<?php else : ?>
				<?php $this->render_tile_icon( $tile ); ?>

				<div class="pix-why__body">
					<?php if ( 'stat' === $layout ) : ?>
						<?php $this->render_figure( $tile ); ?>
					<?php else : ?>
						<?php $this->render_heading( (string) ( $tile['title'] ?? '' ), $tag, 'pix-why__title pix-why__title--sm' ); ?>
					<?php endif; ?>

					<?php if ( '' !== $text ) : ?>
						<p class="pix-why__text"><?php echo esc_html( $text ); ?></p>
					<?php endif; ?>

					<?php $this->render_detail( $tile ); ?>
				</div>
			<?php endif; ?>
		</li>
		<?php
	}

	/**
	 * Renders a tile's icon chip.
	 *
	 * @param array<string, mixed> $tile One repeater row.
	 * @return void
	 */
	private function render_tile_icon( array $tile ): void {
		$icon = $tile['icon'] ?? '';

		if ( ! $this->has_icon_value( $icon ) ) {
			return;
		}
		?>
		<span class="pix-why__icon"><?php $this->render_icon_value( $icon ); ?></span>
		<?php
	}

	/**
	 * Renders the figure and its label.
	 *
	 * The figure is a paragraph, not a heading: it is a number, and a screen
	 * reader running the heading list of the page should hear the six promises,
	 * not "4 hrs".
	 *
	 * @param array<string, mixed> $tile One repeater row.
	 * @return void
	 */
	private function render_figure( array $tile ): void {
		$figure = (string) ( $tile['figure'] ?? '' );
		$label  = (string) ( $tile['figure_label'] ?? '' );

		if ( '' !== $figure ) {
			printf( '<p class="pix-why__figure">%s</p>', esc_html( $figure ) );
		}

		if ( '' !== $label ) {
			printf( '<p class="pix-why__label">%s</p>', esc_html( $label ) );
		}
	}

	/**
	 * Renders a tile's detail block.
	 *
	 * @param array<string, mixed> $tile One repeater row.
	 * @return void
	 */
	private function render_detail( array $tile ): void {
		$kind  = (string) ( $tile['detail'] ?? '' );
		$lines = self::lines( (string) ( $tile['lines'] ?? '' ) );

		if ( '' === $kind || array() === $lines ) {
			return;
		}

		if ( 'releases' === $kind ) {
			$this->render_releases( $lines );

			return;
		}

		if ( 'checks' === $kind ) {
			$this->render_checks( $lines );

			return;
		}

		if ( 'chips' === $kind ) {
			$this->render_chips( $lines );
		}
	}

	/**
	 * Renders a release list.
	 *
	 * The first row is the current release, and is the only one marked live.
	 * That is a fact about position, not a field an editor has to keep true.
	 *
	 * @param string[] $lines Raw lines.
	 * @return void
	 */
	private function render_releases( array $lines ): void {
		?>
		<ul class="pix-why__releases">
			<?php foreach ( $lines as $index => $line ) : ?>
				<?php $cells = self::cells( $line ); ?>
				<li class="pix-why__release<?php echo 0 === $index ? ' pix-why__release--live' : ''; ?>">
					<span class="pix-why__bullet" aria-hidden="true"></span>
					<span class="pix-why__version"><?php echo esc_html( (string) ( $cells[0] ?? '' ) ); ?></span>
					<span class="pix-why__note"><?php echo esc_html( (string) ( $cells[1] ?? '' ) ); ?></span>
					<span class="pix-why__when"><?php echo esc_html( (string) ( $cells[2] ?? '' ) ); ?></span>
				</li>
			<?php endforeach; ?>
		</ul>
		<?php
	}

	/**
	 * Renders a two-up checklist.
	 *
	 * @param string[] $lines Raw lines.
	 * @return void
	 */
	private function render_checks( array $lines ): void {
		?>
		<ul class="pix-why__checks">
			<?php foreach ( $lines as $line ) : ?>
				<?php
				$cells = self::cells( $line );
				$note  = (string) ( $cells[1] ?? '' );
				?>
				<li class="pix-why__check">
					<span class="pix-why__tick"><?php $this->icon( 'check', 13, 2.4 ); ?></span>
					<span class="pix-why__check-body">
						<span class="pix-why__check-title"><?php echo esc_html( (string) ( $cells[0] ?? '' ) ); ?></span>
						<?php if ( '' !== $note ) : ?>
							<span class="pix-why__check-note"><?php echo esc_html( $note ); ?></span>
						<?php endif; ?>
					</span>
				</li>
			<?php endforeach; ?>
		</ul>
		<?php
	}

	/**
	 * Renders a chip row.
	 *
	 * @param string[] $lines Raw lines.
	 * @return void
	 */
	private function render_chips( array $lines ): void {
		?>
		<ul class="pix-why__chips">
			<?php foreach ( $lines as $line ) : ?>
				<li class="pix-why__chip"><?php echo esc_html( $line ); ?></li>
			<?php endforeach; ?>
		</ul>
		<?php
	}

	/**
	 * Splits a textarea into its non-empty lines.
	 *
	 * @param string $value Raw control value.
	 * @return string[]
	 */
	private static function lines( string $value ): array {
		$lines = preg_split( '/\R/', $value );

		if ( false === $lines ) {
			return array();
		}

		return array_values(
			array_filter(
				array_map( 'trim', $lines ),
				static function ( string $line ): bool {
					return '' !== $line;
				}
			)
		);
	}

	/**
	 * Splits one line into its pipe-separated cells.
	 *
	 * @param string $line One line.
	 * @return string[]
	 */
	private static function cells( string $line ): array {
		return array_map( 'trim', explode( '|', $line ) );
	}
}
