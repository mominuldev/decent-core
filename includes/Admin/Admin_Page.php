<?php
/**
 * Settings screen.
 *
 * Every field is generated from config/settings.php. There is no second list
 * of fields to keep in step with the schema, which is why a setting cannot be
 * added without its type, default and sanitiser.
 *
 * The Widgets tab is generated from config/widgets.php the same way.
 *
 * @package DecentCore
 */

namespace DecentCore\Admin;

defined( 'ABSPATH' ) || exit;

use DecentCore\Elementor\Widget_Registry;
use DecentCore\Settings\Schema;
use DecentCore\Settings\Settings;

/**
 * Renders and saves the settings screen.
 */
final class Admin_Page {

	/**
	 * Menu slug.
	 */
	public const SLUG = 'decent-core';

	/**
	 * Nonce action.
	 */
	private const NONCE = 'decent_core_settings';

	/**
	 * Attaches hooks.
	 *
	 * @return void
	 */
	public function register(): void {
		add_action( 'admin_menu', array( $this, 'add_menu' ) );
		add_action( 'admin_post_decent_core_save', array( $this, 'handle_save' ) );
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
	 * Returns the tab being viewed.
	 *
	 * @return string
	 */
	private function current_tab(): string {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only tab selection.
		$tab  = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'general';
		$tabs = Schema::tabs();

		return isset( $tabs[ $tab ] ) ? $tab : 'general';
	}

	/**
	 * Renders the screen.
	 *
	 * @return void
	 */
	public function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$tabs = Schema::tabs();
		$tab  = $this->current_tab();
		?>
		<div class="wrap">
			<h1><?php echo esc_html__( 'Decent Core', 'decent-core' ); ?></h1>

			<?php
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Display-only flag set by our own redirect.
			if ( isset( $_GET['updated'] ) ) :
				?>
				<div class="notice notice-success is-dismissible">
					<p><?php esc_html_e( 'Settings saved.', 'decent-core' ); ?></p>
				</div>
			<?php endif; ?>

			<nav class="nav-tab-wrapper">
				<?php foreach ( $tabs as $slug => $label ) : ?>
					<a class="nav-tab <?php echo $slug === $tab ? 'nav-tab-active' : ''; ?>"
						href="<?php echo esc_url( admin_url( 'admin.php?page=' . self::SLUG . '&tab=' . $slug ) ); ?>">
						<?php echo esc_html( $label ); ?>
					</a>
				<?php endforeach; ?>
			</nav>

			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="decent_core_save">
				<input type="hidden" name="tab" value="<?php echo esc_attr( $tab ); ?>">
				<?php wp_nonce_field( self::NONCE ); ?>

				<?php
				if ( 'widgets' === $tab ) {
					$this->render_widgets_tab();
				} elseif ( 'tools' === $tab ) {
					$this->render_tools_tab();
				} else {
					$this->render_fields( $tab );
				}
				?>

				<?php if ( 'tools' !== $tab ) : ?>
					<?php submit_button(); ?>
				<?php endif; ?>
			</form>
		</div>
		<?php
	}

