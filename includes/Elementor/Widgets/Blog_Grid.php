<?php
/**
 * Blog Grid widget.
 *
 * @package DecentCore
 */

namespace DecentCore\Elementor\Widgets;

defined( 'ABSPATH' ) || exit;

use DecentCore\Elementor\Base\Traits\Has_Grid_Controls;
use DecentCore\Elementor\Base\Traits\Has_Section_Head;
use DecentCore\Elementor\Base\Widget_Base;
use Elementor\Controls_Manager;
use WP_Query;

/**
 * Recent posts, using the theme's own post card.
 */
final class Blog_Grid extends Widget_Base {

	use Has_Section_Head;
	use Has_Grid_Controls;

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
		$this->start_controls_section( 'head', array( 'label' => __( 'Section head', 'decent-core' ) ) );
		$this->register_section_head_controls(
			__( 'From the journal', 'decent-core' ),
			__( 'Writing', 'decent-core' )
		);
		$this->end_controls_section();

		$this->start_controls_section( 'query', array( 'label' => __( 'Posts', 'decent-core' ) ) );

		$this->add_control(
			'count',
			array(
				'label'   => __( 'How many', 'decent-core' ),
				'type'    => Controls_Manager::NUMBER,
				'default' => 3,
				'min'     => 1,
				'max'     => 12,
			)
		);

		$this->add_control(
			'category',
			array(
				'label'       => __( 'Category', 'decent-core' ),
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
				'label' => __( 'Layout', 'decent-core' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);
		$this->register_grid_controls( 3 );
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

				<ul class="post-grid decent-grid">
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
