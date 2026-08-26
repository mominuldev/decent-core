<?php
/**
 * Button widget.
 *
 * One `.btn` — the theme's own button component, not a second one this widget
 * invents. The Style select is the whole of the theme's `.btn--*` set (Figma's
 * "Interaction states" frame documents Primary, Secondary and Ghost as the
 * button component proper; the stylesheet carries four more for context — a
 * dark band, a photo, a destructive action — and all eight are offered here
 * rather than picking a subset the next section inevitably needs). Hover,
 * active, focus and disabled are the stylesheet's `:hover`, `:active`,
 * `:focus-visible` and `[disabled]` rules and are not settings — a state an
 * editor could toggle open on a published page is not a state.
 *
 * @package PixelomaticCore
 */

namespace PixelomaticCore\Elementor\Widgets;

defined( 'ABSPATH' ) || exit;

use PixelomaticCore\Elementor\Base\Traits\Has_Style_Controls;
use PixelomaticCore\Elementor\Base\Widget_Base;
use Elementor\Controls_Manager;

/**
 * A single button.
 */
final class Button extends Widget_Base {

	use Has_Style_Controls;

	/**
	 * Widget slug.
	 *
	 * @return string
	 */
	public static function slug(): string {
		return 'button';
	}

	/**
	 * Registers the panel controls.
	 *
	 * @return void
	 */
	protected function register_controls(): void {
		$this->register_content_controls();
		$this->register_style_controls();
	}

	/**
	 * The Content tab.
	 *
	 * @return void
	 */
	private function register_content_controls(): void {
		$this->start_controls_section( 'content', array( 'label' => __( 'Content', 'pixelomatic-core' ) ) );

		$this->add_control(
			'text',
			array(
				'label'       => __( 'Text', 'pixelomatic-core' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Buy now', 'pixelomatic-core' ),
				'label_block' => true,
			)
		);

		$this->add_control(
			'link',
			array(
				'label'       => __( 'Link', 'pixelomatic-core' ),
				'type'        => Controls_Manager::URL,
				'label_block' => true,
				'default'     => array( 'url' => '#' ),
			)
		);

		$this->add_control(
			'icon',
			array(
				'label' => __( 'Icon', 'pixelomatic-core' ),
				'type'  => Controls_Manager::ICONS,
			)
		);

		$this->add_control(
			'icon_position',
			array(
				'label'     => __( 'Icon position', 'pixelomatic-core' ),
				'type'      => Controls_Manager::CHOOSE,
				'default'   => 'after',
				'options'   => array(
					'before' => array(
						'title' => __( 'Before', 'pixelomatic-core' ),
						'icon'  => 'eicon-h-align-left',
					),
					'after'  => array(
						'title' => __( 'After', 'pixelomatic-core' ),
						'icon'  => 'eicon-h-align-right',
					),
				),
				'condition' => array( 'icon[value]!' => '' ),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * The Style tab.
	 *
	 * @return void
	 */
	private function register_style_controls(): void {
		$this->start_style_section( 'style_button', __( 'Button', 'pixelomatic-core' ) );

		$this->add_control(
			'style',
			array(
				'label'   => __( 'Style', 'pixelomatic-core' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'primary',
				'options' => array(
					'primary'       => __( 'Primary', 'pixelomatic-core' ),
					'gradient'      => __( 'Gradient', 'pixelomatic-core' ),
					'secondary'     => __( 'Secondary', 'pixelomatic-core' ),
					'dark'          => __( 'Dark', 'pixelomatic-core' ),
					'outline-light' => __( 'Outline (on dark)', 'pixelomatic-core' ),
					'white'         => __( 'White (on colour)', 'pixelomatic-core' ),
					'ghost'         => __( 'Ghost', 'pixelomatic-core' ),
					'danger'        => __( 'Danger', 'pixelomatic-core' ),
				),
			)
		);

		$this->add_control(
			'size',
			array(
				'label'   => __( 'Size', 'pixelomatic-core' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'default',
				'options' => array(
					'sm'      => __( 'Small', 'pixelomatic-core' ),
					'default' => __( 'Default', 'pixelomatic-core' ),
					'lg'      => __( 'Large', 'pixelomatic-core' ),
				),
			)
		);

		$this->add_control(
			'full_width',
			array(
				'label'        => __( 'Full width', 'pixelomatic-core' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
			)
		);

		$this->register_alignment_style( 'align', '{{WRAPPER}}' );

		$this->register_button_style(
			'button',
			__( 'Colours', 'pixelomatic-core' ),
			'{{WRAPPER}} .btn'
		);

		$this->register_gap_style( 'icon_gap', __( 'Icon gap', 'pixelomatic-core' ), '{{WRAPPER}} .btn', 32 );

		$this->end_controls_section();

		$this->start_style_section(
			'style_icon',
			__( 'Icon', 'pixelomatic-core' ),
			array( 'condition' => array( 'icon[value]!' => '' ) )
		);

		$this->add_control(
			'icon_color',
			array(
				'label'     => __( 'Icon colour', 'pixelomatic-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pix-button__icon' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'icon_size',
			array(
				'label'      => __( 'Icon size', 'pixelomatic-core' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min'  => 10,
						'max'  => 32,
						'step' => 1,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .pix-button__icon' => 'font-size: {{SIZE}}{{UNIT}};',
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
		$text = $this->text( 'text' );
		$link = (array) ( $this->get_settings_for_display( 'link' ) ?? array() );
		$url  = (string) ( $link['url'] ?? '' );

		if ( '' === $text || '' === $url ) {
			return;
		}

		$settings = $this->get_settings_for_display();
		$icon     = $this->has_picked_icon( 'icon' );
		$before   = $icon && 'before' === ( $settings['icon_position'] ?? 'after' );
		$after    = $icon && ! $before;

		$classes = array( 'btn', 'btn--' . (string) $settings['style'] );

		if ( 'default' !== $settings['size'] ) {
			$classes[] = 'btn--' . (string) $settings['size'];
		}

		if ( 'yes' === ( $settings['full_width'] ?? '' ) ) {
			$classes[] = 'btn--block';
		}
		?>
		<a
			class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>"
			href="<?php echo esc_url( $url ); ?>"
			<?php echo $this->link_attributes( $link ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Built and escaped in link_attributes(). ?>
		>
			<?php if ( $before ) : ?>
				<span class="pix-button__icon"><?php $this->render_picked_icon( 'icon' ); ?></span>
			<?php endif; ?>
			<?php echo esc_html( $text ); ?>
			<?php if ( $after ) : ?>
				<span class="pix-button__icon"><?php $this->render_picked_icon( 'icon' ); ?></span>
			<?php endif; ?>
		</a>
		<?php
	}
}
