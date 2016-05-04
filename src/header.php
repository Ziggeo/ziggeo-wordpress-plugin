<?php
//Checking if WP is running or if this is a direct call..
defined('ABSPATH') or die();

function ziggeo_script_header() {
	$options = get_option('ziggeo_video');

	echo '
	<!-- Ziggeo API code -->
	<script type="text/javascript">
		ZiggeoApi.token = "' . ( ( isset($options, $options["token"]) ) ? $options["token"] : "") . '";
		ZiggeoApi.Config.webrtc = true;
		ZiggeoApi.Config.resumable = true;
		ZiggeoApi.Config.cdn = true;
	</script>';
	//@TODO Do we want them to use webrtc always? Resumable and CDN seem as logical, since webrtc fallback is flash that would work as well like this, but should we maybe allow them to change these things?
}

add_action('wp_head', "ziggeo_script_header");
add_action('admin_head', "ziggeo_script_header");
