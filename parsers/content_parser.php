<?php

//Checking if WP is running or if this is a direct call..
defined('ABSPATH') or die();

//This file contains codes that hook into and parse the text to detect if template codes are within them.
// If they are the calls are made to right functions.

// Supported parses
// [ziggeotemplate ID] - latest, recommended [v2]
// [ziggeotemplate ID media_token] - latest, recommended [v2] (replaces %ZIGGEO_MEDIA_TOKEN% with token)
// [ziggeorecorder parameter1=value1 ...] - OK to use, considered as shortcode code, not template with ID [v1]
// [ziggeoplayer parameter1=value1 ...] - OK to use, considered as shortcode code, not template with ID [v1]
// [ziggeorerecorder parameter1=value1 ...] - OK to use, considered as shortcode code, not template with ID [v1]
// [ziggeouploader parameter1=value1 ...] - OK to use, considered as shortcode code, not template with ID [v1]
// [ziggeoplayer]VIDEO_TOKEN[/ziggeoplayer] - OK to use, considered as shortcode for default player (comments) [v1]
// [ziggeo ID] - legacy, not recommended [v1]
// [ziggeo]video_token[/ziggeo] - old legacy code - not recommended [v1]



//To initialize filters after the theme was loaded..
add_action('after_setup_theme', 'ziggeo_p_filters_init');

function ziggeo_p_filters_init() {

	$option = ziggeo_get_plugin_options('support_templates_v1');

	// [ziggeotemplate ...]
	add_filter('the_content', 'ziggeo_p_content_ziggeotemplate_parser');
	add_filter('comment_text', 'ziggeo_p_content_ziggeotemplate_parser');
	add_filter('the_excerpt', 'ziggeo_p_content_ziggeotemplate_parser');
	add_filter('thesis_comment_text', 'ziggeo_p_content_ziggeotemplate_parser');

	// [ziggeodownloads ...]
	add_filter('the_content', 'ziggeo_p_content_ziggeodownloads_parser');
	add_filter('comment_text', 'ziggeo_p_content_ziggeodownloads_parser');
	add_filter('the_excerpt', 'ziggeo_p_content_ziggeodownloads_parser');
	add_filter('thesis_comment_text', 'ziggeo_p_content_ziggeodownloads_parser');

	// This is additional level of support
	// It means additional processing, however it offers support for legacy template codes, which is needed
	// at the start of this introduction to new template structure
	if($option === true) {
		add_filter('the_content', 'ziggeo_p_content_filter', 90);
		add_filter('comment_text', 'ziggeo_p_content_filter', 90);
		add_filter('the_excerpt', 'ziggeo_p_content_filter', 90);
		add_filter('thesis_comment_text', 'ziggeo_p_content_filter', 90);
	}

}

// Templates v2 support
///////////////////////

// This function is checking the content that is passed to it if there is [ziggeotemplate {ID}] present or not present
// We also use this to avoid using REGEX that we used before (more convenient), and instead use faster PHP native functions
// For reference, please check: https://stackoverflow.com/questions/9477984/which-is-the-fast-process-strpos-stripos-or-preg-match-in-php
// used by: templates v2
function ziggeo_p_content_ziggeotemplate_parser($content) {

	$start = strpos($content, '[ziggeotemplate');

	if($start > -1) {
		// There is template present in the content that was passed to the function

		// To support lazyload mode
		if(!defined('ZIGGEO_FOUND')) {
			define('ZIGGEO_FOUND', true);
		}

		while($start > -1) {
			$t_before_template = '';    // To temporary store the content before the template
			$t_after_template = '';     // To temporary store content behind the template
			$t_template = '';           // To temporary store the template info itself
			$t_code = '';               // The code produced from the template

			$end = strpos($content, ']', $start); // [ziggeotemplate ID]

			$t_before_template = substr($content, 0, $start);
			$t_after_template = substr($content, $end+1);

			$t_template = substr($content, $start, ($end-$start)+1);

			$t_code = ziggeo_p_template_parser($t_template);

			if($t_code['status'] == 'success') {
				$content = $t_before_template . $t_code['result'] . $t_after_template;
			}
			else {
				// For now we just strip the template, however in future we might add filter or option
				// to handle this through code if someone reaches out about it.
				$content = $t_before_template .
							'<!--
								ziggeo-error: template code should be show here.
								reason: ' . $t_code['result'] . '
								solution: Please see: https://ziggeo.com/docs/integrations/wordpress/ for more info.
							-->' .
							$t_after_template;
				// Add notice
				ziggeo_notification_create('There was an error grabbing template on following page:  ' . esc_url($_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) .
					'  Error: ' . $t_code['result'], 'error');
			}

			// To find if we have multiple templates set
			$start = strpos($content, '[ziggeotemplate', $start+1);
		}
	}

	$content = ziggeo_p_assets_maybeload($content);

	return $content;
}

