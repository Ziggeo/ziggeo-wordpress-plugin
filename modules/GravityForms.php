<?php
// Gravity forms module that could be loaded and used as Ziggeo integration.
// Building integration means following few things:
// All functions have the following format: {prefix}_{name}_{function}
//      {prefix} is always "ZiggeoIntegration_"
//      Name of the plugin is the same as filename, not as 'plugin_name' parameter. So if file is GravityForms.php, the {name} here would be "GravityForms"
//      {sufix} is the name of the function to call
// Required functions:
//      details() - to retrieve all details about the integration module and is required.
//      checkbase() - all code that is needed to check if the main plugin that we are integrating to is present and if needed to check version, etc.
//      run() - called when needed per details() function and this should be the starting point of all code. Used to register hooks, etc.
//      getVersion() - called when needed to get the version of the plugin that the integration integrates to - in this case version of Gravity Forms.
//
// It is good to know 2 things, v 1.14 is the first version of Ziggeo plugin that supports integrations and that if possible, _min values should be added for the validations to be made correctly if it can not work on lower versions.

//Required for each module.
//This gives us the details needed to output the data to the Integrations tab and possibly do other things later on.
//If not present, we ignore the module as incomplete
function ZiggeoIntegration_GravityForms_details() {
    return array(
        'author_name'       => 'Ziggeo', //author name
        'author_url'        => 'https://ziggeo.com/', //link to author website
        'requires_min'      => '1.15', //version of Ziggeo plugin required as minimum for this integration to work properly. (required)
        'requires_max'      => '', //not known to not work with some version
        'plugin_name'       => 'Gravity Forms', //Name of the integration shown in Integrations tab (should be original plugin name as is)
        'plugin_url'        => 'http://www.gravityforms.com/', //URL to the plugin to be downloaded from.
        'plugin_min'        => '2.0', //minimum version of the plugin this module integrates to (required) - GravityForms version in this case.
        'plugin_max'        => '', //up to which version would module work upon which it should be disabled
        'screenshot_url'    => ZIGGEO_ROOT_URL . 'images/integrations/gravityforms.png', //URL to the screenshot of the plugin this module integrates to, to show it in Integrations tab
        'firesOn'           => 'both' //Where does the plugin fires on? "admin", "public" or "both" - so that we only run plugin where it is needed.
    );
}

//Function to call to see if the main / base plugin is present or not.
function ZiggeoIntegration_GravityForms_checkbase() {
    //since Gravity Forms integration should only run in the back, we can check out if it exists using the following
    if ( is_plugin_active('gravityforms/gravityforms.php') ) {
        return true;
    }
    return false;
}

//Function that we call to activate integration. Best place for hooks to be added and executed from..
function ZiggeoIntegration_GravityForms_run() {
    add_action( 'gform_loaded', array( 'ziggeo_integration_gravityforms_start', 'load' ), 5 );
}

//returns the version of the plugin that we integrate to.
function ZiggeoIntegration_GravityForms_getVersion() {
    if(class_exists('GFForms')) {
        return GFForms::$version;
    }

    return 0;
}

class ziggeo_integration_gravityforms_start {

    public static function load() {

        if( !method_exists('GFForms', 'include_addon_framework') ) {
            return;
        }

        require_once('GravityForms_class.php');

        GFAddOn::register( 'ZiggeoIntegrationGravityFormsClass' );
    }

}

?>