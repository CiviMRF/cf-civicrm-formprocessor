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