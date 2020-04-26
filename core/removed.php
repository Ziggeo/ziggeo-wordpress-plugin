<?php

// Purpose of this file is to offer you a way to know that something was trying to call a function that is no
// longer supported by the Core plugin.
// It might be available through a different function, plugin or has been completely removed for some reason.
// All of these functions will utilize notifications to tell you exactly what happened and why as well as what to do.

//Checking if WP is running or if this is a direct call..
defined('ABSPATH') or die();

//
// INDEX
//********
// 1. VideoWalls functions
//		* videowallsz_get_wall_placeholder()
//		* videowallsz_content_parse_videowall()
//		* videowallsz_prep_parameters_videowall()
//		* videowallsz_videowall_parameter_values()
//

//Only have this if the videowalls version is not available - meaning that the plugin is not activated or installed
if(!defined('VIDEOWALLSZ_VERSION')) {

	//Plugin page: https://wordpress.org/plugins/videowalls-for-ziggeo/
	//

	// FILE: /parsers/videowall_parser.php
	////////////////////////////////////////

	//Function to get the start and end of the videowall
	function videowallsz_get_wall_placeholder($inline_styles) {
		$msg = 'Function `videowallsz_get_wall_placeholder` was called on page "' . 
				esc_url_raw($_SERVER['REQUEST_URI']) . '"' .
				' Nothing happened, however to use VideoWalls you will need to download Videowalls Plugin.' .
				'You can get it by searching for "Videowalls For Ziggeo" or by going to this link: ' .
				'https://wordpress.org/plugins/videowalls-for-ziggeo/';
		ziggeo_notification_create($msg, 'error');
	}

	//$post_code - to see if we should post the code to the page or return it back
	function videowallsz_content_parse_videowall($template, $post_code = true) {
		$msg = 'Function `videowallsz_content_parse_videowall` was called on page "' . 
				esc_url_raw($_SERVER['REQUEST_URI']) . '"' .
				' Nothing happened, however to use VideoWalls you will need to download Videowalls Plugin.' .
				'You can get it by searching for "Videowalls For Ziggeo" or by going to this link: ' .
				'https://wordpress.org/plugins/videowalls-for-ziggeo/';
		ziggeo_notification_create($msg, 'error');
	}

	function videowallsz_prep_parameters_videowall($raw_parameters = null) {
		$msg = 'Function `videowallsz_prep_parameters_videowall` was called on page "' . 
				esc_url_raw($_SERVER['REQUEST_URI']) . '"' .
				' Nothing happened, however to use VideoWalls you will need to download Videowalls Plugin.' .
				'You can get it by searching for "Videowalls For Ziggeo" or by going to this link: ' .
				'https://wordpress.org/plugins/videowalls-for-ziggeo/';
		ziggeo_notification_create($msg, 'error');
	}

	// FILE: /parsers/videowall_template_parser.php
	/////////////////////////////////////////////////

	function videowallsz_videowall_parameter_values($toParse) {
		$msg = 'Function `videowallsz_videowall_parameter_values` was called on page "' . 
				esc_url_raw($_SERVER['REQUEST_URI']) . '"' .
				' Nothing happened, however to use VideoWalls you will need to download Videowalls Plugin.' .
				'You can get it by searching for "Videowalls For Ziggeo" or by going to this link: ' .
				'https://wordpress.org/plugins/videowalls-for-ziggeo/';
		ziggeo_notification_create($msg, 'error');
	}

	// FILE: /core/assets.php

	function videowallsz_css_video_wall() {
		$msg = 'Function `videowallsz_css_video_wall` was called on page "' . 
				esc_url_raw($_SERVER['REQUEST_URI']) . '"' .
				' Nothing happened, however to use VideoWalls you will need to download Videowalls Plugin.' .
				'You can get it by searching for "Videowalls For Ziggeo" or by going to this link: ' .
				'https://wordpress.org/plugins/videowalls-for-ziggeo/';
		ziggeo_notification_create($msg, 'error');
	}

}

?>