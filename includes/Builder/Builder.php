<?php
/**
 * Header and footer builder.
 *
 * @package PixelomaticCore
 */

namespace PixelomaticCore\Builder;

defined( 'ABSPATH' ) || exit;

use PixelomaticCore\Assets\Usage_Index;
use PixelomaticCore\Builder\Admin\Conditions_Box;
use PixelomaticCore\Builder\Admin\Templates_List;
use PixelomaticCore\Builder\Conditions\Manager;
use PixelomaticCore\Contracts\Module;

/**
 * Boots the builder.
 *
 * The whole module is switchable, and when it is off nothing here registers a
 * hook — the theme falls back to its static header and footer, which is the
 * behaviour a site has before anyone builds anything.
 *
 * The parts, in the order a request meets them:
 *
 *   Post_Type          the template itself.
 *   Canvas             it is edited and previewed on a bare page.
 *   Display_Settings   sticky, overlay and the rest, stored on the template.
 *   Conditions\Manager where it applies, compiled to one option on save.
 *   Resolver           which template this request gets, from that option.
 *   Assets             what that template needs, enqueued before the head closes.
 *   Renderer           the markup, into the theme's header and footer slots.
 *   Body               a singular view's body, in place of the theme's template.
 */
final class Builder implements Module {

	/**
	 * Settings key.
	 *
	 * @return string
	 */
	public static function key(): string {
		return 'module_builder';
	}

	/**
	 * Attaches hooks.
	 *
	 * @return void
	 */
	public function register(): void {
		$conditions = new Manager();
		$resolver   = new Resolver( $conditions );

		( new Post_Type() )->register();
		( new Canvas() )->register();
		$conditions->register();
		( new Conditions_Box() )->register();
		( new Display_Settings() )->register();
		( new Assets( $resolver, new Usage_Index() ) )->register();
		( new Renderer( $resolver ) )->register();
		( new Body( $resolver ) )->register();
		( new Shortcode() )->register();
		( new Elementor_Support() )->register();

		if ( is_admin() ) {
			( new Templates_List() )->register();
		}
	}
}
