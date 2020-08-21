<?php echo Caldera_Forms_Processor_UI::config_fields($config['form_data_fields']); ?>

<h2><?php echo esc_html_e( 'Retrieve defaults', 'cf-civicrm-formprocessor' ); ?></h2>
<p class="description">
  <?php echo esc_html_e('Use the retrieval of defaults for filling a form with existing data.', 'cf-civicrm-formprocessor'); ?>
</p>
<?php echo Caldera_Forms_Processor_UI::config_field([
  'id' => 'enable_default',
  'label' => __('Enable default retrieval'),
  'type' => 'checkbox',
  'magic' => false,
]); ?>

<div class="formprocessor_default_data">
<?php echo Caldera_Forms_Processor_UI::config_fields($config['default_data_fields']); ?>
</div>

<h2><?php echo esc_html_e( 'Error message', 'cf-civicrm-formprocessor' ); ?></h2>
<p class="description">
  <?php echo esc_html_e('When something goes wrong with this processor show this error message to the user. Leave empty to show a default message.', 'cf-civicrm-formprocessor'); ?>
</p>
<?php echo Caldera_Forms_Processor_UI::config_field([
  'id' => 'error_message',
  'label' => __('Error message'),
  'type' => 'text',
  'magic' => false,
]); ?>

<script>
jQuery(document).ready(function($) {
  $('#enable_default').on('change', function() {
    if ($(this).prop('checked') == true){
      $('.formprocessor_default_data').show();
    } else {
      $('.formprocessor_default_data').hide();
    }
  });
  $('#enable_default').trigger('change');
});
</script>