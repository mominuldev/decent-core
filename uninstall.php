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
 * @package DecentCore
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

$decent_core_settings = get_option( 'decent_core_settings', array() );
$decent_core_purge    = is_array( $decent_core_settings ) && ! empty( $decent_core_settings['remove_data_on_uninstall'] );

// Options are always removed: they are configuration, and reinstalling
// restores the defaults.
foreach (
	array(
		'decent_core_settings',
		'decent_core_version',
		'decent_core_activated_at',
		'decent_core_conditions_map',
		'decent_core_kit_seeded',
		'decent_core_breakpoints_synced',
	) as $decent_core_option
) {
	delete_option( $decent_core_option );
}

wp_clear_scheduled_hook( 'decent_core_sweep_assets' );

// Generated bundles are derived files; nothing is lost by removing them.
$decent_core_uploads = wp_upload_dir();
$decent_core_dir     = trailingslashit( (string) ( $decent_core_uploads['basedir'] ?? '' ) ) . 'decent-core';

if ( '' !== $decent_core_uploads['basedir'] && is_dir( $decent_core_dir ) ) {
	foreach ( array( 'css', 'js' ) as $decent_core_ext ) {
		$decent_core_files = glob( $decent_core_dir . '/' . $decent_core_ext . '/*' );

		foreach ( is_array( $decent_core_files ) ? $decent_core_files : array() as $decent_core_file ) {
			wp_delete_file( $decent_core_file );
		}
	}
}

// Content only when explicitly asked for.
if ( ! $decent_core_purge ) {
	return;
}

$decent_core_templates = get_posts(
	array(
		'post_type'      => 'decent_template',
		'post_status'    => 'any',
		'posts_per_page' => 500,
		'fields'         => 'ids',
		'no_found_rows'  => true,
	)
);

foreach ( $decent_core_templates as $decent_core_template ) {
	wp_delete_post( (int) $decent_core_template, true );
}
