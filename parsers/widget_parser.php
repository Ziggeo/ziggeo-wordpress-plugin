<?php

//Checking if WP is running or if this is a direct call..
defined('ABSPATH') or die();

//add_filter('widget_title', 'ziggeo_content_filter');
add_filter('widget_title', function($title, $instance, $id_base) {

	$title = ziggeo_p_content_filter($title);

	$start = stripos($title, 'ziggeo-tags="wordpress');

	if($start > 0) {
		$title = substr($title, 0, $start) . 'ziggeo-tags="widget,widget_title,' . $id_base . ',wordpress' . substr($title, $start + strlen('ziggeo-tags="wordpress'));
	}


	return $title;
}, 10, 3);


add_filter( 'dynamic_sidebar_params', function($params) {
	global $wp_registered_widgets;

	$settings_getter = $wp_registered_widgets[ $params[0]['widget_id'] ]['callback'][0];

	if(is_object($settings_getter) && method_exists($settings_getter, 'get_settings')) {
		$settings = $settings_getter->get_settings();
		//$settings = $settings[ $params[1]['number'] ];

		/*
		// We could really change title, however it is then outputed through escape html functions, so our codes get outputted. Better to not handle the title then.. (here)
		if(isset($settings[ $params[1]['number'] ]['title'])) {
			//check the title
			$settings[ $params[1]['number'] ]['title'] =  ziggeo_p_content_filter($settings[ $params[1]['number'] ]['title']);
		}*/
		if(isset($settings[ $params[1]['number'] ]['content'])) {
			//check the description
			$content = $settings[ $params[1]['number'] ]['content'];
			$content =  ziggeo_p_content_filter($content);

			$start = stripos($content, 'ziggeo-tags="wordpress');

			if($start > 0) {
				$content = substr($content, 0, $start) . 'ziggeo-tags="widget,widget_content,' . $params[0]['widget_id'] . ',wordpress' . substr($content, $start + strlen('ziggeo-tags="wordpress'));
			}

			$settings[ $params[1]['number'] ]['content'] = $content;
		}

		$settings_getter->save_settings($settings);
	}

	return $params;
});

?>