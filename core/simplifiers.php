<?php

//Checking if WP is running or if this is a direct call..
defined('ABSPATH') or die();

//Functions that save time..

function ziggeo_p_get_current_user() {
	global $wp_version;

	if( version_compare( $wp_version, '4.5') >= 0 ) {
		$current_user = wp_get_current_user();
	}
	else {
		global $current_user;
		get_currentuserinfo();        
	}

	//allows to change the user dynamically if needed for some tests.
	// you would do it by calling add_action('ziggeo_get_user_modify', 'your-function');
	do_action('ziggeo_get_user_modify', $current_user);

	return $current_user;
}

function ziggeo_p_parse_custom_tags($content) {

	$content = apply_filters('ziggeo_custom_tags_processing', $content);

	//return all of the parsed content
	return $content;
}

//Function checks for the tells if the code is v1 or v2..
//$code is for the template/parameters to check - required and fullcode is optional to have a better context allowing us to see more than parameters only
function ziggeo_p_check_code_is_v1($code, $fullcode = false) {

	//v1 has underscores in parameter names while v2 has dashes (v1: example_param v2: example-param)
	if( stripos($code, "_") > -1 ) {
		return true;
	}

	//v1 had square brackets, v2 does not
	if( stripos($code, "[") > -1 ) {
		return true;
	}

	//some very v1 specific paramets check
	$v1tells = array(
		'modes',
		'-limit',
		' limit',//timelimit is v2
		' data', //custom-data is v2
		'perms',
		'apitoken' //v2 application
	);

	if($fullcode !== false) {
		//lets check for existance of '<ziggeo>' element
		if( stripos($fullcode, '</ziggeo>') > -1 ||
			stripos($fullcode, '<ziggeo>') > -1 ||
			stripos($fullcode, '<ziggeo ') > -1 ) {
			return true;
		}
	}

	for($i = 0, $c = count($v1tells); $i < $c; $i++) {
		if( stripos($code, $v1tells[$i]) ) {
			return true;
		}
	}

	return false;
}

//You can override this function. If you do, it is recommended to keep the same hooks, otherwise some things might not work any more
if(!function_exists('ziggeo_template_v1_to_v2')) {

	//@TODO: Lets add some hooks..
	function ziggeo_template_v1_to_v2($code) {

		//if changing, it is important to note that first in one array corresponds to first in second array
		// this means that n in first also corresponds to n entry in second. Changing only one would make it not work right!
		$_v1 = array(
			'limit',
			'disable_first_screen',
			'face_outline',
			'disable_timer',
			'disable_snapshots',
			'hide_rerecord_on_snapshots',
			'default_image_selector',
			'flip_camera',
			'early_rerecord',
			'expiration_days',
			'recording_width',
			'recording_height',
			'auto_crop',
			'auto_pad',
			'minlimit',
			'enforce_duration',
			'nosound',
			'data',
			'modes[recorder]',
			'perms[allowupload]',
			'perms[forbidrerecord]',
			'disable_device_test',
			'immediate_playback',
			'rerecordings',
			'delete_old_streams',
			'input_bind',
			'form_accept',
			'client_auth',
			'server_auth'
		);

		$_v2 = array(
			'timelimit',
			'skipinitial',
			'faceoutline',
			'display-timer', //@ this would require values to be altered!
			'picksnapshots', //@ this would require values to be altered!
			'early-rerecord', //@ this would require values to be altered! PS: Not exact parameter, however best match
			' ', //intentionally left empty as there is no replacement
			'flip-camera',
			'early-rerecord',
			'expiration-days',
			'recordingwidth',
			'recordingheight',
			'auto-crop',
			'auto-pad',
			'timeminlimit',
			'enforce-duration',
			'noaudio',
			'custom-data',
			'recordermode',
			'allowupload',
			'rerecordable', //@ this would require value to be altered!
			'audio-test-mandatory', //@ this would require values to be altered!
			' ', //intentionally left empty as there is no replacement PS: While similar to localplayback, it is not really the same
			'recordings',
			'delete-old-streams',
			'input-bind',
			'form-accept',
			'client-auth',
			'server-auth'
		);

		//parameters separated by single space " "
		for($i = 0, $c = count($_v1); $i < $c; $i++) {
			if( stripos($code, $_v2[$i]) === false ) {
				$code = str_ireplace(' ' . $_v1[$i], ' ' . $_v2[$i], $code);
			}
			else {
				$code = str_ireplace(' ' . $_v1[$i], ' ', $code);
			}
		} 

		//parameters separated by "ziggeo-"
		for($i = 0, $c = count($_v1); $i < $c; $i++) {
			if($_v2[$i] === ' ') {
				//At this place we take care of removal of `ziggeo-` and `="value"` if present, when the parameter we should switch to
				// does not exist.
				//example: default_image_selector in v1 does not have the alternative in v2..
				$position = stripos($code, $_v1[$i]) - 7; //- length(ziggeo-)

				//if currently searched for parameter is found AND the v2 alternative of it is not already present..
				if($position > -1) {
					$code = substr($code, 0, $position) . 
						substr($code, stripos($code, ' ', $position++ ));
					$code = str_ireplace('ziggeo-' . $_v1[$i], ' ' . $_v2[$i], $code); //@todo - remove the value as well..
				}
			}
			else {
				//Is the parameter already added? If so, lets remove it..
				if( stripos($code, $_v2[$i]) === false ) {
					$code = str_ireplace('ziggeo-' . $_v1[$i], 'ziggeo-' . $_v2[$i], $code);
				}
				else {
					//leave it as is..
					//"deprecated_" for now until we start removing value as well.
					//$code = str_ireplace('ziggeo-' . $_v1[$i], ' deprecated_' . $v_1[$i], $code);
				}
			}
		} 

		return $code;
	}
}

function ziggeo_get_version() {
	return ZIGGEO_VERSION;
}
?>