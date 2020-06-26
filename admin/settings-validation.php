<?php

//Checking if WP is running or if this is a direct call..
defined('ABSPATH') or die();

// Validation functions for incoming data

//Function to capture the values submitted by the customer in the settings panel.
function ziggeo_a_s_validation($input) {

	// Prep
	/////////

	//We will first grab the old values, then add to them, or simply replace them where needed..
	$options = ziggeo_get_plugin_options();

	//List of all options that we accept
	$allowed_options = array(
		//not shown
			'version' => true,
		//templates tab
			'templates_id' => true, 'templates_editor' => true, 'templates_manager' => true, 'feedback' => true,
		//general tab
			'token' => true,
			'recorder_config' => true, 'player_config' => true, 'disable_video_comments' => true, 'disable_text_comments' => true,
			'comments_recorder_template' => true, 'comments_player_template' => true, 'video_and_text' => true,
			'comment_roles' => true, 'integrations_recorder_template' => true, 'default_lang' => true,
		//integrations tab
			'integrations' => true,
		//experts tab
			'dev_mode' => true, 'p_token' => true, 'e_token' => true, 'templates_save_to' => true, 'templates_clear' => true, 'webrtc_for_mobile' => true, 'webrtc_streaming' => true, 'webrtc_streaming_needed' => true, 'sauth_token' => true, 'use_auth' => true, 'use_version' => true, 'use_revision' => true
	);

	//Needed for checkboxes otherwise we would clear them
	$clear_if_not_set = array(
		'disable_video_comments' => true, 'disable_text_comments' => true, 'video_and_text' => true, 'use_auth' => true
	);

	//DEVS: Should we add any hooks here to add your own options into the main settings? Let us know.

	//Lets handle the feedback banner..
	$t_rez = ziggeo_a_s_v_feedback_handler( $options, $input );

	$show_feedback_thank_you = $t_rez['show_message'];
	$input = $t_rez['input'];

	//Clear out the input array of all things that we already have saved, leaving just the ones that we need to update.
	if(is_array($options)) {
		//Going through all updated settings so that we can update all that need to be so
		foreach($allowed_options as $option => $value) {
			if(isset($input[$option])) {
				$options[$option] = $input[$option];
				//We have used the option, now lets not have it available any more
				unset($input[$option]);
			}
			elseif(isset($clear_if_not_set[$option])) {
				//Fields we should clear out if not set (checkboxes)
				$options[$option] = ZIGGEO_NO;
			}
		}
	}
	else {
			$options = array ();
	}

	//Now we check if there are any new options that are passed to us and that we allow them
	if( !empty($input) ) {
		foreach($input as $option => $value)
		{
			if(isset($allowed_options[$option]))
			{
				$options[$option] = $value;
			}
		}
	}
	else {
		//return false; //nothing to do here..
		return $options;
	}

	// General tab settings
	/////////////////////////

	//Comments
	$options = ziggeo_a_s_v_comments_handler($options, $input);

	// Integrations tab settings
	//////////////////////////////

	$options = ziggeo_a_s_v_integrations_handler($options, $input);

	//From this point on, we should not use $input, only $options



	// Templates tab settings
	///////////////////////////

	//templates
	$options = ziggeo_a_s_v_templates_handler($options);


	//Contact us tab settings
	///////////////////////////



	//Expert Settings tab
	///////////////////////

	//Synce the templates between the files and DB templates that are saved
	if(isset($input['expert_sync']) && $input['expert_sync'] === 'sync_now') {
		//Get all templates from the DB
		$db_templates = ziggeo_p_templates_index(null, 'db');
		//Get all templates from the files
		$file_templates = ziggeo_p_templates_index(null, 'files');

		if( is_array($db_templates)) {
			$final_templates = $db_templates;
		}
		else {
			$final_templates = array();
		}

		//Prepare the templates data
		if( is_array($file_templates) ) {
			//All is good, lets do it
			foreach ($file_templates as $key => $value) {
				if(!isset($final_templates[strtolower($key)])) {
					$final_templates[strtolower($key)] = $value;
				}
			}
		}

		//Sync them in
		ziggeo_p_templates_add_all($final_templates, 'db');
		//This might fail - depends on what your server is set up like, so if files can not be made, this will fail.
		ziggeo_p_templates_add_all($final_templates, 'files');
	}

	//Do we want to clear out the templates? (WHY? - please let us know)
	if(isset($options['templates_clear']) && $options['templates_clear'] === 'clear') {
		ziggeo_p_templates_remove_all();

		unset($options['templates_clear']);
	}

	//Everything has been done, lets show messages or do any background tasks if needed 

	//Lets show a nice thank you if the link was clicked that we already got feedback.
	if($show_feedback_thank_you) {
		add_settings_error('ziggeo_feedback', 'feedback removed',
			__('Feedback banner was removed.', 'ziggeo') .
			'<div id="ziggeo_feedback-thankYOU" onclick ="this.parentNode.removeChild(this);">' .
			__('<b>Thank you</b> for leaving us a feedback. We hope that you enjoy our plugin and we welcome any ideas or suggestions :)', 'ziggeo') .
			'<script type="text/javascript">setTimeout( function() {var box = document.getElementById("ziggeo_feedback-thankYOU"); if(box) {box.parentNode.removeChild(box);}}, 5000 );</script></div>',
			'updated');
	}

	//adding version in the DB as well so that we can know when plugin is updated and do any required actions..
	if(!isset($options['version'])) {
		//plugin settings were not declared so far, so we do not need to convert from old version
		$options['version'] = ZIGGEO_VERSION;
	}

	return $options;
}

