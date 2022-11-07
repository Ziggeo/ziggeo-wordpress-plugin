<?php 
//Ziggeo file parser v1
// It will be used for events and templates, but could also be used for notifications so that we keep the log in the local storage
// File access vs db - faster, especially if looking up for entry that does not exist, but depending on the hosting, it might ask customers to fill out the details multiple times. To work around it, they could add the same credentials into their wp_config.php file..

//Checking if WP is running or if this is a direct call.. - can not use it due to its use through tinymce plugin
/*defined('ABSPATH') or die();*/

//Checks if file exists and if so updates it, otherwise creates one in 'userData' directory
// > file - the path to file including file name
// > content - content to write down
// > context - it is only set for failure, so that we can know which form fields to keep
function ziggeo_p_file_write($file, $content, $context = false) {

	//encode content as JSON
	$content = json_encode($content);

	//If we already have an error, lets go back..
	if(!$content)   { return null; }

	//add PHP tags
	$content = '<' . '?' . 'php//' . $content . '?' . '>';

	if(file_exists($file)) {
		//Lets temporarily unlock the file if possible..
		// No longer attempting this, leaving if someone wants to uncomment these lines
		//$c = @chmod($file, 0766);
		//if($c === false) {
			//nope, it failed..
			//Leaving this for notifications ;)
		//}
	}
	else {
			//raise error.. 
	}

	//write it down
	$ret = @file_put_contents($file, $content);

	if($ret) {
		//Lets set it back to `closed`
		//$c = @chmod($file, 0755);

		//if($c === false) {
			//nope, it failed..
			//Leaving this for notifications @ADD in future version ;)
		//}
	}

	return $ret;
}

//Get values from the file and return them as array
function ziggeo_p_file_read($file) {

	//Lets check if it exists or not
	if(!file_exists($file)) { return false; }

	//Lets get the content
	$read = file_get_contents($file);

	if($read === false || $read === '' || strlen($read) < 10 ) { return false; }

	//Strip away php tags and the WP check related to direct file calls
	$read = substr($read, 7, -2);
	
	$read = trim($read);

	if( strlen($read) > 1 ) {
		$read = json_decode($read, true);
	}

	if($read)       { return $read; }

	return false;
}

// @CHECK - is not used, could be removed safely..
//retireves all files in a folder and returns array with their names, without the ones mentioned in $ignore
function ziggeo_p_file_get_all_in_dir($path, $ignore = array('.', '..', 'index.php'), $ignoreEnding = array('_class.php') ) {
	$ret = @scandir($path); //need @ to avoid getting an error if no files are present or it fails to load them..

	if($ret) {
		//quick way to remove the ones that match the ignore list..
		$ret = array_diff($ret, $ignore);
		$newDiff = array();

		if(is_array($ignoreEnding)) {
			//manually checking them as well, so that we can remove any 'ends with' files as well..
			foreach($ignoreEnding as $param) {
				foreach($ret as &$file) {
					if(stripos($file, $param) > -1){
						$newDiff[] = $file;
					}
				}
			}
		}

		if(count($newDiff) > 0) {
			$ret = array_diff($ret, $newDiff);
		}

		return $ret;
	}

	return false;
}
?>