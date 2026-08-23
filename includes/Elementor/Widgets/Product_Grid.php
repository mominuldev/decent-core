<?php
/**
 * Product Grid widget.
 *
 * @package DecentCore
 */

namespace DecentCore\Elementor\Widgets;

defined( 'ABSPATH' ) || exit;

use DecentCore\Elementor\Base\Traits\Has_Grid_Controls;
use DecentCore\Elementor\Base\Traits\Has_Product_Card_Style;
use DecentCore\Elementor\Base\Traits\Has_Query_Controls;
use DecentCore\Elementor\Base\Traits\Has_Style_Controls;
use DecentCore\Elementor\Base\Widget_Base;
use Elementor\Controls_Manager;
use WP_Term;

/**
 * Products in a filterable grid.
 *
 * Renders through the theme's Card::render(). That is the single most
 * important line in this widget: the card is implemented once, in the theme,
 * and the archive loop, the related row, the AJAX filter response and this
 * widget all call it. A widget that emitted its own .product-card markup
 * would fork the design system on day one — and bin/check.sh in the theme
 * fails the build if any file outside template-parts/product/ tries.
 *
 * The filter bar is progressive enhancement, on the theme's own terms: every
 * chip is a real link to that category's archive and the sort control is a
 * real GET form, so the section works with no JavaScript at all. The script
 * intercepts both and swaps in server-rendered cards from
 * Rest\Product_Grid_Controller — the same grid_response() this widget calls
 * for its first paint, so the two can never drift.
 *
 * There is no section head. Headings are the Heading widget's job now, which
 * is what lets an editor put a grid under a head, beside one, or under
 * nothing at all.
 */
final class Product_Grid extends Widget_Base {

	use Has_Grid_Controls;
	use Has_Query_Controls;
	use Has_Style_Controls;
	use Has_Product_Card_Style;

	/**
	 * Widget slug.
	 *
	 * @return string
	 */
	public static function slug(): string {
		return 'product-grid';
	}

