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
	// you would do it by calling add_filter('ziggeo_get_user_modify', 'your-function');
	$current_user = apply_filters('ziggeo_get_user_modify', $current_user);

	return $current_user;
}

function ziggeo_p_parse_custom_tags($content, $location = 'core') {

	$content = apply_filters('ziggeo_custom_tags_processing', $content, $location);

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

//Strip new lines for spaces
function ziggeo_line_min($code) {
	return str_replace(array("\n", "\t"), ' ', $code);
}

//This is to allow us to remove characters that would cause issues while saving or showing the info
if(!function_exists('ziggeo_clean_text_values')) {

	function ziggeo_clean_text_values($text, $replace_array = null) {
		//replace '
		$text = str_replace("'", '&apos;', $text);

		//replace "
		$text = str_replace('"', '&quot;', $text);

		//In case we want to do some additional switching, we use this array
		//array( ['from' => 'VALUE', 'to' => 'VALUE'] )
		if($replace_array !== null) {
			for($i = 0, $c = count($replace_array); $i < $c; $i++) {
				$text = str_ireplace($replace_array[$i]['from'], $replace_array[$i]['to'], $text);
			}
		}

		return $text;
	}
}

//Used to remove entities and put them as original characters instead, so it looks right
if(!function_exists('ziggeo_restore_text_values')) {

	function ziggeo_restore_text_values($text, $restore_array = null) {
		//restore '
		$text = str_replace("'", '&apos;', $text);

		//restore "
		$text = str_replace('"', '&quot;', $text);

		//In case we want to do some additional switching, we use this array
		//array( ['from' => 'VALUE', 'to' => 'VALUE'] )
		if($restore_array !== null) {
			for($i = 0, $c = count($restore_array); $i < $c; $i++) {
				$text = str_ireplace($restore_array[$i]['from'], $restore_array[$i]['to'], $text);
			}
		}

		return $text;
	}
}

function ziggeo_get_plugin_options_defaults() {
	$defaults = array(
		'version'							=> ZIGGEO_VERSION,
		'templates_id'						=> '',
		//'templates_editor'
		//'templates_manager'
		'feedback'							=> '',
		'token'								=> '',
		'recorder_config'					=> '',
		'player_config'						=> '',
		'disable_video_comments'			=> '',
		'disable_text_comments'				=> '',
		'comments_recorder_template'		=> '',
		'comments_player_template'			=> '',
		'video_and_text'					=> '',
		'comment_roles'						=> 0,
		'integrations'						=> '',
		'integrations_recorder_template'	=> '',
		'integrations_player_template'		=> '',
		'default_lang'						=> 'auto',
		'dev_mode'							=> ZIGGEO_YES,
		'p_token'							=> '',
		'e_token'							=> '',
		'templates_save_to'					=> 'db',
		//'templates_clear'					=> '',
		'webrtc_for_mobile'					=> ZIGGEO_YES,
		'webrtc_streaming'					=> ZIGGEO_NO,
		'webrtc_streaming_needed'			=> ZIGGEO_YES,
		'use_auth'							=> ZIGGEO_NO,
		'use_debugger'						=> ZIGGEO_NO,
		'sauth_token'						=> '',
		'use_version'						=> 'v1',
		'use_revision'						=> 'stable'
	);

	return $defaults;
}

// Returns all plugin settings or defaults if not existing
function ziggeo_get_plugin_options($specific = null) {
	$options = get_option('ziggeo_video');

	$defaults = ziggeo_get_plugin_options_defaults();

	//in case we need to get the defaults
	if($options === false || $options === '') {
		// the defaults need to be applied
		$options = $defaults;
	}

	// In case we are after a specific one.
	if($specific !== null) {
		if(isset($options[$specific])) {
			return $options[$specific];
		}
		elseif(isset($defaults[$specific])) {
			return $defaults[$specific];
		}
	}
	else {
		return $options;
	}

	return false;
}

// Returns all notifications or defaults if not existing
function ziggeo_get_notifications($specific = null) {
	$notifications = get_option('ziggeo_notifications');

	$defaults = array(
		'list'		=> array(),
		'last_id'	=> 0
	);

	//in case we need to get the defaults
	//if($notifications === false || $notifications === '' || (strlen($notifications) < 10)) {
	if($notifications === false || $notifications === '' || !isset($notifications['list'])) {
		// the defaults need to be applied
		$notifications = $defaults;
	}

	// In case we are after a specific one.
	if($specific !== null) {
		if(isset($notifications[$specific])) {
			return $notifications[$specific];
		}
		elseif(isset($defaults[$specific])) {
			return $defaults[$specific];
		}
	}
	else {
		return $notifications;
	}

	return false;
}

//Get videos that were recorded on WP side
function ziggeo_get_video_notices() {
	$videos = get_option('ziggeo_videos_count');

	//in case we need to get the defaults
	if($videos === false || $videos === '') {
		// the defaults need to be applied
		$videos = 0;
	}

	return $videos;
}

// Function to output if select field is selected or not. Used mostly in the admin files.
function ziggeo_echo_selected($var_to_check, $value_to_have) {
	if($var_to_check === $value_to_have) {
		echo ' selected="selected" ';
	}
}

// Helper function for integration detecting our embeddings and then adding the 
function ziggeo_p_integrations_field_add_custom_tag($field, $code_addition) {
	$_slice_point = stripos($field, ' ', stripos($field, '<ziggeo'));
	$field = substr($field, 0, $_slice_point) . ' ' . $code_addition . ' ' . substr($field, $_slice_point);

	return $field;
}

//Create the toolbar
function ziggeo_p_pre_editor($ajax = false) {
	?>
	<div id="ziggeo-post-editor-toolbar">
		<?php echo do_action('ziggeo_toolbar_button', $ajax); ?>
	</div>
	<?php
}

//Creates the button in the toolbar, allowing us to quickly create uniform buttons
function ziggeo_create_toolbar_button($id='', $title = '', $icon='video-alt', $visible = true, $type = 'button') {

	$code = '<a href="#" id="' . $id . '" class="button" title="' . $title . '" ';
	if($visible !== true) {
		$code .= ' style="display:none;" ';
	}
	$code .= ' onclick="return false;"';
	$code .= '>';
	$code .= '<span class="dashicons dashicons-' . $icon . '"></span> ' . $title . ' </a>';

	return $code;
}


?>