# Integration of CiviCRM's Form Processor with Caldera Forms

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

# Reporting bugs

Bugs can be reported at [Github](https://github.com/CiviMRF/cf-civicrm-formprocessor/).

# Contributing

The code of this plugin is published and maintained at [Github](https://github.com/CiviMRF/cf-civicrm-formprocessor/).
The plugin is also published at [Wordpress.org](https://wordpress.org/plugins/cf-civicrm-formprocessor) 
and this requires that we submit each release to the [Wordpress SVN](https://plugins.svn.wordpress.org/cf-civicrm-formprocessor)

**Workflow for development**

1. Fork the repository at Github
1. Create a new branch for the functionality you want to develop, or for the bug you want to fix.
1. Write your code and test it, once you are finished push it to your fork.
1. Create a Pull Request at Github to notify us to merge your changes.

**Workflow for creating a release**

Based on the instruction from [Learn with Daniel](https://learnwithdaniel.com/2019/09/publishing-your-first-wordpress-plugin-with-git-and-svn/)

1. Update `readme.txt` with the new version number (also update the Changelog section)
1. Update `cf-civicrm-formprocessor` with the new version number
1. Create a new version at [Github](https://github.com/CiviMRF/cf-civicrm-formprocessor/).
1. To publish the release at Wordpress Plugin directory follow the following steps:
   1. Create a temp directory: `mkdir cf-civicrm-formprocessor-tmp`
   1. Go into this directory: `cd cf-civicrm-formprocessor-tmp`
   1. Do an SVN checkout into SVN directory: `svn checkout --depth immediates https://plugins.svn.wordpress.org/cf-civicrm-formprocessor svn`
   1. Clone the Github repository into Github directory: `git clone https://github.com/CiviMRF/cf-civicrm-formprocessor.git github`
   1. Go into the Github directory: `cd github`
   1. Checkout the created release (in our example 1.0.0): `git checkout 1.0.0`
   1. Go into the svn directory: `cd ../svn`
   1. Copie the files from github to SVN: `rsync -rc --exclude-from="../github/.distignore" "../github/" trunk/ --delete --delete-excluded`
   1. Add the files to SVN: `svn add . --force`
   1. Tag the release in SVN (in our example 1.0.0): `svn cp "trunk" "tags/1.0.0"`
   1. Now submit to the Wordpress SVN with a message: `svn ci -m 'Adding 1.0.0'`


# License

The plugin is licensed under [AGPL-3.0](LICENSE.txt).

