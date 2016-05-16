<?php
//Checking if WP is running or if this is a direct call..
defined('ABSPATH') or die();

add_filter('the_content', 'ziggeo_content_filter');
add_filter('comment_text', 'ziggeo_content_filter');
add_filter('the_excerpt', 'ziggeo_content_filter');
add_filter('thesis_comment_text', 'ziggeo_content_filter');

// -- Default values to use: 'ziggeo-width=320 ziggeo-height=240'
function ziggeo_content_replace($matches) {
	$options = get_option('ziggeo_video');
	$default = 'ziggeo-width=320 ziggeo-height=240';
	$video_token = trim($matches[1]);
	$tagname = "ziggeo";
	if (@$video_token) { //so if there is video token it is player, while if it is not set, it is not.. This means that it was not as easy to set up re-recorder.
		if (isset($options, $options["beta"])) {
			$tagname = "ziggeoplayer";
		}
		$config = isset($options, $options["player_config"]) ? $options["player_config"] : $default; 
		
		return "<" . $tagname . " ba-theme='modern' " . $config . " ziggeo-video='" . $video_token . "'></" . $tagname . ">";
	} else {
		$config = isset($options, $options["recorder_config"]) ? $options["recorder_config"] : $default;
		try {
			$current_user = wp_get_current_user();
			$config .= ' ziggeo-tags="' . $current_user->user_login . '"';
		} catch (Exception $e) {}

		return "<" . $tagname . " " . $config . "'></" . $tagname . ">"; //This seems to hold ' too many..
	}
}