//Handles the feedback banner events
function ziggeo_a_s_v_feedback_handler($options, $input) {
	$show_message = false;

	//The option is not yet set and input suggests that we are setting it now..
	if( (!isset($options['feedback']) || (isset($options['feedback']) && $options['feedback'] !== "1" ) ) &&
		( isset($input['feedback']) && $input['feedback'] === "1" ) ) {
		$showMessage = true;
	}
	elseif(isset($input['feedback'])) {
		//If option is already set that the feedback was left, but the input is passed (as it will be when something is saved), we just 'neutralize' it here
		unset($input['feedback']);
	}

	return [ 'input' => $input, 'show_message' => $show_message ];
}

//Handles the Comments section logic so that opposing options are not selected.
function ziggeo_a_s_v_comments_handler($options, $input) {
	//Lets make sure that if video and text is selected, that video and comment options are not selected
	// (no sense having them disabled and this enabled)
	if( isset($input['video_and_text']) && !empty($input['video_and_text']) ) {
		unset($options['disable_video_comments'], $options['disable_text_comments']);
	}
	elseif( ( isset($input['disable_video_comments']) && !empty($input['disable_video_comments']) ) ||
		( isset($input['disable_text_comments']) && !empty($input['disable_text_comments']) ) ) {
		unset($options['video_and_text']);
	}

	return $options;
	
}

//Integrations handling codes
function ziggeo_a_s_v_integrations_handler($options, $input) {
	if(isset($input['integration_change']) && $input['integration_change']!== "" ) {
		//the call was made to change the status of some integration..
		$details = explode('=', $input['integration_change']);

		if(isset($options['integrations'], $options['integrations'][$details[0]])) {

			$options['integrations'][$details[0]]['active'] = true;//($details[1] === 'disable') ? false : true;
		}
		else {
			//seems that it was not set up so far, lets set it up..
			if(!isset($options['integrations'])) {
				$options['integrations'] = array();
			}
			//lets add integration..
			$options['integrations'][$details[0]] = array('active' => ($details[1] === 'disable') ? false : true );
		}
	}

	return $options;
}

