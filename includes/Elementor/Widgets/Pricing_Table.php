<?php
/**
 * Pricing Table widget.
 *
 * @package DecentCore
 */

namespace DecentCore\Elementor\Widgets;

defined( 'ABSPATH' ) || exit;

use DecentCore\Elementor\Base\Traits\Has_Grid_Controls;
use DecentCore\Elementor\Base\Traits\Has_Section_Head;
use DecentCore\Elementor\Base\Traits\Has_Style_Controls;
use DecentCore\Elementor\Base\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Repeater;

/**
 * Licence or plan comparison.
 */
final class Pricing_Table extends Widget_Base {

	use Has_Section_Head;
	use Has_Grid_Controls;
	use Has_Style_Controls;

	/**
	 * Widget slug.
	 *
	 * @return string
	 */
	public static function slug(): string {
		return 'pricing-table';
	}

	/**
	 * Registers controls.
	 *
	 * @return void
	 */
	protected function register_controls(): void {
		$this->start_controls_section( 'head', array( 'label' => __( 'Section head', 'decent-core' ) ) );
		$this->register_section_head_controls(
			__( 'One-time price, lifetime updates', 'decent-core' ),
			__( 'Pricing', 'decent-core' )
		);
		$this->end_controls_section();

		$this->start_controls_section( 'items', array( 'label' => __( 'Plans', 'decent-core' ) ) );

		$repeater = new Repeater();

		$repeater->add_control(
			'name',
			array(
				'label'   => __( 'Name', 'decent-core' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'Regular licence', 'decent-core' ),
			)
		);

		$repeater->add_control(
			'price',
			array(
				'label'   => __( 'Price', 'decent-core' ),
				'type'    => Controls_Manager::TEXT,
				'default' => '$59',
			)
		);

		$repeater->add_control(
			'note',
			array(
				'label'       => __( 'Note', 'decent-core' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
			)
		);

		$repeater->add_control(
			'features',
			array(
				'label'       => __( 'Included', 'decent-core' ),
				'type'        => Controls_Manager::TEXTAREA,
				'rows'        => 5,
				'description' => __( 'One per line.', 'decent-core' ),
				'label_block' => true,
			)
		);

		$repeater->add_control(
			'button_label',
			array(
				'label'   => __( 'Button', 'decent-core' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'Choose', 'decent-core' ),
			)
		);

		$repeater->add_control(
			'button_url',
			array(
				'label' => __( 'Link', 'decent-core' ),
				'type'  => Controls_Manager::URL,
			)
		);

		$repeater->add_control(
			'featured',
			array(
				'label' => __( 'Highlight this plan', 'decent-core' ),
				'type'  => Controls_Manager::SWITCHER,
			)
		);

		$this->add_control(
			'plans',
			array(
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'title_field' => '{{{ name }}}',
				'default'     => array(
					array(
						'name'     => __( 'Regular licence', 'decent-core' ),
						'price'    => '$59',
						'note'     => __( 'One end product you do not charge for', 'decent-core' ),
						'features' => __( "Lifetime updates\n6 months support\nDocumentation and demo content", 'decent-core' ),
					),
					array(
						'name'     => __( 'Extended licence', 'decent-core' ),
						'price'    => '$295',
						'note'     => __( 'One paid end product, plus client resale', 'decent-core' ),
						'features' => __( "Everything in Regular\nResale rights\n12 months support", 'decent-core' ),
						'featured' => 'yes',
					),
				),
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
		$this->register_grid_controls( 2 );
		$this->end_controls_section();

		$this->start_style_section( 'style_head', __( 'Section head', 'decent-core' ) );
		$this->register_section_head_style_controls();
		$this->end_controls_section();

		$this->start_style_section( 'style_card', __( 'Plan cards', 'decent-core' ) );

		$this->register_box_style(
			'card',
			__( 'Card', 'decent-core' ),
			'{{WRAPPER}} .price-card',
			array( 'separator' => 'none' )
		);

		// The highlighted plan is styled after the base card so its values win
		// on the one card that carries both classes.
		$this->register_box_style(
			'card_featured',
			__( 'Highlighted card', 'decent-core' ),
			'{{WRAPPER}} .price-card--featured'
		);

		$this->register_text_style( 'plan_name', __( 'Plan name', 'decent-core' ), '{{WRAPPER}} .price-card__head h3' );

		$this->register_box_style(
			'plan_badge',
			__( 'Badge', 'decent-core' ),
			'{{WRAPPER}} .price-card__head .badge',
			array( 'shadow' => false )
		);

		$this->register_text_style(
			'plan_badge_text',
			__( 'Badge text', 'decent-core' ),
			'{{WRAPPER}} .price-card__head .badge',
			array(
				'heading' => false,
				'spacing' => false,
			)
		);

		$this->register_text_style(
			'plan_price',
			__( 'Price', 'decent-core' ),
			'{{WRAPPER}} .price-card__price span:first-child',
			array( 'spacing' => false )
		);

		$this->register_text_style(
			'plan_note',
			__( 'Price note', 'decent-core' ),
			'{{WRAPPER}} .price-card__price span + span',
			array( 'spacing' => false )
		);

		$this->register_text_style(
			'plan_feature',
			__( 'Included item', 'decent-core' ),
			'{{WRAPPER}} .price-card .tick-list li',
			array( 'spacing' => false )
		);

		$this->register_icon_style(
			'plan_tick',
			__( 'Tick icon', 'decent-core' ),
			'{{WRAPPER}} .price-card .tick-list li svg',
			array(
				'svg_selector' => '{{WRAPPER}} .price-card .tick-list li svg',
				'box'          => false,
			)
		);

		$this->register_gap_style(
			'plan_feature_gap',
			__( 'Included list gap', 'decent-core' ),
			'{{WRAPPER}} .price-card .tick-list',
			32
		);

		$this->register_button_style( 'plan_button', __( 'Button', 'decent-core' ), '{{WRAPPER}} .price-card .btn' );

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
		$plans = (array) ( $this->get_settings_for_display( 'plans' ) ?? array() );

		if ( empty( $plans ) ) {
			return;
		}
		?>
		<section class="section">
			<div class="container section__inner section__inner--tight">
				<?php $this->render_section_head(); ?>

				<ul class="pix-grid">
					<?php foreach ( $plans as $plan ) : ?>
						<?php
						$features = array_values( array_filter( array_map( 'trim', (array) preg_split( '/\R/', (string) ( $plan['features'] ?? '' ) ) ) ) );
						$link     = (array) ( $plan['button_url'] ?? array() );
						$url      = (string) ( $link['url'] ?? '' );
						$featured = 'yes' === ( $plan['featured'] ?? '' );
						?>
						<li class="card price-card<?php echo $featured ? ' price-card--featured' : ''; ?>">
							<div class="price-card__head">
								<h3><?php echo esc_html( (string) ( $plan['name'] ?? '' ) ); ?></h3>
								<?php if ( $featured ) : ?>
									<span class="badge badge--blue"><?php esc_html_e( 'POPULAR', 'decent-core' ); ?></span>
								<?php endif; ?>
							</div>

							<?php // .price-card__price sizes its first and second span, so the figure and its note are spans rather than two paragraphs. ?>
							<p class="price-card__price">
								<span><?php echo esc_html( (string) ( $plan['price'] ?? '' ) ); ?></span>
								<?php if ( ! empty( $plan['note'] ) ) : ?>
									<span><?php echo esc_html( (string) $plan['note'] ); ?></span>
								<?php endif; ?>
							</p>

							<?php if ( ! empty( $features ) ) : ?>
								<ul class="tick-list">
									<?php foreach ( $features as $feature ) : ?>
										<li>
											<?php $this->icon( 'check', 17, 1.9 ); ?>
											<span><?php echo esc_html( $feature ); ?></span>
										</li>
									<?php endforeach; ?>
								</ul>
							<?php endif; ?>

							<?php if ( '' !== $url ) : ?>
								<a class="btn <?php echo $featured ? 'btn--primary' : 'btn--secondary'; ?> btn--block"
									href="<?php echo esc_url( $url ); ?>"
									<?php echo ! empty( $link['is_external'] ) ? 'target="_blank" rel="noopener noreferrer"' : ''; ?>>
									<?php echo esc_html( (string) ( $plan['button_label'] ?? __( 'Choose', 'decent-core' ) ) ); ?>
								</a>
							<?php endif; ?>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		</section>
		<?php
	}
}
