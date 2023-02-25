<?php

//Checking if WP is running or if this is a direct call..
defined('ABSPATH') or die();


if(!function_exists('ziggeo_content_parse_player')) {

	//@TODO: Lets add few hooks into this
	function ziggeo_content_parse_player($code, $post_code = true) {
		$result = '<ziggeoplayer ' . ziggeo_template_v1_to_v2(ziggeo_p_parameter_prep($code)) . '></ziggeoplayer>';

		if($post_code === true) {
			echo $result;
		}
		else {
			//return the HTML code
			return $result;
		}
	}
}

//Shortcode handling for Ziggeo Player
// Only active if we use older content parsing
// Note: Removed intentionally as it can cause issues in parsing
//add_shortcode( 'ziggeoplayer', function($attrs, $content, $tag) {
//	return ziggeo_p_shortcode_handler('[ziggeoplayer', $attrs);
//});

// Support for audio player
if(!function_exists('ziggeo_content_parse_audio_player')) {

	//@TODO: Lets add few hooks into this
	function ziggeo_content_parse_audio_player($code, $post_code = true) {
		$result = '<ziggeoaudioplayer ' . ziggeo_p_parameter_prep($code) . '></ziggeoaudioplayer>';

		if($post_code === true) {
			echo $result;
		}
		else {
			//return the HTML code
			return $result;
		}
	}
}

//Shortcode handling for Ziggeo Audio Player
add_shortcode( 'ziggeoaudioplayer', function($attrs) {
	return ziggeo_p_shortcode_handler('[ziggeoaudioplayer', $attrs);
});

//checks if the given template is player or not..
function ziggeo_p_template_is_player($template, $specific = 'video') {

	$is_v1 = ziggeo_p_check_code_is_v1($template, $template);

	//these should be only the definite parameters that only player can use..
	$playerParams = array(
		//v1
		'popup', // player in v1 and in v2..
		'nofullscreen',
		'stream', // v1 + v2 video and audio
		'modes[player]',
		'player',
		'video',
		'autoplay', // v1 + v2 video and audio
		'loop',
		//v2 - video
		'stretch', // deprecated
		'playfullscreenonmobile',
		'airplay',
		'chromecast',
		'stream-width',
		'stream-height',
		// video & audio
		'allowpip',
		'disablepause',
		'disableseeking',
		'forcerefresh',
		'initialseek',
		'loop',
		'loopall',
		'pauseonplay',
		'playlist',
		'playonclick',
		'playwhenvisible',
		'visibilityfraction',
		'preload',
		'skipseconds',
		'source',
		'tracktags',
		'tracktagsstyled',
		'volume'
		//'audio'
	);

	for($i = 0, $c = count($playerParams); $i < $c; $i++) {
		if( stripos($template, $playerParams[$i] . '=') !== false ||
			stripos($template, $playerParams[$i] . ']') !== false ||
			stripos($template, $playerParams[$i] . ' ') !== false) {

			if( $playerParams[$i] == 'popup' && $is_v1 === true ) {
				return true;
			}
			elseif( $playerParams[$i] !== 'popup' ) {
				return true;
			}
		}
	}

	return false;
}



//handles the raw parameters for the ziggeo player..
function ziggeo_p_prep_parameters_player($raw_parameters = null) {

	if($raw_parameters === null) {
		return '';
	}

	//we are trying to detect video token being present..
	if( strlen(trim($raw_parameters)) === 32 && strpos($raw_parameters, '=') === false) {
		$raw_parameters = ' video="' . trim($raw_parameters) . '" ';
	}

	return ziggeo_p_parameter_processing(ziggeo_get_parameters_from_template_code(ZIGGEO_DEFAULTS_PLAYER), $raw_parameters );
}

//handles the raw parameters for the ziggeo audio player..
function ziggeo_p_prep_parameters_audio_player($raw_parameters = null) {

	if($raw_parameters === null) {
		return '';
	}

	return ziggeo_p_parameter_processing( ziggeo_get_parameters_from_template_code(ZIGGEO_DEFAULTS_AUDIO_PLAYER), $raw_parameters );
}

?>