//This works like shortcode does, allowing us to capture the codes through various filters and parse them as needed.
function ziggeo_content_replace_templates($matches)
{
	$ret = '';
	$beta_params = array('stretch' => true, 'theme' => true, '_wpbeta_' => true); //the last one just to state if the template is beta or not..
	$type = null; //the type of tag that we are using..

	//So that we can fill out the tags as we did before.
	$current_user = wp_get_current_user();

	//Lets check what we are filtering through at this time..
	$filter = current_filter();

	//We will need to check few options from the DB..
	$options = get_option('ziggeo_video');

	//Lets add a tag to the video, that the same is not only a wordpress video, but that it is also one made in comments ;)
	if( $filter === 'comment_text' || $filter === 'thesis_comment_text' ) {
		$locationTag = 'comment';

		//1. Test if there is a specific template to use when playing comments
		//2. if so get its parameters and combine them with video token and pass further.
		$tmp = '[ziggeo ';

		if( isset($options['comments_player_template']) && !empty($options['comments_player_template']) ) {
			$commentTemplateID = $options['comments_player_template'];

			$tmp .= ziggeo_template_params($commentTemplateID);
		}

		$index = stripos($matches[0], ' video=');
		$tmp .=  substr($matches[0], $index, stripos($matches[0], ' ', $index+1) );

		$matches[0] = $tmp;
		$matches[1] = 'ignore';
	}
	//For now it is else, in future, we can expend this to include other filters.
	else {
		$locationTag = 'post';
	}

	//We are listing all of the required parameters for that specific tag. All of the player tags will however have ziggeo as their base.
	$presets = array (
		'ziggeo' => array (
						'width' => 320,
						'height' => 240
					),
		'ziggeoplayer' => array (
						'video' => '', //requires token to play video. If it is not added, we will pass empty token, so that they are shown the player error and know that they need to fix it.
						'width' => 320,
						'height' => 240
					),
		'ziggeorecorder' => array (
						'tags' => array ('wordpress', $current_user->user_login, $locationTag ),
						'width' => 320,
						'height' => 240
					), //tags are pre-set
		'ziggeorerecorder' => array (
						'video' => '', //requires token to play video. If it is not added, we will pass empty token, so that they are shown the player error and know that they need to fix it.
						'tags' => array ('wordpress', $current_user->user_login, $locationTag ),
						'width' => 320,
						'height' => 240
					),
		'ziggeouploader' => array (
						'perms' => array ('allowupload', 'forbidrecord'),
						'width' => 320,
						'height' => 240
					),
		//These are to be done later...
		'ziggeovideowall' => array (), //!uses different parameters
		'ziggeoform' => array () //!uses different parameters
	);

	//The new templates called the old way..[ziggeoplayer]TOKEN[/ziggeoplayer]
	if(isset($matches, $matches[3]))
	{
		$full = '[ziggeo ';
		$params = '';

		if($matches[3] === 'rerecorder') {
			$full = '[ziggeorerecorder ';

			//Since we know that it is re-recorder, lets check if we 
			if( ( $start = stripos($matches[2], 'modes') ) > -1) {
				if(stripos($matches[2], 'rerecorder', $start) === false) {
					//we have modes set, but they do not have rerecorder as one of the modes.. since rerecorder tag is used, it should override the same
					$matches[2] = str_replace('modes="', ' modes="rerecorder,', $matches[2]);
				}
			}
			//we did not have mode set, so lets set it up by adding at the start
			else {
				$full .= ' modes="rerecorder" ';
			}
		}

		$tmp = stripos($matches[0], ' ');
		$tmp = substr($matches[0], $tmp, stripos($matches[0], ']') - $tmp+1 );

		//Did we add the template through TinyMCE? If so we will have a string placeholder in there..
		if(stripos($matches[0], 'YOUR_VIDEO_TOKEN') > -1) {
			$params = str_replace('YOUR_VIDEO_TOKEN', $matches[2], $tmp);
		}
		//OK, so we have someone that writes their own templates and specific term provided by TinyMCE was not used..
		else {
			//Do we have video parameter mentioned at all? If not, we will just add it to the list, otherwise, lets do some search and replace instead.
			if( ($start = stripos($matches[0], 'video=') ) > -1) {

				$params = str_replace('video=', '', $matches[1]);
				//$params = str_replace($matches[0], 'video=', '');
				$params .= ' video="' . $matches[2] . '" ';

			}
			//we just addd video parameter to the existing list of parameters
			else {
				$params = ' video="' . $matches[2] . '" ';
			}
		}
		$full .= $params;

		return ziggeo_content_replace_templates( array($full, $params) );
	}

	//This should be active for new templates only
	if(isset($matches, $matches[1]) )
	{
		$savedVideo = false;

		//Quick check to see if we have video= in there or not..
		//This would happen if we use tinyMCE to add template
		if( ($ts = stripos($matches[0], 'video=')) > -1 ) {
			//[ziggeo comments player video='bb9c5916d80277f7edba2d088c8c16a3']
			$savedVideo = substr($matches[0], $ts );
			$savedVideo = str_replace( ']', '', $savedVideo );
			$matches[0] = str_replace( $savedVideo, '', $matches[0]);
		}

		//These are parameters sent to us through the [ziggeo] shortcode. It can be a raw setup like: " width=320 height=240 limit=4" or template ID/name
		$parameters = trim($matches[1]);
		$fullMatch = $matches[0];

		//It could be an empty list.. if it is, then we should apply defaults to the same and just send it up..
		if($parameters === "") {
			return '<ziggeo ziggeo-width=320 ziggeo-height=240 ziggeo-tags="' . implode(',', $presets['ziggeorecorder']['tags']) . '"></ziggeo>';
		}

		//There is something for us to do, so lets determine what is our starting tag at this stage since later we will use it if it is template and if it is not
		else {
			$tag = '';

			//Is it base?
			if( stripos($fullMatch, '[ziggeo ') > -1 ) {
				$parameters = substr($fullMatch, 8, -1);
				$tag = 'ziggeo';
			}
			//Is it video player
			if( stripos($fullMatch, 'ziggeoplayer') > -1 ) {
				$parameters = substr($fullMatch, 13, -1);
				$tag = 'ziggeoplayer';
			}
			//is it recorder
			elseif( stripos($fullMatch, 'ziggeorecorder') > -1 ) {
				$parameters = substr($fullMatch, 15, -1);

				$tag = 'ziggeorecorder';
			}
			//is it re-recorder
			elseif( stripos($fullMatch, 'ziggeorerecorder') > -1 ) {
				$parameters = substr($fullMatch, 17, -1);

				$tag = 'ziggeorerecorder';
			}
			//is it uploader
			elseif( stripos($fullMatch, 'ziggeouploader') > -1 ) {
				$parameters = substr($fullMatch, 15, -1);

				$tag = 'ziggeouploader';
			}
			//is it videowall
			elseif( stripos($fullMatch, 'ziggeovideowall') > -1 ) {
				$parameters = substr($fullMatch, 16, -1);

				$tag = 'ziggeovideowall';
			}
			//is it form
			elseif( stripos($fullMatch, 'ziggeoform') > -1 ) {
				$parameters = substr($fullMatch, 11, -1);

				$tag = 'ziggeoform';
			}

			//When the call is not done right, we might not get it captured by [ziggeo test at the top, so we should check it again..
			if($tag === '') {
				if( stripos($fullMatch, '[ziggeo') > -1 ) {
					$parameters = substr($fullMatch, 8, -1);
					$tag = 'ziggeo';
				}
			}
		}

		//Lets determine if it is ID/name of a template and call it
		if( $template = ziggeo_template_exists( $parameters ) ) {

			//Lets check if we sent the video along with template name, and if we did, lets give it back its video.
			if($savedVideo) {
				
				if( stripos($template, ' video=') ) {
					$template = str_ireplace( array('video=""', "video=''"), ' ' . $savedVideo . ' ', $template);
				}
				else {
					$template = str_replace( ']', ' ' . $savedVideo . ']', $template);
				}
			}

			//At this time the parameters holds the template ID not parameters and temaplte is having the the template loaded with tags and everything..
			return ziggeo_content_replace_templates(array($template, $template));
		}
		//if it is not a template name, it is likely parameters list, so just post it 'as is'..
		else {
			//This is the actual processing ;)

			//Lets check if we sent the video along with template name, and if we did, lets give it back its video.
			if($savedVideo) {
				$parameters .= ' ' . $savedVideo;
			}

			$template = ziggeo_parameter_processing($presets[$tag], $parameters);

			$tagName = 'ziggeo';

			//Check if there are any beta tags in there..
			foreach($beta_params as $param => $value) {
				if( stripos($template, $param) > -1 ) {
					$tagName = 'ziggeoplayer';

					//Since this is only plugin specific beta option - per template, so we need to remove it so that it is not passed to Ziggeo API
					if($param === '_wpbeta_')	{ $template = str_replace('_wpbeta_', '', $template); }
				}
			}

			//Apply ziggeo prefixes
			$template = ziggeo_parameter_prep($template);

			//To check if this is a call for video wall or form since we serve them differently
			if($tag === 'ziggeovideowall' || $tag === 'ziggeoform')
			{
				$ret = '<b> Here a ' . $tag . ' code would be placed</b>';
			}
			//one of the players/recorders/uploaders
			else {
				if( isset($options, $options['beta']) && $tag === 'ziggeoplayer' ) { 
					$ret = '<ziggeoplayer ziggeo-theme="modern" ' . $template . '></ziggeoplayer>';
				}
				else {
					$ret = '<ziggeo ' . $template . '></ziggeo>';
				}				
			}

		}

		return $ret;
	}
}

