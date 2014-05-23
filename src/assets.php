<?php

function ziggeo_enqueue_scripts() {
    wp_register_script('ziggeo-js', "//assets.ziggeo.com/js/ziggeo-json2-betajs-player.min.js", array('jquery'));
    wp_enqueue_script('ziggeo-js');
    wp_register_style('ziggeo-css', "//assets.ziggeo.com/css/ziggeo-betajs-player.min.css", array());
    wp_enqueue_style('ziggeo-css');
    wp_register_style('ziggeo-styles-css', plugins_url('../styles.css', __FILE__), array());	
    wp_enqueue_style('ziggeo-styles-css');
}

function ziggeo_admin_scripts() {
    wp_register_script('ziggeo-admin-js', plugins_url('../admin.js', __FILE__), array("jquery"));
    wp_enqueue_script('ziggeo-admin-js');
}

add_action('wp_enqueue_scripts', "ziggeo_enqueue_scripts");	
add_action('admin_enqueue_scripts', "ziggeo_enqueue_scripts");	
add_action('admin_enqueue_scripts', "ziggeo_admin_scripts");
