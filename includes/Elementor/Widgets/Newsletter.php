<?php
/**
 * Newsletter widget.
 *
 * @package PixelomaticCore
 */

namespace PixelomaticCore\Elementor\Widgets;

defined( 'ABSPATH' ) || exit;

use PixelomaticCore\Elementor\Base\Traits\Has_Style_Controls;
use PixelomaticCore\Elementor\Base\Widget_Base;
use Elementor\Controls_Manager;

/**
 * An email signup that posts to a real endpoint.
 *
 * If no endpoint is configured the widget renders nothing rather than a form
 * that silently discards addresses — the static template's demo handler called
 * preventDefault(), which is fine for a mockup and dishonest on a live site.
 */
final class Newsletter extends Widget_Base {

	use Has_Style_Controls;

	/**
	 * Widget slug.
	 *
	 * @return string
	 */
	public static function slug(): string {
		return 'newsletter';
	}

	/**
	 * Registers controls.
	 *
	 * @return void
	 */
	protected function register_controls(): void {
		$this->start_controls_section( 'content', array( 'label' => __( 'Content', 'pixelomatic-core' ) ) );

		$this->add_control(
			'action',
			array(
				'label'       => __( 'Form action URL', 'pixelomatic-core' ),
				'type'        => Controls_Manager::URL,
				'label_block' => true,
				'description' => __( 'Your provider\'s form endpoint. Without it the widget renders nothing rather than a form that throws addresses away.', 'pixelomatic-core' ),
			)
		);

		$this->add_control(
			'field_name',
			array(
				'label'       => __( 'Email field name', 'pixelomatic-core' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => 'EMAIL',
				'description' => __( 'Mailchimp uses EMAIL; other providers differ.', 'pixelomatic-core' ),
			)
		);

		$this->add_control(
			'label',
			array(
				'label'       => __( 'Label', 'pixelomatic-core' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Product updates, twice a month', 'pixelomatic-core' ),
				'label_block' => true,
			)
		);

		$this->add_control(
			'button_label',
			array(
				'label'   => __( 'Button label', 'pixelomatic-core' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'Subscribe', 'pixelomatic-core' ),
			)
		);

		$this->add_control(
			'note',
			array(
				'label'       => __( 'Small print', 'pixelomatic-core' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'New releases and changelogs only. Unsubscribe anytime.', 'pixelomatic-core' ),
				'label_block' => true,
			)
		);

		$this->end_controls_section();

		$this->start_style_section( 'style_form', __( 'Form', 'pixelomatic-core' ) );

		$this->register_box_style(
			'form',
			__( 'Form', 'pixelomatic-core' ),
			'{{WRAPPER}} .newsletter',
			array(
				'separator' => 'none',
				'shadow'    => false,
			)
		);

		$this->register_text_style( 'label', __( 'Label', 'pixelomatic-core' ), '{{WRAPPER}} .newsletter__label' );

		$this->register_box_style(
			'field',
			__( 'Email field', 'pixelomatic-core' ),
			'{{WRAPPER}} .newsletter .input',
			array( 'shadow' => false )
		);

		$this->register_text_style(
			'field_text',
			__( 'Email field text', 'pixelomatic-core' ),
			'{{WRAPPER}} .newsletter .input',
			array(
				'heading' => false,
				'spacing' => false,
			)
		);

		$this->add_control(
			'field_placeholder_color',
			array(
				'label'     => __( 'Placeholder colour', 'pixelomatic-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .newsletter .input::placeholder' => 'color: {{VALUE}};',
				),
			)
		);

		$this->register_button_style( 'submit_button', __( 'Button', 'pixelomatic-core' ), '{{WRAPPER}} .newsletter .btn' );

		$this->register_gap_style( 'row_gap', __( 'Field and button gap', 'pixelomatic-core' ), '{{WRAPPER}} .newsletter__row', 32 );

		$this->register_text_style(
			'note',
			__( 'Small print', 'pixelomatic-core' ),
			'{{WRAPPER}} .newsletter__note',
			array( 'spacing' => false )
		);

		$this->end_controls_section();
	}

	/**
	 * Renders the widget.
	 *
	 * @return void
	 */
	protected function render(): void {
		$link   = (array) ( $this->get_settings_for_display( 'action' ) ?? array() );
		$action = (string) ( $link['url'] ?? '' );

		if ( '' === $action ) {
			return;
		}

		$field = $this->text( 'field_name', 'EMAIL' );
		$label = $this->text( 'label' );
		$note  = $this->text( 'note' );
		$id    = 'pixelomatic-newsletter-' . $this->get_id();
		?>
		<form class="newsletter" action="<?php echo esc_url( $action ); ?>" method="post" target="_blank" rel="noopener">
			<?php if ( '' !== $label ) : ?>
				<label class="newsletter__label" for="<?php echo esc_attr( $id ); ?>">
					<?php echo esc_html( $label ); ?>
				</label>
			<?php endif; ?>

			<div class="newsletter__row">
				<input class="input"
					id="<?php echo esc_attr( $id ); ?>"
					name="<?php echo esc_attr( $field ); ?>"
					type="email"
					autocomplete="email"
					required
					placeholder="<?php esc_attr_e( 'you@company.com', 'pixelomatic-core' ); ?>">

				<button class="btn btn--dark" type="submit">
					<?php echo esc_html( $this->text( 'button_label', __( 'Subscribe', 'pixelomatic-core' ) ) ); ?>
				</button>
			</div>

			<?php if ( '' !== $note ) : ?>
				<p class="newsletter__note"><?php echo esc_html( $note ); ?></p>
			<?php endif; ?>
		</form>
		<?php
	}
}
