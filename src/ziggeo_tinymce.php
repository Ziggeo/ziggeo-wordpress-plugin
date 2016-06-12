<?php
    //All code related to tinyMCE is stored here
    // This file is included from within ziggeo.php file

defined('ABSPATH') or die();

//To allow us to see if people want to have the TinyMCE button shown in the toolbar or not. If not, no need to show the same.
function ziggeo_check_mce() {

    $options = get_option('ziggeo_video'); 

    if( (isset($options['showVideoAidButton']) && $options['showVideoAidButton'] === '1') || !isset($options['showVideoAidButton']) ) {

        ///if it can not get it, it does not need to be shown..
        if(@include_once(ABSPATH . 'wp-includes/pluggable.php'))
        {
            //If current user can edit the posts, then they should be able to add Ziggeo video..it does not matter if it is on public side or not..
            if(current_user_can('edit_posts')) {
                //Registering our plugin in the list of TinyMCE list of external plugins
                add_filter('mce_external_plugins', 'ziggeo_mce_register');
                //Adding a button to the TinyMCE toolbar
                add_filter('mce_buttons', 'ziggeo_mce_add_button');        
            }
        }
    }
}
ziggeo_check_mce();

//We must register the URL to our plugin in TinyMCE, and we do that here
function ziggeo_mce_register($plugin_array) {
    //url to our plugin's js file handling tinyMCE through js code
    $url = ZIGGEO_ROOT_URL . 'src/ziggeo_tinymce_plugin.php';

    $plugin_array['ziggeo'] = $url;

    return $plugin_array;
}

//Adding the button to the list of existing TinyMCE buttons
function ziggeo_mce_add_button($buttons) {
    array_push($buttons, 'separator', 'ziggeo_templates');

    return $buttons;
}

?>