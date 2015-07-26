<?php
namespace TenUp\A1D_WP_Accessibility\Core;

$accessibility_guidelines = array (
  'WCAG2-AA',
  'BITV1',
  '508',
  'STANCA',
  'WCAG1-A',
  'WCAG1-AA',
  'WCAG1-AAA',
  'WCAG2-A',
  'WCAG2-AAA'
);

/**
 * Generate available plugin settings
 *
 * @uses add_settings_section 
 *
 *
 * @return void
 */

function a1daccess_settings_init() {

  // Allow users to choose from a number of accessibility guidelines available 
  // from the IDI Web Accessibility Checker API (http://achecker.ca/documentation/web_service_api.php)
  register_setting( 
    'a1daccess_accessibility_options', 
    'a1daccess_accessibility_options', 
    __NAMESPACE__ . '\a1daccess_accessibility_options_validation' 
  );
  add_settings_section( 
    'a1daccess_accessibility_settings', 
    'Accessibility Settings', 
    __NAMESPACE__ . '\a1daccess_settings_info', 
    'a1d-accessibility' 
  ); 
  add_settings_field( 
    'a1daccess_validation_guideline_options', 
    'Validation Guideline', 
    __NAMESPACE__ . '\a1daccess_settings_validation_options', 
    'a1d-accessibility', 
    'a1daccess_accessibility_settings' 
  );

}

function a1daccess_settings_info() {

  $info = "<p>There are several available guidelines for web accessibility. For more information about the available options, please reference the w3 page: <a href='http://www.w3.org/WAI/intro/wcag'>http://www.w3.org/WAI/intro/wcag</a></p>";
  echo $info;
}

function a1daccess_settings_validation_options($guidelines) { 

  global $accessibility_guidelines;
  $options = get_option('a1daccess_accessibility_options');


  if ( $options['guideline'] ) {
    $guideline_options = "<select id='a1daccess_accessibility_options_guideline' name='a1daccess_accessibility_options[guideline]' value='{$options['guideline']}'>"; 
  } else {
    $guideline_options = "<select id='a1daccess_accessibility_options_guideline' name='a1daccess_accessibility_options[guideline]' value=''>"; 
  }
  foreach ( $accessibility_guidelines as $guideline ) {
    if ( $options['guideline'] === $guideline ) {
      $guideline_options .= "<option selected value='{$guideline}'>{$guideline}</option>";
    } else {
      $guideline_options .= "<option value='{$guideline}'>{$guideline}</option>";
    }
  } 
  $guideline_options .= "</select>";

  echo $guideline_options;
}

/**
  * Validate settings input
  * 
  */

function a1daccess_accessibility_options_validation ( $input ){

  if( $input['guideline'] ){
    return $input;
  }

}


/**
 * Generate Admin dashboard
 * 
 * @uses add_options_page 
 *
 * @return void
 */

function register_a1daccess_admin() {

  add_options_page( 'A1D Accessibility', 'Accessibility', 'manage_options', 'a1d-accessibility', '\TenUp\A1D_WP_Accessibility\Core\a1daccess_dashboard' ); 
}

function a1daccess_dashboard() {

  $admin_template = A1DACCESS_INC . 'templates/admin.php';
  include_once( $admin_template );
}
