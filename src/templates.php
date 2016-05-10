<?php
//Checking if WP is running or if this is a direct call..
defined('ABSPATH') or die();

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

//----------------------------------------------------------------------------

// Custom templates file: 'custom_templates.php'
// For now, all files will be written with fwrite and fread.. This is fastest method and allows us to avoid asking customer about their details. It would also not be a very good method for reading files in either case since when pages are loaded, we need to run the same, not to wait for input about credentials..
//after this is working right, we can add notifications about the files created so that customers can modify them if needed, and have a fallback on the file parser using WP filesystem..

//Adds new templates to the currently existing ones
function ziggeo_templates_add($id, $value) {

	//path to userData directory 
	$dir = ZIGGEO_ROOT_PATH . 'userData';
	$file = $dir . '/custom_templates.php';

	$content = array ( $id => $value );

	//In case directory does not exist, we will make one.
	if(!file_exists($dir)) {
		mkdir($dir);

		return ziggeo_file_write($file, $content);
	}
	else
	{
		if($current = ziggeo_file_read($file)) {
			//This way the new data updates the old one, if array keys match..
			//@TODO - the name should be unique, so do we want to record an error for our customer to know that they had used the name previously used in some other template, or do we just overwrite it? -> error settings_error() otherwise leave as is.
			$content = array_merge($current, $content);
		}

		return ziggeo_file_write($file, $content);
	}
}

//Finds and updates the passed template
function ziggeo_templates_update($old_id, $id, $content) {
	$updated = array();

	//path to custom templates file
	$file = ZIGGEO_ROOT_PATH . 'userData/custom_templates.php';

	//grab all
	if($current = ziggeo_file_read($file)) {
		foreach($current as $template => $value)
		{
			//find old
			if($template === $old_id) {
				//update old
				$updated[$id] = $content;
			}
			else{
				$updated[$template] = $value;
			}
		}

		//save all
		return ziggeo_file_write($file, $updated);
	}

	return false;
}

//Removes the template from the list of existing ones
function ziggeo_templates_remove($id) {
	$updated = array();

	//path to custom templates file
	$file = ZIGGEO_ROOT_PATH . 'userData/custom_templates.php';

	//grab all
	if($current = ziggeo_file_read($file)) {
		foreach($current as $template => $value)
		{
			//find old and skip it
			if($template !== $id) {
				$updated[$template] = $value;
			}
		}

		//save all
		return ziggeo_file_write($file, $updated);
	}

	return false;
}

//Searches for all existing templates. Returns the list or false if none
function ziggeo_templates_index() {
	//path to custom templates file
	$file = ZIGGEO_ROOT_PATH . 'userData/custom_templates.php';

	return ziggeo_file_read($file);
}

//Checks if the template with specified ID exists or not
function ziggeo_template_exists($id)
{
	//Lets get a list of all existing templates
	$index = ziggeo_templates_index();

	if( isset($index, $index[$id]) )
	{
		//yey we found it, lets get it sent back for processing :)
		return $index[$id];
	}

	//if we did not find it, lets just return false..
	return false;
}