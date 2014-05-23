<?php

function ziggeo_script_header() {
	$options = get_option('ziggeo_video');
	echo "<script>ZiggeoApi.token = '" . (@$options && @$options["token"] ? $options["token"] : '') . "';</script>\n";
}

add_action('wp_head', "ziggeo_script_header");
add_action('admin_head', "ziggeo_script_header");
