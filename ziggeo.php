<?php 
    /*
    Plugin Name: Ziggeo Video Posts and Comments
    Plugin URI: http://ziggeo.com
    Description: Plugin for adding video posts and video comments
    Author: Ziggeo
    Tags: comments, posts, video comments, crowdsourced video, crowdsourced video plugin, page, recorder, user generated content, user generated content plugin, user generated video, video comments, video posts, video recorder, video recording, video reviews, video submission, video submission plugin, video testimonial plugin, video testimonials, video upload, video widget, webcam, webcam recorder
    Version: 1.10
    Author URI: http://ziggeo.com
    */

//Checking if WP is running or if this is a direct call..
defined('ABSPATH') or die();

//While the functions are fast, this will get the string of the parth the WP way and keep it saved so we can just reference it. * WP ver 2.8 and up	
define('ZIGGEO_ROOT_PATH', plugin_dir_path(__FILE__) );

include_once(ZIGGEO_ROOT_PATH . "src/player.php");
include_once(ZIGGEO_ROOT_PATH . "src/assets.php");
include_once(ZIGGEO_ROOT_PATH . "src/header.php");
include_once(ZIGGEO_ROOT_PATH . "src/config_required_notice.php");
include_once(ZIGGEO_ROOT_PATH . "src/settings.php");
include_once(ZIGGEO_ROOT_PATH . "src/templates.php");
include_once(ZIGGEO_ROOT_PATH . "src/file_parser.php");
