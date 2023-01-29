<?php

// This file is intended to hold code specific to the events editor calls over AJAX
// Added in v2.11

// Uses `ziggeo_events` option to store events
// Uses data/events to save the events for quick load

//Checking if WP is running or if this is a direct call..
defined('ABSPATH') or die();


add_filter('ziggeo_ajax_call', function($rez, $operation) {

	//settings_manage_template
	if($operation === 'event_editor_save_template') {

		$id = $_POST['id'];
		$event = $_POST['event'];
		$code = stripcslashes($_POST['code']);
		$inject_type = $_POST['inject_type'];

		$saves = get_option('ziggeo_events');

		if(!is_array($saves)) {
			$saves = array();
		}

		$saves[$id] = array(
			'event'         => $event,
			'code'          => $code,
			'inject_type'   => $inject_type
		);

		return update_option('ziggeo_events', $saves);
	}

	return $rez;
}, 10, 2);
?>