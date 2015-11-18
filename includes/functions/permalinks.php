<?php
/**
 * Sets up rewrites and permalink related functionality for parser post types.
 *
 * @package WP_Documentor
 * @subpackage Parser_Post_Types
 */

namespace WP_Doc\Parser_Types\Permalinks;

/**
 * Initializes this file's functionality on a WP hook.
 *
 * @return void
 */
function load() {
	add_action( 'plugins_loaded', __NAMESPACE__ . '\\setup' );
}

/**
 * Sets up various functionaly with the WP APIs.
 *
 * @return void
 */
function setup() {
	add_action( 'query_vars',     __NAMESPACE__ . '\\add_query_vars'            );
	add_action( 'wpd_pcpts_init', __NAMESPACE__ . '\\rewrites',           20    );
	add_filter( 'post_type_link', __NAMESPACE__ . '\\method_permalink',   10, 2 );
	add_filter( 'term_link',      __NAMESPACE__ . '\\taxonomy_permalink', 10, 3 );
}

/**
 * Add query vars to the acceptable list for WP Rewrites
 *
 * @param  array $query_vars The current available query vars accepted.
 * @return array             The customized set of available query vars.
 */
function add_query_vars( $query_vars ) {
	return array_merge( $query_vars, array( 'custom_archive', 'rs' ) );
}

/**
 * Setup some special rewrites for the reference post type.
 *
 * @return void
 */
function rewrites() {
	// Methods
	add_rewrite_rule( '^reference/classes/page/([0-9]{1,})/?$', 'index.php?post_type=wp-parser-class&paged=$matches[1]', 'top' );
	add_rewrite_rule( '^reference/classes/([^/]+)/([^/]+)/?$', 'index.php?post_type=wp-parser-method&name=$matches[1]-$matches[2]', 'top' );
	// Rererence Archive
	add_rewrite_rule( '^reference/?$', 'index.php?custom_archive=reference', 'top' );
	add_rewrite_rule( '^reference/page/([^/]+)/?$', 'index.php?custom_archive=reference&paged=matches[1]', 'top' );
	add_rewrite_rule( '^reference/feed/(feed|rdf|rss|rss2|atom)/?$', 'index.php?custom_archive=reference&feed=$matches[1]');
}

/**
 * Make the method permalink actually work when using get_permalink helpers.
 *
 * @param  string $link  The current permalink.
 * @param  WP_Post $post The WP_Post object the permalink is being generated for.
 * @return string        The correct permalink for the object.
 */
function method_permalink( $link, $post ) {
	if ( $post->post_type !== 'wp-parser-method' ) {
		return $link;
	}

	$url_bits = explode( '-', $post->post_name );
	$method = array_pop( $url_bits );
	$class = implode( '-', $url_bits );
	$link = home_url( user_trailingslashit( "reference/classes/$class/$method" ) );
	return $link;
}

/**
 * Make the taxonomy permalink actually work when using get_taxonomy_permalink.
 *
 * @param  string   $link     The current taxonomy permalink.
 * @param  stdClass $term     The term object we're creating the permalink for.
 * @param  string   $taxonomy The taxonomy slug for the term.
 * @return string             The correct permalink for the term.
 */
function taxonomy_permalink( $link, $term, $taxonomy ) {
	if ( $taxonomy === 'wp-parser-source-file' ) {
		$slug = $term->slug;
		if ( substr( $slug, -4 ) === '-php' ) {
			$slug = substr( $slug, 0, -4 ) . '.php';
			$slug = str_replace( '_', '/', $slug );
		}
		$link = home_url( user_trailingslashit( "reference/files/$slug" ) );
	} elseif ( $taxonomy === 'wp-parser-since' ) {
		$link = str_replace( $term->slug, str_replace( '-', '.', $term->slug ), $link );
	}
	return $link;
}
