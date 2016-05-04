<?php
//Checking if WP is running or if this is a direct call..
defined('ABSPATH') or die();

function ziggeo_enqueue_scripts() {
    wp_register_script('ziggeo-js', "//assets-cdn.ziggeo.com/v1-stable/ziggeo.js", array());
    wp_enqueue_script('ziggeo-js');
    wp_register_style('ziggeo-css', "//assets-cdn.ziggeo.com/v1-stable/ziggeo.css", array());
    wp_enqueue_style('ziggeo-css');
    wp_register_style('ziggeo-styles-css', plugins_url('../styles.css', __FILE__), array());	
    wp_enqueue_style('ziggeo-styles-css');
}

function ziggeo_admin_scripts() {
	//Enqueue admin panel scripts
    wp_register_script('ziggeo-admin-js', plugins_url('../admin.js', __FILE__), array("jquery"));
    wp_enqueue_script('ziggeo-admin-js');

	//Enqueue admin panel styles
    wp_register_style('ziggeo-admin-css', plugins_url('../admin-styles.css', __FILE__), array());	
    wp_enqueue_style('ziggeo-admin-css');
}

add_action('wp_enqueue_scripts', "ziggeo_enqueue_scripts");	
add_action('admin_enqueue_scripts', "ziggeo_enqueue_scripts");	
add_action('admin_enqueue_scripts', "ziggeo_admin_scripts");