//Handles the templates saving.
function ziggeo_a_s_v_templates_handler($options) {

	$ajax_status = array(
		'message'	=> false,
		'status'	=> false
	);

	//if we have a value that is not empty and something other than default one...
	if( isset($options['templates_editor']) &&
		$options['templates_editor'] !== '' &&
		$options['templates_editor'] !== '[ziggeo ' && //old templates started with this
		$options['templates_editor'] !== '[ziggeoplayer ' &&
		$options['templates_editor'] !== '[ziggeorecorder ' &&
		$options['templates_editor'] !== '[ziggeouploader ' &&
		$options['templates_editor'] !== '[ziggeorerecorder ') { //new templates start with this

		if(isset($options['templates_id'])) {
			$options['templates_id'] = trim($options['templates_id']);
		}
		else {
			$options['templates_id'] = '';
		}

		//Lets check if templates_editor code ends with ] or not.. if not, we need to add it, since customers might forget adding it.
		if( substr( $options['templates_editor'], -1) !== "]" ) {
			$options['templates_editor'] .= ']';
		}

		//We should check what is the action..
		//add new
		if( !isset($options['templates_manager']) || $options['templates_manager'] === '' ) {
			$id_given = true;

			//before adding template we need to know that the template name was added, if not, lets just name it for our customer :)
			if($options['templates_id'] === '' ) {
				$options['templates_id'] = "ziggeo_template_" . rand(20, 3000);

				$message = sprintf( __('We have saved your template, but since Template ID was not given, we have set one up for you! - "%s"', 'ziggeo'), $options['templates_id'] );
				$id_given = false;
			}
			//if the template is just a number, it will not work, we need to add it some text at the start
			elseif( is_numeric($options['templates_id']) ) {
				$options['templates_id'] = '_' . $options['templates_id'];
			}

			//Templates Editor value gets saved in a bit different manner, together with the ID.. We need to keep these two clean each time
			// instead we save them into a new file as JSON, but we must make sure that such file does not exist currently.
			if( ($rez = ziggeo_p_templates_add( $options['templates_id'], $options['templates_editor']) ) ) {

				//Was template ID set (true) or did we make it for our customer? (false)
				if($id_given) {
					$message = sprintf( __('Your template "%s" has been successfully created.', 'ziggeo'), $options['templates_id']);
				}

				$ajax_status['message'] = 'added';
				$ajax_status['status'] = 'success';
				$ajax_status['template_id'] = $options['templates_id'];

				add_settings_error('ziggeo_templates_manager',
														'template_created',
														$message,
														'updated');
			}
			elseif($rez === false || $rez === null) {

				//for now we handle false and null in the same manner, however later we might do it differently..

				//@here - false depends on where we save data to. If DB then it already exists or there was an error

				$ajax_status['message'] = 'unchanged';
				$ajax_status['status'] = 'success';
			}
		}
		//edit old template
		elseif( isset($options['templates_manager']) && $options['templates_manager'] !== '' ) {

			//old ID, new ID, template structure
			if( ziggeo_p_templates_update($options['templates_manager'], $options['templates_id'] , $options['templates_editor']) ) {
				$ajax_status['message'] = 'updated';
				$ajax_status['status'] = 'success';
				add_settings_error('ziggeo_templates_manager',
																'template_updated',
																sprintf(__('Your template "%s" has been successfully updated.', 'ziggeo'), $options['templates_id']),
																'updated');
			}
		}

		unset( $options['templates_editor'], $options['templates_id'] );
	}
	//Should we delete template?
	elseif( isset($options['templates_manager']) && $options['templates_manager'] !== '' ) {
		if( ziggeo_p_templates_remove($options['templates_manager']) ) {
			$ajax_status['message'] = 'removed';
			$ajax_status['status'] = 'success';
			add_settings_error('ziggeo_templates_manager',
													'template_removed',
													sprintf(__('Your template "%s" has been successfully deleted.', 'ziggeo'), $options['templates_manager']),
													'updated');
		}
	}

	if(defined('DOING_AJAX') && DOING_AJAX) {
		return $ajax_status;
	}
	else {
		//We are currently showing it up as default, so we should remove it at this point - we do not want it saved
		unset($options['templates_editor']);

		return $options;
	}

}

?>