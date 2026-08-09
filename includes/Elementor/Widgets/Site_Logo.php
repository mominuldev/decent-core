<?php
/**
 * Site Logo widget.
 *
 * @package DecentCore
 */

namespace DecentCore\Elementor\Widgets;

defined( 'ABSPATH' ) || exit;

use DecentCore\Elementor\Base\Widget_Base;
use Elementor\Controls_Manager;

/**
 * The site's brand mark, for header templates.
 */
final class Site_Logo extends Widget_Base {

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
		$this->start_controls_section( 'content', array( 'label' => __( 'Logo', 'decent-core' ) ) );

		$this->add_control(
			'source',
			array(
				'label'   => __( 'Source', 'decent-core' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'site',
				'options' => array(
					'site'   => __( 'Site logo (Customizer)', 'decent-core' ),
					'custom' => __( 'Custom image', 'decent-core' ),
					'text'   => __( 'Text mark', 'decent-core' ),
				),
			)
		);

		$this->add_control(
			'image',
			array(
				'label'     => __( 'Image', 'decent-core' ),
				'type'      => Controls_Manager::MEDIA,
				'condition' => array( 'source' => 'custom' ),
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
