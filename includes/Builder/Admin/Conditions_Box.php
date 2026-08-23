<?php
/**
 * Conditions metabox.
 *
 * @package PixelomaticCore
 */

namespace PixelomaticCore\Builder\Admin;

defined( 'ABSPATH' ) || exit;

use PixelomaticCore\Builder\Conditions\Manager;
use PixelomaticCore\Builder\Conditions\Specificity;
use PixelomaticCore\Builder\Post_Type;
use PixelomaticCore\Builder\Template_Type;

/**
 * Where a template applies, and what type it is.
 */
final class Conditions_Box {

	/**
	 * Nonce action.
	 */
	private const NONCE = 'pixelomatic_core_conditions';

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
			'pixelomatic-core-conditions',
			__( 'Type and display conditions', 'pixelomatic-core' ),
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

		$type  = self::current_type( $post );
		$rules = Manager::rules_for( (int) $post->ID );
		?>
		<p>
			<label for="pixelomatic-template-type"><strong><?php esc_html_e( 'Type', 'pixelomatic-core' ); ?></strong></label><br>
			<select class="widefat" id="pixelomatic-template-type" name="pixelomatic_template_type">
				<?php foreach ( Template_Type::all() as $value => $label ) : ?>
					<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $type, $value ); ?>>
						<?php echo esc_html( $label ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</p>

		<p>
			<strong><?php esc_html_e( 'Show on', 'pixelomatic-core' ); ?></strong><br>
			<span class="description">
				<?php esc_html_e( 'A template with no rules applies nowhere, so a half-finished header cannot take over the site the moment it is published.', 'pixelomatic-core' ); ?>
			</span>
		</p>

		<?php $selected = wp_list_pluck( $rules, 'type' ); ?>

		<ul style="margin:0">
			<?php foreach ( Specificity::types() as $rule_type => $score ) : ?>
				<li>
					<label>
						<input type="checkbox"
							name="pixelomatic_conditions[]"
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
	 * The type to preselect.
	 *
	 * A template created from a filtered list — "Add New" while looking at the
	 * footers — starts as a footer. Anywhere else the stored type wins, and a
	 * template with none stored is a header.
	 *
	 * @param \WP_Post $post Template being edited.
	 * @return string
	 */
	private static function current_type( $post ): string {
		if ( 'auto-draft' === $post->post_status ) {
			$selected = Templates_List::selected_type();

			if ( '' !== $selected ) {
				return $selected;
			}
		}

		return Template_Type::of( (int) $post->ID );
	}

	/**
	 * Returns a human label for a rule type.
	 *
	 * @param string $type Rule type.
	 * @return string
	 */
	private static function label_for( string $type ): string {
		$labels = array(
			'entire_site'   => __( 'Entire site', 'pixelomatic-core' ),
			'front_page'    => __( 'Front page', 'pixelomatic-core' ),
			'blog'          => __( 'Blog index', 'pixelomatic-core' ),
			'search'        => __( 'Search results', 'pixelomatic-core' ),
			'not_found'     => __( '404 page', 'pixelomatic-core' ),
			'archive'       => __( 'All archives', 'pixelomatic-core' ),
			'post_type'     => __( 'All single posts and pages', 'pixelomatic-core' ),
			'taxonomy'      => __( 'All taxonomy archives', 'pixelomatic-core' ),
			'term'          => __( 'All term archives', 'pixelomatic-core' ),
			'author'        => __( 'Author archives', 'pixelomatic-core' ),
			'singular'      => __( 'All singular views', 'pixelomatic-core' ),
			'edd_downloads' => __( 'Products and the catalogue', 'pixelomatic-core' ),
			'edd_checkout'  => __( 'Checkout', 'pixelomatic-core' ),
			'edd_account'   => __( 'Account pages', 'pixelomatic-core' ),
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

		$type = isset( $_POST['pixelomatic_template_type'] )
			? Template_Type::sanitize( sanitize_key( wp_unslash( $_POST['pixelomatic_template_type'] ) ) )
			: Template_Type::HEADER;

		update_post_meta( $post_id, Template_Type::META, $type );

		$posted = isset( $_POST['pixelomatic_conditions'] )
			? array_map( 'sanitize_key', (array) wp_unslash( $_POST['pixelomatic_conditions'] ) )
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
				$out['pixelomatic_type']       = __( 'Type', 'pixelomatic-core' );
				$out['pixelomatic_conditions'] = __( 'Shown on', 'pixelomatic-core' );
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

		if ( 'pixelomatic_type' === $column ) {
			echo esc_html( Template_Type::all()[ Template_Type::of( $post_id ) ] ?? '' );
			return;
		}

		if ( 'pixelomatic_conditions' !== $column ) {
			return;
		}

		$rules = Manager::rules_for( $post_id );

		if ( empty( $rules ) ) {
			printf( '<em>%s</em>', esc_html__( 'Nowhere yet', 'pixelomatic-core' ) );
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
