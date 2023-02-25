<?php

//Checking if WP is running or if this is a direct call..
defined('ABSPATH') or die();

//This file has the codes that are related to processing / parsing of templates, their parameters and / or values.

// Functions in this file
///////////////////////////
// 1. ziggeo_get_parameters_from_template_code()
// 2. ziggeo_p_parameter_processing()
// 3. ziggeo_p_parameter_prep()
// 4. ziggeo_content_parse_with_token_only()



//helper function to turn the default templates (strings) to array for ziggeo_p_parameter_processing function..
// should also be useful for standard templates to get the array of parameters
if(!function_exists('ziggeo_get_parameters_from_template_code')) {

	function ziggeo_get_parameters_from_template_code($code) {

		$_temp = explode('ziggeo-', $code);
		$rez = array();
		foreach($_temp as $key => $value) {
			$value = str_ireplace('ziggeo-', '', $value);
			$_t = explode('=', $value);

			//in case any empty spaces are passed over..
			if(isset($_t[1])) {
				$rez[$_t[0]] = str_ireplace('"', '', trim($_t[1]));
			}
		}

		return $rez;
	}
}

//Function to process parameters. We send it which ones have to be in and which one are currently set and it sends us back a string that we can use as a template
//It will also (TODO) check if we are setting same parameters to remove duplicates, while seeing if one should be used over the other..
function ziggeo_p_parameter_processing($attributes, $process, $replaceDuplicates = false) {

	$processed = $process; //for now

	foreach ($attributes as $key => $value) {

		if( stripos($process, $key) === false )
		{
			$processed .= ' ' .  $key;

			if($value === '')            { $processed .= '=""';  } //if it is empty string
			elseif($value === false)    { $processed .= '=false'; } //if it is false
			elseif(is_array($value))    { $processed .= '="' . implode(',', $value) . '"'; }
			elseif($value !== true)        {  $processed .= '="' . $value . '"'; } //if it is number (since it already passed the above..)
		}
		else {
			$position_start = stripos($process, $key);
			$_tstr = substr($process, 0, $position_start);

			$_tstr .= substr($process, stripos($process, ' ', $position_start));

			$process = $_tstr;
		}
	}

	//Seems that if customers use "" within the visual editor, it will change quote to &#8221; and &#8243; so lets clean that up..
	$processed = str_replace( array('&#8220;', '&#8221;', '&#8243;'), '"', $processed);
	$processed = str_replace( array('&#8216;','&#8217;', '&#8242;'), "'", $processed);
	//Thank you Jay for catching the additional quotes

	$processed = str_replace( "\\'", '"', $processed);

	return $processed;
}

//Function to search for parameters without "ziggeo-" and apply the same to them.
function ziggeo_p_parameter_prep($data) {

	//When new templates are added, we might not want them to be handled by this function, so we add them to the list to be skipped..
	$skip = apply_filters('ziggeo_parameter_prep_skip_list', array());

	for($i = 0, $c = count($skip); $i < $c; $i++) {

		if( stripos($data, $skip[$i]) > -1 ) {
			return $data;
		}
	}

	$tmp_str = explode(' ', $data);
	$tmp_str2 = '';

	foreach($tmp_str as $key => $value) {
		$value = trim($value, " \t\n\r\0\x0B".chr(0xC2).chr(0xA0));

		if( $value !== '' && $value !== '[' && $value !== '[ziggeo' && $value !== ']' && $value !== '""' &&
			$value !== '"' && $value !== 'player' && $value !== 'recorder' && $value !== 'rerecorder') {

			//@TODO
			// 2. make it understand that 'some text' are not actually 2 parameters..

			if( stripos($value, 'ziggeo-') > -1 ) {
				//seems that ziggeo- prefix is already present.. should we do something then, or just skip it?
			}
			elseif(stripos($value, '=') > -1) {
				$tmp_str2 .= ' ziggeo-' . $value;
			}
			else {
				// This part makes it not support the boolean parameters (which seems not needed, leaving for now, will be removed in future)
				$tmp_str2 .= ' ' . $value;
			}
		}
	}

	return $tmp_str2;
}

//parses templates with token by using default values
//example: [ziggeoplayer]TOKEN[/ziggeoplayer], [ziggeorecorder]TOKEN[/ziggeorecorder]..
function ziggeo_content_parse_with_token_only($template, $token, $type) {

	// player - just plays back the video (default)
	// recorder - no playback is shown, it will try to overwrite/rerecord over existing video upon recording
	// rerecorder - offers playback and to rerecord the video

	$params = '';

	if($type === 'player') {
		$full = '[ziggeoplayer ';
	}
	elseif($type === 'rerecorder') {
		$full = '[ziggeorerecorder ';
	}
	else { //($type === 'recorder')
		$full = '[ziggeorecorder ';
	}

	//Did we add the template through TinyMCE? If so we will have a string placeholder in there..
	if(stripos($template, 'YOUR_VIDEO_TOKEN') > -1) {
		$t_pos = strpos($template, ' ');
		$tmp = substr($template, $t_pos, strpos($template, ']') - $t_pos+1 );

		$params = str_replace('YOUR_VIDEO_TOKEN', $token, $tmp);
	}
	//OK, so we have someone that writes their own templates and specific term provided by TinyMCE was not used..
	else {
		//Do we have video parameter mentioned at all? If not, we will just add it to the list, otherwise, lets do some search and replace instead.
		$start = stripos($template, 'video=');
		if( $start > -1) {

			$params = str_replace(array('video=', ']'), ' ', $template);

			//cleanup
			$params = str_replace( array(
										'[ziggeoplayer ',
										'[ziggeorecorder ',
										'[ziggeorerecorder ',
										'[ziggeouploader ',

										'[/ziggeoplayer',
										'[/ziggeorecorder',
										'[/ziggeorerecorder',
										'[/ziggeouploader',
										$token
										), '', $params);

			$params .= ' video="' . $token . '" ';

		}
		//we just add video parameter to the existing list of parameters
		else {
			if($type === 'player') {
				$params = ' video="' . $token . '" ';
			}
		}
	}

	$params = str_ireplace($full, '', $params);

	$full .= $params;

	//At this $full holds something like: [ziggeo  video="VIDEO_TOKEN"
	//and $params holds video="VIDEO_TOKEN"
	return ziggeo_p_content_parse_templates( array($full, $params) );
}

?>