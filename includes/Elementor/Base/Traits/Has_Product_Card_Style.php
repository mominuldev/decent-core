<?php
/**
 * Product card style controls.
 *
 * @package DecentCore
 */

namespace DecentCore\Elementor\Base\Traits;

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;

/**
 * Style controls for the theme's product card.
 *
 * The card is implemented exactly once, in the theme, and four widgets render
 * it — the grid, the slider, the catalogue and the related row. Its Style tab
 * is written once here for the same reason: four copies of these selectors
 * would be four places for a class name to fall out of step with the theme's
 * stylesheet.
 *
 * Every selector is scoped by {{WRAPPER}}, so restyling the card inside one
 * widget never reaches the archive loop or another widget on the same page.
 *
 * Expects the widget to also use Has_Style_Controls.
 */
trait Has_Product_Card_Style {

	/**
	 * Registers the product card's style controls.
	 *
	 * @param string $prefix Control id prefix, unique per widget.
	 * @return void
	 */
	protected function register_product_card_style_controls( string $prefix = 'card' ): void {
		$card = '{{WRAPPER}} .product-card';

		$this->register_box_style(
			$prefix,
			__( 'Card', 'decent-core' ),
			$card,
			array( 'separator' => 'none' )
		);

		$this->add_responsive_control(
			$prefix . '_media_height',
			array(
				'label'      => __( 'Image height', 'decent-core' ),
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
					$card . ' .product-card__media'     => 'height: {{SIZE}}{{UNIT}};',
					$card . ' .product-card__media img' => 'height: 100%; object-fit: cover;',
				),
			)
		);

		$this->register_box_style(
			$prefix . '_badge',
			__( 'Badge', 'decent-core' ),
			$card . ' .product-card__badge',
			array( 'shadow' => false )
		);

		$this->register_text_style(
			$prefix . '_badge_text',
			__( 'Badge text', 'decent-core' ),
			$card . ' .product-card__badge',
			array(
				'heading' => false,
				'spacing' => false,
			)
		);

		$this->register_text_style(
			$prefix . '_type',
			__( 'Type line', 'decent-core' ),
			$card . ' .product-card__type'
		);

		$this->register_icon_style(
			$prefix . '_type_icon',
			__( 'Type icon', 'decent-core' ),
			$card . ' .product-card__icon'
		);

		$this->register_text_style(
			$prefix . '_title',
			__( 'Title', 'decent-core' ),
			$card . ' .product-card__title'
		);

		$this->register_link_style(
			$prefix . '_title_link',
			__( 'Title link', 'decent-core' ),
			$card . ' .product-card__title a'
		);

		$this->register_text_style(
			$prefix . '_desc',
			__( 'Description', 'decent-core' ),
			$card . ' .product-card__desc'
		);

		$this->register_text_style(
			$prefix . '_rating',
			__( 'Rating line', 'decent-core' ),
			$card . ' .rating-line'
		);

		$this->register_text_style(
			$prefix . '_stars',
			__( 'Stars', 'decent-core' ),
			$card . ' .stars',
			array( 'spacing' => false )
		);

		$this->register_text_style(
			$prefix . '_price',
			__( 'Price', 'decent-core' ),
			$card . ' .price-now',
			array( 'spacing' => false )
		);

		$this->register_text_style(
			$prefix . '_price_note',
			__( 'Price note', 'decent-core' ),
			$card . ' .price-note',
			array( 'spacing' => false )
		);

		$this->register_button_style(
			$prefix . '_button',
			__( 'Buy button', 'decent-core' ),
			$card . ' .btn--primary'
		);

		$this->register_button_style(
			$prefix . '_button_alt',
			__( 'Demo button', 'decent-core' ),
			$card . ' .btn--secondary'
		);

		$this->register_gap_style(
			$prefix . '_actions_gap',
			__( 'Button gap', 'decent-core' ),
			$card . ' .product-card__actions',
			32
		);
	}
}
