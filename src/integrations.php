<?php

/*
    The main file to load all integrations and run them
*/

//Checking if WP is running or if this is a direct call..
defined('ABSPATH') or die();

class ZiggeoIntegrations {

    //location of where we keep the integration modules
    private $modulesPath = '';
    //holds php (module) file names without the extension. 
    private $integrationModules = null;
    //holds the integrations details that we use in the end
    private $integrations = null;

    //comparison values
    private $_NOT_AVAILABLE = -1;
    private $_BAD = 0;
    private $_OK = 1;
    private $_GOOD = 2;

    private function can_we_run_integration($plugin) {
        include_once($this->modulesPath . $plugin . '.php');

        if($this->get_integration_details($plugin)) {
            return true;
        }

        return false;
    }

    function __construct() {

        //Registering AJAX hook to be called over..
        add_action( 'wp_ajax_ziggeo_integrations', array($this, 'run_ajax'));

        //we do not need constructor for this, however just adding it here and we will see what we will do at the end
        $this->modulesPath = ZIGGEO_ROOT_PATH . 'modules/';

        //We can have the code that will run each time here
        $this->check_available_integrations();
        
        if($this->integrationModules === false) {
            //Not needed now, will be later ;)
        }
        elseif($this->integrationModules === null) {
            //No integrations available, exit this now.
            return false;
        }

        $this->is_base_available($this->integrationModules);

        //At this point we will need to check options from DB as well..
        $options = get_option('ziggeo_video');

        $tmp_changed = null;

        //check if we can run the integration based on version info..
        //it will populate $this->integrations with correct data..
        $this->versionsCheck();

        //We just need the part of the saved options that is related to integrations.
        if( isset($options['integrations']) ) {
            $opt_in = $options['integrations'];

            //we add it to the current integrations..
            foreach($this->integrations as $integration => &$data) {
                //We are now checking for disabled integrations.
                //Basically, we have already checked which ones can run and which ones can not, and now we will just check if customer wants one of those that can be run, to actually run..
                if(isset($opt_in[$integration])) {
                    if($opt_in[$integration]['active'] === true && $data['active'] === true) {
                        //It is set as active, and it is active through initial checks (it can be active)
                        //Should we do something here? Maybe notification 'loaded'
                    }
                    elseif($opt_in[$integration]['active'] === true && $data['active'] !== true) {
                        //It is activated by customer, however it is not possible to be run..
                        //definitely a place to raise an 'error' so that they can check it out..
                        $opt_in[$integration]['active'] = null;
                        $tmp_changed = true;
                    }
                    elseif($opt_in[$integration]['active'] !== true) {
                        $data['active'] = false;
                    }
                }
                else {
                    //Some integration seems to be added and not yet in DB..
                    //we load it by default in such case and make record in DB.
                    $opt_in[$integration] = array('active' => $data['active']);
                    $tmp_changed = true;
                }
            }
            $options['integrations'] = $opt_in;
        }
        else {
            if($this->integrations) {
                //There are no integrations records.. we should make some..
                //$options['integrations'] = $this->integrations; - we do not want to keep all details.. lets keep it small so that we can autoload options and not have them take long.
                $options['integrations'] = array();
                foreach($this->integrations as $integration => &$data) {
                    $options['integrations'][$integration] = array('active' => $data['active']);
                }

                $tmp_changed = true;     
            }
        }

        //if something is changed, lets update the options
        if($tmp_changed) {
            update_option('ziggeo_video', $options);
        }

        $this->run_integrations();
    }

    //How many integrations do we have available?
    public function check_available_integrations() {
        $files = ziggeo_file_get_all_in_dir($this->modulesPath);

        //No integrations found..
        if($files === false) { return null; }

        $this->integrationModules = array();

        foreach($files as $file) {
            $this->integrationModules[] = str_ireplace('.php', '', $file);
        }
 
        return $this->integrationModules;
    }

    //Check if it can run
    //returns associative array ('plugin_name' => true) || ('plugin_name' => false)
    public function is_base_available($plugin) {
        $check = array();

        //We will be passing array internally to check all of the plugins. However it might be needed to check one specific instead.
        if( is_array($plugin) ) {
            foreach($plugin as $name) {
                if($this->can_we_run_integration($name)) {
                    $check[] = $name;
                }
            }
        }

        //Check only the plugin that was asked for..
        else {
            if($this->can_we_run_integration($name)){
               $check[] = $name; 
            }
        }

        foreach($check as $name) {
            $f = 'ZiggeoIntegration_' . $name . '_checkbase';

            $this->integrations[$name] = $this->get_integration_details($name);

            //We are including the plugin.php so that we can run the is_plugin_active function..
            include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

            if($f() === true) {
                //OK, we have the integration module, it contains what it needs and it seems to have the base plugin (that we integrate to) present.
                $this->integrations[$name]['active'] = true;
            }
            else {
                //this one can not be active
                $this->integrations[$name]['active'] = false;
                $this->integrations[$name]['comparison'] = $this->_NOT_AVAILABLE;
            }
        }
    }

