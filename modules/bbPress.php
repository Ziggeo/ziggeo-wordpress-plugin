<?php
// bbPress integration module

//Required for each module.
//This gives us the details needed to output the data to the Integrations tab and possibly do other things later on.
//If not present, we ignore the module as incomplete
function ZiggeoIntegration_bbPress_details() {
    return array(
        'author_name'       => 'Ziggeo', //author name
        'author_url'        => 'https://ziggeo.com/', //link to author website
        'requires_min'      => '1.15', //version of Ziggeo plugin required as minimum for this integration to work properly. (required)
        'requires_max'      => '', //not known to not work with some version
        'plugin_name'       => 'bbPress', //Name of the integration shown in Integrations tab (should be original plugin name as is)
        'plugin_url'        => 'https://bbpress.org/', //URL to the plugin to be downloaded from.
        'plugin_min'        => '2.5.10', //minimum version of the plugin this module integrates to (required) - bbPress version in this case.
        'plugin_max'        => '', //up to which version would module work upon which it should be disabled
        'screenshot_url'    => ZIGGEO_ROOT_URL . 'images/integrations/bbpress.png', //URL to the screenshot of the plugin this module integrates to, to show it in Integrations tab
        'firesOn'           => 'both' //Where does the plugin fires on? "admin", "public" or "both" - so that we only run plugin where it is needed.
    );
}

//Function to call to see if the main / base plugin is present or not.
function ZiggeoIntegration_bbPress_checkbase() {
    if ( is_plugin_active('bbpress/bbpress.php') ) {
        return true;
    }
    return false;
}

//Function that we call to activate integration. Best place for hooks to be added and executed from..
function ZiggeoIntegration_bbPress_run() {
//-    add_action( 'bbp_loaded', 'ziggeo_integration_bbPress_start', 5 ); //we do not need it for now..

    //add the button in the create topic form, right above the toolbar, as well as in replies to topics
    add_filter('bbp_after_get_the_content_parse_args', 'ziggeo_bbPress_add_button');

    //parse embeddings in forum
    add_filter('bbp_get_forum_content', 'ziggeo_content_filter');

    //parse embeddings in topic
    add_filter('bbp_get_topic_content', 'ziggeo_content_filter');

    //parse embeddings in replies
    add_filter('bbp_get_reply_content', 'ziggeo_content_filter');
}

//returns the version of the plugin that we integrate to.
function ZiggeoIntegration_bbPress_getVersion() {
    if(class_exists('bbPress')) {
        return bbp_get_version();
    }

    return 0;
}

//Hook got activated, so we can just run our code as bbPress should already be available for us.
function ziggeo_integration_bbPress_start() {

}

//Adds the record/discard/accept buttons above the toolbar when creating topic and reply
function ziggeo_bbPress_add_button($buttons) {
    //on admin side we already have the record button present for forum, topic and reply creation. As such we only need it on public side..
    if(is_admin()) { return $buttons; }

    //Lets get details of the current user to use them in tags
    $current_user = wp_get_current_user();

    //If a guest was to record a reply, we still want to add the comments tag, but it seems nice to indicate that that was done by some guest instead of not having that tag - which makes it look like it is missing.
    if(empty($current_user->user_login)) {
        $curent_username = 'guest';
    }
    else {
        $curent_username = $current_user->user_login;
    }

    ?>
    <div class="wp-media-buttons ziggeo-rem">
    </div>
    <br style="clear:both;" id="record_video">
    <script type="text/template" id="ziggeo-insert-button-template">
        <a href="#record_video" id="insert-ziggeo-button" class="button" title="Record Video">
            <span class="dashicons dashicons-video-alt"></span> Record Video
        </a>
        <a href="#record_video" id="revert-ziggeo-button" class="button" title="Discard Video" style="display: none">
            <span class="dashicons dashicons-editor-break"></span> Discard Video
        </a>
        <a href="#record_video" id="accept-ziggeo-button" class="button" title="Accept Video" style="display: none">
            <span class="dashicons dashicons-yes"></span> Accept Video
        </a>
        <div id="ziggeo-bbPress-video-recorder" style="display:none;"></div>
    </script>

    <script type="text/template" id="ziggeo-recorder-template">
        <div id="ziggeo-recorder">
            <ziggeo
                ziggeo-width=480
                ziggeo-height=360
                ziggeo-limit=240
                ziggeo-form_accept="#post"
                ziggeo-perms="allowupload"
                ziggeo-tags="wordpress,<?php echo $current_user->user_login; ?>,bbPress"
            ></ziggeo>
        </div>
    </script>

    <script>
        jQuery(document).on("ready", function () {
            if(jQuery(".wp-media-buttons").length > 1) {
                //remove the ones we added manually..as they will have .ziggeo-rem as well.
                jQuery(".wp-media-buttons.ziggeo-rem").remove();
            }
            jQuery(".wp-media-buttons").prepend(jQuery("#ziggeo-insert-button-template").html());
            var editor_elements = [".wp-editor-tabs", ".wp-editor-container", "#post-status-info tr", "#insert-media-button"];
            var video_mode = false;

            jQuery("#insert-ziggeo-button").on("click", function () {
                if (video_mode)     { return; }
                video_mode = true;
                jQuery("#content-html").click();
                for (var i = 0; i < editor_elements.length; ++i) {
                    jQuery(editor_elements[i]).css("display", "none");
                }
                jQuery("#revert-ziggeo-button").css("display", "");
                jQuery("#accept-ziggeo-button").css("display", "none");
                jQuery("#ziggeo-bbPress-video-recorder").css("display", "block");
                jQuery("#ziggeo-bbPress-video-recorder").append(jQuery("#ziggeo-recorder-template").html());
            });
            jQuery("#revert-ziggeo-button").on("click", function () {
                if (!video_mode)    { return; }
                video_mode = false;
                for (var i = 0; i < editor_elements.length; ++i) {
                    jQuery(editor_elements[i]).css("display", "");
                }
                jQuery("#revert-ziggeo-button").css("display", "none");
                jQuery("#accept-ziggeo-button").css("display", "none");
                jQuery("#ziggeo-bbPress-video-recorder").css("display", "none");
                jQuery("#ziggeo-recorder").remove();
            });
            jQuery("#accept-ziggeo-button").on("click", function () {
                if (!video_mode)    { return; }
                video_mode = false;
                for (var i = 0; i < editor_elements.length; ++i) {
                    jQuery(editor_elements[i]).css("display", "");
                }
                jQuery("#revert-ziggeo-button").css("display", "none");
                jQuery("#accept-ziggeo-button").css("display", "none");
                jQuery("#ziggeo-bbPress-video-recorder").css("display", "none");
                jQuery("#ziggeo-recorder").remove();
            });
            ZiggeoApi.Events.on("submitted", function (data) {
                //creating topic: #bbp_topic_content
                if(document.getElementById('bbp_topic_content')) {
                    var elem = '#bbp_topic_content';
                }
                //replying to topic: #bbp_reply_content
                else if(document.getElementById('bbp_reply_content')) {
                    var elem = '#bbp_reply_content';
                }

                jQuery( elem ).val(jQuery( elem ).val() + "\n[ziggeo video=" + data.video.token + "]\n\n");
                jQuery("#revert-ziggeo-button").css("display", "none");
                jQuery("#accept-ziggeo-button").css("display", "");
                jQuery("#ziggeo-bbPress-video-recorder").css("display", "none");
            });
        });
    </script>
    <?php

    return $buttons;
};

?>