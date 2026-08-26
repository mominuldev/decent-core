<?php
/**
 * Accordion widget.
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
 * A standalone, general-purpose accordion.
 *
 * Independent of the theme's own `.accordion` component — this renders its
 * own `pix-` markup so it can carry a smooth open/close transition (a CSS
 * grid-rows tween, driven by a class the script toggles) without touching
 * the theme's shared FAQ accordion. Every panel ships open in the markup and
 * JavaScript closes the inactive ones on init, so content stays readable
 * with JavaScript off.
 */
final class Accordion extends Widget_Base {

	use Has_Style_Controls;

	/**
	 * Widget slug.
	 *
	 * @return string
	 */
	public static function slug(): string {
		return 'accordion';
	}

	/**
	 * Registers controls.
	 *
	 * @return void
	 */
	protected function register_controls(): void {
		$this->start_controls_section( 'items', array( 'label' => __( 'Items', 'pixelomatic-core' ) ) );

		$repeater = new Repeater();

		$repeater->add_control(
			'title',
			array(
				'label'       => __( 'Title', 'pixelomatic-core' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Accordion item', 'pixelomatic-core' ),
				'label_block' => true,
			)
		);

		$repeater->add_control(
			'content',
			array(
				'label'       => __( 'Content', 'pixelomatic-core' ),
				'type'        => Controls_Manager::TEXTAREA,
				'rows'        => 4,
				'label_block' => true,
			)
		);

		$repeater->add_control(
			'active',
			array(
				'label'        => __( 'Open by default', 'pixelomatic-core' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'entries',
			array(
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'title_field' => '{{{ title }}}',
				'default'     => array(
					array(
						'title'   => __( 'Are updates included in the price?', 'pixelomatic-core' ),
						'content' => __( 'Yes. Every purchase includes lifetime updates for that product — new versions appear in your account under Downloads, and you can subscribe to a changelog feed per product so you know what changed before you update.', 'pixelomatic-core' ),
						'active'  => 'yes',
					),
					array(
						'title'   => __( 'Do products include support?', 'pixelomatic-core' ),
						'content' => __( 'Yes, every purchase includes support directly from the author for the length of your licence.', 'pixelomatic-core' ),
					),
					array(
						'title'   => __( 'Can I use a product on client projects?', 'pixelomatic-core' ),
						'content' => __( 'A regular licence covers one end product you do not charge for; an extended licence covers a paid end product and client resale.', 'pixelomatic-core' ),
					),
					array(
						'title'   => __( 'What is your refund policy?', 'pixelomatic-core' ),
						'content' => __( 'A full refund is available within 14 days of purchase if the product does not work as described.', 'pixelomatic-core' ),
					),
				),
			)
		);

		$this->add_control(
			'allow_multiple',
			array(
				'label'        => __( 'Allow multiple panels open', 'pixelomatic-core' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'separator'    => 'before',
			)
		);

		$this->end_controls_section();

		$this->start_style_section( 'style_row', __( 'Row', 'pixelomatic-core' ) );

		$this->register_box_style(
			'item',
			__( 'Row', 'pixelomatic-core' ),
			'{{WRAPPER}} .pix-accordion__item',
			array(
				'separator' => 'none',
				'shadow'    => false,
			)
		);

		$this->end_controls_section();

		$this->start_style_section( 'style_trigger', __( 'Title', 'pixelomatic-core' ) );

		$this->register_link_style(
			'trigger',
			__( 'Title text', 'pixelomatic-core' ),
			'{{WRAPPER}} .pix-accordion__trigger'
		);

		$this->register_icon_style(
			'icon',
			__( 'Icon', 'pixelomatic-core' ),
			'{{WRAPPER}} .pix-accordion__icon',
			array( 'box' => false )
		);

		$this->end_controls_section();

		$this->start_style_section( 'style_body', __( 'Content', 'pixelomatic-core' ) );

		$this->register_text_style(
			'body',
			__( 'Content text', 'pixelomatic-core' ),
			'{{WRAPPER}} .pix-accordion__body',
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
		$items = (array) ( $this->get_settings_for_display( 'entries' ) ?? array() );

		if ( empty( $items ) ) {
			return;
		}

		$settings       = $this->get_settings_for_display();
		$allow_multiple = 'yes' === ( $settings['allow_multiple'] ?? '' );
		$uid            = $this->get_id();
		?>
		<div class="pix-accordion" data-accordion <?php echo $allow_multiple ? 'data-accordion-multiple' : ''; ?>>
			<?php foreach ( $items as $index => $item ) : ?>
				<?php
				$panel_id   = 'pix-accordion-panel-' . $uid . '-' . $index;
				$trigger_id = 'pix-accordion-trigger-' . $uid . '-' . $index;
				$open       = 'yes' === ( $item['active'] ?? '' );
				?>
				<div class="pix-accordion__item">
					<h3 class="pix-accordion__heading">
						<button
							type="button"
							class="pix-accordion__trigger"
							id="<?php echo esc_attr( $trigger_id ); ?>"
							aria-controls="<?php echo esc_attr( $panel_id ); ?>"
							aria-expanded="<?php echo $open ? 'true' : 'false'; ?>"
							data-accordion-trigger
						>
							<span class="pix-accordion__title"><?php echo esc_html( (string) ( $item['title'] ?? '' ) ); ?></span>
							<span class="pix-accordion__icon" data-accordion-icon><?php $this->icon( 'chevron-down', 20 ); ?></span>
						</button>
					</h3>

					<div
						class="pix-accordion__panel"
						id="<?php echo esc_attr( $panel_id ); ?>"
						role="region"
						aria-labelledby="<?php echo esc_attr( $trigger_id ); ?>"
						data-accordion-panel
					>
						<div class="pix-accordion__panel-inner">
							<p class="pix-accordion__body"><?php echo esc_html( (string) ( $item['content'] ?? '' ) ); ?></p>
						</div>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
		<?php
	}
}
