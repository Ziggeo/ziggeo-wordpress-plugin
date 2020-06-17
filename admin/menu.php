<?php

// This file has the codes that add Ziggeo settings and all pages into the Wordpress dashboard menu.


add_action('admin_menu', function() {

	add_menu_page(
		'Ziggeo Video',								//page title
		'Ziggeo Video',								//menu title
		'manage_options',							//capability
		'ziggeo_video',								//menu slug
		'ziggeo_a_s_page',							//function
		ZIGGEO_ROOT_URL . 'assets/images/icon.png', //icon url
		68											//65 - plugins 70 - users
	);

	//Settings sub menu
	ziggeo_p_add_menu_entry(array(
		'page_title'	=> 'Ziggeo Video Settings',				//page title
		'menu_title'	=> 'Settings',							//menu title
		'capability'	=> 'manage_options',					//min capability to view
		'slug'			=> 'ziggeo_video',						//menu slug
		'callback'		=> 'ziggeo_a_s_page')					//function
	);

	// Get notifications count
	$n_count = ziggeo_notifications_count();
	$n_title_extra = '';

	if($n_count > 0) {
		$n_title_extra = ' <span class="ziggeo-counter">' . $n_count . '</span>';
	}

	$v_count = ziggeo_videoslist_count();
	$v_title_extra = '';

	if($v_count > 0) {
		$v_title_extra = ' <span class="ziggeo-counter">' . $v_count . '</span>';
	}

	//Notifications sub menu
	ziggeo_p_add_menu_entry(array(
		'page_title'	=> 'Notifications',						//page title
		'menu_title'	=> 'Notifications' . $n_title_extra,	//menu title
		'capability'	=> 'manage_options',					//min capability to view
		'slug'			=> 'ziggeo_notifications',				//menu slug
		'callback'		=> 'ziggeo_a_n_page')					//function
	);

	//Videos List sub menu
	ziggeo_p_add_menu_entry(array(
		'page_title'	=> 'Videos List',						//page title
		'menu_title'	=> 'Videos List' . $v_title_extra,		//menu title
		'capability'	=> 'manage_options',					//min capability to view
		'slug'			=> 'ziggeo_videoslist',					//menu slug
		'callback'		=> 'ziggeo_a_v_page')					//function
	);

	//The power of SDK
	if(file_exists(ZIGGEO_ROOT_PATH . 'sdk/Ziggeo.php')) {
		ziggeo_p_add_menu_entry(array(
			'page_title'	=> 'SDK',							//page title
			'menu_title'	=> 'PHP SDK',						//menu title
			'capability'	=> 'manage_options',				//min capability to view
			'slug'			=> 'ziggeo_sdk',					//menu slug
			'callback'		=> 'ziggeo_a_sdk_page')				//function
		);
	}


	//Addons sub menu
	ziggeo_p_add_menu_entry(array(
		'page_title'	=> 'Addons',							//page title
		'menu_title'	=> 'Addons<hr>',						//menu title
		'capability'	=> 'manage_options',					//min capability to view
		'slug'			=> 'ziggeo_addons',						//menu slug
		'callback'		=> 'ziggeo_a_addons_page')				//function
	);

	//for backwards compatibility
	add_options_page('Ziggeo Video', '<img src="' . ZIGGEO_ROOT_URL . 'assets/images/icon.png" style="height: 1em; position: relative; top: 0.1em; padding-right: 0.2em;">Ziggeo Video', 'manage_options', 'ziggeo_video', 'ziggeo_a_s_page');
}, 8);

//Function to add a submenu to Ziggeo Video
function ziggeo_p_add_menu_entry($details = null) {

	if($details === null) {
		return false;
	}

	add_submenu_page(
		'ziggeo_video',				//parent slug
		$details['page_title'],		//page title
		$details['menu_title'],		//menu title
		$details['capability'],		//min capability to view
		$details['slug'],			//menu/page slug
		$details['callback']		//function
	);
}

//Function to add a "sub menu of a submenu" after addons submenu
function ziggeo_p_add_addon_submenu($details = null) {

	if($details === null) {
		return false;
	}

	$details['menu_title'] = '<span class="ziggeo-wp-menu-space"></span><span class="ziggeo-wp-menu-title">' . $details['menu_title'] . '</span>';

	add_submenu_page(
		'ziggeo_video',				//parent slug
		$details['page_title'],		//page title
		$details['menu_title'],		//menu title
		$details['capability'],		//min capability to view
		$details['slug'],			//menu/page slug
		$details['callback']		//function
	);
}

//Thank you brasofilo: https://wordpress.stackexchange.com/a/60190
// Used to add a class
add_action( 'admin_init', function() {
	global $submenu;

	if(isset($submenu['ziggeo_video'])) {
		foreach($submenu['ziggeo_video'] as $menu_item => $details) {
			if($details[2] === 'ziggeo_addons') {
				$submenu['ziggeo_video'][$menu_item][4] = 'ziggeo_addons';
			}
		}
	}
});

?>