<?php
/**
 * Product Support widget.
 *
 * @package PixelomaticCore
 */

namespace PixelomaticCore\Elementor\Widgets;

defined( 'ABSPATH' ) || exit;

use PixelomaticCore\Elementor\Base\Traits\Has_Product_Context;
use PixelomaticCore\Elementor\Base\Traits\Has_Style_Controls;
use PixelomaticCore\Elementor\Base\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Repeater;

/**
 * The questions buyers ask about the current product, and the ticket link.
 *
 * A delegate, deliberately. The theme owns
 * template-parts/product/section-support.php and the single-product template
 * renders the same file, so placing this in a builder layout cannot produce
 * different markup from the template it replaces.
 *
 * Every answer ships visible and the theme's script collapses all but the
 * first once it has confirmed it can reopen them, so the section is a plain
 * question-and-answer list with JavaScript off — which is also what a search
 * engine indexes.
 */
final class Product_Support extends Widget_Base {

	use Has_Style_Controls;
	use Has_Product_Context;

	/**
	 * Widget slug.
	 *
	 * @return string
	 */
	public static function slug(): string {
		return 'product-support';
	}

	/**
	 * Registers controls.
	 *
	 * @return void
	 */
	protected function register_controls(): void {
		$this->start_controls_section( 'content', array( 'label' => __( 'Support', 'pixelomatic-core' ) ) );

		$this->register_product_notice();

		$this->add_control(
			'eyebrow',
			array(
				'label'       => __( 'Eyebrow', 'pixelomatic-core' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Support', 'pixelomatic-core' ),
				'label_block' => true,
			)
		);

		$this->add_control(
			'title',
			array(
				'label'       => __( 'Title', 'pixelomatic-core' ),
				'type'        => Controls_Manager::TEXTAREA,
				'rows'        => 2,
				'default'     => __( 'Questions buyers ask', 'pixelomatic-core' ),
				'label_block' => true,
			)
		);

		$this->add_control(
			'intro',
			array(
				'label'       => __( 'Intro', 'pixelomatic-core' ),
				'type'        => Controls_Manager::TEXTAREA,
				'rows'        => 3,
				'default'     => __( 'Anything else, open a ticket and a developer who works on this product answers it.', 'pixelomatic-core' ),
				'label_block' => true,
			)
		);

		$this->add_control(
			'link_label',
			array(
				'label'       => __( 'Ticket link text', 'pixelomatic-core' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Open a ticket', 'pixelomatic-core' ),
				'label_block' => true,
			)
		);

		$this->add_control(
			'link',
			array(
				'label'       => __( 'Ticket link', 'pixelomatic-core' ),
				'type'        => Controls_Manager::URL,
				'description' => __( 'Left empty, the product\'s own support URL is used.', 'pixelomatic-core' ),
				'options'     => false,
			)
		);

		$repeater = new Repeater();

		$repeater->add_control(
			'question',
			array(
				'label'       => __( 'Question', 'pixelomatic-core' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Question', 'pixelomatic-core' ),
				'label_block' => true,
			)
		);

		$repeater->add_control(
			'answer',
			array(
				'label'       => __( 'Answer', 'pixelomatic-core' ),
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
						'question' => __( 'Are updates included in the price?', 'pixelomatic-core' ),
						'answer'   => __( 'Yes. Every release is free for as long as the product is sold, and there is no renewal fee to keep receiving them.', 'pixelomatic-core' ),
					),
					array(
						'question' => __( 'What is the difference between the two licences?', 'pixelomatic-core' ),
						'answer'   => __( 'A regular licence covers one end product that is not sold on to a client. An extended licence covers one end product that end users are charged for.', 'pixelomatic-core' ),
					),
					array(
						'question' => __( 'Can I get a refund if the product does not fit?', 'pixelomatic-core' ),
						'answer'   => __( 'Thirty days, no questions beyond what went wrong so we can fix it for the next buyer.', 'pixelomatic-core' ),
					),
				),
			)
		);

		$this->end_controls_section();

		$this->start_style_section( 'style_aside', __( 'Heading column', 'pixelomatic-core' ) );

		$this->register_text_style(
			'eyebrow',
			__( 'Eyebrow', 'pixelomatic-core' ),
			'{{WRAPPER}} .faq-layout__aside .eyebrow',
			array( 'separator' => 'none' )
		);

		$this->register_text_style( 'title', __( 'Title', 'pixelomatic-core' ), '{{WRAPPER}} .faq-layout__aside .sec-head__title' );

		$this->register_text_style( 'intro', __( 'Intro', 'pixelomatic-core' ), '{{WRAPPER}} .faq-layout__aside .sec-head__intro' );

		$this->register_link_style( 'ticket_link', __( 'Ticket link', 'pixelomatic-core' ), '{{WRAPPER}} .faq-layout__link' );

		$this->register_gap_style( 'layout_gap', __( 'Column gap', 'pixelomatic-core' ), '{{WRAPPER}} .faq-layout', 96 );

		$this->end_controls_section();

		$this->start_style_section( 'style_accordion', __( 'Questions', 'pixelomatic-core' ) );

		$this->register_box_style(
			'item',
			__( 'Item', 'pixelomatic-core' ),
			'{{WRAPPER}} .faq-accordion > li',
			array(
				'separator' => 'none',
				'shadow'    => false,
			)
		);

		$this->register_text_style( 'question', __( 'Question', 'pixelomatic-core' ), '{{WRAPPER}} .accordion__trigger' );

		// Open is expressed with aria-expanded rather than a class, so the
		// accessible state and the styled state cannot drift apart.
		$this->add_control(
			'question_color_open',
			array(
				'label'     => __( 'Open question colour', 'pixelomatic-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .accordion__trigger[aria-expanded="true"]' => 'color: {{VALUE}};',
				),
			)
		);

		$this->register_text_style(
			'mark',
			__( 'Mark', 'pixelomatic-core' ),
			'{{WRAPPER}} .accordion__mark',
			array( 'spacing' => false )
		);

		$this->register_text_style( 'answer', __( 'Answer', 'pixelomatic-core' ), '{{WRAPPER}} .accordion__panel-inner' );

		$this->end_controls_section();
	}

	/**
	 * Renders the widget.
	 *
	 * @return void
	 */
	protected function render(): void {
		$settings = $this->get_settings_for_display();

		$args = array(
			'eyebrow' => (string) ( $settings['eyebrow'] ?? '' ),
			'title'   => (string) ( $settings['title'] ?? '' ),
			'intro'   => (string) ( $settings['intro'] ?? '' ),
			'label'   => (string) ( $settings['link_label'] ?? '' ),
			'link'    => (string) ( $settings['link']['url'] ?? '' ),
		);

		$args['faqs'] = $this->items();

		$this->render_product_part( 'section', 'support', $args );
	}

	/**
	 * The questions as the theme's part takes them.
	 *
	 * @return array<int, array{question:string, answer:string}>
	 */
	private function items(): array {
		$rows  = (array) $this->get_settings_for_display( 'faqs' );
		$items = array();

		foreach ( $rows as $row ) {
			$row = (array) $row;

			if ( '' === trim( (string) ( $row['question'] ?? '' ) ) ) {
				continue;
			}

			$items[] = array(
				'question' => (string) $row['question'],
				'answer'   => (string) ( $row['answer'] ?? '' ),
			);
		}

		return $items;
	}
}
