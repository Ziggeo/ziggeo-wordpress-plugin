<?php
//Checking if WP is running or if this is a direct call..
defined('ABSPATH') or die();


//if comments (WP) are not yet loaded, we will need to load them first to avoid errors..
if (file_exists(TEMPLATEPATH . '/comments.php'))
	include_once TEMPLATEPATH . '/comments.php';
elseif(file_exists(TEMPLATEPATH . '/includes/comments.php'))
	include_once TEMPLATEPATH . '/includes/comments.php';



//Getting our options
$options = get_option('ziggeo_video');

//Lets check if we have any role that we want to not have access to the video comments and break from here if any is set up..
// by default we will not forbid this..
if(isset($options['comment_roles']) && $options['comment_roles'] !== "") {
	//How this works..
	//SuperAdmin = 6
	//Admin = 5
	//Editor = 4
	//Author = 3
	//Contributor = 2
	//Subscriber = 1
	//Everyone (guest) = 0 || ""

	//superadmin & admin
	if( ($options['comment_roles'] >= 5) && current_user_can('activate_plugins') ) {
		ziggeo_p_setup_comments();
	}
	//editor - can make, edit, delete, publish theirs and posts of others
	elseif($options['comment_roles'] >= 4 && current_user_can('moderate_comments')){
		ziggeo_p_setup_comments();
	}
	//author - can make posts, delete their own and publish
	elseif($options['comment_roles'] >= 3 && current_user_can('edit_published_posts')) {
		ziggeo_p_setup_comments();
	}
	//contributor - can make posts and delete their own without publishing
	elseif($options['comment_roles'] >= 2 && current_user_can('edit_posts')) {
		ziggeo_p_setup_comments();
	}
	//subscriber - only read, no need to check
	elseif($options['comment_roles'] >= 1 && current_user_can('edit_posts')) {
		ziggeo_p_setup_comments();
	}
	//guests aka everyone..
	elseif($options['comment_roles'] == 0) {
		ziggeo_p_setup_comments();
	}
}
else {
	ziggeo_p_setup_comments();
}


//Setting up defaults - intentional double underscore since this is specific to this page..
function ziggeo_p_setup_comments() {

	//Getting our options
	$options = get_option('ziggeo_video');

	//we only include code if needed
	include_once(ZIGGEO_ROOT_PATH . '/templates/defaults_recorder.php');
	include_once(ZIGGEO_ROOT_PATH . '/templates/defaults_player.php');

	$template_recorder = ziggeo_get_recorder_code('comments');
	$template_player = ziggeo_get_player_code('comments');

	//If video is set as required and text as optional..
	if( isset($options['video_and_text']) && $options['video_and_text'] === ZIGGEO_YES ) {
		
		//Lets make sure that our settings in general tab are properly set.
		if(isset($options['disable_video_comments'])) {
			$options['disable_video_comments'] = '';
		}
		if(isset($options['disable_text_comments'])) {
			$options['disable_text_comments'] = '';
		}

		include_once(ZIGGEO_ROOT_PATH . '/templates/comments_video_required.php');

		ziggeo_comment_vrto_js_code($template_recorder, $template_player);
	}

	//If video comments are not disabled...
	elseif( !isset($options["disable_video_comments"]) ||
			(isset($options["disable_video_comments"]) && $options["disable_video_comments"] !== ZIGGEO_YES)) {

		include_once(ZIGGEO_ROOT_PATH . '/templates/comments_video_used.php');

		ziggeo_comment_vat_js_code($template_recorder, $template_player);
	}
}

?>