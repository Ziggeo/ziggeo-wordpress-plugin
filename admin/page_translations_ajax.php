<?php

// This file is intended to hold code specific to the Translations Panel calls over AJAX
// Added in v3.0

// Uses `ziggeo_translations` option to store translations
// Uses data/events to save the events for quick load

//Checking if WP is running or if this is a direct call..
defined('ABSPATH') or die();


add_filter('ziggeo_ajax_call', function($rez, $operation) {

	//settings_manage_template
	if($operation === 'translations_panel_save_strings') {

		$language = $_POST['lang'];
		$strings = $_POST['strings'];

		$saved = get_option('ziggeo_translations');

		if(!is_array($saved)) {
			$saved = array();
		}

		$saved[$language] = array(
			'strings'   => $strings
		);

		return update_option('ziggeo_translations', $saved);
	}

	return $rez;
}, 10, 2);
?>