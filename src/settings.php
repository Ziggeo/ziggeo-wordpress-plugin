<?php
//Checking if WP is running or if this is a direct call..
defined('ABSPATH') or die();


function ziggeo_admin_init() {
	//Register settings
	register_setting('ziggeo_video', 'ziggeo_video', 'ziggeo_video_validate');

	//Add all sections in order of desired apperance
	add_settings_section('ziggeo_video_tabss', '', 'ziggeo_video_tabss_html', 'ziggeo_video'); //for styling purposes start
	add_settings_section('ziggeo_video_templates', '', 'ziggeo_video_templates_text', 'ziggeo_video'); //templates tab
	add_settings_section('ziggeo_video_main', '', 'ziggeo_video_general_text', 'ziggeo_video'); //general tab
	add_settings_section('ziggeo_video_contact', '', 'ziggeo_video_contact_text', 'ziggeo_video'); //contact us tab
	add_settings_section('ziggeo_video_tabse', '', 'ziggeo_video_tabse_html', 'ziggeo_video'); //for styling purposes end

	//Add sections settings
	//----------------------
		//General section
		add_settings_field('ziggeo_app_token', 'Ziggeo API Token', 'ziggeo_app_token_setting_string', 'ziggeo_video', 'ziggeo_video_main');
		add_settings_field('ziggeo_comments_html', '', 'ziggeo_comments_html', 'ziggeo_video', 'ziggeo_video_main');
			add_settings_field('ziggeo_video_comments', 'Disable Video Comments', 'ziggeo_video_comments_string', 'ziggeo_video', 'ziggeo_video_main');
			add_settings_field('ziggeo_video_and_text', 'Require Video and have text as optional', 'ziggeo_video_and_text_comments_string', 'ziggeo_video', 'ziggeo_video_main');
			add_settings_field('ziggeo_video_comments_template_recorder', 'Video Comments recorder template', 'ziggeo_video_comments_template_recorder_string', 'ziggeo_video', 'ziggeo_video_main');
			add_settings_field('ziggeo_video_comments_template_player', 'Video Comments player template', 'ziggeo_video_comments_template_player_string', 'ziggeo_video', 'ziggeo_video_main');
			add_settings_field('ziggeo_text_comments', 'Disable Text Comments', 'ziggeo_text_comments_string', 'ziggeo_video', 'ziggeo_video_main');
		add_settings_field('ziggeo_global_html', '', 'ziggeo_global_html', 'ziggeo_video', 'ziggeo_video_main');
			add_settings_field('ziggeo_recorder_config', 'Ziggeo Recorder Config', 'ziggeo_recorder_config_setting_string', 'ziggeo_video', 'ziggeo_video_main');
			add_settings_field('ziggeo_player_config', 'Ziggeo Player Config', 'ziggeo_player_config_setting_string', 'ziggeo_video', 'ziggeo_video_main');
			add_settings_field('ziggeo_beta', 'Use Ziggeo Beta Player', 'ziggeo_beta_setting_string', 'ziggeo_video', 'ziggeo_video_main');

		//Templates section
		add_settings_field('ziggeo_templates_id', 'Template ID', 'ziggeo_templates_id_string', 'ziggeo_video', 'ziggeo_video_templates');
		add_settings_field('ziggeo_templates_editor', 'Template Editor', 'ziggeo_templates_editor_string', 'ziggeo_video', 'ziggeo_video_templates');
		add_settings_field('ziggeo_templates_manager', 'Manage your templates', 'ziggeo_templates_manager_string', 'ziggeo_video', 'ziggeo_video_templates');

		//Contact us section
		add_settings_field('ziggeo_contact_ziggeo', 'Contact Us on our platform', 'ziggeo_contact_ziggeo_string', 'ziggeo_video', 'ziggeo_video_contact');
		add_settings_field('ziggeo_contact_wp', 'Contact Us on WordPress', 'ziggeo_contact_wp_string', 'ziggeo_video', 'ziggeo_video_contact');

}

add_action('admin_init', 'ziggeo_admin_init');

