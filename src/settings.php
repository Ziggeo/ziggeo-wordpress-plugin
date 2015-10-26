<?php

function ziggeo_admin_init() {
	register_setting('ziggeo_video', 'ziggeo_video', 'ziggeo_video_validate');
	add_settings_section('ziggeo_video_main', 'Settings', 'ziggeo_video_section_text', 'ziggeo_video');
	add_settings_field('ziggeo_app_token', 'Ziggeo API Token', 'ziggeo_app_token_setting_string', 'ziggeo_video', 'ziggeo_video_main');
	add_settings_field('ziggeo_recorder_config', 'Ziggeo Recorder Config (leave blank for default settings)', 'ziggeo_recorder_config_setting_string', 'ziggeo_video', 'ziggeo_video_main');
	add_settings_field('ziggeo_player_config', 'Ziggeo Player Config (leave blank for default settings)', 'ziggeo_player_config_setting_string', 'ziggeo_video', 'ziggeo_video_main');
}

add_action('admin_init', 'ziggeo_admin_init');

function ziggeo_video_section_text() { ?>
	<p>
		Add your <a href="http://ziggeo.com" target="_blank">Ziggeo API</a> application token here.
	</p>
<?php }

function ziggeo_app_token_setting_string() {
	$options = get_option('ziggeo_video');
	echo "<input id='ziggeo_app_token' name='ziggeo_video[token]' size='40' type='text' value='{$options['token']}' />";	
}

function ziggeo_recorder_config_setting_string() {
	$options = get_option('ziggeo_video');
	echo "<input id='ziggeo_recorder_config' name='ziggeo_video[recorder_config]' size='40' type='text' value='{$options['recorder_config']}' />";	
}

function ziggeo_player_config_setting_string() {
	$options = get_option('ziggeo_video');
	echo "<input id='ziggeo_player_config' name='ziggeo_video[player_config]' size='40' type='text' value='{$options['player_config']}' />";	
}

function ziggeo_video_validate($input) {
	return $input;
}

function ziggeo_admin_add_page() {
	add_options_page('Ziggeo Video', 'Ziggeo Video', 'manage_options', 'ziggeo_video', 'ziggeo_settings_page');
}

add_action('admin_menu', 'ziggeo_admin_add_page');

function ziggeo_settings_page() {
	include_once(dirname(__FILE__) . "/settings_page.php");
}