//We are updating this in such a way that we will keep the old calls, so that we have backwards compatibility, but in the same time, we are adding another call that will check for us if there are any tags matching new templates. We must do it like this, since using regex we will be able to find this in all locations that we want, while if we use shortcode, it will only work (out of the box) if the shortcode is within the section covered by 'the_content' filter.
function ziggeo_content_filter($content) {

	//Match the current setups - the ones done by previous versions
	$content = preg_replace_callback("|\\[ziggeo\\](.*)\\[/ziggeo\\]|", 'ziggeo_content_replace', $content);

	//Match the new setups - must be made after the above, since this one will match that one as well..

	//matching new templates with old way of calling them in case someone does the same..
	$content = preg_replace_callback("|\\[ziggeo(.*)\\](.*)\\[/ziggeo(.*)\\]|", 'ziggeo_content_replace_templates', $content);

	//finally we do a check for the latest way of doing it only.
	$content = preg_replace_callback("|\\[ziggeo(.*)\\]|", 'ziggeo_content_replace_templates', $content);

	//check to make sure that we get even [ziggeo calls without end bracket and show the embedding matching the call as much as possible instead of an error on a page
	$content = preg_replace_callback("|\\[ziggeo*([^\s\<]+)|", 'ziggeo_content_replace_templates', $content);

	return $content;
}

//Function to process parameters. We send it which ones have to be in and which one are currently set and it sends us back a string that we can use as a template
//It will also (TODO) check if we are setting same parameters to remove duplicates, while seeing if one should be used over the other..
function ziggeo_parameter_processing($requiredAtt, $process, $stripDuplicates = false)
{
	$processed = $process; //for now

	foreach ($requiredAtt as $req => $value) {
		if( stripos($process, $req) === false )
		{
			$processed .= ' ' .  $req;
			
			if($value === '')			{ $processed .= '=""';  } //if it is empty string
			elseif($value === false)	{ $processed .= '=false'; } //if it is false
			elseif(is_array($value))	{ $processed .= '="' . implode(',', $value) . '"'; }
			elseif($value !== true)		{ $processed .= '=' . $value; } //if it is number (since it already passed the above..)
		}
	}

	//Seems that if customers use "" within the visual editor, it will change quote to &#8221; and &#8243; so lets clean that up..
	$processed = str_replace( array('&#8221;', '&#8243;'), '"', $processed);
	$processed = str_replace( array('&#8217;', '&#8242;'), "'", $processed);

	return $processed;
}

//Function to search for parameters without "ziggeo-" and apply the same to them.
function ziggeo_parameter_prep($data) {

	$tmp_str = explode(' ', $data);
	$tmp_str2 = '';

	foreach($tmp_str as $key => $value) {
		$value = trim($value);
		if( $value !== '' && $value !== ']' && $value !== '""'&& $value !== '"'
			&& $value !== 'player' && $value !== 'recorder' && $value !== 'rerecorder') {

			if( stripos($value, 'ziggeo-') > -1 ) {
				//seems that ziggeo- prefix is already present.. should we do something then, or just skip it?
			}
			else {
				$tmp_str2 .= ' ziggeo-' . $value;
			}
		}
	}

	return $tmp_str2;
}
?>
