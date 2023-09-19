=== Integration of CiviCRM's Form Processor with Caldera Forms ===
Contributors: jaapjansma
Donate link: https://github.com/CiviMRF/cf-civicrm-formprocessor/
Tags: CiviCRM, form, contact form, forms
Requires at least: 5.2
Tested up to: 5.6
Requires PHP: 7.2
Stable tag: 1.0.1
License: AGPL-3.0

This plugin integrates Caldera Forms with CiviCRM's form processor. Funded by CiviCooP, Civiservice.de, Bundesverband Soziokultur e.V., Article 19

== Description ==

This plugin makes it possible to submit caldera forms to [CiviCRM's Form Processor](https://lab.civicrm.org/extensions/form-processor/).
CiviCRM does not necessarily be installed in the same installation.
If it isn't use the [CiviCRM McRestFace Connector plugin(https://github.com/CiviMRF/wpcmrf) to connect to a remote CiviCRM.

**Configuration when CiviCRM is on a remote server**

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

**Configuration when CiviCRM is installed locally**

Use this when the frontend WordPress site and CiviCRM are in the same wordpress installation:

1. Install and activate:
   - [Caldera Forms](https://wordpress.org/plugins/caldera-forms/) - WP plugin
   - [CiviCRM Form Processor](https://lab.civicrm.org/extensions/form-processor/) - CiviCRM extension
   - [Caldera Forms integration with CiviCRM Form Processor](https://github.com/civimrf/cf-civicrm-formprocessor)  - this WP plugin
1. Create a CiviCRM Form Processor
1. Create a Caldera Form
1. On the Processors tab, click `Add Processor`
1. For each Form Processor you created, you should see a processor named the same as the Form Processor prefixed by 'CiviCRM Form Processor'

NB - If you have not created any Form Processors, no CiviCRM Caldera Processors will be listed!

**Funded by**

* [CiviCooP](https://www.civicoop.org)
* [Civiservice.de GmbH](https://civiservice.de/)
* [Bundesverband Soziokultur e.V.](https://www.soziokultur.de/)
* [Article 19](https://www.article19.org/)


== Changelog ==

1.0.1: Fixed issue with advanced file uploader
1.0.0: First version.