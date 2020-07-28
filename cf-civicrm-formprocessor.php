<?php
/**
 * Plugin Name: Caldera Forms integration with CiviCRM Form Procesor
 * Description: CiviCRMs Form Processor integration for Caldera Forms.
 * Version: 1.0.0
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
    foreach($loader->options as $presetName => $options) {
      if ($field['config']['auto_type'] == $presetName) {
        $field['config']['option'] = $options;
      }
    }
  }
  return $field;
}

function cf_civicrm_formprocessor_wpcmrf_api($profile, $entity, $action, $params, $options = []) {
  $profile_id = substr($profile, 15);
  $call = wpcmrf_api($entity, $action, $params, $options, $profile_id);
  return $call->getReply();
}