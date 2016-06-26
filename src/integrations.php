<?php

/*
    The main file to load all integrations and run them
*/

//Checking if WP is running or if this is a direct call..
defined('ABSPATH') or die();

class ZiggeoIntegrations {

//  + check how many integrations we have and list them
//  +run each integration module to check if their base (plugin we are integrating to is installed and active)
//      each module should implement this, base just calls it and does what is needed based on response - disables the plugin for example.
//      run each integration module to check if they support current Ziggeo version or require some other version instead.
//  run modules of active plugins to make them integrate into other plugins.
//      run admin side
//      run public side

    //location of where we keep the integration modules
    private $modulesPath = '';
    //holds php (module) file names without the extension. 
    private $integrationModules = null;
    //holds the integrations details that we use in the end
    private $integrations = null;

    private function can_we_run_plugin($plugin) {
        include_once($this->modulesPath . $plugin . '.php');

        if($this->get_plugin_details($plugin)) {
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

        //We just need the part of the saved options that is related to integrations.
        if( isset($options['integrations']) ) {
            $opt_in = $options['integrations'];

            //we add it to the current integrations..
            foreach($this->integrations as $integration => &$data) {
                //We are now checking for disabled integrations.
                //Basically, we have already checked which ones can run and which ones can not, and now we will just check if customer wants one of those that can be run, to actually run..
                if(isset($opt_in[$integration])) {
                    if($opt_in[$integration]['active'] === true && $data['active'] === true) {
                        //It is set as active, and it is active through initial the checks (it can be active)
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

    //Check if needed to run
    //returns associative array ('plugin_name' => true) || ('plugin_name' => false)
    public function is_base_available($plugin) {
        $check = array();

        //We will be passing array internally to check all of the plugins. However it might be needed to check one specific instead.
        if( is_array($plugin) ) {
            foreach($plugin as $name) {
                if($this->can_we_run_plugin($name)) {
                    $check[] = $name;
                }
            }
        }

        //Check only the plugin that was asked for..
        else {
            if($this->can_we_run_plugin($name)){
               $check[] = $name; 
            }
        }

        foreach($check as $name) {
            $f = 'ZiggeoIntegration_' . $name . '_checkbase';

            $this->integrations[$name] = $this->get_plugin_details($name);

            //We are including the plugin.php so that we can run the is_plugin_active function..
            include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

            if($f() === true) {
                //OK, we have the integration module, it contains what it needs and it seems to have the base plugin (that we integrate to) present.
                $this->integrations[$name]['active'] = true;
            }
            else {
                //this one can not be active
                $this->integrations[$name]['active'] = false;
            }
        }
    }

    //returns the details of each module
    public function get_plugin_details($plugin) {
        if(function_exists('ZiggeoIntegration_' . $plugin . '_details')) {
            $f = 'ZiggeoIntegration_' . $plugin . '_details';
            return $f();
        }

        return false;
    }

    //We can print all rows of integrations, or just one, defaulting on all. As it is print, this outputs the HTML code!
    public function print_integration_details() {
        ?>
        <ul class="ziggeo_integrations_list">
        <?php

        foreach($this->integrations as $integration => $details) {
            $bad = null;
            ?>
            <li class="ziggeo_integrations_row">
                <div class="ziggeo_integration_left">
                    <img class="ziggeo_integration_logo" src="<?php echo $details['screenshot_url']; ?>">
                </div>
                <div class="ziggeo_integrations_right">
                    <div class="integration">
                        <b>Integration for:</b> <strong><?php echo $details['plugin_name']; ?></strong> - you can get it from <a href="<?php echo $details['plugin_url']; ?>" target="_blank"><?php echo $details['plugin_url']; ?></a>
                        <br>
                        <?php
//@TODO - we will need to see if there is a way for us to get older versions as well as the newer versions of the plugin, so that we can know the exact versions to put in..
//@TODO - add checks for
//    'plugin_min'
//    'plugin_max'
                        ?>
                    </div>
                    <div class="author">
                        <b>Author:</b> <?php echo $details['author_name']; ?>
                            <?php
                                if( isset($details['author_url']) && $details['author_url'] !== '') {
                                    ?>
                                    @ <a href="<?php echo $details['author_url']; ?>" target="_blank"><?php echo $details['author_url']; ?></a><br>
                                <?php
                                }

                        if(version_compare(ZIGGEO_VERSION, $details['requires_min'], '<')) {
                            $bad = true;
                            ?>
                            <b class="warning">Per Author: The version of the Ziggeo WordPress plugin you are using is not compatible with the required version for integration to work properly. Please upgrade the plugin to the latest (if you already are and get this message, do let us know)</b>
                            <?php
                        }

                        if($details['requires_max'] === '?') {
                            //unknown how high it is recommended, so it is likely that all is good
                        }
                        elseif($details['requires_max'] === '~') {
                            //Should be working properly with the latest plugin version
                        }
                        elseif(version_compare(ZIGGEO_VERSION, $details['requires_max'], '>')) {
                            $bad = true;
                            ?>
                            <b class="warning">Per Author: The version of the Ziggeo WordPress plugin you are using is not compatible with the required version for integration to work properly. Please contact author to upgrade the integration</b>
                            <?php
                        }
                        else {
                            ?>
                            <b class="message">Author did not specify the version of the plugin required to run the integration</b>
                            <?php
                        }

                        if($bad !== true) {
                            ?>
                            <b class="message">Per Author: The integration should work properly with your current version of our plugin</b>
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