//Function to start the tabs
function ziggeo_video_tabss_html() {

	/*
		<span class="ziggeo-tabName">Events</span>
		<span class="ziggeo-tabName">Video Listing</span>
		<span class="ziggeo-tabName">Notifications</span>
		<span class="ziggeo-tabName">Backup/Restore</span>

		<div style="display: none;" class="ziggeo-frame"></div>
	*/
	//Here we print the tabs
	?>
	<br>
	<span id="ziggeo-tab_id_templates" class="ziggeo-tabName" style="border-top-left-radius: 8px;" onclick="ziggeo_changeTab('templates');">Templates</span>
	<span id="ziggeo-tab_id_general" class="ziggeo-tabName selected" onclick="ziggeo_changeTab('general');">General</span>
	<span id="ziggeo-tab_id_contact" class="ziggeo-tabName" style="border-top-right-radius: 8px;" onclick="ziggeo_changeTab('contact');">Contact us</span>
	<?php

	// @NOTE
	// To show a tab frame, we use:
	//<div class="ziggeo-frame" style="display: none;" id="ziggeo-tab_{section}">
	//If there are any frames before it we need to add </div> before the frame to close the previous one.
}

//Function to close the last tab frame
function ziggeo_video_tabse_html() {
	/*We just need to close the last tab frame.. */ ?>
	</div>
	<?php 
}



// - TEMPLATES - tab fields functions
//----------------------------------

