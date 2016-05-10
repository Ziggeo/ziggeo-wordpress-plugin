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
		if (@$options["beta"])
			$tagname = "ziggeoplayer";
		$config = @$options["player_config"] ? $options["player_config"] : $default; 
		return "<" . $tagname . " ba-theme='modern' " . $config . " ziggeo-video='" . $video_token . "'></" . $tagname . ">";
	} else {
		$config = @$options["recorder_config"] ? $options["recorder_config"] : $default;
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
		$tmp = ziggeo_content_replace_templates( array( substr( $matches[0], 0, strpos($matches[0], ']') ) . 'video="' . $matches[2] . '"]', $matches[2] ) );
		return $tmp;
	}

	//This should be active for new templates only
	if(isset($matches, $matches[1]) )
	{
		//These are parameters sent to us through the [ziggeo] shortcode. It can be a raw setup like: " width=320 height=240 limit=4" or template ID/name
		$parameters = trim($matches[1]);
		$fullMatch = $matches[0];

		//It could be an empty list.. if it is, then we should apply defaults to the same and just send it up..
		if($parameters === "") {
			return '<ziggeo ziggeo-width=320 ziggeo-height=240 ziggeo-tags="' . implode(',', $presets['ziggeorecorder']['tags']) . '"></ziggeo>';
		}

		//There is something for us to do, so lets determine what is our starting tag at this stage since later we will use it if it is template and if it is not
		else {
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
		}

		//Lets determine if it is ID/name of a template and call it
		if( $template = ziggeo_template_exists( $parameters ) ) {

			//At this time the parameters holds the template ID not parameters and temaplte is having the the template loaded with tags and everything..
			return ziggeo_content_replace_templates(array($template, $template));
		}
		//if it is not a template name, it is likely parameters list, so just post it 'as is'..
		else {
			//This is the actual processing ;)

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

	//Just looking at the above, it seems to me that it would be best to apply some moderation checks. For example, if we just scan the comments for shortcode, it would mean that anyone could come and set up a ziggeo recorder on the comments, even if they are not logged in.. As such it might be best to limit this, to only if the author of the comment is the one that made the post, page or the comment hence allowing us to not trust the shortcodes sent to us by the people that just posted, but should not otherwise be able to do the same..

	//@TODO if author admin/moderator - do it..

	//Match the current setups - the ones done by previous versions
	$content = preg_replace_callback("|\\[ziggeo\\](.*)\\[/ziggeo\\]|", 'ziggeo_content_replace', $content);

	//Match the new setups - must be made after the above, since this one will match that one as well..

	//matching new templates with old way of calling them in case someone does the same..
	$content = preg_replace_callback("|\\[ziggeo(.*)\\](.*)\\[/ziggeo(.*)\\]|", 'ziggeo_content_replace_templates', $content);

	//finally we do a check for the latest way of doing it only.
	return preg_replace_callback("|\\[ziggeo(.*)\\]|", 'ziggeo_content_replace_templates', $content);
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

	return $processed;
}

//Function to search for parameters without "ziggeo-" and apply the same to them.
function ziggeo_parameter_prep($data) {

	$tmp_str = explode(' ', $data);
	$tmp_str2 = '';

	foreach($tmp_str as $key => $value) {
		$value = trim($value);
		if( $value !== '' && $value !== ']' ) {

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
