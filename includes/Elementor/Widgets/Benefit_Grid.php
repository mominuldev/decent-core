<?php
/**
 * Benefit Grid widget.
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
 * Asymmetric benefit cards on a dark band.
 */
final class Benefit_Grid extends Widget_Base {

	use Has_Section_Head;
	use Has_Grid_Controls;
	use Has_Style_Controls;

	/**
	 * Widget slug.
	 *
	 * @return string
	 */
	public static function slug(): string {
		return 'benefit-grid';
	}

	/**
	 * Registers controls.
	 *
	 * @return void
	 */
	protected function register_controls(): void {
		$this->start_controls_section( 'head', array( 'label' => __( 'Section head', 'pixelomatic-core' ) ) );
		$this->register_section_head_controls( __( 'Buy once. Ship with confidence.', 'pixelomatic-core' ), __( 'Why choose us', 'pixelomatic-core' ) );
		$this->end_controls_section();

		// Not 'items': the repeater below is already called that, and a section
		// shares the control stack with the controls inside it. Elementor
		// refuses the second registration, so the repeater would never appear.
		$this->start_controls_section( 'items_section', array( 'label' => __( 'Benefits', 'pixelomatic-core' ) ) );

		$repeater = new Repeater();

		$repeater->add_control(
			'icon',
			array(
				'label'   => __( 'Icon', 'pixelomatic-core' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'check',
				'options' => self::icon_options(),
			)
		);

		$repeater->add_control(
			'title',
			array(
				'label'       => __( 'Title', 'pixelomatic-core' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Title', 'pixelomatic-core' ),
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
			'items',
			array(
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'title_field' => '{{{ title }}}',
				'default'     => array(
					array(
						'icon'  => 'refresh',
						'title' => __( 'Lifetime updates', 'pixelomatic-core' ),
						'text'  => __( 'Every product you buy keeps updating, with version history and changelogs in your dashboard.', 'pixelomatic-core' ),
					),
					array(
						'icon'  => 'shield',
						'title' => __( 'Quality assurance', 'pixelomatic-core' ),
						'text'  => __( 'Reviewed in-house for code standards, accessibility and performance before it ships.', 'pixelomatic-core' ),
					),
					array(
						'icon'  => 'card',
						'title' => __( 'Secure payments', 'pixelomatic-core' ),
						'text'  => __( 'Stripe and PayPal checkout, PCI compliant, with invoices and VAT handling.', 'pixelomatic-core' ),
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
			'{{WRAPPER}} .benefit-card',
			array( 'separator' => 'none' )
		);

		$this->register_icon_style(
			'card_icon',
			__( 'Icon', 'pixelomatic-core' ),
			'{{WRAPPER}} .benefit-card > svg',
			array(
				'svg_selector' => '{{WRAPPER}} .benefit-card > svg',
				'box'          => false,
			)
		);

		$this->register_text_style( 'card_title', __( 'Title', 'pixelomatic-core' ), '{{WRAPPER}} .benefit-card h3' );

		$this->register_text_style(
			'card_text',
			__( 'Text', 'pixelomatic-core' ),
			'{{WRAPPER}} .benefit-card p',
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
		$items = (array) ( $this->get_settings_for_display( 'items' ) ?? array() );
		?>
		<section class="section section--dark">
			<div class="container section__inner section__inner--tight">
				<?php $this->render_section_head(); ?>

				<?php if ( ! empty( $items ) ) : ?>
					<ul class="benefit-grid pix-grid">
						<?php foreach ( $items as $item ) : ?>
							<li class="benefit-card">
								<?php $this->icon( (string) ( $item['icon'] ?? 'check' ), 22, 1.5 ); ?>
								<h3><?php echo esc_html( (string) ( $item['title'] ?? '' ) ); ?></h3>
								<?php if ( ! empty( $item['text'] ) ) : ?>
									<p><?php echo esc_html( (string) $item['text'] ); ?></p>
								<?php endif; ?>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</div>
		</section>
		<?php
	}
}
