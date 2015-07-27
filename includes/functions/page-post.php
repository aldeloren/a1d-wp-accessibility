<?php
namespace TenUp\A1D_WP_Accessibility\Core;

/**
 * Add metabox(es) to pages and posts
 *
 * @uses a bunch
 *
 */

function a1daccess_add_metabox() {

  $screens = array (
      'page',
      'post'
  );

  // Add metabox to pages and posts
  foreach ( $screens as $screen ) {
    add_meta_box(
      'a1daccess_metabox',
      __( 'Accessibility', 'a1daccess' ),
      __NAMESPACE__ . '\a1daccess_metabox_callback',
      $screen
    );
  }
}

add_action( 'add_meta_boxes', __NAMESPACE__ . '\a1daccess_add_metabox' );

/**
 * Generate metabox structure and contents
 *
 * @uses wp_nonce_field
 * @uses stuff
 *
 */

function a1daccess_metabox_callback( $post ) {

  wp_nonce_field( 'a1daccess_metabox_save_data', 'a1daccess_metabox_nonce' );

  $values = get_post_meta( $post->ID, '_a1daccess_metabox_data', true );
  $page_post_template = A1DACCESS_INC . 'templates/page-post-meta.php';
  include_once( $page_post_template );
}
