<?php
/**
 * Benefit Grid widget.
 *
 * @package DecentCore
 */

namespace DecentCore\Elementor\Widgets;

defined( 'ABSPATH' ) || exit;

use DecentCore\Elementor\Base\Traits\Has_Grid_Controls;
use DecentCore\Elementor\Base\Traits\Has_Section_Head;
use DecentCore\Elementor\Base\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Repeater;

/**
 * Asymmetric benefit cards on a dark band.
 */
final class Benefit_Grid extends Widget_Base {

	use Has_Section_Head;
	use Has_Grid_Controls;

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
		$this->start_controls_section( 'head', array( 'label' => __( 'Section head', 'decent-core' ) ) );
		$this->register_section_head_controls( __( 'Buy once. Ship with confidence.', 'decent-core' ), __( 'Why choose us', 'decent-core' ) );
		$this->end_controls_section();

		$this->start_controls_section( 'items', array( 'label' => __( 'Benefits', 'decent-core' ) ) );

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
						'icon'  => 'refresh',
						'title' => __( 'Lifetime updates', 'decent-core' ),
						'text'  => __( 'Every product you buy keeps updating, with version history and changelogs in your dashboard.', 'decent-core' ),
					),
					array(
						'icon'  => 'shield',
						'title' => __( 'Quality assurance', 'decent-core' ),
						'text'  => __( 'Reviewed in-house for code standards, accessibility and performance before it ships.', 'decent-core' ),
					),
					array(
						'icon'  => 'card',
						'title' => __( 'Secure payments', 'decent-core' ),
						'text'  => __( 'Stripe and PayPal checkout, PCI compliant, with invoices and VAT handling.', 'decent-core' ),
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
					<ul class="benefit-grid decent-grid">
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
