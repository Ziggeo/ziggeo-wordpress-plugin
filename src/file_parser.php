<?php 
//Ziggeo file parser v1
// It will be used for events and templates, but could also be used for notifications so that we keep the log in the local storage
// File access vs db - faster, especially if looking up for entry that does not exist, but depending on the hosting, it might ask customers to fill out the details multiple times. To work around it, they could add the same credentials into their wp_config.php file..

//Checking if WP is running or if this is a direct call..
/*defined('ABSPATH') or die();*/

//Checks if file exists and if so updates it, otherwise creates one in 'userData' directory
function ziggeo_file_write($file, $content) {

	//encode content as JSON
	$content = json_encode($content);

	//If we already have an error, lets go back..
	if(!$content)	{ return null; }

	//add PHP tags
	$content = '<' . '?' . 'php//' . $content . '?' . '>';

	//Lets temporarily unlock the file if possible..
	$c = chmod($file, 0766);

	if($c === false) {
		//nope, it failed..
		//Leaving this for notifications ;)
	}

	//write it down
	$ret = file_put_contents($file, $content);

	//Lets set it back to closed
	$c = chmod($file, 0755);

	if($c === false) {
		//nope, it failed..
		//Leaving this for notifications ;)
	}

	return $ret;
}

//Get values from the file and return them as array
function ziggeo_file_read($file) {

	//Lets check if it exists or not
	if(!file_exists($file))	{ return false; }

	//Lets get the content
	$read = file_get_contents($file);

	if($read === false || $read === '' || strlen($read) < 10 ) { return false; }		

	//Strip away php tags and the WP check related to direct file calls
	$read = substr($read, 7, -2);
	
	$read = trim($read);

	if( strlen($read) > 1 ) {
		$read = json_decode($read, true);
	}

	if($read)	{ return $read; }

	return false;
}

?>