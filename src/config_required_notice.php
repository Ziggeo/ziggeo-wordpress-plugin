<?php
//Checking if WP is running or if this is a direct call..
defined('ABSPATH') or die();

function ziggeo_token_notice() { ?>
    <div class="update-nag">
		<?php //Fixed to accomodate the customers having WP website within a folder - not directly on a domain name root like ziggeo.com/wp/ ?>
        <p><?php _e('You need to <a href="' . site_url() . '/wp-admin/options-general.php?page=ziggeo_video">specify an API key</a> for the Ziggeo API to have videos as functional.', 'my-text-domain'); ?></p>
    </div>
<?php }

if ($_SERVER["PHP_SELF"] != "/wp-admin/options-general.php" || @$_GET["page"] != "ziggeo_video") {
	$options = get_option('ziggeo_video');
	if (!@$options || !@$options["token"])
		add_action('admin_notices', 'ziggeo_token_notice');
}
