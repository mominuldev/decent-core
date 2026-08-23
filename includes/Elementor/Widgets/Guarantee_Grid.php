<?php
/**
 * Guarantee Grid widget.
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
 * Purchase guarantees, shown as icon rows.
 */
final class Guarantee_Grid extends Widget_Base {

	use Has_Section_Head;
	use Has_Grid_Controls;
	use Has_Style_Controls;

	/**
	 * Widget slug.
	 *
	 * @return string
	 */
	public static function slug(): string {
		return 'guarantee-grid';
	}

	/**
	 * Registers controls.
	 *
	 * @return void
	 */
	protected function register_controls(): void {
		$this->start_controls_section( 'head', array( 'label' => __( 'Section head', 'decent-core' ) ) );
		$this->register_section_head_controls();
		$this->end_controls_section();

		// Not 'items': the repeater below is already called that, and a section
		// shares the control stack with the controls inside it. Elementor
		// refuses the second registration, so the repeater would never appear.
		$this->start_controls_section( 'items_section', array( 'label' => __( 'Guarantees', 'decent-core' ) ) );

		$repeater = new Repeater();

		$repeater->add_control(
			'icon',
			array(
				'label'   => __( 'Icon', 'decent-core' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'check',
				'options' => self::icon_options(),
			)
		);

		$repeater->add_control(
			'title',
			array(
				'label'       => __( 'Title', 'decent-core' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Title', 'decent-core' ),
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
			'items',
			array(
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'title_field' => '{{{ title }}}',
				'default'     => array(
					array(
						'icon'  => 'shield-check',
						'title' => __( '14-day refund window', 'decent-core' ),
						'text'  => __( 'If a product does not work as described, we refund it.', 'decent-core' ),
					),
					array(
						'icon'  => 'file-text',
						'title' => __( 'Documentation included', 'decent-core' ),
						'text'  => __( 'Setup guide, hook reference and a walkthrough with every release.', 'decent-core' ),
					),
					array(
						'icon'  => 'refresh',
						'title' => __( 'Six months of support', 'decent-core' ),
						'text'  => __( 'Extendable to twelve, answered by the people who wrote the code.', 'decent-core' ),
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

		$this->start_style_section( 'style_card', __( 'Guarantees', 'decent-core' ) );

		$this->register_box_style(
			'card',
			__( 'Card', 'decent-core' ),
			'{{WRAPPER}} .guarantee-card',
			array( 'separator' => 'none' )
		);

		$this->register_icon_style( 'card_icon', __( 'Icon', 'decent-core' ), '{{WRAPPER}} .guarantee-card__icon' );

		$this->register_text_style( 'card_title', __( 'Title', 'decent-core' ), '{{WRAPPER}} .guarantee-card h3' );

		$this->register_text_style(
			'card_text',
			__( 'Text', 'decent-core' ),
			'{{WRAPPER}} .guarantee-card p',
			array( 'spacing' => false )
		);

		$this->register_gap_style(
			'card_gap',
			__( 'Icon and text gap', 'decent-core' ),
			'{{WRAPPER}} .guarantee-card',
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
		$items = (array) ( $this->get_settings_for_display( 'items' ) ?? array() );
		?>
		<section class="section">
			<div class="container section__inner section__inner--tight">
				<?php $this->render_section_head(); ?>

				<?php if ( ! empty( $items ) ) : ?>
					<ul class="guarantee-grid pix-grid">
						<?php foreach ( $items as $item ) : ?>
							<li class="guarantee-card">
								<span class="guarantee-card__icon">
									<?php $this->icon( (string) ( $item['icon'] ?? 'check' ), 20, 1.6 ); ?>
								</span>
								<div>
									<h3><?php echo esc_html( (string) ( $item['title'] ?? '' ) ); ?></h3>
									<?php if ( ! empty( $item['text'] ) ) : ?>
										<p><?php echo esc_html( (string) $item['text'] ); ?></p>
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
