<?php
/**
 * Minimal Elementor stubs for static analysis.
 *
 * Elementor publishes no stub package and is not on Packagist, and PHPStan
 * refuses to ignore a class.notFound on an `extends` clause — correctly, since
 * an unknown parent means it cannot check anything in the subclass either.
 *
 * So rather than waive the whole seam, this declares only the surface the
 * plugin actually touches. It is deliberately small: every entry is a method
 * this codebase calls, so if a call site is wrong PHPStan still says so, and
 * if Elementor changes one of these signatures the mismatch shows up here
 * rather than in production.
 *
 * Never loaded at runtime. See phpstan.neon.dist -> scanFiles.
 *
 * @package DecentCore
 */

namespace Elementor;

/**
 * Elementor's widget base class.
 */
abstract class Widget_Base {

	/**
	 * Returns the widget's unique name.
	 *
	 * @return string
	 */
	public function get_name() {}

	/**
	 * Returns the panel title.
	 *
	 * @return string
	 */
	public function get_title() {}

	/**
	 * Returns the panel icon class.
	 *
	 * @return string
	 */
	public function get_icon() {}

	/**
	 * Returns the panel categories.
	 *
	 * @return string[]
	 */
	public function get_categories() {}

	/**
	 * Returns the panel search keywords.
	 *
	 * @return string[]
	 */
	public function get_keywords() {}

	/**
	 * Returns style handles the widget depends on.
	 *
	 * @return string[]
	 */
	public function get_style_depends() {}

	/**
	 * Returns script handles the widget depends on.
	 *
	 * @return string[]
	 */
	public function get_script_depends() {}

	/**
	 * Returns the element's id.
	 *
	 * @return string
	 */
	public function get_id() {}

	/**
	 * Returns a setting, processed for display.
	 *
	 * @param string|null $setting Setting key.
	 * @return mixed
	 */
	public function get_settings_for_display( $setting = null ) {}

	/**
	 * Opens a controls section.
	 *
	 * @param string              $id   Section id.
	 * @param array<string,mixed> $args Section arguments.
	 * @return void
	 */
	protected function start_controls_section( $id, array $args = array() ) {}

	/**
	 * Closes a controls section.
	 *
	 * @return void
	 */
	protected function end_controls_section() {}

	/**
	 * Adds a control.
	 *
	 * @param string              $id   Control id.
	 * @param array<string,mixed> $args Control arguments.
	 * @param array<string,mixed> $options Control options.
	 * @return void
	 */
	protected function add_control( $id, array $args = array(), array $options = array() ) {}

	/**
	 * Adds a responsive control.
	 *
	 * @param string              $id   Control id.
	 * @param array<string,mixed> $args Control arguments.
	 * @param array<string,mixed> $options Control options.
	 * @return void
	 */
	protected function add_responsive_control( $id, array $args = array(), array $options = array() ) {}

	/**
	 * Adds a group control.
	 *
	 * @param string              $type Group control type.
	 * @param array<string,mixed> $args Control arguments.
	 * @param array<string,mixed> $options Control options.
	 * @return void
	 */
	protected function add_group_control( $type, array $args = array(), array $options = array() ) {}
}

/**
 * Control type constants and helpers.
 */
class Controls_Manager {
	const TEXT        = 'text';
	const TEXTAREA    = 'textarea';
	const SELECT      = 'select';
	const CHOOSE      = 'choose';
	const COLOR       = 'color';
	const SWITCHER    = 'switcher';
	const SLIDER      = 'slider';
	const NUMBER      = 'number';
	const REPEATER    = 'repeater';
	const TAB_STYLE   = 'style';
	const TAB_CONTENT = 'content';
}

/**
 * Typography group control.
 */
class Group_Control_Typography {

	/**
	 * Returns the group control type.
	 *
	 * @return string
	 */
	public static function get_type() {}
}

/**
 * Widget registry.
 */
class Widgets_Manager {

	/**
	 * Registers a widget instance.
	 *
	 * @param Widget_Base $widget Widget.
	 * @return bool
	 */
	public function register( $widget ) {}

	/**
	 * Returns registered widget types.
	 *
	 * @param string|null $name Widget name.
	 * @return Widget_Base[]|Widget_Base|null
	 */
	public function get_widget_types( $name = null ) {}
}

/**
 * Element and category registry.
 */
class Elements_Manager {

	/**
	 * Adds a panel category.
	 *
	 * @param string              $name       Category name.
	 * @param array<string,mixed> $properties Category properties.
	 * @return void
	 */
	public function add_category( $name, $properties ) {}

	/**
	 * Returns registered categories.
	 *
	 * @return array<string,mixed>
	 */
	public function get_categories() {}
}

/**
 * Files manager.
 */
class Files_Manager {

	/**
	 * Clears Elementor's generated CSS cache.
	 *
	 * @return void
	 */
	public function clear_cache() {}
}

/**
 * Elementor's plugin container.
 */
class Plugin {

	/**
	 * Files manager.
	 *
	 * @var Files_Manager|null
	 */
	public $files_manager;

	/**
	 * Widgets manager.
	 *
	 * @var Widgets_Manager|null
	 */
	public $widgets_manager;

	/**
	 * Elements manager.
	 *
	 * @var Elements_Manager|null
	 */
	public $elements_manager;

	/**
	 * Returns the singleton.
	 *
	 * @return Plugin
	 */
	public static function instance() {}
}
