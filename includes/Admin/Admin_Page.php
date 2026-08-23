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
 * @package PixelomaticCore
 */

namespace PixelomaticCore\Admin;

defined( 'ABSPATH' ) || exit;

use PixelomaticCore\Elementor\Manager;
use PixelomaticCore\Elementor\Widget_Registry;
use PixelomaticCore\Settings\Schema;
use PixelomaticCore\Settings\Settings;

/**
 * Registers and boots the settings application.
 */
final class Admin_Page {

	/**
	 * Menu slug.
	 */
	public const SLUG = 'pixelomatic-core';

	/**
	 * Whether the build output is missing on this request.
	 *
	 * Tracked rather than read twice because clearing the screen's notices
	 * removes our own warning along with everybody else's, and this is what
	 * says whether it has to go back.
	 *
	 * @var bool
	 */
	private $build_missing = false;

	/**
	 * Attaches hooks.
	 *
	 * @return void
	 */
	public function register(): void {
		add_action( 'admin_menu', array( $this, 'add_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );

		// After admin_enqueue_scripts, which is where our own notice is
		// registered, and before the notice hooks themselves fire.
		add_action( 'in_admin_header', array( $this, 'clear_notices' ), PHP_INT_MAX );
	}

	/**
	 * Adds the top-level menu.
	 *
	 * @return void
	 */
	public function add_menu(): void {
		add_menu_page(
			__( 'Pixelomatic Core', 'pixelomatic-core' ),
			__( 'Pixelomatic', 'pixelomatic-core' ),
			'manage_options',
			self::SLUG,
			array( $this, 'render' ),
			'dashicons-screenoptions',
			58
		);

		// add_menu_page() does not give the top-level page a submenu entry of
		// its own. The builder's template post type is registered with
		// show_in_menu => SLUG, so core adds "Templates" beneath us, and once
		// a submenu exists WordPress points the parent link at its first item
		// — leaving this screen with nothing linking to it. Claiming position
		// zero keeps the settings screen first and the parent link correct,
		// whichever order the two submenus are added in.
		add_submenu_page(
			self::SLUG,
			__( 'Pixelomatic Core', 'pixelomatic-core' ),
			__( 'Settings', 'pixelomatic-core' ),
			'manage_options',
			self::SLUG,
			array( $this, 'render' ),
			0
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
		if ( ! file_exists( PIXELOMATIC_CORE_DIR . $script ) ) {
			$this->build_missing = true;
			add_action( 'admin_notices', array( $this, 'missing_build_notice' ) );
			return;
		}

		wp_enqueue_style(
			'pixelomatic-core-admin',
			PIXELOMATIC_CORE_URL . $style,
			array(),
			(string) filemtime( PIXELOMATIC_CORE_DIR . $style )
		);

		wp_enqueue_script(
			'pixelomatic-core-admin',
			PIXELOMATIC_CORE_URL . $script,
			array(),
			(string) filemtime( PIXELOMATIC_CORE_DIR . $script ),
			true
		);

		wp_add_inline_script(
			'pixelomatic-core-admin',
			'window.pixelomaticCore = ' . wp_json_encode( $this->boot_data() ) . ';',
			'before'
		);
	}

	/**
	 * Clears every admin notice on this screen.
	 *
	 * Notices are not printed where they are hooked. WordPress relocates any
	 * `.notice` inside `.wrap` to sit directly after the first heading, and
	 * this screen's `h1` is inside the header card — so a licence reminder
	 * from an unrelated plugin lands between the title and its description,
	 * on a gradient it was never styled against and cannot be read on.
	 *
	 * There is nowhere better to put them either: the app owns the whole
	 * column, and every band of it is doing a job. Other plugins with a
	 * single-purpose screen of their own do the same thing.
	 *
	 * Scoped to this one screen. Anything genuinely urgent — a core update, a
	 * PHP warning — is still shown on every other page in wp-admin, which is
	 * where somebody reading it can act on it.
	 *
	 * @return void
	 */
	public function clear_notices(): void {
		if ( ! $this->is_settings_screen() ) {
			return;
		}

		remove_all_actions( 'admin_notices' );
		remove_all_actions( 'all_admin_notices' );
		remove_all_actions( 'user_admin_notices' );
		remove_all_actions( 'network_admin_notices' );

		// The one exception. Without it a missing build is a blank screen with
		// no explanation, which is the state this notice exists to describe.
		if ( $this->build_missing ) {
			add_action( 'admin_notices', array( $this, 'missing_build_notice' ) );
		}
	}

	/**
	 * Whether the current request is this plugin's settings screen.
	 *
	 * @return bool
	 */
	private function is_settings_screen(): bool {
		if ( ! function_exists( 'get_current_screen' ) ) {
			return false;
		}

		$screen = get_current_screen();

		return $screen instanceof \WP_Screen && 'toplevel_page_' . self::SLUG === $screen->id;
	}

	/**
	 * Warns that the build output is missing.
	 *
	 * @return void
	 */
	public function missing_build_notice(): void {
		echo '<div class="notice notice-error"><p>';
		esc_html_e( 'Pixelomatic Core: the admin bundle is missing. Run "npm install && npm run build" in the plugin directory.', 'pixelomatic-core' );
		echo '</p></div>';
	}

	/**
	 * Everything the application needs to start.
	 *
	 * @return array<string, mixed>
	 */
	private function boot_data(): array {
		return array(
			'restUrl'    => esc_url_raw( rest_url( 'pixelomatic/v1/settings' ) ),
			'toolsUrl'   => esc_url_raw( rest_url( 'pixelomatic/v1/tools' ) ),
			// Proves the request came from this session. The capability check
			// itself happens server-side in Rest_Controller.
			'nonce'      => wp_create_nonce( 'wp_rest' ),
			// Also present in the system read-out, but under a translated key.
			// The header badge needs it by a stable name.
			'version'    => PIXELOMATIC_CORE_VERSION,
			'tabs'       => Schema::tabs(),
			'schema'     => $this->schema_for_js(),
			'settings'   => $this->settings_for_js(),
			'widgets'    => $this->widgets_for_js(),
			// The four editor panel categories, which the widget list groups
			// by. Sent as a map so the app keeps their order without knowing
			// any of the slugs itself.
			'categories' => Manager::categories(),
			'system'     => $this->system_info(),
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

				if ( 'theme' === $dependency && ! function_exists( 'pixelomatic_icon' ) ) {
					$missing[] = 'the Pixelomatic theme';
				}
			}

			$out[ $slug ] = array(
				'key'      => Widget_Registry::toggle_key( $slug ),
				'title'    => $widget['title'],
				// The editor panel category, not the finer-grained `group`:
				// sixteen groups across twenty-nine widgets left most of them
				// alone under a heading of their own, which is a list with
				// extra steps.
				'category' => $widget['category'] ?? '',
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
			__( 'Plugin', 'pixelomatic-core' )           => PIXELOMATIC_CORE_VERSION,
			__( 'PHP', 'pixelomatic-core' )              => PHP_VERSION,
			__( 'WordPress', 'pixelomatic-core' )        => (string) get_bloginfo( 'version' ),
			__( 'Elementor', 'pixelomatic-core' )        => defined( 'ELEMENTOR_VERSION' ) ? ELEMENTOR_VERSION : __( 'not active', 'pixelomatic-core' ),
			__( 'EDD', 'pixelomatic-core' )              => defined( 'EDD_VERSION' ) ? EDD_VERSION : __( 'not active', 'pixelomatic-core' ),
			__( 'Theme', 'pixelomatic-core' )            => (string) wp_get_theme()->get( 'Name' ),
			__( 'Object cache', 'pixelomatic-core' )     => wp_using_ext_object_cache() ? __( 'persistent', 'pixelomatic-core' ) : __( 'none', 'pixelomatic-core' ),
			__( 'Uploads writable', 'pixelomatic-core' ) => wp_is_writable( (string) ( $uploads['basedir'] ?? '' ) ) ? __( 'yes', 'pixelomatic-core' ) : __( 'no', 'pixelomatic-core' ),
			__( 'Elementor kit', 'pixelomatic-core' )    => ( get_option( 'elementor_active_kit' ) ? (string) get_option( 'elementor_active_kit' ) : __( 'none', 'pixelomatic-core' ) ),
			__( 'Widgets active', 'pixelomatic-core' )   => count( Widget_Registry::active() ) . ' / ' . count( Widget_Registry::map() ),
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
			<div id="pixelomatic-core-app"></div>
			<noscript>
				<div class="notice notice-warning">
					<p><?php esc_html_e( 'This screen needs JavaScript. The same settings can be changed with WP-CLI or the pixelomatic/v1/settings REST route.', 'pixelomatic-core' ); ?></p>
				</div>
			</noscript>
		</div>
		<?php
	}
}
