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
abstract class Widget_Base extends Element_Base {

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

	/**
	 * Opens a set of control tabs.
	 *
	 * @param string              $id   Tabs id.
	 * @param array<string,mixed> $args Tabs arguments.
	 * @return void
	 */
	protected function start_controls_tabs( $id, array $args = array() ) {}

	/**
	 * Closes a set of control tabs.
	 *
	 * @return void
	 */
	protected function end_controls_tabs() {}

	/**
	 * Opens one control tab.
	 *
	 * @param string              $id   Tab id.
	 * @param array<string,mixed> $args Tab arguments.
	 * @return void
	 */
	protected function start_controls_tab( $id, array $args = array() ) {}

	/**
	 * Closes one control tab.
	 *
	 * @return void
	 */
	protected function end_controls_tab() {}
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
	const TAB_STYLE    = 'style';
	const TAB_CONTENT  = 'content';
	const TAB_SETTINGS = 'settings';
	const SELECT2     = 'select2';
	const URL         = 'url';
	const MEDIA       = 'media';
	const ICONS       = 'icons';
	const RAW_HTML    = 'raw_html';
	const HEADING     = 'heading';
	const DIVIDER     = 'divider';
	const DIMENSIONS  = 'dimensions';
	const HIDDEN      = 'hidden';
	const ALERT       = 'alert';
	const NOTICE      = 'notice';
}

/**
 * Elementor's icon picker back end.
 */
class Icons_Manager {

	/**
	 * Returns the markup for a picked icon, or an empty string.
	 *
	 * @param array<string, mixed> $icon       Icon, as `value` and `library`.
	 * @param array<string, mixed> $attributes Attributes for the icon element.
	 * @param string               $tag        Tag to use for a font icon.
	 * @return string|false
	 */
	public static function try_get_icon_html( $icon, $attributes = array(), $tag = 'i' ) {}
}

/**
 * Repeater control builder.
 */
class Repeater {

	/**
	 * Adds a field to the repeater row.
	 *
	 * @param string              $id      Control id.
	 * @param array<string,mixed> $args    Control arguments.
	 * @param array<string,mixed> $options Control options.
	 * @return void
	 */
	public function add_control( $id, array $args = array(), array $options = array() ) {}

	/**
	 * Returns the repeater's field definitions.
	 *
	 * @return array<string,mixed>
	 */
	public function get_controls() {}
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
 * Border group control.
 */
class Group_Control_Border {

	/**
	 * Returns the group control type.
	 *
	 * @return string
	 */
	public static function get_type() {}
}

/**
 * Box shadow group control.
 */
class Group_Control_Box_Shadow {

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

	/**
	 * Builds a live element from its saved data.
	 *
	 * @param array<string, mixed> $element_data Saved element data.
	 * @param array<string, mixed> $element_args Extra arguments.
	 * @param Element_Base|null    $element_type Element type.
	 * @return Element_Base|null
	 */
	public function create_element_instance( $element_data, $element_args = array(), $element_type = null ) {}
}

/**
 * The base every element and widget extends.
 */
class Element_Base {}

/**
 * One Elementor document — a page, a template, a builder part.
 */
class Document {

	/**
	 * The post the document is saved on.
	 *
	 * @return int
	 */
	public function get_main_id() {}

	/**
	 * Whether the post was actually built in Elementor.
	 *
	 * @return bool
	 */
	public function is_built_with_elementor() {}

	/**
	 * The saved element tree.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function get_elements_data() {}
}

/**
 * Documents registry.
 */
class Documents_Manager {

	/**
	 * Returns a document for a post.
	 *
	 * @param int  $post_id      Post ID.
	 * @param bool $from_cache   Whether a cached instance may be returned.
	 * @return Document|false
	 */
	public function get( $post_id, $from_cache = true ) {}

	/**
	 * The document currently being rendered, if any.
	 *
	 * @return Document|false
	 */
	public function get_current() {}

	/**
	 * Makes a document the current one.
	 *
	 * @param Document $document Document.
	 * @return void
	 */
	public function switch_to_document( $document ) {}

	/**
	 * Restores the document switch_to_document() replaced.
	 *
	 * @return void
	 */
	public function restore_document() {}
}

/**
 * One responsive breakpoint.
 */
class Breakpoint {

