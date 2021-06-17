<?php
/*
	This file is inteded to handle the AJAX calls for the SDK pages. The output provided by these functions can change as it is mostly intended to be used by our codes. For building your own dashboard, it would be best to use the functionality provided in the core/sdk.php file 
	This file will also include some calls that might not be retrieved over AJAX
*/

//Admin calls handler for workinjg with Ziggeo SDK
add_filter('ziggeo_ajax_call', function($rez, $operation) {

	//settings_manage_template
	if($operation === 'sdk_applications') {
		if(isset($_POST['sdk_action'])) {
			$action = $_POST['sdk_action'];
			ziggeo_p_include_sdk();

			if($action === 'application_change_name') {
				if(!isset($_POST['name'])) {
					$rez = '';
				}
				else {
					$rez = ziggeo_a_sdk_page_application_change_name($_POST['name']);
				}
			}
			if($action === 'application_change_description') {
				if(!isset($_POST['description'])) {
					$rez = '';
				}
				else {
					$rez = ziggeo_a_sdk_page_application_change_description($_POST['description']);
				}
			}
			elseif($action === 'application_new_keys') {
				if(!isset($_POST['token'], $_POST['key'])) {
					$rez = '';
				}
				else {
					$rez = ziggeo_a_sdk_page_application_new_keys($_POST['token'], $_POST['key']);
				}
			}
			elseif($action === 'application_require_auth') {
				if(!isset($_POST['type'], $_POST['value'])) {
					$rez = '';
				}
				else {
					$rez = ziggeo_a_sdk_page_auth_require($_POST['type'], $_POST['value']);
				}
			}
			elseif($action === 'application_allow_index') {
				if(!isset($_POST['type'], $_POST['value'])) {
					$rez = '';
				}
				else {
					//$rez = ziggeo_a_sdk_page_auth_require($_POST['type'], $_POST['value']);
				}
			}
			elseif($action === 'applications_get_detail') {
				if(!isset($_POST['token'])) {
					$rez = '';
				}
				else {
					$rez = ziggeo_a_sdk_page_applications_get($_POST['token']);
				}
			}
		}
		else {
			$rez = false;
		}
	}
	elseif($operation === 'sdk_analytics') {
		if(isset($_POST['sdk_action'])) {

			$action = $_POST['sdk_action'];
			ziggeo_p_include_sdk();

			if($action === 'analytics_get') {
				if(!isset($_POST['token'], $_POST['from'], $_POST['to'])) {
					$rez = '';
				}
				else {

					// In some cases this can be empty string (this is OK)
					// When that happens we are making request for default application
					if($_POST['token'] === '') {
						$_POST['token'] = null;
					}

					$available = [
						'device_views_by_os',
						'device_views_by_date',
						'total_plays_by_country',
						'full_plays_by_country',
						'total_plays_by_hour',
						'full_plays_by_hour',
						'total_plays_by_browser',
						'full_plays_by_browser'
					];

					$rez = [];

					for($i = 0; $i < count($available); $i++) {
						$query = array(
							'from' => $_POST['from'],
							'to' => $_POST['to'],
							'query' => $available[$i]
						);

						$rez[$available[$i]] = ziggeo_a_sdk_page_analytics_get($_POST['token'], $query);
					}
				}
			}
		}

	}
	elseif($operation === 'sdk_effect_profiles') {
		if(isset($_POST['sdk_action'])) {

			$action = $_POST['sdk_action'];
			ziggeo_p_include_sdk();

			if($action === 'effect_profile_get_all') {
				$rez = ziggeo_a_sdk_page_effect_profile_get($_POST['token']);
			}
			elseif($action === 'effect_profile_processes_list') {
				$rez = ziggeo_p_sdk_effect_profiles_processes_list($_POST['token']);
			}
			elseif($action === 'effect_profile_delete') {
				$rez = ziggeo_p_sdk_effect_profiles_delete($_POST['token']);
			}
			elseif($action === 'effect_profile_create') {

				if(isset($_POST['data']['default'])) {
					$_POST['data']['default_effect'] = $_POST['data']['default'];
					unset($_POST['default']);
				}

				$rez = ziggeo_p_sdk_effect_profiles_create($_POST['data']);

				$rez = array(
					'token' => $rez->token,
					'data'  => ziggeo_p_sdk_page_effect_profile_list_item($rez, 'new', 'new')
				);
			}
			elseif($action === 'effect_profiles_create_watermark') {
				$rez = ziggeo_p_sdk_effect_profiles_create_watermark($_POST, $_FILES['file']);
			}
			elseif($action === 'effect_profiles_create_filter') {
				$rez = ziggeo_p_sdk_effect_profiles_create_filter($_POST);
			}
		}
		else {
			$rez = false;
		}
	}

	return array(
		'status' => 'success',
		'result' => $rez
	);

}, 10, 2);


