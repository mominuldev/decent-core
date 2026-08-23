<?php
/**
 * Feature Grid widget.
 *
 * @package PixelomaticCore
 */

namespace PixelomaticCore\Elementor\Widgets;

defined( 'ABSPATH' ) || exit;

use PixelomaticCore\Elementor\Base\Traits\Has_Grid_Controls;
use PixelomaticCore\Elementor\Base\Traits\Has_Section_Head;
use PixelomaticCore\Elementor\Base\Traits\Has_Style_Controls;
use PixelomaticCore\Elementor\Base\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Repeater;

/**
 * Icon, title and copy in a grid.
 */
final class Feature_Grid extends Widget_Base {

	use Has_Section_Head;
	use Has_Grid_Controls;
	use Has_Style_Controls;

	/**
	 * Widget slug.
	 *
	 * @return string
	 */
	public static function slug(): string {
		return 'feature-grid';
	}

	/**
	 * Registers controls.
	 *
	 * @return void
	 */
	protected function register_controls(): void {
		$this->start_controls_section( 'head', array( 'label' => __( 'Section head', 'pixelomatic-core' ) ) );
		$this->register_section_head_controls( __( 'Built for the way you work', 'pixelomatic-core' ), __( 'Features', 'pixelomatic-core' ) );
		$this->end_controls_section();

		$this->start_controls_section( 'items', array( 'label' => __( 'Features', 'pixelomatic-core' ) ) );

		$repeater = new Repeater();

		// `icon` used to be a select of theme icon slugs, and it stays
		// registered — as a hidden field, holding whatever a row was saved
		// with. It cannot simply be re-typed as a picker: Elementor's
		// multi-value controls discard any stored value that is not an array
		// (Control_Base_Multiple::get_value), so a bare slug would be thrown
		// away and silently replaced by the default before render() ever saw
		// it, and every feature grid already on a site would quietly change
		// icon. Keeping the old field and reading it first is the same shape
		// Elementor uses for its own icon → selected_icon migration.
		$repeater->add_control(
			'icon',
			array(
				'type'    => Controls_Manager::HIDDEN,
				'default' => '',
			)
		);

		// The picker. The design's set is the "Pixelomatic Icons" tab in it, so the
		// default is still a theme icon rendered as inline SVG; what an editor
		// gains is Font Awesome and SVG upload for what the design does not
		// cover. Clearing a row's icon here falls back to the legacy slug if
		// the row has one, which is what an editor of an existing grid would
		// expect — the icon they can see is the icon that stays.
		$repeater->add_control(
			'selected_icon',
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
				'default'     => __( 'Feature', 'pixelomatic-core' ),
				'label_block' => true,
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

		$this->add_control(
			'features',
			array(
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'title_field' => '{{{ title }}}',
				'default'     => array(
					array(
						'selected_icon' => self::design_icon( 'shield-check' ),
						'title'         => __( 'Reviewed in-house', 'pixelomatic-core' ),
						'text'          => __( 'Every release is checked for code standards, accessibility and performance before it ships.', 'pixelomatic-core' ),
					),
					array(
						'selected_icon' => self::design_icon( 'refresh' ),
						'title'         => __( 'Lifetime updates', 'pixelomatic-core' ),
						'text'          => __( 'Version history and changelogs live in your dashboard, so you can review changes before pushing them.', 'pixelomatic-core' ),
					),
					array(
						'selected_icon' => self::design_icon( 'file-text' ),
						'title'         => __( 'Documented', 'pixelomatic-core' ),
						'text'          => __( 'Setup guides, hook references and a PDF walkthrough with every product.', 'pixelomatic-core' ),
					),
				),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'layout',
			array(
				'label' => __( 'Layout', 'pixelomatic-core' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);
		$this->register_grid_controls( 3 );
		$this->end_controls_section();

		$this->start_style_section( 'style_head', __( 'Section head', 'pixelomatic-core' ) );
		$this->register_section_head_style_controls();
		$this->end_controls_section();

		$this->start_style_section( 'style_card', __( 'Cards', 'pixelomatic-core' ) );

		$this->register_box_style(
			'card',
			__( 'Card', 'pixelomatic-core' ),
			'{{WRAPPER}} .feature-card',
			array( 'separator' => 'none' )
		);

		$this->style_icon_controls();

		$this->register_text_style( 'card_title', __( 'Title', 'pixelomatic-core' ), '{{WRAPPER}} .feature-card h3' );

		$this->register_text_style(
			'card_text',
			__( 'Text', 'pixelomatic-core' ),
			'{{WRAPPER}} .feature-card p',
			array( 'spacing' => false )
		);

		$this->register_gap_style(
			'card_gap',
			__( 'Icon and text gap', 'pixelomatic-core' ),
			'{{WRAPPER}} .feature-card',
			40
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
	 * The icon for one repeater row.
	 *
	 * The legacy slug wins when a row has one, because a row that has one was
	 * saved before the picker existed and the slug is the only record of what
	 * its author chose. Everything saved since has an empty `icon` and a real
	 * `selected_icon`, so this reduces to the picker for every new row.
	 *
	 * @param array<string, mixed> $feature Repeater row.
	 * @return mixed Icon value, in either format.
	 */
	private function feature_icon( array $feature ) {
		$legacy = (string) ( $feature['icon'] ?? '' );

		return '' !== $legacy ? $legacy : ( $feature['selected_icon'] ?? '' );
	}

	/**
	 * The icon tile's style controls.
	 *
	 * Written out rather than delegated to register_icon_style(), because that
	 * helper sizes an icon by putting width and height on the SVG — which a
	 * Font Awesome glyph does not have. Now that the picker can produce one,
	 * the size has to be a font-size on the tile with the glyph drawn at 1em,
	 * so a single control moves all three things the picker can return.
	 *
	 * The control ids are the ones register_icon_style() generated, so styling
	 * already saved against this widget carries over untouched.
	 *
	 * @return void
	 */
	private function style_icon_controls(): void {
		$this->style_heading( 'card_icon', __( 'Icon', 'pixelomatic-core' ) );

		$this->add_control(
			'card_icon_color',
			array(
				'label'     => __( 'Icon colour', 'pixelomatic-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .feature-card__icon' => 'color: {{VALUE}};',
				),
			)
		);

		// Set as a custom property, not as font-size directly. `.feature-card`
		// is the theme's component, so the rule that reads this has to be
		// scoped to the widget wrapper — which is two classes, exactly what
		// Elementor's own Style-tab CSS is, and a tie would be settled by
		// stylesheet order. Nothing else writes this property, so there is no
		// tie to settle.
		$this->add_responsive_control(
			'card_icon_size',
			array(
				'label'      => __( 'Icon size', 'pixelomatic-core' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min'  => 12,
						'max'  => 64,
						'step' => 1,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .feature-card__icon' => '--pix-feature-icon-size: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'card_icon_tile',
			array(
				'label'      => __( 'Tile size', 'pixelomatic-core' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min'  => 24,
						'max'  => 120,
						'step' => 2,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .feature-card__icon' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->register_box_style(
			'card_icon',
			__( 'Tile', 'pixelomatic-core' ),
			'{{WRAPPER}} .feature-card__icon',
			array(
				'heading' => false,
				'shadow'  => false,
			)
		);
	}

	/**
	 * Renders the widget.
	 *
	 * @return void
	 */
	protected function render(): void {
		$features = (array) ( $this->get_settings_for_display( 'features' ) ?? array() );
		?>
		<section class="section">
			<div class="container section__inner section__inner--tight">
				<?php $this->render_section_head(); ?>

				<?php if ( ! empty( $features ) ) : ?>
					<ul class="feature-grid pix-grid">
						<?php foreach ( $features as $feature ) : ?>
							<li class="feature-card">
								<?php $icon = $this->feature_icon( $feature ); ?>
								<?php if ( $this->has_icon_value( $icon ) ) : ?>
									<span class="feature-card__icon">
										<?php $this->render_icon_value( $icon ); ?>
									</span>
								<?php endif; ?>
								<?php // .feature-card is display:flex — the icon and a single content block, not three siblings. ?>
								<div>
									<h3><?php echo esc_html( (string) ( $feature['title'] ?? '' ) ); ?></h3>
									<?php if ( ! empty( $feature['text'] ) ) : ?>
										<p><?php echo esc_html( (string) $feature['text'] ); ?></p>
									<?php endif; ?>
								</div>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</div>
		</section>
		<?php
	}
}
