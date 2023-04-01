<?php

//Handle manage button press on notifications
// This fires within admin only
add_filter('ziggeo_ajax_call', function($rez, $operation) {

	if($operation === 'notification_handler') {
		if(isset($_POST['id'], $_POST['status'])) {

			$id = (int)($_POST['id']);
			$status = $_POST['status'];

			$rez = ziggeo_notification_manage($id, $status);
		}
		else {
			$rez = false;
		}
	}

	return $rez;
}, 10, 2);

// This fires from the dashboard only (might be admin might be anyone)
add_filter('ziggeo_ajax_call', 'ziggeo_notification_dev_report', 10, 2);
// This fires from the frontend (might be admin might be anyone)
add_filter('ziggeo_ajax_call_client', 'ziggeo_notification_dev_report', 10, 2);

function ziggeo_notification_dev_report($rez, $operation) {

	if($operation === 'ziggeo_dev_report') {
		if(isset($_POST['type'], $_POST['message'], $_POST['page'])) {

			$type = 'notice';
			if($_POST['type'] === 'error') { $type = 'error'; }
			$page = strip_tags($_POST['page']);

			$message = 'There was ' . $type . ' on following page: ' . $page . '.' .
			           ' Error: ' . strip_tags($_POST['message']);

			ziggeo_notification_create($message, $type);

			$rez = true;
		}
		else {
			$rez = false;
		}
	}

	return $rez;
}


// Function to create a notification
function ziggeo_notification_create($message, $type='notice') {

	$notifications = ziggeo_get_notifications();

	$notifications['list'][] = array(
		'id'			=> $notifications['last_id']+1,
		'type'			=> $type,
		'message'		=> $message,
		'time'			=> time(),
		'status'		=> '' // empty string means nothing happened, then we record action within it
	);

	$notifications['last_id']++;

	update_option('ziggeo_notifications', $notifications);
}

// Function to remove notification
function ziggeo_notification_remove($notification_id) {

	//$notifications = ziggeo_get_notifications();

	//TODO - find notification with ID and remove it
	// or should we have a list of them as seen/unseen || checked/unchecked
	// For now going with just hidding them, reach out to us if you have suggestion or preference here.
}

// Function to manage notification (OK, HIDE, non-removal)
function ziggeo_notification_manage($notification_id, $status) {

	$notification_id = (int)($notification_id);

	if($status !== 'OK' && $status !== 'HIDE' && $status !== 'PRUNE' && $status !== 'CLEAR') {
		return false;
	}

	$found = false;

	//$notifications = get_option('ziggeo_notifications');
	$notifications = ziggeo_get_notifications();

	//Make it unique
	if($status === 'PRUNE' || $status === 'CLEAR') {
		//Let us check if this is admin 
		if(current_user_can('activate_plugins')) {
			if($status === 'CLEAR') {
				$notifications = false; //To clear it in a way that will make it easy for other codes to understand it
				$found = true;
			}
			else {
				$notifications['list'] = array_unique($notifications['list']);
				$found = true;
			}
		}
	}
	else {
		for($i = 0, $c = count($notifications['list']); $found !== true; $i++) {
			if($notifications['list'][$i]['id'] === $notification_id) {
				$notifications['list'][$i]['status'] = $status;

				$found = true;
			}
		}
	}

	if($found === true) {
		update_option('ziggeo_notifications', $notifications);
		return true;
	}

	return false;
}

//Get the current count of all notifications that do not have any status, or total if passed true
function ziggeo_notifications_count($total = false) {

	$notifications = ziggeo_get_notifications();

	if($total === true) {
		return count($notifications['list']);
	}
	else {
		for($i = 0, $t = 0, $c = count($notifications['list']); $i < $c; $i++) {
			$current = $notifications['list'][$i];

			if($current['status'] !== 'OK' && $current['status'] !== 'HIDE') {
				$t++;
			}
		}

		return $t;
	}

}

?>