// Function that takes care of the template with ID to retrive the actual embedding code for us.
// used by: templates v2
// returns array with error or success status
function ziggeo_p_template_parser($template) {

	// Cleanup
	$template = str_replace('[ziggeotemplate', '', $template);
	$template = str_replace(']', '', $template);

	$template_id = trim($template);
	$token = false;

	if(strpos($template_id, ' ') > -1) {
		// We have ID and media token
		$template = explode(' ', $template_id);
		$template_id = $template[0];
		$token = $template[1];
	}

	// Retrieve the template ID
	$template_info = ziggeo_p_template_get_params($template_id);

	if(is_array($template_info)) {

		if($token !== false) {
			if(strpos($template_info['params'], '%ZIGGEO_MEDIA_TOKEN%') > -1) {
				$template_info['params'] = str_replace('%ZIGGEO_MEDIA_TOKEN%', $token, $template_info['params']);
			}
			else {
				// We need to modify it by adding the video parameter (or audio - later)
				$template_info['params'] .= ' video=\'' . $token . '\'';
			}
		}

		switch ($template_info['type']) {
			case '[ziggeorecorder':
			case '[ziggeorerecorder':
			case '[ziggeouploader':
				$template_code = '<ziggeorecorder ' .
				                    ziggeo_p_template_prefix_params($template_info['params']) .
				                 '></ziggeorecorder>';
				break;
			case '[ziggeoplayer':
				$template_code = '<ziggeoplayer ' .
				                    ziggeo_p_template_prefix_params($template_info['params']) .
				                 '></ziggeoplayer>';
				break;
			case '[ziggeoaudiorecorder':
				$template_code = '<ziggeoaudiorecorder ' .
				                    ziggeo_p_template_prefix_params($template_info['params']) .
				                 '></ziggeoaudiorecorder>';
				break;
			case '[ziggeoaudioplayer':
				$template_code = '<ziggeoaudioplayer ' .
				                    ziggeo_p_template_prefix_params($template_info['params']) .
				                 '></ziggeoaudioplayer>';
				break;
			default:
				$template_info['type'] = str_replace('[', '', $template_info['type']);
				// If we get to this point, it is a non core template (like videowalls template)
				// We allow plugins to hook into this to replace their own templates and do their magic
				$template_code = apply_filters('ziggeo_template_parser_type_' . $template_info['type'], $template_info);

				if(is_array($template_code)) {
					return array(
						'status' => 'error',
						'result' => 'Template type "' . $template_info['type'] . '" is not supported.'
					);
				}
				break;
		}

		return array(
			'status' => 'success',
			'result' => $template_code
		);
	}

	return array(
		'status' => 'error',
		'result' => 'Template ID not found'
	);
}


// Extra Templates Support
//////////////////////////

