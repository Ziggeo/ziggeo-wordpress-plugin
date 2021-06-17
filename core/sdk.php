<?php
/*
	This file holds all functions that can help you work with the SDK, without caring for the tokens in your own code.
	This file is intended to provide you the results for your own calls, minimizing the formatting output yet providing access to functionality a bit closer to WP than the raw SDK access.
*/

//This is a global variable. Do not use it directly.
$ziggeo_sdk = null;

// Sets the global variable in the Ziggeo SDK object and returns the Ziggeo object.
function ziggeo_p_sdk_init() {
	global $ziggeo_sdk;

	$options = ziggeo_get_plugin_options();

	$ziggeo_sdk = new Ziggeo($options['token'], $options['p_token'], $options['e_token']);

	return $ziggeo_sdk;
}

// Function to remove the use of SDK if we do not need it.
function ziggeo_p_sdk_unload() {
	global $ziggeo_sdk;
	$ziggeo_sdk = null;
}

// Returns the Ziggeo object. (please use init function first, this is for calls that would follow it in different functions)
function ziggeo_p_sdk_get_object($init_if_needed = false) {
	global $ziggeo_sdk;

	if($init_if_needed === true) {
		if($ziggeo_sdk === null) {
			return ziggeo_p_sdk_init();
		}
	}

	return $ziggeo_sdk;
}

/////////////////////////////////// APPLICATION ///////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////

	//Returns the application details.
	//$app_token and $private_key can be null, in which case it uses the default WP application (and private token)
	function ziggeo_p_sdk_applications_get($app_token = null, $private_key = null, $return_error = true) {

		if($app_token === null) {
			$ziggeo = ziggeo_p_sdk_get_object(true);
		}
		elseif($private_key === null) {
			//get private key based on the application token (if already added to the system)
			$keys = ziggeo_p_get_keys();

			for($i = 0, $c = count($keys); $i < $c; $i++) {
				if($keys[$i]['app_token'] === $app_token) {
					$ziggeo = new Ziggeo($keys[$i]['app_token'], $keys[$i]['private']);
				}
			}
		}
		else {
			//use the provided app token and private key
			$ziggeo = new Ziggeo($app_token, $private_key);
		}

		if($ziggeo) {
			try {
				$result = $ziggeo->application()->get();
				return $result;
			}
			catch(Exception $exc) {
				if($return_error === true) {
					return 'Could not do it:' . $exc->getMessage();
				}
				else {
					return false;
				}
			}
		}

		return false;
	}

	// Updates the name of the application
	// $args can be:
    //   name: Name of the application
    //   auth_token_required_for_create: Require auth token for creating videos
    //   auth_token_required_for_update: Require auth token for updating videos
    //   auth_token_required_for_read: Require auth token for reading videos
    //   auth_token_required_for_destroy: Require auth token for deleting videos
    //   client_can_index_videos: Client is allowed to perform the index operation
    //   client_cannot_access_unaccepted_videos: Client cannot view unaccepted videos
    //   enable_video_subpages: Enable hosted video pages
	function ziggeo_a_sdk_page_application_update($args, $app_token = null) {

		if($app_token === null) {
			$ziggeo = ziggeo_p_sdk_get_object(true);
		}
		else {
			//get private key based on the application token (if already added to the system)
			$keys = ziggeo_p_get_keys();

			for($i = 0, $c = count($keys); $i < $c; $i++) {
				if($keys[$i]['app_token'] === $app_token) {
					$ziggeo = new Ziggeo($keys[$i]['app_token'], $keys[$i]['private']);
				}
			}
		}

		if($ziggeo) {
			try {
				$result = $ziggeo->application()->update($args);
				return $result;
			}
			catch(Exception $exc) {
				return 'Could not do it:' . $exc->getMessage();
			}
		}

		return false;
	}

