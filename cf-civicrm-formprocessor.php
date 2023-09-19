<?php
/**
 * Plugin Name: Integration of CiviCRM's Form Processor with Caldera Forms
 * Description: This plugin integrates Caldera Forms with CiviCRM's form processor. Funded by CiviCooP, Civiservice.de, Bundesverband Soziokultur e.V., Article 19
 * Version: 1.0.1
 * Author: Jaap Jansma
 * Plugin URI: https://github.com/civimrf/cf-civicrm-formprocessor
 * GitHub Plugin URI: civimrf/cf-civicrm-formprocessor
 * Text Domain: cf-civicrm-formprocessor
 * Domain Path: /languages
 * License: AGPL-3.0
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

// All functions are Wordpress-specific.
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Define constants.
 *
 * @since 0.1
 */
define( 'CF_CIVICRM_FORMPROCESSOR_INTEGRATION_VER', '1.0.0' );
define( 'CF_CIVICRM_FORMPROCESSOR_INTEGRATION_URL', plugin_dir_url( __FILE__ ) );
define( 'CF_CIVICRM_FORMPROCESSOR_INTEGRATION_PATH', plugin_dir_path( __FILE__ ) );

/**
 * Wrapper function for the CiviCRM api's.
 * We use profiles to connect to different remote CiviCRM.
 *
 * @param $profile
 * @param $entity
 * @param $action
 * @param $params
 * @param array $options
 * @param bool $ignore
 *
 * @return array|mixed|null
 */
function cf_civicrm_formprocessor_api_wrapper($profile, $entity, $action, $params, $options=[], $ignore=false) {
  $profiles = cf_civicrm_formprocessor_get_profiles();
  if (isset($profiles[$profile])) {
    if (isset($profiles[$profile]['file'])) {
      require_once($profiles[$profile]['file']);
    }
    $result = call_user_func($profiles[$profile]['function'], $profile, $entity, $action, $params, $options);
  } else {
    $result = ['error' => 'Profile not found', 'is_error' => 1];
  }
  if (!empty($result['is_error']) && $ignore) {
    return null;
  }
  return $result;
}

/**
 * Returns a list of possible profiles
 * @return array
 */
function cf_civicrm_formprocessor_get_profiles() {
  static $profiles = null;
  if (is_array($profiles)) {
    return $profiles;
  }

  $profiles = array();
  require_once(CF_CIVICRM_FORMPROCESSOR_INTEGRATION_PATH.'includes/class-local-civicrm.php');
  $profiles = CiviCRM_Caldera_Forms_FormProcessor_LocalCiviCRM::loadProfile($profiles);

  if (function_exists('wpcmrf_get_core')) {
    $core = wpcmrf_get_core();
    $wpcmrf_profiles = $core->getConnectionProfiles();
    foreach($wpcmrf_profiles as $profile) {
      $profile_name = 'wpcmrf_profile_'.$profile['id'];
      $profiles[$profile_name] = [
        'title' => $profile['label'],
        'function' => 'cf_civicrm_formprocessor_wpcmrf_api',
      ];
    }
  }

  $profiles = apply_filters('cf_civicrm_formprocessor_get_profiles', $profiles);
  return $profiles;
}

add_action( 'caldera_forms_pre_load_processors', function(){
  require_once CF_CIVICRM_FORMPROCESSOR_INTEGRATION_PATH.'includes/class-formprocessor-loader.php';
  $loader = CiviCRM_Caldera_Forms_FormProcessor_Loader::singleton();
  $loader->loadAll();
});

add_filter( 'caldera_forms_render_get_form', 'cf_civicrm_formprocessor_get_form');
add_action( 'caldera_forms_autopopulate_types', 'cf_civicrm_formprocessor_fields_types');
add_filter( 'caldera_forms_render_get_field',  'cf_civicrm_formprocessor_fields_values', 20, 2 );
add_filter( 'caldera_forms_field_option_presets', 'cf_civicrm_formprocessor_options_presets');

/**
 * Pre render a caldera form.
 * Load data from CiviCRM when a CiviCRM Form Processor is enabled.
 *
 * @param array $form Form config
 */