	/**
	 * Registers controls.
	 *
	 * @return void
	 */
	protected function register_controls(): void {
		$this->start_controls_section( 'query', array( 'label' => __( 'Products', 'decent-core' ) ) );
		$this->register_query_controls( 6 );

		$this->add_control(
			'density',
			array(
				'label'     => __( 'Card size', 'decent-core' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'md',
				'options'   => array(
					'sm' => __( 'Compact', 'decent-core' ),
					'md' => __( 'Standard', 'decent-core' ),
				),
				'separator' => 'before',
			)
		);

		$this->add_control(
			'show_actions',
			array(
				'label'   => __( 'Show buttons', 'decent-core' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);

		$this->end_controls_section();

		$this->start_controls_section( 'filter', array( 'label' => __( 'Filter bar', 'decent-core' ) ) );

		$this->add_control(
			'show_filter',
			array(
				'label'   => __( 'Category chips', 'decent-core' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);

		$this->add_control(
			'all_label',
			array(
				'label'       => __( 'First chip', 'decent-core' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'All products', 'decent-core' ),
				'label_block' => true,
				'condition'   => array( 'show_filter' => 'yes' ),
			)
		);

		$this->add_control(
			'filter_categories',
			array(
				'label'       => __( 'Chips', 'decent-core' ),
				'type'        => Controls_Manager::SELECT2,
				'multiple'    => true,
				'label_block' => true,
				'options'     => $this->chip_options(),
				'description' => __( 'Leave empty to use the categories above, or the busiest top-level categories when those are empty too.', 'decent-core' ),
				'condition'   => array( 'show_filter' => 'yes' ),
			)
		);

		$this->add_control(
			'filter_limit',
			array(
				'label'       => __( 'Most chips', 'decent-core' ),
				'type'        => Controls_Manager::NUMBER,
				'default'     => 4,
				'min'         => 2,
				'max'         => 10,
				'description' => __( 'Only caps the automatic list. Chips picked by hand are all shown.', 'decent-core' ),
				'condition'   => array(
					'show_filter'       => 'yes',
					'filter_categories' => '',
				),
			)
		);

		$this->add_control(
			'show_sort',
			array(
				'label'     => __( 'Sort control', 'decent-core' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'yes',
				'separator' => 'before',
			)
		);

		$this->add_control(
			'sort_prefix',
			array(
				'label'     => __( 'Sort label', 'decent-core' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => __( 'Sort:', 'decent-core' ),
				'condition' => array( 'show_sort' => 'yes' ),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section( 'more', array( 'label' => __( 'Footer', 'decent-core' ) ) );

		$this->add_control(
			'show_more',
			array(
				'label'   => __( 'View all button', 'decent-core' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);

		$this->add_control(
			'more_label',
			array(
				'label'       => __( 'Button text', 'decent-core' ),
				'type'        => Controls_Manager::TEXT,
				/* translators: %s: number of products, inserted by the widget. */
				'default'     => __( 'See all %s products', 'decent-core' ),
				'label_block' => true,
				/* translators: %s is a literal placeholder token the editor types, not a value. */
				'description' => __( '%s becomes the number of products the query matched.', 'decent-core' ),
				'condition'   => array( 'show_more' => 'yes' ),
			)
		);

		$this->add_control(
			'more_url',
			array(
				'label'       => __( 'Button link', 'decent-core' ),
				'type'        => Controls_Manager::URL,
				'placeholder' => (string) get_post_type_archive_link( 'download' ),
				'description' => __( 'Defaults to the product archive.', 'decent-core' ),
				'condition'   => array( 'show_more' => 'yes' ),
			)
		);

		$this->add_control(
			'more_note',
			array(
				'label'       => __( 'Note', 'decent-core' ),
				'type'        => Controls_Manager::TEXTAREA,
				'rows'        => 2,
				'default'     => __( 'Prices shown are one-time. VAT added at checkout where applicable.', 'decent-core' ),
				'label_block' => true,
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

		$this->add_responsive_control(
			'filter_gap',
			array(
				'label'      => __( 'Space below the filter bar', 'decent-core' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'default'    => array(
					'size' => 40,
					'unit' => 'px',
				),
				'range'      => array(
					'px' => array(
						'min'  => 0,
						'max'  => 80,
						'step' => 4,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .pix-filter' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				),
				'condition'  => array( 'show_filter' => 'yes' ),
			)
		);

		$this->add_responsive_control(
			'more_gap',
			array(
				'label'      => __( 'Space above the footer', 'decent-core' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'default'    => array(
					'size' => 50,
					'unit' => 'px',
				),
				'range'      => array(
					'px' => array(
						'min'  => 0,
						'max'  => 96,
						'step' => 2,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .pix-product-grid__more' => 'margin-top: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();

		$this->start_style_section(
			'style_chips',
			__( 'Category chips', 'decent-core' ),
			array( 'condition' => array( 'show_filter' => 'yes' ) )
		);

		$this->register_button_style(
			'chip',
			__( 'Chip', 'decent-core' ),
			'{{WRAPPER}} .pix-filter__chip',
			array( 'separator' => 'none' )
		);

		$this->add_control(
			'chip_active_heading',
			array(
				'label'     => __( 'Selected chip', 'decent-core' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_control(
			'chip_active_color',
			array(
				'label'     => __( 'Text', 'decent-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pix-filter__chip--active' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'chip_active_background',
			array(
				'label'     => __( 'Background', 'decent-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pix-filter__chip--active' => 'background: {{VALUE}}; border-color: {{VALUE}};',
				),
			)
		);

		$this->register_gap_style( 'chip_gap', __( 'Space between chips', 'decent-core' ), '{{WRAPPER}} .pix-filter__chips', 32 );

		$this->end_controls_section();

		$this->start_style_section(
			'style_sort',
			__( 'Sort control', 'decent-core' ),
			array( 'condition' => array( 'show_sort' => 'yes' ) )
		);

		$this->register_text_style(
			'sort',
			__( 'Sort', 'decent-core' ),
			'{{WRAPPER}} .pix-filter__sort',
			array(
				'heading' => false,
				'spacing' => false,
				'align'   => false,
			)
		);

		$this->end_controls_section();

		$this->start_style_section( 'style_card', __( 'Product cards', 'decent-core' ) );
		$this->register_product_card_style_controls();
		$this->end_controls_section();

		$this->start_style_section(
			'style_more',
			__( 'Footer', 'decent-core' ),
			array( 'condition' => array( 'show_more' => 'yes' ) )
		);

		$this->register_button_style(
			'more',
			__( 'Button', 'decent-core' ),
			'{{WRAPPER}} .pix-product-grid__more-link',
			array( 'separator' => 'none' )
		);

		$this->register_text_style(
			'note',
			__( 'Note', 'decent-core' ),
			'{{WRAPPER}} .pix-product-grid__more-note',
			array( 'align' => false )
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

	/* --------------------------------------------------------------- render */

	/**
	 * Renders the widget.
	 *
	 * @return void
	 */
	protected function render(): void {
		if ( ! class_exists( '\DecentThemes\Frontend\Card' ) ) {
			$this->render_unavailable();
			return;
		}

		$settings = $this->get_settings_for_display();
		$state    = array(
			'category' => 0,
			'orderby'  => (string) ( $settings['orderby'] ?? 'popular' ),
			'page'     => 1,
		);

		$result = $this->grid_response( $state );
		$terms  = $this->chip_terms();

		// Nothing to show and nothing to filter with: render nothing at all
		// rather than an empty band with a heading-less gap in it.
		if ( '' === $result['html'] && empty( $terms ) ) {
			return;
		}
		?>
		<section class="section pix-product-grid"
			data-product-grid
			data-endpoint="<?php echo esc_url( rest_url( 'decent/v1/product-grid' ) ); ?>"
			data-post="<?php echo esc_attr( (string) $this->document_id() ); ?>"
			data-widget="<?php echo esc_attr( $this->get_id() ); ?>">
			<div class="container section__inner">
				<?php $this->render_filter_bar( $terms, $state ); ?>

				<div class="pix-product-grid__results" data-results aria-live="polite" aria-busy="false">
					<?php
					// Server-rendered cards, printed as-is. Escaping here would
					// escape the theme's own card markup.
					echo $result['html']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Built by Card::render(), escaped at source.
					?>
				</div>

				<?php $this->render_more( $result['more'] ); ?>
			</div>
		</section>
		<?php
	}

	/**
	 * Renders the chip row and the sort control.
	 *
	 * @param WP_Term[]            $terms Chip terms.
	 * @param array<string, mixed> $state Current filter state.
	 * @return void
	 */
	private function render_filter_bar( array $terms, array $state ): void {
		$settings = $this->get_settings_for_display();
		$chips    = ! empty( $terms );
		$sort     = 'yes' === ( $settings['show_sort'] ?? 'yes' );

		if ( ! $chips && ! $sort ) {
			return;
		}

		$archive = (string) get_post_type_archive_link( 'download' );
		?>
		<div class="pix-filter">
			<?php if ( $chips ) : ?>
				<div class="pix-filter__chips" data-chips>
					<?php
					$this->render_chip(
						0,
						$this->text( 'all_label', __( 'All products', 'decent-core' ) ),
						$archive,
						0 === (int) $state['category']
					);

					foreach ( $terms as $term ) {
						$link = get_term_link( $term );

						$this->render_chip(
							(int) $term->term_id,
							$term->name,
							is_wp_error( $link ) ? $archive : (string) $link,
							(int) $term->term_id === (int) $state['category']
						);
					}
					?>
				</div>
			<?php endif; ?>

			<?php if ( $sort ) : ?>
				<?php $field = 'decent-sort-' . $this->get_id(); ?>
				<form class="pix-filter__sort" method="get" action="<?php echo esc_url( $archive ); ?>" data-sort-form>
					<label class="pix-filter__sort-label" for="<?php echo esc_attr( $field ); ?>">
						<?php echo esc_html( $this->text( 'sort_prefix', __( 'Sort:', 'decent-core' ) ) ); ?>
					</label>

					<?php
					// The design draws the sort as a line of text with a chevron,
					// and a bare <select> cannot be that: browsers size one to its
					// widest option, so "Most popular" would sit in a box built for
					// "Price: low to high". The select is therefore laid over the
					// visible text and made transparent — still the real control,
					// still the native picker and the keyboard, but the width is
					// the width of what is selected.
					$sorts = self::sort_options();
					?>
					<span class="pix-filter__select">
						<span class="pix-filter__value" data-sort-value aria-hidden="true">
							<?php echo esc_html( $sorts[ $state['orderby'] ] ?? reset( $sorts ) ); ?>
						</span>

						<?php $this->icon( 'chevron-down', 15, 1.8 ); ?>

						<select id="<?php echo esc_attr( $field ); ?>" name="orderby" data-sort>
							<?php foreach ( $sorts as $key => $label ) : ?>
								<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $key, $state['orderby'] ); ?>>
									<?php echo esc_html( $label ); ?>
								</option>
							<?php endforeach; ?>
						</select>
					</span>

					<?php // Without a script the select cannot submit itself, so the button is the whole interaction. ?>
					<noscript>
						<button class="btn btn--sm btn--secondary" type="submit"><?php esc_html_e( 'Sort', 'decent-core' ); ?></button>
					</noscript>
				</form>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Renders one category chip.
	 *
	 * A link, not a button: without a script it navigates to that category's
	 * archive, which is the honest fallback for "show me these".
	 *
	 * @param int    $term_id Term ID, or 0 for the "all" chip.
	 * @param string $label   Chip text.
	 * @param string $url     Fallback destination.
	 * @param bool   $active  Whether this chip is the current filter.
	 * @return void
	 */
	private function render_chip( int $term_id, string $label, string $url, bool $active ): void {
		printf(
			'<a class="pix-filter__chip%1$s" href="%2$s" data-category="%3$s"%4$s>%5$s</a>',
			$active ? ' pix-filter__chip--active' : '',
			esc_url( $url ),
			esc_attr( (string) $term_id ),
			$active ? ' aria-current="true"' : '',
			esc_html( $label )
		);
	}

	/**
	 * Renders the view-all button and the note beneath it.
	 *
	 * @param array{label: string, url: string, target: string}|null $link Resolved
	 *                                                                    button, or null.
	 * @return void
	 */
	private function render_more( ?array $link ): void {
		$note = $this->text( 'more_note' );

		if ( null === $link && '' === $note ) {
			return;
		}
		?>
		<div class="pix-product-grid__more">
			<?php if ( null !== $link ) : ?>
				<a class="btn btn--secondary pix-product-grid__more-link" data-more href="<?php echo esc_url( $link['url'] ); ?>"<?php echo $link['target']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Fixed attribute string. ?>>
					<span data-more-label><?php echo esc_html( $link['label'] ); ?></span>
					<?php $this->icon( 'arrow-right', 16, 1.8 ); ?>
				</a>
			<?php endif; ?>

			<?php if ( '' !== $note ) : ?>
				<p class="pix-product-grid__more-note"><?php echo esc_html( $note ); ?></p>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Resolves the view-all button for one filter state.
	 *
	 * Composed server-side and shipped with every AJAX response, so the script
	 * never has to build a label or guess a URL. Both move with the chips: the
	 * count is the count of what the grid is actually showing, and the button
	 * points at that category's archive rather than at the whole catalogue —
	 * unless the editor named a URL, which always wins.
	 *
	 * @param int $total    Products the query matched.
	 * @param int $category Active category ID, or 0.
	 * @return array{label: string, url: string, target: string}|null
	 */
	private function more_link( int $total, int $category ): ?array {
		$settings = $this->get_settings_for_display();

		if ( 'yes' !== ( $settings['show_more'] ?? 'yes' ) ) {
			return null;
		}

		$label = $this->text( 'more_label' );

		if ( '' === $label ) {
			return null;
		}

		// The label carries the count, so it can only be composed once the
		// query has run. A label with no %s is used verbatim.
		if ( false !== strpos( $label, '%s' ) ) {
			$label = sprintf( $label, number_format_i18n( $total ) );
		}

		$link   = (array) ( $settings['more_url'] ?? array() );
		$url    = (string) ( $link['url'] ?? '' );
		$target = ! empty( $link['is_external'] ) ? ' target="_blank" rel="noopener noreferrer"' : '';

		if ( '' === $url && $category > 0 ) {
			$term = get_term( $category, 'download_category' );
			$path = $term instanceof WP_Term ? get_term_link( $term ) : '';
			$url  = is_wp_error( $path ) ? '' : (string) $path;
		}

		if ( '' === $url ) {
			$url = (string) get_post_type_archive_link( 'download' );
		}

		if ( '' === $url ) {
			return null;
		}

		return array(
			'label'  => $label,
			'url'    => $url,
			'target' => $target,
		);
	}

	/**
	 * Explains why nothing rendered, in the editor only.
	 *
	 * On the front end an unavailable widget renders nothing at all — a
	 * visitor should never see a message about a missing theme.
	 *
	 * @return void
	 */
	private function render_unavailable(): void {
		if ( ! \Elementor\Plugin::instance()->editor->is_edit_mode() ) {
			return;
		}

		printf(
			'<p style="padding:16px;border:1px dashed #ced4da;color:#6c757d">%s</p>',
			esc_html__( 'Product Grid needs the Decent Themes theme, which owns the product card markup.', 'decent-core' )
		);
	}

	/* ------------------------------------------------------------ public API */

	/**
	 * Runs the query for a filter state and returns the rendered grid.
	 *
	 * The one place cards are produced, called by render() for the first paint
	 * and by Rest\Product_Grid_Controller for every filter afterwards. Two
	 * implementations would be two things to keep in sync, and the AJAX one
	 * would be the one nobody looks at.
	 *
	 * @param array<string, mixed> $state Category ID, sort key and page.
	 * @return array{html: string, total: int, shown: int, pages: int, page: int, more: array{label: string, url: string, target: string}|null}
	 */
	public function grid_response( array $state ): array {
		$settings = $this->get_settings_for_display();
		$page     = max( 1, (int) ( $state['page'] ?? 1 ) );

		$overrides = array(
			'orderby'       => (string) ( $state['orderby'] ?? $settings['orderby'] ?? 'popular' ),
			'paged'         => $page,
			'count_results' => true,
		);

		$category = (int) ( $state['category'] ?? 0 );

		if ( $category > 0 ) {
			$overrides['category'] = $category;
		}

		$query = $this->product_query( $overrides );
		$html  = '';

		if ( $query->have_posts() ) {
			ob_start();
			?>
			<div class="product-grid pix-grid">
				<?php
				while ( $query->have_posts() ) :
					$query->the_post();

					\DecentThemes\Frontend\Card::render(
						(int) get_the_ID(),
						array(
							'density' => (string) ( $settings['density'] ?? 'md' ),
							'actions' => 'yes' === ( $settings['show_actions'] ?? 'yes' ),
							'context' => 'widget',
						)
					);
				endwhile;
				?>
			</div>
			<?php
			$html = trim( (string) ob_get_clean() );
		} else {
			$html = $this->empty_state();
		}

		wp_reset_postdata();

		$total = (int) $query->found_posts;

		return array(
			'html'  => $html,
			'total' => $total,
			'shown' => (int) $query->post_count,
			'pages' => (int) $query->max_num_pages,
			'page'  => $page,
			'more'  => $this->more_link( $total, $category ),
		);
	}

	/**
	 * The "nothing matched that chip" state.
	 *
	 * Rendered server-side and shipped with the response, so the script never
	 * has to own a string. The theme's part is used where it exists.
	 *
	 * @return string
	 */
	private function empty_state(): string {
		if ( ! function_exists( 'get_template_part' ) || ! locate_template( 'template-parts/global/empty-state.php' ) ) {
			return '';
		}

		ob_start();

		get_template_part(
			'template-parts/global/empty-state',
			null,
			array(
				'icon'  => 'search',
				'tag'   => 'p',
				'title' => __( 'No products in that category yet', 'decent-core' ),
				'text'  => __( 'Pick another category, or see the whole catalogue.', 'decent-core' ),
			)
		);

		return trim( (string) ob_get_clean() );
	}

	/**
	 * The term IDs a chip may name.
	 *
	 * The allow-list the REST endpoint validates against: a category that is
	 * not on a chip is not a filter this widget offers, whatever the request
	 * says. Derived from the saved settings, never from the request.
	 *
	 * @return int[]
	 */
	public function chip_category_ids(): array {
		return array_map(
			static function ( WP_Term $term ): int {
				return (int) $term->term_id;
			},
			$this->chip_terms()
		);
	}

	/* -------------------------------------------------------------- internals */

	/**
	 * Resolves the chip terms.
	 *
	 * Hand-picked terms win. Failing that the widget's own category set is
	 * used when it names more than one, then that single category's children,
	 * and finally the busiest top-level categories — so a widget dropped with
	 * no configuration at all still shows a usable bar.
	 *
	 * @return WP_Term[]
	 */
	private function chip_terms(): array {
		$settings = $this->get_settings_for_display();

		if ( 'yes' !== ( $settings['show_filter'] ?? 'yes' ) || ! taxonomy_exists( 'download_category' ) ) {
			return array();
		}

		$picked = array_filter( array_map( 'absint', (array) ( $settings['filter_categories'] ?? array() ) ) );

		if ( ! empty( $picked ) ) {
			return $this->terms(
				array(
					'include' => $picked,
					'orderby' => 'include',
				)
			);
		}

		$limit = max( 2, min( 10, (int) ( $settings['filter_limit'] ?? 4 ) ) );
		$saved = array_filter( array_map( 'absint', (array) ( $settings['category'] ?? array() ) ) );

		if ( count( $saved ) > 1 ) {
			return $this->terms(
				array(
					'include' => $saved,
					'orderby' => 'include',
				)
			);
		}

		if ( 1 === count( $saved ) ) {
			$children = $this->terms(
				array(
					'parent'  => (int) reset( $saved ),
					'orderby' => 'count',
					'order'   => 'DESC',
					'number'  => $limit,
				)
			);

			// A leaf category has no children to offer, and chips that repeat
			// the one category already applied would filter nothing.
			if ( ! empty( $children ) ) {
				return $children;
			}
		}

		return $this->terms(
			array(
				'parent'  => 0,
				'orderby' => 'count',
				'order'   => 'DESC',
				'number'  => $limit,
			)
		);
	}

	/**
	 * Fetches download categories.
	 *
	 * @param array<string, mixed> $args get_terms() arguments.
	 * @return WP_Term[]
	 */
	private function terms( array $args ): array {
		$terms = get_terms(
			array_merge(
				array(
					'taxonomy'   => 'download_category',
					'hide_empty' => true,
				),
				$args
			)
		);

		if ( is_wp_error( $terms ) ) {
			return array();
		}

		$found = array_filter(
			$terms,
			static function ( $term ): bool {
				return $term instanceof WP_Term;
			}
		);

		return array_values( $found );
	}

	/**
	 * The categories offered in the chip picker.
	 *
	 * @return array<int, string>
	 */
	private function chip_options(): array {
		if ( ! taxonomy_exists( 'download_category' ) ) {
			return array();
		}

		$terms = get_terms(
			array(
				'taxonomy'   => 'download_category',
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
	 * The post the widget's settings are saved on.
	 *
	 * Not get_the_ID(): inside a theme-builder template the settings live on
	 * the template, while the queried object is whatever the template is
	 * rendering. The endpoint has to load the former.
	 *
	 * @return int
	 */
	private function document_id(): int {
		if ( class_exists( '\Elementor\Plugin' ) ) {
			$document = \Elementor\Plugin::instance()->documents->get_current();

			if ( $document ) {
				return (int) $document->get_main_id();
			}
		}

		return (int) get_the_ID();
	}
}
