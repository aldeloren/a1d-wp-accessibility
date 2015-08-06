<?php

if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) 
    exit();

/*
 * Remove plugin options 
 *
 * @uses delete_option()
 *
 * @returns void
 */

function a1daccess_remove_plugin() {
 
  delete_option( 'a1daccess_accessibility_options' );
}

a1daccess_remove_plugin();
