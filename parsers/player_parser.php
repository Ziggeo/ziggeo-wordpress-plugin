<?php

//Checking if WP is running or if this is a direct call..
defined('ABSPATH') or die();


if(!function_exists('ziggeo_content_parse_player')) {

//@TODO: Lets add few hooks into this
	function ziggeo_content_parse_player($code) {
		//return the HTML code
		return '<ziggeoplayer ' . ziggeo_template_v1_to_v2(ziggeo_p_parameter_prep($code)) . '></ziggeoplayer>';
	}
}

//Shortcode handling for Ziggeo Player
add_shortcode( 'ziggeoplayer', function($attrs) {
	return ziggeo_p_shortcode_handler('[ziggeoplayer', $attrs);
});

//checks if the given template is player or not..
function ziggeo_p_template_is_player($template) {

	$is_v1 = ziggeo_p_check_code_is_v1($template, $template);

	//these should be only the definite parameters that only player can use..
	$playerParams = array(
		//v1
		'popup', // player in v1 both in v2..
		'nofullscreen',
		'stream',
		'modes[player]',
		'player',
		'video',
		'autoplay',
		'loop',
		//v2
		'stretch',
		'source',
		'loop',
		'initialseek',
		'playfullscreenonmobile',
		'playwhenvisible',
		'playonclick',
		'disableseeking',
		'disablepause',
		'pauseonplay',
		'airplay',
		'chromecast',
		'preload',
		'playlist',
		'stream-width',
		'stream-height'
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
	if( strlen(trim($raw_parameters)) === 32 && stripos($raw_parameters, '=') === false) {
		$raw_parameters = ' video="' . trim($raw_parameters) . '" ';
	}

	return ziggeo_p_parameter_processing( ziggeo_get_parameters_from_template_code(ZIGGEO_DEFAULTS_PLAYER), $raw_parameters );
}


?>