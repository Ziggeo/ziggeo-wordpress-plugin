<?php
// bbPressEnableTinyMCEVisualTab integration module

//Required for each module.
//This gives us the details needed to output the data to the Integrations tab and possibly do other things later on.
//If not present, we ignore the module as incomplete
function ZiggeoIntegration_bbPressEnableTinyMCEVisualTab_details() {
    return array(
        'author_name'       => 'Ziggeo', //author name
        'author_url'        => 'https://ziggeo.com/', //link to author website
        'requires_min'      => '1.15', //version of Ziggeo plugin required as minimum for this integration to work properly. (required)
        'requires_max'      => '', //not known to not work with some version
        'plugin_name'       => 'bbPress Enable TinyMCE Visual Tab', //Name of the integration shown in Integrations tab (should be original plugin name as is)
        'plugin_url'        => 'http://wordpress.org/extend/plugins/bbpress-enable-tinymce-visual-tab/', //URL to the plugin to be downloaded from.
        'plugin_min'        => '1.0.1', //minimum version of the plugin this module integrates to (required) - bbPress version in this case.
        'plugin_max'        => '', //up to which version would module work upon which it should be disabled
        'screenshot_url'    => ZIGGEO_ROOT_URL . 'images/integrations/bbpress.png', //URL to the screenshot of the plugin this module integrates to, to show it in Integrations tab
        'firesOn'           => 'public' //Where does the plugin fires on? "admin", "public" or "both" - so that we only run plugin where it is needed.
    );
}

//Function to call to see if the main / base plugin is present or not.
function ZiggeoIntegration_bbPressEnableTinyMCEVisualTab_checkbase() {
    //since Gravity Forms integration should only run in the back, we can check out if it exists using the following
    if ( is_plugin_active('bbpress-enable-tinymce-visual-tab/init.php') ) {
        return true;
    }
    return false;
}

//Function that we call to activate integration. Best place for hooks to be added and executed from..
function ZiggeoIntegration_bbPressEnableTinyMCEVisualTab_run() {

    //We need to register our plugin for TinyMCE plugin
    add_filter('mce_external_plugins', 'ziggeo_mce_register');

    //Adding a button to the TinyMCE toolbar
    add_filter('mce_buttons', 'ziggeo_mce_add_button');

    //We also need to add our button to 
//    $buttons = bbp_get_teeny_mce_buttons();
//    array_push($buttons, 'ziggeo_templates', 'separator');

//    echo 'iinfo';
//    add_filter('bbp_get_tiny_mce_plugins', 'ziggeo_mce_register');
//    var_dump(apply_filters( 'bbp_get_teeny_mce_buttons', array_reverse($buttons), 1 ));
//
    add_filter( "teeny_mce_buttons", 'ziggeo_test');

    add_filter( 'bbp_after_get_the_content_parse_args', 'ziggeo_test2');
}
function ziggeo_test2($args) {
//    var_dump($args);
}

function ziggeo_test($buttons) {
    $button2 = bbp_get_teeny_mce_buttons();

    array_push($button2, 'separator', 'ziggeo_templates');

    $buttons = array_merge($buttons, $button2);

apply_filters( 'bbp_get_teeny_mce_buttons', $buttons );


    return $buttons;
}

//returns the version of the plugin that we integrate to.
function ZiggeoIntegration_bbPressEnableTinyMCEVisualTab_getVersion() {
    if(class_exists('ja_bbp_tinymce_visual_tab')) {
        return '1.0.1'; //hardcoded since there is no function to get it from the plugin itself
    }

    return 0;
}

?>