<?php
/**
 * Showcase widget.
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
 * Alternating text-and-visual rows.
 *
 * The design brief bans three-equal-column feature layouts and calls for a
 * zig-zag instead, which is what .showcase is. Rows alternate automatically
 * rather than asking an editor to remember to flip every second one.
 */
final class Showcase extends Widget_Base {

	use Has_Section_Head;
	use Has_Style_Controls;

	/**
	 * Widget slug.
	 *
	 * @return string
	 */
	public static function slug(): string {
		return 'showcase';
	}

	/**
	 * Registers controls.
	 *
	 * @return void
	 */
	protected function register_controls(): void {
		$this->start_controls_section( 'head', array( 'label' => __( 'Section head', 'pixelomatic-core' ) ) );
		$this->register_section_head_controls(
			__( 'Built for the stack you work in', 'pixelomatic-core' ),
			__( 'Product showcase', 'pixelomatic-core' )
		);
		$this->end_controls_section();

		$this->start_controls_section( 'items', array( 'label' => __( 'Rows', 'pixelomatic-core' ) ) );

		$repeater = new Repeater();

		$repeater->add_control(
			'count',
			array(
				'label'       => __( 'Count label', 'pixelomatic-core' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( '418 PRODUCTS', 'pixelomatic-core' ),
				'label_block' => true,
			)
		);

		$repeater->add_control(
			'title',
			array(
				'label'       => __( 'Title', 'pixelomatic-core' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Themes that survive a real content team', 'pixelomatic-core' ),
				'label_block' => true,
			)
		);

		$repeater->add_control(
			'body',
			array(
				'label'       => __( 'Body', 'pixelomatic-core' ),
				'type'        => Controls_Manager::TEXTAREA,
				'rows'        => 4,
				'label_block' => true,
			)
		);

		$repeater->add_control(
			'points',
			array(
				'label'       => __( 'Tick list', 'pixelomatic-core' ),
				'type'        => Controls_Manager::TEXTAREA,
				'rows'        => 4,
				'description' => __( 'One per line.', 'pixelomatic-core' ),
				'label_block' => true,
			)
		);

		$repeater->add_control(
			'image',
			array(
				'label' => __( 'Image', 'pixelomatic-core' ),
				'type'  => Controls_Manager::MEDIA,
			)
		);

		$this->add_control(
			'rows',
			array(
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'title_field' => '{{{ title }}}',
				'default'     => array(
					array(
						'count'  => __( '418 PRODUCTS', 'pixelomatic-core' ),
						'title'  => __( 'Themes that survive a real content team', 'pixelomatic-core' ),
						'body'   => __( 'Block-native themes built on current WordPress standards: full site editing, patterns, WP-CLI friendly builds and no page-builder lock-in.', 'pixelomatic-core' ),
						'points' => __( "Block patterns and theme.json variations included\nChild theme and demo content in every download\nTested against the current and previous major release", 'pixelomatic-core' ),
					),
				),
			)
		);

		$this->end_controls_section();

		$this->start_style_section( 'style_head', __( 'Section head', 'pixelomatic-core' ) );
		$this->register_section_head_style_controls();
		$this->end_controls_section();

		$this->start_style_section( 'style_rows', __( 'Rows', 'pixelomatic-core' ) );

		$this->register_text_style(
			'row_count',
			__( 'Count label', 'pixelomatic-core' ),
			'{{WRAPPER}} .showcase__count',
			array( 'separator' => 'none' )
		);

		$this->register_text_style( 'row_title', __( 'Title', 'pixelomatic-core' ), '{{WRAPPER}} .showcase__title' );

		$this->register_text_style( 'row_body', __( 'Body', 'pixelomatic-core' ), '{{WRAPPER}} .showcase__body' );

		$this->register_text_style(
			'row_point',
			__( 'Tick list item', 'pixelomatic-core' ),
			'{{WRAPPER}} .showcase__points li',
			array( 'spacing' => false )
		);

		$this->register_icon_style(
			'row_tick',
			__( 'Tick icon', 'pixelomatic-core' ),
			'{{WRAPPER}} .showcase__points li svg',
			array(
				'svg_selector' => '{{WRAPPER}} .showcase__points li svg',
				'box'          => false,
			)
		);

		$this->register_gap_style( 'points_gap', __( 'Tick list gap', 'pixelomatic-core' ), '{{WRAPPER}} .showcase__points', 32 );

		$this->end_controls_section();

		$this->start_style_section( 'style_layout', __( 'Layout', 'pixelomatic-core' ) );

		$this->register_gap_style(
			'row_gap',
			__( 'Text and image gap', 'pixelomatic-core' ),
			'{{WRAPPER}} .showcase',
			96
		);

		$this->add_responsive_control(
			'row_spacing',
			array(
				'label'      => __( 'Space between rows', 'pixelomatic-core' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min'  => 0,
						'max'  => 160,
						'step' => 8,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .showcase + .showcase' => 'margin-top: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'image_radius',
			array(
				'label'      => __( 'Image radius', 'pixelomatic-core' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .showcase img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->register_box_style(
			'band',
			__( 'Band', 'pixelomatic-core' ),
			'{{WRAPPER}} .section',
			array( 'shadow' => false )
		);

		$this->end_controls_section();
	}

	/**
	 * Renders the widget.
	 *
	 * @return void
	 */
	protected function render(): void {
		$rows = (array) ( $this->get_settings_for_display( 'rows' ) ?? array() );

		if ( empty( $rows ) ) {
			return;
		}
		?>
		<section class="section">
			<div class="container section__inner section__inner--tight">
				<?php $this->render_section_head(); ?>

				<?php foreach ( $rows as $index => $row ) : ?>
					<?php
					// Rows alternate on their own; asking an editor to flip
					// every second one is a rule they will eventually forget.
					$reverse = 1 === $index % 2;
					$points  = array_values( array_filter( array_map( 'trim', (array) preg_split( '/\R/', (string) ( $row['points'] ?? '' ) ) ) ) );
					$image   = (array) ( $row['image'] ?? array() );
					?>
					<div class="showcase<?php echo $reverse ? ' showcase--reverse' : ''; ?>">
						<div>
							<?php if ( ! empty( $row['count'] ) ) : ?>
								<p class="showcase__count"><?php echo esc_html( (string) $row['count'] ); ?></p>
							<?php endif; ?>

							<h3 class="showcase__title"><?php echo esc_html( (string) ( $row['title'] ?? '' ) ); ?></h3>

							<?php if ( ! empty( $row['body'] ) ) : ?>
								<p class="showcase__body"><?php echo esc_html( (string) $row['body'] ); ?></p>
							<?php endif; ?>

							<?php if ( ! empty( $points ) ) : ?>
								<ul class="tick-list showcase__points">
									<?php foreach ( $points as $point ) : ?>
										<li>
											<?php $this->icon( 'check', 18, 1.8 ); ?>
											<span><?php echo esc_html( $point ); ?></span>
										</li>
									<?php endforeach; ?>
								</ul>
							<?php endif; ?>
						</div>

						<div>
							<?php
							if ( ! empty( $image['id'] ) && class_exists( '\Pixelomatic\Frontend\Media' ) ) {
								\Pixelomatic\Frontend\Media::render(
									(int) $image['id'],
									array( 'frame' => 'lg' )
								);
							}
							?>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</section>
		<?php
	}
}
