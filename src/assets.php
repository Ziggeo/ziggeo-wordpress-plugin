<?php
//Checking if WP is running or if this is a direct call..
defined('ABSPATH') or die();

function ziggeo_enqueue_scripts() {
    //js
    wp_register_script('ziggeo-js', "//assets-cdn.ziggeo.com/v1-stable/ziggeo.js", array());
    wp_enqueue_script('ziggeo-js');
    wp_register_script('ziggeo-plugin-js', ZIGGEO_ROOT_URL . 'js/ziggeo_plugin.js', array());
    wp_enqueue_script('ziggeo-plugin-js');
    //CSS
    wp_register_style('ziggeo-css', "//assets-cdn.ziggeo.com/v1-stable/ziggeo.css", array());
    wp_enqueue_style('ziggeo-css');
    wp_register_style('ziggeo-styles-css', ZIGGEO_ROOT_URL . 'css/styles.css', array());    
    wp_enqueue_style('ziggeo-styles-css');
}

function ziggeo_admin_scripts() {
    //Enqueue admin panel scripts
    wp_register_script('ziggeo-admin-js', ZIGGEO_ROOT_URL . 'js/admin.js', array("jquery"));
    wp_enqueue_script('ziggeo-admin-js');

    //Enqueue admin panel styles
    wp_register_style('ziggeo-admin-css', ZIGGEO_ROOT_URL . 'css/admin-styles.css', array());    
    wp_enqueue_style('ziggeo-admin-css');
}

add_action('wp_enqueue_scripts', "ziggeo_enqueue_scripts");    
add_action('admin_enqueue_scripts', "ziggeo_enqueue_scripts");    
add_action('admin_enqueue_scripts', "ziggeo_admin_scripts");
