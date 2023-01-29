<?php

//Checking if WP is running or if this is a direct call..
defined('ABSPATH') or die();


function ziggeo_p_admin_init() {
	//Register settings
	register_setting('ziggeo_video', 'ziggeo_video', 'ziggeo_a_s_validation');

		//Add all sections in order of desired appearance
		// for styling purposes -start-
		add_settings_section('ziggeo_video_tabss', '', 'ziggeo_a_s_tabs_s_html', 'ziggeo_video');
		// general tab
		add_settings_section('ziggeo_video_main', '', 'ziggeo_a_s_g_text', 'ziggeo_video');
		// integrations tab
		add_settings_section('ziggeo_video_integrations', '', 'ziggeo_a_s_i_text', 'ziggeo_video');
		// contact us tab
		add_settings_section('ziggeo_video_contact', '', 'ziggeo_a_s_c_text', 'ziggeo_video');
		//Experts tab
		add_settings_section('ziggeo_video_expert', '', 'ziggeo_a_s_e_text', 'ziggeo_video');
		// for styling purposes -end-
		add_settings_section('ziggeo_video_tabse', '', 'ziggeo_a_s_tabs_e_html', 'ziggeo_video');

	//Add sections settings
	//----------------------

		//-General section-

		// Ziggeo API token
		add_settings_field('ziggeo_app_token',
							__('Ziggeo API APP Token', 'ziggeo'),
							'ziggeo_a_s_g_app_token_field',
							'ziggeo_video',
							'ziggeo_video_main');

		//Set up the language that you want to use by default
		add_settings_field('ziggeo_default_language',
							__('Ziggeo Default language', 'ziggeo'),
							'ziggeo_a_s_g_default_language',
							'ziggeo_video',
							'ziggeo_video_main');

		//comments section
		add_settings_field('ziggeo_comments_html',
							'',
							'ziggeo_a_s_g_comments_html',
							'ziggeo_video',
							'ziggeo_video_main');

			//disables video comments
			add_settings_field('ziggeo_modify_comments',
								__('Modify Comments', 'ziggeo'),
								'ziggeo_a_s_g_modify_comments',
								'ziggeo_video',
								'ziggeo_video_main');

			// Saves the comments form ID
			add_settings_field('ziggeo_comments_form_id',
								__('Comments Form ID', 'ziggeo'),
								'ziggeo_a_s_g_advanced_comments_identifier_form',
								'ziggeo_video',
								'ziggeo_video_main');

			// Saves the comments form ID
			add_settings_field('ziggeo_comments_form_text_id',
								__('Comments Form Text ID', 'ziggeo'),
								'ziggeo_a_s_g_advanced_comments_identifier_text',
								'ziggeo_video',
								'ziggeo_video_main');

			//disables video comments
			add_settings_field('ziggeo_video_comments',
								__('Disable Video Comments', 'ziggeo'),
								'ziggeo_a_s_g_accept_video_comments_field',
								'ziggeo_video',
								'ziggeo_video_main');

			//sets video comment as required and text as optional
			add_settings_field('ziggeo_video_and_text',
								__('Require Video and have text as optional', 'ziggeo'),
								'ziggeo_a_s_g_video_comments_required_with_text',
								'ziggeo_video',
								'ziggeo_video_main');

			//sets the template for the recorder in the comments (allows to pick one of the existing templates)
			add_settings_field('ziggeo_video_comments_template_recorder',
								__('Video Comments recorder template', 'ziggeo'),
								'ziggeo_a_s_g_video_comments_default_recorder_field',
								'ziggeo_video',
								'ziggeo_video_main');

			//sets the template for the player in the comments (allows you to pick the template from existing ones)
			add_settings_field('ziggeo_video_comments_template_player',
								__('Video Comments player template', 'ziggeo'),
								'ziggeo_a_s_g_video_comments_default_player_field',
								'ziggeo_video',
								'ziggeo_video_main');

			//disables text comments
			add_settings_field('ziggeo_text_comments',
								__('Disable Text Comments', 'ziggeo'),
								'ziggeo_a_s_g_accept_video_comments_only_field',
								'ziggeo_video',
								'ziggeo_video_main');

			//useful if video comments are available per specific role (utilizes WP roles)
			add_settings_field('ziggeo_video_comments_roles',
								__('Minimal required permissions for video comments', 'ziggeo'),
								'ziggeo_a_s_g_video_comments_required_roles',
								'ziggeo_video',
								'ziggeo_video_main');

		//Defaults section
		add_settings_field('ziggeo_global_html',
							'',
							'ziggeo_a_s_g_global_html',
							'ziggeo_video',
							'ziggeo_video_main');

			//default recorder parameters
			add_settings_field('ziggeo_recorder_config',
								__('Ziggeo Recorder Config', 'ziggeo'),
								'ziggeo_a_s_g_fallback_recorder_config_field',
								'ziggeo_video',
								'ziggeo_video_main');

			//default player parameters
			add_settings_field('ziggeo_player_config',
								__('Ziggeo Player Config', 'ziggeo'),
								'ziggeo_a_s_g_fallback_player_config_field',
								'ziggeo_video',
								'ziggeo_video_main');

			//Preferred template that should be used by integrations when creating recorder
			add_settings_field('ziggeo_recorder_integrations_default',
								__('Ziggeo Recorder for Integrations', 'ziggeo'),
								'ziggeo_a_s_g_default_integrations_recorder_field',
								'ziggeo_video',
								'ziggeo_video_main');

			//Preferred tempalte that should be used by integrations when they are creating players
			add_settings_field('ziggeo_player_integrations_default',
								__('Ziggeo Player for Integrations', 'ziggeo'),
								'ziggeo_a_s_g_default_integrations_player_field',
								'ziggeo_video',
								'ziggeo_video_main');

		//-VAST section-
			add_settings_field('ziggeo_vast_global',
		                       '',
		                       'ziggeo_a_s_g_vast_global_field',
		                       'ziggeo_video',
		                       'ziggeo_video_main');

			// AdServer URL
			add_settings_field('ziggeo_vast_adserver',
		                       __('VAST 3.0 AdServer URL', 'ziggeo'),
		                       'ziggeo_a_s_g_vast_adserver_field',
		                       'ziggeo_video',
		                       'ziggeo_video_main');

			// skipAfter value (seconds)
			add_settings_field('ziggeo_vast_skipafter',
			                   __('After what time should Skip button be shown?', 'ziggeo'),
			                   'ziggeo_a_s_g_vast_skipafter_field',
			                   'ziggeo_video',
			                   'ziggeo_video_main');

			// muted
			add_settings_field('ziggeo_vast_muted',
			                   __('Should the ad start muted?', 'ziggeo'),
			                   'ziggeo_a_s_g_vast_muted_field',
			                   'ziggeo_video',
			                   'ziggeo_video_main');

			// ad title
			add_settings_field('ziggeo_vast_ad_title',
			                   __('Does your ad have a title?', 'ziggeo'),
			                   'ziggeo_a_s_g_vast_ad_title_field',
			                   'ziggeo_video',
			                   'ziggeo_video_main');

			// ad description
			add_settings_field('ziggeo_vast_ad_description',
			                   __('Does your ad have a description?', 'ziggeo'),
			                   'ziggeo_a_s_g_vast_ad_description_field',
			                   'ziggeo_video',
			                   'ziggeo_video_main');

			// ad ID
			add_settings_field('ziggeo_vast_ad_id',
			                   __('Does your ad have an ID?', 'ziggeo'),
			                   'ziggeo_a_s_g_vast_ad_id_field',
			                   'ziggeo_video',
			                   'ziggeo_video_main');

			// ad advertiser
			add_settings_field('ziggeo_vast_ad_advertiser',
			                   __('Do you want to say who the advertiser is?', 'ziggeo'),
			                   'ziggeo_a_s_g_vast_ad_advertiser_field',
			                   'ziggeo_video',
			                   'ziggeo_video_main');

		//-Integrations tab (lists integrations and allows to turn them on or off)-
		add_settings_field('ziggeo_integration_change',
							'',
							'ziggeo_a_s_i_change_status_field',
							'ziggeo_video',
							'ziggeo_video_integrations');


		//-Contact us section-

		//Email and forum
		add_settings_field('ziggeo_contact_ziggeo',
							__('Contact us on our platform', 'ziggeo'),
							'ziggeo_a_s_c_email_forum_field',
							'ziggeo_video',
							'ziggeo_video_contact');

		//wp support pages
		add_settings_field('ziggeo_contact_wp',
							__('Contact us on WordPress', 'ziggeo'),
							'ziggeo_a_s_c_wp_forum_field',
							'ziggeo_video',
							'ziggeo_video_contact');

		//Zendesk (Zopim) chat pages
		add_settings_field('ziggeo_contact_chat',
							__('Lets chat', 'ziggeo'),
							'ziggeo_a_s_c_zchat_field',
							'ziggeo_video',
							'ziggeo_video_contact');

		//-Expert section-

		//Is this development location (and you want things to pop up in console)?
		add_settings_field('ziggeo_dev_mode',
							__('Ziggeo Development Mode', 'ziggeo'),
							'ziggeo_a_s_e_development_mode_field',
							'ziggeo_video',
							'ziggeo_video_expert');

		//Option to remove all templates
		add_settings_field('ziggeo_templates_clear',
							__('Want to clear all templates?', 'ziggeo'),
							'ziggeo_a_s_e_clear_templates',
							'ziggeo_video',
							'ziggeo_video_expert');

		//Use native camera apps or WebRTC (if availble) on mobile devices?
		add_settings_field('ziggeo_webrtc_for_mobile',
							__('Want to use WebRTC for mobile?', 'ziggeo'),
							'ziggeo_a_s_e_webrtc_for_mobile',
							'ziggeo_video',
							'ziggeo_video_expert');

		//Use WebRTC Streaming?
		add_settings_field('ziggeo_webrtc_streaming',
							__('Want to use WebRTC Streaming (can change quality)?', 'ziggeo'),
							'ziggeo_a_s_e_webrtc_streaming',
							'ziggeo_video',
							'ziggeo_video_expert');

		//Use WebRTC Streaming?
		add_settings_field('ziggeo_webrtc_streaming_needed',
							__('Turn on WebRTC Streaming (when needed only)', 'ziggeo'),
							'ziggeo_a_s_e_webrtc_streaming_when_needed',
							'ziggeo_video',
							'ziggeo_video_expert');

		// Ziggeo Private token
		add_settings_field('ziggeo_private_token',
							__('Ziggeo API Private Token', 'ziggeo'),
							'ziggeo_a_s_e_private_token_field',
							'ziggeo_video',
							'ziggeo_video_expert');

		// Ziggeo Encryption token
		add_settings_field('ziggeo_encryption_token',
							__('Ziggeo API Encryption Token', 'ziggeo'),
							'ziggeo_a_s_e_encryption_token_field',
							'ziggeo_video',
							'ziggeo_video_expert');

		// Ziggeo Server Auth token
		add_settings_field('ziggeo_server_auth_token',
							__('Ziggeo Server Auth Token', 'ziggeo'),
							'ziggeo_a_s_e_server_auth_token_field',
							'ziggeo_video',
							'ziggeo_video_expert');

		add_settings_field('ziggeo_auth',
							__('Activate Ziggeo Auth system', 'ziggeo'),
							'ziggeo_a_s_e_auth_system',
							'ziggeo_video',
							'ziggeo_video_expert');

		//Sync the templates
		add_settings_field('ziggeo_expert_sync',
							__('Recreate templates file', 'ziggeo'),
							'ziggeo_a_s_e_sync_field',
							'ziggeo_video',
							'ziggeo_video_expert');

		//Set version to use
		add_settings_field('ziggeo_expert_version',
							__('Set the version you wish to use', 'ziggeo'),
							'ziggeo_a_s_e_version_to_use',
							'ziggeo_video',
							'ziggeo_video_expert');

		//Set revision to use
		add_settings_field('ziggeo_expert_revision',
							__('Set the revision you wish to use', 'ziggeo'),
							'ziggeo_a_s_e_revision_to_use',
							'ziggeo_video',
							'ziggeo_video_expert');

		//Are we using Lazy Load approach or standard
		add_settings_field('ziggeo_expert_lazyload',
							__('Turn on lazyload approach', 'ziggeo'),
							'ziggeo_a_s_e_lazyload',
							'ziggeo_video',
							'ziggeo_video_expert');

		//Should we support old embedding code templates?
		add_settings_field('ziggeo_support_templates_v1',
							__('Do you want to support older version of templates?', 'ziggeo'),
							'ziggeo_a_s_e_support_old_templates',
							'ziggeo_video',
							'ziggeo_video_expert');
}

