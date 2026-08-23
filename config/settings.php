<?php
/**
 * Settings schema.
 *
 * Every setting is declared once, here, with its type, default, allowed values
 * and sanitiser. From this one declaration the plugin derives the rendered
 * field, the REST argument schema, the validator, the default and the
 * export payload.
 *
 * A setting cannot exist without a sanitiser, because there is nowhere to
 * declare one without the other. That structural guarantee is worth more than
 * any amount of review discipline.
 *
 * @package PixelomaticCore
 */

defined( 'ABSPATH' ) || exit;

return array(

	// -- General ----------------------------------------------------------
	'breadcrumbs'        => array(
		'tab'      => 'general',
		'type'     => 'boolean',
		'default'  => true,
		'label'    => __( 'Breadcrumbs', 'pixelomatic-core' ),
		'help'     => __( 'Show the breadcrumb trail on archives and single products.', 'pixelomatic-core' ),
		'sanitize' => 'rest_sanitize_boolean',
	),
	'back_to_top'        => array(
		'tab'      => 'general',
		'type'     => 'boolean',
		'default'  => false,
		'label'    => __( 'Back to top button', 'pixelomatic-core' ),
		'help'     => __( 'Appears after the visitor scrolls past one viewport.', 'pixelomatic-core' ),
		'sanitize' => 'rest_sanitize_boolean',
	),

	// -- Extensions -------------------------------------------------------
	'module_builder'     => array(
		'tab'      => 'extensions',
		'type'     => 'boolean',
		'default'  => true,
		'label'    => __( 'Header & Footer Builder', 'pixelomatic-core' ),
		'help'     => __( 'Build headers and footers in Elementor and assign them by condition.', 'pixelomatic-core' ),
		'sanitize' => 'rest_sanitize_boolean',
	),
	'module_mega_menu'   => array(
		'tab'      => 'extensions',
		'type'     => 'boolean',
		'default'  => false,
		'label'    => __( 'Mega Menu', 'pixelomatic-core' ),
		'help'     => __( 'Adds an Elementor content panel to any menu item.', 'pixelomatic-core' ),
		'sanitize' => 'rest_sanitize_boolean',
	),
	'module_edd'         => array(
		'tab'      => 'extensions',
		'type'     => 'boolean',
		'default'  => true,
		'label'    => __( 'Product widgets', 'pixelomatic-core' ),
		'help'     => __( 'Elementor widgets that read Easy Digital Downloads products. Requires EDD.', 'pixelomatic-core' ),
		'sanitize' => 'rest_sanitize_boolean',
	),

	// -- Performance ------------------------------------------------------
	'assets_bundling'    => array(
		'tab'      => 'performance',
		'type'     => 'boolean',
		'default'  => true,
		'label'    => __( 'Combine widget assets', 'pixelomatic-core' ),
		'help'     => __( 'Writes one CSS and one JS file per widget combination to the uploads folder, instead of one request per widget. Falls back automatically if uploads is not writable.', 'pixelomatic-core' ),
		'sanitize' => 'rest_sanitize_boolean',
	),
	'assets_minify'      => array(
		'tab'      => 'performance',
		'type'     => 'boolean',
		'default'  => true,
		'label'    => __( 'Minify bundled assets', 'pixelomatic-core' ),
		'help'     => __( 'Strips comments and whitespace. No renaming or restructuring.', 'pixelomatic-core' ),
		'sanitize' => 'rest_sanitize_boolean',
	),
	'disable_eicons'     => array(
		'tab'      => 'performance',
		'type'     => 'boolean',
		'default'  => false,
		'label'    => __( 'Skip Elementor icon fonts on the front end', 'pixelomatic-core' ),
		'help'     => __( 'The theme uses inline SVG. Turn this on only if no widget on the site uses an Elementor icon.', 'pixelomatic-core' ),
		'sanitize' => 'rest_sanitize_boolean',
	),
	'sync_breakpoints'   => array(
		'tab'      => 'performance',
		'type'     => 'boolean',
		'default'  => true,
		'label'    => __( 'Match Elementor breakpoints to the theme', 'pixelomatic-core' ),
		'help'     => __( 'Sets Elementor to 1180 / 1024 / 900 / 768 / 560 so editor previews agree with the stylesheet. Without this, widget overrides and the theme CSS disagree about what "tablet" means.', 'pixelomatic-core' ),
		'sanitize' => 'rest_sanitize_boolean',
	),

	// -- Catalogue --------------------------------------------------------
	'catalogue_per_page' => array(
		'tab'      => 'edd',
		'type'     => 'integer',
		'default'  => 12,
		'min'      => 1,
		'max'      => 48,
		'label'    => __( 'Products per page', 'pixelomatic-core' ),
		'help'     => __( 'Applies to the catalogue and to product category archives.', 'pixelomatic-core' ),
		'sanitize' => 'absint',
	),
	'best_seller_sales'  => array(
		'tab'      => 'edd',
		'type'     => 'integer',
		'default'  => 100,
		'min'      => 1,
		'max'      => 100000,
		'label'    => __( 'Best seller threshold', 'pixelomatic-core' ),
		'help'     => __( 'Sales at or above this number earn the BEST SELLER badge.', 'pixelomatic-core' ),
		'sanitize' => 'absint',
	),
	'new_badge_days'     => array(
		'tab'      => 'edd',
		'type'     => 'integer',
		'default'  => 30,
		'min'      => 1,
		'max'      => 365,
		'label'    => __( 'New badge window (days)', 'pixelomatic-core' ),
		'help'     => __( 'Products published within this many days show the NEW badge, which takes precedence over BEST SELLER.', 'pixelomatic-core' ),
		'sanitize' => 'absint',
	),
	'default_sort'       => array(
		'tab'      => 'edd',
		'type'     => 'string',
		'default'  => 'popular',
		'allowed'  => array( 'popular', 'new', 'price-asc', 'price-desc', 'rating', 'title' ),
		'label'    => __( 'Default catalogue sort', 'pixelomatic-core' ),
		'help'     => __( 'The order used when the visitor has not chosen one.', 'pixelomatic-core' ),
		'sanitize' => 'sanitize_key',
	),
);
