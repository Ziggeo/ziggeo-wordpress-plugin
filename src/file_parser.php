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
function ziggeo_file_write($file, $content, $context = false) {

    //encode content as JSON
    $content = json_encode($content);

    //If we already have an error, lets go back..
    if(!$content)   { return null; }

    //add PHP tags
    $content = '<' . '?' . 'php//' . $content . '?' . '>';

    if(file_exists($file)) {
        //Lets temporarily unlock the file if possible..
        $c = @chmod($file, 0766);
        if($c === false) {
            //nope, it failed..
            //Leaving this for notifications ;)
        }
    }
    else {
            //raise error.. 
    }

    //write it down
    $ret = @file_put_contents($file, $content);

    //the file writing has failed
    if($ret === false) {
        //lets use WP file system since customer has different users set.
        if($context === 'templates' || $context === false){
            $form_fields = array ('templates_editor', 'templates_id');
            $context = 'ziggeo_templates_id';
        }

        //lets notify customer that there were some issues along the way..
        add_settings_error($context,
                            'file_write_action',
                            'There were issues creating directory. If you were not shown WordPress credentials form, the action failed.
                                    Please see more about it here: <a href="https://ziggeo.com/wordpress-plugin-filesystem" target="_blank">How to create setup files in our WordPress plugin?</a>',
                            'error');

        //later we will check other things here as well.

        $ret = ziggeo_file_WP_prepare('write', $form_fields, $file, $content);
    }

    if($ret) {
        //Lets set it back to `closed`
        $c = @chmod($file, 0755);

        if($c === false) {
            //nope, it failed..
            //Leaving this for notifications ;)
        }
    }

    return $ret;
}

//Get values from the file and return them as array
function ziggeo_file_read($file) {

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

/// -- These functions will only work with WP initialized and should not be called otherwise --- ///

//WP filesystem parsing

//Checks if file exists and if so updates it, otherwise creates one in 'userData' directory
//It must be called through ziggeo_file_WP_prepare()
function ziggeo_file_WP_write($file, $content) {
    global $wp_filesystem;

    //We have all we need, now just to save the file..
    //$fileName = trailingslashit( $wp_filesystem->wp_plugins_dir ) . $fileName;

    //At this point this should be an array holding the old values as well as the new ones..
    $content = json_encode($content);

    //create file
    if ( !$wp_filesystem->put_contents( $file, $content, FS_CHMOD_FILE) ) {
        return false;
    }
}

//Prepares everything so that we can write to file or read from the same..
function ziggeo_file_WP_prepare($action, $form_fields, $file, $content) {
    global $wp_filesystem;

    wp_verify_nonce('ziggeo_video_nonce', 'ziggeo_nonce_action');

    $url = wp_nonce_url('options-general.php?page=ziggeo_video','ziggeo_nonce_action');

    //Lets grab credentials if we do not have it..
    if( ($credentials = request_filesystem_credentials($url, '', false, ZIGGEO_ROOT_PATH, $form_fields, true) ) === false ) {
        
        //Customer did not enter these details before, so lets wait for input since the form is shown..
        ?><div class="wrap">
            <h1>We need few details to complete this action</h1>
            <p>Seems that you are running a secure setup on your server, which means that we can not write a file on the same. To help us with this we are calling
            WordPress functions that will allow you to share FTP details in secure way and allow us to use the same (through other WordPress functions) to save
            the files to your server.</p>
        </div> <?php //closing the .wrap above
        return null;
    }

    // We have the needed credentials, lets see if we can roll some wheels
    if( !WP_Filesystem($credentials, ZIGGEO_ROOT_PATH, true) ) {
        //Password is easy to be entered wrong, and since something is wrong, letting the user know with the error
        request_filesystem_credentials($url, '', true, ZIGGEO_ROOT_PATH, $form_fields, true);
        ?>
        <div class="wrap">
            <h1>We need few details to complete this action</h1>
            <p>Seems that you are running a secure setup on your server, which means that we can not write a file on the same. To help us with this we are calling
            WordPress functions that will allow you to share FTP details in secure way and allow us to use the same (through other WordPress functions) to save
            the files to your server.</p>
        </div> <?php //closing the .wrap above
        return null;
    }

    //To show the errors that happened - if any
    if ( $wp_filesystem->errors->get_error_code() ) {
        foreach ( $wp_filesystem->errors->get_error_messages() as $message ) {
                show_message($message);
        }

        echo '</div>';
        return false;
    }

    //We got it all set up and good to go..now lets do what we wanted to do..

    //We define this here, so that we can use it without further recalculations - the wp_filesystem should be available at this time.
    define('ZIGGEO_DATA_ROOT_PATH_FS', str_replace(ABSPATH, $wp_filesystem->abspath(), ZIGGEO_DATA_ROOT_PATH));

    //Since all data should be in "userData" folder, if the same does not exist, we can simply return false on reading action and create it on write action..
    if( !$wp_filesystem->is_dir( ZIGGEO_DATA_ROOT_PATH_FS) ) {
        if($action === 'write') {
            $wp_filesystem->mkdir(ZIGGEO_DATA_ROOT_PATH_FS);
        }
        else {
            return false;
        }
    }

    if($action === 'write') {
            //Read the entire file, then add this back
            return ziggeo_file_WP_write($file, $content);
    }
    else {
        //Since we have the path and file already set, we check it here for the same
        if(!$wp_filesystem->exists($file)) {
            return false;
        }

        //return ziggeo_file_WP_read($file, $content); //only website admin can read file with credentials..we do not want that.
        return file_get_contents($file, $content);
    }
}

//retireves all files in a folder and returns array with their names, without the ones mentioned in $ignore
function ziggeo_file_get_all_in_dir($path, $ignore = array('.', '..', 'index.php'), $ignoreEnding = array('_class.php') ) {
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