//Function that starts the tab frames, starting with templates
function ziggeo_video_templates_text() {
	$options = get_option('ziggeo_video');

	//We show description and the list in the select option, since both are used to help customers, not to capture and handle any values
	?>
	<div class="ziggeo-frame" style="display: none;" id="ziggeo-tab_templates">
		<p>Welcome to templates - an easy way for you to set up the Ziggeo codes; you can call them from any post or page with a simple shortcode with the template ID, while everything is saved and handled for you by Ziggeo plugin.</p>
		<p>You can start from the default shortcode and work your way from it, or choose one that is pre-set with some specific options</p>
		<p>The different parameters have the following value types:
			<ol>
				<li>Integer - after equal you simply add the number, no quotes</li>
				<li>Boolean - you can just remove parameter (which equals to false) or add it and will be seen as true</li>
				<li>String - value holding numbers, characters and spaces (as needed), which must be enclosed in quotation marks on both sides (on start and end)</li>
				<li>Array - similar to string as it needs to be enclosed with quotation marks, but you can select multiple options, separating them with comma</li>
				<li>JSON - data formated as per JSON specification</li>
			</ol>
		</p>
		<br id="ziggeo_editing">
	<?php
}

	//Field for ID of the template that we are editing or creating
	function ziggeo_templates_id_string() {
		//On load, we do not need to load any data, just have the box empty. When we get back the response, that is when we need to capture the data..
		?>
		<input id="ziggeo_templates_id" name="ziggeo_video[templates_id]" size="50" type="text" placeholder="Give the template any name you wish here" value="" />
		<?php
	}

	//This shows textarea for templates editing, but also shows the available parameters / attributes that people can use on their template as well as the select field to select our starting template
	function ziggeo_templates_editor_string() {
		//When we load the page the editor textarea should be empty, it should only have values once it is being saved (but does not need to)..

		//We will need options for beta check
		$options = get_option('ziggeo_video');

		// We are showing the list of available templates to start from
		?>
		<label for="ziggeo_shorttags_list">Select the template base</label>
		<select id="ziggeo_shorttags_list" onchange="ziggeo_templates_change(this);">
			<option value="[ziggeo">Default</option>
			<option value="[ziggeoplayer">Ziggeo Player</option>
			<option value="[ziggeorecorder">Ziggeo Recorder</option>
			<option value="[ziggeorerecorder">Ziggeo ReRecorder</option>
			<?php // <option value="[ziggeovideowall">Ziggeo VideoWall</option> ?>
			<option value="[ziggeouploader">Ziggeo Uploader</option>
			<?php // <option value="[ziggeoform">Ziggeo Form</option> ?>
		</select>
		
		<?php //button would make more sense, but it would submit the form on click (its default action) and that is not what we want.. ?>
		<span id="ziggeo_templates_turn_to_new" style="display:none;"  onclick="ziggeo_templates_turn_into_new();">Turn into new</span>

		<?php //Use beta option so that they can set up templates with and without being beta.. This is only shown if beta is disabled in global
		if( isset($options['beta']) && $options['beta'] !== "1" ) {
			?>
			<input id="ziggeo_turn_to_beta" type="checkbox" style="display:none;" value="0">
			<label for="ziggeo_turn_to_beta" onclick="ziggeo_templates_turn_into_beta();" title="Even if you have the default option turned off, you can enable each template into beta by itself">Turn into beta</label>
			<?php
		}

		?>
		<br><br>

		<?php //The actual template body that we will save ?>
		<textarea id="ziggeo_templates_editor" name="ziggeo_video[templates_editor]" rows="11" cols="50">[ziggeo </textarea>

		<?php //The list of parameters to use in templates ?>

		<div id="ziggeo-params-holder">
			<b>Ziggeo parameters that you can use in templates (Click to add)</b>
			<dl class="ziggeo-params">
				<dt class="play record rerecord" data-equal="=">width</dt>
					<dd>Integer value representing the width of <span title="ziggeo player or recorder that you are setting this for">embedding</span>. This will not change the width of recording, just of the video screen</dd>
				<dt class="play record rerecord" data-equal="=">height</dt>
					<dd>Integer value representing the height of <span title="ziggeo player or recorder that you are setting this for">embedding</span>. This will not change the height of recording, just of the video screen</dd>
				<dt class="record rerecord" data-equal="=">recording_width</dt>
					<dd>Integer representing the width of recording captured</dd>
				<dt class="record rerecord" data-equal="=">recording_height</dt>
					<dd>Integer representing the width of recording captured</dd>
				<dt data-equal="">responsive</dt>
					<dd>Boolean value that allows you to make embedding capture the full size of the bounding box (applyed on load only)</dd>
				<dt data-equal="=">popup_width</dt>
					<dd>Integer value setting up the width of the popup holding the embedding</dd>
				<dt data-equal="=">popup height</dt>
					<dd>Integer value setting up the height of the popup holding the embedding</dd>
				<dt data-equal="=''">video</dt>
					<dd>String representation of a video token or video key</dd>
				<dt class="record rerecord" data-equal="">face_outline</dt>
					<dd>Boolean value setting if face outline would be shown on the video or not</dd>
				<dt data-equal="''">stream</dt>
					<dd>String representing stream token or stream key</dd>
				<dt class="default" data-equal="=''">modes</dt>
					<dd>Array value determining how the embedding is used. Possible values are "recorder", "player", "rerecorder" For more modes, separate values with comma</dd>
				<dt class="record rerecord" data-equal="=''">tags</dt>
					<dd>Array holding the tags that the new video should be associated with. By default it will add "wordpress, {username}"</dd>

				<dt class="play record rerecord" data-equal="=''">effect_profile</dt>
					<dd>Array allowing you to select what effects to be applied to recorder, or which video stream to get when playing (the one with the same effects applied)</dd>
				<dt class="record rerecord" data-equal="=''">data</dt>
					<dd>JSON formatted data that you wish to pass with the video</dd>
				<dt class="default" data-equal="=''">perms</dt>
					<dd>Array value with video permissions that you could apply: "<span title="Enables uploading of videos for your customer">allowupload</span>", "<span title="Disables recoring of video">forbidrecord</span>", "<span title="Disables switching between uploading and recording">forbidswitch</span>", "<span title="Disables rerecording completely">forbidrerecord</span>", "<span title="Overwrites the video if a video with the same key already exists">forceoverwrite</span>"</dd>
				<dt class="record rerecord" data-equal="">disable_first_screen</dt>
					<dd>Boolean value to disable recorder's initial screen</dd>
				<dt class="record rerecord" data-equal="">disable_device_test</dt>
					<dd>Boolean value to disable the camera and microphone tests prior to recording</dd>
				<dt class="record rerecord" data-equal="">disable_timer</dt>
					<dd>Boolean value to hide the duration of recording on the recorder</dd>
				<dt class="record rerecord" data-equal="">disable_snapshots</dt>
					<dd>Disables the selection of snapshots after the recording</dd>
				<dt class="record rerecord" data-equal="">hide_rerecord_on_snapshots</dt>
					<dd>Boolean value to hide rerecord option while picking snapshots</dd>
				<dt class="record rerecord" data-equal="">auto_crop</dt>
					<dd>Boolean value to automatically crop videos to specific resolution (this cuts all the parts that are bigger than set resolution) - can only be applied to recorder</dd>
				<dt class="record rerecord" data-equal="">auto_pad</dt>
					<dd>Boolean value to automatically add black surface padding if video does not match set resolution - can only be applied to recorder</dd>
				<dt class="play record rerecord" data-equal="=''">key</dt>
					<dd>String that tells recorder under which key the video should be saved under</dd>
				<dt class="play record rerecord" data-equal="=">limit</dt>
					<dd>Integer value limiting the number of seconds that video / recording can be</dd>
			</dl>
			<dl class="ziggeo-params">
				<dt data-equal="=">countdown</dt>
					<dd>Integer value to set when the recording should start after selectig same. Defaults to 3 seconds. Use 0 to disable countdown</dd>
				<dt data-equal="=''">input_bind</dt>
					<dd>String value representing form field name to which video token would be passed over</dd>
				<dt data-equal="=''">form_accept</dt>
					<dd>String value holding jQuery selector to disable form submission until video is created</dd>
				<dt data-equal="=''">id</dt>
					<dd>String value representing desired ID of embedding element so that it can be looked up using JavaScript code</dd>
				<dt data-equal="">immediate_playback</dt>
					<dd>Boolean value to tell if the video should start playing right away after recording</dd>
				<dt data-equal="">autoplay</dt>
					<dd>Boolean value to indicate if the video should automatically play back in player</dd>
				<dt data-equal="">loop</dt>
					<dd>Boolean value to set if you wish for the player to play the video indefinitely</dd>
				<dt data-equal="=''">server_auth</dt>
					<dd>String representing authorization token retrieved from the server side</dd>
				<dt data-equal="=''">client_auth</dt>
					<dd>String representing authorization token for use on client side</dd>
				<dt data-equal="=">rerecordings</dt>
					<dd>Integer value indicating how many rerecordings you would allow to be made</dd>
				<dt data-equal="=">expiration_days</dt>
					<dd>Integer value to set after how many days you want to delete the recorded video (by defaul, never)</dd>
				<dt data-equal="=''">video_profile</dt>
					<dd>Strig value holding key or token of your video profile that you want to use</dd>

				<dt data-equal="=''">meta_profile</dt>
					<dd>Strig value holding key or token of your meta profile that you want to use</dd>
				<dt data-equal="=">stream_width</dt>
					<dd>Integer value setting the optimal width of the stream</dd>
				<dt data-equal="=">stream_height</dt>
					<dd>Integer value setting the optimal height of the stream</dd>
				<dt data-equal="=''">title</dt>
					<dd>String value to set title of the video being recorded</dd>
				<dt data-equal="=''">description</dt>
					<dd>String value to set the description of the video</dd>
				<dt data-equal="=''">allowed_extensions</dt>
					<dd>String value to limit the uploads to only specific extensions (all allowed by default)</dd>
				<dt data-equal="=">default_image_selector</dt>
					<dd>Float (integer with decimal point) value to indicate the default image selector</dd>
				<dt data-equal="">enforce_duration</dt>
					<dd>Boolean value to reject videos if they are too long.</dd>
				<dt data-equal="=">limit_upload_size</dt>
					<dd>Integer value to limit the size of videos being uploaded in bytes (no limit by default)</dd>
				<dt data-equal="">performance_warning</dt>
					<dd>Boolean value to set a warning to be shown if framerate is too low</dd>
				<dt data-equal="">recover_streams</dt>
					<dd>Boolean value to set the attempt to recover videos feature if your customers close their browser while recording</dd>
				<dt data-equal="">nofullscreen</dt>
					<dd>Boolean value to disable fullscreen option in player</dd>
				<dt class="beta" data-equal="">stretch</dt>
					<dd>Boolean value to set the beta player to be responsive (this happens in realtime)</dd>
				<dt class="beta" data-equal="=''">theme</dt>
					<dd>String value of the name of the theme that you wish to have applied to your player</dd>
			</dl>
		</div>
		<br style="clear: both;">
		<?php
	}

	//This function build the interface that will help us show and manage the templates.
	//It will show a list of templates and over each, at the top right corner there should be options to edit and remove the same.
	function ziggeo_templates_manager_string() {
		?>
		<div>
			<ul class="ziggeo-manage_list">
				<?php
					$list = ziggeo_templates_index();
					if($list) {
						foreach($list as $template => $value)
						{
							?><li><?php echo $template; ?> <span class="delete">x</span><span class="edit" data-template="<?php echo $value; ?>">edit</span></li><?php
						}						
					}
					else {
						?><li>No templates yet, please create one</li><?php
					}
				?>
				<?php //Edit should do //document.location += "#ziggeo_editing" while edit should do confim() ?>
			</ul>
			<?php //We use this to help us see what action we need to make.. if edit, or delete, we store the old ID into its value, while it is empty if we create new ?>
			<input type="hidden" id="ziggeo_templates_manager" name="ziggeo_video[templates_manager]" value="">
		</div>
		<?php
	}


// - GENERAL - tab fields functions
//----------------------------------

//Shows instructions on how to manually get API app token and starts the general tab frame (closing the one before it)
function ziggeo_video_general_text() {

	$options = get_option('ziggeo_video');

	?>
	</div>
	<div class="ziggeo-frame" id="ziggeo-tab_general">
	<?php

	//Only show the instructions if the token is not already set
	if( !isset($options, $options['token']) )
	{
		?>
		<p>
			Get your Ziggeo API application token <a href="http://ziggeo.com" target="_blank">from here</a>. <br>
			<span>* Login to your account -> App -> Overview</span>
		</p>
		<?php
	}
}

	//Token input
	function ziggeo_app_token_setting_string() {
		$options = get_option('ziggeo_video');

		if(!isset($options['token']) )	{ $options['token'] = ''; }

		?>
		<input id="ziggeo_app_token" name="ziggeo_video[token]" size="50" type="text" placeholder="Your app token goes here" value="<?php echo $options['token']; ?>" />
		<?php
	}

	//beta is currently used to show beta player. We should make it possible to choose beta player and recorder at some point, and will need to capture this @OLD value
	function ziggeo_beta_setting_string() {
		$options = get_option('ziggeo_video');

		if(!isset($options['beta']) )	{ $options['beta'] = ''; }

		?>
		<input id="ziggeo_beta" name="ziggeo_video[beta]" type="checkbox" value="1" <?php echo checked( 1, $options['beta'], false ); ?> />
		<label for="ziggeo_beta">If you select this option, each template will try to use beta version ( you will still be able to make decisions per template )</label>
		<?php
	}

	//Used for styling purposes only.
	function ziggeo_comments_html() {
		?>
		<hr class="ziggeo_linespacer">
		<span class="ziggeo-subframe">Comments options</span>
		<?php
	}

	//Allows us to select if video comments are accepted on a post where comments are enabled
	function ziggeo_video_comments_string() {
		$options = get_option('ziggeo_video');

		if(!isset($options['disable_video_comments']) )	{ $options['disable_video_comments'] = ''; }

		?>
		<input id="ziggeo_video_comments" name="ziggeo_video[disable_video_comments]" type="checkbox" value="1" <?php echo checked( 1, $options['disable_video_comments'], false ); ?> />
		<label for="ziggeo_video_comments">By default the comments will get activated with the feature to add videos as comments (check this to disable it)</label>
		<?php	
	}

	//Show video (and it is required) and the WordPress comment field next to it
	function ziggeo_video_and_text_comments_string() {
		$options = get_option('ziggeo_video');

		if(!isset($options['video_and_text']) )	{ $options['video_and_text'] = ''; }

		?>
		<input id="ziggeo_video_and_text" name="ziggeo_video[video_and_text]" type="checkbox" value="1" <?php echo checked( 1, $options['video_and_text'], false ); ?> />
		<label for="ziggeo_video_and_text">Set video comment to be required, but allow your visitors to leave some text for you as well (next to video)</label>
		<?php	
	}

	//Recorder template option to be used in comments
	function ziggeo_video_comments_template_recorder_string() {
		$options = get_option('ziggeo_video');

		if( !isset($options, $options['comments_recorder_template']) ) { $options['comments_recorder_template'] = false; }
		?>
		<select id="ziggeo_video_comments_template_recorder" name="ziggeo_video[comments_recorder_template]">
			<option value="">Default</option>
		<?php
			$list = ziggeo_templates_index();
			if($list) {
				foreach($list as $template => $value)
				{
					if( $template === $options['comments_recorder_template'] ) {
						?><option value="<?php echo $template; ?>" selected><?php echo $template; ?></option><?php
					}
					else{
						?><option value="<?php echo $template; ?>"><?php echo $template; ?></option><?php
					}
				}				
			}
		?>
		</select>
		<label for="ziggeo_video_comments_template_recorder">This template will be applied to all comment recorders (it can be uploader template, recorder, ... the choice is yours)</label>
		<?php
	}

	//Player template option to be used in comments
	function ziggeo_video_comments_template_player_string() {
		$options = get_option('ziggeo_video');
		if( !isset($options, $options['comments_player_template']) ) { $options['comments_player_template'] = false; }

		?>
		<select id="ziggeo_video_comments_template_player" name="ziggeo_video[comments_player_template]">
			<option value="">Default</option>
		<?php
			$list = ziggeo_templates_index();
			if($list) {
				foreach($list as $template => $value)
				{
					if( $template === $options['comments_player_template'] ) {
						?><option value="<?php echo $template; ?>" selected><?php echo $template; ?></option><?php						
					}
					else {
						?><option value="<?php echo $template; ?>"><?php echo $template; ?></option><?php						
					}
				}				
			}
		?>
		</select>
		<label for="ziggeo_video_comments_template_player">This template will be applied to all comment players (player, rerecorder maybe? ... the choice  is yours )</label>
		<?php
	}

	//Allows us to set so that text comments are available or disabled. Useful if one wants to have only video comments. Applied only if comments are enabled.
	function ziggeo_text_comments_string() {
		$options = get_option('ziggeo_video');

		if(!isset($options['disable_text_comments']) )	{ $options['disable_text_comments'] = ''; }

		?>
		<input id="ziggeo_text_comments" name="ziggeo_video[disable_text_comments]" type="checkbox" value="1" <?php echo checked( 1, $options['disable_text_comments'], false ); ?> />
		<label for="ziggeo_text_comments">Want to have video comments only? Check this to set it as such ( leave unchecked to allow<span id="ziggeo-comments_video_checker"> video and</span> text comments ).</label>
		<?php
	}


	//Used for styling only
	function ziggeo_global_html() {
		?>
		<hr class="ziggeo_linespacer">
		<span class="ziggeo-subframe">Global & Default options</span>
		<?php
	}

	//Used as defaults - fallbacks :)
	function ziggeo_recorder_config_setting_string() {
		$options = get_option('ziggeo_video');

		if(!isset($options['recorder_config']) )	{ $options['recorder_config'] = ''; }

		?>
		<input id="ziggeo_recorder_config" name="ziggeo_video[recorder_config]" size="50" type="text" placeholder="Ziggeo Recorder Config (leave blank for default settings)" value="<?php echo $options['recorder_config']; ?>" />
		<?php
	}

	//Used as defaults - fallbacks :)
	function ziggeo_player_config_setting_string() {
		$options = get_option('ziggeo_video');

		if(!isset($options['player_config']) )	{ $options['player_config'] = ''; }

		?>
		<input id="ziggeo_player_config" name="ziggeo_video[player_config]" size="50" type="text" placeholder="Ziggeo Player Config (leave blank for default settings)" value="<?php echo $options['player_config']; ?>" />
		<?php
	}



// - contact us - tab fields functions
//-------------------------------------

//Function to show the frame of our tab
function ziggeo_video_contact_text() {
	?>
	</div>
	<div class="ziggeo-frame" style="display: none;" id="ziggeo-tab_contact">
		<p><i>Regardless where your question is posted, we are happy to assist with the same, so all you need to do is ask</i></p>
	<?php
}
	//Function to show the contact details on our Zendesk platform.
	function ziggeo_contact_ziggeo_string() {
		?>
		<p>We are using Zendesk to provide assistance with your issues. To contact us there, you should either send an email
		to <a href="mailto:support@ziggeo.com">support@ziggeo.com</a> or simply go to <a href="http://support.ziggeo.com/">our helpdesk</a> where
		you might find the answers to your questions already being answered.</p>
		<?php
	}

	//Function to show the contact instructions for contacting on WordPress itself instead.
	function ziggeo_contact_wp_string() {
		?>
		<p>If you prefer to contact us over WordPress, all you need is to head here: <a href="https://wordpress.org/support/plugin/ziggeo">Ziggeo Plugin Support</a></p>
		<?php
	}


//--- Tab functions end ----

//Function to capture the values submitted by the customer
function ziggeo_video_validate($input) {

	//We will first grab the old values, then add to them, or simply replace them where needed..
	$options = get_option('ziggeo_video');

	//List of all options that we accept
	$allowed_options = array(
		//templates tab
			'templates_id' => true, 'templates_editor' => true, 'templates_manager' => true,
		//general tab
			'token' => true, 'recorder_config' => true, 'player_config' => true, 'beta' => true, 'disable_video_comments' => true, 'disable_text_comments' => true, 'comments_recorder_template' => true, 'comments_player_template' => true, 'video_and_text' => true
	);

	if(is_array($options)) {
		//Going through all updated settings so that we can update all that need to be so
		foreach($options as $option => $value)
		{
			if(isset($input[$option])) {	
				$options[$option] = $input[$option];
				//We have used the option, now lets not have it available any more
				unset($input[$option]);
			}
			else {
				$options[$option] = '';
			}
		}		
	}
	else {
		$options = array ();
	}

	//Now we check if there are any new options that are passed to us and we allow them
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
		return false; //nothing to do here..
	}

	//Lets make sure that if video and text is selected, that video and comment options are not selected (no sense having them disabled and this enabled)
	if( isset($input['video_and_text']) && !empty($input['video_and_text']) ) {
		unset($options['disable_video_comments'], $options['disable_text_comments']);
	}
	elseif( ( isset($input['disable_video_comments']) && !empty($input['disable_video_comments']) ) || ( isset($input['disable_text_comments']) && !empty($input['disable_text_comments']) ) ) {
		unset($options['video_and_text']);
	}
		

	//From this point on, we should not use $input, only $options

	if( isset($options['templates_editor']) && $options['templates_editor'] !== '' && $options['templates_editor'] !== '[ziggeo ' )
	{
		//Lets check if templates_editor code ends with ] or not.. if not, we need to add it, since customers might forget adding it.
		if( substr( $options['templates_editor'], -1) !== "]" )	{ $options['templates_editor'] .= ']'; }

		//We should check what is the action..
		//add new
		if( !isset($options['templates_manager']) || $options['templates_manager'] === '' )
		{
			$idGiven = true;

			//before adding template we need to know that the template name was added, if not, lets just name it for our customer :)
			if( trim($options['templates_id']) === '' ) {
				$options['templates_id'] = "ziggeo_template_" . rand(20, 3000);

				$message = 'We have saved your template, but since Template ID was not given, we have set one up for you! - "' . $options['templates_id'] . '"';
				$idGiven = false;
			}
			//if the template is just a number, it will not work, we need to add it some text at the start
			elseif( is_numeric($options['templates_id']) ) {
				$options['templates_id'] = '_' . $options['templates_id'];
			}

			//Templates Editor value gets saved in a bit different manner, together with the ID.. We need to keep these two clean each time
			// instead we save them into a new file as JSON, but we must make sure that such file does not exist currently.
			if( ziggeo_templates_add( $options['templates_id'], $options['templates_editor']) ) {

				if($idGiven) {
					$message = 'Your template "' . $options['templates_id'] . '" has been successfully created.';
				}

				add_settings_error('ziggeo_templates_manager',
									'template_created',
									$message,
									'updated');					
			}
		}
		//edit old
		elseif( isset($options['templates_manager']) && $options['templates_manager'] !== '' )
		{
			//old ID, new ID, template structure
			if( ziggeo_templates_update($options['templates_manager'], $options['templates_id'] , $options['templates_editor']) ) {
				add_settings_error('ziggeo_templates_manager',
									'template_updated',
									'Your template "' . $options['templates_id'] . '" has been successfully updated.',
									'updated');	
			}
		}

		unset( $options['templates_editor'], $options['templates_id'] );
	}
	//Should we delete template?
	elseif( isset($options['templates_manager']) && $options['templates_manager'] !== '' ) {
		if( ziggeo_templates_remove($options['templates_manager']) ) {
				add_settings_error('ziggeo_templates_manager',
									'template_removed',
									'Your template "' . $options['templates_manager'] . '" has been successfully deleted.',
									'updated');	
			}
	}

	//We are currently showing it up as default, so we should remove it at this point - we do not want it saved
	unset($options['templates_editor']);


	return $options;
}

function ziggeo_admin_add_page() {
	add_options_page('Ziggeo Video', '<img src="' . ZIGGEO_ROOT_URL . '/images/icon.png" style="height: 1em; position: relative; top: 0.1em; padding-right: 0.2em;">Ziggeo Video', 'manage_options', 'ziggeo_video', 'ziggeo_settings_page');
}

add_action('admin_menu', 'ziggeo_admin_add_page');

function ziggeo_settings_page() {
	include_once(dirname(__FILE__) . "/settings_page.php");
}
?>