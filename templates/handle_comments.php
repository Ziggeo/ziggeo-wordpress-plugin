<?php
//Checking if WP is running or if this is a direct call..
defined('ABSPATH') or die();


//if comments (WP) are not yet loaded, we will need to load them first to avoid errors..
if (file_exists(TEMPLATEPATH . '/comments.php'))
	include_once TEMPLATEPATH . '/comments.php';
elseif(file_exists(TEMPLATEPATH . '/includes/comments.php'))
	include_once TEMPLATEPATH . '/includes/comments.php';



//Getting our options
$option = ziggeo_get_plugin_options('comment_roles');

//Lets check if we have any role that we want to not have access to the video comments and break from here if any is set up..
// by default we will not forbid this..

	//How this works..
	//SuperAdmin = 6
	//Admin = 5
	//Editor = 4
	//Author = 3
	//Contributor = 2
	//Subscriber = 1
	//Everyone (guest) = 0 || ""

//superadmin & admin
if( $option >= 5 && current_user_can('activate_plugins') ) {
	ziggeo_p_setup_comments();
}
//editor - can make, edit, delete, publish theirs and posts of others
elseif($option >= 4 && current_user_can('moderate_comments')){
	ziggeo_p_setup_comments();
}
//author - can make posts, delete their own and publish
elseif($option >= 3 && current_user_can('edit_published_posts')) {
	ziggeo_p_setup_comments();
}
//contributor - can make posts and delete their own without publishing
elseif($option >= 2 && current_user_can('edit_posts')) {
	ziggeo_p_setup_comments();
}
//subscriber - only read, no need to check
elseif($option >= 1 && current_user_can('read')) {
	ziggeo_p_setup_comments();
}
//guests aka everyone..
elseif($option == 0) {
	ziggeo_p_setup_comments();
}


//Setting up defaults - intentional double underscore since this is specific to this page..
function ziggeo_p_setup_comments() {

	//Getting our options
	$options = ziggeo_get_plugin_options();

	$template_recorder = ziggeo_get_recorder_code('comments');
	$template_player = ziggeo_get_player_code('comments');

	// We need to prepare the codes for use
	$template_recorder = str_replace("\'", '"', $template_recorder);
	$template_recorder = str_replace("&apos;", "'", $template_recorder);

	$template_player = str_replace("\'", '"', $template_player);
	$template_player = str_replace("&apos;", "'", $template_player);

	//If video is set as required and text as optional..
	if( $options['video_and_text'] === ZIGGEO_YES ) {

		include_once(ZIGGEO_ROOT_PATH . '/templates/comments_video_required.php');

		ziggeo_comment_vrto_js_code($template_recorder, $template_player);
	}
	//If video comments are not disabled...
	elseif( $options["disable_video_comments"] !== ZIGGEO_YES) {

		include_once(ZIGGEO_ROOT_PATH . '/templates/comments_video_used.php');

		ziggeo_comment_vat_js_code($template_recorder, $template_player);
	}
}

?>