<?php
/**
 * Module contract.
 *
 * @package DecentCore
 */

namespace DecentCore\Contracts;

defined( 'ABSPATH' ) || exit;

/**
 * A switchable feature.
 *
 * A module that is off costs nothing: its class is never loaded, its hooks are
 * never attached and its assets are never registered. That is the point of the
 * settings toggles — not to hide UI, but to remove work.
 */
interface Module {

	/**
	 * The settings key that switches this module on and off.
	 *
	 * @return string
	 */
	public static function key(): string;

	/**
	 * Attaches the module's hooks.
	 *
	 * @return void
	 */
	public function register(): void;
}
