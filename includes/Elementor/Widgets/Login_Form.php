<?php
/**
 * Login Form widget.
 *
 * The design's sign-in card: a heading, optional social provider buttons, a
 * divider, the email and password fields, "keep me signed in", the submit
 * button and a link to registration.
 *
 * **This widget never authenticates anyone.** It renders a form and posts it
 * to a handler that already exists — EDD's `user_login` action where EDD is
 * active, `wp-login.php` where it is not. Both own the nonce, the credential
 * check, the brute-force hooks other plugins bind to, and the redirect. A
 * widget that ran `wp_signon()` itself would be a second front door onto the
 * same accounts, and the first one to fall behind a core change.
 *
 * EDD is preferred where present for one concrete reason: its handler puts a
 * failed sign-in back on this page through `edd_print_errors()`, while
 * `wp-login.php` answers on its own screen. On a page built to look like this
 * one, that difference is the whole experience of getting a password wrong.
 *
 * Social providers are links and nothing more. The plugin implements no OAuth,
 * so a provider row without a URL renders nothing rather than a button that
 * goes nowhere — the same rule Newsletter follows about a form with no
 * endpoint.
 *
 * @package PixelomaticCore
 */

namespace PixelomaticCore\Elementor\Widgets;

defined( 'ABSPATH' ) || exit;

use PixelomaticCore\Elementor\Base\Traits\Has_Style_Controls;
use PixelomaticCore\Elementor\Base\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Repeater;

/**
 * A sign-in form.
 */
final class Login_Form extends Widget_Base {

	use Has_Style_Controls;

	/**
	 * Widget slug.
	 *
	 * @return string
	 */
	public static function slug(): string {
		return 'login-form';
	}

	/**
	 * Registers the panel controls.
	 *
	 * @return void
	 */
	protected function register_controls(): void {
		$this->register_content_controls();
		$this->register_provider_controls();
		$this->register_signed_in_controls();
		$this->register_style_controls();
	}

	/* --------------------------------------------------------------- panel */

