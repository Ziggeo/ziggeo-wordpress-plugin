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
	if (@$video_token) {
		$config = @$options["player_config"] ? $options["player_config"] : $default; 
		return "<ziggeo " . $config . " ziggeo-video='" . $video_token . "'></ziggeo>";
	} else {
		$config = @$options["recorder_config"] ? $options["recorder_config"] : $default;
		return "<ziggeo " . $config . "'></ziggeo>";
	}
}

function ziggeo_content_filter($content) {
	return preg_replace_callback("|\\[ziggeo\\](.*)\\[/ziggeo\\]|", 'ziggeo_content_replace', $content);
}	
