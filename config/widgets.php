<?php
/**
 * Widget map.
 *
 * The plugin's database of itself. Four things read this one array:
 *
 *   Widget_Registry  decides what to register with Elementor, and generates
 *                    one settings toggle per entry
 *   Admin_Page       renders those toggles and their unmet dependencies
 *   Manager          registers a style and script handle per widget
 *   Bundler          resolves a page's widget set into a single file
 *
 * Adding a widget is a class and one entry here. Nothing else changes.
 *
 * `requires` is checked before registration: a product widget on a site
 * without EDD would only be able to render an error, so it never reaches the
 * panel at all.
 *
 * @package DecentCore
 */

defined( 'ABSPATH' ) || exit;

return array(

	'hero-search'       => array(
		'class'    => DecentCore\Elementor\Widgets\Hero_Search::class,
		'title'    => __( 'Hero Search', 'decent-core' ),
		'category' => 'decent-layout',
		'group'    => 'hero',
		'icon'     => 'eicon-search',
		'keywords' => array( 'hero', 'search', 'banner' ),
		'styles'   => array( 'hero-search' ),
		'scripts'  => array(),
		'requires' => array(),
		'default'  => true,
	),

	'category-grid'     => array(
		'class'    => DecentCore\Elementor\Widgets\Category_Grid::class,
		'title'    => __( 'Category Grid', 'decent-core' ),
		'category' => 'decent-layout',
		'group'    => 'category',
		'icon'     => 'eicon-gallery-grid',
		'keywords' => array( 'category', 'taxonomy', 'grid' ),
		'styles'   => array( 'category-grid' ),
		'scripts'  => array(),
		'requires' => array(),
		'default'  => true,
	),

	'feature-grid'      => array(
		'class'    => DecentCore\Elementor\Widgets\Feature_Grid::class,
		'title'    => __( 'Feature Grid', 'decent-core' ),
		'category' => 'decent-content',
		'group'    => 'feature',
		'icon'     => 'eicon-info-box',
		'keywords' => array( 'feature', 'icon', 'grid' ),
		'styles'   => array( 'feature-grid' ),
		'scripts'  => array(),
		'requires' => array(),
		'default'  => true,
	),

	'benefit-grid'      => array(
		'class'    => DecentCore\Elementor\Widgets\Benefit_Grid::class,
		'title'    => __( 'Benefit Grid', 'decent-core' ),
		'category' => 'decent-content',
		'group'    => 'feature',
		'icon'     => 'eicon-checkbox',
		'keywords' => array( 'benefit', 'dark', 'grid' ),
		'styles'   => array( 'benefit-grid' ),
		'scripts'  => array(),
		'requires' => array(),
		'default'  => true,
	),

	'guarantee-grid'    => array(
		'class'    => DecentCore\Elementor\Widgets\Guarantee_Grid::class,
		'title'    => __( 'Guarantee Grid', 'decent-core' ),
		'category' => 'decent-content',
		'group'    => 'feature',
		'icon'     => 'eicon-lock-user',
		'keywords' => array( 'guarantee', 'trust', 'refund' ),
		'styles'   => array( 'guarantee-grid' ),
		'scripts'  => array(),
		'requires' => array(),
		'default'  => true,
	),

	'stats-counter'     => array(
		'class'    => DecentCore\Elementor\Widgets\Stats_Counter::class,
		'title'    => __( 'Statistics Counter', 'decent-core' ),
		'category' => 'decent-content',
		'group'    => 'stats',
		'icon'     => 'eicon-counter',
		'keywords' => array( 'stats', 'counter', 'metrics' ),
		'styles'   => array( 'stats-counter' ),
		'scripts'  => array(),
		'requires' => array(),
		'default'  => true,
	),

	'faq-accordion'     => array(
		'class'    => DecentCore\Elementor\Widgets\Faq_Accordion::class,
		'title'    => __( 'FAQ Accordion', 'decent-core' ),
		'category' => 'decent-content',
		'group'    => 'faq',
		'icon'     => 'eicon-help-o',
		'keywords' => array( 'faq', 'accordion', 'questions' ),
		'styles'   => array(),
		'scripts'  => array(),
		'requires' => array(),
		'default'  => true,
	),

	'cta'               => array(
		'class'    => DecentCore\Elementor\Widgets\Cta::class,
		'title'    => __( 'CTA', 'decent-core' ),
		'category' => 'decent-content',
		'group'    => 'cta',
		'icon'     => 'eicon-call-to-action',
		'keywords' => array( 'cta', 'banner', 'action' ),
		'styles'   => array(),
		'scripts'  => array(),
		'requires' => array(),
		'default'  => true,
	),

	'promo-card'        => array(
		'class'    => DecentCore\Elementor\Widgets\Promo_Card::class,
		'title'    => __( 'Promo Card', 'decent-core' ),
		'category' => 'decent-content',
		'group'    => 'cta',
		'icon'     => 'eicon-price-table',
		'keywords' => array( 'promo', 'bundle', 'offer' ),
		'styles'   => array(),
		'scripts'  => array(),
		'requires' => array(),
		'default'  => true,
	),

	'breadcrumb'        => array(
		'class'    => DecentCore\Elementor\Widgets\Breadcrumb::class,
		'title'    => __( 'Breadcrumb', 'decent-core' ),
		'category' => 'decent-layout',
		'group'    => 'navigation',
		'icon'     => 'eicon-yoast',
		'keywords' => array( 'breadcrumb', 'trail', 'navigation' ),
		'styles'   => array(),
		'scripts'  => array(),
		'requires' => array( 'theme' ),
		'default'  => true,
	),

	'product-grid'      => array(
		'class'    => DecentCore\Elementor\Widgets\Product_Grid::class,
		'title'    => __( 'Product Grid', 'decent-core' ),
		'category' => 'decent-products',
		'group'    => 'products',
		'icon'     => 'eicon-products',
		'keywords' => array( 'product', 'edd', 'download', 'shop' ),
		'styles'   => array( 'product-grid' ),
		'scripts'  => array(),
		'requires' => array( 'edd', 'theme' ),
		'default'  => true,
	),

	'product-rank-list' => array(
		'class'    => DecentCore\Elementor\Widgets\Product_Rank_List::class,
		'title'    => __( 'Product Rank List', 'decent-core' ),
		'category' => 'decent-products',
		'group'    => 'products',
		'icon'     => 'eicon-bullet-list',
		'keywords' => array( 'product', 'best seller', 'ranking' ),
		'styles'   => array(),
		'scripts'  => array(),
		'requires' => array( 'edd' ),
		'default'  => true,
	),
);
