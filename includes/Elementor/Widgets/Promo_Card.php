<?php
/**
 * Promo Card widget.
 *
 * @package DecentCore
 */

namespace DecentCore\Elementor\Widgets;

defined( 'ABSPATH' ) || exit;

use DecentCore\Elementor\Base\Widget_Base;
use Elementor\Controls_Manager;

/**
 * A compact bundle or offer panel.
 */
final class Promo_Card extends Widget_Base {

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
		$this->start_controls_section( 'content', array( 'label' => __( 'Content', 'decent-core' ) ) );

		$this->add_control(
			'icon',
			array(
				'label'   => __( 'Icon', 'decent-core' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'tag',
				'options' => self::icon_options(),
			)
		);

		$this->add_control(
			'title',
			array(
				'label'       => __( 'Title', 'decent-core' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Bundle and save 40%', 'decent-core' ),
				'label_block' => true,
			)
		);

		$this->add_control(
			'text',
			array(
				'label'       => __( 'Text', 'decent-core' ),
				'type'        => Controls_Manager::TEXTAREA,
				'rows'        => 3,
				'label_block' => true,
			)
		);

		$this->add_control(
			'button_label',
			array(
				'label'   => __( 'Button label', 'decent-core' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'See the bundle', 'decent-core' ),
			)
		);

		$this->add_control(
			'button_url',
			array(
				'label'       => __( 'Button link', 'decent-core' ),
				'type'        => Controls_Manager::URL,
				'label_block' => true,
			)
		);

		$this->add_control(
			'variant',
			array(
				'label'   => __( 'Variant', 'decent-core' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'light',
				'options' => array(
					'light' => __( 'Light', 'decent-core' ),
					'dark'  => __( 'Dark', 'decent-core' ),
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