	/**
	 * The breakpoint's max-width in pixels.
	 *
	 * @return int
	 */
	public function get_value() {}

	/**
	 * The breakpoint's name, e.g. 'tablet'.
	 *
	 * @return string
	 */
	public function get_name() {}
}

/**
 * Breakpoints registry.
 */
class Breakpoints_Manager {

	/**
	 * The breakpoints the active kit switched on, keyed by name.
	 *
	 * @return array<string, Breakpoint>
	 */
	public function get_active_breakpoints() {}
}

/**
 * Elementor's helpers.
 */
class Utils {

	/**
	 * Finds an element by ID anywhere in a saved element tree.
	 *
	 * @param array<int, array<string, mixed>> $elements Element tree.
	 * @param string                           $form_id  Element ID.
	 * @return array<string, mixed>|false
	 */
	public static function find_element_recursive( $elements, $form_id ) {}
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
	 * Breakpoints manager.
	 *
	 * @var Breakpoints_Manager|null
	 */
	public $breakpoints;

	/**
	 * Documents manager.
	 *
	 * @var Documents_Manager|null
	 */
	public $documents;

	/**
	 * Editor.
	 *
	 * @var Editor|null
	 */
	public $editor;

	/**
	 * Frontend renderer.
	 *
	 * @var Frontend|null
	 */
	public $frontend;

	/**
	 * Preview.
	 *
	 * @var Preview|null
	 */
	public $preview;

	/**
	 * Returns the singleton.
	 *
	 * @return Plugin
	 */
	public static function instance() {}
}

/**
 * Editor mode.
 */
class Editor {

	/**
	 * Whether the request is the editor itself.
	 *
	 * @return bool
	 */
	public function is_edit_mode() {}
}

/**
 * Preview frame.
 */
class Preview {

	/**
	 * Whether the request is the editor's preview frame.
	 *
	 * @return bool
	 */
	public function is_preview_mode() {}
}

/**
 * Frontend renderer.
 */
class Frontend {

	/**
	 * Returns a template's rendered builder content.
	 *
	 * @param int  $content_id Template ID.
	 * @param bool $with_css   Whether to include generated CSS.
	 * @return string
	 */
	public function get_builder_content_for_display( $content_id, $with_css = false ) {}
}

namespace Elementor\Core\DynamicTags;

/**
 * Dynamic tag base.
 */
abstract class Tag {

	/**
	 * Tag name.
	 *
	 * @return string
	 */
	abstract public function get_name();

	/**
	 * Picker label.
	 *
	 * @return string
	 */
	abstract public function get_title();

	/**
	 * Tag group.
	 *
	 * @return string
	 */
	public function get_group() {}

	/**
	 * Control categories this tag can fill.
	 *
	 * @return string[]
	 */
	public function get_categories() {}

	/**
	 * Outputs the tag's value.
	 *
	 * @return void
	 */
	public function render() {}
}

namespace Elementor\Core\Base;

/**
 * The document behind one post: a page, a template, a builder part.
 *
 * Only the surface the builder registers controls against. The methods are
 * public on Elementor's own Controls_Stack, which is what makes adding a
 * section to somebody else's document possible at all.
 */
class Document {

	/**
	 * The post the document is saved on.
	 *
	 * @return int
	 */
	public function get_main_id() {}

	/**
	 * Opens a controls section.
	 *
	 * @param string              $section_id Section ID.
	 * @param array<string,mixed> $args       Section arguments.
	 * @return void
	 */
	public function start_controls_section( $section_id, array $args = array() ) {}

	/**
	 * Adds a control to the open section.
	 *
	 * @param string              $id      Control ID.
	 * @param array<string,mixed> $args    Control arguments.
	 * @param array<string,mixed> $options Control options.
	 * @return void
	 */
	public function add_control( $id, array $args = array(), $options = array() ) {}

	/**
	 * Closes the open controls section.
	 *
	 * @return void
	 */
	public function end_controls_section() {}
}

namespace Elementor\Core\Files\CSS;

/**
 * The stylesheet Elementor generates for one document.
 */
class Post {

	/**
	 * Returns the file object for a post.
	 *
	 * @param int $post_id Post ID.
	 * @return static
	 */
	public static function create( $post_id ) {}

	/**
	 * Enqueues the generated stylesheet.
	 *
	 * @return void
	 */
	public function enqueue() {}
}
