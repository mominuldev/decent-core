<?php
/**
 * Settings screen.
 *
 * The screen itself is a React application; this class does three things and
 * nothing else: register the menu, print a mount point, and hand the app the
 * data it needs.
 *
 * No @wordpress/* packages are involved. Everything WordPress has to tell the
 * app — REST root, nonce, schema, current values — is printed as one JSON
 * object, and the app talks back over the plugin's own REST route, which is
 * capability-checked server-side.
 *
 * The schema is passed through rather than duplicated in JavaScript, so the
 * control a user sees and the rule the server enforces come from the same
 * array in config/settings.php.
 *
 * @package DecentCore
 */

namespace DecentCore\Admin;

defined( 'ABSPATH' ) || exit;

use DecentCore\Elementor\Widget_Registry;
use DecentCore\Settings\Schema;
use DecentCore\Settings\Settings;

/**
 * Registers and boots the settings application.
 */
final class Admin_Page {

	/**
	 * Menu slug.
	 */
	public const SLUG = 'decent-core';

	/**
	 * Attaches hooks.
	 *
	 * @return void
	 */
	public function register(): void {
		add_action( 'admin_menu', array( $this, 'add_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
	}

	/**
	 * Adds the top-level menu.
	 *
	 * @return void
	 */
	public function add_menu(): void {
		add_menu_page(
			__( 'Decent Core', 'decent-core' ),
			__( 'Decent', 'decent-core' ),
			'manage_options',
			self::SLUG,
			array( $this, 'render' ),
			'dashicons-screenoptions',
			58
		);
	}

	/**
	 * Loads the application, and only on its own screen.
	 *
	 * The bundle carries React, so letting it load across wp-admin would put
	 * ~200 KB on every page for one screen that uses it.
	 *
	 * @param string $hook Current admin page hook.
	 * @return void
	 */
	public function enqueue( string $hook ): void {
		if ( 'toplevel_page_' . self::SLUG !== $hook ) {
			return;
		}

		$script = 'assets/admin/app.js';
		$style  = 'assets/admin/app.css';

		// Built files, not sources. If they are missing the plugin was
		// installed from a checkout without running the build, and saying so
		// is far better than a blank screen.
		if ( ! file_exists( DECENT_CORE_DIR . $script ) ) {
			add_action( 'admin_notices', array( $this, 'missing_build_notice' ) );
			return;
		}

		wp_enqueue_style(
			'decent-core-admin',
			DECENT_CORE_URL . $style,
			array(),
			(string) filemtime( DECENT_CORE_DIR . $style )
		);

		wp_enqueue_script(
			'decent-core-admin',
			DECENT_CORE_URL . $script,
			array(),
			(string) filemtime( DECENT_CORE_DIR . $script ),
			true
		);

		wp_add_inline_script(
			'decent-core-admin',
			'window.decentCore = ' . wp_json_encode( $this->boot_data() ) . ';',
			'before'
		);
	}

	/**
	 * Warns that the build output is missing.
	 *
	 * @return void
	 */
	public function missing_build_notice(): void {
		echo '<div class="notice notice-error"><p>';
		esc_html_e( 'Decent Core: the admin bundle is missing. Run "npm install && npm run build" in the plugin directory.', 'decent-core' );
		echo '</p></div>';
	}

	/**
	 * Everything the application needs to start.
	 *
	 * @return array<string, mixed>
	 */
	private function boot_data(): array {
		return array(
			'restUrl'  => esc_url_raw( rest_url( 'decent/v1/settings' ) ),
			// Proves the request came from this session. The capability check
			// itself happens server-side in Rest_Controller.
			'nonce'    => wp_create_nonce( 'wp_rest' ),
			'tabs'     => Schema::tabs(),
			'schema'   => $this->schema_for_js(),
			'settings' => $this->settings_for_js(),
			'widgets'  => $this->widgets_for_js(),
			'system'   => $this->system_info(),
		);
	}

	/**
	 * The schema, minus anything the app has no use for.
	 *
	 * @return array<string, array<string, mixed>>
	 */
	private function schema_for_js(): array {
		$out = array();

		foreach ( Schema::fields() as $key => $field ) {
			$out[ $key ] = array(
				'tab'     => $field['tab'],
				'type'    => $field['type'],
				'label'   => $field['label'],
				'help'    => $field['help'] ?? '',
				'min'     => $field['min'] ?? null,
				'max'     => $field['max'] ?? null,
				'allowed' => $field['allowed'] ?? null,
			);
		}

		return $out;
	}

	/**
	 * Current values, including the widget toggles the schema does not cover.
	 *
	 * @return array<string, mixed>
	 */
	private function settings_for_js(): array {
		$values = Settings::all();

		foreach ( Widget_Registry::map() as $slug => $widget ) {
			$values[ Widget_Registry::toggle_key( $slug ) ] = Widget_Registry::is_enabled( $slug, $widget );
		}

		return $values;
	}

	/**
	 * The widget list, with any unmet dependencies resolved for display.
	 *
	 * @return array<string, array<string, mixed>>
	 */
	private function widgets_for_js(): array {
		$out = array();

		foreach ( Widget_Registry::map() as $slug => $widget ) {
			$missing = array();

			foreach ( (array) ( $widget['requires'] ?? array() ) as $dependency ) {
				if ( 'edd' === $dependency && ! defined( 'EDD_VERSION' ) ) {
					$missing[] = 'Easy Digital Downloads';
				}

				if ( 'theme' === $dependency && ! function_exists( 'decent_icon' ) ) {
					$missing[] = 'the Decent Themes theme';
				}
			}

			$out[ $slug ] = array(
				'key'      => Widget_Registry::toggle_key( $slug ),
				'title'    => $widget['title'],
				'group'    => $widget['group'] ?? '',
				'keywords' => array_values( (array) ( $widget['keywords'] ?? array() ) ),
				'missing'  => $missing,
			);
		}

		return $out;
	}

	/**
	 * The read-out support asks for first.
	 *
	 * @return array<string, string>
	 */
	private function system_info(): array {
		$uploads = wp_upload_dir();

		return array(
			__( 'Plugin', 'decent-core' )           => DECENT_CORE_VERSION,
			__( 'PHP', 'decent-core' )              => PHP_VERSION,
			__( 'WordPress', 'decent-core' )        => (string) get_bloginfo( 'version' ),
			__( 'Elementor', 'decent-core' )        => defined( 'ELEMENTOR_VERSION' ) ? ELEMENTOR_VERSION : __( 'not active', 'decent-core' ),
			__( 'EDD', 'decent-core' )              => defined( 'EDD_VERSION' ) ? EDD_VERSION : __( 'not active', 'decent-core' ),
			__( 'Theme', 'decent-core' )            => (string) wp_get_theme()->get( 'Name' ),
			__( 'Object cache', 'decent-core' )     => wp_using_ext_object_cache() ? __( 'persistent', 'decent-core' ) : __( 'none', 'decent-core' ),
			__( 'Uploads writable', 'decent-core' ) => wp_is_writable( (string) ( $uploads['basedir'] ?? '' ) ) ? __( 'yes', 'decent-core' ) : __( 'no', 'decent-core' ),
			__( 'Elementor kit', 'decent-core' )    => (string) ( get_option( 'elementor_active_kit' ) ?: __( 'none', 'decent-core' ) ),
			__( 'Widgets active', 'decent-core' )   => count( Widget_Registry::active() ) . ' / ' . count( Widget_Registry::map() ),
		);
	}

	/**
	 * Prints the mount point.
	 *
	 * @return void
	 */
	public function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// A noscript fallback rather than an empty page: the settings are all
		// reachable over REST or WP-CLI, and saying so beats a blank screen.
		?>
		<div class="wrap">
			<div id="decent-core-app"></div>
			<noscript>
				<div class="notice notice-warning">
					<p><?php esc_html_e( 'This screen needs JavaScript. The same settings can be changed with WP-CLI or the decent/v1/settings REST route.', 'decent-core' ); ?></p>
				</div>
			</noscript>
		</div>
		<?php
	}
}
