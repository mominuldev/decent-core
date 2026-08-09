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

	'hero-split'        => array(
		'class'    => DecentCore\Elementor\Widgets\Hero_Split::class,
		'title'    => __( 'Hero Split', 'decent-core' ),
		'category' => 'decent-layout',
		'group'    => 'hero',
		'icon'     => 'eicon-image-box',
		'keywords' => array( 'hero', 'split', 'banner' ),
		'styles'   => array(),
		'scripts'  => array(),
		'requires' => array(),
		'default'  => true,
	),

	'trust-band'        => array(
		'class'    => DecentCore\Elementor\Widgets\Trust_Band::class,
		'title'    => __( 'Trust Band', 'decent-core' ),
		'category' => 'decent-content',
		'group'    => 'trust',
		'icon'     => 'eicon-logo',
		'keywords' => array( 'trust', 'clients', 'logos', 'metrics' ),
		'styles'   => array(),
		'scripts'  => array(),
		'requires' => array(),
		'default'  => true,
	),

	'showcase'          => array(
		'class'    => DecentCore\Elementor\Widgets\Showcase::class,
		'title'    => __( 'Showcase', 'decent-core' ),
		'category' => 'decent-content',
		'group'    => 'feature',
		'icon'     => 'eicon-image-before-after',
		'keywords' => array( 'showcase', 'zigzag', 'alternating' ),
		'styles'   => array(),
		'scripts'  => array(),
		'requires' => array( 'theme' ),
		'default'  => true,
	),

	'newsletter'        => array(
		'class'    => DecentCore\Elementor\Widgets\Newsletter::class,
		'title'    => __( 'Newsletter', 'decent-core' ),
		'category' => 'decent-content',
		'group'    => 'form',
		'icon'     => 'eicon-email-field',
		'keywords' => array( 'newsletter', 'email', 'subscribe' ),
		'styles'   => array(),
		'scripts'  => array(),
		'requires' => array(),
		'default'  => true,
	),

	'plugin-list'       => array(
		'class'    => DecentCore\Elementor\Widgets\Plugin_List::class,
		'title'    => __( 'Plugin List', 'decent-core' ),
		'category' => 'decent-products',
		'group'    => 'products',
		'icon'     => 'eicon-post-list',
		'keywords' => array( 'plugin', 'product', 'list', 'edd' ),
		'styles'   => array(),
		'scripts'  => array(),
		'requires' => array( 'edd', 'theme' ),
		'default'  => true,
	),

	'product-slider'    => array(
		'class'    => DecentCore\Elementor\Widgets\Product_Slider::class,
		'title'    => __( 'Product Slider', 'decent-core' ),
		'category' => 'decent-products',
		'group'    => 'products',
		'icon'     => 'eicon-slider-push',
		'keywords' => array( 'product', 'slider', 'carousel', 'edd' ),
		'styles'   => array( 'product-slider' ),
		'scripts'  => array( 'product-slider' ),
		'requires' => array( 'edd', 'theme' ),
		'default'  => true,
	),

	'product-archive'   => array(
		'class'    => DecentCore\Elementor\Widgets\Product_Archive::class,
		'title'    => __( 'Product Archive', 'decent-core' ),
		'category' => 'decent-products',
		'group'    => 'products',
		'icon'     => 'eicon-archive-posts',
		'keywords' => array( 'catalogue', 'archive', 'shop', 'filter' ),
		'styles'   => array(),
		'scripts'  => array(),
		'requires' => array( 'edd', 'theme' ),
		'default'  => true,
	),

	'product-hero'      => array(
		'class'    => DecentCore\Elementor\Widgets\Product_Hero::class,
		'title'    => __( 'Product Hero', 'decent-core' ),
		'category' => 'decent-products',
		'group'    => 'single',
		'icon'     => 'eicon-product-info',
		'keywords' => array( 'product', 'title', 'single' ),
		'styles'   => array(),
		'scripts'  => array(),
		'requires' => array( 'edd', 'theme' ),
		'default'  => true,
	),

	'product-tabs'      => array(
		'class'    => DecentCore\Elementor\Widgets\Product_Tabs::class,
		'title'    => __( 'Product Tabs', 'decent-core' ),
		'category' => 'decent-products',
		'group'    => 'single',
		'icon'     => 'eicon-tabs',
		'keywords' => array( 'product', 'tabs', 'changelog', 'reviews' ),
		'styles'   => array(),
		'scripts'  => array(),
		'requires' => array( 'edd', 'theme' ),
		'default'  => true,
	),

	'product-buy-box'   => array(
		'class'    => DecentCore\Elementor\Widgets\Product_Buy_Box::class,
		'title'    => __( 'Product Buy Box', 'decent-core' ),
		'category' => 'decent-products',
		'group'    => 'single',
		'icon'     => 'eicon-cart-medium',
		'keywords' => array( 'product', 'buy', 'cart', 'price' ),
		'styles'   => array(),
		'scripts'  => array(),
		'requires' => array( 'edd', 'theme' ),
		'default'  => true,
	),

	'product-related'   => array(
		'class'    => DecentCore\Elementor\Widgets\Product_Related::class,
		'title'    => __( 'Related Products', 'decent-core' ),
		'category' => 'decent-products',
		'group'    => 'single',
		'icon'     => 'eicon-posts-carousel',
		'keywords' => array( 'product', 'related', 'cross-sell' ),
		'styles'   => array(),
		'scripts'  => array(),
		'requires' => array( 'edd', 'theme' ),
		'default'  => true,
	),

	'blog-grid'         => array(
		'class'    => DecentCore\Elementor\Widgets\Blog_Grid::class,
		'title'    => __( 'Blog Grid', 'decent-core' ),
		'category' => 'decent-content',
		'group'    => 'blog',
		'icon'     => 'eicon-posts-grid',
		'keywords' => array( 'blog', 'posts', 'articles' ),
		'styles'   => array( 'blog-grid' ),
		'scripts'  => array(),
		'requires' => array( 'theme' ),
		'default'  => true,
	),

	'testimonial-grid'  => array(
		'class'    => DecentCore\Elementor\Widgets\Testimonial_Grid::class,
		'title'    => __( 'Testimonial Grid', 'decent-core' ),
		'category' => 'decent-content',
		'group'    => 'social',
		'icon'     => 'eicon-testimonial',
		'keywords' => array( 'testimonial', 'review', 'quote' ),
		'styles'   => array( 'testimonial-grid' ),
		'scripts'  => array(),
		'requires' => array(),
		'default'  => true,
	),

	'pricing-table'     => array(
		'class'    => DecentCore\Elementor\Widgets\Pricing_Table::class,
		'title'    => __( 'Pricing Table', 'decent-core' ),
		'category' => 'decent-content',
		'group'    => 'pricing',
		'icon'     => 'eicon-price-table',
		'keywords' => array( 'pricing', 'plans', 'licence' ),
		'styles'   => array( 'pricing-table' ),
		'scripts'  => array(),
		'requires' => array(),
		'default'  => true,
	),
);
