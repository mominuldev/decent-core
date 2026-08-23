<?php
/**
 * Hero Split widget.
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
 * Copy on the left, a visual on the right.
 *
 * The second of the two heroes in the design. The static template shipped
 * both behind a preview switch; here they are two widgets and an editor picks
 * one, which is what the switch was standing in for.
 */
final class Hero_Split extends Widget_Base {

	use Has_Style_Controls;

	/**
	 * Widget slug.
	 *
	 * @return string
	 */
	public static function slug(): string {
		return 'hero-split';
	}

	/**
	 * Registers controls.
	 *
	 * @return void
	 */
	protected function register_controls(): void {
		$this->start_controls_section( 'content', array( 'label' => __( 'Content', 'pixelomatic-core' ) ) );

		$this->add_control(
			'pill',
			array(
				'label'       => __( 'Eyebrow pill', 'pixelomatic-core' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( '1,200+ products · 14 new releases this month', 'pixelomatic-core' ),
				'label_block' => true,
			)
		);

		$this->add_control(
			'heading',
			array(
				'label'       => __( 'Heading', 'pixelomatic-core' ),
				'type'        => Controls_Manager::TEXTAREA,
				'rows'        => 2,
				'default'     => __( 'Professional themes, plugins and templates', 'pixelomatic-core' ),
				'label_block' => true,
			)
		);

		$this->add_control(
			'heading_tag',
			array(
				'label'   => __( 'Heading tag', 'pixelomatic-core' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'h1',
				'options' => array(
					'h1' => 'H1',
					'h2' => 'H2',
					'p'  => __( 'Paragraph', 'pixelomatic-core' ),
				),
			)
		);

		$this->add_control(
			'text',
			array(
				'label'       => __( 'Supporting text', 'pixelomatic-core' ),
				'type'        => Controls_Manager::TEXTAREA,
				'rows'        => 3,
				'default'     => __( 'One-time price, lifetime updates, and documentation with every release.', 'pixelomatic-core' ),
				'label_block' => true,
			)
		);

		$this->add_control(
			'primary_label',
			array(
				'label'   => __( 'Primary button', 'pixelomatic-core' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'Browse products', 'pixelomatic-core' ),
			)
		);

		$this->add_control(
			'primary_url',
			array(
				'label'       => __( 'Primary link', 'pixelomatic-core' ),
				'type'        => Controls_Manager::URL,
				'label_block' => true,
			)
		);

		$this->add_control(
			'secondary_label',
			array(
				'label' => __( 'Secondary button', 'pixelomatic-core' ),
				'type'  => Controls_Manager::TEXT,
			)
		);

		$this->add_control(
			'secondary_url',
			array(
				'label'       => __( 'Secondary link', 'pixelomatic-core' ),
				'type'        => Controls_Manager::URL,
				'label_block' => true,
			)
		);

		$this->add_control(
			'image',
			array(
				'label' => __( 'Visual', 'pixelomatic-core' ),
				'type'  => Controls_Manager::MEDIA,
			)
		);

		$this->end_controls_section();

		// Not 'stats': the repeater below is already called that, and a section
		// shares the control stack with the controls inside it. Elementor
		// refuses the second registration, so the repeater would never appear.
		$this->start_controls_section( 'stats_section', array( 'label' => __( 'Statistics', 'pixelomatic-core' ) ) );

		$repeater = new Repeater();

		$repeater->add_control(
			'value',
			array(
				'label'   => __( 'Figure', 'pixelomatic-core' ),
				'type'    => Controls_Manager::TEXT,
				'default' => '25,400+',
			)
		);

		$repeater->add_control(
			'label',
			array(
				'label'       => __( 'Label', 'pixelomatic-core' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Customers', 'pixelomatic-core' ),
				'label_block' => true,
			)
		);

		$this->add_control(
			'stats',
			array(
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'title_field' => '{{{ value }}}',
				'default'     => array(
					array(
						'value' => '25,400+',
						'label' => __( 'Customers', 'pixelomatic-core' ),
					),
					array(
						'value' => '1,240',
						'label' => __( 'Products', 'pixelomatic-core' ),
					),
					array(
						'value' => '98%',
						'label' => __( 'Positive reviews', 'pixelomatic-core' ),
					),
				),
			)
		);

		$this->end_controls_section();

		$this->start_style_section( 'style_text', __( 'Text', 'pixelomatic-core' ) );

		$this->register_alignment_style( 'text_align', '{{WRAPPER}} .hero__inner > div:first-child' );

		$this->register_text_style( 'pill', __( 'Eyebrow pill', 'pixelomatic-core' ), '{{WRAPPER}} .pill' );

		$this->add_control(
			'pill_dot_color',
			array(
				'label'     => __( 'Pill dot colour', 'pixelomatic-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pill__dot' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->register_text_style( 'heading', __( 'Heading', 'pixelomatic-core' ), '{{WRAPPER}} .hero__title' );

		$this->register_text_style(
			'text',
			__( 'Supporting text', 'pixelomatic-core' ),
			'{{WRAPPER}} .hero__text',
			array( 'spacing' => false )
		);

		$this->end_controls_section();

		$this->start_style_section( 'style_buttons', __( 'Buttons', 'pixelomatic-core' ) );

		$this->register_button_style(
			'primary_button',
			__( 'Primary button', 'pixelomatic-core' ),
			'{{WRAPPER}} .hero__actions .btn--primary',
			array( 'separator' => 'none' )
		);

		$this->register_button_style(
			'secondary_button',
			__( 'Secondary button', 'pixelomatic-core' ),
			'{{WRAPPER}} .hero__actions .btn--secondary'
		);

		$this->register_gap_style( 'buttons_gap', __( 'Gap', 'pixelomatic-core' ), '{{WRAPPER}} .hero__actions', 40 );

		$this->end_controls_section();

		$this->start_style_section( 'style_stats', __( 'Statistics', 'pixelomatic-core' ) );

		$this->register_text_style(
			'stat_value',
			__( 'Figure', 'pixelomatic-core' ),
			'{{WRAPPER}} .hero__stats dt',
			array( 'separator' => 'none' )
		);

		$this->register_text_style(
			'stat_label',
			__( 'Label', 'pixelomatic-core' ),
			'{{WRAPPER}} .hero__stats dd',
			array( 'spacing' => false )
		);

		$this->register_gap_style( 'stats_gap', __( 'Gap', 'pixelomatic-core' ), '{{WRAPPER}} .hero__stats', 64 );

		$this->end_controls_section();

		$this->start_style_section( 'style_visual', __( 'Visual', 'pixelomatic-core' ) );

		$this->register_box_style(
			'visual',
			__( 'Browser frame', 'pixelomatic-core' ),
			'{{WRAPPER}} .hero__visual .browser',
			array( 'separator' => 'none' )
		);

		$this->add_control(
			'visual_bar_background',
			array(
				'label'     => __( 'Title bar background', 'pixelomatic-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .browser__bar' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'visual_image_radius',
			array(
				'label'      => __( 'Image radius', 'pixelomatic-core' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .hero__visual img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();

		$this->start_style_section( 'style_band', __( 'Hero band', 'pixelomatic-core' ) );

		$this->register_box_style(
			'band',
			__( 'Band', 'pixelomatic-core' ),
			'{{WRAPPER}} .hero',
			array(
				'heading' => false,
				'shadow'  => false,
			)
		);

		$this->register_gap_style( 'band_gap', __( 'Column gap', 'pixelomatic-core' ), '{{WRAPPER}} .hero__inner', 96 );

		$this->end_controls_section();
	}

	/**
	 * Renders the widget.
	 *
	 * @return void
	 */
	protected function render(): void {
		$pill  = $this->text( 'pill' );
		$text  = $this->text( 'text' );
		$stats = (array) ( $this->get_settings_for_display( 'stats' ) ?? array() );
		$image = (array) ( $this->get_settings_for_display( 'image' ) ?? array() );
		?>
		<section class="hero">
			<div class="container hero__inner">
				<div>
					<?php if ( '' !== $pill ) : ?>
						<p class="pill"><span class="pill__dot"></span><?php echo esc_html( $pill ); ?></p>
					<?php endif; ?>

					<?php
					$this->render_heading(
						$this->text( 'heading' ),
						(string) $this->get_settings_for_display( 'heading_tag' ),
						'hero__title'
					);
					?>

					<?php if ( '' !== $text ) : ?>
						<p class="hero__text"><?php echo esc_html( $text ); ?></p>
					<?php endif; ?>

					<div class="btn-row hero__actions">
						<?php
						$this->button( 'primary_label', 'primary_url', 'btn btn--primary btn--lg' );
						$this->button( 'secondary_label', 'secondary_url', 'btn btn--secondary btn--lg' );
						?>
					</div>

					<?php if ( ! empty( $stats ) ) : ?>
						<dl class="hero__stats">
							<?php foreach ( $stats as $stat ) : ?>
								<div>
									<dt><?php echo esc_html( (string) ( $stat['value'] ?? '' ) ); ?></dt>
									<dd><?php echo esc_html( (string) ( $stat['label'] ?? '' ) ); ?></dd>
								</div>
							<?php endforeach; ?>
						</dl>
					<?php endif; ?>
				</div>

				<div class="hero__visual">
					<?php if ( ! empty( $image['id'] ) && class_exists( '\Pixelomatic\Frontend\Media' ) ) : ?>
						<div class="browser">
							<div class="browser__bar">
								<span class="browser__dot browser__dot--red"></span>
								<span class="browser__dot browser__dot--yellow"></span>
								<span class="browser__dot browser__dot--green"></span>
							</div>
							<?php
							// The hero image is the page's LCP candidate.
							\Pixelomatic\Frontend\Media::render(
								(int) $image['id'],
								array(
									'frame' => 'hero',
									'lcp'   => true,
								)
							);
							?>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</section>
		<?php
	}

	/**
	 * Renders one hero button, if both its label and link are set.
	 *
	 * @param string $label_key Label setting key.
	 * @param string $url_key   URL setting key.
	 * @param string $class     Button classes.
	 * @return void
	 */
	private function button( string $label_key, string $url_key, string $class ): void {
		$label = $this->text( $label_key );
		$link  = (array) ( $this->get_settings_for_display( $url_key ) ?? array() );
		$url   = (string) ( $link['url'] ?? '' );

		if ( '' === $label || '' === $url ) {
			return;
		}

		printf(
			'<a class="%1$s" href="%2$s"%3$s>%4$s</a>',
			esc_attr( $class ),
			esc_url( $url ),
			! empty( $link['is_external'] ) ? ' target="_blank" rel="noopener noreferrer"' : '',
			esc_html( $label )
		);
	}
}
