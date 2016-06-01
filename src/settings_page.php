<?php

//Checking if WP is running or if this is a direct call..
defined('ABSPATH') or die();

    global $wp_version;

    if( version_compare( $wp_version, '4.5') >= 0 ) {
        $current_user = wp_get_current_user();
    }
    else {
        global $current_user;
        get_currentuserinfo();        
    }

    $options = get_option('ziggeo_video');
?>
<div>
    <?php if( !isset($options, $options["token"]) || (isset($options["token"]) && trim($options["token"]) === '') ) { ?>
        <div class="update-nag">
            <p>
                Haven't signed up for the Ziggeo API yet?
                <a href="javascript:ziggeo_onboard_submit()">
                    Click here to automatically generate a key for <?= $current_user->user_email ?>
                </a>
            </p>
        </div>
        <?php /* We might want to add this into some js file instead and add in it head, although it is small, but just to keep it clean */ ?>
        <script type="text/javascript">
            function ziggeo_onboard_submit() {
                ziggeo_onboard(
                    "<?= $current_user->user_firstname . ' ' . $current_user->user_lastname ?>",
                    "<?= $current_user->user_email ?>",
                    function (result) {
                        alert("Success! We have obtained an API key for you.\n" +
                              "Ziggeo will send you an email with a link shortly that you need to confirm.\n" + 
                              "It will also ask you to create a password to secure your Ziggeo account.");
                        jQuery("#ziggeo_app_token").val(result);
                        jQuery("#ziggeo_app_token").closest("form").submit();
                    },
                    function (err) {
                        alert("We could not create an API key for you:\n" + err);
                    }
                );
            }
        </script>
    <?php } ?>
    <h2>Ziggeo Video Posts &amp; Comments</h2>

    <form action="options.php" method="post">
        <?php

        wp_nonce_field('ziggeo_nonce_action', 'ziggeo_video_nonce');
        get_settings_errors();
        settings_fields('ziggeo_video');

        if(isset($_GET['secureForm']) && $_GET['secureForm'] === "true") {
            ?>
            <p>We were not able to create the needed file(s) on your server. As such and for you to be secure, we require your FTP details. They can be provided through this form, which will allow us to create everything for you.</p>
            <p>This form is WordPress FileSystem form - the same one that is shown when you try to install plugins or upgrade your WordPress system. We do not handle your credentials in any other manner except to pass it to WordPress itself.</p>
            <p>If you wish to not use this, that is perfectly fine with us. You can achieve the same by creating the folder and files manually by yourself instead.</p>
            <p>To proceed with the automated process just fill out the form. For manual steps please go to this page instead: <a href="https://ziggeo.com/wordpress-plugin-filesystem" target="_blank">How to create setup files in our WordPress plugin?</a></p>
            <p>If you want to get back to Ziggeo plugin settings screen, just click on the following link: <a href="<?php echo esc_url( get_admin_url(null, 'options-general.php?page=ziggeo_video') ); ?>">Ziggeo Plugin settings</a>.</p>
            <?php

            //We should show the secure form in order to create files and do the first write..
            $url = wp_nonce_url('options-general.php?page=ziggeo_video&secureForm=true','ziggeo_nonce_action');
            $form_fields = null;

            //Are we trying to save template or something else?
            if( isset($_GET['templateID']) ) {
                ?>
                <input id="ziggeo_templates_id" name="ziggeo_video[templates_id]" type="hidden" value="<?php echo $_GET['templateID']; ?>" />
                <textarea id="ziggeo_templates_editor" name="ziggeo_video[templates_editor]" style="display:none;"><?php echo base64_decode( $_GET['template'] ); ?></textarea>
                <?php
                $form_fields = array('ziggeo_video[templates_id]', 'ziggeo_video[templates_editor]');
            }

            //We will use this 2 times, instead of having it 2 times
            $wasError = ( isset($_GET['error']) ) ? true : false;

            //Lets setup some fields that we will use later on to know that secure form was used..
            ?>
            <input type="hidden" name="ziggeo[secure_form]" value="1">
            <input type="hidden" name="ziggeo[secure_try]" value="<?php echo ( isset($_GET['attempt']) ) ? $_GET['attempt'] : ( ($wasError) ? 2 : 1 ); ?>">
            <?php

            if( ($credentials = request_filesystem_credentials($url, '', $wasError, ZIGGEO_ROOT_PATH, $form_fields, true) ) === false ) {
                //Customer did not enter these details before, so lets wait for input since the form is shown..
                ?> </div> <?php //closing the .wrap above
                return null;
            }
        }
        elseif(isset($_GET['secureForm']) && $_GET['secureForm'] === "failed") {
            ?>
            <p>Uf, oh. We have tried, however it seems that WordPress FileSystem was not able to access your system. Sorry about that.</p>
            <p>Please do check out this page to see how to manuanlly create needed files: <a href="https://ziggeo.com/wordpress-plugin-filesystem" target="_blank">How to create setup files in our WordPress plugin?</a>. It takes only a few seconds to do it manually (screenshots inside).</p>
            <hr>
            <p>If you want to get back to Ziggeo plugin settings screen, just click on the following link: <a href="<?php echo esc_url( get_admin_url(null, 'options-general.php?page=ziggeo_video') ); ?>">Ziggeo Plugin settings</a>.</p>
            <?php //'Save Changes' button seems out of place on this page ?>
            <p><style type="text/css">input[type="submit"] {display: none; }</style></p>
            <?php
        }
        else {
            do_settings_sections('ziggeo_video');
        }
        ?>
        <input name="Submit" type="submit" value="<?php esc_attr_e('Save Changes'); ?>" />

    </form>
</div>
