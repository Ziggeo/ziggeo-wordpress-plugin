<?php

//Checking if WP is running or if this is a direct call..
defined('ABSPATH') or die();

//Code used to switch from older version of the plugin (v1.15 was latest) to v2.0

//This file will run all of the updates that might be needed, per a version check.
function ziggeo_p_on_update($options = null) {

	//Is this backed or frontend?
	//We do not run this on frontend
	if(!is_admin()) {
		return false;
	}

	//Get options - we always want to do this using the standard WP way
	$options = get_option('ziggeo_video');
	$defaults = ziggeo_get_plugin_options_defaults();

	//Are we already up to date?
	if(isset($options['version']) && ($options['version'] == ZIGGEO_VERSION)) {
		//All good and up to date, lets just go out of this.
		return true;
	}

	//In case this is very old version, lets make it safe for check down the road
	if(!isset($options['version'])) {
		$options['version'] = 1;
	}

	////////////////////////
	// PER VERSION UPDATES
	////////////////////////

	// 2.2
	// Info: Apply the fix for templates
	if(version_compare($options['version'], '2.2', '<')) {
		//Fix for template names
		$t_templates = ziggeo_p_templates_index();
		$templates = array();

		if(is_array($t_templates)) {
			//All is good, lets do it
			foreach ($t_templates as $key => $value) {

				if(trim($key) === '') {
					$key = "ziggeo_template_" . rand(20, 3000);
				}

				$templates[strtolower($key)] = $value;
			}
		}

		//Save templates
		ziggeo_p_templates_add_all($templates);
	}

	// 2.3
	// Info: Apply the fix for the values of some options so all use ZIGGEO_ constants
	if(version_compare($options['version'], '2.3', '<')) {
		if(isset($options['webrtc_for_mobile']) && $options['webrtc_for_mobile'] === 'off') {
			$options['webrtc_for_mobile'] = ZIGGEO_NO;
		}
		else {
			$options['webrtc_for_mobile'] = ZIGGEO_YES;
		}
		if(isset($options['webrtc_streaming']) && $options['webrtc_streaming'] === 'on') {
			$options['webrtc_streaming'] = ZIGGEO_YES;
		}
		else {
			$options['webrtc_streaming'] = ZIGGEO_NO;
		}
		if(isset($options['webrtc_streaming_needed']) && $options['webrtc_streaming_needed'] === 'off') {
			$options['webrtc_streaming_needed'] = ZIGGEO_NO;
		}
		else {
			$options['webrtc_streaming_needed'] = ZIGGEO_YES;
		}
		if(isset($options['use_auth']) && $options['use_auth'] === 'true') {
			$options['use_auth'] = ZIGGEO_YES;
		}
		else {
			$options['use_auth'] = ZIGGEO_NO;
		}
	}

	// 2.3.4
	// Info: Turns out the fileds were not change right in the dashboard, final update for that.
	if(version_compare($options['version'], '2.3.4', '<')) {
		if(isset($options['webrtc_for_mobile']) &&
			( $options['webrtc_for_mobile'] === false || $options['webrtc_for_mobile'] === 'false') ) {
			$options['webrtc_for_mobile'] = ZIGGEO_NO;
		}
		else {
			$options['webrtc_for_mobile'] = ZIGGEO_YES;
		}

		if(isset($options['webrtc_streaming']) &&
			( $options['webrtc_streaming'] === true || $options['webrtc_streaming'] === 'true') ) {
			$options['webrtc_streaming'] = ZIGGEO_YES;
		}
		else {
			$options['webrtc_streaming'] = ZIGGEO_NO;
		}

		if(isset($options['webrtc_streaming_needed']) &&
			( $options['webrtc_streaming_needed'] === false || $options['webrtc_streaming_needed'] === 'false') ) {
			$options['webrtc_streaming_needed'] = ZIGGEO_NO;
		}
		else {
			$options['webrtc_streaming_needed'] = ZIGGEO_YES;
		}

		if(isset($options['use_auth']) &&
			( $options['use_auth'] === false || $options['use_auth'] === 'false') ) {
			$options['use_auth'] = ZIGGEO_NO;
		}
		else {
			$options['use_auth'] = ZIGGEO_YES;
		}
	}

	// 2.12
	if(version_compare($options['version'], '2.12', '<')) {
		// We would be here if we were updating from some older revision. As such we should leave the comments as they were, with the defauilt of modifying comments set to true instead of false (the actual default).
		$defaults['modify_comments'] = ZIGGEO_YES;
	}

	//Using this method, we actually allow some new options to be added and saved even if they are not made through our plugin.
	foreach($options as $option => $value) {
		$defaults[$option] = $value;
	}

	//This way all defaults are applied as well as the old settings are kept.
	$options = $defaults;

	//In the end we also update the version
	//NOTE: This should always be last
	$options['version'] = ZIGGEO_VERSION;

	update_option('ziggeo_video', $options);
}

add_action('plugins_loaded', 'ziggeo_p_on_update');


?>