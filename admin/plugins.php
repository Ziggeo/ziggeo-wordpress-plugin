<?php

//Checking if WP is running or if this is a direct call..
defined('ABSPATH') or die();


//WP Dashboad > Plugins (list)

//For a link to settings in plugins screen
add_filter('plugin_action_links_ziggeo/ziggeo.php', 'ziggeo_p_plugins_listing_mod');

function ziggeo_p_plugins_listing_mod($links) {

	$links[] = '<a href="' . esc_url( get_admin_url(null, 'admin.php?page=ziggeo_video') ) . '">' .
				_x('Settings', '"Settings" link on the Plugins page', 'ziggeo') . '</a>';
	$links[] = '<a href="mailto:support@ziggeo.com">'.
				_x('Support', '"Support" link on the Plugins page', 'ziggeo') . '</a>';
	return $links;
}

?>