<?php 
/*
Plugin Name: Ziggeo Video Posts and Comments
Plugin URI: https://ziggeo.com/integrations/wordpress
Description: Plugin for adding videos to your website quickly and easily. It is powered by Ziggeo and allows you to add video posts and video comments and so much more.
Author: Ziggeo
Version: 2.3.4
Author URI: https://ziggeo.com
Text Domain: ziggeo
*/

//Checking if WP is running or if this is a direct call..
defined('ABSPATH') or die();


//While the functions are fast, this will get the string of the path the WP way and keep it saved so we can just reference it. * WP ver 2.8 and up     
define('ZIGGEO_ROOT_PATH', plugin_dir_path(__FILE__) );
define('ZIGGEO_INTEGRATIONS_PATH', ZIGGEO_ROOT_PATH . 'modules/');

//For ease of reading values
define('ZIGGEO_YES', "1");
define('ZIGGEO_NO', "0");

//Setting up the URL so that we can get/built on it later on from the plugin root
define('ZIGGEO_ROOT_URL', plugins_url('', __FILE__) . '/');

//We will store data in another folder, so that it is not removed when the plugin gets updated
// From v2.0 we use this only if the option is set to use file instead of DB. Since in many cases the calls are made to the DB it makes sense to also have the data there.
define('ZIGGEO_DATA_ROOT_PATH', ZIGGEO_ROOT_PATH . '../ziggeo-userData/');
define('ZIGGEO_DATA_ROOT_URL', plugins_url() . '/ziggeo-userData/');

//plugin version - this way other plugins can get it as well and we will be updating this file for each version change as is
define('ZIGGEO_VERSION', '2.3.4');

//Best to state default code in one location, then just call for it when needed.

//player
define('ZIGGEO_DEFAULTS_PLAYER', 'ziggeo-theme="modern" ziggeo-themecolor="red" ziggeo-width="360" ziggeo-height="240"');
//recorder
define('ZIGGEO_DEFAULTS_RECORDER', 'ziggeo-theme="modern" ziggeo-themecolor="red" ziggeo-width="360" ziggeo-height="240" ziggeo-limit="120"');
//uploader
define('ZIGGEO_DEFAULTS_UPLOADER', 'ziggeo-theme="modern" ziggeo-themecolor="red" ziggeo-width="360" ziggeo-height="240" ziggeo-limit="120" ziggeo-allowrecord="false"');
//rerecorder
define('ZIGGEO_DEFAULTS_RERECORDER', 'ziggeo-theme="modern" ziggeo-themecolor="red" ziggeo-width="360" ziggeo-height="240" ziggeo-limit="120" ziggeo-rerecordable="true"');
//screen recorder
define('ZIGGEO_DEFAULTS_SCREEN', 'ziggeo-theme="modern" ziggeo-themecolor="red" ziggeo-width="360" ziggeo-height="240" ziggeo-limit="120" ziggeo-allowscreen="true"');
//image uploader
//image player
//audio only player
//audio only recorder
//live
//conference
//hostepage / form

//Set up the translations
add_action('plugins_loaded', function() {
	$plugin_rel_path = basename( dirname( __FILE__ ) ) . '/languages'; /* Relative to WP_PLUGIN_DIR */
	load_plugin_textdomain( 'ziggeo', false, $plugin_rel_path );
});


include_once(ZIGGEO_ROOT_PATH . 'core/simplifiers.php');

//Lets check if we need to upgrade anything
include_once(ZIGGEO_ROOT_PATH . 'admin/update.php');

//Parsing codes
//codes to detect templates
include_once(ZIGGEO_ROOT_PATH . 'parsers/content_parser.php');
//codes used by the template parsers
include_once(ZIGGEO_ROOT_PATH . 'parsers/template_parser.php');
//Parsing players
include_once(ZIGGEO_ROOT_PATH . 'parsers/player_parser.php');
//parsing recorders
include_once(ZIGGEO_ROOT_PATH . 'parsers/recorder_parser.php');
//passing rerecorders
include_once(ZIGGEO_ROOT_PATH . 'parsers/rerecorder_parser.php');
//parsing uploaders
include_once(ZIGGEO_ROOT_PATH . 'parsers/uploader_parser.php');

//header codes that create v2 application
include_once(ZIGGEO_ROOT_PATH . 'core/header.php');
//assets (js and css files)
include_once(ZIGGEO_ROOT_PATH . 'core/assets.php');
//Codes for handling templates (file and db stuff)
include_once(ZIGGEO_ROOT_PATH . 'core/templates.php');

//codes for setting values and the dashboard part.
include_once(ZIGGEO_ROOT_PATH . 'admin/settings.php');

include_once(ZIGGEO_ROOT_PATH . 'admin/onboard-helper-missing-token.php');
include_once(ZIGGEO_ROOT_PATH . 'parsers/file_parser.php'); //integrations require file parser..

include_once(ZIGGEO_ROOT_PATH . 'core/integrations.php');
include_once(ZIGGEO_ROOT_PATH . 'core/notifications.php');
include_once(ZIGGEO_ROOT_PATH . 'core/videoslist.php');

include_once(ZIGGEO_ROOT_PATH . 'admin/plugins.php');
include_once(ZIGGEO_ROOT_PATH . 'admin/oembed.php');

//Add post and page editor toolbar
include_once(ZIGGEO_ROOT_PATH . 'admin/post_toolbar.php');

//addd some examples by showing our hooks that are actually in use by us
include_once(ZIGGEO_ROOT_PATH . 'core/hooks-examples.php');

//Include ajax handler
include_once(ZIGGEO_ROOT_PATH . 'core/ajax.php');

//Includes the rest handler
include_once(ZIGGEO_ROOT_PATH . 'core/rest.php');

//include the PHP SDK so that we can talk with the Ziggeo servers in the back as well (and receive useful info through webhooks)
//include_once(ZIGGEO_ROOT_PATH . 'sdk/Ziggeo.php');
//@ADD - To be added in next version

//Expose functions for integrations and custom codes
include_once(ZIGGEO_ROOT_PATH . '/templates/defaults_recorder.php');
include_once(ZIGGEO_ROOT_PATH . '/templates/defaults_player.php');

// The file that holds all functions that are removed from plugin, to give us a chance to show notification
// Functions would be kept for few versions
include_once(ZIGGEO_ROOT_PATH . '/core/removed.php');


?>