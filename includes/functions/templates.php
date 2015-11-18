<?php
/**
 * Make reference type templates work by adding to the template hierarchy.
 *
 * @package WP_Documentor
 * @subpackage Parser_Post_Types
 */

namespace WP_Doc\Parser_Types\Templates;

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
	add_action( 'template_include', __NAMESPACE__ . '\\archive_template' );
	add_filter( 'single_template',  __NAMESPACE__ . '\\single_template'  );
	add_filter( 'search_template',  __NAMESPACE__ . '\\search_template'  );
}

/**
 * Make a 'reference' archive a thing in the template hierarchy.
 *
 * @param  string $template The current template chosen.
 * @return string           The template to use for this view.
 */
function archive_template( $template ) {
	if ( ! is_search() && ! is_feed() && 'reference' === get_query_var( 'custom_archive' ) ) {
		$archive_template = get_query_template( 'archive', array(
			'archive-reference.php',
			'archive.php'
		) );
		$template = ( $archive_template ) ? $archive_template : $template;
	}
	return $template;
}

/**
 * Make a 'reference' single template available in the template hierarchy.
 *
 * The single reference template will fall between the single-{post_type}.php
 * template and the single.php template for any reference post type. If the
 * single-{post-type}.php is defined it will use that, then single-reference.php
 * then single.php, then singluar.php, then index.php as a final fallback.
 *
 * @param  string $template The located template to use.
 * @return string           The template, overriden as needed to add reference.
 */
function single_template( $template ) {
	// check if we hit single.php or missed completely.
	if ( 'single.php' === basename( $template ) || empty( $template ) ) {
		$object = get_queried_object();
		// Make sure there is a post type.
		if ( empty( $object->post_type ) ) {
			return $template;
		}
		// If this is a reference type, inject the single-reference template check.
		if ( in_array( $object->post_type, apply_filters( 'wpd_reference_types', array() ) ) ) {
			$template = locate_template( array(
				'single-reference.php',
				'single.php',
			) );
		}
	}

	return $template;
}

/**
 * Make a 'search' template hierarchy where one is for reference searches.
 *
 * The new reference search template will be available on ?rs= searches. This will
 * with then load search-reference first, then search.php, then finally fall back
 * to the index.php template as a last resort.
 *
 * @param  string $template The located template to use.
 * @return string           The template, overriden as needed to add reference.
 */
function search_template( $template ) {
	$reference_search = get_query_var( 'rs' );
	// check if we hit single.php or missed completely.
	if ( $reference_search ) {
		$template = locate_template( array(
			'search-reference.php',
			'search.php',
		) );
	}

	return $template;
}
