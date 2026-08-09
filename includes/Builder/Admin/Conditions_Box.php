<?php
/**
 * Conditions metabox.
 *
 * @package DecentCore
 */

namespace DecentCore\Builder\Admin;

defined( 'ABSPATH' ) || exit;

use DecentCore\Builder\Conditions\Manager;
use DecentCore\Builder\Conditions\Specificity;
use DecentCore\Builder\Post_Type;
use DecentCore\Builder\Template_Type;

/**
 * Where a template applies, and what type it is.
 */
final class Conditions_Box {

	/**
	 * Nonce action.
	 */
	private const NONCE = 'decent_core_conditions';

	/**
	 * Attaches hooks.
	 *
	 * @return void
	 */
	public function register(): void {
		add_action( 'add_meta_boxes', array( $this, 'add_box' ) );
		add_action( 'save_post_' . Post_Type::NAME, array( $this, 'save' ), 10 );
		add_filter( 'manage_' . Post_Type::NAME . '_posts_columns', array( $this, 'columns' ) );
		add_action( 'manage_' . Post_Type::NAME . '_posts_custom_column', array( $this, 'column' ), 10, 2 );
	}

	/**
	 * Adds the metabox.
	 *
	 * @return void
	 */
	public function add_box(): void {
		add_meta_box(
			'decent-core-conditions',
			__( 'Type and display conditions', 'decent-core' ),
			array( $this, 'render' ),
			Post_Type::NAME,
			'side',
			'high'
		);
	}

	/**
	 * Renders the metabox.
	 *
	 * @param \WP_Post $post Template being edited.
	 * @return void
	 */
	public function render( $post ): void {
		wp_nonce_field( self::NONCE, self::NONCE . '_nonce' );

		$type  = Template_Type::of( (int) $post->ID );
		$rules = Manager::rules_for( (int) $post->ID );
		?>
		<p>
			<label for="decent-template-type"><strong><?php esc_html_e( 'Type', 'decent-core' ); ?></strong></label><br>
			<select class="widefat" id="decent-template-type" name="decent_template_type">
				<?php foreach ( Template_Type::all() as $value => $label ) : ?>
					<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $type, $value ); ?>>
						<?php echo esc_html( $label ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</p>

		<p>
			<strong><?php esc_html_e( 'Show on', 'decent-core' ); ?></strong><br>
			<span class="description">
				<?php esc_html_e( 'A template with no rules applies nowhere, so a half-finished header cannot take over the site the moment it is published.', 'decent-core' ); ?>
			</span>
		</p>

		<?php $selected = wp_list_pluck( $rules, 'type' ); ?>

		<ul style="margin:0">
			<?php foreach ( Specificity::types() as $rule_type => $score ) : ?>
				<li>
					<label>
						<input type="checkbox"
							name="decent_conditions[]"
							value="<?php echo esc_attr( $rule_type ); ?>"
							<?php checked( in_array( $rule_type, $selected, true ) ); ?>>
						<?php echo esc_html( self::label_for( $rule_type ) ); ?>
					</label>
				</li>
			<?php endforeach; ?>
		</ul>
		<?php
	}

	/**
	 * Returns a human label for a rule type.
	 *
	 * @param string $type Rule type.
	 * @return string
	 */
	private static function label_for( string $type ): string {
		$labels = array(
			'entire_site'   => __( 'Entire site', 'decent-core' ),
			'front_page'    => __( 'Front page', 'decent-core' ),
			'blog'          => __( 'Blog index', 'decent-core' ),
			'search'        => __( 'Search results', 'decent-core' ),
			'not_found'     => __( '404 page', 'decent-core' ),
			'archive'       => __( 'All archives', 'decent-core' ),
			'post_type'     => __( 'All single posts and pages', 'decent-core' ),
			'taxonomy'      => __( 'All taxonomy archives', 'decent-core' ),
			'term'          => __( 'All term archives', 'decent-core' ),
			'author'        => __( 'Author archives', 'decent-core' ),
			'singular'      => __( 'All singular views', 'decent-core' ),
			'edd_downloads' => __( 'Products and the catalogue', 'decent-core' ),
			'edd_checkout'  => __( 'Checkout', 'decent-core' ),
			'edd_account'   => __( 'Account pages', 'decent-core' ),
		);

		return $labels[ $type ] ?? $type;
	}

	/**
	 * Saves the metabox.
	 *
	 * @param int $post_id Template ID.
	 * @return void
	 */
	public function save( $post_id ): void {
		$post_id = (int) $post_id;

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		$nonce = isset( $_POST[ self::NONCE . '_nonce' ] )
			? sanitize_text_field( wp_unslash( $_POST[ self::NONCE . '_nonce' ] ) )
			: '';

		if ( '' === $nonce || ! wp_verify_nonce( $nonce, self::NONCE ) ) {
			return;
		}

		// A header changes every page, so this is gated on the same capability
		// as switching themes rather than on editing a post.
		if ( ! current_user_can( 'edit_theme_options' ) ) {
			return;
		}

		$type = isset( $_POST['decent_template_type'] )
			? Template_Type::sanitize( sanitize_key( wp_unslash( $_POST['decent_template_type'] ) ) )
			: Template_Type::HEADER;

		update_post_meta( $post_id, Template_Type::META, $type );

		$posted = isset( $_POST['decent_conditions'] )
			? array_map( 'sanitize_key', (array) wp_unslash( $_POST['decent_conditions'] ) )
			: array();

		$rules = array();

		foreach ( $posted as $rule_type ) {
			$rules[] = array(
				'type'    => $rule_type,
				'object'  => 0,
				'exclude' => false,
			);
		}

		Manager::save_rules( $post_id, $rules );
	}

	/**
	 * Adds type and conditions columns to the list table.
	 *
	 * @param array<string, string> $columns Existing columns.
	 * @return array<string, string>
	 */
	public function columns( array $columns ): array {
		$out = array();

		foreach ( $columns as $key => $label ) {
			$out[ $key ] = $label;

			if ( 'title' === $key ) {
				$out['decent_type']       = __( 'Type', 'decent-core' );
				$out['decent_conditions'] = __( 'Shown on', 'decent-core' );
			}
		}

		return $out;
	}

	/**
	 * Renders a custom column.
	 *
	 * @param string $column  Column key.
	 * @param int    $post_id Template ID.
	 * @return void
	 */
	public function column( $column, $post_id ): void {
		$post_id = (int) $post_id;

		if ( 'decent_type' === $column ) {
			echo esc_html( Template_Type::all()[ Template_Type::of( $post_id ) ] ?? '' );
			return;
		}

		if ( 'decent_conditions' !== $column ) {
			return;
		}

		$rules = Manager::rules_for( $post_id );

		if ( empty( $rules ) ) {
			printf( '<em>%s</em>', esc_html__( 'Nowhere yet', 'decent-core' ) );
			return;
		}

		$labels = array_map(
			static function ( array $rule ): string {
				return self::label_for( (string) $rule['type'] );
			},
			$rules
		);

		echo esc_html( implode( ', ', $labels ) );
	}
}
