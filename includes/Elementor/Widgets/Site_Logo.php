<?php
/**
 * Site Logo widget.
 *
 * @package PixelomaticCore
 */

namespace PixelomaticCore\Elementor\Widgets;

defined( 'ABSPATH' ) || exit;

use PixelomaticCore\Elementor\Base\Traits\Has_Style_Controls;
use PixelomaticCore\Elementor\Base\Widget_Base;
use Elementor\Controls_Manager;

/**
 * The site's brand mark, for header templates.
 */
final class Site_Logo extends Widget_Base {

	use Has_Style_Controls;

	/**
	 * Widget slug.
	 *
	 * @return string
	 */
	public static function slug(): string {
		return 'site-logo';
	}

	/**
	 * Registers controls.
	 *
	 * @return void
	 */
	protected function register_controls(): void {
		$this->start_controls_section( 'content', array( 'label' => __( 'Logo', 'pixelomatic-core' ) ) );

		$this->add_control(
			'source',
			array(
				'label'   => __( 'Source', 'pixelomatic-core' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'site',
				'options' => array(
					'site'   => __( 'Site logo (Customizer)', 'pixelomatic-core' ),
					'custom' => __( 'Custom image', 'pixelomatic-core' ),
					'text'   => __( 'Text mark', 'pixelomatic-core' ),
				),
			)
		);

		$this->add_control(
			'image',
			array(
				'label'     => __( 'Image', 'pixelomatic-core' ),
				'type'      => Controls_Manager::MEDIA,
				'condition' => array( 'source' => 'custom' ),
			)
		);

		$this->end_controls_section();

		$this->start_style_section( 'style_logo', __( 'Logo', 'pixelomatic-core' ) );

		$this->add_responsive_control(
			'logo_width',
			array(
				'label'      => __( 'Image width', 'pixelomatic-core' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%' ),
				'range'      => array(
					'px' => array(
						'min'  => 40,
						'max'  => 400,
						'step' => 2,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} img' => 'width: {{SIZE}}{{UNIT}}; height: auto;',
				),
				'condition'  => array( 'source' => array( 'site', 'custom' ) ),
			)
		);

		$this->add_responsive_control(
			'logo_radius',
			array(
				'label'      => __( 'Image radius', 'pixelomatic-core' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'  => array( 'source' => array( 'site', 'custom' ) ),
			)
		);

		$this->end_controls_section();

		// The text mark is also the fallback when no image resolves, so these
		// are offered whatever the source is set to.
		$this->start_style_section( 'style_mark', __( 'Text mark', 'pixelomatic-core' ) );

		$this->register_box_style(
			'mark',
			__( 'Initials tile', 'pixelomatic-core' ),
			'{{WRAPPER}} .brand__mark',
			array(
				'separator' => 'none',
				'shadow'    => false,
			)
		);

		$this->register_text_style(
			'mark_text',
			__( 'Initials text', 'pixelomatic-core' ),
			'{{WRAPPER}} .brand__mark',
			array(
				'heading' => false,
				'spacing' => false,
			)
		);

		$this->register_text_style(
			'brand_name',
			__( 'Site name', 'pixelomatic-core' ),
			'{{WRAPPER}} .brand__name',
			array( 'spacing' => false )
		);

		$this->register_link_style( 'brand_link', __( 'Link', 'pixelomatic-core' ), '{{WRAPPER}} .brand' );

		$this->register_gap_style( 'brand_gap', __( 'Gap', 'pixelomatic-core' ), '{{WRAPPER}} .brand', 32 );

		$this->end_controls_section();
	}

	/**
	 * Renders the widget.
	 *
	 * @return void
	 */
	protected function render(): void {
		$source = (string) $this->get_settings_for_display( 'source' );
		$home   = home_url( '/' );
		$name   = get_bloginfo( 'name' );

		if ( 'site' === $source && has_custom_logo() ) {
			the_custom_logo();
			return;
		}

		if ( 'custom' === $source ) {
			$image = (array) ( $this->get_settings_for_display( 'image' ) ?? array() );

			if ( ! empty( $image['id'] ) ) {
				printf(
					'<a class="brand" href="%s" rel="home">%s</a>',
					esc_url( $home ),
					wp_get_attachment_image( (int) $image['id'], 'full', false, array( 'alt' => $name ) )
				);
				return;
			}
		}

		// The text mark is also the fallback: a header with no logo at all is
		// worse than a header with the site's initials in it.
		?>
		<a class="brand" href="<?php echo esc_url( $home ); ?>" rel="home">
			<span class="brand__mark" aria-hidden="true"><?php echo esc_html( mb_strtoupper( mb_substr( $name, 0, 2 ) ) ); ?></span>
			<span class="brand__name"><?php echo esc_html( $name ); ?></span>
		</a>
		<?php
	}
}
