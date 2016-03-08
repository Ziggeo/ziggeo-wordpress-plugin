<?php

add_filter('the_content', 'ziggeo_content_filter');
add_filter('comment_text', 'ziggeo_content_filter');
add_filter('the_title', 'ziggeo_content_filter');
add_filter('the_excerpt', 'ziggeo_content_filter');
add_filter('thesis_comment_text', 'ziggeo_content_filter');

function ziggeo_content_replace($matches) {
	$options = get_option('ziggeo_video');
	$default = 'ziggeo-width=320 ziggeo-height=240';
	$video_token = trim($matches[1]);
	$tagname = "ziggeo";
	if (@$video_token) {
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
		return "<" . $tagname . " " . $config . "'></" . $tagname . ">";
	}
}

function ziggeo_content_filter($content) {
	return preg_replace_callback("|\\[ziggeo\\](.*)\\[/ziggeo\\]|", 'ziggeo_content_replace', $content);
}	
