<?php

//Checking if WP is running or if this is a direct call..
defined('ABSPATH') or die();



//You can override this function. If you do, it is recommended to keep the same hooks, otherwise some things might not work any more
if(!function_exists('ziggeo_content_parse_recorder')) {

//@TODO: Lets add few hooks into this
	function ziggeo_content_parse_recorder($code) {

		//return the HTML code
		return '<ziggeorecorder ' . ziggeo_template_v1_to_v2(ziggeo_p_parameter_prep($code)) . '></ziggeorecorder>';
	}
}

//Shortcode handling for Ziggeo Recorder
add_shortcode( 'ziggeorecorder', function($attrs) {
	return ziggeo_p_shortcode_handler('[ziggeorecorder', $attrs);
});


function ziggeo_p_template_is_recorder($template) {

	$recorderParams = array(
		//v1
		'disable_first_screen',
		'face_outline',
		'disable_timer',
		'disable_snapshots',
		'hide_rerecord_on_snapshots',
		'countdown',
		'default_image_selector',
		'flip_camera',
		'early_rerecord',
		'expiration_days',
		'recording_width',
		'recording_height',
		'auto_crop',
		'auto_pad',
		'key',
		'limit',
		'minlimit',
		'enforce_duration',
		'nosound',
		'tags',
		'data',
		'title',
		'description',
		'modes[recorder]',
		'perms[allowupload]',
		'perms[forbidrerecord]',
		'disable_device_test',
		'immediate_playback',
		'simulate',
		'input_bind',
		'form_accept',
		//v2
		'allowscreen',
		'skipinitial',
		'faceoutline',
		'display-timer',
		'gallerysnapshots',
		'picksnapshots',
		'countdown',
		'snapshotmax',
		'localplayback',
		'autorecord',
		'primaryrecord',
		'flip-camera',
		'expiration-days',
		'recordingwidth',
		'recordingheight',
		'preview-effect-profile',
		'timelimit',
		'timeminlimit',
		'enforce-duration',
		'noaudio',
		'framerate',
		'videobitrate',
		'audiobitrate',
		'microphone-volume',
		'custom-covershots',
		'tags',
		'custom-data',
		'title',
		'description',
		'allowrecord',
		'simulate',
		'recordermode',
		'input-bind',
		'form-accept',
		'recordings' //this generally tells us that it is recorder, without inspecting value we can not know if it is a rerecorder
	);

	for($i = 0, $c = count($recorderParams); $i < $c; $i++) {
		if( stripos($template, $recorderParams[$i] . '=') !== false ||
			stripos($template, $recorderParams[$i] . ']') !== false ||
			stripos($template, $recorderParams[$i] . ' ') !== false) {
			return true;
		}
	}

	return false;
}

//handles the raw parameters for the ziggeo recorder..
function ziggeo_p_prep_parameters_recorder($raw_parameters = null) {
	if($raw_parameters === null) {
		return '';
	}

	return ziggeo_p_parameter_processing( ziggeo_get_parameters_from_template_code(ZIGGEO_DEFAULTS_RERECORDER), $raw_parameters );
}

?>