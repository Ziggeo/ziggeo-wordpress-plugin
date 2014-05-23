<?php

if ($_SERVER["PHP_SELF"] == "/wp-admin/post-new.php" || $_SERVER["PHP_SELF"] == "/wp-admin/edit.php" || $_SERVER["PHP_SELF"] == "/wp-admin/post.php") 
	add_action('admin_head', "ziggeo_post_template");
	
function ziggeo_post_template() {
	include(dirname(__FILE__) . "/../templates/post_template.php");
}
 

function ziggeo_comment_template($comment_template) {
	return dirname(__FILE__) . "/../templates/comments_template.php";
}

add_filter("comments_template", "ziggeo_comment_template");
