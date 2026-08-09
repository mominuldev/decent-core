<?php
/**
 * Design token mirror.
 *
 * The theme's assets/css/base.css is the source of truth. This array exists
 * for one purpose: seeding Elementor's Global Colors and Fonts so an editor
 * picking "Blue" from the global picker gets the same value the stylesheet
 * uses.
 *
 * The duplication is real and is the reason `wp decent-core tokens verify`
 * exists — it parses base.css and diffs it against this file, so drift is a
 * build failure rather than a slow visual rot.
 *
 * Only flat colours are listed. The palette's gradients are deliberately not
 * exposed to the picker: an editor must not be able to author a tenth one.
 *
 * @package DecentCore
 */

defined( 'ABSPATH' ) || exit;

return array(
	'colors'      => array(
		'blue'     => array( 'Blue', '#007BFF' ),
		'blue-600' => array( 'Blue Deep', '#0069D9' ),
		'ink'      => array( 'Ink', '#212529' ),
		'muted'    => array( 'Muted', '#6C757D' ),
		'green'    => array( 'Green', '#28A745' ),
		'yellow'   => array( 'Yellow', '#FFC107' ),
		'red'      => array( 'Red', '#DC3545' ),
		'border'   => array( 'Border', '#E9ECEF' ),
		'gray-50'  => array( 'Gray 50', '#F8F8F8' ),
		'white'    => array( 'White', '#FFFFFF' ),
	),
	'fonts'       => array(
		'sans' => array( 'Inter', 'Inter' ),
		'mono' => array( 'JetBrains Mono', 'JetBrains Mono' ),
	),
	// Desktop-first max-width breakpoints, matching the theme stylesheet.
	'breakpoints' => array(
		'laptop'       => 1180,
		'tablet_extra' => 1024,
		'tablet'       => 900,
		'mobile_extra' => 768,
		'mobile'       => 560,
	),
);
