# Developer Documentation

This plugin provides the following filter `cf_civicrm_formprocessor_get_profiles` with this filter it is possible to 
add additional _api profile_.

An api profile consists of a name, a title and a callback function. 
This plugin provides a default api callback for when CiviCRM is installed in the same wordpress environment.

With this filter we can later provide additional api callbacks for example for a remote CiviCRM.