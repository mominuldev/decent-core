<?php
/**
 * Showcase widget.
 *
 * @package DecentCore
 */

namespace DecentCore\Elementor\Widgets;

defined( 'ABSPATH' ) || exit;

use DecentCore\Elementor\Base\Traits\Has_Section_Head;
use DecentCore\Elementor\Base\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Repeater;

/**
 * Alternating text-and-visual rows.
 *
 * The design brief bans three-equal-column feature layouts and calls for a
 * zig-zag instead, which is what .showcase is. Rows alternate automatically
 * rather than asking an editor to remember to flip every second one.
 */
final class Showcase extends Widget_Base {

	use Has_Section_Head;

	/**
	 * Widget slug.
	 *
	 * @return string
	 */
	public static function slug(): string {
		return 'showcase';
	}

	/**
	 * Registers controls.
	 *
	 * @return void
	 */
	protected function register_controls(): void {
		$this->start_controls_section( 'head', array( 'label' => __( 'Section head', 'decent-core' ) ) );
		$this->register_section_head_controls(
			__( 'Built for the stack you work in', 'decent-core' ),
			__( 'Product showcase', 'decent-core' )
		);
		$this->end_controls_section();

		$this->start_controls_section( 'items', array( 'label' => __( 'Rows', 'decent-core' ) ) );

		$repeater = new Repeater();

		$repeater->add_control(
			'count',
			array(
				'label'       => __( 'Count label', 'decent-core' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( '418 PRODUCTS', 'decent-core' ),
				'label_block' => true,
			)
		);

		$repeater->add_control(
			'title',
			array(
				'label'       => __( 'Title', 'decent-core' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Themes that survive a real content team', 'decent-core' ),
				'label_block' => true,
			)
		);

		$repeater->add_control(
			'body',
			array(
				'label'       => __( 'Body', 'decent-core' ),
				'type'        => Controls_Manager::TEXTAREA,
				'rows'        => 4,
				'label_block' => true,
			)
		);

		$repeater->add_control(
			'points',
			array(
				'label'       => __( 'Tick list', 'decent-core' ),
				'type'        => Controls_Manager::TEXTAREA,
				'rows'        => 4,
				'description' => __( 'One per line.', 'decent-core' ),
				'label_block' => true,
			)
		);

		$repeater->add_control(
			'image',
			array(
				'label' => __( 'Image', 'decent-core' ),
				'type'  => Controls_Manager::MEDIA,
			)
		);

		$this->add_control(
			'rows',
			array(
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'title_field' => '{{{ title }}}',
				'default'     => array(
					array(
						'count'  => __( '418 PRODUCTS', 'decent-core' ),
						'title'  => __( 'Themes that survive a real content team', 'decent-core' ),
						'body'   => __( 'Block-native themes built on current WordPress standards: full site editing, patterns, WP-CLI friendly builds and no page-builder lock-in.', 'decent-core' ),
						'points' => __( "Block patterns and theme.json variations included\nChild theme and demo content in every download\nTested against the current and previous major release", 'decent-core' ),
					),
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
		$rows = (array) ( $this->get_settings_for_display( 'rows' ) ?? array() );

		if ( empty( $rows ) ) {
			return;
		}
		?>
		<section class="section">
			<div class="container section__inner section__inner--tight">
				<?php $this->render_section_head(); ?>

				<?php foreach ( $rows as $index => $row ) : ?>
					<?php
					// Rows alternate on their own; asking an editor to flip
					// every second one is a rule they will eventually forget.
					$reverse = 1 === $index % 2;
					$points  = array_values( array_filter( array_map( 'trim', (array) preg_split( '/\R/', (string) ( $row['points'] ?? '' ) ) ) ) );
					$image   = (array) ( $row['image'] ?? array() );
					?>
					<div class="showcase<?php echo $reverse ? ' showcase--reverse' : ''; ?>">
						<div>
							<?php if ( ! empty( $row['count'] ) ) : ?>
								<p class="showcase__count"><?php echo esc_html( (string) $row['count'] ); ?></p>
							<?php endif; ?>

							<h3 class="showcase__title"><?php echo esc_html( (string) ( $row['title'] ?? '' ) ); ?></h3>

							<?php if ( ! empty( $row['body'] ) ) : ?>
								<p class="showcase__body"><?php echo esc_html( (string) $row['body'] ); ?></p>
							<?php endif; ?>

							<?php if ( ! empty( $points ) ) : ?>
								<ul class="tick-list showcase__points">
									<?php foreach ( $points as $point ) : ?>
										<li>
											<?php $this->icon( 'check', 18, 1.8 ); ?>
											<span><?php echo esc_html( $point ); ?></span>
										</li>
									<?php endforeach; ?>
								</ul>
							<?php endif; ?>
						</div>

						<div>
							<?php
							if ( ! empty( $image['id'] ) && class_exists( '\DecentThemes\Frontend\Media' ) ) {
								\DecentThemes\Frontend\Media::render(
									(int) $image['id'],
									array( 'frame' => 'lg' )
								);
							}
							?>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</section>
		<?php
	}
}
