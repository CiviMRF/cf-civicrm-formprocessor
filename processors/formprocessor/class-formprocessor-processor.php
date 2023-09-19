<?php

// All functions are Wordpress-specific.
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

require_once CF_CIVICRM_FORMPROCESSOR_INTEGRATION_PATH.'includes/class-formprocessor-loader.php';

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

  public static $isProcessingSubmittedData = false;

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
    $processor_config['post_processor'] = 'post_processor';
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
    $loader = CiviCRM_Caldera_Forms_FormProcessor_Loader::singleton();

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
      if (is_array($defaultValues)) {
        foreach ($defaultValues as $key => $value) {
          $fieldName = 'form_data_' . $key;
          $preset_name = $this->profile_name . '_' . $this->form_processor_name . '_' . $key;
          $slug      = str_replace('%', '', $processor['config'][$fieldName]);
          $field     = Caldera_Forms_Field_Util::get_field_by_slug($slug, $form);
          if (is_array($field) && isset($field['ID'])) {
            $fieldId = $field['ID'];
            if (isset($loader->options_meta[$preset_name]['multiple']) && $loader->options_meta[$preset_name]['multiple']) {
              $form['fields'][$fieldId]['config']['default'] = $value;
            } elseif (isset($loader->options[$preset_name])) {
              foreach ($loader->options[$preset_name] as $optionIdx => $option) {
                if ($option['value'] == $value) {
                  $form['fields'][$fieldId]['config']['default'] = $optionIdx;
                  break;
                }
              }
            } elseif (isset($field['config']['option'])) {
              $options = Caldera_Forms_Field_Util::find_option_values($form['fields'][$fieldId]);
              if (is_array($value)) {
                $form['fields'][$fieldId]['config']['default'] = [];
                foreach ($value as $v) {
                  $form['fields'][$fieldId]['config']['default'][] = array_search($v, $options);
                }
              } else {
                $form['fields'][$fieldId]['config']['default'] = array_search($value, $options);
              }
            } elseif (isset($form['fields'][$fieldId]['config'])) {
              $form['fields'][$fieldId]['config']['default'] = $value;
            }
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
    // Do nothing.
    return true;
  }

  public function post_processor(array $config, array $form, $processid) {
    global $transdata;
    $loader = CiviCRM_Caldera_Forms_FormProcessor_Loader::singleton();
    $this->set_data_object_initial($config, $form);
    $values = $this->get_submitted_value($config, $form);

    if (is_array($values)) {
      foreach ($values as $key => $value) {
        if (stripos($key, 'form_data_') === 0) {
          $preset_name = $this->profile_name . '_' . $this->form_processor_name . '_' . substr($key, 10);
          if (isset($loader->options_meta[$preset_name]) && $loader->options_meta[$preset_name]['multiple']) {
            if ($value !== null) {
              $value = explode(", ", $value);
            }
          }
          $params[substr($key, 10)] = $value;
        }
      }
    }

    $result = cf_civicrm_formprocessor_api_wrapper($this->profile_name,'FormProcessor', $this->form_processor_name, $params, [],false);
    if (isset($result['is_error']) && $result['is_error']) {
      cf_civicrm_formprocessor_log('Error from form processor');
      cf_civicrm_formprocessor_log($result);
      $transdata['error'] = true;
      if (!empty($config['error_message'])) {
        $transdata['note'] = $config['error_message'];
      } else {
        $transdata['note'] = __('Something went wrong.', 'cf-civicrm-formprocessor');
      }
      return $result;
    }
    // Remove null values from array otherwise we might get uggly errors.
    foreach($result as $key => $val) {
      if ($val === null) {
        unset($result[$key]);
      }
    }
    return $result;
  }

  /**
   * Get values from POST data and set in the value property
   *
   * @since 1.3.0
   *
   * @access protected
   *
   * @param $config
   * @param $form
   */
  protected function get_submitted_value( $config, $form ) {
    self::$isProcessingSubmittedData = true;
    $message_pattern = __( '%s is required', 'caldera-forms' );
    $default_args = array(
      'message' => false,
      'default' => false,
      'sanatize' => 'strip_tags',
      'magic' => true,
      'required' => true,
    );
    $values = array();
    $fields = $this->fields();
    foreach( $fields as $field  => $args ) {
      if ( ( 0 == $field || is_int( $field ) ) ) {
        if ( is_string( $args ) ) {
          $key = $field;
          $fields[ $field ] = $default_args;
          unset( $fields[ $field ] );
        }elseif ( 0 == $field || is_int( $field ) && is_array( $args ) &&isset( $args[ 'id' ]) ) {
          $key = $args[ 'id' ];
          $fields[ $key  ] = $args;
          unset( $fields[ $field ] );
        }else{
          unset( $fields[ $field ] );
          continue;
        }
      }else{
        $key = $field;
      }

      $fields[ $key ] = wp_parse_args( $args, $default_args );

      if ( isset( $config[ $key ] ) ) {
        $_field = Caldera_Forms_Field_Util::get_field_by_slug( str_replace( '%', '', $config[ $key ] ), $form );
      } else {
        $_field = null;
      }

      if ( is_array( $_field ) ) {
        $fields[ $key ][ 'config_field' ] = $_field[ 'ID' ];
      }else{
        $fields[ $key ][ 'config_field' ] = false;
      }
      if ( false === $fields[ $key][ 'message' ] ) {
        $fields[ $key ][ 'message' ] = sprintf( $message_pattern, $args[ 'label' ] );
      }

    }

    foreach ( $fields as $field => $args  ) {
      if ( isset( $config[ $field ]) ) {
        if ( $args[ 'magic' ] ) {
          $value = Caldera_Forms::do_magic_tags( $config[ $field ], null, $form );
        } else {
          $value = $config[ $field ];
        }

        if (is_string($value)) {
          $field_id_passed = strpos( $value, 'fld_' );
            if (FALSE !== $field_id_passed) {
            $value = Caldera_Forms::get_field_data( $value, $form );
          }
        }

      }else{
        $value = null;
      }

      if (!empty($value) && !is_array($value)) {
        $value = call_user_func( $args['sanatize'], $value );
      }

      /**
       * Filter value for field of processor
       *
       * @since 1.3.1
       *
       * @param mixed $value The value of the field.
       * @param string $field The name of the field.
       * @param array $args Config for this field.
       * @param array $config Processor config.
       * @param array $form Form config.
       */
      $value = apply_filters( 'caldera_forms_processor_value', $value, $field, $args, $config, $form );

      if ( ! empty( $value )  ) {
        $values[ $field ] = $value;

      }else{
        if ( $args[ 'required' ] ) {
          $this->data_object->add_error( $args[ 'message' ] );
        }else{
          $values[ $field ] = null;
        }
      }

    }
    self::$isProcessingSubmittedData = false;
    return $values;
  }


}
