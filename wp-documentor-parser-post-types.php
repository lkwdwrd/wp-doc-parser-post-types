<?php
/**
 * Plugin Name: WP Documentor Parser Post Types
 * Plugin URI:  http://wordpress.org/plugins
 * Description: Standalone Parser Post Types with proper rewrite rules.
 * Version:     0.1.0
 * Author:      Luke Woodward
 * Author URI:  https://lkwdwrd.com
 * License:     GPLv2+
 * Text Domain: wpd_pcpts
 * Domain Path: /languages
 *
 * @package WP_Documentor
 * @subpackage Parser_Post_Types
 */

/**
 * Copyright (c) 2015 10up (email : info@10up.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2 or, at
 * your discretion, any later version, as published by the Free
 * Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

/**
 * Built using yo wp-make:plugin
 * Copyright (c) 2015 10up, LLC
 * https://github.com/10up/generator-wp-make
 */

namespace WP_Doc\Parser_Types;

// Useful global constants
define( 'WPD_PCPTS_VERSION', '0.1.0' );
define( 'WPD_PCPTS_URL',     plugin_dir_url( __FILE__ ) );
define( 'WPD_PCPTS_PATH',    dirname( __FILE__ ) . '/' );
define( 'WPD_PCPTS_INC',     WPD_PCPTS_PATH . 'includes/' );

// Include files
require_once WPD_PCPTS_INC . 'functions/core.php';
require_once WPD_PCPTS_INC . 'functions/helpers.php';
require_once WPD_PCPTS_INC . 'functions/permalinks.php';
require_once WPD_PCPTS_INC . 'functions/queries.php';
require_once WPD_PCPTS_INC . 'functions/registrations.php';
require_once WPD_PCPTS_INC . 'functions/templates.php';

// Activation/Deactivation
register_activation_hook( __FILE__, 'WP_Doc\Parser_Types\Core\activate' );
register_deactivation_hook( __FILE__, 'WP_Doc\Parser_Types\Core\deactivate' );

// Bootstrap
\WP_Doc\Parser_Types\Core\load();
\WP_Doc\Parser_Types\Registrations\load();
\WP_Doc\Parser_Types\Helpers\load();
\WP_Doc\Parser_Types\Permalinks\load();
\WP_Doc\Parser_Types\Queries\load();
\WP_Doc\Parser_Types\Templates\load();
