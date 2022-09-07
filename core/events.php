<?php

// Support for event shortcodes
add_shortcode('ziggeo_event', function($attrs) {

	// Defaults
	$event = 'verified';
	$type = 'alert';
	$msg = 'Captured media has been verified';
	$id = false;
	$saved_events = false;
	$extra_code = '';
	$inject_type = 'on_load';

	if(isset($attrs['event'])) {
		$event = $attrs['event'];
	}

	if(isset($attrs['type'])) {
		$type = $attrs['type'];
	}

	if(isset($attrs['message'])) {
		$msg = $attrs['message'];
	}

	if(isset($attrs['id'])) {
		$id = $attrs['id'];

		$saved_events = get_option('ziggeo_events');

		if(isset($saved_events[$id])) {
			$extra_code = stripcslashes($saved_events[$id]['code']);
			$event = $saved_events[$id]['event'];
			$inject_type = $saved_events[$id]['inject_type'];
		}
		else {
			$type === 'ignore'; // We do not want to output anything since the template was not found
			// @here - We could add some error reporting of sorts to notify that the event with ID is missing.. Anyone needs something like that? Let us know :)
		}
	}

	if($type === 'alert') {
		$code = '<script>';
			$code .= 'ziggeo_app.embed_events.on("' . $event . '", function (embedding, attr1, attr2, attr3, attr4) {';
				$code .= 'alert("' . $msg . '")'; // We do not escape the strings at this time
			$code .= '});';
		$code .= '</script>';
	}
	elseif($type === 'template') {
		if($inject_type === 'on_fire') {
			$code = '<script>';
			$code .= 'ziggeo_app.embed_events.on("' . $event . '", function (embedding, attr1, attr2, attr3, attr4) {';
				$code .= $extra_code;
			$code .= '});';
			$code .= '</script>';
		}
		else {
			$code = $extra_code;
		}
	}

	return $code;
});

?>