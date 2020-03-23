<?php

//Checking if WP is running or if this is a direct call..
defined('ABSPATH') or die();


//we have hooks within it, but we also allow the entire function to be overwritten in functions.php of a child theme
// or a different plugin if that is desired.
if(!function_exists('ziggeo_get_recorder_code')) {
	function ziggeo_get_recorder_code($location) {

		$options = get_option('ziggeo_video');
		$template_params = false;
		$template_recorder = false;

		$t_recorder_config = '';

		if(isset($options["recorder_config"])) {
			$t_recorder_config = $options["recorder_config"];
		}

		//hook allowing people to change the template if they wanted
		$t_recorder_config = apply_filters('ziggeo_get_template_recorder_default', $t_recorder_config);

		//Player defaults to be used in comments.
		$player_code = ( !empty($t_recorder_config) ) ? $t_recorder_config : ZIGGEO_DEFAULTS_RECORDER;

		if($location === "comments") {

			$t_recorder_comment_config = '';

			if(isset($options["comments_recorder_template"])) {
				$t_recorder_comment_config = $options["comments_recorder_template"];
			}

			//hook to change the comments player template name on the fly..
			$t_recorder_comment_config = apply_filters('ziggeo_get_template_recorder_comments', $t_recorder_comment_config);

			//Just so that we know if we are using template or not..
			if(!empty($t_recorder_comment_config)) {
				$template_recorder = true;
				$template_params = ziggeo_p_template_params($t_recorder_comment_config);
			}

		}
		elseif($location === 'integrations') {
			//Just so that we know if we are using template or not..
			$template_recorder = ( isset($options['integrations_recorder_template'])  &&
								!empty($options["integrations_recorder_template"]) );

			if($template_recorder) {
				$template_prams = apply_filter('ziggeo_get_template_recorder_integrations', $options['integrations_recorder_template']);
			}
		}

		// if we need to change the default recorder code 
		if($template_recorder && $template_params) {
			//Just confirm it one more time which one we should use, in case template was removed and settings not updated.
			//maybe change this to raise a notification if it happens to call deleted template.
			$recorder_code = ( $template_params ) ? $template_params : $recorder_code;

			//Make sure that template is parsed and prefix added if needed.
			$recorder_code = ziggeo_p_parameter_prep($recorder_code);
		}

		return $recorder_code;
	}
}
?>