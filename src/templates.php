<?php

function ziggeo_url_endswith($needle) {
	global $_SERVER;
	$haystack = $_SERVER["PHP_SELF"];
	return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== FALSE);
}

if (ziggeo_url_endswith("/wp-admin/post-new.php") || ziggeo_url_endswith("/wp-admin/edit.php") || ziggeo_url_endswith($_SERVER["PHP_SELF"])) 
	add_action('admin_head', "ziggeo_post_template");
	
function ziggeo_post_template() {
	include(dirname(__FILE__) . "/../templates/post_template.php");
}

function ziggeo_comment_template($comment_template) {
	return dirname(__FILE__) . "/../templates/comments_template.php";
}

add_filter("comments_template", "ziggeo_comment_template");
