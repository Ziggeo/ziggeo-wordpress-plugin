<?php

function ziggeo_token_notice() { ?>
    <div class="update-nag">
        <p><?php _e('You need to <a href="/wp-admin/options-general.php?page=ziggeo_video">specify an API key</a> for the Ziggeo API to allow videos be functional.', 'my-text-domain'); ?></p>
    </div>
<?php }

if ($_SERVER["PHP_SELF"] != "/wp-admin/options-general.php" || @$_GET["page"] != "ziggeo_video") {
	$options = get_option('ziggeo_video');
	if (!@$options || !@$options["token"])
		add_action('admin_notices', 'ziggeo_token_notice');
}
