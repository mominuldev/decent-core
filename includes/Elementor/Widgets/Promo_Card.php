<?php
/**
 * Promo Card widget.
 *
 * @package PixelomaticCore
 */

namespace PixelomaticCore\Elementor\Widgets;

defined( 'ABSPATH' ) || exit;

use PixelomaticCore\Elementor\Base\Traits\Has_Style_Controls;
use PixelomaticCore\Elementor\Base\Widget_Base;
use Elementor\Controls_Manager;

/**
 * A compact bundle or offer panel.
 */
final class Promo_Card extends Widget_Base {

	use Has_Style_Controls;

	/**
	 * Widget slug.
	 *
	 * @return string
	 */
	public static function slug(): string {
		return 'promo-card';
	}

	/**
	 * Registers controls.
	 *
	 * @return void
	 */
	protected function register_controls(): void {
		$this->start_controls_section( 'content', array( 'label' => __( 'Content', 'pixelomatic-core' ) ) );

		$this->add_control(
			'icon',
			array(
				'label'   => __( 'Icon', 'pixelomatic-core' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'tag',
				'options' => self::icon_options(),
			)
		);

		$this->add_control(
			'title',
			array(
				'label'       => __( 'Title', 'pixelomatic-core' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Bundle and save 40%', 'pixelomatic-core' ),
				'label_block' => true,
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
				'default' => __( 'See the bundle', 'pixelomatic-core' ),
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
			'variant',
			array(
				'label'   => __( 'Variant', 'pixelomatic-core' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'light',
				'options' => array(
					'light' => __( 'Light', 'pixelomatic-core' ),
					'dark'  => __( 'Dark', 'pixelomatic-core' ),
				),
			)
		);

		$this->end_controls_section();

		$this->start_style_section( 'style_card', __( 'Card', 'pixelomatic-core' ) );

		$this->register_box_style(
			'promo',
			__( 'Card', 'pixelomatic-core' ),
			'{{WRAPPER}} .promo',
			array( 'separator' => 'none' )
		);

		$this->register_alignment_style( 'promo_align', '{{WRAPPER}} .promo' );

		$this->register_icon_style(
			'promo_icon',
			__( 'Icon', 'pixelomatic-core' ),
			'{{WRAPPER}} .promo > svg',
			array(
				'svg_selector' => '{{WRAPPER}} .promo > svg',
				'box'          => false,
			)
		);

		$this->register_text_style( 'promo_title', __( 'Title', 'pixelomatic-core' ), '{{WRAPPER}} .promo h3' );

		$this->register_text_style( 'promo_text', __( 'Text', 'pixelomatic-core' ), '{{WRAPPER}} .promo p' );

		$this->register_button_style( 'promo_button', __( 'Button', 'pixelomatic-core' ), '{{WRAPPER}} .promo .btn' );

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
		$link  = (array) ( $this->get_settings_for_display( 'button_url' ) ?? array() );
		$url   = (string) ( $link['url'] ?? '' );
		$dark  = 'dark' === (string) $this->get_settings_for_display( 'variant' );
		?>
		<div class="promo<?php echo $dark ? ' promo--dark' : ''; ?>">
			<?php $this->icon( (string) $this->get_settings_for_display( 'icon' ), 22, 1.6 ); ?>

			<?php if ( '' !== $title ) : ?>
				<h3><?php echo esc_html( $title ); ?></h3>
			<?php endif; ?>

			<?php if ( '' !== $text ) : ?>
				<p><?php echo esc_html( $text ); ?></p>
			<?php endif; ?>

			<?php if ( '' !== $label && '' !== $url ) : ?>
				<a class="btn <?php echo $dark ? 'btn--primary' : 'btn--white'; ?>"
					href="<?php echo esc_url( $url ); ?>"
					<?php echo ! empty( $link['is_external'] ) ? 'target="_blank" rel="noopener noreferrer"' : ''; ?>>
					<?php echo esc_html( $label ); ?>
				</a>
			<?php endif; ?>
		</div>
		<?php
	}
}
