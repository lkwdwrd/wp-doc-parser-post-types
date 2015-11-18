<?php
/**
 * Helpers to make use of the rewrites and parser types a little easier.
 *
 * These helpers are tied to hooks so that rather than call them directly, themes can
 * instead invoke the hook. If this plugin is not active, no fatal error is called, it
 * just runs the hook with nothing attached.
 *
 * @package WP_Documentor
 * @subpackage Parser_Post_Types
 */

namespace WP_Doc\Parser_Types\Helpers;

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
 * This function is called on the plugins loaded action. To customize the hooks
 * tied here, hook in late on plugins loaded and modify as needed. Similarly, to
 * remove all of these hook, hook in early and unhook the load function.
 *
 * @return void.
 */
function setup() {
	add_filter( 'wpd_reference_types',  __NAMESPACE__ . '\\default_reference_types'       );
	add_filter( 'wpd_callable_types',   __NAMESPACE__ . '\\default_callable_types'        );
	add_filter( 'wpd_search_form',      __NAMESPACE__ . '\\reference_search_form'         );
	add_filter( 'wpd_is_ref_type',      __NAMESPACE__ . '\\is_reference_type',      10, 2 );
	add_filter( 'wpd_is_callable_type', __NAMESPACE__ . '\\is_reference_type',      10, 2 );
}

/**
 * Get an array of parser post type slugs.
 *
 * This funciton returns the default available parser post type slugs in an array.
 * It is intended to be used with the 'wpd_reference_types' filter. Calling this
 * function using that filter means you don't have to worry about this function
 * existing before using it. Just call the filter and if this function is available
 * it will add in the post types as needed. By the same token, customizations can be
 * made by registering this function to that hook and adding additional types or
 * hooking late and modifying what this function sends.
 *
 * ```
 * apply_filters( 'wpd_reference_types', array() );
 * ```
 *
 * @param  array $post_types Optional. The array of parser post types.
 * @return array             The array of parser types with the default types added.
 */
function default_reference_types( $post_types = array() ) {
	$post_types = array_merge( $post_types, array(
		'wp-parser-function',
		'wp-parser-hook',
		'wp-parser-class',
		'wp-parser-method',
	) );
	return $post_types;
}

/**
 * Get an array of callable parser post type slugs.
 *
 * This funciton returns the default available parser post type slugs in an array.
 * It is intended to be used with the 'wpd_callable_types' filter. Calling this
 * function using that filter means you don't have to worry about this function
 * existing before using it. Just call the filter and if this function is available
 * it will add in the post types as needed. By the same token, customizations can be
 * made by registering this function to that hook and adding additional types or
 * hooking late and modifying what this function sends.
 *
 * ```
 * apply_filters( 'wpd_callable_types', array() );
 * ```
 *
 * @param  array $post_types Optional. The array of parser post types.
 * @return array             The array of parser types with the default types added.
 */
function default_callable_types( $post_types = array() ) {
	$post_types = array_merge( $post_types, array(
		'wp-parser-function',
		'wp-parser-method',
	) );
	return $post_types;
}

/**
 * Display a reference search form.
 *
 * Will first attempt to locate the searchform-reference.php file in either the
 * child or the parent, then load it. If it doesn't exist, then the default
 * search form will be displayed.
 *
 * The classic WP search form actions are called, but as reference variants.
 *
 * This funciton is intended to be used with the 'wpd_search_form' filter. Calling
 * this function using that filter means you don't have to worry about this function
 * existing before using it. Just call the filter and if this function is available
 * it will the search form. By the same token, customizations can be made by
 * hooking late and modifying what this function sends. Although the internal hooks
 * should be more than sufficient.
 *
 * ```
 * echo apply_filters( 'wpd_search_form', '' );
 * ```
 *
 * @return string The reference search form HTML.
 */
function reference_search_form() {
	/**
	 * Fires before the search form is retrieved, at the start of reference_serach_form().
	 */
	do_action( 'pre_reference_search_form' );

	$search_form_template = locate_template( 'searchform-reference.php' );
	if ( '' === $search_form_template ) {
		$search_form_template = WPD_PCPTS_PATH . 'templates/searchform-reference.php';
	}

	ob_start();
	require( $search_form_template );
	$form = ob_get_clean();

	/**
	 * Filter the HTML output of the reference search form.
	 *
	 * @param string $form The reference search form HTML output.
	 */
	return apply_filters( 'reference_search_form', $form );
}

/**
 * Special helper for checking if a post type is a reference type.
 *
 * This function should not be used directly. Instead, use the filter 'wpd_is_ref_type'
 * sending false as the first parameter and the post type as the second parameter. This
 * function is hooked to that filter and will run the check. However, if for some
 * reason this plugin is disabled, nothing will throw fatal errors, it will simply get
 * false back from the filter and your code will continue on its merry way.
 *
 * ```
 * if( apply_filters( 'wpd_is_ref_type', false, $post_type ) ) {
 * 	//do something with only reference post types.
 * }
 * ```
 *
 * @param   bool   $result The current result, typically false since this checks.
 * @param   string $type   The post type slug to check.
 * @return  bool           Whether or not the passed type is a reference post type.
 */
function is_reference_type( $result, $type ) {
	$result = in_array( $type, apply_filters( 'wpd_reference_types', array() ) );
	return $result;
}

/**
 * Special helper for checking if a post type is a callable reference type.
 *
 * This function should not be used directly. Instead, use the filter
 * 'wpd_is_callable_type' sending false as the first parameter and the post type as
 * the second parameter. This function is hooked to that filter and will run the check.
 * However, if for some reason this plugin is disabled, nothing will throw fatal
 * errors, it will simply get false back from the filter and your code will continue
 * on its merry way.
 *
 * ```
 * if( apply_filters( 'wpd_is_callable_type', false, $post_type ) ) {
 * 	//do something with only callable reference post types.
 * }
 * ```
 */
function is_callable_type( $result, $type ) {
	$result = in_array( $type, apply_filters( 'wpd_callable_types', array() ) );
	return $result;
}
