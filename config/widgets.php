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
 * @package PixelomaticCore
 */

defined( 'ABSPATH' ) || exit;

return array(

	// The slug stays `hero-search` — it is the widget's identity in the
	// Elementor name and on every page already built with it. The title and
	// icon moved with the redesign: the search form is now one option inside
	// the landing hero rather than the thing the widget is.
	'hero-search'            => array(
		'class'    => PixelomaticCore\Elementor\Widgets\Hero_Search::class,
		'title'    => __( 'Hero', 'pixelomatic-core' ),
		'category' => 'pixelomatic-layout',
		'group'    => 'hero',
		'icon'     => 'eicon-banner',
		'keywords' => array( 'hero', 'banner', 'landing', 'cta', 'search' ),
		'styles'   => array( 'hero-search' ),
		'scripts'  => array(),
		'requires' => array(),
		'default'  => true,
	),

	'heading'                => array(
		'class'    => PixelomaticCore\Elementor\Widgets\Heading::class,
		'title'    => __( 'Heading', 'pixelomatic-core' ),
		'category' => 'pixelomatic-content',
		'group'    => 'heading',
		'icon'     => 'eicon-t-letter',
		'keywords' => array( 'heading', 'title', 'section', 'eyebrow', 'intro' ),
		'styles'   => array( 'heading' ),
		'scripts'  => array(),
		'requires' => array(),
		'default'  => true,
	),

	// A single box, not a grid of them: the row is composed with Elementor's
	// own grid container, so the columns behave like every other container on
	// the page instead of like a control only this widget understands.
	'icon-box'               => array(
		'class'    => PixelomaticCore\Elementor\Widgets\Icon_Box::class,
		'title'    => __( 'Icon Box', 'pixelomatic-core' ),
		'category' => 'pixelomatic-content',
		'group'    => 'feature',
		'icon'     => 'eicon-icon-box',
		'keywords' => array( 'icon', 'box', 'feature', 'service', 'card' ),
		'styles'   => array( 'icon-box' ),
		'scripts'  => array(),
		'requires' => array(),
		'default'  => true,
	),

	'category-grid'          => array(
		'class'    => PixelomaticCore\Elementor\Widgets\Category_Grid::class,
		'title'    => __( 'Category Grid', 'pixelomatic-core' ),
		'category' => 'pixelomatic-layout',
		'group'    => 'category',
		'icon'     => 'eicon-gallery-grid',
		'keywords' => array( 'category', 'taxonomy', 'grid' ),
		'styles'   => array( 'category-grid' ),
		'scripts'  => array(),
		'requires' => array(),
		'default'  => true,
	),

	'feature-grid'           => array(
		'class'    => PixelomaticCore\Elementor\Widgets\Feature_Grid::class,
		'title'    => __( 'Feature Grid', 'pixelomatic-core' ),
		'category' => 'pixelomatic-content',
		'group'    => 'feature',
		'icon'     => 'eicon-info-box',
		'keywords' => array( 'feature', 'icon', 'grid' ),
		'styles'   => array( 'feature-grid' ),
		'scripts'  => array(),
		'requires' => array(),
		'default'  => true,
	),

	'benefit-grid'           => array(
		'class'    => PixelomaticCore\Elementor\Widgets\Benefit_Grid::class,
		'title'    => __( 'Benefit Grid', 'pixelomatic-core' ),
		'category' => 'pixelomatic-content',
		'group'    => 'feature',
		'icon'     => 'eicon-checkbox',
		'keywords' => array( 'benefit', 'dark', 'grid' ),
		'styles'   => array( 'benefit-grid' ),
		'scripts'  => array(),
		'requires' => array(),
		'default'  => true,
	),

	// The landing's WHY BUY HERE band. Distinct from benefit-grid, which is a
	// plain three-up of icon/title/text on the same dark ground: this one is
	// the design's asymmetric six-tile composition, where two tiles lead with
	// a figure, two carry a chip row, and the two wide ones carry a release
	// list and a checklist. Both are kept — a section that only needs three
	// promises should not have to delete four tiles to get there.
	'why-buy'                => array(
		'class'    => PixelomaticCore\Elementor\Widgets\Why_Buy::class,
		'title'    => __( 'Why Buy Here', 'pixelomatic-core' ),
		'category' => 'pixelomatic-layout',
		'group'    => 'trust',
		'icon'     => 'eicon-checkbox',
		'keywords' => array( 'why', 'trust', 'promise', 'benefit', 'guarantee', 'dark' ),
		'styles'   => array( 'why-buy' ),
		'scripts'  => array(),
		'requires' => array(),
		'default'  => true,
	),

	'guarantee-grid'         => array(
		'class'    => PixelomaticCore\Elementor\Widgets\Guarantee_Grid::class,
		'title'    => __( 'Guarantee Grid', 'pixelomatic-core' ),
		'category' => 'pixelomatic-content',
		'group'    => 'feature',
		'icon'     => 'eicon-lock-user',
		'keywords' => array( 'guarantee', 'trust', 'refund' ),
		'styles'   => array( 'guarantee-grid' ),
		'scripts'  => array(),
		'requires' => array(),
		'default'  => true,
	),

	'stats-counter'          => array(
		'class'    => PixelomaticCore\Elementor\Widgets\Stats_Counter::class,
		'title'    => __( 'Statistics Counter', 'pixelomatic-core' ),
		'category' => 'pixelomatic-content',
		'group'    => 'stats',
		'icon'     => 'eicon-counter',
		'keywords' => array( 'stats', 'counter', 'metrics' ),
		'styles'   => array( 'stats-counter' ),
		'scripts'  => array(),
		'requires' => array(),
		'default'  => true,
	),

	'faq-accordion'          => array(
		'class'    => PixelomaticCore\Elementor\Widgets\Faq_Accordion::class,
		'title'    => __( 'FAQ Accordion', 'pixelomatic-core' ),
		'category' => 'pixelomatic-content',
		'group'    => 'faq',
		'icon'     => 'eicon-help-o',
		'keywords' => array( 'faq', 'accordion', 'questions' ),
		'styles'   => array(),
		'scripts'  => array(),
		'requires' => array(),
		'default'  => true,
	),

	// A standalone accordion, independent of the theme's `.accordion`
	// component — its own `pix-` markup, styles and script, for general
	// content (not only Q&A) and with a smooth open/close transition the
	// theme's own accordion does not have.
	'accordion'              => array(
		'class'    => PixelomaticCore\Elementor\Widgets\Accordion::class,
		'title'    => __( 'Accordion', 'pixelomatic-core' ),
		'category' => 'pixelomatic-content',
		'group'    => 'accordion',
		'icon'     => 'eicon-accordion',
		'keywords' => array( 'accordion', 'toggle', 'collapse', 'expand', 'faq' ),
		'styles'   => array( 'accordion' ),
		'scripts'  => array( 'accordion' ),
		'requires' => array(),
		'default'  => true,
	),

	// A single `.btn` — the theme's own button, not a class of its own. `style`
	// covers every modifier the stylesheet ships (`btn--primary` through
	// `btn--danger`); this widget is a panel over that set, never a second
	// button component.
	'button'                 => array(
		'class'    => PixelomaticCore\Elementor\Widgets\Button::class,
		'title'    => __( 'Button', 'pixelomatic-core' ),
		'category' => 'pixelomatic-content',
		'group'    => 'cta',
		'icon'     => 'eicon-button',
		'keywords' => array( 'button', 'btn', 'cta', 'link', 'action' ),
		'styles'   => array( 'button' ),
		'scripts'  => array(),
		'requires' => array(),
		'default'  => true,
	),

	'cta'                    => array(
		'class'    => PixelomaticCore\Elementor\Widgets\Cta::class,
		'title'    => __( 'CTA', 'pixelomatic-core' ),
		'category' => 'pixelomatic-content',
		'group'    => 'cta',
		'icon'     => 'eicon-call-to-action',
		'keywords' => array( 'cta', 'banner', 'action' ),
		'styles'   => array(),
		'scripts'  => array(),
		'requires' => array(),
		'default'  => true,
	),

	'promo-card'             => array(
		'class'    => PixelomaticCore\Elementor\Widgets\Promo_Card::class,
		'title'    => __( 'Promo Card', 'pixelomatic-core' ),
		'category' => 'pixelomatic-content',
		'group'    => 'cta',
		'icon'     => 'eicon-price-table',
		'keywords' => array( 'promo', 'bundle', 'offer' ),
		'styles'   => array(),
		'scripts'  => array(),
		'requires' => array(),
		'default'  => true,
	),

	'breadcrumb'             => array(
		'class'    => PixelomaticCore\Elementor\Widgets\Breadcrumb::class,
		'title'    => __( 'Breadcrumb', 'pixelomatic-core' ),
		'category' => 'pixelomatic-layout',
		'group'    => 'navigation',
		'icon'     => 'eicon-yoast',
		'keywords' => array( 'breadcrumb', 'trail', 'navigation' ),
		'styles'   => array(),
		'scripts'  => array(),
		'requires' => array( 'theme' ),
		'default'  => true,
	),

	'product-grid'           => array(
		'class'    => PixelomaticCore\Elementor\Widgets\Product_Grid::class,
		'title'    => __( 'Product Grid', 'pixelomatic-core' ),
		'category' => 'pixelomatic-products',
		'group'    => 'products',
		'icon'     => 'eicon-products',
		'keywords' => array( 'product', 'edd', 'download', 'shop' ),
		'styles'   => array( 'product-grid' ),
		'scripts'  => array( 'product-grid' ),
		'requires' => array( 'edd', 'theme' ),
		'default'  => true,
	),

	'product-rank-list'      => array(
		'class'    => PixelomaticCore\Elementor\Widgets\Product_Rank_List::class,
		'title'    => __( 'Product Rank List', 'pixelomatic-core' ),
		'category' => 'pixelomatic-products',
		'group'    => 'products',
		'icon'     => 'eicon-bullet-list',
		'keywords' => array( 'product', 'best seller', 'ranking' ),
		'styles'   => array(),
		'scripts'  => array(),
		'requires' => array( 'edd' ),
		'default'  => true,
	),

	'hero-split'             => array(
		'class'    => PixelomaticCore\Elementor\Widgets\Hero_Split::class,
		'title'    => __( 'Hero Split', 'pixelomatic-core' ),
		'category' => 'pixelomatic-layout',
		'group'    => 'hero',
		'icon'     => 'eicon-image-box',
		'keywords' => array( 'hero', 'split', 'banner' ),
		'styles'   => array(),
		'scripts'  => array(),
		'requires' => array(),
		'default'  => true,
	),

	'trust-band'             => array(
		'class'    => PixelomaticCore\Elementor\Widgets\Trust_Band::class,
		'title'    => __( 'Trust Band', 'pixelomatic-core' ),
		'category' => 'pixelomatic-content',
		'group'    => 'trust',
		'icon'     => 'eicon-logo',
		'keywords' => array( 'trust', 'clients', 'logos', 'metrics' ),
		'styles'   => array(),
		'scripts'  => array(),
		'requires' => array(),
		'default'  => true,
	),

	'showcase'               => array(
		'class'    => PixelomaticCore\Elementor\Widgets\Showcase::class,
		'title'    => __( 'Showcase', 'pixelomatic-core' ),
		'category' => 'pixelomatic-content',
		'group'    => 'feature',
		'icon'     => 'eicon-image-before-after',
		'keywords' => array( 'showcase', 'zigzag', 'alternating' ),
		'styles'   => array(),
		'scripts'  => array(),
		'requires' => array( 'theme' ),
		'default'  => true,
	),

	'newsletter'             => array(
		'class'    => PixelomaticCore\Elementor\Widgets\Newsletter::class,
		'title'    => __( 'Newsletter', 'pixelomatic-core' ),
		'category' => 'pixelomatic-content',
		'group'    => 'form',
		'icon'     => 'eicon-email-field',
		'keywords' => array( 'newsletter', 'email', 'subscribe' ),
		'styles'   => array(),
		'scripts'  => array(),
		'requires' => array(),
		'default'  => true,
	),

	// No 'requires'. The widget posts to EDD's login handler where EDD is
	// active and to wp-login.php where it is not, so it has somewhere to send
	// a sign-in either way.
	'login-form'             => array(
		'class'    => PixelomaticCore\Elementor\Widgets\Login_Form::class,
		'title'    => __( 'Login Form', 'pixelomatic-core' ),
		'category' => 'pixelomatic-content',
		'group'    => 'form',
		'icon'     => 'eicon-lock-user',
		'keywords' => array( 'login', 'sign in', 'account', 'password', 'form' ),
		'styles'   => array( 'login-form' ),
		'scripts'  => array( 'login-form' ),
		'requires' => array(),
		'default'  => true,
	),

	'plugin-list'            => array(
		'class'    => PixelomaticCore\Elementor\Widgets\Plugin_List::class,
		'title'    => __( 'Plugin List', 'pixelomatic-core' ),
		'category' => 'pixelomatic-products',
		'group'    => 'products',
		'icon'     => 'eicon-post-list',
		'keywords' => array( 'plugin', 'product', 'list', 'edd' ),
		'styles'   => array(),
		'scripts'  => array(),
		'requires' => array( 'edd', 'theme' ),
		'default'  => true,
	),

	'product-slider'         => array(
		'class'       => PixelomaticCore\Elementor\Widgets\Product_Slider::class,
		'title'       => __( 'Product Slider', 'pixelomatic-core' ),
		'category'    => 'pixelomatic-products',
		'group'       => 'products',
		'icon'        => 'eicon-slider-push',
		'keywords'    => array( 'product', 'slider', 'carousel', 'edd' ),
		// No stylesheet or script of its own: the carousel is shared and the
		// cards are the theme's.
		'styles'      => array( 'carousel' ),
		'scripts'     => array( 'carousel' ),
		'style_deps'  => array( 'swiper' ),
		'script_deps' => array( 'swiper' ),
		'requires'    => array( 'edd', 'theme' ),
		'default'     => true,
	),

	'product-archive'        => array(
		'class'    => PixelomaticCore\Elementor\Widgets\Product_Archive::class,
		'title'    => __( 'Product Archive', 'pixelomatic-core' ),
		'category' => 'pixelomatic-products',
		'group'    => 'products',
		'icon'     => 'eicon-archive-posts',
		'keywords' => array( 'catalogue', 'archive', 'shop', 'filter' ),
		'styles'   => array(),
		'scripts'  => array(),
		'requires' => array( 'edd', 'theme' ),
		'default'  => true,
	),

	'product-hero'           => array(
		'class'    => PixelomaticCore\Elementor\Widgets\Product_Hero::class,
		'title'    => __( 'Product Hero', 'pixelomatic-core' ),
		'category' => 'pixelomatic-products',
		'group'    => 'single',
		'icon'     => 'eicon-product-info',
		'keywords' => array( 'product', 'title', 'single' ),
		'styles'   => array(),
		'scripts'  => array(),
		'requires' => array( 'edd', 'theme' ),
		'default'  => true,
	),

	// The bar under the hero. Anchor links to real sections, not a tablist:
	// every section below is rendered, crawlable and reachable with
	// JavaScript off.
	'product-section-nav'    => array(
		'class'    => PixelomaticCore\Elementor\Widgets\Product_Section_Nav::class,
		'title'    => __( 'Product Section Nav', 'pixelomatic-core' ),
		'category' => 'pixelomatic-products',
		'group'    => 'single',
		'icon'     => 'eicon-nav-menu',
		'keywords' => array( 'product', 'nav', 'anchor', 'sections', 'tabs' ),
		'styles'   => array(),
		'scripts'  => array(),
		'requires' => array( 'edd', 'theme' ),
		'default'  => true,
	),

	// The slug is still `product-tabs`: it is the widget's identity on every
	// page already built with it. What it renders is the theme's sections,
	// which is what the tabs became.
	'product-tabs'           => array(
		'class'    => PixelomaticCore\Elementor\Widgets\Product_Tabs::class,
		'title'    => __( 'Product Sections', 'pixelomatic-core' ),
		'category' => 'pixelomatic-products',
		'group'    => 'single',
		'icon'     => 'eicon-post-content',
		'keywords' => array( 'product', 'sections', 'tabs', 'changelog', 'reviews' ),
		'styles'   => array(),
		'scripts'  => array(),
		'requires' => array( 'edd', 'theme' ),
		'default'  => true,
	),

	// The same sections one at a time, for a layout that wants something of
	// its own between them. Each renders the theme part its section owns and
	// nothing at all when the product has no content for it.
	'product-overview'       => array(
		'class'    => PixelomaticCore\Elementor\Widgets\Product_Overview::class,
		'title'    => __( 'Product Overview', 'pixelomatic-core' ),
		'category' => 'pixelomatic-products',
		'group'    => 'single',
		'icon'     => 'eicon-document-file',
		'keywords' => array( 'product', 'overview', 'description', 'content' ),
		'styles'   => array(),
		'scripts'  => array(),
		'requires' => array( 'edd', 'theme' ),
		'default'  => true,
	),

	'product-features'       => array(
		'class'    => PixelomaticCore\Elementor\Widgets\Product_Features::class,
		'title'    => __( 'Product Features', 'pixelomatic-core' ),
		'category' => 'pixelomatic-products',
		'group'    => 'single',
		'icon'     => 'eicon-featured-image',
		'keywords' => array( 'product', 'features', 'grid', 'single' ),
		'styles'   => array(),
		'scripts'  => array(),
		'requires' => array( 'edd', 'theme' ),
		'default'  => true,
	),

	'product-specifications' => array(
		'class'    => PixelomaticCore\Elementor\Widgets\Product_Specifications::class,
		'title'    => __( 'Product Specifications', 'pixelomatic-core' ),
		'category' => 'pixelomatic-products',
		'group'    => 'single',
		'icon'     => 'eicon-table',
		'keywords' => array( 'product', 'specifications', 'specs', 'technical' ),
		'styles'   => array(),
		'scripts'  => array(),
		'requires' => array( 'edd', 'theme' ),
		'default'  => true,
	),

	'product-changelog'      => array(
		'class'    => PixelomaticCore\Elementor\Widgets\Product_Changelog::class,
		'title'    => __( 'Product Changelog', 'pixelomatic-core' ),
		'category' => 'pixelomatic-products',
		'group'    => 'single',
		'icon'     => 'eicon-time-line',
		'keywords' => array( 'product', 'changelog', 'releases', 'versions' ),
		'styles'   => array(),
		'scripts'  => array(),
		'requires' => array( 'edd', 'theme' ),
		'default'  => true,
	),

	'product-reviews'        => array(
		'class'    => PixelomaticCore\Elementor\Widgets\Product_Reviews::class,
		'title'    => __( 'Product Reviews', 'pixelomatic-core' ),
		'category' => 'pixelomatic-products',
		'group'    => 'single',
		'icon'     => 'eicon-review',
		'keywords' => array( 'product', 'reviews', 'rating', 'stars' ),
		'styles'   => array(),
		'scripts'  => array(),
		'requires' => array( 'edd', 'theme' ),
		'default'  => true,
	),

	'product-support'        => array(
		'class'    => PixelomaticCore\Elementor\Widgets\Product_Support::class,
		'title'    => __( 'Product Support', 'pixelomatic-core' ),
		'category' => 'pixelomatic-products',
		'group'    => 'single',
		'icon'     => 'eicon-help-o',
		'keywords' => array( 'product', 'support', 'faq', 'questions' ),
		'styles'   => array(),
		'scripts'  => array(),
		'requires' => array( 'edd', 'theme' ),
		'default'  => true,
	),

	'product-cta'            => array(
		'class'    => PixelomaticCore\Elementor\Widgets\Product_Cta::class,
		'title'    => __( 'Product CTA', 'pixelomatic-core' ),
		'category' => 'pixelomatic-products',
		'group'    => 'single',
		'icon'     => 'eicon-call-to-action',
		'keywords' => array( 'product', 'cta', 'buy', 'demo' ),
		'styles'   => array(),
		'scripts'  => array(),
		'requires' => array( 'edd', 'theme' ),
		'default'  => true,
	),

	'product-buy-box'        => array(
		'class'    => PixelomaticCore\Elementor\Widgets\Product_Buy_Box::class,
		'title'    => __( 'Product Buy Box', 'pixelomatic-core' ),
		'category' => 'pixelomatic-products',
		'group'    => 'single',
		'icon'     => 'eicon-cart-medium',
		'keywords' => array( 'product', 'buy', 'cart', 'price' ),
		'styles'   => array(),
		'scripts'  => array(),
		'requires' => array( 'edd', 'theme' ),
		'default'  => true,
	),

	'product-related'        => array(
		'class'    => PixelomaticCore\Elementor\Widgets\Product_Related::class,
		'title'    => __( 'Related Products', 'pixelomatic-core' ),
		'category' => 'pixelomatic-products',
		'group'    => 'single',
		'icon'     => 'eicon-posts-carousel',
		'keywords' => array( 'product', 'related', 'cross-sell' ),
		'styles'   => array(),
		'scripts'  => array(),
		'requires' => array( 'edd', 'theme' ),
		'default'  => true,
	),

	'blog-grid'              => array(
		'class'    => PixelomaticCore\Elementor\Widgets\Blog_Grid::class,
		'title'    => __( 'Blog Grid', 'pixelomatic-core' ),
		'category' => 'pixelomatic-content',
		'group'    => 'blog',
		'icon'     => 'eicon-posts-grid',
		'keywords' => array( 'blog', 'posts', 'articles' ),
		'styles'   => array( 'blog-grid' ),
		'scripts'  => array(),
		'requires' => array( 'theme' ),
		'default'  => true,
	),

	'testimonial-grid'       => array(
		'class'       => PixelomaticCore\Elementor\Widgets\Testimonial_Grid::class,
		'title'       => __( 'Testimonials', 'pixelomatic-core' ),
		'category'    => 'pixelomatic-content',
		'group'       => 'social',
		'icon'        => 'eicon-testimonial',
		'keywords'    => array( 'testimonial', 'review', 'quote', 'slider', 'carousel' ),
		// `carousel` is the shared slider chrome and its Swiper boot — every
		// slider widget lists it and needs no script of its own. Elementor
		// already ships Swiper 8.4.5 under the `swiper` handles, so the
		// carousel costs no bytes of ours either.
		'styles'      => array( 'carousel', 'testimonial-grid' ),
		'scripts'     => array( 'carousel' ),
		'style_deps'  => array( 'swiper' ),
		'script_deps' => array( 'swiper' ),
		'requires'    => array(),
		'default'     => true,
	),

	'pricing-table'          => array(
		'class'    => PixelomaticCore\Elementor\Widgets\Pricing_Table::class,
		'title'    => __( 'Pricing Table', 'pixelomatic-core' ),
		'category' => 'pixelomatic-content',
		'group'    => 'pricing',
		'icon'     => 'eicon-price-table',
		'keywords' => array( 'pricing', 'plans', 'licence' ),
		'styles'   => array( 'pricing-table' ),
		'scripts'  => array(),
		'requires' => array(),
		'default'  => true,
	),

	'site-logo'              => array(
		'class'    => PixelomaticCore\Elementor\Widgets\Site_Logo::class,
		'title'    => __( 'Site Logo', 'pixelomatic-core' ),
		'category' => 'pixelomatic-header',
		'group'    => 'branding',
		'icon'     => 'eicon-site-logo',
		'keywords' => array( 'logo', 'brand', 'header' ),
		'styles'   => array(),
		'scripts'  => array(),
		'requires' => array(),
		'default'  => true,
	),

	// The navigation with a burger and an off-canvas panel of its own, for a
	// header built in Elementor: the theme's mobile menu is revealed by
	// `.site-header__inner.is-open`, an ancestor only its own static header
	// has, so a plain Nav Menu in a builder header is gone on a phone. Ported
	// from Genesis Core's `de-navigation-menu` — same feature list, through
	// the theme's walker and the theme's tokens.
	'navigation-menu'        => array(
		'class'    => PixelomaticCore\Elementor\Widgets\Navigation_Menu::class,
		'title'    => __( 'Navigation Menu', 'pixelomatic-core' ),
		'category' => 'pixelomatic-header',
		'group'    => 'navigation',
		'icon'     => 'eicon-menu-bar',
		'keywords' => array( 'menu', 'navigation', 'header', 'mobile', 'burger', 'offcanvas' ),
		'styles'   => array( 'navigation-menu' ),
		'scripts'  => array( 'navigation-menu' ),
		'requires' => array( 'theme' ),
		'default'  => true,
	),

	'nav-menu'               => array(
		'class'    => PixelomaticCore\Elementor\Widgets\Nav_Menu::class,
		'title'    => __( 'Nav Menu', 'pixelomatic-core' ),
		'category' => 'pixelomatic-header',
		'group'    => 'navigation',
		'icon'     => 'eicon-nav-menu',
		'keywords' => array( 'menu', 'navigation', 'header' ),
		'styles'   => array(),
		'scripts'  => array(),
		'requires' => array( 'theme' ),
		'default'  => true,
	),

	'copyright'              => array(
		'class'    => PixelomaticCore\Elementor\Widgets\Copyright::class,
		'title'    => __( 'Copyright', 'pixelomatic-core' ),
		'category' => 'pixelomatic-header',
		'group'    => 'footer',
		'icon'     => 'eicon-copyright',
		'keywords' => array( 'copyright', 'footer', 'year' ),
		'styles'   => array(),
		'scripts'  => array(),
		'requires' => array(),
		'default'  => true,
	),
);
