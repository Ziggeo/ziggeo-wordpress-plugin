<?php

//Checking if WP is running or if this is a direct call..
defined('ABSPATH') or die();

function ziggeo_p_assets_global() {

	$options = get_option('ziggeo_video');

	//use add_filter('ziggeo_assets_init', 'your-function-name') to change the options on fly if wanted
	// it needs to return modified $options array.
	$options = apply_filters('ziggeo_assets_init', $options);

	if(isset($options, $options['use_version'], $options['use_revision'])) {
		$use = $options['use_version'] . '-' . $options['use_revision'];
	}
	else {
		$use = 'v1-stable';
	}

	if(apply_filters('ziggeo_assets_pre_server_load', true)) {
		//server assets
		wp_register_script('ziggeo-js', 'https://assets-cdn.ziggeo.com/' . $use . '/ziggeo.js', array());
		wp_enqueue_script('ziggeo-js');
		wp_register_style('ziggeo-css', 'https://assets-cdn.ziggeo.com/' . $use . '/ziggeo.css', array());
		wp_enqueue_style('ziggeo-css');
	}


	if(apply_filters('ziggeo_assets_pre_local_load', true)) {
		//local assets
		wp_register_script('ziggeo-plugin-js', ZIGGEO_ROOT_URL . 'assets/js/ziggeo_plugin.js', array("jquery"));
		wp_enqueue_script('ziggeo-plugin-js');
		wp_register_style('ziggeo-styles-css', ZIGGEO_ROOT_URL . 'assets/css/styles.css', array());
		wp_enqueue_style('ziggeo-styles-css');
	}

	do_action('ziggeo_assets_post');
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


//==================================================================================
// @REMOVE in future versions
//==================================================================================

//links to the background image, since CSS can not be hard coded (and make it work everywhere)
if(!function_exists('videowallsz_css_video_wall')) {
	function videowallsz_css_video_wall() {
		$css = '';
		//use add_filter('videowallsz_assets_videowall_css', 'your-function-name') to add any CSS codes that you want to have present on pages
		$css = apply_filters('videowallsz_assets_videowall_css', $css);

		?>
		<style type="text/css">
			.ziggeo_videowall_slide_previous {
				background-image: url("<?php echo ZIGGEO_ROOT_URL . 'assets/images/arrow-previous.png'; ?>");
			}
			.ziggeo_videowall_slide_next {
				background-image: url("<?php echo ZIGGEO_ROOT_URL . 'assets/images/arrow-next.png'; ?>");
			}
			<?php echo $css; ?>
		</style>
		<?php
	}
}