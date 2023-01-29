<?php

//Checking if WP is running or if this is a direct call..
defined('ABSPATH') or die();


//You can override this function. If you do, it is recommended to keep the same hooks, otherwise some things might not work any more
if(!function_exists('ziggeo_content_parse_uploader')) {

//@TODO: Lets add few hooks into this
	function ziggeo_content_parse_uploader($code, $post_code = true) {

		//return the HTML code
		$result = '<ziggeorecorder ' . ziggeo_template_v1_to_v2(ziggeo_p_parameter_prep($code)) . '></ziggeorecorder>';

		if($post_code === true) {
			echo $result;
		}
		else {
			//return the HTML code
			return $result;
		}
	}
}

//Shortcode handling for Ziggeo Uploader
add_shortcode( 'ziggeouploader', function($attrs) {
	return ziggeo_p_shortcode_handler('[ziggeouploader', $attrs);
});

function ziggeo_p_template_is_uploader($template) {

	$uploaderParams = array(
		'ziggeouploader', //if it is v2 this is the only part we need to confirm, hence it being at the top..
		'manual_upload',
		'perms[allowupload]',
		'perms[forbidrecord]',
		'allowed_extensions',
		'limit_upload_size',

		//v2
		'allowcustomupload',
		// video & audio
		'allowupload',
		'manualsubmit',
		'allowedextensions',
		'filesizelimit'
	);

	for($i = 0, $c = count($uploaderParams); $i < $c; $i++) {
		if( stripos($template, $uploaderParams[$i] . '=') !== false ||
			stripos($template, $uploaderParams[$i] . ']') !== false ||
			stripos($template, $uploaderParams[$i] . ' ') !== false) {
			return true;
		}
	}

	return false;
}

//handles the raw parameters for the ziggeo recorder..
function ziggeo_p_prep_parameters_uploader($raw_parameters = null) {
	if($raw_parameters === null) {
		return '';
	}

	return ziggeo_p_parameter_processing( ziggeo_get_parameters_from_template_code(ZIGGEO_DEFAULTS_UPLOADER), $raw_parameters );
}

?>