	/**
	 * Renders the fields belonging to a tab.
	 *
	 * @param string $tab Tab key.
	 * @return void
	 */
	private function render_fields( string $tab ): void {
		$fields = Schema::fields_for( $tab );

		if ( empty( $fields ) ) {
			printf( '<p>%s</p>', esc_html__( 'Nothing to configure here yet.', 'decent-core' ) );
			return;
		}

		echo '<table class="form-table" role="presentation"><tbody>';

		foreach ( $fields as $key => $field ) {
			$value = Settings::get( $key );
			$id    = 'decent-' . $key;
			?>
			<tr>
				<th scope="row">
					<label for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $field['label'] ); ?></label>
				</th>
				<td>
					<?php if ( 'boolean' === $field['type'] ) : ?>
						<label>
							<input type="checkbox"
								id="<?php echo esc_attr( $id ); ?>"
								name="settings[<?php echo esc_attr( $key ); ?>]"
								value="1"
								<?php checked( (bool) $value ); ?>>
							<?php echo esc_html( $field['label'] ); ?>
						</label>
					<?php elseif ( 'integer' === $field['type'] ) : ?>
						<input type="number"
							id="<?php echo esc_attr( $id ); ?>"
							name="settings[<?php echo esc_attr( $key ); ?>]"
							value="<?php echo esc_attr( (string) $value ); ?>"
							min="<?php echo esc_attr( (string) ( $field['min'] ?? 0 ) ); ?>"
							max="<?php echo esc_attr( (string) ( $field['max'] ?? 9999 ) ); ?>"
							class="small-text">
					<?php elseif ( isset( $field['allowed'] ) ) : ?>
						<select id="<?php echo esc_attr( $id ); ?>" name="settings[<?php echo esc_attr( $key ); ?>]">
							<?php foreach ( (array) $field['allowed'] as $option ) : ?>
								<option value="<?php echo esc_attr( $option ); ?>" <?php selected( $value, $option ); ?>>
									<?php echo esc_html( $option ); ?>
								</option>
							<?php endforeach; ?>
						</select>
					<?php else : ?>
						<input type="text"
							id="<?php echo esc_attr( $id ); ?>"
							name="settings[<?php echo esc_attr( $key ); ?>]"
							value="<?php echo esc_attr( (string) $value ); ?>"
							class="regular-text">
					<?php endif; ?>

					<?php if ( ! empty( $field['help'] ) ) : ?>
						<p class="description"><?php echo esc_html( $field['help'] ); ?></p>
					<?php endif; ?>
				</td>
			</tr>
			<?php
		}

