<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

// All functions are Wordpress-specific.
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

class CiviCRM_Caldera_Forms_FormProcessor_LocalCiviCRM {

  public static function api($profile, $entity, $action, $params, $options = []) {
    if (empty($entity) || empty($action) || !is_array($params)) {
      throw new Exception('One of given parameters is empty.');
    }

    if (!civi_wp()->initialize()) {
      return ['error' => 'CiviCRM not Initialized', 'is_error' => '1'];
    }

    try {
      if (!empty($options)) {
        $params['options'] = $options;
      }
      $result = civicrm_api3($entity, $action, $params);
    } catch (CiviCRM_API3_Exception $e) {
      $error = $e->getMessage() . '<br><br><pre>' . $e->getTraceAsString() . '</pre>';
      return ['error' => $error, 'is_error' => '1'];
    }
    return $result;
  }

  /**
   * Load local CiviCRM Profile.
   * Only when CiviCRM is installed.
   *
   * @param $profiles
   *
   * @return array
   */
  public static function loadProfile($profiles) {
    if (function_exists('civi_wp')) {
      $profiles['_local_civi_'] = [
        'title' => __('Local CiviCRM'),
        'function' => ['CiviCRM_Caldera_Forms_FormProcessor_LocalCiviCRM', 'api']
      ];
    }
    return $profiles;
  }
}