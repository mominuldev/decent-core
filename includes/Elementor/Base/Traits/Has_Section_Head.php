<?php
/**
 * Section head controls.
 *
 * @package DecentCore
 */

namespace DecentCore\Elementor\Base\Traits;

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;

/**
 * The eyebrow / title / intro block every section in the design opens with.
 *
 * Shared rather than repeated: fourteen widgets need it, and fourteen copies
 * would be fourteen places for the heading level allow-list to drift.
 */
trait Has_Section_Head {

	/**
	 * Registers the section head controls.
	 *
	 * @param string $default_title Default heading text.
	 * @param string $default_eyebrow Default eyebrow text.
	 * @return void
	 */
	protected function register_section_head_controls( string $default_title = '', string $default_eyebrow = '' ): void {
		$this->add_control(
			'eyebrow',
			array(
				'label'       => __( 'Eyebrow', 'decent-core' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => $default_eyebrow,
				'label_block' => true,
			)
		);

		$this->add_control(
			'title',
			array(
				'label'       => __( 'Title', 'decent-core' ),
				'type'        => Controls_Manager::TEXTAREA,
				'rows'        => 2,
				'default'     => $default_title,
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
					'h1' => 'H1',
					'h2' => 'H2',
					'h3' => 'H3',
					'h4' => 'H4',
					'p'  => __( 'Paragraph', 'decent-core' ),
				),
			)
		);

		$this->add_control(
			'intro',
			array(
				'label'       => __( 'Intro', 'decent-core' ),
				'type'        => Controls_Manager::TEXTAREA,
				'rows'        => 3,
				'label_block' => true,
			)
		);
	}

	/**
	 * Renders the section head.
	 *
	 * @return void
	 */
	protected function render_section_head(): void {
		$eyebrow = $this->text( 'eyebrow' );
		$title   = $this->text( 'title' );
		$intro   = $this->text( 'intro' );

		if ( '' === $eyebrow && '' === $title && '' === $intro ) {
			return;
		}
		?>
		<div class="section-head">
			<div>
				<?php if ( '' !== $eyebrow ) : ?>
					<p class="eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
				<?php endif; ?>

				<?php $this->render_heading( $title, (string) $this->get_settings_for_display( 'title_tag' ), 'section-title' ); ?>

				<?php if ( '' !== $intro ) : ?>
					<p class="section-intro"><?php echo esc_html( $intro ); ?></p>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}
}
