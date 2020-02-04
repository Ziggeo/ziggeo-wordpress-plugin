<?php

//Checking if WP is running or if this is a direct call..
defined('ABSPATH') or die();

//Used to show the info about missing token while in the WP dashboard

function ziggeo_p_no_token_notice() { ?>
	<div class="update-nag">
		<p><?php _e('You need to <a href="' . site_url() . '/wp-admin/admin.php?page=ziggeo_video">specify an API key</a> for the Ziggeo API to have videos as functional.', 'ziggeo'); ?>
		</p>
	</div>
<?php }

if ( is_admin() ) {
	if($_SERVER["PHP_SELF"] != "/wp-admin/admin.php" || @$_GET["page"] != "ziggeo_video") {
		$options = get_option('ziggeo_video');
		if (!isset($options, $options["token"]) ) {
			add_action('admin_notices', 'ziggeo_p_no_token_notice');
		}
	}
}

?>