    //returns the details of each module
    public function get_integration_details($integration) {
        if(function_exists('ZiggeoIntegration_' . $integration . '_details')) {
            $f = 'ZiggeoIntegration_' . $integration . '_details';
            return $f();
        }

        return false;
    }

    //checks the version numbers based on integration setup and sees if it can be active or not..
    public function versionsCheck() {
        foreach($this->integrations as $integration => &$data) {

            if(!isset($data['comparison'])) {
                $data['comparison'] = array( 'base' => '', 'into' => '' );
            }

            if($data['comparison'] !== $this->_NOT_AVAILABLE) {
                $min = $this->_GOOD;
                $max = $this->_GOOD;

                //-checks against our plugin verion-

                //min checks
                if($data['requires_min'] === '') {
                    $min = $this->_OK;
                }
                //its less than what its needed..
                elseif(version_compare(ZIGGEO_VERSION, $data['requires_min'], '<')) {
                    $min = $this->_BAD;
                }
                //if we are not able to get proper info, it is not bad, however not entirely good neither..
                elseif(!version_compare(ZIGGEO_VERSION, $data['requires_min'], '>=')) {
                    $min = $this->_OK;
                }

                //max checks
                if($data['requires_max'] === '') {
                    $max = $this->_OK;
                }
                //its less than what its needed..
                elseif(version_compare(ZIGGEO_VERSION, $data['requires_max'], '>')) {
                    $max = $this->_BAD;
                }

                //if either is bad, we want to disable the integration..
                if($min === $this->_BAD || $max === $this->_BAD) {
                    $data['active'] = false;
                }

                //save the data so that we do not need to check it again later.
                $data['comparison']['base'] = array( 'min' => $min, 'max' => $max );

                //-checks against the plugin the integration is for-

                $min = $this->_GOOD;
                $max = $this->_GOOD;

                //get the plugin integrating to version
                $f = 'ZiggeoIntegration_' . $integration . '_getVersion';
                $data['plugin_version'] = $f();

                //min checks
                if($data['requires_min'] === '') {
                    $min = $this->_OK;
                }
                //its less than what its needed..
                elseif(version_compare($data['plugin_version'], $data['plugin_min'], '<')) {
                    $min = $this->_BAD;
                }
                //if we are not able to get proper info, it is not bad, however not entirely good neither..
                elseif(!version_compare($data['plugin_version'], $data['plugin_min'], '>=')) {
                    $min = $this->_OK;
                }

                //max checks
                if($data['plugin_max'] === '') {
                    $max = $this->_OK;
                }
                //its less than what its needed..
                elseif(version_compare($data['plugin_version'], $data['plugin_max'], '>')) {
                    $max = $this->_BAD;
                }
                
                //if either is bad, we want to disable the integration..
                if($min === $this->_BAD || $max === $this->_BAD) {
                    $data['active'] = false;
                }

                //save the data so that we do not need to check it again later.
                $data['comparison']['into'] = array( 'min' => $min, 'max' => $max );
            }
        }
    }

