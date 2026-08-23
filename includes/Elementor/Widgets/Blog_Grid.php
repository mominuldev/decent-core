<?php
/**
 * Blog Grid widget.
 *
 * @package PixelomaticCore
 */

namespace PixelomaticCore\Elementor\Widgets;

defined( 'ABSPATH' ) || exit;

use PixelomaticCore\Elementor\Base\Traits\Has_Grid_Controls;
use PixelomaticCore\Elementor\Base\Traits\Has_Section_Head;
use PixelomaticCore\Elementor\Base\Traits\Has_Style_Controls;
use PixelomaticCore\Elementor\Base\Widget_Base;
use Elementor\Controls_Manager;
use WP_Query;

/**
 * Recent posts, using the theme's own post card.
 */
final class Blog_Grid extends Widget_Base {

	use Has_Section_Head;
	use Has_Grid_Controls;
	use Has_Style_Controls;

	/**
	 * Widget slug.
	 *
	 * @return string
	 */
	public static function slug(): string {
		return 'blog-grid';
	}

	/**
	 * Registers controls.
	 *
	 * @return void
	 */
	protected function register_controls(): void {
		$this->start_controls_section( 'head', array( 'label' => __( 'Section head', 'pixelomatic-core' ) ) );
		$this->register_section_head_controls(
			__( 'From the journal', 'pixelomatic-core' ),
			__( 'Writing', 'pixelomatic-core' )
		);
		$this->end_controls_section();

		$this->start_controls_section( 'query', array( 'label' => __( 'Posts', 'pixelomatic-core' ) ) );

		$this->add_control(
			'count',
			array(
				'label'   => __( 'How many', 'pixelomatic-core' ),
				'type'    => Controls_Manager::NUMBER,
				'default' => 3,
				'min'     => 1,
				'max'     => 12,
			)
		);

		$this->add_control(
			'category',
			array(
				'label'       => __( 'Category', 'pixelomatic-core' ),
				'type'        => Controls_Manager::SELECT2,
				'multiple'    => true,
				'label_block' => true,
				'options'     => $this->category_options(),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'layout',
			array(
				'label' => __( 'Layout', 'pixelomatic-core' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);
		$this->register_grid_controls( 3 );
		$this->end_controls_section();

		$this->start_style_section( 'style_head', __( 'Section head', 'pixelomatic-core' ) );
		$this->register_section_head_style_controls();
		$this->end_controls_section();

		// The card itself is the theme's, shared with the blog archive. These
		// selectors name its classes rather than reimplementing it, and they are
		// scoped to this widget, so restyling it here cannot reach the archive.
		$this->start_style_section( 'style_card', __( 'Post cards', 'pixelomatic-core' ) );

		$this->register_box_style(
			'card',
			__( 'Card', 'pixelomatic-core' ),
			'{{WRAPPER}} .post-card',
			array( 'separator' => 'none' )
		);

		$this->add_responsive_control(
			'card_media_height',
			array(
				'label'      => __( 'Image height', 'pixelomatic-core' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min'  => 80,
						'max'  => 400,
						'step' => 4,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .post-card__media'     => 'height: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .post-card__media img' => 'height: 100%; object-fit: cover;',
				),
			)
		);

		$this->register_text_style( 'card_type', __( 'Category line', 'pixelomatic-core' ), '{{WRAPPER}} .post-card__type' );

		$this->register_text_style( 'card_title', __( 'Title', 'pixelomatic-core' ), '{{WRAPPER}} .post-card__title' );

		$this->register_link_style(
			'card_title_link',
			__( 'Title link', 'pixelomatic-core' ),
			'{{WRAPPER}} .post-card__title a'
		);

		$this->register_text_style( 'card_desc', __( 'Excerpt', 'pixelomatic-core' ), '{{WRAPPER}} .post-card__desc' );

		$this->register_text_style(
			'card_foot',
			__( 'Footer', 'pixelomatic-core' ),
			'{{WRAPPER}} .post-card__foot',
			array( 'spacing' => false )
		);

		$this->end_controls_section();

		$this->start_style_section( 'style_band', __( 'Band', 'pixelomatic-core' ) );

		$this->register_box_style(
			'band',
			__( 'Band', 'pixelomatic-core' ),
			'{{WRAPPER}} .section',
			array(
				'heading' => false,
				'shadow'  => false,
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Returns post categories for the picker.
	 *
	 * @return array<int, string>
	 */
	private function category_options(): array {
		$terms = get_terms(
			array(
				'taxonomy'   => 'category',
				'hide_empty' => false,
			)
		);

		if ( is_wp_error( $terms ) ) {
			return array();
		}

		$options = array();

		foreach ( $terms as $term ) {
			$options[ $term->term_id ] = $term->name;
		}

		return $options;
	}

	/**
	 * Renders the widget.
	 *
	 * @return void
	 */
	protected function render(): void {
		$settings   = $this->get_settings_for_display();
		$categories = array_filter( array_map( 'absint', (array) ( $settings['category'] ?? array() ) ) );

		$args = array(
			'post_type'      => 'post',
			'post_status'    => 'publish',
			'posts_per_page' => max( 1, min( 12, (int) ( $settings['count'] ?? 3 ) ) ),
			'no_found_rows'  => true,
		);

		if ( ! empty( $categories ) ) {
			$args['category__in'] = $categories;
		}

		$query = new WP_Query( $args );

		if ( ! $query->have_posts() ) {
			wp_reset_postdata();
			return;
		}
		?>
		<section class="section">
			<div class="container section__inner section__inner--tight">
				<?php $this->render_section_head(); ?>

				<ul class="post-grid pix-grid">
					<?php
					while ( $query->have_posts() ) :
						$query->the_post();

						// The theme's own post card, so the widget and the blog
						// archive cannot drift apart.
						get_template_part( 'template-parts/content/excerpt', null, array( 'heading' => 'h3' ) );
					endwhile;

					wp_reset_postdata();
					?>
				</ul>
			</div>
		</section>
		<?php
	}
}
