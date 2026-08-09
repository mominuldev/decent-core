<?php
/**
 * Filesystem access for generated assets.
 *
 * Every path is built from a hash this plugin generated, under a fixed
 * directory, with a fixed extension. No segment is ever caller-supplied, so
 * traversal is structurally impossible rather than merely guarded against.
 *
 * @package DecentCore
 */

namespace DecentCore\Assets;

defined( 'ABSPATH' ) || exit;

/**
 * Reads and writes bundles under uploads/decent-core/.
 */
final class Filesystem {

	/**
	 * Directory name inside uploads.
	 */
	private const DIR = 'decent-core';

	/**
	 * Cached writability result for this request.
	 *
	 * @var bool|null
	 */
	private $writable = null;

	/**
	 * Returns the absolute path of a bundle.
	 *
	 * @param string $hash      Bundle hash.
	 * @param string $extension 'css' or 'js'.
	 * @return string
	 */
	public function path( string $hash, string $extension ): string {
		return $this->base_dir() . '/' . $this->safe( $extension ) . '/' . $this->safe( $hash ) . '.' . $this->safe( $extension );
	}

	/**
	 * Returns the public URL of a bundle.
	 *
	 * @param string $hash      Bundle hash.
	 * @param string $extension 'css' or 'js'.
	 * @return string
	 */
	public function url( string $hash, string $extension ): string {
		return $this->base_url() . '/' . $this->safe( $extension ) . '/' . $this->safe( $hash ) . '.' . $this->safe( $extension );
	}

	/**
	 * Whether a bundle already exists.
	 *
	 * @param string $hash      Bundle hash.
	 * @param string $extension File extension.
	 * @return bool
	 */
	public function exists( string $hash, string $extension ): bool {
		return file_exists( $this->path( $hash, $extension ) );
	}

	/**
	 * Writes a bundle.
	 *
	 * @param string $hash      Bundle hash.
	 * @param string $extension File extension.
	 * @param string $contents  File contents.
	 * @return bool True on success.
	 */
	public function write( string $hash, string $extension, string $contents ): bool {
		if ( ! $this->is_writable() ) {
			return false;
		}

		$path = $this->path( $hash, $extension );
		$dir  = dirname( $path );

		if ( ! wp_mkdir_p( $dir ) ) {
			return false;
		}

		$this->protect( $dir );

		// Written to a temporary name and renamed, so a concurrent request
		// never reads a half-written bundle.
		$temp = $path . '.' . wp_generate_password( 6, false ) . '.tmp';

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents -- Generated asset under uploads; WP_Filesystem's credential flow does not apply on a front-end request.
		if ( false === file_put_contents( $temp, $contents ) ) {
			return false;
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.rename_rename -- Atomic replace of our own temp file.
		if ( ! rename( $temp, $path ) ) {
			wp_delete_file( $temp );
			return false;
		}

		return true;
	}

	/**
	 * Whether the uploads directory can be written to.
	 *
	 * A read-only uploads folder is a normal hardened-host configuration, not
	 * an error. The bundler simply steps aside when this is false.
	 *
	 * @return bool
	 */
	public function is_writable(): bool {
		if ( null === $this->writable ) {
			$uploads = wp_upload_dir();

			$this->writable = empty( $uploads['error'] )
				&& ! empty( $uploads['basedir'] )
				&& wp_is_writable( $uploads['basedir'] );
		}

		return (bool) $this->writable;
	}

	/**
	 * Returns every bundle file currently on disk.
	 *
	 * @return string[] Absolute paths.
	 */
	public function all(): array {
		$files = array();

		foreach ( array( 'css', 'js' ) as $extension ) {
			$found = glob( $this->base_dir() . '/' . $extension . '/*.' . $extension );

			if ( is_array( $found ) ) {
				$files = array_merge( $files, $found );
			}
		}

		return $files;
	}

	/**
	 * Deletes every generated bundle.
	 *
	 * @return int Number of files removed.
	 */
	public function purge(): int {
		$removed = 0;

		foreach ( $this->all() as $file ) {
			if ( wp_delete_file_from_directory( $file, $this->base_dir() ) ) {
				++$removed;
			}
		}

		return $removed;
	}

	/**
	 * The bundle directory.
	 *
	 * @return string
	 */
	public function base_dir(): string {
		$uploads = wp_upload_dir();

		return trailingslashit( (string) ( $uploads['basedir'] ?? '' ) ) . self::DIR;
	}

	/**
	 * The bundle directory's URL.
	 *
	 * @return string
	 */
	private function base_url(): string {
		$uploads = wp_upload_dir();

		return trailingslashit( (string) ( $uploads['baseurl'] ?? '' ) ) . self::DIR;
	}

	/**
	 * Drops an index file so the directory cannot be browsed.
	 *
	 * @param string $dir Directory.
	 * @return void
	 */
	private function protect( string $dir ): void {
		$index = trailingslashit( $dir ) . 'index.php';

		if ( file_exists( $index ) ) {
			return;
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents -- Guard file in our own directory.
		file_put_contents( $index, "<?php\n// Silence is golden.\n" );
	}

	/**
	 * Strips anything that is not a hash or an extension.
	 *
	 * Belt and braces: these values are generated, never received, but a
	 * filter could one day change that.
	 *
	 * @param string $value Path segment.
	 * @return string
	 */
	private function safe( string $value ): string {
		return preg_replace( '/[^a-z0-9]/i', '', $value ) ?? '';
	}
}