    //We can print all rows of integrations, or just one, defaulting on all. As it is print, this outputs the HTML code!
    public function print_integration_details() {
        ?>
        <ul class="ziggeo_integrations_list">
        <?php

        foreach($this->integrations as $integration => $details) {
            $bad = null;
            $bad2 = null;

            //We need to get this info to be able to compare everything..
            $f = 'ZiggeoIntegration_' . $integration . '_getVersion';
            $details['plugin_version'] = $f();

            ?>
            <li class="ziggeo_integrations_row">
                <div class="ziggeo_integration_left">
                    <img class="ziggeo_integration_logo" src="<?php echo $details['screenshot_url']; ?>">
                </div>
                <div class="ziggeo_integrations_right">
                    <div class="integration">
                        <b>Integration for:</b> <strong><?php echo $details['plugin_name']; ?></strong> - you can get it from <a href="<?php echo $details['plugin_url']; ?>" target="_blank"><?php echo $details['plugin_url']; ?></a>
                        <br>
                    </div>
                    <div class="author">
                        <b>Author:</b> <?php echo $details['author_name']; ?>
                            <?php
                                if( isset($details['author_url']) && $details['author_url'] !== '') {
                                    ?>
                                    @ <a href="<?php echo $details['author_url']; ?>" target="_blank"><?php echo $details['author_url']; ?></a><br>
                                <?php
                                }

                        //Checks to see if our plugin meets the minimal requirement of the version required by the integration itself
                        if($details['comparison']['base']['min'] === $this->_BAD) {
                            $bad = true;
                            ?>
                            <b class="warning">Per Author: The version of the Ziggeo WordPress plugin you are using is not compatible with the minimal required version for integration to work properly. Please upgrade the plugin to the latest (if you already did and still get this message, do let us know)</b>
                            <?php
                        }
                        //if we are not able to get proper info, it is not bad, however not entirely good neither..
                        elseif($details['comparison']['base']['min'] === $this->_OK) {
                            $bad = true;
                            ?>
                            <b class="warning">We were unable to verify if the integration will work properly with your version of our plugin. If you experience any issues try disabling the same.</b>
                            <?php
                        }

                        //Does our plugin meet the maximum version requirement as well?
                        if($details['comparison']['base']['max'] === $this->_BAD) {
                            $bad = true;
                            ?>
                            <b class="warning">Per Author: The version of the Ziggeo WordPress plugin you are using is not compatible with the required version for integration to work properly. Please contact author to upgrade the integration</b>
                            <?php
                        }
                        

                        //Lets check what is happening with the plugin that the integration integrates to - does it meet the requirements as well

                        //Is the plugin the integration is made into of proper minimal version?
                        if($details['comparison']['into']['min'] === $this->_BAD) {
                            $bad2 = true;
                            ?>
                            <b class="warning">Per Author: The version of the <u><?php echo $details['plugin_name']; ?></u> WordPress plugin you are using is not compatible with the minimal required version for integration to work properly. Please upgrade the plugin to the latest (if you already did and are still getting this message, do let us know)</b>
                            <?php
                        }
                        //if for some reason we can not make a valid check..
                        elseif($details['comparison']['into']['min'] === $this->_OK) {
                            $bad2 = true;
                            ?>
                            <b class="warning">We were unable to verify if the integration will work properly with your version of <u><?php echo $details['plugin_name']; ?></u> plugin. If you experience any issues try disabling the same.</b>
                            <?php
                        }

                        //If there is a top version of the plugin this integration can be used for, we should have it listed here..
                        if($details['comparison']['into']['max'] === $this->_BAD) {
                            $bad2 = true;
                            ?>
                            <b class="warning">Per Author: The version of the <u><?php echo $details['plugin_name']; ?></u> WordPress plugin you are using is not compatible with the required version for integration to work properly. Please contact author to upgrade the integration</b>
                            <?php
                        }

                        //If we did not hit any big obstacles, we should be all good to go for integration module -> our plugin
                        if($bad !== true) {
                            ?>
                            <b class="message">Per Author: The integration should work properly with your current version of our plugin</b>
                            <?php
                        }

                        //Is the plugin maybe not installed that we would integrate into? If it is, lets tell this
                        if($details['comparison'] === $this->_NOT_AVAILABLE) {
                            $bad2 = true;
                            ?>
                            <b class="message">Seems that your <u><?php echo $details['plugin_name']; ?></u> WordPress plugin is either not active, or you have not installed it yet. As such this integration is disabled.</b>
                            <?php
                        }

                        //If we did not hit any big obstacles, we should be all good to go for integration module -> some other plugin
                        if($bad2 !== true) {
                            ?>
                            <b class="message">Per Author: The integration should work properly with your current version of <u><?php echo $details['plugin_name']; ?></u> plugin</b>
                            <?php
                        }
                        ?>
                    </div>
                    <div>
                        <?php if($details['active']) {
                            ?>
                            <button class="integration_button active" disabled="disabled">Active</button>
                            <button class="integration_button" onclick="ziggeo_integration_status('<?php echo $integration; ?>', 'disable');">Disable</button>
                            <?php
                        }
                        else {
                            ?>
                            <button class="integration_button" onclick="ziggeo_integration_status('<?php echo $integration; ?>', 'activate');">Activate</button>
                            <button class="integration_button disabled" disabled="disabled">Disabled</button>
                            <?php
                        }
                        ?>
                    </div>
                </div>
            </li>
            <?php
        }
        ?>
        </ul>
        <?php
    }

    //runs the integrations depending on where we are
    public function run_integrations() {
        //if true, we run integrations that have 'both' or 'admin'
        if(is_admin()) {
            foreach($this->integrations as $integration => $details) {
                if($details['firesOn'] !== 'public') {
                    //check if we can actually run it..
                    if($details['active'] === true) {
                        //run this integration
                        $f = 'ZiggeoIntegration_' . $integration . '_run';
                        $f();                        
                    }
                }
            }
        }
        //we run integrations with 'public' or 'admin'
        else {
            foreach($this->integrations as $integration => $details) {
                if($details['firesOn'] !== 'admin') {
                    //check if we can actually run it..
                    if($details['active'] === true) {
                        //run this integration
                        $f = 'ZiggeoIntegration_' . $integration . '_run';
                        $f();
                    }
                }
            }
        }
    }

    //Function to return the answer to our AJAX request..
    public function run_ajax() {

        //we only process the call if it has what we are expecting..
        if(isset($_POST, $_POST['action'])){
            //Later on we could add a hook here so if someone else makes the integration, we can allow them to use our AJAX as well..

            $integration = $_POST['integration'];

            switch($integration){
                case 'GravityForms': {
                    $template = $_POST['template'];
                    //For gravity forms we need ajax to turn the template into the HTML code.
                    $tmp = ziggeo_content_replace_templates(array($template, $template));
                    $tmp = str_replace("\'", "'", $tmp);
                    echo $tmp;
                    break;
                }
                default: {
                }
            }
        }

        wp_die();            
    }
}

if( ($x = ($ziggeoIntegration = new ZiggeoIntegrations())) === false) {
    unset($ziggeoIntegration); //since we do not need it, lets release resources right away.
}


?>