	/**
	 * The Content tab.
	 *
	 * @return void
	 */
	private function register_content_controls(): void {
		$this->start_controls_section( 'content', array( 'label' => __( 'Content', 'pixelomatic-core' ) ) );

		$this->add_control(
			'title',
			array(
				'label'       => __( 'Title', 'pixelomatic-core' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Sign in', 'pixelomatic-core' ),
				'label_block' => true,
			)
		);

		// A sign-in page's form title is usually the page's own heading, so
		// h1 is offered and h2 is the default — the widget cannot know whether
		// something above it already claimed the level.
		$this->add_control(
			'title_tag',
			array(
				'label'     => __( 'Title tag', 'pixelomatic-core' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'h2',
				'options'   => array(
					'h1'   => 'H1',
					'h2'   => 'H2',
					'h3'   => 'H3',
					'h4'   => 'H4',
					'p'    => __( 'Paragraph', 'pixelomatic-core' ),
					'span' => __( 'Span', 'pixelomatic-core' ),
				),
				'condition' => array( 'title!' => '' ),
			)
		);

		$this->add_control(
			'intro',
			array(
				'label'       => __( 'Intro', 'pixelomatic-core' ),
				'type'        => Controls_Manager::TEXTAREA,
				'rows'        => 3,
				'default'     => __( 'Your downloads, licence keys and support tickets are all in here.', 'pixelomatic-core' ),
				'label_block' => true,
			)
		);

		$this->add_control(
			'username_label',
			array(
				'label'       => __( 'Username field label', 'pixelomatic-core' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Email address', 'pixelomatic-core' ),
				'label_block' => true,
				'separator'   => 'before',
			)
		);

		$this->add_control(
			'username_placeholder',
			array(
				'label'       => __( 'Username field placeholder', 'pixelomatic-core' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
			)
		);

		$this->add_control(
			'password_label',
			array(
				'label'       => __( 'Password field label', 'pixelomatic-core' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Password', 'pixelomatic-core' ),
				'label_block' => true,
			)
		);

		$this->add_control(
			'reveal',
			array(
				'label'        => __( 'Show password toggle', 'pixelomatic-core' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'description'  => __( 'Revealed by script. Without JavaScript the field is an ordinary password field rather than a button that does nothing.', 'pixelomatic-core' ),
				'label_on'     => __( 'Show', 'pixelomatic-core' ),
				'label_off'    => __( 'Hide', 'pixelomatic-core' ),
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'reveal_label',
			array(
				'label'     => __( 'Reveal label', 'pixelomatic-core' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => __( 'Show', 'pixelomatic-core' ),
				'condition' => array( 'reveal' => 'yes' ),
			)
		);

		$this->add_control(
			'conceal_label',
			array(
				'label'     => __( 'Conceal label', 'pixelomatic-core' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => __( 'Hide', 'pixelomatic-core' ),
				'condition' => array( 'reveal' => 'yes' ),
			)
		);

		$this->add_control(
			'lost_label',
			array(
				'label'       => __( 'Lost password link', 'pixelomatic-core' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Forgot password?', 'pixelomatic-core' ),
				'description' => __( 'Points at the store’s own password-reset screen. Leave empty to drop it.', 'pixelomatic-core' ),
				'label_block' => true,
				'separator'   => 'before',
			)
		);

		$this->add_control(
			'lost_url',
			array(
				'label'       => __( 'Lost password URL', 'pixelomatic-core' ),
				'type'        => Controls_Manager::URL,
				'description' => __( 'Optional. Empty uses the store’s reset screen.', 'pixelomatic-core' ),
				'condition'   => array( 'lost_label!' => '' ),
			)
		);

		$this->add_control(
			'remember',
			array(
				'label'        => __( 'Keep signed in', 'pixelomatic-core' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'separator'    => 'before',
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'remember_label',
			array(
				'label'       => __( 'Keep signed in label', 'pixelomatic-core' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Keep me signed in on this device', 'pixelomatic-core' ),
				'label_block' => true,
				'condition'   => array( 'remember' => 'yes' ),
			)
		);

		// Unticked by default. A shared machine is the common case on a
		// storefront, and a box that arrives ticked asks the visitor to notice
		// and undo it.
		$this->add_control(
			'remember_checked',
			array(
				'label'        => __( 'Ticked by default', 'pixelomatic-core' ),
				'type'         => Controls_Manager::SWITCHER,
				'condition'    => array( 'remember' => 'yes' ),
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'button_label',
			array(
				'label'     => __( 'Button label', 'pixelomatic-core' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => __( 'Sign in', 'pixelomatic-core' ),
				'separator' => 'before',
			)
		);

		$this->add_control(
			'redirect',
			array(
				'label'       => __( 'After signing in', 'pixelomatic-core' ),
				'type'        => Controls_Manager::URL,
				'description' => __( 'Where a successful sign-in lands. Empty returns to this page.', 'pixelomatic-core' ),
				'options'     => array( 'url' ),
			)
		);

		$this->add_control(
			'alt_text',
			array(
				'label'       => __( 'Footer text', 'pixelomatic-core' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'New here?', 'pixelomatic-core' ),
				'label_block' => true,
				'separator'   => 'before',
			)
		);

		$this->add_control(
			'alt_label',
			array(
				'label'       => __( 'Footer link', 'pixelomatic-core' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Create an account', 'pixelomatic-core' ),
				'label_block' => true,
			)
		);

		$this->add_control(
			'alt_url',
			array(
				'label'       => __( 'Footer link URL', 'pixelomatic-core' ),
				'type'        => Controls_Manager::URL,
				'description' => __( 'Optional. Empty uses the site’s registration screen, and drops the link where registration is closed.', 'pixelomatic-core' ),
				'condition'   => array( 'alt_label!' => '' ),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * The Providers section.
	 *
	 * @return void
	 */
	private function register_provider_controls(): void {
		$this->start_controls_section( 'providers', array( 'label' => __( 'Social providers', 'pixelomatic-core' ) ) );

		$this->add_control(
			'providers_note',
			array(
				'type'            => Controls_Manager::RAW_HTML,
				'raw'             => esc_html__( 'These are links. The plugin implements no OAuth of its own, so each row needs the sign-in URL your provider plugin hands out — a row without one renders nothing rather than a button that goes nowhere.', 'pixelomatic-core' ),
				'content_classes' => 'elementor-descriptor',
			)
		);

		$repeater = new Repeater();

		$repeater->add_control(
			'label',
			array(
				'label'       => __( 'Label', 'pixelomatic-core' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Continue with GitHub', 'pixelomatic-core' ),
				'label_block' => true,
			)
		);

		$repeater->add_control(
			'url',
			array(
				'label'   => __( 'Sign-in URL', 'pixelomatic-core' ),
				'type'    => Controls_Manager::URL,
				'options' => array( 'url', 'nofollow' ),
			)
		);

		$repeater->add_control(
			'icon',
			array(
				'label' => __( 'Icon', 'pixelomatic-core' ),
				'type'  => Controls_Manager::ICONS,
			)
		);

		$repeater->add_control(
			'variant',
			array(
				'label'   => __( 'Style', 'pixelomatic-core' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'dark',
				'options' => array(
					'dark'      => __( 'Dark', 'pixelomatic-core' ),
					'secondary' => __( 'Outline', 'pixelomatic-core' ),
					'primary'   => __( 'Primary', 'pixelomatic-core' ),
				),
			)
		);

		$this->add_control(
			'provider_items',
			array(
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'title_field' => '{{{ label }}}',
				'default'     => array(
					array(
						'label'   => __( 'Continue with GitHub', 'pixelomatic-core' ),
						'variant' => 'dark',
					),
					array(
						'label'   => __( 'Continue with Google', 'pixelomatic-core' ),
						'variant' => 'secondary',
					),
				),
			)
		);

		$this->add_control(
			'divider_label',
			array(
				'label'       => __( 'Divider label', 'pixelomatic-core' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'or use your email', 'pixelomatic-core' ),
				'description' => __( 'Sits between the providers and the fields. Nothing renders when no provider has a URL.', 'pixelomatic-core' ),
				'label_block' => true,
			)
		);

		$this->end_controls_section();
	}

	/**
	 * The Signed in section.
	 *
	 * @return void
	 */
	private function register_signed_in_controls(): void {
		$this->start_controls_section( 'signed_in', array( 'label' => __( 'Already signed in', 'pixelomatic-core' ) ) );

		$this->add_control(
			'signed_in_note',
			array(
				'type'            => Controls_Manager::RAW_HTML,
				'raw'             => esc_html__( 'What a signed-in visitor sees in place of the form. The editor always shows the form, so this panel is the only way to check this state.', 'pixelomatic-core' ),
				'content_classes' => 'elementor-descriptor',
			)
		);

		$this->add_control(
			'signed_in_text',
			array(
				'label'       => __( 'Text', 'pixelomatic-core' ),
				'type'        => Controls_Manager::TEXT,
				/* translators: %s: the signed-in visitor's display name. */
				'default'     => __( 'You are signed in as %s.', 'pixelomatic-core' ),
				/* translators: %s is the literal token an editor types into the setting above, not a value substituted here. */
				'description' => __( 'Put %s where the visitor’s name should appear.', 'pixelomatic-core' ),
				'label_block' => true,
			)
		);

		$this->add_control(
			'account_label',
			array(
				'label'       => __( 'Account button', 'pixelomatic-core' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Go to your account', 'pixelomatic-core' ),
				'label_block' => true,
			)
		);

		$this->add_control(
			'account_url',
			array(
				'label'     => __( 'Account URL', 'pixelomatic-core' ),
				'type'      => Controls_Manager::URL,
				'condition' => array( 'account_label!' => '' ),
			)
		);

		$this->add_control(
			'logout_label',
			array(
				'label'       => __( 'Sign out link', 'pixelomatic-core' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Sign out', 'pixelomatic-core' ),
				'label_block' => true,
			)
		);

		$this->end_controls_section();
	}

	/**
	 * The Style tab.
	 *
	 * @return void
	 */
	private function register_style_controls(): void {
		$this->start_style_section( 'style_card', __( 'Card', 'pixelomatic-core' ) );

		// The design centres a 420px form in its column. Left empty the form
		// fills whatever container it is dropped in, which is what a form in a
		// narrow sidebar wants.
		$this->add_responsive_control(
			'card_width',
			array(
				'label'      => __( 'Width', 'pixelomatic-core' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%' ),
				'range'      => array(
					'px' => array(
						'min'  => 240,
						'max'  => 720,
						'step' => 4,
					),
					'%'  => array(
						'min'  => 20,
						'max'  => 100,
						'step' => 1,
					),
				),
				'selectors'  => array( '{{WRAPPER}} .pix-login' => 'max-width: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->register_alignment_flex_style( 'card_align', '{{WRAPPER}}', __( 'Card alignment', 'pixelomatic-core' ) );

		$this->register_box_style(
			'card',
			__( 'Card', 'pixelomatic-core' ),
			'{{WRAPPER}} .pix-login',
			array(
				'heading' => false,
				'margin'  => true,
			)
		);

		$this->register_gap_style( 'card_gap', __( 'Block gap', 'pixelomatic-core' ), '{{WRAPPER}} .pix-login', 64 );

		$this->end_controls_section();

		$this->start_style_section( 'style_head', __( 'Head', 'pixelomatic-core' ) );

		$this->register_text_style(
			'title',
			__( 'Title', 'pixelomatic-core' ),
			'{{WRAPPER}} .pix-login__title',
			array(
				'spacing'   => false,
				'separator' => 'none',
			)
		);

		$this->register_text_style(
			'intro',
			__( 'Intro', 'pixelomatic-core' ),
			'{{WRAPPER}} .pix-login__intro',
			array( 'spacing' => false )
		);

		$this->register_gap_style( 'head_gap', __( 'Title and intro gap', 'pixelomatic-core' ), '{{WRAPPER}} .pix-login__head', 40 );

		$this->end_controls_section();

		$this->start_style_section( 'style_providers', __( 'Social providers', 'pixelomatic-core' ) );

		$this->register_button_style(
			'provider',
			__( 'Buttons', 'pixelomatic-core' ),
			'{{WRAPPER}} .pix-login__provider',
			array( 'heading' => false )
		);

		$this->register_gap_style( 'provider_gap', __( 'Gap between buttons', 'pixelomatic-core' ), '{{WRAPPER}} .pix-login__providers', 32 );

		$this->add_responsive_control(
			'provider_icon_size',
			array(
				'label'      => __( 'Icon size', 'pixelomatic-core' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min'  => 10,
						'max'  => 40,
						'step' => 1,
					),
				),
				'selectors'  => array( '{{WRAPPER}} .pix-login__provider-icon' => 'font-size: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->register_text_style(
			'divider',
			__( 'Divider label', 'pixelomatic-core' ),
			'{{WRAPPER}} .pix-login__divider',
			array( 'spacing' => false )
		);

		$this->add_control(
			'divider_line_color',
			array(
				'label'     => __( 'Divider line colour', 'pixelomatic-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pix-login__divider::before, {{WRAPPER}} .pix-login__divider::after' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_section();

		$this->start_style_section( 'style_fields', __( 'Fields', 'pixelomatic-core' ) );

		$this->register_text_style(
			'label',
			__( 'Labels', 'pixelomatic-core' ),
			'{{WRAPPER}} .pix-login__label',
			array(
				'spacing'   => false,
				'separator' => 'none',
			)
		);

		$this->register_box_style(
			'field',
			__( 'Input', 'pixelomatic-core' ),
			'{{WRAPPER}} .pix-login .pix-login__input',
			array( 'shadow' => false )
		);

		$this->register_text_style(
			'field_text',
			__( 'Input text', 'pixelomatic-core' ),
			'{{WRAPPER}} .pix-login .pix-login__input',
			array(
				'heading' => false,
				'spacing' => false,
			)
		);

		$this->add_control(
			'field_placeholder_color',
			array(
				'label'     => __( 'Placeholder colour', 'pixelomatic-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pix-login .pix-login__input::placeholder' => 'color: {{VALUE}};',
				),
			)
		);

		$this->register_gap_style( 'fields_gap', __( 'Gap between fields', 'pixelomatic-core' ), '{{WRAPPER}} .pix-login__fields', 48 );

		$this->register_text_style(
			'remember',
			__( 'Keep signed in', 'pixelomatic-core' ),
			'{{WRAPPER}} .pix-login__remember',
			array( 'spacing' => false )
		);

		$this->add_control(
			'remember_accent',
			array(
				'label'     => __( 'Tick colour', 'pixelomatic-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .pix-login__checkbox' => 'accent-color: {{VALUE}};' ),
			)
		);

		$this->end_controls_section();

		$this->start_style_section( 'style_actions', __( 'Actions', 'pixelomatic-core' ) );

		$this->register_button_style(
			'submit',
			__( 'Submit button', 'pixelomatic-core' ),
			'{{WRAPPER}} .pix-login__submit',
			array( 'heading' => false )
		);

		// One link style for every small link the card renders: lost password,
		// the reveal toggle, the footer link and the sign-out link all sit at
		// the same size and colour in the design, and splitting them into four
		// identical control groups would be four chances to drift.
		$this->register_link_style(
			'link',
			__( 'Links', 'pixelomatic-core' ),
			'{{WRAPPER}} .pix-login__link'
		);

		$this->register_text_style(
			'alt',
			__( 'Footer text', 'pixelomatic-core' ),
			'{{WRAPPER}} .pix-login__alt',
			array( 'spacing' => false )
		);

		$this->end_controls_section();
	}

	/* -------------------------------------------------------------- render */

	/**
	 * Renders the widget.
	 *
	 * @return void
	 */
	protected function render(): void {
		if ( is_user_logged_in() && ! self::is_editing() ) {
			$this->render_signed_in();

			return;
		}
		?>
		<div class="pix-login">
			<?php
			$this->render_head();
			$this->render_errors();
			$this->render_providers();
			$this->render_form();
			$this->render_alt();
			?>
		</div>
		<?php
	}

	/**
	 * The title and the intro.
	 *
	 * @return void
	 */
	private function render_head(): void {
		$title = $this->text( 'title' );
		$intro = $this->text( 'intro' );

		if ( '' === $title && '' === $intro ) {
			return;
		}
		?>
		<div class="pix-login__head">
			<?php
			$this->render_heading( $title, (string) $this->get_settings_for_display( 'title_tag' ), 'pix-login__title' );

			if ( '' !== $intro ) :
				?>
				<p class="pix-login__intro"><?php echo esc_html( $intro ); ?></p>
				<?php
			endif;
			?>
		</div>
		<?php
	}

	/**
	 * A failed sign-in, where the handler puts one on this page.
	 *
	 * EDD's handler does; `wp-login.php` answers on its own screen instead, so
	 * there is nothing to print for that path.
	 *
	 * @return void
	 */
	private function render_errors(): void {
		if ( ! $this->uses_edd() || ! function_exists( 'edd_get_errors' ) ) {
			return;
		}

		if ( ! edd_get_errors() ) {
			return;
		}
		?>
		<div class="pix-login__errors">
			<?php edd_print_errors(); ?>
		</div>
		<?php
	}

	/**
	 * The provider buttons and the divider beneath them.
	 *
	 * @return void
	 */
	private function render_providers(): void {
		$items = $this->providers();

		if ( array() === $items ) {
			return;
		}
		?>
		<div class="pix-login__providers">
			<?php foreach ( $items as $item ) : ?>
				<a class="btn btn--lg btn--block pix-login__provider btn--<?php echo esc_attr( $item['variant'] ); ?>"
					href="<?php echo esc_url( $item['url'] ); ?>"
					<?php echo empty( $item['nofollow'] ) ? '' : 'rel="nofollow"'; ?>>
					<?php if ( $this->has_icon_value( $item['icon'] ) ) : ?>
						<span class="pix-login__provider-icon">
							<?php $this->render_icon_value( $item['icon'] ); ?>
						</span>
					<?php endif; ?>
					<?php echo esc_html( $item['label'] ); ?>
				</a>
			<?php endforeach; ?>
		</div>

		<?php
		$divider = $this->text( 'divider_label' );

		if ( '' !== $divider ) :
			?>
			<p class="pix-login__divider"><?php echo esc_html( $divider ); ?></p>
			<?php
		endif;
	}

	/**
	 * The form itself.
	 *
	 * @return void
	 */
	private function render_form(): void {
		$edd      = $this->uses_edd();
		$id       = 'pixelomatic-login-' . $this->get_id();
		$remember = 'yes' === $this->get_settings_for_display( 'remember' );
		$reveal   = 'yes' === $this->get_settings_for_display( 'reveal' );
		?>
		<form class="pix-login__form"
			method="post"
			<?php
			// EDD reads its action off the request that landed on this page, so
			// the form posts back to it. Core's handler is a screen of its own.
			echo $edd ? 'action=""' : 'action="' . esc_url( wp_login_url() ) . '"';
			?>
			>
			<div class="pix-login__fields">
				<div class="pix-login__field">
					<div class="pix-login__label-row">
						<label class="pix-login__label" for="<?php echo esc_attr( $id . '-user' ); ?>">
							<?php echo esc_html( $this->text( 'username_label', __( 'Email address', 'pixelomatic-core' ) ) ); ?>
						</label>
					</div>

					<?php
					// `text`, not `email`. Both handlers accept a username as
					// readily as an address, and type="email" would have the
					// browser reject one before it was ever submitted.
					?>
					<input class="input pix-login__input"
						id="<?php echo esc_attr( $id . '-user' ); ?>"
						name="<?php echo esc_attr( $edd ? 'edd_user_login' : 'log' ); ?>"
						type="text"
						inputmode="email"
						autocomplete="username"
						autocapitalize="none"
						spellcheck="false"
						required
						placeholder="<?php echo esc_attr( $this->text( 'username_placeholder' ) ); ?>">
				</div>

				<div class="pix-login__field">
					<div class="pix-login__label-row">
						<label class="pix-login__label" for="<?php echo esc_attr( $id . '-pass' ); ?>">
							<?php echo esc_html( $this->text( 'password_label', __( 'Password', 'pixelomatic-core' ) ) ); ?>
						</label>
						<?php $this->render_lost_link(); ?>
					</div>

					<div class="pix-login__password">
						<input class="input pix-login__input pix-login__input--password"
							id="<?php echo esc_attr( $id . '-pass' ); ?>"
							name="<?php echo esc_attr( $edd ? 'edd_user_pass' : 'pwd' ); ?>"
							type="password"
							autocomplete="current-password"
							required
							data-password>

						<?php if ( $reveal ) : ?>
							<?php
							// Ships hidden and is revealed by the script that
							// can drive it. A button that cannot toggle
							// anything is worse than no button.
							?>
							<button class="pix-login__link pix-login__reveal"
								type="button"
								aria-controls="<?php echo esc_attr( $id . '-pass' ); ?>"
								aria-pressed="false"
								data-reveal
								data-show="<?php echo esc_attr( $this->text( 'reveal_label', __( 'Show', 'pixelomatic-core' ) ) ); ?>"
								data-hide="<?php echo esc_attr( $this->text( 'conceal_label', __( 'Hide', 'pixelomatic-core' ) ) ); ?>"
								hidden>
								<?php echo esc_html( $this->text( 'reveal_label', __( 'Show', 'pixelomatic-core' ) ) ); ?>
							</button>
						<?php endif; ?>
					</div>
				</div>
			</div>

			<?php if ( $remember ) : ?>
				<label class="pix-login__remember">
					<?php
					// A native checkbox tinted with accent-color. The design
					// draws a filled blue box with a white tick, which is what
					// every engine already paints for a ticked checkbox — a
					// hand-built one would cost the keyboard and assistive
					// behaviour this gets for nothing.
					?>
					<input class="pix-login__checkbox"
						type="checkbox"
						name="rememberme"
						value="forever"
						<?php checked( 'yes', $this->get_settings_for_display( 'remember_checked' ) ); ?>>
					<span><?php echo esc_html( $this->text( 'remember_label', __( 'Keep me signed in on this device', 'pixelomatic-core' ) ) ); ?></span>
				</label>
			<?php endif; ?>

			<button class="btn btn--primary btn--lg btn--block pix-login__submit" type="submit">
				<?php echo esc_html( $this->text( 'button_label', __( 'Sign in', 'pixelomatic-core' ) ) ); ?>
			</button>

			<?php $this->render_hidden_fields( $edd ); ?>
		</form>
		<?php
	}

	/**
	 * The fields the chosen handler needs.
	 *
	 * @param bool $edd Whether the form posts to EDD.
	 * @return void
	 */
	private function render_hidden_fields( bool $edd ): void {
		$redirect = $this->redirect();

		if ( ! $edd ) {
			?>
			<input type="hidden" name="redirect_to" value="<?php echo esc_url( $redirect ); ?>">
			<?php
			return;
		}
		?>
		<input type="hidden" name="edd_redirect" value="<?php echo esc_url( $redirect ); ?>">
		<input type="hidden" name="edd_login_nonce" value="<?php echo esc_attr( wp_create_nonce( 'edd-login-nonce' ) ); ?>">
		<input type="hidden" name="edd_action" value="user_login">
		<?php
	}

	/**
	 * The lost-password link beside the password label.
	 *
	 * @return void
	 */
	private function render_lost_link(): void {
		$label = $this->text( 'lost_label' );

		if ( '' === $label ) {
			return;
		}

		$link = (array) ( $this->get_settings_for_display( 'lost_url' ) ?? array() );
		$url  = (string) ( $link['url'] ?? '' );

		if ( '' === $url ) {
			$url = $this->uses_edd() && function_exists( 'edd_get_lostpassword_url' )
				? (string) edd_get_lostpassword_url()
				: wp_lostpassword_url( $this->redirect() );
		}
		?>
		<a class="pix-login__link" href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $label ); ?></a>
		<?php
	}

	/**
	 * The line under the form.
	 *
	 * @return void
	 */
	private function render_alt(): void {
		$text  = $this->text( 'alt_text' );
		$label = $this->text( 'alt_label' );
		$link  = (array) ( $this->get_settings_for_display( 'alt_url' ) ?? array() );
		$url   = (string) ( $link['url'] ?? '' );

		// No URL and registration closed means there is nowhere to send them,
		// so the link goes rather than pointing at a screen that turns them
		// away.
		if ( '' === $url && get_option( 'users_can_register' ) ) {
			$url = wp_registration_url();
		}

		if ( '' === $url ) {
			$label = '';
		}

		if ( '' === $text && '' === $label ) {
			return;
		}
		?>
		<p class="pix-login__alt">
			<?php echo esc_html( $text ); ?>
			<?php if ( '' !== $label ) : ?>
				<a class="pix-login__link" href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $label ); ?></a>
			<?php endif; ?>
		</p>
		<?php
	}

	/**
	 * What a signed-in visitor sees in place of the form.
	 *
	 * @return void
	 */
	private function render_signed_in(): void {
		$user   = wp_get_current_user();
		$text   = $this->text( 'signed_in_text' );
		$label  = $this->text( 'account_label' );
		$link   = (array) ( $this->get_settings_for_display( 'account_url' ) ?? array() );
		$url    = (string) ( $link['url'] ?? '' );
		$logout = $this->text( 'logout_label' );
		?>
		<div class="pix-login pix-login--signed-in">
			<?php if ( '' !== $text ) : ?>
				<p class="pix-login__intro">
					<?php echo esc_html( sprintf( $text, $user->display_name ) ); ?>
				</p>
			<?php endif; ?>

			<?php if ( '' !== $label && '' !== $url ) : ?>
				<a class="btn btn--primary btn--lg btn--block pix-login__submit" href="<?php echo esc_url( $url ); ?>">
					<?php echo esc_html( $label ); ?>
				</a>
			<?php endif; ?>

			<?php if ( '' !== $logout ) : ?>
				<p class="pix-login__alt">
					<a class="pix-login__link" href="<?php echo esc_url( wp_logout_url( $this->redirect() ) ); ?>">
						<?php echo esc_html( $logout ); ?>
					</a>
				</p>
			<?php endif; ?>
		</div>
		<?php
	}

	/* --------------------------------------------------------------- state */

	/**
	 * The provider rows that have somewhere to go.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	private function providers(): array {
		$rows  = (array) ( $this->get_settings_for_display( 'provider_items' ) ?? array() );
		$items = array();

		foreach ( $rows as $row ) {
			$link = (array) ( $row['url'] ?? array() );
			$url  = (string) ( $link['url'] ?? '' );

			if ( '' === $url ) {
				continue;
			}

			$variant = (string) ( $row['variant'] ?? 'dark' );

			$items[] = array(
				'label'    => (string) ( $row['label'] ?? '' ),
				'url'      => $url,
				'icon'     => $row['icon'] ?? array(),
				'nofollow' => ! empty( $link['nofollow'] ),
				'variant'  => in_array( $variant, array( 'dark', 'secondary', 'primary' ), true ) ? $variant : 'dark',
			);
		}

		return $items;
	}

	/**
	 * Where a successful sign-in lands.
	 *
	 * @return string
	 */
	private function redirect(): string {
		$link = (array) ( $this->get_settings_for_display( 'redirect' ) ?? array() );
		$url  = (string) ( $link['url'] ?? '' );

		if ( '' !== $url ) {
			return $url;
		}

		if ( $this->uses_edd() && function_exists( 'edd_get_current_page_url' ) ) {
			return (string) edd_get_current_page_url();
		}

		return (string) get_permalink();
	}

	/**
	 * Whether EDD's login handler is the one to post to.
	 *
	 * @return bool
	 */
	private function uses_edd(): bool {
		return function_exists( 'edd_process_login_form' ) && function_exists( 'edd_print_errors' );
	}

	/**
	 * Whether this render is the Elementor canvas.
	 *
	 * The signed-out form is what an editor is building, and an editor is by
	 * definition signed in — without this the widget would be uneditable for
	 * everyone who can edit it.
	 *
	 * @return bool
	 */
	private static function is_editing(): bool {
		if ( ! class_exists( '\\Elementor\\Plugin' ) ) {
			return false;
		}

		$editor = \Elementor\Plugin::instance()->editor ?? null;

		return $editor && $editor->is_edit_mode();
	}
}
