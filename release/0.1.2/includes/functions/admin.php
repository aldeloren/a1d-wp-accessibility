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
 * Register plugin administrative styles
 *
 * @uses wp_register_style()
 * @uses wp_enqueue_style()
 *
 * @returns void
 */

function a1daccess_load_admin_styles() {

  wp_register_style( 'a1daccess_admin_css', A1DACCESS_URL . '/assets/css/ad-wp-accessibility-admin.css', false, '' );
  wp_enqueue_style( 'a1daccess_admin_css' );
}

/**
 * Generate available plugin settings
 *
 * @uses register_setting()
 * @uses add_settings_section()
 * @uses add_settings_field()
 *
 * @return void
 */

function a1daccess_settings_init() {

   register_setting (
    'a1daccess_accessibility_options',
    'a1daccess_accessibility_options',
    __NAMESPACE__ . '\a1daccess_accessibility_options_validation'
  );
  add_settings_section (
    'a1daccess_accessibility_settings',
    'Accessibility Settings',
    __NAMESPACE__ . '\a1daccess_settings_info',
    'a1d-accessibility'
  );
  add_settings_field (
    'a1daccess_api_id',
    'API ID',
    __NAMESPACE__ . '\a1daccess_settings_api_id',
    'a1d-accessibility',
    'a1daccess_accessibility_settings'
  );
  // Allow users to choose from a number of accessibility guidelines available
  // from the IDI Web Accessibility Checker API (http://achecker.ca/documentation/web_service_api.php)
  add_settings_field (
    'a1daccess_validation_guideline_options',
    'Validation Guideline',
    __NAMESPACE__ . '\a1daccess_settings_validation_options',
    'a1d-accessibility',
    'a1daccess_accessibility_settings'
  );
}

/**
 * Display plugin info and helper text
 *
 * @uses get_option()
 *
 * @return string html
 */

function a1daccess_settings_info() {

  $options = get_option( 'a1daccess_accessibility_options' );
  $info = '';
  $is_registered = false;
  if ( array_key_exists ( 'api_id', $options ) ) {
    if ( true === is_idi_app_id_valid( $options['api_id'] ) ) {
      $is_registered = true;
    }
  }

  if ( false === $is_registered ) {
    $info .= "<p class='a1daccess_important'>Please register a valid API ID by registering at this page: <a href='http://achecker.ca/register.php'>http://achecker.ca/register.php</a></p>";
  }
  $info .= "<p>There are several available guidelines for web accessibility. For more information about the available options, please reference the w3 page: <a href='http://www.w3.org/WAI/intro/wcag'>http://www.w3.org/WAI/intro/wcag</a></p>";
  echo $info;
}

/**
 * Accept API ID
 *
 * @return string 40 char API ID
 */

function a1daccess_settings_api_id() {

  $options = get_option( 'a1daccess_accessibility_options' );
  if ( array_key_exists( 'api_id', $options ) ) {
    $api_id_option = "<input id='a1daccess_api_id' type='text' name='a1daccess_accessibility_options[api_id]' value='{$options['api_id']}' required pattern='[a-fA-F0-9]{40}$' title='Please enter a valid 40 character API ID'>";
  } else {
    $api_id_option = "<input id='a1daccess_api_id' type='text' name='a1daccess_accessibility_options[api_id]' value='' required pattern='[a-fA-F0-9]{40}$' title='Please enter a valid 40 character API ID'>";
  }
  echo $api_id_option;
}

/**
 * Generate available accessibility guidelines values
 *
 * @uses get_option()
 *
 * @return string html select input for setting form submission
 */

function a1daccess_settings_validation_options() {

  global $accessibility_guidelines;
  $options = get_option( 'a1daccess_accessibility_options' );

  if ( array_key_exists( 'guideline', $options ) ) {
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
  * @returns array validated input
  */

function a1daccess_accessibility_options_validation ( $input ){

  global $accessibility_guidelines;
  $new_input = array ();

  if ( $input['guideline'] ){
    if ( in_array ( $input['guideline'], $accessibility_guidelines ) ) {
      $new_input['guideline'] = $input['guideline'];
    } else {
      $new_input['guideline'] = $accessibility_guidelines[0];
    }
  }

  if ( $input['api_id'] ) {
    if ( true === is_idi_app_id_valid( $input['api_id'] ) ){
      $new_input['api_id'] = $input['api_id'];
    }
  }
  return $new_input;
}

/**
 * Check input to determine if string is valid
 *
 * @returns bool
 */

function is_idi_app_id_valid ( $api_id ) {

  $api_id = trim( $api_id );
  $validity = false;
  if ( ctype_alnum( $api_id ) && 40 === strlen( $api_id ) ) {
    $validity = true;
  }
  return $validity;
}

/**
 * Generate Admin dashboard
 *
 * @uses add_options_page()
 *
 * @return void
 */

function register_a1daccess_admin() {

  add_menu_page( 'A1D Accessibility', 'Accessibility', 'manage_options', 'a1d-accessibility', '\TenUp\A1D_WP_Accessibility\Core\a1daccess_dashboard', 'dashicons-universal-access', 61 );
}

function a1daccess_dashboard() {

  $admin_template = A1DACCESS_INC . 'templates/admin.php';
  include_once( $admin_template );
}

/**
 * Display meaningful messages to users about failed input or validation
 *
 * @uses manage_options()
 *
 */

function a1daccess_notifications() {

  $options = get_option( 'a1daccess_accessibility_options' );
  if ( false === is_idi_app_id_valid( $options['api_id'] ) ) {
    echo "<div class='error'><p>Your API ID is invalid. Please register at the following page <a href='http://achecker.ca/register.php'>http://achecker.ca/register.php</a> to retreive a valid API ID.</p></div>";
  }
}
