<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

// All functions are Wordpress-specific.
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

class CiviCRM_Caldera_Forms_FormProcessor_Loader {

  const SLUG_PREFIX = 'cf_civicrm_form_processor_';

  /**
   * @var CiviCRM_Caldera_Forms_FormProcessor_Loader
   */
  public static $instance;

  /**
   * @var CiviCRM_Caldera_Forms_FormProcessor_Processor[]
   */
  public $processors = [];

  public $presets = [];

  public $options = [];

  public $options_meta = [];

  /**
   * Load all form processors
   *
   * @throws \Exception
   */
  public function loadAll() {
    $profiles = cf_civicrm_formprocessor_get_profiles();
    foreach($profiles as $profile => $profile_data) {
      $result = cf_civicrm_formprocessor_api_wrapper($profile,'FormProcessorInstance', 'list', [], ['cache' => '180 minutes']);
      if (isset($result['values'] )) {
        foreach ($result['values'] as $value) {
          $this->load($profile, $value['name'], $value['title']);
        }
      }
    }
  }

  public function loadOptionsByPresentName($presetName) {
    $profiles = cf_civicrm_formprocessor_get_profiles();
    foreach($profiles as $profile => $profile_data) {
      if (stripos($presetName, $profile) === 0) {
        $result = cf_civicrm_formprocessor_api_wrapper($profile,'FormProcessorInstance', 'list', [], ['cache' => '180 minutes']);
        if (isset($result['values'] )) {
          foreach ($result['values'] as $value) {
            if (stripos($presetName, $profile.'_'.$value['name']) === 0) {
              $this->loadBySlug(self::SLUG_PREFIX.$profile. '_'.$value['name']);
              break;
            }
          }
        }
      }
    }
  }

  /**
   * Load a form processor by its slug.
   *
   * @param $slug
   *
   * @return CiviCRM_Caldera_Forms_FormProcessor_Processor|null
   * @throws \Exception
   */
  public function loadBySlug($slug) {
    if (isset($this->processors[$slug])) {
      return $this->processors[$slug];
    }

    $profiles = cf_civicrm_formprocessor_get_profiles();
    foreach($profiles as $profile => $profile_data) {
      if (strpos($slug, self::SLUG_PREFIX.$profile.'_') === 0) {
        $name = substr($slug, strlen(self::SLUG_PREFIX.$profile. '_'));
        $result = cf_civicrm_formprocessor_api_wrapper($profile,'FormProcessorInstance', 'list', ['name' => $name], ['cache' => '180 minutes']);
        if (isset($result['values'] )) {
          foreach ($result['values'] as $value) {
            return $this->load($profile, $value['name'], $value['title']);
          }
        }
      }
    }
    return null;
  }

  /**
   * Load a particular form processor.
   *
   * @param $profile
   * @param $name
   * @param $title
   *
   * @return CiviCRM_Caldera_Forms_FormProcessor_Processor
   * @throws \Exception
   */
  public function load($profile, $name, $title) {
    $slug = 'cf_civicrm_form_processor_'.$profile.'_'.$name;
    if (isset($this->processors[$slug])) {
      return $this->processors[$slug];
    }

    $profiles = cf_civicrm_formprocessor_get_profiles();
    $profileTitle = $profiles[$profile]['title'];

    $config['form_processor_name'] = $name;
    $config['profile_name'] = $profile;
    $config['name'] = sprintf(__( 'CiviCRM Form Processor %1$s at %2$s', 'cf-civicrm-formprocessor' ), $title, $profileTitle);
    $config['description'] = sprintf(__( 'Submit to CiviCRM Form Processor %1$s', 'cf-civicrm-formprocessor' ), $title);
    $fields = [];
    $fieldsApi = cf_civicrm_formprocessor_api_wrapper($profile, 'FormProcessor', 'getfields', ['api_action' => $name], ['limit' => 0, 'cache' => '180 minutes']);
    $idx = 0;
    foreach($fieldsApi['values'] as $field) {
      $fields[$idx] = [
        'id' => 'form_data_'.$field['name'],
        'label' => $field['title'],
        'type' => 'text',
        'required' => !empty($field['api.required']),
      ];
      if ($field['type'] == 2 || $field['type'] == 32) {
        // String = 2
        // Text = 32
        // Long Text = 32
        $fields[$idx]['sanatize'] = 'cf_civicrm_formprocessor_santize';
      }
      if (isset($field['options']) && is_array($field['options'])) {
        $options = [];
        $data = "";
        foreach($field['options'] as $k=>$v) {
          $data .= $k."|".$v."\n";
          $options[$k] = [
            'value' => $k,
            'label' => $v,
          ];
        }
        $presetName = $profile.'_'.$name.'_'.$field['name'];
        $this->presets[$presetName]['name'] = sprintf(__( '%1$s from %2$s (%3$s)', 'cf-civicrm-formprocessor' ), $field['title'], $title, $profileTitle);
        $this->presets[$presetName]['data'] = $data;
        $this->options_meta[$presetName]['multiple'] = isset($field['formprocessor.is_multiple']) && $field['formprocessor.is_multiple'] ? TRUE : false;
        $this->options[$presetName] = $options;
      }
      $idx++;
    }
    $defaultFieldsApi = cf_civicrm_formprocessor_api_wrapper($profile,'FormProcessorDefaults', 'getfields', ['api_action' => $name], ['limit' => 0, 'cache' => '180 minutes']);
    $defaultFields = [];
    foreach($defaultFieldsApi['values'] as $field) {
      $defaultFields[] = [
        'id' => 'default_data_'.$field['name'],
        'label' => $field['title'],
        'type' => 'text',
        'required' => !empty($field['api.required']),
      ];
    }
    $config['form_data_fields'] = $fields;
    $config['default_data_fields'] = $defaultFields;
    $config['template'] = CF_CIVICRM_FORMPROCESSOR_INTEGRATION_PATH.'/templates/processor.php';
    $config['magic_tags'] = ['*'];
    $allFields = array_merge($fields, $defaultFields);

    require_once(CF_CIVICRM_FORMPROCESSOR_INTEGRATION_PATH . 'processors/formprocessor/class-formprocessor-processor.php');
    $this->processors[$slug] = new CiviCRM_Caldera_Forms_FormProcessor_Processor($config, $allFields, $slug);
    return $this->processors[$slug];
  }

  /**
   * @return CiviCRM_Caldera_Forms_FormProcessor_Loader
   */
  public static function singleton() {
    if (!self::$instance) {
      self::$instance = new CiviCRM_Caldera_Forms_FormProcessor_Loader();
    }
    return self::$instance;
  }

  private function __construct() {

  }

}