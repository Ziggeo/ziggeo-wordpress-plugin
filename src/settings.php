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
	add_settings_section('ziggeo_video_tabse', '', 'ziggeo_video_tabse_html', 'ziggeo_video'); //for styling purposes end

	//Add sections settings
	//----------------------
		//General section
		add_settings_field('ziggeo_app_token', 'Ziggeo API Token', 'ziggeo_app_token_setting_string', 'ziggeo_video', 'ziggeo_video_main');
		// We could use input placeholder instead of adding it into settings: 'Ziggeo Recorder Config (leave blank for default settings)' - check with Oliver
		add_settings_field('ziggeo_recorder_config', 'Ziggeo recorder config', 'ziggeo_recorder_config_setting_string', 'ziggeo_video', 'ziggeo_video_main');
		add_settings_field('ziggeo_player_config', 'Ziggeo player config', 'ziggeo_player_config_setting_string', 'ziggeo_video', 'ziggeo_video_main');
		add_settings_field('ziggeo_beta', 'Use Ziggeo Beta Player', 'ziggeo_beta_setting_string', 'ziggeo_video', 'ziggeo_video_main');
		add_settings_field('ziggeo_video_comments', 'Disable Video Comments', 'ziggeo_video_comments_string', 'ziggeo_video', 'ziggeo_video_main');
		add_settings_field('ziggeo_text_comments', 'Disable Text Comments', 'ziggeo_text_comments_string', 'ziggeo_video', 'ziggeo_video_main');
		
		//Templates section
		// @IMPORTANT - we must make it respect the previus tags as well
		add_settings_field('ziggeo_templates_id', 'Template ID', 'ziggeo_templates_id_string', 'ziggeo_video', 'ziggeo_video_templates');
		add_settings_field('ziggeo_templates_editor', 'Template Editor', 'ziggeo_templates_editor_string', 'ziggeo_video', 'ziggeo_video_templates');
		add_settings_field('ziggeo_templates_manager', 'Manage your templates', 'ziggeo_templates_manager_string', 'ziggeo_video', 'ziggeo_video_templates');

}

add_action('admin_init', 'ziggeo_admin_init');

