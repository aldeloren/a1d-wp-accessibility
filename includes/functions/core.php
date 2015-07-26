<?php
namespace TenUp\A1D_WP_Accessibility\Core;

/**
 * Default setup routine
 *
 * @uses add_action()
 * @uses do_action()
 *
 * @return void
 */

include_once( __DIR__ . '/admin.php' );

function setup() {
	$n = function( $function ) {
		return __NAMESPACE__ . "\\$function";
	};

	add_action( 'init', $n( 'i18n' ) );
	add_action( 'init', $n( 'init' ) );
  add_action( 'admin_menu', $n( 'register_a1daccess_admin' ) );
  add_action( 'admin_menu', $n( 'a1daccess_settings_init' ) );

	do_action( 'a1daccess_loaded' );
	do_action( 'register_a1daccess_admin' );
}

/**
 * Registers the default textdomain.
 *
 * @uses apply_filters()
 * @uses get_locale()
 * @uses load_textdomain()
 * @uses load_plugin_textdomain()
 * @uses plugin_basename()
 *
 * @return void
 */
function i18n() {
	$locale = apply_filters( 'plugin_locale', get_locale(), 'a1daccess' );
	load_textdomain( 'a1daccess', WP_LANG_DIR . '/a1daccess/a1daccess-' . $locale . '.mo' );
	load_plugin_textdomain( 'a1daccess', false, plugin_basename( A1DACCESS_PATH ) . '/languages/' );
}

/**
 * Initializes the plugin and fires an action other plugins can hook into.
 *
 * @uses do_action()
 *
 * @return void
 */
function init() {
	do_action( 'a1daccess_init' );
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

}
