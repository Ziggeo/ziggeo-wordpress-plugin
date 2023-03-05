<?php

// This file is intended to hold code specific to the events editor calls over AJAX
// Added in v2.11

// Uses `ziggeo_events` option to store events
// Uses data/events to save the events for quick load

//Checking if WP is running or if this is a direct call..
defined('ABSPATH') or die();


add_filter('ziggeo_ajax_call', function($rez, $operation) {

	if($operation === 'event_editor_save_template'   ||
	   $operation === 'event_editor_update_template' ||
	   $operation === 'event_editor_remove_template') {

		$existing_event_templates = get_option('ziggeo_events');

		if(!is_array($existing_event_templates)) {
			$existing_event_templates = array();
		}

		$id = isset($_POST['id']) ? $_POST['id'] : false;

		if($id === false) {
			return false;
		}
	}

	if($operation === 'event_editor_save_template' || $operation === 'event_editor_update_template') {

		if($operation === 'event_editor_update_template') {
			$old_id = isset($_POST['old_id']) ? $_POST['old_id'] : false;

			// old ID should always be set in this call
			if($old_id === false) {
				return false;
			}

			if(isset($existing_event_templates[$old_id])) {
				unset($existing_event_templates[$old_id]);
			}
			else {
				return false;
			}
		}

		$event = isset($_POST['event']) ? $_POST['event'] : '';
		$code = isset($_POST['code']) ? stripcslashes($_POST['code']) : '';
		$inject_type = isset($_POST['inject_type']) ? $_POST['inject_type'] : '';

		$existing_event_templates[$id] = array(
			'event'         => $event,
			'code'          => $code,
			'inject_type'   => $inject_type
		);

		return update_option('ziggeo_events', $existing_event_templates);
	}
	elseif($operation === 'event_editor_remove_template') {

		if(isset($existing_event_templates[$id])) {
			unset($existing_event_templates[$id]);
			return update_option('ziggeo_events', $existing_event_templates);
		}
		else {
			return false;
		}
	}

	return $rez;
}, 10, 2);
?>