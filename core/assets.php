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
	if(defined('ZIGGEO_PARSED_ASSETS') || defined('ZIGGEO_LOAD_ASSETS')) {
		return;
	}

	$options = ziggeo_get_plugin_options();

	//To allow the lazy load of our resources instead of loading them right away.
	if($options['lazy_load'] === ZIGGEO_YES) {
		wp_register_script( 'ziggeo-lazyload', '', [], '', true );
		wp_enqueue_script( 'ziggeo-lazyload'  );
		wp_add_inline_script( 'ziggeo-lazyload', 'ZiggeoWP.lazyload = ' . json_encode(ziggeo_p_assets_get_raw()) );

		define('ZIGGEO_LOAD_ASSETS', true);
		return;
	}

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

	//Enqueue admin panel styles
	wp_register_style('ziggeo-admin-css', ZIGGEO_ROOT_URL . 'assets/css/admin-styles.css', array());
	wp_enqueue_style('ziggeo-admin-css'); // We always include CSS in dashboard since there are some menu styles


	if(get_current_screen()->id === 'ziggeo-video_page_ziggeo_sdk') {
		//Adding support for charts
		wp_register_script('chart-js', 'https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.2.0/chart.min.js');
		wp_enqueue_script('chart-js');
		//This is usually added into Admin, however just being safe
		wp_enqueue_script('jquery-ui-datepicker');
		//To add support for the drag and drop on our SDK pages
		wp_enqueue_script("jquery-ui-draggable");

		echo ziggeo_p_get_lazyload_activator();
	}

	$ziggeo_plugin_pages = array(
		// Core plugin pages
		'toplevel_page_ziggeo_video',
		'ziggeo-video_page_ziggeo_videoslist',
		'ziggeo-video_page_ziggeo_video',
		'ziggeo-video_page_ziggeo_editor_templates',
		'ziggeo-video_page_ziggeo_editor_events',
		'ziggeo-video_page_ziggeo_notifications',
		'ziggeo-video_page_ziggeo_videoslist',
		'ziggeo-video_page_ziggeo_sdk',
		'ziggeo-video_page_ziggeo_addons'
	);

	$supported_screens = array(
		'post',
		'page'
	);

	if(in_array(get_current_screen()->id, $ziggeo_plugin_pages) || in_array(get_current_screen()->id, $supported_screens)) {
		wp_enqueue_script('ziggeo-admin-js');
		echo ziggeo_p_get_lazyload_activator();
	}

}

add_action('wp_enqueue_scripts', "ziggeo_p_assets_global");
add_action('admin_enqueue_scripts', "ziggeo_p_assets_global");
add_action('admin_enqueue_scripts', "ziggeo_p_assets_admin");


// Function that helps us with the lazyload assets loading
function ziggeo_p_get_lazyload_activator() {
	return '<script>function ziggeoLoadAssets() {' .
			'var _head = document.getElementsByTagName(\'head\')[0];' .
			'for(i = 0, c = ZiggeoWP.lazyload.length; i < c; i++) {' .
				//Check for and create script element
				'if( typeof ZiggeoWP.lazyload[i].js !== \'undefined\' ){' .
					'var _script = document.createElement(\'script\');' .
					'_script.type = "text/javascript";' .
					'_script.src = ZiggeoWP.lazyload[i].js;' .
					'_head.appendChild(_script);' .
				'}' .
				//Check for and create style element
				'if( typeof ZiggeoWP.lazyload[i].css !== \'undefined\' ){' .
					'var _style = document.createElement(\'link\');' .
					'_style.rel = "stylesheet";' .
					'_style.href = ZiggeoWP.lazyload[i].css;' .
					'_style.media = \'all\';' .
					'_head.appendChild(_style);' .
				'}' .
			'}' .
		'}' .
		'if(document.readyState === \'complete\'){' .
			'ziggeoReInitApp();' .
			'ziggeoLoadAssets();' .
		'}' .
		'else {' .
			'window.addEventListener(\'load\', function() {' . 
				'ziggeoReInitApp();' .
				'ziggeoLoadAssets();' .
			'});' .
		'}' .
	'</script>';
}

// Filter function that will maybe add the lazy load code (only once and only if needed) 
function ziggeo_p_assets_maybeload($content) {

	if(defined('ZIGGEO_FOUND')) {
		if(!defined('ZIGGEO_FOUND_POST')) {
			$options = ziggeo_get_plugin_options();
			if($options['lazy_load'] === ZIGGEO_YES) {
				// Create the function that will load the scripts after page has been loaded.
				$content .= ziggeo_p_get_lazyload_activator();
				define('ZIGGEO_FOUND_POST', true);
			}
		}
	}
	return $content;
}