		echo '</tbody></table>';
	}

	/**
	 * Renders the per-widget toggles, generated from the widget map.
	 *
	 * @return void
	 */
	private function render_widgets_tab(): void {
		$map = Widget_Registry::map();

		printf(
			'<p>%s</p>',
			esc_html__( 'Switching a widget off removes it from the Elementor panel and stops registering its assets. Pages already using it will render nothing in its place.', 'decent-core' )
		);

		echo '<table class="form-table" role="presentation"><tbody>';

		foreach ( $map as $slug => $widget ) {
			$key = Widget_Registry::toggle_key( $slug );
			?>
			<tr>
				<th scope="row"><?php echo esc_html( $widget['title'] ); ?></th>
				<td>
					<label>
						<input type="checkbox"
							name="widgets[<?php echo esc_attr( $key ); ?>]"
							value="1"
							<?php checked( Widget_Registry::is_enabled( $slug, $widget ) ); ?>>
						<?php esc_html_e( 'Enabled', 'decent-core' ); ?>
					</label>

					<?php if ( ! empty( $widget['requires'] ) ) : ?>
						<p class="description">
							<?php
							printf(
								/* translators: %s: comma-separated dependency list. */
								esc_html__( 'Requires: %s', 'decent-core' ),
								esc_html( implode( ', ', (array) $widget['requires'] ) )
							);
							?>
						</p>
					<?php endif; ?>
				</td>
			</tr>
			<?php
		}

		echo '</tbody></table>';
	}

	/**
	 * Renders the tools tab.
	 *
	 * @return void
	 */
	private function render_tools_tab(): void {
		$kit_id = (int) get_option( 'elementor_active_kit' );
		?>
		<h2><?php esc_html_e( 'System', 'decent-core' ); ?></h2>
		<table class="widefat striped" style="max-width:720px">
			<tbody>
				<tr><td><?php esc_html_e( 'Plugin version', 'decent-core' ); ?></td><td><code><?php echo esc_html( DECENT_CORE_VERSION ); ?></code></td></tr>
				<tr><td><?php esc_html_e( 'PHP', 'decent-core' ); ?></td><td><code><?php echo esc_html( PHP_VERSION ); ?></code></td></tr>
				<tr><td><?php esc_html_e( 'WordPress', 'decent-core' ); ?></td><td><code><?php echo esc_html( get_bloginfo( 'version' ) ); ?></code></td></tr>
				<tr><td><?php esc_html_e( 'Elementor', 'decent-core' ); ?></td><td><code><?php echo esc_html( defined( 'ELEMENTOR_VERSION' ) ? ELEMENTOR_VERSION : __( 'not active', 'decent-core' ) ); ?></code></td></tr>
				<tr><td><?php esc_html_e( 'Easy Digital Downloads', 'decent-core' ); ?></td><td><code><?php echo esc_html( defined( 'EDD_VERSION' ) ? EDD_VERSION : __( 'not active', 'decent-core' ) ); ?></code></td></tr>
				<tr><td><?php esc_html_e( 'Theme', 'decent-core' ); ?></td><td><code><?php echo esc_html( (string) wp_get_theme()->get( 'Name' ) ); ?></code></td></tr>
				<tr><td><?php esc_html_e( 'Persistent object cache', 'decent-core' ); ?></td><td><code><?php echo esc_html( wp_using_ext_object_cache() ? __( 'yes', 'decent-core' ) : __( 'no', 'decent-core' ) ); ?></code></td></tr>
				<tr><td><?php esc_html_e( 'Uploads writable', 'decent-core' ); ?></td><td><code><?php echo esc_html( wp_is_writable( (string) ( wp_upload_dir()['basedir'] ?? '' ) ) ? __( 'yes', 'decent-core' ) : __( 'no', 'decent-core' ) ); ?></code></td></tr>
				<tr><td><?php esc_html_e( 'Elementor active kit', 'decent-core' ); ?></td><td><code><?php echo esc_html( $kit_id ? (string) $kit_id : __( 'none', 'decent-core' ) ); ?></code></td></tr>
				<tr><td><?php esc_html_e( 'Registered widgets', 'decent-core' ); ?></td><td><code><?php echo esc_html( (string) count( Widget_Registry::active() ) . ' / ' . count( Widget_Registry::map() ) ); ?></code></td></tr>
			</tbody>
		</table>
		<?php
	}

	/**
	 * Handles the form submit.
	 *
	 * @return void
	 */
	public function handle_save(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to change these settings.', 'decent-core' ), '', array( 'response' => 403 ) );
		}

		check_admin_referer( self::NONCE );

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Each value is sanitised by Schema::sanitize() against its declared type.
		$posted = isset( $_POST['settings'] ) ? (array) wp_unslash( $_POST['settings'] ) : array();
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Cast to bool below.
		$widget_toggles = isset( $_POST['widgets'] ) ? (array) wp_unslash( $_POST['widgets'] ) : array();

		$tab = isset( $_POST['tab'] ) ? sanitize_key( wp_unslash( $_POST['tab'] ) ) : 'general';

		// An unchecked checkbox posts nothing at all, so every boolean on the
		// tab being saved is seeded false first. Without this, switching one
		// off would look like "no change" and silently do nothing.
		if ( 'widgets' !== $tab ) {
			foreach ( Schema::fields_for( $tab ) as $key => $field ) {
				if ( 'boolean' === $field['type'] && ! isset( $posted[ $key ] ) ) {
					$posted[ $key ] = false;
				}
			}

			Settings::save( $posted );
		} else {
			$values = array();

			foreach ( Widget_Registry::map() as $slug => $widget ) {
				$key            = Widget_Registry::toggle_key( $slug );
				$values[ $key ] = isset( $widget_toggles[ $key ] );
			}

			// Widget toggles are not in the schema, so they are written
			// directly — as plain booleans, keyed by a slug we generated.
			$current = get_option( Settings::OPTION, array() );
			$current = is_array( $current ) ? $current : array();

			update_option( Settings::OPTION, array_merge( $current, $values ), true );
			Settings::flush();
		}

		wp_safe_redirect(
			add_query_arg(
				array(
					'page'    => self::SLUG,
					'tab'     => $tab,
					'updated' => '1',
				),
				admin_url( 'admin.php' )
			)
		);
		exit;
	}
}
