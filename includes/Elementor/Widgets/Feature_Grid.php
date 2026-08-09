<?php
/**
 * Feature Grid widget.
 *
 * @package DecentCore
 */

namespace DecentCore\Elementor\Widgets;

defined( 'ABSPATH' ) || exit;

use DecentCore\Elementor\Base\Traits\Has_Grid_Controls;
use DecentCore\Elementor\Base\Traits\Has_Section_Head;
use DecentCore\Elementor\Base\Traits\Has_Style_Controls;
use DecentCore\Elementor\Base\Widget_Base;
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
		$this->start_controls_section( 'head', array( 'label' => __( 'Section head', 'decent-core' ) ) );
		$this->register_section_head_controls( __( 'Built for the way you work', 'decent-core' ), __( 'Features', 'decent-core' ) );
		$this->end_controls_section();

		$this->start_controls_section( 'items', array( 'label' => __( 'Features', 'decent-core' ) ) );

		$repeater = new Repeater();

		$repeater->add_control(
			'icon',
			array(
				'label'   => __( 'Icon', 'decent-core' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'check',
				// Restricted to the theme's own SVG map, so a widget cannot
				// introduce an off-system icon or an icon font.
				'options' => self::icon_options(),
			)
		);

		$repeater->add_control(
			'title',
			array(
				'label'       => __( 'Title', 'decent-core' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Feature', 'decent-core' ),
				'label_block' => true,
			)
		);

		$repeater->add_control(
			'text',
			array(
				'label'       => __( 'Text', 'decent-core' ),
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
						'icon'  => 'shield-check',
						'title' => __( 'Reviewed in-house', 'decent-core' ),
						'text'  => __( 'Every release is checked for code standards, accessibility and performance before it ships.', 'decent-core' ),
					),
					array(
						'icon'  => 'refresh',
						'title' => __( 'Lifetime updates', 'decent-core' ),
						'text'  => __( 'Version history and changelogs live in your dashboard, so you can review changes before pushing them.', 'decent-core' ),
					),
					array(
						'icon'  => 'file-text',
						'title' => __( 'Documented', 'decent-core' ),
						'text'  => __( 'Setup guides, hook references and a PDF walkthrough with every product.', 'decent-core' ),
					),
				),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'layout',
			array(
				'label' => __( 'Layout', 'decent-core' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);
		$this->register_grid_controls( 3 );
		$this->end_controls_section();

		$this->start_style_section( 'style_head', __( 'Section head', 'decent-core' ) );
		$this->register_section_head_style_controls();
		$this->end_controls_section();

		$this->start_style_section( 'style_card', __( 'Cards', 'decent-core' ) );

		$this->register_box_style(
			'card',
			__( 'Card', 'decent-core' ),
			'{{WRAPPER}} .feature-card',
			array( 'separator' => 'none' )
		);

		$this->register_icon_style( 'card_icon', __( 'Icon', 'decent-core' ), '{{WRAPPER}} .feature-card__icon' );

		$this->register_text_style( 'card_title', __( 'Title', 'decent-core' ), '{{WRAPPER}} .feature-card h3' );

		$this->register_text_style(
			'card_text',
			__( 'Text', 'decent-core' ),
			'{{WRAPPER}} .feature-card p',
			array( 'spacing' => false )
		);

		$this->register_gap_style(
			'card_gap',
			__( 'Icon and text gap', 'decent-core' ),
			'{{WRAPPER}} .feature-card',
			40
		);

		$this->end_controls_section();

		$this->start_style_section( 'style_band', __( 'Band', 'decent-core' ) );

		$this->register_box_style(
			'band',
			__( 'Band', 'decent-core' ),
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
		$features = (array) ( $this->get_settings_for_display( 'features' ) ?? array() );
		?>
		<section class="section">
			<div class="container section__inner section__inner--tight">
				<?php $this->render_section_head(); ?>

				<?php if ( ! empty( $features ) ) : ?>
					<ul class="feature-grid decent-grid">
						<?php foreach ( $features as $feature ) : ?>
							<li class="feature-card">
								<span class="feature-card__icon">
									<?php $this->icon( (string) ( $feature['icon'] ?? 'check' ), 20, 1.7 ); ?>
								</span>
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
