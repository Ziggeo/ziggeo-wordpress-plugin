<?php

//Checking if WP is running or if this is a direct call..
defined('ABSPATH') or die();

//Prepare the headers information
function ziggeo_p_assets_get_raw() {

	$options = ziggeo_get_plugin_options();

	//use add_filter('ziggeo_assets_init', 'your-function-name') to change the options on fly if wanted
	// it needs to return modified $options array.
	$options = apply_filters('ziggeo_assets_init_raw', $options);

	if(isset($options, $options['use_version'], $options['use_revision'])) {
		$use = $options['use_version'] . '-' . $options['use_revision'];
	}
	else {
		$use = 'v1-stable';
	}

	$ver = '?version=' . ZIGGEO_VERSION;

	$result = array(
		array(
			'js'	=> 'https://assets-cdn.ziggeo.com/' . $use . '/ziggeo.js' . $ver,
			'css'	=> 'https://assets-cdn.ziggeo.com/' . $use . '/ziggeo.css' . $ver
		),
		array(
			'js'	=> 'https://imasdk.googleapis.com/js/sdkloader/ima3.js' . $ver
		),
		array(
			'js'	=> ZIGGEO_ROOT_URL . 'assets/js/ziggeo_plugin.js' . $ver,
			'css'	=> ZIGGEO_ROOT_URL . 'assets/css/styles.css' . $ver
		)
	);

	//Allowing to add additional URLs
	$result = apply_filters('ziggeo_assets_init_raw_post', $result);

	return $result;
}

//Function that prepares the headers information for Wordpress page load
function ziggeo_p_assets_global() {

	//To make sure that we are parsing this only once
	if(defined('ZIGGEO_PARSED_ASSETS')) {
		return;
	}

	$options = ziggeo_get_plugin_options();

	//use add_filter('ziggeo_assets_init', 'your-function-name') to change the options on fly if wanted
	// it needs to return modified $options array.
	$options = apply_filters('ziggeo_assets_init', $options);

	if(isset($options, $options['use_version'], $options['use_revision'])) {
		$use = $options['use_version'] . '-' . $options['use_revision'];
	}
	else {
		$use = 'v1-stable';
	}

	$ver = '?version=' . ZIGGEO_VERSION;

	if(apply_filters('ziggeo_assets_pre_server_load', true)) {
		//server assets
		wp_register_script('ziggeo-js', 'https://assets-cdn.ziggeo.com/' . $use . '/ziggeo.js' . $ver, array());
		wp_enqueue_script('ziggeo-js', array('jquery'));
		wp_register_style('ziggeo-css', 'https://assets-cdn.ziggeo.com/' . $use . '/ziggeo.css' . $ver, array());
		wp_enqueue_style('ziggeo-css');

		//In case VAST is used we will have a URL here
		if(!empty($options['vast_adserver'])) {
			wp_register_script('vast-google-js', 'https://imasdk.googleapis.com/js/sdkloader/ima3.js' . $ver, array());
			wp_enqueue_script('vast-google-js');
		}

	}
	if(apply_filters('ziggeo_assets_pre_local_load', true)) {
		//local assets
		wp_register_script('ziggeo-plugin-js', ZIGGEO_ROOT_URL . 'assets/js/ziggeo_plugin.js' . $ver, array("jquery"));
		wp_enqueue_script('ziggeo-plugin-js');
		wp_register_style('ziggeo-styles-css', ZIGGEO_ROOT_URL . 'assets/css/styles.css' . $ver, array());
		wp_enqueue_style('ziggeo-styles-css');
	}

	do_action('ziggeo_assets_post');

	define('ZIGGEO_PARSED_ASSETS', true);
}

function ziggeo_p_assets_admin() {

	//Enqueue admin panel scripts
	wp_register_script('ziggeo-admin-js', ZIGGEO_ROOT_URL . 'assets/js/admin.js', array("jquery"));
	wp_enqueue_script('ziggeo-admin-js');

	//Enqueue admin panel styles
	wp_register_style('ziggeo-admin-css', ZIGGEO_ROOT_URL . 'assets/css/admin-styles.css', array());
	wp_enqueue_style('ziggeo-admin-css');
}

add_action('wp_enqueue_scripts', "ziggeo_p_assets_global");
add_action('admin_enqueue_scripts', "ziggeo_p_assets_global");
add_action('admin_enqueue_scripts', "ziggeo_p_assets_admin");
