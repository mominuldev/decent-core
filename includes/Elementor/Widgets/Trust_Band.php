<?php
/**
 * Trust Band widget.
 *
 * @package DecentCore
 */

namespace DecentCore\Elementor\Widgets;

defined( 'ABSPATH' ) || exit;

use DecentCore\Elementor\Base\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Repeater;

/**
 * Client names beside headline metrics.
 *
 * Matches _reference/index.html: a .trust__grid holding a .trust__clients list
 * and a .trust__metrics definition list, where each metric is a div wrapping a
 * dt and a dd — not a flat dl.
 */
final class Trust_Band extends Widget_Base {

	/**
	 * Widget slug.
	 *
	 * @return string
	 */
	public static function slug(): string {
		return 'trust-band';
	}

	/**
	 * Registers controls.
	 *
	 * @return void
	 */
	protected function register_controls(): void {
		$this->start_controls_section( 'content', array( 'label' => __( 'Content', 'decent-core' ) ) );

		$this->add_control(
			'eyebrow',
			array(
				'label'       => __( 'Eyebrow', 'decent-core' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Trusted by agencies, startups and product teams', 'decent-core' ),
				'label_block' => true,
			)
		);

		$clients = new Repeater();

		$clients->add_control(
			'name',
			array(
				'label'       => __( 'Name', 'decent-core' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Client', 'decent-core' ),
				'label_block' => true,
			)
		);

		$this->add_control(
			'clients',
			array(
				'label'       => __( 'Clients', 'decent-core' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $clients->get_controls(),
				'title_field' => '{{{ name }}}',
				'default'     => array(
					array( 'name' => 'Northwind Studio' ),
					array( 'name' => 'Baseline Agency' ),
					array( 'name' => 'Orbit Labs' ),
					array( 'name' => 'Perch Digital' ),
					array( 'name' => 'Meridian Works' ),
				),
			)
		);

		$metrics = new Repeater();

		$metrics->add_control(
			'value',
			array(
				'label'   => __( 'Figure', 'decent-core' ),
				'type'    => Controls_Manager::TEXT,
				'default' => '25,400+',
			)
		);

		$metrics->add_control(
			'label',
			array(
				'label'       => __( 'Label', 'decent-core' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Customers', 'decent-core' ),
				'label_block' => true,
			)
		);

		$this->add_control(
			'metrics',
			array(
				'label'       => __( 'Metrics', 'decent-core' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $metrics->get_controls(),
				'title_field' => '{{{ value }}}',
				'default'     => array(
					array(
						'value' => '25,400+',
						'label' => __( 'Customers in 80+ countries', 'decent-core' ),
					),
					array(
						'value' => '1,240',
						'label' => __( 'Reviewed products', 'decent-core' ),
					),
					array(
						'value' => '98%',
						'label' => __( 'Positive reviews', 'decent-core' ),
					),
					array(
						'value' => '< 4 hrs',
						'label' => __( 'Median support reply', 'decent-core' ),
					),
				),
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
		$eyebrow = $this->text( 'eyebrow' );
		$clients = (array) ( $this->get_settings_for_display( 'clients' ) ?? array() );
		$metrics = (array) ( $this->get_settings_for_display( 'metrics' ) ?? array() );
		?>
		<section class="section section--alt section--bordered">
			<div class="container trust__inner">
				<?php if ( '' !== $eyebrow ) : ?>
					<p class="eyebrow eyebrow--muted"><?php echo esc_html( $eyebrow ); ?></p>
				<?php endif; ?>

				<div class="trust__grid">
					<?php if ( ! empty( $clients ) ) : ?>
						<ul class="trust__clients">
							<?php foreach ( $clients as $client ) : ?>
								<li><?php echo esc_html( (string) ( $client['name'] ?? '' ) ); ?></li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>

					<?php if ( ! empty( $metrics ) ) : ?>
						<dl class="trust__metrics">
							<?php foreach ( $metrics as $metric ) : ?>
								<div>
									<dt><?php echo esc_html( (string) ( $metric['value'] ?? '' ) ); ?></dt>
									<dd><?php echo esc_html( (string) ( $metric['label'] ?? '' ) ); ?></dd>
								</div>
							<?php endforeach; ?>
						</dl>
					<?php endif; ?>
				</div>
			</div>
		</section>
		<?php
	}
}