/////////////////////////////////// APPLICATION ///////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////

	// Returns the data for the given application token. It will require the info for the private key to exist within the data.
	function ziggeo_a_sdk_page_applications_get($app_token = null) {

		$data = ziggeo_p_sdk_applications_get($app_token, null, false);

		if($data === false) {
			return false;
		}

		$result = '<label for="application_name">Application Name</label>' .
		'<input id="application_name" type="text" value="' . $data->name . '">' .
		'<span class="ziggeo-ctrl-btn ziggeo-sdk-ajax" ' .
		       'data-action="application_change_name" ' .
		       'data-operation="sdk_applications" ' .
		       'data-value="name:application_name">Update</span>' .
		'<input disabled="disabled" type="text" value="' . $data->token . '">' .

		'<label for="application_description">Application Description</label>' .
		'<textarea id="application_description">' . $data->description . '</textarea>' .
		'<span class="ziggeo-ctrl-btn ziggeo-sdk-ajax" ' .
		       'data-action="application_change_description" ' .
		       'data-operation="sdk_applications" ' .
		       'data-value="description:application_description">Update</span>' .

		'<label for=private_key>Private Key</label>' .
		'<input id="application_private_key" class="ziggeo_secret" disabled="disabled" type="text" value="' . $data->private_key . '">' .
		'<label for=encryption_key>Encryption Key</label>' .
		'<input id="application_encryption_key" class="ziggeo_secret" disabled="disabled" type="text" value="' . $data->encryption_key . '">' .

		'<label for="application_delete_delay">Video Removal Delay (in days)</label>' .
		'<input id="application_delete_delay" type="text" value="' . $data->delete_videos_after . '" disabled="disabled">' .

		'<label for="application_allowed_domains">Allowed Domains</label>' .
		'<ul id="application_allowed_domains" class="ziggeo-list" tabindex="0" disabled="disabled">';

		for($i = 0, $c = count($data->config->allowed_domains); $i < $c; $i++) {
			$result .= '<li tabindex="0" value="' . $data->config->allowed_domains[$i] . '">' .
			               $data->config->allowed_domains[$i] .
			           '</li>';
		}
		if($c === 0) {
			$result .= '<li value="">No domains are allowed (not recommended).</li>';
		}

		$result .= '</ul>' .

		// '<label for="application_allow_domain">Add new domain</label>' .
		// '<input id="application_allow_domain" type="text">' .
		// '<span class="ziggeo-ctrl-btn ziggeo-sdk-ajax" ' .
		//       'data-action="application_allow_domain" ' .
		//       'data-operation="sdk_applications" ' .
		//       'data-results="application_allowed_domains">Add</span>' .

		'<label for="application_custom_domains">Custom Domains</label>' .
		'<ul id="application_custom_domains" class="ziggeo-list" tabindex="0">';

		for($i = 0, $c = count($data->config->allowed_subdomains); $i < $c; $i++) {
			$result .= '<li value="' . $data->config->allowed_subdomains[$i] . '">' .
			               $data->config->allowed_subdomains[$i] .
			           '</li>';
		}
		if($c === 0) {
			$result .= '<li value="">No custom domains are set.</li>';
		}

		$result .= '</ul>' .

		// '<label for="application_custom_domain">Add new domain</label>' .
		// '<input id="application_custom_domain" type="text">' .
		// '<span class="ziggeo-ctrl-btn ziggeo-sdk-ajax" ' .
		//       'data-action="application_custom_domain" ' .
		//       'data-operation="sdk_applications" ' .
		//       'data-results="application_custom_domains">Add</span>' .

		'<label for="application_email_notifications">Emails to be notified</label>' .
		'<ul id="application_email_notifications" class="ziggeo-list" tabindex="0">';

		for($i = 0, $c = count($data->config->notify_emails); $i < $c; $i++) {
			$result .= '<li value="' . $data->config->notify_emails[$i] . '">' .
			               $data->config->notify_emails[$i] .
			           '</li>';
		}
		if($c === 0) {
			$result .= '<li value="">No emails are set.</li>';
		}

		$result .= '</ul>' .

		// '<label for="application_email_notification">Add new domain</label>' .
		// '<input id="application_email_notification" type="text">' .
		// '<span class="ziggeo-ctrl-btn ziggeo-sdk-ajax" ' .
		//       'data-action="application_email_notification" ' .
		//       'data-operation="sdk_applications" ' .
		//       'data-results="application_email_notifications">Add</span>' .

		'<label>Auth tokens required for:</label>';

		$on_off = ($data->config->auth_token_required_for_create === true) ? 'on' : 'off';

		$result .= '<span id="application_authtoken_for_create" ' .
		       'class="ziggeo-ctrl-img-toggle ' . $on_off . '" tabindex="0" ' .
		       'data-action="application_require_auth" ' .
		       'data-operation="sdk_applications" ' .
		       'data-value="' . $on_off . '" ' .
		       'data-type="create" ' .
		       'data-options="type:type">Record / Update</span>';

		$on_off = ($data->config->auth_token_required_for_update === true) ? 'on' : 'off';

		$result .= '<span id="application_authtoken_for_update" ' .
		       'class="ziggeo-ctrl-img-toggle ' . $on_off . '" tabindex="0" ' .
		       'data-action="application_require_auth" ' .
		       'data-operation="sdk_applications" ' .
		       'data-value="' . $on_off . '" ' .
		       'data-type="update" ' .
		       'data-options="type:type">Edit / Update</span>';

		$on_off = ($data->config->auth_token_required_for_read === true) ? 'on' : 'off';

		$result .= '<span id="application_authtoken_for_read" ' .
		       'class="ziggeo-ctrl-img-toggle ' . $on_off . '" tabindex="0" ' .
		       'data-action="application_require_auth" ' .
		       'data-operation="sdk_applications" ' .
		       'data-value="' . $on_off . '" ' .
		       'data-type="read" ' .
		       'data-options="type:type">Read / View</span>';

		$on_off = ($data->config->auth_token_required_for_destroy === true) ? 'on' : 'off';

		$result .= '<span id="application_authtoken_for_destroy" ' .
		       'class="ziggeo-ctrl-img-toggle ' . $on_off . '" tabindex="0" ' .
		       'data-action="application_require_auth" ' .
		       'data-operation="sdk_applications" ' .
		       'data-value="' . $on_off . '" ' .
		       'data-type="destroy" ' .
		       'data-options="type:type">Remove / Delete</span>';

		$result .= '<label>Additional Options:</label>';

		$on_off = ($data->allow_iframe_embeds === true) ? 'on' : 'off';

		$result .= '<span id="application_iframe_embeds" ' .
		       'class="ziggeo-ctrl-img-toggle ' . $on_off . ' disabled" tabindex="0" ' .
		       'data-action="application_allow_iframe" ' .
		       'data-operation="sdk_applications" ' .
		       'data-value="' . $on_off . '">Allow Iframe Embedds</span>';

		$on_off = ($data->delete_expired_videos === true) ? 'on' : 'off';

		$result .= '<span id="application_delete_expired" ' .
		       'class="ziggeo-ctrl-img-toggle ' . $on_off . ' disabled" tabindex="0" ' .
		       'data-action="application_delete_expired" ' .
		       'data-operation="sdk_applications" ' .
		       'data-value="' . $on_off . '">Delete Expired Videos</span>';

		$on_off = ($data->config->client_can_index_videos === true) ? 'on' : 'off';

		$result .= '<span id="application_client_can_index" ' .
		       'class="ziggeo-ctrl-img-toggle ' . $on_off . '" tabindex="0" ' .
		       'data-action="application_allow_index" ' .
		       'data-operation="sdk_applications" ' .
		       'data-value="' . $on_off . '">Allow JS API Search (Index)</span>';

		$on_off = ($data->config->client_can_index_videos === true) ? 'on' : 'off';

		$result .= '<span id="application_view_unapproved" class="ziggeo-ctrl-img-toggle off" tabindex="0">Disable video if not approved</span>';

		//Add option to add additional applications (they can be created through Ziggeo dashboard, however they can be added with public and private keys.)
		$result .= '<label for="application_new_app_token">Add Application Token</label>' .
		'<input id="application_new_app_token">' .
		'<label for="application_new_private_key">Add Private Key</label>' .
		'<input id="application_new_private_key">' .
		'<span class="ziggeo-ctrl-btn ziggeo-sdk-ajax" ' .
		       'data-action="application_new_keys" ' .
		       'data-operation="sdk_applications" ' .
		       'data-value="token:application_new_app_token,key:application_new_private_key" ' .
		       'data-validate="notempty:application_new_app_token,notempty:application_new_private_key"' .
		       'data-results="applications_list">Add</span>';

		return $result;
	}

	//Updates the name of the application
	function ziggeo_a_sdk_page_application_change_name($name, $app_token = null) {

		$args = array('name' => $name);

		return ziggeo_a_sdk_page_application_update($args, $app_token);
	}

	//Updates the description of the application
	function ziggeo_a_sdk_page_application_change_description($description, $app_token = null) {

		$args = array('description' => $description);

		return ziggeo_a_sdk_page_application_update($args, $app_token);
	}


	function ziggeo_a_sdk_page_application_new_keys($token, $key) {

		//1. Try to get the application details with provided token and key
		$app_test = ziggeo_p_sdk_applications_get($token, $key);

		//2. If we get it, that is OK
		if(isset($app_test->name)) {
			//3. We save the tokens

			$new_app[] = array(
				'app_token'     => $token,
				'private'       => $key,
				'encryption'    => $app_test->encryption_key,
				'title'         => $app_test->name
			);

			$tokens = get_option('ziggeo_keys');

			if(is_array($tokens)) {

				//Let us check if it is within the list already or not
				for($i = 0, $c = count($tokens); $i < $c; $i++) {
					if($tokens[$i]['app_token'] === $token) {
						return false;
					}
				}

				$tokens = array_merge($tokens, $new_app);
			}
			else {
				$tokens = $new_app;
			}

			update_option('ziggeo_keys', $tokens);

			//This way we get the plugin ones as well
			$tokens = ziggeo_p_get_keys();

			$result = '';

			for($i = 0, $c = count($tokens); $i < $c; $i++) {
				//$result .= '<li value="' . $tokens[$i]['app_token'] . '" tabIndex="0">' . $tokens[$i]['title'] . '</li>';
				$result .= '<option value="' . $tokens[$i]['app_token'] . '">' . $tokens[$i]['title'] . '</option>';
			}

			return $result;
		}
	}

	function ziggeo_a_sdk_page_auth_require($type = null, $value = null) {
		if($type === null || $value === null) {
			return false;
		}

		if($value === 'off') {
			$value = false;
		}
		elseif($value === 'on') {
			$value = true;
		}
		else {
			return false;
		}

		$args = array();

		if($type === 'create') {
			$args['auth_token_required_for_create'] = $value;
		}
		elseif($type === 'read') {
			$args['auth_token_required_for_read'] = $value;
		}
		elseif($type === 'update') {
			$args['auth_token_required_for_update'] = $value;
		}
		elseif($type === 'destroy') {
			$args['auth_token_required_for_destroy'] = $value;
		}
		else {
			return false;
		}

		ziggeo_a_sdk_page_application_update($args);

		return true;
	}





