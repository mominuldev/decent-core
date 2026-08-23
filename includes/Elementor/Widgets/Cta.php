<?php
/**
 * CTA widget.
 *
 * @package PixelomaticCore
 */

namespace PixelomaticCore\Elementor\Widgets;

defined( 'ABSPATH' ) || exit;

use PixelomaticCore\Elementor\Base\Traits\Has_Style_Controls;
use PixelomaticCore\Elementor\Base\Widget_Base;
use Elementor\Controls_Manager;

/**
 * A closing call to action.
 */
final class Cta extends Widget_Base {

	use Has_Style_Controls;

	/**
	 * Widget slug.
	 *
	 * @return string
	 */
	public static function slug(): string {
		return 'cta';
	}

	/**
	 * Registers controls.
	 *
	 * @return void
	 */
	protected function register_controls(): void {
		$this->start_controls_section( 'content', array( 'label' => __( 'Content', 'pixelomatic-core' ) ) );

		$this->add_control(
			'title',
			array(
				'label'       => __( 'Title', 'pixelomatic-core' ),
				'type'        => Controls_Manager::TEXTAREA,
				'rows'        => 2,
				'default'     => __( 'Start with a product your team can ship this week', 'pixelomatic-core' ),
				'label_block' => true,
			)
		);

		$this->add_control(
			'title_tag',
			array(
				'label'   => __( 'Title tag', 'pixelomatic-core' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'h2',
				'options' => array(
					'h2' => 'H2',
					'h3' => 'H3',
					'p'  => __( 'Paragraph', 'pixelomatic-core' ),
				),
			)
		);

		$this->add_control(
			'text',
			array(
				'label'       => __( 'Text', 'pixelomatic-core' ),
				'type'        => Controls_Manager::TEXTAREA,
				'rows'        => 3,
				'label_block' => true,
			)
		);

		$this->add_control(
			'button_label',
			array(
				'label'   => __( 'Button label', 'pixelomatic-core' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'Browse products', 'pixelomatic-core' ),
			)
		);

		$this->add_control(
			'button_url',
			array(
				'label'       => __( 'Button link', 'pixelomatic-core' ),
				'type'        => Controls_Manager::URL,
				'label_block' => true,
			)
		);

		$this->add_control(
			'note',
			array(
				'label'       => __( 'Small print', 'pixelomatic-core' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
			)
		);

		$this->end_controls_section();

		$this->start_style_section( 'style_panel', __( 'Panel', 'pixelomatic-core' ) );

		$this->register_box_style(
			'panel',
			__( 'Panel', 'pixelomatic-core' ),
			'{{WRAPPER}} .cta',
			array( 'separator' => 'none' )
		);

		$this->register_gap_style( 'panel_gap', __( 'Copy and actions gap', 'pixelomatic-core' ), '{{WRAPPER}} .cta', 64 );

		$this->end_controls_section();

		$this->start_style_section( 'style_text', __( 'Text', 'pixelomatic-core' ) );

		$this->register_text_style(
			'cta_title',
			__( 'Title', 'pixelomatic-core' ),
			'{{WRAPPER}} .cta .section-title',
			array(
				'separator' => 'none',
				'align'     => true,
			)
		);

		$this->register_text_style(
			'cta_text',
			__( 'Text', 'pixelomatic-core' ),
			'{{WRAPPER}} .cta .section-intro',
			array(
				'align'   => true,
				'spacing' => false,
			)
		);

		$this->register_text_style(
			'cta_note',
			__( 'Small print', 'pixelomatic-core' ),
			'{{WRAPPER}} .cta__note',
			array(
				'align'   => true,
				'spacing' => false,
			)
		);

		$this->end_controls_section();

		$this->start_style_section( 'style_button', __( 'Button', 'pixelomatic-core' ) );

		$this->register_button_style(
			'cta_button',
			__( 'Button', 'pixelomatic-core' ),
			'{{WRAPPER}} .cta__actions .btn',
			array( 'separator' => 'none' )
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
		$title = $this->text( 'title' );
		$text  = $this->text( 'text' );
		$label = $this->text( 'button_label' );
		$note  = $this->text( 'note' );
		$link  = (array) ( $this->get_settings_for_display( 'button_url' ) ?? array() );
		$url   = (string) ( $link['url'] ?? '' );
		?>
		<section class="section">
			<div class="container section__inner section__inner--tight">
				<div class="cta">
					<div>
						<?php $this->render_heading( $title, (string) $this->get_settings_for_display( 'title_tag' ), 'section-title' ); ?>
						<?php if ( '' !== $text ) : ?>
							<p class="section-intro"><?php echo esc_html( $text ); ?></p>
						<?php endif; ?>
					</div>

					<div class="cta__actions">
						<?php if ( '' !== $label && '' !== $url ) : ?>
							<a class="btn btn--primary btn--lg"
								href="<?php echo esc_url( $url ); ?>"
								<?php echo ! empty( $link['is_external'] ) ? 'target="_blank" rel="noopener noreferrer"' : ''; ?>>
								<?php echo esc_html( $label ); ?>
							</a>
						<?php endif; ?>

						<?php if ( '' !== $note ) : ?>
							<p class="cta__note"><?php echo esc_html( $note ); ?></p>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</section>
		<?php
	}
}
