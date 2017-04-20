<?php
    //All code related to tinyMCE is stored here
    // This file is included from within ziggeo.php file

defined('ABSPATH') or die();

//To allow us to see if people want to have the TinyMCE button shown in the toolbar or not. If not, no need to show the same.
function ziggeo_check_mce() {

    $options = get_option('ziggeo_video'); 

    if( (isset($options['showVideoAidButton']) && $options['showVideoAidButton'] === '1') || !isset($options['showVideoAidButton']) ) {
        global $pagenow;

        //We must detect if we are editing or creating post / page due to bbPress bug.. (its been out for years, will probably not be fully fixed any time soon)
        if(in_array( $pagenow, array( 'post.php', 'post-new.php' ) )) {
            ///if it can not get it, it does not need to be shown..
            if(@include_once(ABSPATH . 'wp-includes/pluggable.php'))
            {
                //We need to check this as well otherwise the bbPress forum and topic form will not be shown, however this still does not allow it to work properly - does not work on edit..
                if(!isset($_GET['post_type']) && !(isset($_POST, $_POST['post_type']) && in_array($_POST['post_type'], array('forum', 'topic') ) ) ) {

                    //Additional check for bbPress to make it all work..
                    if(isset($_GET['post']) && in_array(get_post($_GET['post'])->post_type, array('forum', 'topic'))) {
                        return;
                    }

                    //If current user can edit the posts, then they should be able to add Ziggeo video..it does not matter if it is on public side or not..

                    //well due to the bbPress bug, this check causes issues otherwise it complains about bb_user not done right...so lets add another check..
                    //this one helps us post/save things when creating posts, otherwise if commented out or removed, it would not work. It still shows error in loading of the page to create a post :/
                    if( (isset($_GET) || count($_GET) > 0 ) && (!isset($_POST) || count($_POST) === 0 ) ) {
						if(current_user_can('edit_posts')) {
							//Registering our plugin in the list of TinyMCE list of external plugins
							add_filter('mce_external_plugins', 'ziggeo_mce_register');
							//Adding a button to the TinyMCE toolbar
							add_filter('mce_buttons', 'ziggeo_mce_add_button');
						}
					}
                }
            }
        }

    }
}
ziggeo_check_mce();

//We must register the URL to our plugin in TinyMCE, and we do that here
function ziggeo_mce_register($plugin_array) {
    //url to our plugin's js file handling tinyMCE through js code

    //This is a hack which allows us to see if we are using frontend or backend of WP..
    //Since the plugin is being called through GET request directly as a JS resource, it is not having the info or possible to ger the info through WP functions if we are on the backend or frontend, so using this approach we are actually registering the plugin with the URL fragment (The hash and its value). Now the reason why we are using this is because while registering the URL it is being cleaned up through WordPress code, so using ? would not work nor by adding array or &, etc. Now since WP will add mce parameter to the script, with the URL fragment, there will be none, so we just leave admin as it is since it is usually more sensitive to changes and set the frontend with the fragment. In turn in our ziggeo_tinymce_plugin.php file we detect the empty GET request for calls made to frontend and the one with the mce parameter as the one for backend, and based on that show correct details.
    //in same time if someone is calling the file directly, they would not see templates being shown (which previously would have been shown) - which is not any security measure since templates did not reveal anything either way.
    if(is_admin()) {
        $url = ZIGGEO_ROOT_URL . 'src/ziggeo_tinymce_plugin.php';
    }
    else {
        $url = ZIGGEO_ROOT_URL . 'src/ziggeo_tinymce_plugin.php#frontend=1';
    }

    $plugin_array['ziggeo'] = $url;

    return $plugin_array;
}

//Adding the button to the list of existing TinyMCE buttons
function ziggeo_mce_add_button($buttons) {

    array_push($buttons, 'separator', 'ziggeo_templates');

    return $buttons;
}

?>