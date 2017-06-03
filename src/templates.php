<?php
//Checking if WP is running or if this is a direct call..
defined('ABSPATH') or die();

function ziggeo_url_endswith($needle) {
    global $_SERVER;
    $haystack = $_SERVER["PHP_SELF"];
    return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== FALSE);
}

//To check the PHP_SELF only when we are in the back
if(is_admin()) {
    if (ziggeo_url_endswith("/wp-admin/post-new.php") || ziggeo_url_endswith("/wp-admin/edit.php") || ziggeo_url_endswith($_SERVER["PHP_SELF"])) {
        add_action('admin_head', "ziggeo_post_template");
    }
}

function ziggeo_post_template() {
    include(ZIGGEO_ROOT_PATH . "templates/post_template.php");
}

function ziggeo_comment_template($comment_template) {
    return ZIGGEO_ROOT_PATH . "templates/comments_template.php";
}

add_filter("comments_template", "ziggeo_comment_template");

//----------------------------------------------------------------------------

// Custom templates file: 'custom_templates.php'
// For now, all files will be written with fwrite and fread.. This is fastest method and allows us to avoid asking customer about their details. It would also not be a very good method for reading files in either case since when pages are loaded, we need to run the same, not to wait for input about credentials..
//after this is working right, we can add notifications about the files created so that customers can modify them if needed, and have a fallback on the file parser using WP filesystem..

//Adds new templates to the currently existing ones
function ziggeo_templates_add($id, $value) {

    //path to userData directory 
    $dir = ZIGGEO_DATA_ROOT_PATH;
    $file = $dir . '/custom_templates.php';

    $content = array ( $id => $value );

    //In case directory does not exist, we will make one.
    if(!file_exists($dir)) {
        if(@mkdir($dir, 0777) === false) {

            //We have this error here and under ziggeo_file_write, since we do other checks there as well. The same error will however not be called twice
//            add_settings_error('templates_editor',
//                            'file_write_action',
//                            'There were issues creating directory. If you were not shown WordPress credentials form, the action failed.
//                                Please see more about it here: <a href="https://ziggeo.com/wordpress-plugin-filesystem" target="_blank">How to create setup files in our WordPress plugin?</a>',
//                            'error');

            //This has failed.. we need to create it in a different manner..
            $form_fields = array ('templates_editor', 'templates_id');
            return ziggeo_file_WP_prepare('write', $form_fields, $file, $content);
        }

        return ziggeo_file_write($file, $content);
    }
    else
    {
        if($current = ziggeo_file_read($file)) {
            //This way the new data updates the old one, if array keys match..
            //@TODO - the name should be unique, so do we want to record an error for our customer to know that they had used the name that was previously used in some other template, or do we just overwrite it? -> report otherwise leave as is.
            $content = array_merge($current, $content);
        }

        return ziggeo_file_write($file, $content);
    }
}

//Finds and updates the passed template
function ziggeo_templates_update($old_id, $id, $content) {
    $updated = array();

    //path to custom templates file
    $file = ZIGGEO_DATA_ROOT_PATH . 'custom_templates.php';

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
    $file = ZIGGEO_DATA_ROOT_PATH . 'custom_templates.php';

    //grab all
    if($current = ziggeo_file_read($file)) {
        foreach($current as $template => $value)
        {
            //find old and skip it
            if( ($template !== $id) && (trim($template) !== $id) ) {
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
    $file = ZIGGEO_DATA_ROOT_PATH . 'custom_templates.php';

    $ret = ziggeo_file_read($file);

    if($ret) {
        //If there are double quotes, it would cause issues with TinyMCE, however with templates editing as well.
        //Since this is called for templates only, we know that we are OK with changing all double quotes into single quotes..
        $ret = str_replace('"', "'", $ret);        
    }

    return $ret;
}

//Checks if the template with specified ID exists or not
function ziggeo_template_exists($id)
{
    //If we do not pass anything we do not want to parse the templates..
    if(!$id) {
        return false;
    }

    //Lets get a list of all existing templates
    $index = ziggeo_templates_index();

    $id = trim($id);

    if( isset($index, $index[$id]) )
    {
        //yey we found it, lets get it sent back for processing :)
        return $index[$id];
    }

    //if we did not find it, lets just return false..
    return false;
}

//Retrieves template parameters only based on template ID returns false if not available.
function ziggeo_template_params($id) {

    $rez = ziggeo_template_exists($id);

    //OK, template exists, lets parse it
    if($rez) {
        $rez = substr($rez, stripos($rez, ' '), -1);
        return $rez;
    }

    return false;
}

//function is OK for v1, however for v2 it would cause issues with parameters using minus (dash) as if turned into
// object as is, it would be two variables, instead of name, and error out..
// TODO: Add code to handle this 
function ziggeo_template_params_as_object($id) {
	$parameters = ziggeo_template_params($id);

	if($parameters) {
		//width=222 height=222 perms=\"allowupload\"
		// width=640 height=480 face_outline perms='allowupload' limit=30

		$parameters = trim($parameters);
		$parameters = '"' . $parameters;

		$parameters = str_replace('=', '":', $parameters);
		$parameters = str_replace("\'", "'", $parameters);
		$parameters = str_replace('\"', "'", $parameters);
		$parameters = str_replace("\'", "'", $parameters);

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
// > embeddingString - the body of the template
// > parameter - the parameter that we are after in the template
// > value - new value to add / replace with
// > action - do we want it to add to or replace the current value?
// > connector - what are we using to connect the old and new parameter value (needed if action is 'append')
function add_replace_template_parameter_value($embeddingString, $parameter, $value, $action = 'append', $connector = '+') {
    //the parameter was already added to the template
    if(stripos($embeddingString,$parameter) > -1) {
        //the parameter was set before by the template, so we will add this one to it as well.
        //get the location where our parameter starts
        $start = stripos($embeddingString, $parameter . '=');
        //get the end of the parameter value
        $end = strpos($embeddingString, ' ', $start);
        //the length of parameter
        $len = strlen($parameter);

        //lets get the current value
        $current = substr($embeddingString, ($start+$len), ($end-$start-$len));

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
            $embeddingString = str_ireplace($current, $current . $connector . $value, $embeddingString);
        }
        //replace the previous value with the one sent..
        else {
            $embeddingString = str_ireplace($current, $value, $embeddingString);
        }
    }
    //The parameter did not exist in the template before
    else {
        $replace = ' ' . $parameter . '="' . $value . '" ';
        $firstSpace = strpos($embeddingString, ' ');
        $embeddingString = substr($embeddingString, 0, $firstSpace) . $replace . substr($embeddingString, $firstSpace);
    }
    return $embeddingString;
}