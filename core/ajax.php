<?php

//
// File that brings AJAX options to Ziggeo Plugin
//

//Checking if WP is running or if this is a direct call..
defined('ABSPATH') or die();

//Registering AJAX hook to be called over..
add_action('wp_ajax_nopriv_ziggeo_ajax', function() {

	$rez = array('error' => 'Not Allowed');

	//we only process the call if it has what we are expecting..
	if(isset($_POST, $_POST['operation'])) {

		$rez = '';
		$operation = $_POST['operation'];

		if(check_ajax_referer('ziggeo_ajax_nonce', 'ajax_nonce')) {
			$rez = apply_filters('ziggeo_ajax_call_client', $rez, $operation);
		}
	}

	echo json_encode($rez);

	wp_die();
});

add_action('wp_ajax_ziggeo_ajax', function() {

	$rez = array('error' => 'Not Allowed');

	//we only process the call if it has what we are expecting..
	if(isset($_POST, $_POST['operation'])){

		$rez = '';
		$operation = $_POST['operation'];

		if(check_ajax_referer('ziggeo_ajax_nonce', 'ajax_nonce')) {
			$rez = apply_filters('ziggeo_ajax_call', $rez, $operation);
		}
	}

	echo json_encode($rez);

	wp_die();
});