//Function to start the tabs
function ziggeo_video_tabss_html() {

	/*
		<span class="ziggeo-tabName">Events</span>
		<span class="ziggeo-tabName">Video Listing</span>
		<span class="ziggeo-tabName">Notifications</span>
		<span class="ziggeo-tabName">Backup/Restore</span>
		<span class="ziggeo-tabName" style="border-top-right-radius: 8px;">Contact us</span>

		<div style="display: none;" class="ziggeo-frame"></div>
	*/
	//Here we print the tabs
	?>
	<br>
	<span id="ziggeo-tab_id_templates" class="ziggeo-tabName" style="border-top-left-radius: 8px;" onclick="ziggeo_changeTab('templates');">Templates</span>
	<span id="ziggeo-tab_id_general" class="ziggeo-tabName selected" onclick="ziggeo_changeTab('general');">General</span>
	<?php 
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
		<p>Welcome to templates - an easy way for you to set up the ziggeo codes which you can then call from any post or page with a simple shortcode with the template ID, while everything is saved and handled for you by Ziggeo plugin.</p>
		<p>You can start from the default shortcode and work your way from it, or choose one that is pre-set with some specific options</p>
		<p>There are few values that you will use:
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
		<input id="ziggeo_templates_id" name="ziggeo_video[templates_id]" size="50" type="text" placeholder="Give the temaplate any name you wish here" value="" />
		<?php
	}

	//This shows textarea for templates editing, but also shows the available parameters / attributes that people can use on their template as well as the select field to select our starting template
	function ziggeo_templates_editor_string() {
		//When we load the page it should be empty, it should only have values once it is being saved (but does not need to)..
		
		// We are showing the list of available templates to start from
		?>
		<label for="ziggeo_shorttags_list">Select the template base</label>
		<select id="ziggeo_shorttags_list" onchange="ziggeo_templates_change(this);">
			<option value="[ziggeo">Default</option>
			<option value="[ziggeoplayer">Ziggeo Player</option>
			<option value="[ziggeorecorder">Ziggeo Recorder</option>
			<option value="[ziggeorerecorder">Ziggeo ReRecorder</option>
			<option value="[ziggeovideowall">Ziggeo VideoWall</option>
			<option value="[ziggeouploader">Ziggeo Uploader</option>
			<option value="[ziggeoform">Ziggeo Form</option>
		</select>
		
		<?php //button would make more sense, but it would submit the form on click (its default action) and that is not what we want.. ?>
		<span id="ziggeo_templates_turn_to_new" style="display:none;"  onclick="ziggeo_templates_turn_into_new();">Turn into new</span>
		
		<?php //@TODO - transfer the option for beta here - so that if it is beta, we show the right attributes, otherwise hide the same ?>
		<br><br>

		<?php //The actual template body that we will save ?>
		<textarea id="ziggeo_templates_editor" name="ziggeo_video[templates_editor]" rows="11" cols="50"></textarea>

		<?php //The list of parameters to use in templates ?>
		
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
			<dt class="beta" data-equal="=''">ba-video</dt>
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
				<dd>Boolean value to automatically crop videos to specific resolution (this cuts all the parts that are bigger than set resolution)</dd>
			<dt class="record rerecord" data-equal="">auto_pad</dt>
				<dd>Boolean value to automatically add black surface padding if video does not match set resolution</dd>
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
			<dt class="beta" data-equal="=''">ba-theme</dt>
				<dd>String value of the name of the theme that you wish to have applied to your player</dd>
		</dl>
		<br><hr>
		<?php
	}

	//This function build the interface that will help us show and manage the templates.
	//It will show a list of templates and over each, at the top right corner there should be options to edit and remove the same.
	function ziggeo_templates_manager_string() {
		//@TODO - we need to first create templates parsing function which we would call at this point to get all of the templates.
		// We would always skip the default templates
		//SAMPLE CODE
		?>
		<div>
			<ul class="ziggeo-manage_list">
				<?php
					$file = ZIGGEO_ROOT_PATH . 'userData/custom_templates.php';

					$list = ziggeo_file_read($file);
					foreach($list as $template => $value)
					{
						?><li><?php echo $template; ?> <span class="delete">x</span><span class="edit" data-template="<?php echo $value; ?>">edit</span></li><?php
					}
				?>
				<?php //Edit should do //document.location += "#ziggeo_editing" while edit should do confim() ?>
			</ul>
			<?php //We use this to help us see what action we need to make.. if edit, or delete, we store the old ID into its value, while it is empty if we create new ?>
			<input type="hidden" id="ziggeo_templates_manager" name="ziggeo_video[templates_manager]" value="">
		</div>
		<?php
	}
	// Admin panel requirements
	//+1. <p> description of what it is for
	//+2. <select> list available shorttags (dropdown would be best for this) - values get inserted into textarea as they click on the same
	//+3. <dl> list of parameters/attributes that they can use - clicking on the same adds them to textarea maybe?
	//+4. <input> to capture the id of the template //ID and Name would be same, they can name it any way they wish, we just call it ID
	//+5. <textarea> to capture the customized shortcode
	//+6. <select> list of all existing templates.
