<?php
/**
 * Section head controls.
 *
 * @package PixelomaticCore
 */

namespace PixelomaticCore\Elementor\Base\Traits;

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
	 * @param string $default_title   Default heading text.
	 * @param string $default_eyebrow Default eyebrow text.
	 * @param string $default_intro   Default supporting paragraph.
	 * @return void
	 */
	protected function register_section_head_controls( string $default_title = '', string $default_eyebrow = '', string $default_intro = '' ): void {
		$this->add_control(
			'eyebrow',
			array(
				'label'       => __( 'Eyebrow', 'pixelomatic-core' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => $default_eyebrow,
				'label_block' => true,
			)
		);

		$this->add_control(
			'title',
			array(
				'label'       => __( 'Title', 'pixelomatic-core' ),
				'type'        => Controls_Manager::TEXTAREA,
				'rows'        => 2,
				'default'     => $default_title,
				'label_block' => true,
			)
		);

		$this->add_control(
			'title_tag',
			array(
				'label'   => __( 'Title tag', 'pixelomatic-core' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'h2',
				'options' => array(
					'h1' => 'H1',
					'h2' => 'H2',
					'h3' => 'H3',
					'h4' => 'H4',
					'p'  => __( 'Paragraph', 'pixelomatic-core' ),
				),
			)
		);

		$this->add_control(
			'intro',
			array(
				'label'       => __( 'Intro', 'pixelomatic-core' ),
				'type'        => Controls_Manager::TEXTAREA,
				'rows'        => 3,
				'default'     => $default_intro,
				'label_block' => true,
			)
		);
	}

	/**
	 * Registers the optional trailing link.
	 *
	 * Opt-in, and separate from register_section_head_controls(): the design
	 * puts an "All 1,240 products →" link at the end of some section heads and
	 * not others, and a widget that has no catalogue to point at should not
	 * offer the control at all. A widget that never calls this renders no link,
	 * because the settings it reads simply do not exist.
	 *
	 * @param string $default_label Default link text.
	 * @return void
	 */
	protected function register_section_head_link_controls( string $default_label = '' ): void {
		$this->add_control(
			'head_link_label',
			array(
				'label'       => __( 'Trailing link', 'pixelomatic-core' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => $default_label,
				'description' => __( 'Sits at the end of the head. Needs a link to render.', 'pixelomatic-core' ),
				'label_block' => true,
				'separator'   => 'before',
			)
		);

		$this->add_control(
			'head_link_url',
			array(
				'label'     => __( 'Trailing link URL', 'pixelomatic-core' ),
				'type'      => Controls_Manager::URL,
				'condition' => array( 'head_link_label!' => '' ),
			)
		);
	}

	/**
	 * Renders the section head.
	 *
	 * @param array<string, string> $args Optional modifiers on the eyebrow and
	 *                                    the intro — `eyebrow_class` and
	 *                                    `intro_class`, both from the theme's
	 *                                    own set. Empty for the fourteen
	 *                                    widgets that embed a plain head.
	 * @return void
	 */
	protected function render_section_head( array $args = array() ): void {
		$eyebrow = $this->text( 'eyebrow' );
		$title   = $this->text( 'title' );
		$intro   = $this->text( 'intro' );
		$link    = $this->section_head_link();

		if ( '' === $eyebrow && '' === $title && '' === $intro && null === $link ) {
			return;
		}

		$eyebrow_class = trim( 'eyebrow ' . ( $args['eyebrow_class'] ?? '' ) );
		$intro_class   = trim( 'section-intro ' . ( $args['intro_class'] ?? '' ) );
		?>
		<div class="pix-section-heading">
			<div>
				<?php if ( '' !== $eyebrow ) : ?>
					<p class="<?php echo esc_attr( $eyebrow_class ); ?>"><?php echo esc_html( $eyebrow ); ?></p>
				<?php endif; ?>

				<?php $this->render_heading( $title, (string) $this->get_settings_for_display( 'title_tag' ), 'section-title' ); ?>

				<?php if ( '' !== $intro ) : ?>
					<p class="<?php echo esc_attr( $intro_class ); ?>"><?php echo esc_html( $intro ); ?></p>
				<?php endif; ?>
			</div>

			<?php if ( null !== $link ) : ?>
				<?php
				// The arrow is the design's, and it is decoration: the link
				// text already says where it goes.
				printf(
					'<a class="link-arrow pix-section-heading__aside" href="%1$s"%2$s>%3$s <span aria-hidden="true">&rarr;</span></a>',
					esc_url( $link['url'] ),
					$link['target'], // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Fixed attribute string.
					esc_html( $link['label'] )
				);
				?>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Resolves the trailing link, or null when the widget has none.
	 *
	 * @return array{label: string, url: string, target: string}|null
	 */
	private function section_head_link(): ?array {
		$label = $this->text( 'head_link_label' );
		$link  = (array) ( $this->get_settings_for_display( 'head_link_url' ) ?? array() );
		$url   = (string) ( $link['url'] ?? '' );

		if ( '' === $label || '' === $url ) {
			return null;
		}

		return array(
			'label'  => $label,
			'url'    => $url,
			'target' => ! empty( $link['is_external'] ) ? ' target="_blank" rel="noopener noreferrer"' : '',
		);
	}
}
