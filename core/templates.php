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
	function ziggeo_p_templates_add($id, $value) {

		//Few fixes to ID in special cases when they might be needed
		//Check if the id is empty
		if(trim($id) === '') {
			$id = "ziggeo_template_" . rand(20, 3000);
		}

		//Lets right away put the ID into lowercase
		$id = strtolower($id);

		// Before we would check where the codes would be saved. Starting with v3.0, we save them at 2 places:
		// 1. Into the DB (saving JSON Object and shortcode), allowing us to easily edit the templates
		// 2. Into the files, allowing us to quickly read it and show the prepared template with minimal parsing

		$existing_templates = get_option('ziggeo_templates');

		// Since this is "add", we will reject the call when the ID already exists
		if(isset($existing_templates[$id])) {
			return false;
		}

		$existing_templates[$id] = $value;

		//This always returns false. We can either:
		//1. save the template over AJAX, without saving the settings
		//2. use own table

		$rez_db = update_option('ziggeo_templates', $existing_templates);

		// We will add it to the files only if the DB save passed through
		if($rez_db) {
			// Saving the shortcode version into the files
			$dir = ZIGGEO_DATA_ROOT_PATH;
			$file = $dir . '/custom_templates.php';

			$content = array ( $id => $value['shortcode'] );

			//In case directory does not exist, we will make one.
			if(!file_exists($dir)) {
				//We should exit with a message. We should not create the file, rather admin should manually create it with permissions they want, then we just use the same file..
				return false;
			}
			else {
				$shortcodes = array();

				// Go through templates and only use shortcode
				foreach($existing_templates as $id => $value) {
					$shortcodes[$id] = $value['shortcode'];
				}

				return ziggeo_p_file_write($file, $shortcodes);
			}
		}

		return false;
	}

	//If calling this function be careful, it will remove all other templates!
	function ziggeo_p_templates_add_all($templates, $file_write = false) {

		if($file_write !== true) {
			$rez = update_option('ziggeo_templates', $templates);
		}

		if($rez || $file_write === true) {
			$dir = ZIGGEO_DATA_ROOT_PATH;
			$file = $dir . '/custom_templates.php';
			//In case directory does not exist, we will make one.
			if(!file_exists($dir)) {
				//We should exit with a message. We should not create the file, rather admin should manually create it with permissions they want, then we just use the same file..
				return false;
			}
			else
			{
				$shortcodes = array();

				// Go through templates and only use shortcode
				foreach($templates as $id => $value) {
					$shortcodes[$id] = $value['shortcode'];
				}

				return ziggeo_p_file_write($file, $shortcodes);
			}
		}

		return false;
	}

	//Finds and updates the passed template
	function ziggeo_p_templates_update($old_id, $id, $content) {
		$updated = array();

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

		$rez = update_option('ziggeo_templates', $existing_templates);

		if( $rez ) {
			//path to custom templates file
			$file = ZIGGEO_DATA_ROOT_PATH . 'custom_templates.php';

			$shortcodes = array();
			// Go through templates and only use shortcode
			foreach($existing_templates as $id => $value) {
				if(isset($value['shortcode'])) {
					// templates v2
					$shortcodes[$id] = $value['shortcode'];
				}
				else {
					// templates v1
					$shortcodes[$id] = $value;
				}
			}

			return ziggeo_p_file_write($file, $shortcodes);
		}

		return false;
	}

	//Removes the template from the list of existing ones
	function ziggeo_p_templates_remove($id) {
		$updated = array();

		$existing_templates = get_option('ziggeo_templates');

		foreach($existing_templates as $existing => $value) {
			//find and skip
			if($existing !== $id) {
				$updated[$existing] = $value;
			}
		}

		$rez = update_option('ziggeo_templates', $updated);

		if($rez) {
			//path to custom templates file
			$file = ZIGGEO_DATA_ROOT_PATH . 'custom_templates.php';

			$shortcodes = array();

			// Go through templates and only use shortcode
			foreach($updated as $id => $value) {
				if(isset($value['shortcode'])) {
					// templates v2
					$shortcodes[$id] = $value['shortcode'];
				}
				else {
					// templates v1
					$shortcodes[$id] = $value;
				}
			}

			return ziggeo_p_file_write($file, $shortcodes);
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
	// Searches DB (backend calls) by default, can be overridden with "file" value
	function ziggeo_p_templates_index($specific = 'db') {

		$ret = null;

		if($specific !== 'db') {
			$is_files = true;
		}
		else {
			$is_files = false;
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
	// This is used on both sides, however mostly on frontend requests, so should be set to read files
	function ziggeo_p_template_exists($id = null, $deprecated = null) { 
		//If we do not pass anything we do not want to parse the templates..
		if(!$id) {
			return false;
		}

		//Lets get a list of all existing templates
		$index = ziggeo_p_templates_index('file');

		$id = trim($id);

		if( isset($index, $index[$id]) )
		{
			//yey we found it, lets get it sent back for processing :)
			return $index[$id];
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
	// template system: v1
	function ziggeo_p_template_params($id = null) {

		if($id === null) {
			return false;
		}

		$rez = ziggeo_p_template_exists($id);

		//OK, template exists, lets parse it
		if($rez) {
			$rez = substr($rez, stripos($rez, ' '), -1);
			return $rez;
		}

		return false;
	}

	// Retrieves template parameters as well as template part or false if not available
	// template system: v2
	function ziggeo_p_template_get_params($id = null) {

		if($id === null) {
			return false;
		}

		$rez = ziggeo_p_template_exists($id);

		//OK, template exists, lets parse it
		if($rez) {

			$type = substr($rez, 0, strpos($rez, ' '));
			$params = substr($rez, strpos($rez, ' '), -1);

			return array(
				'type'      => $type,
				'params'    =>  $params
			);
		}

		return false;
	}

	// Adds ziggeo- as a prefix to each parameter in the string
	// template system: v2
	// this will actually not be needed as we will pre-save the templates
	function ziggeo_p_template_prefix_params($parameters = null) {

		if($parameters === null) {
			return array(
				'status' => 'error',
				'result' => 'No parameters provided'
			);
		}

		// This will not be needed later as we update the code @here
		$parameters = stripslashes($parameters);

		$result = '';
		$param_array = explode(' ', $parameters);

		foreach($param_array as $param) {
			if($param !== '') {
				$result .= ' ziggeo-' . $param;
			}
		}

		return $result;
	}


	//Changes the output from chortcode to object like code
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

	// Prepares the template code into the template Object we use in v3.0
	function ziggeo_p_template_code_to_object($code = null, $escape = false) {

		$obj = '';
		$base = '';
		$params = '';
		$params_spaced = '';

		// obj = {"base": "[ziggeorecorder", "params": {"param1": "value1"}}

		if($code === null) { return ''; }

		$code = stripslashes($code);

		$t_bracket = strpos($code, '{');

		if($t_bracket > -1 && $t_bracket < 4) {
			return $code; // it looks like it is object already (would likely be 0 or 1 position,
			              // 4 is just in case we end up with a space in front)
		}

		// At this point we want to be safe, so we should check for existance of any parameters that have space
		$spaced_parameters = array(
			'title',
			'description'
		);

		// A hook to allow you to add custom parameters into this list.
		// Note: No need to hook into this if your code is built after 2022-08-24, this is for legacy support
		$spaced_parameters = apply_filters('ziggeo_template_to_object_parsing_spaced_parameters', $spaced_parameters);

		// Now we try to understand and parse the ones that can be found with a space.
		for($i = 0, $c = count($spaced_parameters); $i < $c; $i++) {
			$start = strpos($code, $spaced_parameters[$i] . '=');
			if($start > -1) {
				$t_quote_position = $start + strlen($spaced_parameters[$i]) + 1;
				$t_quote_mark = $code[$t_quote_position]; // can be ' or ", this helps us know

				$t_length = strpos($code, $t_quote_mark, $t_quote_position+1) - $t_quote_position;

				$t_value = substr($code, $t_quote_position+1, $t_length-1);
				$t_value = str_replace('&apos;', '\'', $t_value);

				if($params_spaced !== '') {
					$params_spaced .= ',';
				}

				$params_spaced .= '"' . $spaced_parameters[$i] . '":"' . addslashes($t_value) . '"';

				// This way we do not have it cause issues parsing in the later part of this code.
				$code = substr($code, 0, $start) . substr($code, $t_quote_position + $t_length + 1);
			}
		}


		// The space can be used in many different fields, which is why this is not good way, however this is why
		// we need to move from the old version of parameters to new one
		$t_code = explode(' ', $code);

		foreach($t_code as $t_piece) {
			if(strpos($t_piece, '[ziggeo') > -1) {
				$base .= '"base":"' . trim($t_piece, ' \'"]') . '",';
			}
			else {
				// We need to get the key and value
				$t_param = explode('=', $t_piece);

				$t_param[0] = trim($t_param[0]);

				if($t_param[0] !== '' && $t_param[0] !== '[' &&  $t_param[0] !== ']') {

					if($params === '') {
						$params = '"params":{';
					}
					else {
						$params .= ',';
					}

					if(isset($t_param[1])) {
						$t_param[1] = trim($t_param[1], '\'" ]');
						$params .= '"' . $t_param[0] . '":"' . $t_param[1] . '"';
					}
					else {
						$params .= '"' . $t_param[0] . '":true';
					}
				}


			}
		}

		if($base === '') {
			$base = '"base":"[ziggeorecorder",';
		}
		if($params_spaced != '') {
			if($params === '') {
				$params = '"params":{';
			}
			else {
				$params .= ',';
			}

			$params .= $params_spaced;
		}
		if($params === '') {
			$params = '"params":{';
		}

		$obj = '{' . $base . $params . '}}';

		// If true, we are going to export this into HTML code, so we need to make it a non standard JSON by switching
		// quotes to escaped single quote \'
		if($escape === true) {
			$obj = str_replace('"', '\'', $obj);
		}

		return $obj;
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