//7. <button> options to {edit}, {delete} and {create} templates * might add confusion as to what they do.. so I think that it might be better to stick with one save button.



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

	//@OLD - will be replaced with templates, but still should be respected. The values from here should be turned into the first RECORDER template!
	function ziggeo_recorder_config_setting_string() {
		$options = get_option('ziggeo_video');

		if(!isset($options['recorder_config']) )	{ $options['recorder_config'] = ''; }

		?>
		<input id="ziggeo_recorder_config" name="ziggeo_video[recorder_config]" size="50" type="text" placeholder="Ziggeo Recorder Config (leave blank for default settings)" value="<?php echo $options['recorder_config']; ?>" />
		<?php
	}

	//@OLD - will be replaced with templates, but still should be respected. The values from here should be turned into the first PLAYER template!
	function ziggeo_player_config_setting_string() {
		$options = get_option('ziggeo_video');

		if(!isset($options['player_config']) )	{ $options['player_config'] = ''; }

		?>
		<input id="ziggeo_player_config" name="ziggeo_video[player_config]" size="50" type="text" placeholder="Ziggeo Player Config (leave blank for default settings)" value="<?php echo $options['player_config']; ?>" />
		<?php
	}

	//beta is currently used to show beta player. We should make it possible to choose beta player and recorder at some point, and will need to capture this @OLD value
	function ziggeo_beta_setting_string() {
		$options = get_option('ziggeo_video');

		if(!isset($options['beta']) )	{ $options['beta'] = ''; }

		?>
		<input id="ziggeo_beta" name="ziggeo_video[beta]" type="checkbox" value="1" <?php echo checked( 1, $options['beta'], false ); ?> />
		<?php
	}

	//Allows us to select if video comments are accepted on a post where comments are enabled
	function ziggeo_video_comments_string() {
		$options = get_option('ziggeo_video');

		if(!isset($options['disable_video_comments']) )	{ $options['disable_video_comments'] = ''; }

		?>
		<input id="ziggeo_video_comments" name="ziggeo_video[disable_video_comments]" type="checkbox" value="1" <?php echo checked( 1, $options['disable_video_comments'], false ); ?> />
		<?php	
	}

	//Allows us to set so that text comments are available or disabled. Useful if one wants to have only video comments. Applied only if comments are enabled.
	function ziggeo_text_comments_string() {
		$options = get_option('ziggeo_video');

		if(!isset($options['disable_text_comments']) )	{ $options['disable_text_comments'] = ''; }

		?>
		<input id="ziggeo_text_comments" name="ziggeo_video[disable_text_comments]" type="checkbox" value="1" <?php echo checked( 1, $options['disable_text_comments'], false ); ?> />
		<?php
	}

//@TODO - we should change this to do some validation
function ziggeo_video_validate($input) {
	$newInput = $input;

	//We will first grab the old values, then add to them, or simply replace them where needed..
	$options = get_option('ziggeo_video');
	
	//List of all options that we accept
	$allowed_options = array(
		//templates tab
			'templates_id' => true, 'templates_editor' => true, 'templates_manager' => true,
		//general tab
			'token' => true, 'recorder_config' => true, 'player_config' => true, 'beta' => true, 'disable_video_comments' => true, 'disable_text_comments' => true
	);

	//Going through all updated settings so that we can update all that need to be so
	foreach($input as $option => $value)
	{
		if(isset($allowed_options[$option]))
		{	
			$options[$option] = $value;
		}
	}

	if( isset($options['templates_editor']) && $options['templates_editor'] !== '' )
	{
		//Lets check if templates_editor code ends with ] or not.. if not, we need to add it, since customers might forget adding it.
		if( substr( $options['templates_editor'], -1) !== "]" )	{ $options['templates_editor'] .= ']'; }

		//We should check what is the action..
		//add new
		if( !isset($options['templates_manager']) || $options['templates_manager'] === '' )
		{
			//Templates Editor value gets saved in a bit different manner, together with the ID.. We need to keep these two clean each time
			// instead we save them into a new file as JSON, but we must make sure that such file does not exist currently.
			ziggeo_templates_add( $options['templates_id'], $options['templates_editor']);
		}
		//edit old
		elseif( isset($options['templates_manager']) && $options['templates_manager'] !== '' )
		{
			//old ID, new ID, template structure
			ziggeo_templates_update($options['templates_manager'], $options['templates_id'] , $options['templates_editor']);
		}

		unset( $options['templates_editor'], $options['templates_id'] );

		//--------------------------------------------------------

		//Check if templates are added
		if( isset( $options['templates_id'] ) ) {

			//@TODO - only as a fallback
			//$form_fields = array ($options['templates_editor'], $options['templates_id']);
			//$content = array ($options['templates_editor'], $options['templates_id']);
			//ziggeo_file_WP_prepare('write', $form_fields, 'custom_templates.php', $content);
		}
		//check if events are added
		//if not, just do nothing..
	}
	//Should we delete template?
	elseif( isset($options['templates_manager']) && $options['templates_manager'] !== '' ) {
		ziggeo_templates_remove($options['templates_manager']);
	}

	$newInput = $options;

	return $newInput;
}

function ziggeo_admin_add_page() {
	add_options_page('Ziggeo Video', 'Ziggeo Video', 'manage_options', 'ziggeo_video', 'ziggeo_settings_page');
}

add_action('admin_menu', 'ziggeo_admin_add_page');

function ziggeo_settings_page() {
	include_once(dirname(__FILE__) . "/settings_page.php");
}