function cf_civicrm_formprocessor_get_form($form) {
  if (empty($form['processors'])) {
    return $form;
  }
  require_once CF_CIVICRM_FORMPROCESSOR_INTEGRATION_PATH.'includes/class-formprocessor-loader.php';

  $processors = $form['processors'];
  foreach($processors as $processor) {
    if (strpos($processor['type'], CiviCRM_Caldera_Forms_FormProcessor_Loader::SLUG_PREFIX) === 0) {
      $loader = CiviCRM_Caldera_Forms_FormProcessor_Loader::singleton();
      $class = $loader->loadBySlug($processor['type']);
      if ($class) {
        $form = $class->get_form($form, $processor);
      }
    }
  }
  return $form;
}

function cf_civicrm_formprocessor_options_presets($presets) {
  require_once CF_CIVICRM_FORMPROCESSOR_INTEGRATION_PATH.'includes/class-formprocessor-loader.php';
  $loader = CiviCRM_Caldera_Forms_FormProcessor_Loader::singleton();
  $presets = array_merge($presets, $loader->presets);
  return $presets;
}

function cf_civicrm_formprocessor_fields_types() {
  require_once CF_CIVICRM_FORMPROCESSOR_INTEGRATION_PATH.'includes/class-formprocessor-loader.php';
  $loader = CiviCRM_Caldera_Forms_FormProcessor_Loader::singleton();
  foreach($loader->presets as $presetName => $preset) {
    echo "<option value=\"{$presetName}\"{{#is auto_type value=\"{$presetName}\"}} selected=\"selected\"{{/is}}>{$preset['name']}</option>";
  }
}

function cf_civicrm_formprocessor_fields_values($field, $form) {
  if (!empty( $field['config']['auto'])) {
    require_once CF_CIVICRM_FORMPROCESSOR_INTEGRATION_PATH.'includes/class-formprocessor-loader.php';
    $loader = CiviCRM_Caldera_Forms_FormProcessor_Loader::singleton();
    if ($field['config']['auto_type'] && !isset($loader->options[$field['config']['auto_type']])) {
      $loader->loadOptionsByPresentName($field['config']['auto_type']);
    }
    if ($field['config']['auto_type'] && isset($loader->options[$field['config']['auto_type']])) {
      $field['config']['option'] = $loader->options[$field['config']['auto_type']];
    }
  }
  return $field;
}

function cf_civicrm_formprocessor_wpcmrf_api($profile, $entity, $action, $params, $options = []) {
  $profile_id = substr($profile, 15);
  $call = wpcmrf_api($entity, $action, $params, $options, $profile_id);
  return $call->getReply();
}

/**
 * Sanitize function which does not do anything.
 *
 * @param $value
 * @return mixed
 */
function cf_civicrm_formprocessor_santize($value) {
  return $value;
}

function cf_civicrm_formprocessor_log($message) {
  if (WP_DEBUG) {
    if (is_array($message) || is_object($message)) {
      error_log(print_r($message, true));
    } else {
      error_log($message);
    }
  }
}

/**
 * This filter is used to add the uploaded files when processing magic tags.
 * This is only needed when the form is submitted to civicrm and is needed because
 * Caldera will only add the files when they are uploaded to the media library.
 */
add_filter('caldera_forms_do_field_magic_value', function($value, $matches, $entry_id, $form ) {
  require_once(CF_CIVICRM_FORMPROCESSOR_INTEGRATION_PATH . 'processors/formprocessor/class-formprocessor-processor.php');
  if (CiviCRM_Caldera_Forms_FormProcessor_Processor::$isProcessingSubmittedData) {
    foreach ($matches[1] as $key => $tag) {
      // check for parts
      $part_tags = explode(':', $tag);
      if (!empty($part_tags[1])) {
        $tag = $part_tags[0];
      }
      $entry = Caldera_Forms::get_slug_data($tag, $form);
      $field = Caldera_Forms_Field_Util::get_field_by_slug($tag, $form);

      if (Caldera_Forms_Field_Util::is_file_field($field, $form)) {
        $value = $entry;
        if (is_array($value)) {
          $value = reset($value);
        }
      }
    }
  }
  return $value;
}, 20, 4);