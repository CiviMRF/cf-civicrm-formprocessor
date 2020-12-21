# Caldera Forms integration with CiviCRMs Form Processor

This plugins adds the ability to process Caldera Forms by a [CiviCRM Form Processor](lab.civicrm.org/extensions/form-processor/).

This extension can connect to a local CiviCRM or to a remote CiviCRM through the Wordpress [CiviMcRestFace Connection Plugin](https://github.com/CiviMRF/wpcmrf)


## Local configuration

Use this when the frontend WordPress site and CiviCRM are on the same server:

1. Install and activate:
   - [Caldera Forms](https://wordpress.org/plugins/caldera-forms/) - WP plugin
   - [CiviCRM Form Processor](https://lab.civicrm.org/extensions/form-processor/) - CiviCRM extension
   - [Caldera Forms integration with CiviCRM Form Processor](https://github.com/civimrf/cf-civicrm-formprocessor)  - this WP plugin
1. Create a CiviCRM Form Processor
1. Create a Caldera Form
1. On the Processors tab, click `Add Processor`
1. For each Form Processor you created, you should see a processor named the same as the Form Processor prefixed by 'CiviCRM Form Processor'

NB - If you have not created any Form Processors, no CiviCRM Caldera Processors will be listed!

## Remote configuration

Use this when the front-end WordPress site and CiviCRM are on different servers:

1. On the front-end site, install and activate:
   - [Caldera Forms](https://wordpress.org/plugins/caldera-forms/) - WP plugin
   - [Caldera Forms integration with CiviCRM Form Processor](https://github.com/civimrf/cf-civicrm-formprocessor) - this WP plugin
   - [CiviMcRestFace Connection Plugin](https://github.com/CiviMRF/wpcmrf) - WP plugin
1. On the front-end site, configure a CMRF connection to connect the front-end to the back-end
1. On the back-end server, install and enable:
   - [CiviCRM Form Processor](https://lab.civicrm.org/extensions/form-processor/) - CiviCRM extension
1. On the back-end server, create a CiviCRM Form Processor
1. On the front-end server, create a Caldera Form
1. On the Procssors tab, click `Add Processor`
1. For each Form Processor you created, you should see a processor named the same as the Form Processor prefixed by 'CiviCRM Form Processor' and suffixed by 'at <name_of_CMRF_connection>'

