<?php
namespace TenUp\A1D_WP_Accessibility\Core;


/**
 * Add metabox(es) to pages and posts
 *
 * @uses add_meta_box()
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
 * @uses wp_nonce_field()
 * @uses get_post_meta()
 *
 */

function a1daccess_metabox_callback( $post ) {

  $page_post_template = A1DACCESS_INC . 'templates/page-post-meta.php';
  include_once( $page_post_template );
}

add_action( 'admin_footer', __NAMESPACE__ . '\a1daccess_metabox_ajax_js' );

/**
 * Build javascript call to admin-ajax.php
 *
 * ajaxurl is defined by wordpress
 * keep script inline to reduce HTTP requests
 * typically this logic should be server-side
 *
 * @return void
 */

function a1daccess_metabox_ajax_js() {
  global $post;  ?>
  <script type="text/javascript">
    jQuery(document).ready(function($){
      var loaderID = '#a1daccess_load_loader';

      var data = {
        'action': 'a1daccess_metabox_update',
        'post_id': '<?php echo $post->ID; ?>'
      };

      jQuery.post(ajaxurl, data, function(response){
        displayreccommendations(response);
        hideLoader(loaderID);
      });

      function displayreccommendations(jsonString){
        var json = jQuery.parseJSON(jsonString);
        if(!json.status || json.status != 'error'){
          var errorHeader = '#a1daccess_metabox_errors',
              potentialHeader = '#a1daccess_metabox_likely_problems',
              likelyPotentialHeader = '#a1daccess_metabox_likely_potential_problems',
              errorBody = '#a1daccess_metabox_errors_container',
              likelyProblemBody = '#a1daccess_metabox_likely_problems_container',
              potentialProblemBody = '#a1daccess_metabox_likely_potential_problems_container',
              sortedJson = sortReccomendations(json),
              errors = formatreccommendations(sortedJson.errors),
              likelyProblems = formatreccommendations(sortedJson.likelyProblems),
              potentialProblems = formatreccommendations(sortedJson.potentialProblems);

          $(errorHeader).text('Errors: ' + sortedJson.errors.length);
          $(errorBody).html(errors);
          $(likelyPotentialHeader).text('Likely Potential Problems: ' + sortedJson.potentialProblems.length);
          $(likelyProblemBody).html(likelyProblems);
          $(potentialHeader).text('Potential Problems: ' + sortedJson.likelyProblems.length);
          $(potentialProblemBody).html(potentialProblems);
          $('#a1daccess_metabox_inner, #a1daccess_metabox_issue_container, #a1daccess_metabox_issue.active').show();
        }
      };

      function formatreccommendations(reccommendations){
        var entries = '';
        $.each(reccommendations, function(key, val){
          var current = val[0],
              line = current.lineNum,
              repair = current.repair || current.decisionFail,
              source = current.errorSourceCode,
              suggestion = current.errorMsg,
              formattedEntry = "<div class='a1daccess_metabox_formatted_entry'>";

          formattedEntry += "<div class='a1daccess_metabox_formatted_line'><strong>Line number:</strong> <span>" + line + "</span></div>";
          formattedEntry += "<div class='a1daccess_metabox_right_inner'><p><strong>Problem:</strong> " + repair + "</p>";
          formattedEntry += "<p><strong>View more:</strong> " + suggestion + "</p></div></div>";
          entries += formattedEntry;
        })
        return entries;
      };

      function sortReccomendations(reccommendations){
        var sortedreccommendations = {
          errors:[],
          likelyProblems: [],
          potentialProblems: []
        };

        $.each(reccommendations.results.result, function(key, val){
          if(val.resultType == "Error"){
            sortedreccommendations.errors.push($(this));
          }
          if(val.resultType == "Likely Problem"){
            sortedreccommendations.likelyProblems.push($(this));
          }
          if(val.resultType == "Potential Problem"){
            sortedreccommendations.potentialProblems.push($(this));
          }
        })
        console.log(sortedreccommendations);
        return sortedreccommendations;
      };

      function switchTab(tab){
        var tabContainer = tab + '_container';
        $('.a1daccess_metabox_issue').hide();
        $(tabContainer).show();
        $('.a1daccess_tabs li').removeClass('active');
        $(tab).addClass('active');
      }

      function showLoader(id){
        $(id).show()
      }

      function hideLoader(id){
        $(id).hide()
      }

      $('.a1daccess_tabs li').click(function(){
        var tabID = '#' + $(this).attr('id');
        switchTab(tabID);
      });
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
  $response = a1daccess_retrieve_accessibility_reccommendations( $options['api_id'], $options['guideline'], $post_id );
  echo $response;

  // Make sure to terminate for proper response
  wp_die();
}

/**
 * Return the accessibility reccommendations provided by the API
 *
 * TODO
 * Find a new API that can accept text, or develop own following standards
 *
 * @uses get_post_status(), needed as current API requires URI to validate against
 *
 * @returns reccommendations or error and status code(s)
 */

function a1daccess_retrieve_accessibility_reccommendations ( $api_id, $guideline, $id ) {

  $response = new \stdclass;
  if ( 'publish' === get_post_status( $id ) ) {
    if ( $api_id && $guideline && $id ) {
      $post_uri = urlencode( esc_url( get_permalink( $id ) ) );
      $validation_uri = "http://achecker.ca/checkacc.php?uri={$post_uri}&id={$api_id}&output=rest&quide={$guideline}";
      $reccommendations = a1daccess_reccommendations_from_service( $validation_uri );
      return $reccommendations;
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
 * Retreive the accessibility reccommendations from service
 *
 * @uses wp_remote_get() (abstraction of WP_http class) to ensure that most systems are supported
 * @uses wp_remote_retrieve_body()
 * @uses is_wp_error()
 *
 * @returns object restful validation response
 */

function a1daccess_reccommendations_from_service ( $uri ) {

  $reccommendations = wp_remote_get( $uri );
  if ( !is_wp_error( $reccommendations ) ) {
    $reccommendations_json = json_encode( simplexml_load_string( wp_remote_retrieve_body( $reccommendations ) ) );
    if ( !is_wp_error( $reccommendations_json ) ) {
      return $reccommendations_json;
    }
  } else {
    $response = new \stdclass;
    $response->status = 'error';
    $response->description = 'Unable to retrieve validation reccommendations at this time.';
    return $response;
  }
}
