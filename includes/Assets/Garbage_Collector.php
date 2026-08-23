<?php
/**
 * Bundle sweeper.
 *
 * Bundles are never mutated, only added — the filename is a content hash — so
 * invalidation is simply "stop referencing the old one". That leaves orphans
 * behind after an update or a settings change, and this removes them.
 *
 * @package PixelomaticCore
 */

namespace PixelomaticCore\Assets;

defined( 'ABSPATH' ) || exit;

/**
 * Removes bundles nothing has asked for in a long time.
 */
final class Garbage_Collector {

	/**
	 * Cron hook.
	 */
	public const HOOK = 'pixelomatic_core_sweep_assets';

	/**
	 * How long an untouched bundle survives.
	 */
	private const MAX_AGE = 30 * DAY_IN_SECONDS;

	/**
	 * Filesystem access.
	 *
	 * @var Filesystem
	 */
	private $files;

	/**
	 * Constructor.
	 *
	 * @param Filesystem $files Filesystem.
	 */
	public function __construct( Filesystem $files ) {
		$this->files = $files;
	}

	/**
	 * Attaches hooks.
	 *
	 * @return void
	 */
	public function register(): void {
		// Wrapped: sweep() returns a count for the Tools button and WP-CLI,
		// and an action callback must not return anything.
		add_action(
			self::HOOK,
			function (): void {
				$this->sweep();
			}
		);

		if ( ! wp_next_scheduled( self::HOOK ) ) {
			wp_schedule_event( time() + HOUR_IN_SECONDS, 'weekly', self::HOOK );
		}
	}

	/**
	 * Deletes bundles not read for MAX_AGE.
	 *
	 * Access time rather than modification time: a bundle written once and
	 * served every day since is in constant use, and its mtime would say it
	 * was ancient.
	 *
	 * @return int Files removed.
	 */
	public function sweep(): int {
		$cutoff  = time() - self::MAX_AGE;
		$removed = 0;

		foreach ( $this->files->all() as $file ) {
			$touched = max( (int) @fileatime( $file ), (int) filemtime( $file ) ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged -- atime is unavailable on some mounts; the mtime fallback is the point.

			if ( $touched > $cutoff ) {
				continue;
			}

			if ( wp_delete_file_from_directory( $file, $this->files->base_dir() ) ) {
				++$removed;
			}
		}

		return $removed;
	}
}