///////////////////////////////////  ANALYTICS  ///////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////

	// Get the analytics data
	function ziggeo_p_sdk_analytics_app_get($app_token = null, $query = null, $return_error = true) {

		if($query === null) {
			return false;
		}

		if($app_token === null) {
			$ziggeo = ziggeo_p_sdk_get_object(true);
		}
		else {
			//get private key based on the application token (if already added to the system)
			$keys = ziggeo_p_get_keys();

			for($i = 0, $c = count($keys); $i < $c; $i++) {
				if($keys[$i]['app_token'] === $app_token) {
					$ziggeo = new Ziggeo($keys[$i]['app_token'], $keys[$i]['private']);
				}
			}
		}

		if($ziggeo) {
			try {
				$result = $ziggeo->analytics()->get($query);
				return $result;
			}
			catch(Exception $exc) {
				if($return_error === false) {
					return 'Could not do it:' . $exc->getMessage();
				}
			}
		}

		return false;
	}



/////////////////////////////////// AUTH TOKENS ///////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////


///////////////////////////////// EFFECT  PROFILS /////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////

	// Creates a new effect profile
	//  $arguments can be:
	//    key: Effect profile key.
	//    title: Effect profile title.
	//    default_effect: Boolean. If TRUE, sets an effect profile as default. If FALSE,
	//                    removes the default status for the given effect
	function ziggeo_p_sdk_effect_profiles_create($arguments = array()) {

		$ziggeo = ziggeo_p_sdk_get_object(true);

		if(!empty($data['key'])) {
			$arguments['key'] = $data['key'];
		}
		else {
			unset($arguments['key']);
		}

		if($ziggeo) {
			try {
				$result = $ziggeo->effectProfiles()->create($arguments);
				return $result;
			}
			catch(Exception $exc) {
				return 'Could not do it:' . $exc->getMessage();
			}
		}

		return false;
	}

	// Searches for all effect profiles within your application
	//  $arguments can be:
	//    limit: Limit the number of returned effect profiles. Can be set up to 100.
	//    skip: Skip the first [n] entries.
	//    reverse: Reverse the order in which effect profiles are returned.
	function ziggeo_p_sdk_effect_profiles_list($arguments = array()) {

		$ziggeo = ziggeo_p_sdk_get_object(true);

		if($ziggeo) {
			try {
				$result = $ziggeo->effectProfiles()->index($arguments);
				return $result;
			}
			catch(Exception $exc) {
				return 'Could not do it:' . $exc->getMessage();
			}
		}

		// Index will return empty array, so this way we are sending back the expected values.
		return array();
	}

	// Returns the data of one specific effect profile
	function ziggeo_p_sdk_effect_profiles_get($token = null) {

		if($token === null) {
			return false;
		}

		$ziggeo = ziggeo_p_sdk_get_object(true);

		if($ziggeo) {
			try {
				$result = $ziggeo->effectProfiles()->get($token);
				return $result;
			}
			catch(Exception $exc) {
				return 'Could not do it:' . $exc->getMessage();
			}
		}

		return false;
	}

	// Function to update the effect profile
	//  $arguments can be:
	//    default_effect: Boolean. If TRUE, sets an effect profile as default. If FALSE,
	//                    removes the default status for the given effect
	function ziggeo_p_sdk_effect_profiles_update($token = null, $arguments = array()) {

		if($token === null) {
			return false;
		}

		$ziggeo = ziggeo_p_sdk_get_object(true);

		if($ziggeo) {
			try {
				$result = $ziggeo->effectProfiles()->update($token, $arguments);
				return $result;
			}
			catch(Exception $exc) {
				return 'Could not do it:' . $exc->getMessage();
			}
		}

		return false;
	}

	// Removes the Effect Profile with the given token
	function ziggeo_p_sdk_effect_profiles_delete($token = null) {

		if($token === null) {
			return false;
		}

		$ziggeo = ziggeo_p_sdk_get_object(true);

		if($ziggeo) {
			try {
				$result = $ziggeo->effectProfiles()->delete($token);
				return $result;
			}
			catch(Exception $exc) {
				return 'Could not do it:' . $exc->getMessage();
			}
		}

		return false;
	}

	// Create effect profile process
	// $type can be: "filter" or "watermark"
	// $arguments can be:
	//    effect: Effect to be applied in the process [filter]
	//    file: Image file to be attached [watermark]
	//    vertical_position: Specify the vertical position of your watermark (a value between 0.0 and 1.0) [watermark]
	//    horizontal_position: Specify the horizontal position of your watermark (a value between 0.0 and 1.0)
	//                         [watermark]
	//    video_scale: Specify the image scale of your watermark (a value between 0.0 and 1.0) [watermark]

	function ziggeo_p_sdk_effect_profiles_processes_create($token = null, $type = 'filter', $arguments = array()) {

		if($token === null) {
			return false;
		}

		$ziggeo = ziggeo_p_sdk_get_object(true);

		if($ziggeo) {
			if($type === 'filter') {
				try {
					$result = $ziggeo->effectProfileProcess()->create_filter_process($token, $arguments);
					return $result;
				}
				catch(Exception $exc) {
					return 'Could not do it:' . $exc->getMessage();
				}
			}
			elseif($type === 'watermark') {
				try {
					$result = $ziggeo->effectProfileProcess()->create_watermark_process($token, $arguments);
					return $result;
				}
				catch(Exception $exc) {
					return 'Could not do it:' . $exc->getMessage();
				}
			}
		}

		return false;
	}

	// Get all of the processes within a single effect profile
	//  $arguments can be:
	//    states: Filter streams by state
	function ziggeo_p_sdk_effect_profiles_processes_list($token = null, $arguments = array()) {

		if($token === null) {
			return false;
		}

		$ziggeo = ziggeo_p_sdk_get_object(true);

		if($ziggeo) {
			try {
				$result = $ziggeo->effectProfileProcess()->index($token, $arguments);
				return $result;
			}
			catch(Exception $exc) {
				return 'Could not do it:' . $exc->getMessage();
			}
		}

		return false;
	}

	// Return the data on single effect profile process
	function ziggeo_p_sdk_effect_profiles_processes_get($effect_token = null, $process_token = null) {

		if($effect_token === null || $process_token === null) {
			return false;
		}

		$ziggeo = ziggeo_p_sdk_get_object(true);

		if($ziggeo) {
			try {
				$result = $ziggeo->effectProfileProcess()->get($effect_token, $process_token);
				return $result;
			}
			catch(Exception $exc) {
				return 'Could not do it:' . $exc->getMessage();
			}
		}

		return false;
	}

	// Remove the specific process within effect profile
	function ziggeo_p_sdk_effect_profiles_processes_delete($effect_token = null, $process_token = null) {

		if($effect_token === null || $process_token === null) {
			return false;
		}

		$ziggeo = ziggeo_p_sdk_get_object(true);

		if($ziggeo) {
			try {
				$result = $ziggeo->effectProfileProcess()->delete($effect_token, $process_token);
				return $result;
			}
			catch(Exception $exc) {
				return 'Could not do it:' . $exc->getMessage();
			}
		}

		return false;
	}


	function ziggeo_p_sdk_effect_profiles_create_watermark($data = null, $image = null) {

		if($data === null || $image === null) {
			return false;
		}

		$ziggeo = ziggeo_p_sdk_get_object(true);

		$arguments = array(
			'file'                  => $image['tmp_name'],
			'vertical_position'     => $data['position_y'],
			'horizontal_position'   => $data['position_x'],
			'video_scale'           => $data['scale']
		);

		if($ziggeo) {
			try {
				$result = $ziggeo->effectProfileProcess()->create_watermark_process($data['effect_token'], $arguments);
				return $result;
			}
			catch(Exception $exc) {
				return 'Could not do it:' . $exc->getMessage();
			}
		}

		return false;
	}

	function ziggeo_p_sdk_effect_profiles_create_filter($data = null) {

		if($data === null) {
			return false;
		}

		$ziggeo = ziggeo_p_sdk_get_object(true);

		$arguments = array(
			'effect'     => $data['effect']
		);

		if($ziggeo) {
			try {
				$result = $ziggeo->effectProfileProcess()->create_filter_process($data['effect_token'], $arguments);
				return $result;
			}
			catch(Exception $exc) {
				return 'Could not do it:' . $exc->getMessage();
			}
		}

		return false;
	}


////////////////////////////////// META PROFILES //////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////



?>