add_action('admin_init', 'ziggeo_p_admin_init');


//--- Tab functions start ----
//function to start the tabs and create the layout
function ziggeo_a_s_tabs_s_html() {
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
	<span id="ziggeo-tab_id_general" class="ziggeo-tabName selected" style="border-top-left-radius: 8px;" onclick="ziggeoPUIChangeTab('general');"><?php _ex('General', '"General" tab in settings', 'ziggeo'); ?></span>
	<span id="ziggeo-tab_id_integrations" class="ziggeo-tabName" onclick="ziggeoPUIChangeTab('integrations');"><?php _ex('Integrations', '"Integrations" tab in settings', 'ziggeo'); ?></span>
	<span id="ziggeo-tab_id_contact" class="ziggeo-tabName" onclick="ziggeoPUIChangeTab('contact');"><?php _ex('Contact Us', '"Contact Us" tab in settings', 'ziggeo'); ?></span>
	<span id="ziggeo-tab_id_expert" class="ziggeo-tabName" style="border-top-right-radius: 8px;" onclick="ziggeoPUIChangeTab('expert');"><?php _ex('Expert Settings', '"Expert Settings" tab in settings', 'ziggeo'); ?></span>
	<?php

	// @NOTE
	// To show a tab frame, we use:
	//<div class="ziggeo-frame" style="display: none;" id="ziggeo-tab_{section}">
	//If there are any frames before it we need to add </div> before the frame to close the previous one.
}