// Function that searches the content for the [ziggeodownloads shortcode
// Needed because add_shortcode would not work as long as v1 support is turned on
function ziggeo_p_content_ziggeodownloads_parser($content) {

	$start = strpos($content, '[ziggeodownloads');

	if($start > -1) {

		// To support lazyload mode
		if(!defined('ZIGGEO_FOUND')) {
			define('ZIGGEO_FOUND', true);
		}

		while($start > -1) {
			$t_before_code = '';    // To temporary store the content before the download option
			$t_after_code = '';     // To temporary store content behind the download option
			$t_shortcode = '';      // To temporary store the download option params info itself
			$t_code = '';           // download option code
			$error = false;

			$t_code = '<script class="ziggeo_download_template">' .
				'setTimeout(function() { ziggeoShowDownloadVideo(\'%token%\'); }, 2000);' .
			'</script>';

			$end = strpos($content, ']', $start); // [ziggeodownloads token]

			$t_before_code = substr($content, 0, $start);
			$t_after_code = substr($content, $end+1);

			$t_shortcode = substr($content, $start, ($end-$start)+1);

			// Check if there is media token or not
			// if not error = 'No media token provided'
			$t_shortcode = str_replace('[ziggeodownloads', '', $t_shortcode);
			$t_shortcode = trim(str_replace(']', '', $t_shortcode));

			if($t_shortcode === '') {
				$error = 'No media token was provided';
			}
			else {
				// we might extend this to support more than just token
				$token = $t_shortcode;
			}

			if($error !== false) {
				// For now we just strip the template, however in future we might add filter or option
				// to handle this through code if someone reaches out about it.
				$content = $t_before_code .
							'<!--
								ziggeo-error: download option should be show here.
								reason: ' . $error . '
								solution: Please see: https://ziggeo.com/docs/integrations/wordpress/ for more info.
							-->' .
							$t_after_code;
				// Add notice
				ziggeo_notification_create('There was an error using download shortcode on following page:  ' . esc_url($_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) .
					'  Error: ' . $error, 'error');
			}
			else {
				$t_code = str_replace('%token%', $token, $t_code);
				$content = $t_before_code . $t_code . $t_after_code;
			}

			// To find if we have multiple templates set
			$start = strpos($content, '[ziggeodownloads', $start+1);
		}
	}

	$content = ziggeo_p_assets_maybeload($content);

	return $content;
}




// Templates v1 support
///////////////////////

//Add support for the shortcodes
function ziggeo_p_shortcode_handler($tag = '[ziggeorecorder', $attrs = '') {

	if(!defined('ZIGGEO_SHORTCODE_RUN')) {
		define('ZIGGEO_SHORTCODE_RUN', true);
	}

	$attrs_str = '';

	if($attrs === '') {
		$attrs_str = ZIGGEO_DEFAULTS_RECORDER;
	}
	else {
		//We have to combine the attrs array into key + value
		foreach($attrs as $key => $value) {
			$attrs_str .= ' ' . $key . "='" . $value . "'";
		}
	}

	return ziggeo_p_content_filter($tag . $attrs_str . ']');
}

//General Ziggeo shortcode support
add_shortcode( 'ziggeo', function($attrs) {

	if($attrs === '') {
		// it could be
		// [ziggeo]video_token[/ziggeo] - legacy code
		// [ziggeo] - which would call for template without ID
		return '';
	}

	return ziggeo_p_shortcode_handler('[ziggeo', $attrs);
});

//We are updating this in such a way that we will keep the old calls, so that we have backwards compatibility, but in the same time, we are adding another call that will check for us if there are any tags matching new templates. We must do it like this, since using regex we will be able to find this in all locations that we want, while if we use shortcode, it will only work (out of the box) if the shortcode is within the section covered by 'the_content' filter.
function ziggeo_p_content_filter($content) {

	//This way we are making it work fine with WPv5 saving where we would parse the content while we should not (like saving the post)
	if(is_rest()) {
		return $content;
	}

	//use add_filter('ziggeo_content_filter_pre', 'your-function-name') to change the content on fly before any checks
	// for your Ziggeo templates
	// it needs to return modified $content.
	if(!defined('ZIGGEO_SHORTCODE_RUN')) {
		$content = apply_filters('ziggeo_content_filter_pre', $content);
	}

	//matching new templates with old way of calling them in case someone does the same..
	//handles [ziggeo]token[/ziggeo]
	$content = preg_replace_callback("|\\[ziggeo(.*)\\](.*)\\[/ziggeo(.*)\\]|", 'ziggeo_p_content_parse_templates', $content);

	//finally we do a check for the latest way of doing it only.
	// fallback so some specific cases
	$content = preg_replace_callback("|\\[ziggeo(.*)\\]|", 'ziggeo_p_content_parse_templates', $content);

	//check to make sure that we get even [ziggeo calls without end bracket and show the embedding matching the call as much as possible instead of an error on a page
	// fallback so some specific cases
	$content = preg_replace_callback("|\\[ziggeo*([^\s\<]+)|", 'ziggeo_p_content_parse_templates', $content);

	//use add_filter('ziggeo_content_filter_post', 'your-function-name') to change the content on fly after checking it
	// for your Ziggeo templates
	// it needs to return modified $content.
	if(!defined('ZIGGEO_SHORTCODE_RUN')) {
		$content = apply_filters('ziggeo_content_filter_post', $content);
	}

	$content = ziggeo_p_assets_maybeload($content);

	return $content;
}

