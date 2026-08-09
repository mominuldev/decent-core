<?php
/**
 * Newsletter widget.
 *
 * @package DecentCore
 */

namespace DecentCore\Elementor\Widgets;

defined( 'ABSPATH' ) || exit;

use DecentCore\Elementor\Base\Widget_Base;
use Elementor\Controls_Manager;

/**
 * An email signup that posts to a real endpoint.
 *
 * If no endpoint is configured the widget renders nothing rather than a form
 * that silently discards addresses — the static template's demo handler called
 * preventDefault(), which is fine for a mockup and dishonest on a live site.
 */
final class Newsletter extends Widget_Base {

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
		$this->start_controls_section( 'content', array( 'label' => __( 'Content', 'decent-core' ) ) );

		$this->add_control(
			'action',
			array(
				'label'       => __( 'Form action URL', 'decent-core' ),
				'type'        => Controls_Manager::URL,
				'label_block' => true,
				'description' => __( 'Your provider\'s form endpoint. Without it the widget renders nothing rather than a form that throws addresses away.', 'decent-core' ),
			)
		);

		$this->add_control(
			'field_name',
			array(
				'label'       => __( 'Email field name', 'decent-core' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => 'EMAIL',
				'description' => __( 'Mailchimp uses EMAIL; other providers differ.', 'decent-core' ),
			)
		);

		$this->add_control(
			'label',
			array(
				'label'       => __( 'Label', 'decent-core' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Product updates, twice a month', 'decent-core' ),
				'label_block' => true,
			)
		);

		$this->add_control(
			'button_label',
			array(
				'label'   => __( 'Button label', 'decent-core' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'Subscribe', 'decent-core' ),
			)
		);

		$this->add_control(
			'note',
			array(
				'label'       => __( 'Small print', 'decent-core' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'New releases and changelogs only. Unsubscribe anytime.', 'decent-core' ),
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
		$link   = (array) ( $this->get_settings_for_display( 'action' ) ?? array() );
		$action = (string) ( $link['url'] ?? '' );

		if ( '' === $action ) {
			return;
		}

		$field = $this->text( 'field_name', 'EMAIL' );
		$label = $this->text( 'label' );
		$note  = $this->text( 'note' );
		$id    = 'decent-newsletter-' . $this->get_id();
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
					placeholder="<?php esc_attr_e( 'you@company.com', 'decent-core' ); ?>">

				<button class="btn btn--dark" type="submit">
					<?php echo esc_html( $this->text( 'button_label', __( 'Subscribe', 'decent-core' ) ) ); ?>
				</button>
			</div>

			<?php if ( '' !== $note ) : ?>
				<p class="newsletter__note"><?php echo esc_html( $note ); ?></p>
			<?php endif; ?>
		</form>
		<?php
	}
}
