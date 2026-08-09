<?php
/**
 * CTA widget.
 *
 * @package DecentCore
 */

namespace DecentCore\Elementor\Widgets;

defined( 'ABSPATH' ) || exit;

use DecentCore\Elementor\Base\Widget_Base;
use Elementor\Controls_Manager;

/**
 * A closing call to action.
 */
final class Cta extends Widget_Base {

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
		$this->start_controls_section( 'content', array( 'label' => __( 'Content', 'decent-core' ) ) );

		$this->add_control(
			'title',
			array(
				'label'       => __( 'Title', 'decent-core' ),
				'type'        => Controls_Manager::TEXTAREA,
				'rows'        => 2,
				'default'     => __( 'Start with a product your team can ship this week', 'decent-core' ),
				'label_block' => true,
			)
		);

		$this->add_control(
			'title_tag',
			array(
				'label'   => __( 'Title tag', 'decent-core' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'h2',
				'options' => array(
					'h2' => 'H2',
					'h3' => 'H3',
					'p'  => __( 'Paragraph', 'decent-core' ),
				),
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
				'default' => __( 'Browse products', 'decent-core' ),
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
			'note',
			array(
				'label'       => __( 'Small print', 'decent-core' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
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
