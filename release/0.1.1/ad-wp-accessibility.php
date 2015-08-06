<?php
/**
 * Plugin Name: A1D WP Accessibility
 * Plugin URI:  https://a1d.co/accessibility
 * Description: A WordPress plugin to to encourage content creators to create more accessible content for the differently-abled.
 * Version:     0.1.0
 * Author:      Anthony DeLorenzo
 * Author URI:  a1d.co
 * License:     GPLv2+
 * Text Domain: a1daccess
 * Domain Path: /languages
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

// Useful global constants
define( 'A1DACCESS_VERSION', '0.1.0' );
define( 'A1DACCESS_URL',     plugin_dir_url( __FILE__ ) );
define( 'A1DACCESS_PATH',    dirname( __FILE__ ) . '/' );
define( 'A1DACCESS_IMGS',    A1DACCESS_PATH . 'images/' );
define( 'A1DACCESS_INC',     A1DACCESS_PATH . 'includes/' );

// Include files
require_once A1DACCESS_INC . 'functions/core.php';


// Activation/Deactivation
register_activation_hook( __FILE__, '\TenUp\A1D_WP_Accessibility\Core\activate' );
register_deactivation_hook( __FILE__, '\TenUp\A1D_WP_Accessibility\Core\deactivate' );

// Bootstrap
TenUp\A1D_WP_Accessibility\Core\setup();
