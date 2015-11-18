<?php
/**
 * Sets up the parser plugin needs, like init hooks and i18n setup.
 *
 * @package WP_Documentor
 * @subpackage Parser_Post_Types
 */
namespace WP_Doc\Parser_Types\Core;

/**
 * Load this file into the WP API.
 *
 * This file is loaded on a WP Hook so that it is easy to control the load
 * order relative to this file if needed. Or, this entire file can be
 * unloaded by simply unhooking this function.
 *
 * @return void
 */
function load() {
	add_action( 'plugins_loaded', __NAMESPACE__ . '\\setup' );
}

/**
 * Hooks up various functions in this file with the WP API.
 *
 * This function is called on the plugins loaded_action. To customize the hooks
 * tied here, hook in late on plugins loaded and modify as needed. Similarly, to
 * remove all of these hooks, hook in early and unhook the load function.
 *
 * @return void
 */
function setup() {
	add_action( 'init', __NAMESPACE__ . '\\i18n' );
	add_action( 'init', __NAMESPACE__ . '\\init' );
	/**
	 * Allows for specific actions to be tied to a plugin specific init hook
	 *
	 * This runs during the normal WordPress init hook on priority 10.
	 */
	do_action( 'wpd_pcpts_loaded' );
}

/**
 * Registers the default textdomain.
 *
 * @return void
 */
function i18n() {
	$locale = apply_filters( 'plugin_locale', get_locale(), 'wpd_pcpts' );
	load_textdomain( 'wpd_pcpts', WP_LANG_DIR . '/wpd_pcpts/wpd_pcpts-' . $locale . '.mo' );
	load_plugin_textdomain( 'wpd_pcpts', false, plugin_basename( WPD_PCPTS_PATH ) . '/languages/' );
}

/**
 * Initializes the plugin and fires an action other plugins can hook into.
 *
 * @uses do_action()
 *
 * @return void
 */
function init() {
	/**
	 * Allows for specific actions to be tied to a plugin specific init hook
	 *
	 * This runs during the normal WordPress init hook on priority 10.
	 */
	do_action( 'wpd_pcpts_init' );
}

/**
 * Activate the plugin
 *
 * @uses init()
 * @uses flush_rewrite_rules()
 *
 * @return void
 */
function activate() {
	// First load the init scripts in case any rewrite functionality is being loaded
	init();
	flush_rewrite_rules();
}

/**
 * Deactivate the plugin
 *
 * Uninstall routines should be in uninstall.php
 *
 * @return void
 */
function deactivate() {
	// Our rewrites are no longer needed, go ahead and flush them.
	flush_rewrite_rules();
}