///////////////////////////////////  ANALYTICS  ///////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////

function ziggeo_a_sdk_page_analytics_get($app_token = null, $query = null) {

	if($query === null) {
		return false;
	}

	$data = ziggeo_p_sdk_analytics_app_get($app_token, $query, false);

	if($data) {
		return $data;
	}

	/*for($i = 0, $c = count($data->analytics); $i < $c; $i++) {
		$current = $data->analytics[$i];

		?>
		<b>OS</b>:<span><?php echo $current->os; ?></span>
		<b>Count</b>:<span><?php echo $current->event_count; ?></span>
		<?php
	}*/

	return false;
}



///////////////////////////////// EFFECT  PROFILS /////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////

	function ziggeo_a_sdk_page_effect_profile_get() {

		$result = '<h2>Existing Effect Profiles</h2>';

		$list = ziggeo_p_sdk_effect_profiles_list();

		if(count($list) === 0) {
			$result .= _x('No effect profiles found.', 'ziggeo');
		}
		else {
			$result .= _x('Effect profiles found:', 'ziggeo');

			$result .= '<ul class="ziggeosdk effect_profiles_list">';

				for($i = 0; $i < count($list); $i++) {
					$result .= ziggeo_p_sdk_page_effect_profile_list_item($list[$i], $i+1, false);
				}
			$result .= '</ul>';
		}

		return $result;
	}


	// Create Effect Profile list item
	function ziggeo_p_sdk_page_effect_profile_list_item($item, $current = 1, $cls = false) {

		$row = '<li id="effect-profile-' . $item->token . '"';

		if($cls !== false) {
			$row .= ' class="' . $cls . '"';
		}

		$row .= '>';

		$row .= '<span class="count">#</span><span class="count">' . $current . '</span>' .
					'<span class="volatile">Volatile?</span>';

		if($item->volatile) {
			$row .= '<span class="volatile">true</span>';
		}
		else {
			$row .= '<span class="volatile">false</span>';
		}

		$row .= '<span class="token">Profile Token</span>' .
					'<span class="token">' . $item->token . '</span>' .
				'<span class="key">Profile Key</span>' .
					'<span class="key">' . $item->key . '</span>' .
				'<span class="title">Profile Title</span>' .
					'<span class="title">' . $item->title . '</span>' .
				'<span class="image_only_effect">Image only?</span>';

		if($item->image_only_effect) {
			$row .= '<span class="image_only_effect">true</span>';
		}
		else {
			$row .= '<span class="image_only_effect">false</span>';
		}

		$row .= '<span class="default_effect">Default?</span>';

		if($item->default_effect) {
			$row .= '<span class="default_effect">true<span class="ziggeo-ctrl-btn-inline" data-effect-token="' . $item->token . '">Change</span></span>';
		}
		else {
			$row .= '<span class="default_effect">false<span class="ziggeo-ctrl-btn-inline" data-effect-token="' . $item->token . '">Change</span></span>';
		}

		$row .= '<span class="type">Profile Type</span>' .
					'<span class="type">' . $item->type . '</span>' .
				'<span class="created">Created at</span>' .
					'<span class="created">' . date_i18n( get_option('date_format'), $item->created) . '</span>' .
				'<span class="owned">Owned</span>';

		if($item->owned) {
			$row .= '<span class="owned">true</span>';
		}
		else {
			$row .= '<span class="owned">false</span>';
		}

		$row .= '<div class="additional_options">' .
					'<span class="ziggeo-ctrl-btn ziggeo-sdk-ajax"' .
					      'data-token="' . $item->token . '"' .
					      'data-action="effect_profile_processes_list"' .
					      'data-operation="sdk_effect_profiles">Get Processes</span>' .
					'<span class="ziggeo-ctrl-btn ziggeo-ctrl-form-popup"' .
					      'data-token="' . $item->token . '"' .
					      'data-form-name="effect_profile_processes_create">Create Process</span>' .
					'<span class="ziggeo-ctrl-btn delete ziggeo-sdk-ajax"' .
					      'data-token="' . $item->token . '"' .
					      'data-action="effect_profile_delete"' .
					      'data-operation="sdk_effect_profiles">Remove</span>' .
				'</div>' .
				'<div class="additional_info" id="ziggeo-sdk-effects-' . $item->token .'">' .
				'</div>' .
			'</li>';

		return $row;
	}

?>