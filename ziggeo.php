<?php 
    /*
    Plugin Name: Ziggeo Video Comments
    Plugin URI: http://api.ziggeo.com
    Description: Plugin for adding video comments to the commenting system
    Author: Oliver Friedmann
    Version: 1.0
    Author URI: http://oliverfriedmann.com
    */

    
    
    
    
$ziggeo_api_token = "REPLACE_WITH_YOUR_API_TOKEN";
    
	
	
	
	
	
function ziggeo_comment_template($comment_template) {
	return dirname(__FILE__) . "/templates/comments_template.php";
}

add_filter("comments_template", "ziggeo_comment_template");    
	
function ziggeo_enqueue_scripts() {
    wp_register_script('ziggeo-js', "//assets.ziggeo.com/js/ziggeo-json2-betajs-player.min.js", array('jquery'));
    wp_enqueue_script('ziggeo-js');
    wp_register_style('ziggeo-css', "//assets.ziggeo.com/css/ziggeo-betajs-player.min.css", array());
    wp_enqueue_style('ziggeo-css');
    wp_register_style('ziggeo-styles-css', plugins_url('styles.css', __FILE__), array());	
    wp_enqueue_style('ziggeo-styles-css');
}

add_action('wp_enqueue_scripts', "ziggeo_enqueue_scripts");	

function ziggeo_script_header() {
	global $ziggeo_api_token;
	echo "<script>window.$=jQuery; ZiggeoApi.token = '" . $ziggeo_api_token . "';</script>\n";
}

add_action('wp_head', "ziggeo_script_header");
