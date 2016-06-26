<?php 
    /*
    Plugin Name: Ziggeo Video Posts and Comments
    Plugin URI: https://ziggeo.com
    Description: Plugin for adding video posts and video comments
    Author: Ziggeo
    Version: 1.15
    Author URI: https://ziggeo.com
    */

//Checking if WP is running or if this is a direct call..
defined('ABSPATH') or die();

//While the functions are fast, this will get the string of the path the WP way and keep it saved so we can just reference it. * WP ver 2.8 and up     
define('ZIGGEO_ROOT_PATH', plugin_dir_path(__FILE__) );
define('ZIGGEO_INTEGRATIONS_PATH', ZIGGEO_ROOT_PATH . 'modules/');

//Setting up the URL so that we can get/built on it later on from the plugin root
define('ZIGGEO_ROOT_URL', plugins_url() . '/ziggeo/' );

//We will store data in another folder, so that it is not removed when the plugin gets updated
define('ZIGGEO_DATA_ROOT_PATH', ZIGGEO_ROOT_PATH . '../ziggeo-userData/');
define('ZIGGEO_DATA_ROOT_URL', plugins_url() . '/ziggeo-userData/');

//plugin version - this way other plugins can get it as well and we will be updating this file for each version change as is
define('ZIGGEO_VERSION', '1.15');

//Best to state default code in one location, then just call for it when needed.
//recorder
define('ZIGGEO_DEFAULTS_RECORDER', 'ziggeo-width=360 ziggeo-height=240 ziggeo-limit=120');
//player
define('ZIGGEO_DEFAULTS_PLAYER', 'ziggeo-width=360 ziggeo-height=240');

include_once(ZIGGEO_ROOT_PATH . "src/player.php");
include_once(ZIGGEO_ROOT_PATH . "src/assets.php");
include_once(ZIGGEO_ROOT_PATH . "src/header.php");
include_once(ZIGGEO_ROOT_PATH . "src/config_required_notice.php");
include_once(ZIGGEO_ROOT_PATH . "src/settings.php");
include_once(ZIGGEO_ROOT_PATH . "src/templates.php");
include_once(ZIGGEO_ROOT_PATH . "src/file_parser.php");
include_once(ZIGGEO_ROOT_PATH . "src/ziggeo_tinymce.php"); //to activate TinyMCE toolbar button
include_once(ZIGGEO_ROOT_PATH . "src/integrations.php");

//For a link to settings in plugins screen
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'ziggeo_action_links');

function ziggeo_action_links($links) {
    $links[] = '<a href="' . esc_url( get_admin_url(null, 'options-general.php?page=ziggeo_video') ) . '">Settings</a>';
    $links[] = '<a href="mailto:support@ziggeo.com">Support</a>';
    return $links;
}
