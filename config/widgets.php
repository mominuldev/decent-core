<?php
/**
 * Widget map.
 *
 * The plugin's database of itself. Four things read this one array:
 *
 *   Widget_Registry  decides what to register with Elementor
 *   Admin_Page       generates the per-widget toggles, so there is no second
 *                    list to keep in step
 *   Asset_Manager    registers a style and script handle per widget
 *   Bundler          resolves a page's widget set into one file
 *
 * Adding a widget is a directory and one entry here. Nothing else changes.
 *
 * @package DecentCore
 */

defined( 'ABSPATH' ) || exit;

return array(

	'hero-search' => array(
		'class'    => DecentCore\Elementor\Widgets\Hero_Search::class,
		'title'    => __( 'Hero Search', 'decent-core' ),
		'category' => 'decent-layout',
		'group'    => 'hero',
		'icon'     => 'eicon-search',
		'keywords' => array( 'hero', 'search', 'banner', 'header' ),
		'styles'   => array( 'hero-search' ),
		'scripts'  => array(),
		'requires' => array(),
		'default'  => true,
	),
);
