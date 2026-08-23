<?php
/**
 * Uninstall.
 *
 * Removes the plugin's own settings and generated files. It does NOT remove
 * builder templates or any product data unless the site owner explicitly asked
 * for that in the settings.
 *
 * People delete plugins to troubleshoot. A deletion that eats a site's headers
 * and footers turns a five-minute diagnosis into a rebuild, and there is no
 * undo.
 *
 * @package PixelomaticCore
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

$pixelomatic_core_settings = get_option( 'pixelomatic_core_settings', array() );
$pixelomatic_core_purge    = is_array( $pixelomatic_core_settings ) && ! empty( $pixelomatic_core_settings['remove_data_on_uninstall'] );

// Options are always removed: they are configuration, and reinstalling
// restores the defaults.
foreach (
	array(
		'pixelomatic_core_settings',
		'pixelomatic_core_version',
		'pixelomatic_core_activated_at',
		'pixelomatic_core_conditions_map',
		'pixelomatic_core_kit_seeded',
		'pixelomatic_core_breakpoints_synced',
	) as $pixelomatic_core_option
) {
	delete_option( $pixelomatic_core_option );
}

wp_clear_scheduled_hook( 'pixelomatic_core_sweep_assets' );

// Generated bundles are derived files; nothing is lost by removing them.
$pixelomatic_core_uploads = wp_upload_dir();
$pixelomatic_core_dir     = trailingslashit( (string) ( $pixelomatic_core_uploads['basedir'] ?? '' ) ) . 'pixelomatic-core';

if ( '' !== $pixelomatic_core_uploads['basedir'] && is_dir( $pixelomatic_core_dir ) ) {
	foreach ( array( 'css', 'js' ) as $pixelomatic_core_ext ) {
		$pixelomatic_core_files = glob( $pixelomatic_core_dir . '/' . $pixelomatic_core_ext . '/*' );

		foreach ( is_array( $pixelomatic_core_files ) ? $pixelomatic_core_files : array() as $pixelomatic_core_file ) {
			wp_delete_file( $pixelomatic_core_file );
		}
	}
}

// Content only when explicitly asked for.
if ( ! $pixelomatic_core_purge ) {
	return;
}

$pixelomatic_core_templates = get_posts(
	array(
		'post_type'      => 'pixelomatic_template',
		'post_status'    => 'any',
		'posts_per_page' => 500,
		'fields'         => 'ids',
		'no_found_rows'  => true,
	)
);

foreach ( $pixelomatic_core_templates as $pixelomatic_core_template ) {
	wp_delete_post( (int) $pixelomatic_core_template, true );
}
