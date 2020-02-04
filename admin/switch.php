<?php

//Checking if WP is running or if this is a direct call..
defined('ABSPATH') or die();

//Code used to switch from older version of the plugin (v1.15 was latest) to v2.0

//This function will update the database settings for us
function ziggeo_p_switch_over($options = null) {

	if($options === null) {
		$options = get_option('ziggeo_video');
	}

	//To go to a newer version we need to:
	/*
		[ ] Add new settings
		[ ] transfer v1 templates to v2
		[ ] save templates to DB (we will make that default)
	*/
}

?>