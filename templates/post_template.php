<?php
//Checking if WP is running or if this is a direct call..
defined('ABSPATH') or die();

    global $wp_version;

    //Since 
    if( version_compare( $wp_version, '4.5') >= 0 ) {
        $current_user = wp_get_current_user();
    }
    else {
        global $current_user;
        get_currentuserinfo();        
    }
?>

<script type="text/template" id="ziggeo-insert-button-template">
    <a href="#" id="insert-ziggeo-button" class="button" title="Record Video">
        <span class="dashicons dashicons-video-alt"></span> Record Video
    </a>
    <a href="#" id="revert-ziggeo-button" class="button" title="Discard Video" style="display: none">
        <span class="dashicons dashicons-editor-break"></span> Discard Video
    </a>
    <a href="#" id="accept-ziggeo-button" class="button" title="Accept Video" style="display: none">
        <span class="dashicons dashicons-yes"></span> Accept Video
    </a>
</script>

<script type="text/template" id="ziggeo-recorder-template">
    <div id="ziggeo-recorder">
        <ziggeo
            ziggeo-width=480
            ziggeo-height=360
            ziggeo-limit=240
            ziggeo-form_accept="#post"
            ziggeo-perms="allowupload"
            ziggeo-tags="wordpress,<?php echo $current_user->user_login; ?>,creatingPost"
        ></ziggeo>
    </div>
</script>

<script type="text/template" id="ziggeo-rerecorder-template">
    <div id="ziggeo-recorder">
        <ziggeo
            ziggeo-width=480
            ziggeo-height=360
            ziggeo-limit=240
            ziggeo-video="VIDEOTOKEN"
            ziggeo-modes="rerecorder"
            ziggeo-perms="allowupload"
            ziggeo-form_accept="#post"
            ziggeo-tags="wordpress,<?php echo $current_user->user_login; ?>,creatingPost,rerecorded"
        ></ziggeo>
    </div>
</script>

<script>
    jQuery(document).on("ready", function () {
        jQuery(".wp-media-buttons").prepend(jQuery("#ziggeo-insert-button-template").html());
        var editor_elements = [".wp-editor-tabs", ".wp-editor-container", "#post-status-info tr", "#insert-media-button"];
        var video_mode = false;
        /*
        var content = jQuery("#content").val();        
        var regex = /\[ziggeo\](.*)\[\/ziggeo\]/g;
        var matches = regex.exec(content);
        if (matches) {
            video_mode = true;
            jQuery("#content-html").click();
            for (var i = 0; i < editor_elements.length; ++i)
                jQuery(editor_elements[i]).css("display", "none");
            jQuery("#revert-ziggeo-button").css("display", "");
            jQuery("#post-body-content").append(jQuery("#ziggeo-rerecorder-template").html().replace("VIDEOTOKEN", matches[1]));
        }
        */
        jQuery("#insert-ziggeo-button").on("click", function () {
            if (video_mode)
                return;
            video_mode = true;
            jQuery("#content-html").click();
            for (var i = 0; i < editor_elements.length; ++i)
                jQuery(editor_elements[i]).css("display", "none");
            jQuery("#revert-ziggeo-button").css("display", "");
            jQuery("#accept-ziggeo-button").css("display", "none");
            jQuery("#post-body-content").append(jQuery("#ziggeo-recorder-template").html());
        });
        jQuery("#revert-ziggeo-button").on("click", function () {
            if (!video_mode)
                return;
            /*
            if (!confirm("Are you sure that you want to remove your video?"))
                return;
            */
            video_mode = false;
            //jQuery("#content").val("");
            //jQuery("#content-tmce").click();
            for (var i = 0; i < editor_elements.length; ++i)
                jQuery(editor_elements[i]).css("display", "");
            jQuery("#revert-ziggeo-button").css("display", "none");
            jQuery("#accept-ziggeo-button").css("display", "none");
            jQuery("#ziggeo-recorder").remove();
        });
        jQuery("#accept-ziggeo-button").on("click", function () {
            if (!video_mode)
                return;
            video_mode = false;
            for (var i = 0; i < editor_elements.length; ++i)
                jQuery(editor_elements[i]).css("display", "");
            jQuery("#revert-ziggeo-button").css("display", "none");
            jQuery("#accept-ziggeo-button").css("display", "none");
            jQuery("#ziggeo-recorder").remove();
        });
        if(typeof ZiggeoApi !== "undefined") {
            //For times when this is included without the ziggeo.js (happens sometimes in GF)
            ZiggeoApi.Events.on("submitted", function (data) {
                jQuery("#content").val(jQuery("#content").val() + "[ziggeo]" + data.video.token + "[/ziggeo]");
                jQuery("#revert-ziggeo-button").css("display", "none");
                jQuery("#accept-ziggeo-button").css("display", "");
            });
        }
    });
</script>
