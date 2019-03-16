<?php

//Checking if WP is running or if this is a direct call..
defined('ABSPATH') or die();


//we have hooks within it, but we also allow the entire function to be overwritten in functions.php of a child theme
// or a different plugin if that is desired.
if(!function_exists('ziggeo_get_player_code')) {
	function ziggeo_get_player_code($location) {

		$options = get_option('ziggeo_video');
		$template_params = false;
		$template_player = false;

		//hook allowing people to change the template if they wanted
		do_action('ziggeo_get_template_player_default', $options["player_config"]);

		//Player defaults to be used in comments.
		$player_code = ( isset($options["player_config"]) &&
						!empty($options["player_config"]) ) ? $options["player_config"] : ZIGGEO_DEFAULTS_PLAYER;

		if($location === "comments") {

			//hook to change the comments player template on fly..
			do_action('ziggeo_get_template_player_comments', $options['comments_player_template']);

			//Just so that we know if we are using template or not..
			$template_player = ( isset($options['comments_player_template']) &&
								!empty($options["comments_player_template"]) );

			//Final player template that we will be using
			if($template_player) {
				//DB holds the name of template, so we need to retrieve the parameters from the same based on the name.
				$template_params = ziggeo_p_template_params($options['comments_player_template']);
			}
		}
		elseif($location === 'integrations') {
			//Just so that we know if we are using template or not..
			$template_player = ( isset($options['integrations_player_template'])  &&
								!empty($options["integrations_player_template"]) );

			if($template_player) {
				$template_prams = apply_filter('ziggeo_get_template_player_integrations', $options['integrations_player_template']);
			}
		}

		//Final player template that we will be using
		if($template_player && $template_params) {
			//Just confirm it one more time which one we should use, in case template was removed and settings not updated.
			//maybe change this to raise a notification if it happens to call deleted template.
			$player_code = ( $template_params ) ? $template_params : $player_code;

			//Make sure that template is parsed and prefix added if needed.
			$player_code = ziggeo_p_parameter_prep($player_code);
		}

		return $player_code;
	}
}

?>