<?php
/**
 * Customize WP_Query objects to add a little sugar for reference types.
 *
 * @package WP_Documentor
 * @subpackage Parser_Post_Types
 */

namespace WP_Doc\Parser_Types\Queries;

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
	add_filter( 'request',       __NAMESPACE__ . '\\custom_archive'                 );
	add_filter( 'request',       __NAMESPACE__ . '\\fix_file_archives'              );
	add_filter( 'request',       __NAMESPACE__ . '\\support_ref_search'             );
	add_action( 'pre_get_posts', __NAMESPACE__ . '\\support_ref_queries',    100    );
	add_action( 'pre_get_posts', __NAMESPACE__ . '\\sort_by_name',           200    );
	add_filter( 'the_posts',     __NAMESPACE__ . '\\fix_empty_exact_search', 10,  2 );
	add_action( 'wp',            __NAMESPACE__ . '\\redirect_exact_matches'         );
}

/**
 * Allow our custom archive by setting the right post types.
 *
 * We are doing this here because the WP object will filter out the post
 * type as invalid if we simply use the rewrite rule to set it. We get
 * around this by setting the var after the filter has run.
 */
function custom_archive( $query_vars ) {
	if ( ! empty( $query_vars['custom_archive'] ) && 'reference' === $query_vars['custom_archive'] ) {
		$query_vars['post_type'] = 'reference';
	}

	return $query_vars;
}

/**
 * Fixes source file archives so they actually function as expected.
 *
 * @return  array The corrected query vars
 */
function fix_file_archives( $query_vars ) {
	if ( ! empty( $query_vars['wp-parser-source-file'] ) ) {
		$query_vars['wp-parser-source-file'] = str_replace( array( '.php', '/' ), array( '-php', '_' ), $query_vars['wp-parser-source-file'] );
	}
	return $query_vars;
}

/**
 * Allow searches to be made with the 'reference' post type.
 *
 * @return void
 */
function support_ref_search( $query_vars ) {
	if ( empty( $query_vars['rs'] ) ) {
		return $query_vars;
	}

	if ( '()' === substr( $query_vars['rs'], -2 ) ) {
		$query_vars['s'] = substr( $query_vars['rs'], 0, -2 );
		$query_vars['exact'] = true;
		$query_vars['post_type'] = 'callable';
	} else {
		$query_vars['s'] = $query_vars['rs'];
		$query_vars['post_type'] = 'reference';
	}

	return $query_vars;
}

/**
 * Allow queries to be made with the special reference post type slugs.
 *
 * The 'reference' type is actually a grouping of several post types. To help make
 * queries for these post types a little easier, this filter adds some etherial
 * post types that you can use when making queries.
 *
 * By querying for the 'reference' post type, you will actually query for all
 * availble reference post types. This can be mixed in with other post types when
 * making queries if needed.
 *
 * By querying for the 'callable' post type, you will actually query for all
 * reference types that can be called. This can also be mixed in with other post
 * types when making queries if needed.
 *
 * @param  WP_Qery $query Reference to the WP_Query object running the get_posts method.
 * @return void
 */
function support_ref_queries( $query ) {
	$post_types = $query->get( 'post_type' );
	if ( ! empty( $post_types ) ) {
		// Make sure post types is always an array.
		$post_types = ( ! is_array( $post_types ) ) ? array( $post_types ) : $post_types;

		// If we have a 'reference' post type, replace it with the actual types.
		$key = array_search( 'reference', $post_types );
		if ( false !== $key ) {
			array_splice( $post_types, $key, 1, apply_filters( 'wpd_reference_types', array() ) );
		}

		// If we have a 'callable' post type, replace it with the actual types.
		$key = array_search( 'callable', $post_types );
		if ( false !== $key ) {
			array_splice( $post_types, $key, 1, apply_filters( 'wpd_callable_types', array() ) );
		}

		$query->set( 'post_type', array_unique( $post_types ) );
	}
}

/**
 * If this query is *only* for reference types, and no sorting is set, sort by name ASC.
 *
 * @param  WP_Query $query Reference to the WP_Query object runny the get_posts method.
 * @return void
 */
function sort_by_name( $query ) {
	// Check to see if this query has a custom post type specified.
	$post_types = $query->get( 'post_type' );
	if ( empty( $post_typs ) ) {
		return;
	}

	// Make sure post_types is always an array.
	$post_types = ( ! is_array( $post_types ) ) ? array( $post_types ) : $post_types;
	$reference_types = apply_filters( 'wpd_reference_types', array() );

	// Only customize if we have reference types and this query is only for reference types.
	if ( ! empty( $reference_types ) && ! empty( array_diff( $post_types, $reference_types ) ) ) {
		return;
	}

	// Customize the query order unless already explicitly set.
	if ( ! $query->get( 'orderby' ) ) {
		$query->set( 'order', 'title' );
	}
	if ( ! $query->get( 'order' ) ) {
		$query->set( 'order', 'ASC' );
	}
}

/**
 * Rerun an 'exact' reference search without exactness if no posts were found.
 *
 * @param  array    $posts Array of posts after the main query
 * @param  WP_Query $query WP_Query object used to get the posts.
 * @return array           The posts array, augmented if needed.
 */
function fix_empty_exact_search( $posts, $query ) {
	if ( ! empty( $query->get('rs') ) && true === $query->get( 'exact' ) && ! $query->found_posts ) {
		$query->set( 'exact', false );
		$posts = $query->get_posts();
	}
	return $posts;
}

/**
 * If doing a reference search and only one match was found, redirect to its single view.
 *
 * @return void
 */
function redirect_exact_matches() {
	global $wp_query;
	if ( ! empty( get_query_var('rs') ) && 1 == $wp_query->found_posts ) {
		wp_redirect( get_permalink( get_post() ) );
		exit();
	}
}
