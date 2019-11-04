<?php

//Checking if WP is running or if this is a direct call..
defined('ABSPATH') or die();

// - EXPERT - tab fields functions
//-------------------------------------

//Function to show the frame of our tab
function ziggeo_a_s_e_text() {
	?>
	</div>
	<div class="ziggeo-frame" style="display: none;" id="ziggeo-tab_expert">
		<p><i><?php _e('Expert settings for everyone that wants a bit more options to change around.', 'ziggeo'); ?></i></p>
	<?php
}

	//This field allows us to specify if we should have some info in the console or not. In future this might include additional details that give info from the server side, however for now they just specify if you get additional info in the console for JS events or not
	//Useful for localhost or live dev environments, not for production servers
	function ziggeo_a_s_e_development_mode_field() {

		$options = get_option('ziggeo_video');

		if(isset($options['dev_mode']) && $options['dev_mode'] === ZIGGEO_YES) {
			$current = ZIGGEO_YES;
			$yes_additional = 'selected';
			$no_additional = '';
		}
		else {
			$current = ZIGGEO_NO;
			$no_additional = 'selected';
			$yes_additional = '';
		}

		?>
		<select id="ziggeo_dev_mode" name="ziggeo_video[dev_mode]">
			<option value="<?php echo ZIGGEO_YES; ?>" <?php echo $yes_additional; ?>>Yes</option>
			<option value="<?php echo ZIGGEO_NO; ?>" <?php echo $no_additional; ?>>No</option>
		</select>
		<label for="ziggeo_dev_mode"><?php _e('Select if you want to see additional output (yes) in console, or hide it (no). Set to yes only on dev environments, otherwise to no.'); ?></label>
		<?php
	}


	//Creates a field that will allow us to choose where the templates will be saved (DB or file)
	// files can offer faster data retrieval over DB especially on slow or websites with many plugins
	// files can open website to security issue depending on setup, and sometimes might not be possible to create them
	// select the DB if you are not sure which one, or if you are not sure how to manually create the file as the automated file
	//  creation is removed in favor of security over ease of use.
	function ziggeo_a_s_e_templates_save_to_field() {

		$options = get_option('ziggeo_video');

		$e_db = ' selected ';
		$e_files = '';

		if(isset($options['templates_save_to']) && $options['templates_save_to'] == 'files') {
			$e_db = '';
			$e_files = ' selected ';
		}


		?>
		<select id="ziggeo_templates_save_to" name="ziggeo_video[templates_save_to]">
			<option value="db" <?php echo $e_db; ?>>DataBase</option>
			<option value="files" <?php echo $e_files; ?>>Files</option>
		</select>
		<label for="ziggeo_templates_save_to"><?php _e('Select where you want the templates to be saved at. Default is "DataBase", previously was "Files".', 'ziggeo'); ?></label>
		<?php
	}

	function ziggeo_a_s_e_clear_templates() {

		$options = get_option('ziggeo_video');

		?>
		<select id="ziggeo_templates_clear" name="ziggeo_video[templates_clear]">
			<option value="leave">Leave it</option>
			<option value="clear">Clear it</option>
		</select>
		<label for="ziggeo_templates_clear"><?php _e('This should be used only if you want to clear all templates for some reason! It will remove all templates', 'ziggeo'); ?></label>
		<?php
	}

	function ziggeo_a_s_e_webrtc_for_mobile() {
		$options = get_option('ziggeo_video');

		$webrtc_on = ' selected ';
		$webrtc_off = '';

		if(isset($options['webrtc_for_mobile']) && $options['webrtc_for_mobile'] == 'off') {
			$webrtc_on = '';
			$webrtc_off = ' selected ';
		}
		?>
		<select id="ziggeo_webrtc_for_mobile" name="ziggeo_video[webrtc_for_mobile]">
			<option value="true"<?php echo $webrtc_on; ?>>Turn On</option>
			<option value="false"<?php echo $webrtc_off; ?>>Turn off</option>
		</select>
		<label for="ziggeo_webrtc_for_mobile"><?php _e('This option allows you to turn on WebRTC recording on mobile devices instead of using native recording (recommended for mobile solutions)', 'ziggeo'); ?></label>
		<?php	
	}

	function ziggeo_a_s_e_webrtc_streaming() {
		$options = get_option('ziggeo_video');

		$webrtc_streaming_on = '';
		$webrtc_streaming_off = ' selected ';

		if(isset($options['webrtc_streaming']) && $options['webrtc_streaming'] == 'on') {
			$webrtc_streaming_on = ' selected ';
			$webrtc_streaming_off = '';
		}
		?>
		<select id="ziggeo_webrtc_streaming" name="ziggeo_video[webrtc_streaming]">
			<option value="true"<?php echo $webrtc_streaming_on; ?>>Turn On</option>
			<option value="false"<?php echo $webrtc_streaming_off; ?>>Turn off</option>
		</select>
		<label for="ziggeo_webrtc_streaming"><?php _e('Uploads start as soon as you start recording. Min length of 10 seconds and the quality will be changed based on currently available internet speed. Use only if you know why you need it', 'ziggeo'); ?></label>
		<?php	
	}

	function ziggeo_a_s_e_webrtc_streaming_when_needed() {
		$options = get_option('ziggeo_video');

		$webrtc_streaming_needed_on = ' selected ';
		$webrtc_streaming_needed_off = '';

		if(isset($options['webrtc_streaming_needed']) && $options['webrtc_streaming_needed'] == 'off') {
			$webrtc_streaming_needed_on = '';
			$webrtc_streaming_needed_off = ' selected ';
		}
		?>
		<select id="ziggeo_webrtc_streaming_needed" name="ziggeo_video[webrtc_streaming_needed]">
			<option value="true"<?php echo $webrtc_streaming_needed_on; ?>>Turn On</option>
			<option value="false"<?php echo $webrtc_streaming_needed_off; ?>>Turn off</option>
		</select>
		<label for="ziggeo_webrtc_streaming_needed"><?php _e('WebRTC Streaming only for browsers that otherwise do not support WebRTC (browsers with specific/incomplete WebRTC implementations of WebRTC)', 'ziggeo'); ?></label>
		<?php	
	}

	/*
	//@ADD - set for next version
	function ziggeo_a_s_e_private_token_field() {
		$options = get_option('ziggeo_video');

		if(!isset($options['p_token']) ) {
			$options['p_token'] = '';
		}

		?>
		<input id="ziggeo_app_ptoken" name="ziggeo_video[p_token]" size="50" type="text"
			placeholder="<?php _ex('Your private token goes here', 'placeholder for private token', 'ziggeo'); ?>"
			value="<?php echo $options['p_token']; ?>" />
		<?php
	}

	function ziggeo_a_s_e_encryption_token_field() {
		$options = get_option('ziggeo_video');

		if(!isset($options['e_token']) ) {
			$options['e_token'] = '';
		}

		?>
		<input id="ziggeo_app_etoken" name="ziggeo_video[e_token]" size="50" type="text"
			placeholder="<?php _ex('Your encryption token goes here', 'placeholder for encryption token', 'ziggeo'); ?>"
			value="<?php echo $options['e_token']; ?>" />
		<?php
	}
	*/

	//Sync field allowing everyone to sync the templates that are in files with templates that are in DB.
	// It uses addition approach, so if some are missing in one, they will be added to it, while none would be removed
	// example file has A and D templates, and DB has B, C, D and F templates. After sync both will have A, B, C, D, F templates
	// If one exists, in both, the DB version will be saved in both
	function ziggeo_a_s_e_sync_field() {
		?>
		<button id="ziggeo_expert_sync" name="ziggeo_video[expert_sync]" value="sync_now">Sync Now</button>
		<?php
	}


?>