<?php

// All functions are Wordpress-specific.
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * CiviCRM Caldera Forms Form Processor Processor Class.
 *
 * This is a boilerplate for creating new processors.
 *
 * @see https://github.com/Desertsnowman/cf-formprocessor-boilerplate
 *
 * @since 0.2
 */
class CiviCRM_Caldera_Forms_FormProcessor_Processor extends Caldera_Forms_Processor_Processor {

  public $form_processor_name;

  public $profile_name;

  /**
   * Construct object for processing object
   *
   * @param array $processor_config Processor configuration
   * @param array $fields Field config
   * @param string $slug Processor slug
   *
   * @since 1.3.5.3
   *
   */
  public function __construct(array $processor_config, array $fields, $slug) {
    $this->form_processor_name = $processor_config['form_processor_name'];
    $this->profile_name = $processor_config['profile_name'];
    parent::__construct($processor_config, $fields, $slug);
  }

  /**
   * Pre render a caldera form.
   * Load data from CiviCRM when a CiviCRM Form Processor is enabled.
   *
   * @param array $form Form config
   * @param array $processor
   */
  public function get_form($form, $processor) {
    if ($processor['type'] != $this->slug) {
      return $form;
    }
    if (isset($processor['config']['enable_default']) && $processor['config']['enable_default'] == 'on') {
      // Load default data;
      $config = [];
      foreach($processor['config'] as $key => $value) {
        if (stripos($key, 'default_data_') === 0) {
          $config[$key] = $value;
        }
      }
      $this->set_data_object_initial($config, $form);
      $defaultParams = [];
      foreach($this->data_object->get_values() as $key => $value) {
        if (stripos($key, 'default_data_') === 0) {
          $defaultParams[substr($key, 13)] = $value;
        }
      }
      $defaultValues = cf_civicrm_formprocessor_api_wrapper($this->profile_name,'FormProcessorDefaults', $this->form_processor_name, $defaultParams, [], true);
      foreach($defaultValues as $key => $value) {
        $fieldName = 'form_data_'.$key;
        $slug = str_replace( '%', '', $processor['config'][$fieldName]);
        $field = Caldera_Forms_Field_Util::get_field_by_slug($slug, $form);
        if (is_array($field) && isset($field['ID'])) {
          $fieldId = $field['ID'];
          if (isset($form['fields'][$fieldId]['config'])) {
            echo "set value";
            $form['fields'][$fieldId]['config']['default'] = $value;
          }
        }
      }
    }
    return $form;
  }

  /**
   * Validate the process if possible, and if not return errors.
   *
   * @param array $config Processor config
   * @param array $form Form config
   * @param string $proccesid Unique ID for this instance of the processor
   *
   * @return array Return if errors, do not return if not
   * @since 1.3.5.3
   *
   */
  public function pre_processor(array $config, array $form, $proccesid) {
    // Do nothing.
  }

  public function processor(array $config, array $form, $proccesid) {
    $this->set_data_object_initial($config, $form);
    $params = [];
    foreach($this->data_object->get_values() as $key => $value) {
      if (stripos($key, 'form_data_') === 0) {
        $params[substr($key, 10)] = $value;
      }
    }
    cf_civicrm_formprocessor_api_wrapper($this->profile_name,'FormProcessor', $this->form_processor_name, $params, [],false);
  }


}