//This works like shortcode functions do, allowing us to capture the codes through various filters and parse them as needed.
//TODO: This needs to be broken up and simplified.
function ziggeo_p_content_parse_templates($matches) {

	// To not parse the ziggeo events
	if(strpos($matches[0], '[ziggeo_event') > -1) {
		return $matches[0];
	}

	// To not parse the ziggeotemplate (it was already parsed at this time)
	if(strpos($matches[0], '[ziggeotemplate') > -1) {
		return $matches[0];
	}

	//for lazyload support
	// To make sure we do not call it multiple times
	if(!defined('ZIGGEO_FOUND')) {
		define('ZIGGEO_FOUND', true);
	}

	//Elementor Support START
	for($i = 0, $l = count($matches); $i < $l; $i++) {
		$matches[$i] = str_replace(' 0=', '', $matches[$i]);
	}

	if($l === 2 && ziggeo_p_template_exists(trim($matches[1], "'"))) {
		$tmp = $matches[1];
		$matches[1] = ziggeo_p_template_exists(trim($matches[1], "'"));
		//$matches[0] = str_replace($tmp, $matches[1], $matches[0]);
		$matches[0] = $matches[1];
	}
	//-- Elementor Support END

	//The new templates called the old way..[ziggeoplayer]TOKEN[/ziggeoplayer]
	//if this is detected, we re-do the call by modifying the parameters and re-calling this function
	//handles: [ziggeo]token[/ziggeo], [ziggeoplayer]TOKEN[/ziggeoplayer], [ziggeorecorder]
	if(isset($matches, $matches[3]) && trim($matches[3]) !== '') {
		//In case this is not set up right, which can happen in some cases
		// such as [ziggeo]e8c1ae11cf40d579e9bb38d4e0c55fa7[/ziggeo]
		if($matches[3] === '') {
			if(stripos($matches[0], '[ziggeo]') > -1 && stripos($matches[0], '[/ziggeo]') > -1) {
				$matches[3] = 'player';
			}
		}

		//$template, $token, $type
		return ziggeo_content_parse_with_token_only($matches[0], $matches[2], $matches[3]);
	}

	//Is this a template?
	$existing_template = ziggeo_p_template_exists(str_replace(array('[ziggeo ', '[ziggeorecorder ', '[ziggeoplayer ', '[ziggeorerecorder ', '[ziggeouploader ', '[ziggeoaudioplayer ', '[ziggeoaudiorecorder ', ']'), '', trim($matches[0] )));

	//Early template catch
	if($existing_template) {
		return ziggeo_p_content_parse_templates(array($existing_template, $existing_template));
	}

	// the variable that we will use to return the data
	$ret = '';

	$type = null; //the type of tag that we are using..

	//So that we can fill out the tags as we did before.
	$current_user = ziggeo_p_get_current_user();

	//Lets check what we are filtering through at this time..
	$filter = current_filter();

	//We will need to check few options from the DB..
	$options = ziggeo_get_plugin_options();

	//Lets add a tag to the video, that the same is not only a wordpress video, but that it is also one made in
	// comments ;)
	if( $filter === 'comment_text' || $filter === 'thesis_comment_text' ) {
		$location_tag = 'comment';
		//1. Test if there is a specific template to use when playing comments
		//2. if so get its parameters and combine them with video token and pass further.
		$tmp = '[ziggeoplayer ';

		if( isset($options['comments_player_template']) && !empty($options['comments_player_template']) ) {
			$commentTemplateID = $options['comments_player_template'];

			$tmp .= ziggeo_p_template_params($commentTemplateID);
		}

		$index = stripos($matches[0], ' video=');

		if($index === false) {
			//RegEx will put the token in here
			if(isset($matches[2])) {
				$tmp .= ' video="' . $matches[2] . '"';
			}
			//We could come here if someone put code where it should not be
			else {
				//Was the comment made by admin
				$comment = get_comment();

				if($comment && isset($comment->user_id)) {
					if( $comment->user_id && $user = get_userdata( $comment->user_id ) ) {
						if(isset($user->caps['administrator']) && $user->caps['administrator'] === true) {
							$tmp = $matches[0];
						}
						else {
							$tmp = '';
						}
					}
				}
				else {
					$tmp = '';
				}
			}
		}
		else {
			$tmp .=  substr($matches[0], $index, stripos($matches[0], ' ', $index+1) );
		}

		//We should not output anything if $tmp is empty, however want the admin to be able to see it properly
		if($tmp === '' && !is_admin()) {
			return '';
		}
		elseif($tmp === '' && is_admin()) {
			return __('[UNSAFE EMBEDDING REMOVED] Click edit to see its code.', 'ziggeo');
		}

		$matches[0] = $tmp;
		$matches[1] = 'ignore';

	}
	//For now it is else, in future, we can expend this to include other filters.
	else {
		$location_tag = 'post';
	}

	//use add_filter('ziggeo_template_parsing_tag_set', 'your-function-name', 10, 2) to change the location tag
	// it needs to return modified $location_tag while $filter should not be changed.

	$location_tag = apply_filters('ziggeo_template_parsing_tag_set', $location_tag, $filter);

	$c_user = ( $current_user->user_login == "" ) ? 'Guest' : $current_user->user_login;

	//We are listing all of the required parameters for that specific tag. All of the player tags will however have ziggeo as their base.
	$presets = array (
		'ziggeo' => array (
						/*'width' => 320,
						'height' => 240*/
					),
		'ziggeoplayer' => array (
						'video' => ''/*, //requires token to play video. If it is not added, we will pass empty token, so that they are shown the player error and know that they need to fix it.
						'width' => 320,
						'height' => 240*/
					),
		'ziggeorecorder' => array (
						'tags' => array ('wordpress', $c_user, $location_tag )/*,
						'width' => 320,
						'height' => 240*/
					), //tags are pre-set
		'ziggeorerecorder' => array (
						'video' => '', //requires token to play video. If it is not added, we will pass empty token, so that they are shown the player error and know that they need to fix it.
						'tags' => array ('wordpress', $c_user, $location_tag )/*,
						'width' => 320,
						'height' => 240*/
					),
		'ziggeouploader' => array (
						'tags' => array ('wordpress', $c_user, $location_tag, 'uploader' ),
						//'perms' => array ('allowupload', 'forbidrecord') - v1
						'allowrecord' => false,
						'allowupload' => true
					),
		'ziggeoaudioplayer' => array (
						'audio' => ''
					),
		'ziggeoaudiorecorder' => array (
						'tags' => array ('wordpress', $c_user, $location_tag )
					)
	);

	//Lets remove that last bracket

	//This should be active for new templates only
	if(isset($matches, $matches[1])) {
		$savedVideo = false;

		//Quick check to see if we have video= in there or not..
		//This would happen if we use tinyMCE to add template
		$ts = stripos($matches[0], 'video=');

		if( $ts > -1 ) {
			$savedVideo = substr($matches[0], $ts );
			$savedVideo = str_replace( ']', '', $savedVideo );

			//What follows is needed to clean up saved video of other parameters..
			$_temp_params = explode(' ', $savedVideo);

			$matches[1] = str_replace( ']', ' ', $matches[1] );
			for($i = 1, $c = count($_temp_params); $i < $c; $i++) {
				$savedVideo = str_ireplace($_temp_params[$i], '', $savedVideo);
			}

			$matches[0] = str_replace( trim($savedVideo), '', $matches[0]);
		}

		//These are parameters sent to us through the [ziggeo] shortcode. It can be a raw setup like: " width=320 height=240 limit=4" or template ID/name
		$parameters = trim($matches[1], " \t\n\r\0\x0B".chr(0xC2).chr(0xA0));
		$fullMatch = $matches[0];

		//It could be an empty list.. if it is, then we should apply defaults to the same and just send it up..
		if($parameters === "") {
			$ret = '<ziggeorecorder ' . ZIGGEO_DEFAULTS_RECORDER . ' ziggeo-tags="' .
					implode(',', $presets['ziggeorecorder']['tags']) .
					'"></ziggeorecorder>';

			// Check for HTTP pages when recorder is used
			if(isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) {
				ziggeo_notification_create(__( 'Looks like video recorder will be used on HTTP page. Most browser vendors will try to block or criple access to camera. Please switch to HTTPS. Page: "' . $_SERVER['REQUEST_URI'] . '"' , 'ziggeo'));
			}
		}

		//There is something for us to do, so lets determine what is our starting tag at this stage since later we will use it if it is template and if it is not
		else {
			$tag = '';

			//Is it base?
			if( stripos($fullMatch, '[ziggeo ') > -1 ) {
				$parameters = substr($fullMatch, 8, -1);
				$tag = 'ziggeo';
				// old template, we should change this or report it in some manner.
				// basically now we need to figure out:
				// 1. is this player
				if(ziggeo_p_template_is_player($fullMatch)) {
					$tag = 'ziggeoplayer';
					$fullMatch = str_ireplace('[ziggeo ', '[ziggeoplayer ', $fullMatch);
				}
				// 2. is this uploader
				elseif(ziggeo_p_template_is_uploader($fullMatch)) {
					$tag = 'ziggeouploader';
					$fullMatch = str_ireplace('[ziggeo ', '[ziggeouploader ', $fullMatch);

				}
				// 3. is this rerecorder
				elseif(ziggeo_p_template_is_rerecorder($fullMatch)) {
					$tag = 'ziggeorerecorder';
					$fullMatch = str_ireplace('[ziggeo ', '[ziggeorerecorder ', $fullMatch);
				}
				//else {
				elseif(ziggeo_p_template_is_recorder($fullMatch)) {
					// 4. is this recorder
					//elseif(ziggeo_p_template_is_recorder($fullMatch)) {
					//lets turn it into recorder by default..
					$tag = 'ziggeorecorder';
					$fullMatch = str_ireplace('[ziggeo ', '[ziggeorecorder ', $fullMatch);
				}

				//now we need to switch the parameters to v2 params.
				$parameters = ziggeo_template_v1_to_v2($parameters);
			}
			elseif($fullMatch === '[ziggeo') {
				$tag = 'ziggeorecorder';
				$fullMatch = '[ziggeorecorder ';
			}

			//Handle possible template options.. lets also make it easy to register new template options through a hook..
			// 1. we would need to know how the template option is named (like "ziggeoplayer", or "ziggeowall")
			// 2. we pass the parameters
			// 3. we get back the handled parameters
			// 4. we set the tag as the template option name
			// 5. continue further..

			$temp_templates_array = array(
				array(
					'name'			=> 'ziggeoplayer',
					'func_pre'		=> 'ziggeo_p_prep_parameters_player',
					'func_final'	=> 'ziggeo_content_parse_player'
				),
				array(
					'name'			=> 'ziggeorecorder',
					'func_pre'		=> 'ziggeo_p_prep_parameters_recorder',
					'func_final'	=> 'ziggeo_content_parse_recorder'
				),
				array(
					'name'			=> 'ziggeorerecorder',
					'func_pre'		=> 'ziggeo_p_prep_parameters_rerecorder',
					'func_final'	=> 'ziggeo_content_parse_rerecorder'
				),
				array(
					'name'			=> 'ziggeouploader',
					'func_pre'		=> 'ziggeo_p_prep_parameters_uploader',
					'func_final'	=> 'ziggeo_content_parse_uploader'
				),
				array(
					'name'			=> 'ziggeoaudioplayer',
					'func_pre'		=> 'ziggeo_p_prep_parameters_audio_player',
					'func_final'	=> 'ziggeo_content_parse_audio_player'
				),
				array(
					'name'			=> 'ziggeoaudiorecorder',
					'func_pre'		=> 'ziggeo_p_prep_parameters_audio_recorder',
					'func_final'	=> 'ziggeo_content_parse_audio_recorder'
				)
			);

			$template_options = apply_filters('ziggeo_manage_template_options_pre', $temp_templates_array);

			for($i = 0, $c = count($template_options); $i < $c; $i++ ) {

				if( stripos($fullMatch, $template_options[$i]['name']) > -1 ) {

					//initial parameters (raw if you will)
					$parameters = substr($fullMatch, strlen($template_options[$i]['name'])+1, -1);

					$parameters = str_replace(']', '', $parameters);

					//properly handled parameters
					$parameters = $template_options[$i]['func_pre']($parameters);

					$tag = $template_options[$i]['name'];
					//so we only find one of them
					break;
				}
			}

			//When the call is not done right, we might not get it captured by [ziggeo test at the top, so we should check it again..
			if($tag === '') {
				if( stripos($fullMatch, '[ziggeo') > -1 ) {
					$parameters = substr($fullMatch, 8, -1);
					$tag = 'ziggeorecorder';

					//we will see it as a recorder
					//apply default template values if the same are not provided already
					$parameters = ziggeo_p_parameter_processing( ziggeo_get_parameters_from_template_code(ZIGGEO_DEFAULTS_RECORDER), $parameters );
				}
			}
		}

		//If any further processing is needed
		if($ret === '') {
			//If there are any custom tags within the parameters (such as %POST_TITLE% and alike) lets change them into right values
			$parameters = ziggeo_p_parse_custom_tags($parameters);
			//Lets determine if it is ID/name of a template and call it
			if( $template = ziggeo_p_template_exists( $parameters ) ) {

				//Lets check if we sent the video along with template name, and if we did, lets give it back its video.
				if($savedVideo) {

					if( stripos($template, ' video=') ) {
						$template = str_ireplace( array('video=""', "video=''"), ' ' . $savedVideo . ' ', $template);
					}
					else {
						if(stripos($template, ']') > -1) {
							$template = str_replace( ']', ' ' . $savedVideo . ']', $template);
						}
						else {
							$template .= ' ' . $savedVideo . ']';
						}
					}
				}

				//At this time the parameters holds the template ID not parameters and template is having the the template loaded with tags and everything..
				$ret = ziggeo_p_content_parse_templates(array($template, $template));
			}
			//if it is not a template name, it is likely parameters list, so just post it 'as is'..
			else {
				//This is the actual processing ;)

				//Lets check if we sent the video along with template name, and if we did, lets give it back its video.
				if($savedVideo) {
					$parameters .= ' ' . $savedVideo;
				}

				if( isset($presets[$tag]) ) {
					$template = ziggeo_p_parameter_processing($presets[$tag], $parameters, true);
				}
				else {
					$template = ziggeo_p_parameter_processing(array(), $parameters, true);
				}

				//we re-utilize the template_options to do a final parsing
				for($i = 0, $c = count($template_options); $i < $c; $i++ ) {
					if( $tag === $template_options[$i]['name'] ) {

						$ret = $template_options[$i]['func_final']($template, false);

						//so we only find one of them
						break;
					}
				}

				// --- VIDEO PLAYER, RECORDER or UPLOADER ---
				// ------------------------------------------

				if($ret === '') {

					//Apply ziggeo prefixes - only needed for <ziggeo> code
					$template = ziggeo_p_parameter_prep($template);

					if( isset($options, $options['beta']) || $tag === 'ziggeoplayer' ) { 
						$ret = '<ziggeoplayer ziggeo-theme="modern" ' . $template . '></ziggeoplayer>';
					}
					else {
						$ret = '<ziggeorecorder ' . $template . '></ziggeorecorder>';
					}
				}
			}
		}
	}

	return $ret;
}

?>