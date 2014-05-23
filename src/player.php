<?php

add_filter('the_content', 'ziggeo_content_filter');
add_filter('comment_text', 'ziggeo_content_filter');
add_filter('the_title', 'ziggeo_content_filter');
add_filter('the_excerpt', 'ziggeo_content_filter');
add_filter('thesis_comment_text', 'ziggeo_content_filter');

function ziggeo_content_replace($matches) {
	return "<ziggeo ziggeo-width=320 ziggeo-height=240 ziggeo-video='" . $matches[1] . "'></ziggeo>";
}

function ziggeo_content_filter($content) {
	return preg_replace_callback("|\\[ziggeo\\](.*)\\[/ziggeo\\]|", 'ziggeo_content_replace', $content);
}	
