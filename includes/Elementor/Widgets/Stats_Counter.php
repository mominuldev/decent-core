<?php
/**
 * Statistics Counter widget.
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
 * A row of headline figures.
 */
final class Stats_Counter extends Widget_Base {

	use Has_Section_Head;
	use Has_Grid_Controls;
	use Has_Style_Controls;

	/**
	 * Widget slug.
	 *
	 * @return string
	 */
	public static function slug(): string {
		return 'stats-counter';
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
		$this->start_controls_section( 'items_section', array( 'label' => __( 'Statistics', 'decent-core' ) ) );

		$repeater = new Repeater();

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
						'title' => __( '25,400+', 'decent-core' ),
						'text'  => __( 'Customers in 80+ countries', 'decent-core' ),
					),
					array(
						'title' => __( '1,240', 'decent-core' ),
						'text'  => __( 'Reviewed products', 'decent-core' ),
					),
					array(
						'title' => __( '98%', 'decent-core' ),
						'text'  => __( 'Positive reviews', 'decent-core' ),
					),
					array(
						'title' => __( '< 4 hrs', 'decent-core' ),
						'text'  => __( 'Median support reply', 'decent-core' ),
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
		$this->register_grid_controls( 4 );
		$this->end_controls_section();

		$this->start_style_section( 'style_head', __( 'Section head', 'decent-core' ) );
		$this->register_section_head_style_controls();
		$this->end_controls_section();

		$this->start_style_section( 'style_items', __( 'Statistics', 'decent-core' ) );

		$this->register_box_style(
			'stat',
			__( 'Item', 'decent-core' ),
			'{{WRAPPER}} .trust__metrics li',
			array( 'separator' => 'none' )
		);

		$this->register_alignment_style( 'stat_align', '{{WRAPPER}} .trust__metrics li' );

		$this->register_text_style(
			'stat_title',
			__( 'Figure', 'decent-core' ),
			'{{WRAPPER}} .trust__metrics dt'
		);

		$this->register_text_style(
			'stat_text',
			__( 'Label', 'decent-core' ),
			'{{WRAPPER}} .trust__metrics dd',
			array( 'spacing' => false )
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
					<ul class="trust__metrics pix-grid">
						<?php foreach ( $items as $item ) : ?>
							<li>
								<dl>
									<dt><?php echo esc_html( (string) ( $item['title'] ?? '' ) ); ?></dt>
									<dd><?php echo esc_html( (string) ( $item['text'] ?? '' ) ); ?></dd>
								</dl>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</div>
		</section>
		<?php
	}
}