//functions for the general tab
include_once( ZIGGEO_ROOT_PATH . 'admin/settings-tabs-general.php');
//functions for the integrations tab
include_once( ZIGGEO_ROOT_PATH . 'admin/settings-tabs-integrations.php');
//contact us functions
include_once( ZIGGEO_ROOT_PATH . 'admin/settings-tabs-contactus.php');
//Expert settings
include_once( ZIGGEO_ROOT_PATH . 'admin/settings-tabs-expert.php');

//Function to close the last tab frame
function ziggeo_a_s_tabs_e_html() {
	/*We just need to close the last tab frame.. */ ?>
	</div>
	<?php 
}

//--- Tab functions end ----

// Validation functions
include_once( ZIGGEO_ROOT_PATH . 'admin/settings-validation.php' );


function ziggeo_a_s_page() {

	if( stripos($_SERVER['REQUEST_URI'], 'options-general.php') > -1) {
		//from yoursite.tld/wp-admin/options-general.php?page=ziggeo_video
		//to yoursite.tld/wp-admin/admin.php?page=ziggeo_video
		//We can no use wp_redirect() because when this fires, the headers are already sent out
		?>
		<script>
			window.location="<?php echo admin_url('admin.php?page=ziggeo_video'); ?>";
		</script>
		<p>
			If you are not redirected automatically, please <a href="<?php echo admin_url('admin.php?page=ziggeo_video'); ?>">click here</a>.
		</p>
		<?php
	}
	else {
		include_once(dirname(__FILE__) . '/page_settings.php');
	}
}

function ziggeo_a_n_page() {
	include_once(ZIGGEO_ROOT_PATH . 'admin/page_notifications.php');
}

function ziggeo_a_v_page() {
	include_once(ZIGGEO_ROOT_PATH . 'admin/page_videos.php');
}

function ziggeo_a_sdk_page() {
	include_once(ZIGGEO_ROOT_PATH . 'admin/page_sdk.php');
}

function ziggeo_a_addons_page() {
	include_once(ZIGGEO_ROOT_PATH . 'admin/page_addons.php');
}

// Inclusion of events editor page
function ziggeo_a_ee_page() {
	include_once(ZIGGEO_ROOT_PATH . 'admin/page_editor_events.php');
}

// Adding the templates editor
function ziggeo_a_et_page() {
	include_once( ZIGGEO_ROOT_PATH . 'admin/page_editor_templates.php');
}

// Adding the translation panel
function ziggeo_a_tr_page() {
	include_once( ZIGGEO_ROOT_PATH . 'admin/page_translations.php');
}



?>