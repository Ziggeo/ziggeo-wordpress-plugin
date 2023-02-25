<?php

//Checking if WP is running or if this is a direct call..
defined('ABSPATH') or die();



//You can override this function. If you do, it is recommended to keep the same hooks, otherwise some things might not work any more
if(!function_exists('ziggeo_content_parse_rerecorder')) {

//@TODO: Lets add few hooks into this
	function ziggeo_content_parse_rerecorder($code, $post_code = true) {

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

//Shortcode handling for Ziggeo Re-Recorder
// Intentionally removed as it can cause issues during parsing, new method works better
//add_shortcode( 'ziggeorerecorder', function($attrs) {
//	return ziggeo_p_shortcode_handler('[ziggeorerecorder', $attrs);
//});

function ziggeo_p_template_is_rerecorder($template) {

	$rerecorderParams = array(
		'ziggeorerecorder', //if it is v2 this is the only part we need to confirm, hence it being at the top..
		'modes[rerecorder]',
		'rerecordings',
		'delete_old_streams',
		'video',
		'perms[forceoverwrite]',
		'hide_rerecord_on_snapshots',
		'early_rerecord',

		//v2
		'skipinitialonrerecord', // video and audio
		'early-rerecord',
		'rerecordable',
		'rerecordableifexists',
		'force-overwrite',
		'delete-old-streams'
	);

	for($i = 0, $c = count($rerecorderParams); $i < $c; $i++) {
		if( stripos($template, $rerecorderParams[$i] . '=') !== false ||
			stripos($template, $rerecorderParams[$i] . ']') !== false ||
			stripos($template, $rerecorderParams[$i] . ' ') !== false) {
			return true;
		}
	}

	return false;
}

//handles the raw parameters for the ziggeo recorder..
function ziggeo_p_prep_parameters_rerecorder($raw_parameters = null) {

	if($raw_parameters === null) {
		return '';
	}

	return ziggeo_p_parameter_processing( ziggeo_get_parameters_from_template_code(ZIGGEO_DEFAULTS_RERECORDER), $raw_parameters );
}

?>