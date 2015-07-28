<?php
namespace TenUp\A1D_WP_Accessibility\Core;


/**
 * Add metabox(es) to pages and posts
 *
 * @uses add_meta_box 
 *
 * @returns void
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
 * @uses get_post_meta 
 *
 */

 function a1daccess_metabox_callback( $post ) {
  
  wp_nonce_field( 'a1daccess_metabox_save_data', 'a1daccess_metabox_nonce' );
  $values = get_post_meta( $post->ID, '_a1daccess_metabox_data', true );
  $page_post_template = A1DACCESS_INC . 'templates/page-post-meta.php';
  include_once( $page_post_template );
}

function a1daccess_metabox_save_data( $post_id ) {
  
}

add_action( 'admin_footer', __NAMESPACE__ . '\a1daccess_metabox_ajax_js' ); 

/**
 * Build javascript call to admin-ajax.php
 *
 * ajaxurl is defined by wordpress
 *
 * @return void
 */

function a1daccess_metabox_ajax_js() { 
  global $post;  ?>
  <script type="text/javascript">
    jQuery(document).ready(function($){
      
      var data = {
        'action': 'a1daccess_metabox_update',
        'post_id': '<?php echo $post->ID; ?>'
      };

      jQuery.post(ajaxurl, data, function(response){
        console.log('server response: ' + response);
      })
    }); 
  </script>
<?php
}


/**
 * Check page/post content against idi accessibility API
 *
 * @return REST response 
 */

add_action( 'wp_ajax_a1daccess_metabox_update', __NAMESPACE__ . '\a1daccess_metabox_ajax_response' );

function a1daccess_metabox_ajax_response() {

  $post_id = intval( $_POST['post_id'] );
  $options = get_option( 'a1daccess_accessibility_options' );
  $response = a1daccess_retrieve_accessibility_recommendations( $options['api_id'], $options['guideline'], $post_id );
  echo $response;
  
  // Make sure to terminate for proper response 
  wp_die();
}

/**
 * Return the accessibility recommendations provided by the API
 *
 * TODO 
 * Find a new API that can accept text, or develop own following standards
 *
 * @uses get_post_status, needed as current API requires URI to validate against
 *
 * @returns recommendations or error and status code(s)
 */

function a1daccess_retrieve_accessibility_recommendations ( $api_id, $guideline, $id ) { 
  
  $response = new \stdclass;
  if ( 'publish' === get_post_status( $id ) ) {
    if ( $api_id && $guideline && $id ) {
      $post_uri = urlencode( esc_url( get_permalink( $id ) ) );
      $validation_uri = "http://achecker.ca/checkacc.php?uri={$post_uri}&id={$api_id}&output=rest&quide={$guideline}";
      $recommendations = a1daccess_recommendations_from_service( $validation_uri );
      return $recommendations;
    } else {
      $response->status = 'error';
      $response->description = 'Please confirm that your API ID, and validation guideline is set.';
      return json_encode( $response );
    }
  } else {
    $response->status = 'error';
    $response->description = 'Pages and posts must be published (and publicly accessible) in order to be validated.';
    return json_encode( $response );
  }
}

/**
 * Retreive the accessibility recommendations from service
 *
 * @uses wp_remote_get (abstraction of WP_http class) to ensure that most systems are supported
 * @uses wp_remote_retrieve_body
 * @uses is_wp_error
 *
 * @returns object restful validation response
 */

function a1daccess_recommendations_from_service ( $uri ) {

  $recommendations = wp_remote_get( $uri ); 
  if ( !is_wp_error( $recommendations ) ) {
    $recommendations_json = json_encode( simplexml_load_string( wp_remote_retrieve_body( $recommendations ) ) );
    if ( !is_wp_error( $recommendations_json ) ) {
      return $recommendations_json;
    }
  } else {
    $response = new \stdclass;
    $response->status = 'error';
    $response->description = 'Unable to retrieve validation recommendations at this time.';
    return $response;
  }
}
