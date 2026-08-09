<?php
/**
 * FAQ Accordion widget.
 *
 * @package DecentCore
 */

namespace DecentCore\Elementor\Widgets;

defined( 'ABSPATH' ) || exit;

use DecentCore\Elementor\Base\Traits\Has_Section_Head;
use DecentCore\Elementor\Base\Traits\Has_Style_Controls;
use DecentCore\Elementor\Base\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Repeater;

/**
 * Questions and answers.
 *
 * The first panel ships open and the rest ship visible; the theme's main.js
 * collapses them once it runs. With JavaScript off every answer is readable,
 * which is the same contract the theme's own accordion honours.
 */
final class Faq_Accordion extends Widget_Base {

	use Has_Section_Head;
	use Has_Style_Controls;

	/**
	 * Widget slug.
	 *
	 * @return string
	 */
	public static function slug(): string {
		return 'faq-accordion';
	}

	/**
	 * Registers controls.
	 *
	 * @return void
	 */
	protected function register_controls(): void {
		$this->start_controls_section( 'head', array( 'label' => __( 'Section head', 'decent-core' ) ) );
		$this->register_section_head_controls(
			__( 'Licensing, updates and support', 'decent-core' ),
			__( 'FAQ', 'decent-core' )
		);
		$this->end_controls_section();

		$this->start_controls_section( 'items', array( 'label' => __( 'Questions', 'decent-core' ) ) );

		$repeater = new Repeater();

		$repeater->add_control(
			'question',
			array(
				'label'       => __( 'Question', 'decent-core' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'What does the licence cover?', 'decent-core' ),
				'label_block' => true,
			)
		);

		$repeater->add_control(
			'answer',
			array(
				'label'       => __( 'Answer', 'decent-core' ),
				'type'        => Controls_Manager::TEXTAREA,
				'rows'        => 4,
				'label_block' => true,
			)
		);

		$this->add_control(
			'faqs',
			array(
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'title_field' => '{{{ question }}}',
				'default'     => array(
					array(
						'question' => __( 'What does a regular licence cover?', 'decent-core' ),
						'answer'   => __( 'One end product that you do not charge users to access. Extended licences cover a paid end product and client resale.', 'decent-core' ),
					),
					array(
						'question' => __( 'How long do updates last?', 'decent-core' ),
						'answer'   => __( 'For the life of the product. Version history and changelogs are in your dashboard so you can review a release before pushing it.', 'decent-core' ),
					),
				),
			)
		);

		$this->end_controls_section();

		$this->start_style_section( 'style_head', __( 'Section head', 'decent-core' ) );
		$this->register_section_head_style_controls();
		$this->end_controls_section();

		$this->start_style_section( 'style_question', __( 'Questions', 'decent-core' ) );

		$this->register_box_style(
			'trigger',
			__( 'Row', 'decent-core' ),
			'{{WRAPPER}} .accordion__trigger',
			array( 'separator' => 'none' )
		);

		$this->register_link_style(
			'trigger_text',
			__( 'Question text', 'decent-core' ),
			'{{WRAPPER}} .accordion__trigger'
		);

		$this->register_text_style(
			'trigger_mark',
			__( 'Plus and minus mark', 'decent-core' ),
			'{{WRAPPER}} .accordion__mark',
			array( 'spacing' => false )
		);

		$this->end_controls_section();

		$this->start_style_section( 'style_answer', __( 'Answers', 'decent-core' ) );

		$this->register_box_style(
			'panel',
			__( 'Panel', 'decent-core' ),
			'{{WRAPPER}} .accordion__panel',
			array(
				'separator' => 'none',
				'shadow'    => false,
			)
		);

		$this->register_text_style(
			'panel_text',
			__( 'Answer text', 'decent-core' ),
			'{{WRAPPER}} .accordion__panel p',
			array( 'spacing' => false )
		);

		$this->end_controls_section();

		$this->start_style_section( 'style_band', __( 'Band', 'decent-core' ) );

		$this->register_box_style(
			'band',
			__( 'Band', 'decent-core' ),
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
		$faqs = (array) ( $this->get_settings_for_display( 'faqs' ) ?? array() );

		if ( empty( $faqs ) ) {
			return;
		}

		$uid = $this->get_id();
		?>
		<section class="section">
			<div class="container section__inner section__inner--tight">
				<?php $this->render_section_head(); ?>

				<div class="accordion" data-accordion>
					<?php foreach ( $faqs as $index => $faq ) : ?>
						<?php
						$panel_id   = 'faq-' . $uid . '-' . $index;
						$trigger_id = 'faq-trigger-' . $uid . '-' . $index;
						$open       = 0 === $index;
						?>
						<h3>
							<button class="accordion__trigger"
								type="button"
								id="<?php echo esc_attr( $trigger_id ); ?>"
								aria-controls="<?php echo esc_attr( $panel_id ); ?>"
								aria-expanded="<?php echo $open ? 'true' : 'false'; ?>"
								data-accordion-trigger>
								<span><?php echo esc_html( (string) ( $faq['question'] ?? '' ) ); ?></span>
								<span class="accordion__mark" data-accordion-mark aria-hidden="true"><?php echo $open ? '&minus;' : '+'; ?></span>
							</button>
						</h3>

						<div class="accordion__panel"
							id="<?php echo esc_attr( $panel_id ); ?>"
							role="region"
							aria-labelledby="<?php echo esc_attr( $trigger_id ); ?>"
							<?php
							// Not [hidden]: every answer stays readable with
							// JavaScript off. main.js collapses them on load.
							echo $open ? '' : 'data-accordion-closed';
							?>
							>
							<p><?php echo esc_html( (string) ( $faq['answer'] ?? '' ) ); ?></p>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
		<?php
	}
}
