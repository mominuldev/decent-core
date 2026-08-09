<?php
/**
 * Header and footer builder.
 *
 * @package DecentCore
 */

namespace DecentCore\Builder;

defined( 'ABSPATH' ) || exit;

use DecentCore\Builder\Admin\Conditions_Box;
use DecentCore\Builder\Conditions\Manager;
use DecentCore\Contracts\Module;

/**
 * Boots the builder.
 *
 * The whole module is switchable, and when it is off nothing here registers a
 * hook — the theme falls back to its static header and footer, which is the
 * behaviour a site has before anyone builds anything.
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
		$conditions->register();
		( new Conditions_Box() )->register();
		( new Renderer( $resolver ) )->register();
		( new Elementor_Support() )->register();
	}
}
