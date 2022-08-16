<?php

//
//	This file brings the core functionality needed for working with tempaltes
//

// INDEX:
// 1. Hooks
//		1.1. hook:'comments_template'
// 2. General functionality
//		2.1. ziggeo_p_templates_add()
//		2.2. ziggeo_p_templates_add_all()
//		2.3. ziggeo_p_templates_update()
//		2.4. ziggeo_p_templates_remove()
//		2.5. ziggeo_p_templates_remove_all()
//		2.6. ziggeo_p_templates_index()
//		2.7. ziggeo_p_template_exists()
// 3. Complex template managment
//		3.1. ziggeo_p_template_params()
//		3.2. ziggeo_p_template_params_as_object()
//		3.3. ziggeo_p_template_add_replace_parameter_value()
// 4. Templates in Comments
//		4.1. ziggeo_comments_template()


//Checking if WP is running or if this is a direct call..
defined('ABSPATH') or die();


/////////////////////////////////////////////////
// 1. HOOKS
/////////////////////////////////////////////////

	add_filter("comments_template", "ziggeo_comments_template");




/////////////////////////////////////////////////
// 2. GENERAL FUNCTIONALITY
/////////////////////////////////////////////////

	//Adds new templates to the currently existing ones
	function ziggeo_p_templates_add($id, $value, $specific = null) {

		//Few fixes to ID in special cases when they might be needed
		//Check if the id is empty
		if(trim($id) === '') {
			$id = "ziggeo_template_" . rand(20, 3000);
		}

		//Lets right away put the ID into lowercase
		$id = strtolower($id);

		$option = ziggeo_get_plugin_options('templates_save_to');

		if( $specific === 'files' || ($specific === null && $option === "files" )) {

			//path to userData directory 
			$dir = ZIGGEO_DATA_ROOT_PATH;
			$file = $dir . '/custom_templates.php';

			$content = array ( $id => $value );

			//In case directory does not exist, we will make one.
			if(!file_exists($dir)) {
				//We should exit with a message. We should not create the file, rather admin should manually create it with permissions they want, then we just use the same file..
				return false;
			}
			else {
				if($current = ziggeo_p_file_read($file)) {
					//This way the new data updates the old one, if array keys match..
					//@TODO - the name should be unique, so do we want to record an error for our customer to know that they had used the name that was previously used in some other template, or do we just overwrite it? -> report otherwise leave as is.
					$content = array_merge($current, $content);
				}

				return ziggeo_p_file_write($file, $content);
			}
		}
		//else save to DB, which is now a new default
		else {

			$existing_templates = get_option('ziggeo_templates');

			$existing_templates[$id] = $value;

			//This always returns false. We can either:
			//1. save the template over AJAX, without saving the settings
			//2. use own table

			return update_option('ziggeo_templates', $existing_templates);
		}
	}

	//If calling this function be careful, it will remove all other templates!
	function ziggeo_p_templates_add_all($templates, $specific = null) {
		$option = ziggeo_get_plugin_options('templates_save_to');

		if( $specific === 'files' || ($specific === null && $option === "files" )) {

			//path to userData directory 
			$dir = ZIGGEO_DATA_ROOT_PATH;
			$file = $dir . '/custom_templates.php';
			//In case directory does not exist, we will make one.
			if(!file_exists($dir)) {
				//We should exit with a message. We should not create the file, rather admin should manually create it with permissions they want, then we just use the same file..
				return false;
			}
			else
			{
				return ziggeo_p_file_write($file, $templates);
			}
		}
		//else save to DB, which is now a new default
		else {
			return update_option('ziggeo_templates', $templates);
		}
	}

	//Finds and updates the passed template
	function ziggeo_p_templates_update($old_id, $id, $content) {
		$updated = array();

		$option = ziggeo_get_plugin_options('templates_save_to');

		if( $option === "files" ) {
			//path to custom templates file
			$file = ZIGGEO_DATA_ROOT_PATH . 'custom_templates.php';

			//grab all
			if($current = ziggeo_p_file_read($file)) {
				foreach($current as $template => $value) {
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
				return ziggeo_p_file_write($file, $updated);
			}
		}
		//save to DB
		else {
			$existing_templates = get_option('ziggeo_templates');

			if(isset($existing_templates[$old_id])) {
				if($old_id !== $id) {
					unset($existing_templates[$old_id]);
					$existing_templates[$id] = $content;
				}
				else {
					$existing_templates[$old_id] = $content;
				}
			}

			return update_option('ziggeo_templates', $existing_templates);
		}

		return false;
	}

	//Removes the template from the list of existing ones
	function ziggeo_p_templates_remove($id) {
		$updated = array();

		$option = ziggeo_get_plugin_options('templates_save_to');

		if( $option === "files" ) {
			//path to custom templates file
			$file = ZIGGEO_DATA_ROOT_PATH . 'custom_templates.php';

			//grab all
			if($current = ziggeo_p_file_read($file)) {
				foreach($current as $template => $value) {
					//find old and skip it
					if( ($template !== $id) && (trim($template) !== $id) ) {
						$updated[$template] = $value;
					}
				}

				//save all
				return ziggeo_p_file_write($file, $updated);
			}
		}
		//remove it from the DB
		else {
			$existing_templates = get_option('ziggeo_templates');

			foreach($existing_templates as $existing => $value) {
				//find and skip
				if($existing !== $id) {
					$updated[$existing] = $value;
				}
			}

			return update_option('ziggeo_templates', $updated);
		}

		return false;
	}

	//Removes all templates. Should not be called lightly
	// This will not remove them from pages, just from the list of templates in panel. It is up to you to make sure those are found, updated and/or removed
	function ziggeo_p_templates_remove_all() {
		//path to custom templates file
		$file = ZIGGEO_DATA_ROOT_PATH . 'custom_templates.php';

		//Remove templates from DB
		update_option('ziggeo_templates', array());

		//Remove templates from files
		if(ziggeo_p_file_read($file)) {
			//We only do this if the file already exits
			ziggeo_p_file_write($file, array());
		}
	}

	//Searches for all existing templates. Returns the list or false if none
	function ziggeo_p_templates_index($opposite = false, $specific = '') {

		$option = ziggeo_get_plugin_options('templates_save_to');
		$ret = null;

		//to make it easier to check with opposite parameter
		$is_files = ($option === "files") ? true : false;

		if($specific !== '') {
			if($specific === 'files') {
				$is_files = true;
			}
			else {
				$is_files = false;
			}
		}
		else {
			if( ($is_files && $opposite === false) || ($is_files === false && $opposite === true) ) {
				$is_files = true;
			}
			else {
				$is_files = false;
			}
		}

		if( $is_files === true ) {

			//path to custom templates file
			$file = ZIGGEO_DATA_ROOT_PATH . 'custom_templates.php';

			$ret = ziggeo_p_file_read($file);

			if($ret) {
				//If there are double quotes, it would cause issues with TinyMCE, however with templates editing as well.
				//Since this is called for templates only, we know that we are OK with changing all double quotes into single quotes..
				$ret = str_replace('"', "'", $ret);        
			}
		}
		//read from DB
		else
		{
			$ret = get_option('ziggeo_templates');
		}

		return $ret;
	}

	//returns the list of templates and their code if they match the desired type of template, otherwise empty array
	function ziggeo_p_templates_index_type($template_type) {
		//get all templates
		$templates = ziggeo_p_templates_index();

		//default
		$result = array();

		//Go through list and get only the ones matching template type
		if($templates) {

			foreach ($templates as $template_id => $template_code) {
				if(stripos($template_code, $template_type) > -1) {
					$result[] = array(
						'id'	=> $template_id,
						'code'	=> $template_code
					);
				}
			}
		}

		return $result;
	}

	//Checks if the template with specified ID exists or not
	function ziggeo_p_template_exists($id, $opposite = false) { 
		//If we do not pass anything we do not want to parse the templates..
		if(!$id) {
			return false;
		}

		//Lets get a list of all existing templates
		$index = ziggeo_p_templates_index($opposite);

		$id = trim($id);

		if( isset($index, $index[$id]) )
		{
			//yey we found it, lets get it sent back for processing :)
			return $index[$id];
		}

		//Since this might be called (and should be) to bring out embedding code, for example to show embedding on a post it might be good to check both files and DB, not just one.. plus this way it will only check the other if the first go failed.
		if($opposite === false) {
			return ziggeo_p_template_exists($id, true);
		}

		//if we did not find it, lets just return false..
		return false;
	}

	//Function that tries to deliver the string of a template in a safe manner. On fail it returns empty string
	function ziggeo_p_get_template_code_safe($id = '') {
		$code = ziggeo_p_template_exists($id);

		if($code === false) {
			return '';
		}

		//We first escape any single quotes
		$code = str_replace("'", "\'", $code);
		//Now we fix if we added double quotes by some chance:
		$code = str_replace("\\\'", "\'", $code);
		//We replace the double quotes with single quotes
		$code = str_replace('"', "'", $code);

		return $code;
	}




/////////////////////////////////////////////////
// 3. COMPLEX TEMPLATE MANAGEMENT
/////////////////////////////////////////////////

	//Retrieves template parameters only based on template ID or false if not available.
	function ziggeo_p_template_params($id) {

		$rez = ziggeo_p_template_exists($id);

		//OK, template exists, lets parse it
		if($rez) {
			$rez = substr($rez, stripos($rez, ' '), -1);
			return $rez;
		}

		return false;
	}

	//Changes the output from HTML to object like code
	function ziggeo_p_template_params_as_object($id, $code = null) {

		if($code === null) {
			$parameters = ziggeo_p_template_params($id);
		}
		else {
			$parameters = $code;
		}

		if($parameters) {
			//width=222 height=222 perms=\"allowupload\"
			// width=640 height=480 face_outline perms='allowupload' limit=30

			$parameters = trim($parameters);
			$parameters = '"' . $parameters;

			$parameters = str_replace('=', '":', $parameters);
			$parameters = str_replace("\'", "'", $parameters);
			$parameters = str_replace('\"', "'", $parameters);
			$parameters = str_replace("\'", "'", $parameters);

			//in the end we also remove the prefix, since JS version is not using it
			$parameters = str_replace('ziggeo-', '', $parameters);

			//we need to trim away any unwanted spaces
			$parameters = trim($parameters);

			//this could lead to issues if there is a value with space! however this is needed for the object
			$parameters = str_replace(' ', ',"', $parameters);

			//"width":640,"height":480,"face_outline":true,"perms":'allowupload',"limit":30

			return $parameters;
		}

		return false;
	}

	//adds, appends or replaces the current value set in the template
	// > embedding_string - the body of the template
	// > parameter - the parameter that we are after in the template
	// > value - new value to add / replace with
	// > action - do we want it to add to or replace the current value?
	// > connector - what are we using to connect the old and new parameter value (needed if action is 'append')
	function ziggeo_p_template_add_replace_parameter_value($embedding_string, $parameter, $value, $action = 'append', $connector = '+') {
		//the parameter was already added to the template
		if(stripos($embedding_string,$parameter) > -1) {
			//the parameter was set before by the template, so we will add this one to it as well.
			//get the location where our parameter starts
			$start = stripos($embedding_string, $parameter . '=');
			//get the end of the parameter value
			$end = strpos($embedding_string, ' ', $start);
			//the length of parameter
			$len = strlen($parameter);

			//lets get the current value
			$current = substr($embedding_string, ($start+$len), ($end-$start-$len));

			//It could have single or double quotes around it, while it does not need to (if it is bool type it would not even have = while int does not require quotes)
			if(strpos($current, '=') > -1 ) {
				//not a bool
				//1. it can, however it does not need to have =
				//2. it can, however id does not need to have ''
				//3. it can, however id does not need to have ""
				$current = str_replace(array('=', '"', "'"), '', $current);
			}

			//append the parameter to the previous one
			if($action === 'append') {
				$embedding_string = str_ireplace($current, $current . $connector . $value, $embedding_string);
			}
			//replace the previous value with the one sent..
			else {
				$embedding_string = str_ireplace($current, $value, $embedding_string);
			}
		}
		//The parameter did not exist in the template before
		else {
			$replace = ' ' . $parameter . '="' . $value . '" ';
			$firstSpace = strpos($embedding_string, ' ');
			$embedding_string = substr($embedding_string, 0, $firstSpace) . $replace . substr($embedding_string, $firstSpace);
		}
		return $embedding_string;
	}




/////////////////////////////////////////////////
// 4. TEMPLATES IN COMMENTS
/////////////////////////////////////////////////

	//This allows anyone to create custom comments video template to use in favor of our default setup
	if(!function_exists('ziggeo_comments_template')) {
		function ziggeo_comments_template($comment_template) {
			
			$option = ziggeo_get_plugin_options('modify_comments');
			if($option === ZIGGEO_YES) {
				return ZIGGEO_ROOT_PATH . "templates/handle_comments.php";
			}
		}
	}




