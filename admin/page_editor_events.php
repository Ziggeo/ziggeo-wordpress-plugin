<?php

// This file is intended to hold code specific to the events editor
// Added in v2.11

// Uses `ziggeo_events` option to store events
// Uses data/events to save the events for quick load

//Checking if WP is running or if this is a direct call..
defined('ABSPATH') or die();


/////////////////////////////////////////////////
//                   BACKEND                   //
/////////////////////////////////////////////////

	// Settings and fields
	////////////////////////

	// register_setting( string $option_group, string $option_name, array $args = array() )
	register_setting('ziggeo_grp_events',                                   // option group
	                 'ziggeo_events',                                       // option name
	                 array(
	                 	'default' => array(),                               // default values
	                 	'sanitize_callback' => 'ziggeo_a_ee_validation'     // sanitize callback
	                 )
	);

		// add_settings_section( string $id, string $title, callable $callback, string $page )
		add_settings_section('ziggeo_sct_events_editor',            // id
		                     '',                                    // title
		                     'ziggeo_a_ee_editor_text',             // callback
		                     'ziggeo_editor_events'                 // page
		);

		// add_settings_field( string $id, string $title, callable $callback, string $page, string $section = 'default', array $args = array() )
		add_settings_field('ziggeo_ee_events_id',                   // id
							__('Event ID', 'ziggeo'),               // title
							'ziggeo_a_ee_id_field',                 // callback
							'ziggeo_editor_events',                 // page
							'ziggeo_sct_events_editor');            // section

		add_settings_field('ziggeo_ee_events_selection',            // id
							__('Select Ziggeo event', 'ziggeo'),    // title
							'ziggeo_a_ee_events_list_field',        // callback
							'ziggeo_editor_events',                 // page
							'ziggeo_sct_events_editor');            // section

		add_settings_field('ziggeo_ee_events_type',                 // id
							__('Select Event Type', 'ziggeo'),      // title
							'ziggeo_a_ee_event_type_field',         // callback
							'ziggeo_editor_events',                 // page
							'ziggeo_sct_events_editor');            // section

		add_settings_field('ziggeo_ee_events_alert_message',        // id
							__('Alert Message', 'ziggeo'),          // title
							'ziggeo_a_ee_alert_message_field',      // callback
							'ziggeo_editor_events',                 // page
							'ziggeo_sct_events_editor');            // section

		add_settings_field('ziggeo_ee_events_custom_code',          // id
							__('Custom Code', 'ziggeo'),            // title
							'ziggeo_a_ee_custom_code_field',        // callback
							'ziggeo_editor_events',                 // page
							'ziggeo_sct_events_editor');            // section

		add_settings_field('ziggeo_ee_events_custom_inject',        // id
							__('Type of code inject', 'ziggeo'),    // title
							'ziggeo_a_ee_code_inject_field',        // callback
							'ziggeo_editor_events',                 // page
							'ziggeo_sct_events_editor');            // section

		add_settings_field('ziggeo_ee_buttons',                     // id
							'',                                     // title
							'ziggeo_a_ee_buttons_field',            // callback
							'ziggeo_editor_events',                 // page
							'ziggeo_sct_events_editor');            // section


	// Callbacks
	//////////////

		//Function to show page content
		function ziggeo_a_ee_editor_text() {
			
			?>
			<p>
				<strong>Example shortcodes</strong>
				<code>[ziggeo_event event=verified message="my message" type=alert]</code>
				<code>[ziggeo_event id="event_id_set_in_editor" type=template]</code>
			</p>
			<p>
				<strong>Types:</strong> There are two types of alerts you can choose from. <i>Alert</i> type will allow you to show alert after some event fires. Useful to show some message once. Not recommended to set it for all events as it could pop up too many alerts.<br>
				<i>Template</i> type will allow you to save it with your own code that would be injected into the page when called by ID.
			</p>
			<p>
				<strong>Custom Codes</strong> can be set to be injected into the page on page load, or to be injected once the specific event fires. Both can be used in same use cases, however one might be more preferred than the other in yours. We suggest testing it out to see which one you like more.<br>
				Note, insertAdjacentHTML is used in both cases. Your code has to activate itself.
			</p>

			<p>
				<strong>Please Note:</strong>
				<ul>
					<li>Please be mindful that some events shown here might not be availble to you depending on the version of the Ziggeo JS SDK you are using (you can change this in plugin settings)</li>
					<li>In your code and if you use custom code injection on event activation, you will always have attributes <code>attr1</code>, <code>attr2</code>, <code>attr3</code>, <code>attr4</code> regardless if event provides them or not, so use only if you know there should be something in there. For more information please go to https://ziggeo.com/docs/sdks/javascript/browser-interaction/events#javascript-revision=r39</li>
					<li>There is always <code>embedding</code> variable available for your custom code</li>
				</ul>
			</p>

			<strong>Existing Event Templates</strong>
			<ol id="existing_event_templates">
			<?php
				$existing = get_option('ziggeo_events');
				if(!is_array($existing)) {
					$existing = array();
					?><li>No events have been saved yet</li><?php
				}
				foreach($existing as $id => $template) {
					?>
					<li title="<?php echo htmlentities($template['code']); ?>">
						<span class="event_id"><?php echo $id ?></span>
						<span class="event_remove" data-id="<?php echo $id; ?>">x</span>
						<textarea><?php echo $template['code']; ?></textarea>
					</li>
					<?php
				}
				?>
			</ol>
			<?php
		}

		// Function that shows the field for capturing event ID
		function ziggeo_a_ee_id_field() {
			?>
				<input id="ziggeo-ee-event-id" type="text">
				<p class="description">Insert Event ID (no space or special characters). This is not related to Ziggeo Event names, this is specific to your Wordpress install as you will use this ID to use this template</p>
			<?php
		}

		// Function that helps us show the list of events to choose from
		function ziggeo_a_ee_events_list_field() {
			?>
			<select id="ziggeo-ee-event">
				<optgroup label="Media Events">
					<option value="ended">Ended</option>
					<option value="invoke-skip">Invoke Skip</option>
					<option value="loaded">Loaded</option>
					<option value="paused">Paused</option>
					<option value="playing">Playing</option>
					<option value="recording">Recording</option>
					<option value="rerecord">Rerecord</option>
					<option value="seek">Seek</option>
					<option value="select-image">Select- Image</option>
					<option value="uploading">Uploading</option>
				</optgroup>
				<optgroup label="DOM Events">
					<option value="attached">Attached</option>
				</optgroup>
				<optgroup label="Progress Events">
					<option value="countdown">Countdown</option>
					<option value="processed">Processed</option>
					<option value="processing">Processing</option>
					<option value="recording_progress">Recording Progress</option>
					<option value="upload_progress">Upload Progress</option>
					<option value="uploaded">Uploaded</option>
				</optgroup>
				<optgroup label="Error Events">
					<option value="access_forbidden">Access Forbidden</option>
					<option value="access_granted">Access Granted</option>
					<option value="camera_nosignal">Camera No Signal</option>
					<option value="camera_unresponsive">Camera Unresponsive</option>
					<option value="error">Error</option>
					<option value="no_camera">No Camera</option>
					<option value="no_microphone">No Microphone</option>
				</optgroup>
				<optgroup label="Information Events">
					<option value="bound">Bound</option>
					<option value="camera_signal">Camera Signal</option>
					<option value="camerahealth">Camera Health</option>
					<option value="has_camera">Has Camera</option>
					<option value="has_microphone">Has Microphone</option>
					<option value="mainvideostreamended">Main Video Stream Ended</option>
					<option value="manually_submitted">Manually Submitted</option>
					<option value="microphonehealth">Microphone Health</option>
					<option value="change-google-cast-volume">Google Cast: Volume Change</option>
					<option value="pause-google-cast">Google Cast: Pause</option>
					<option value="play-google-cast">Google Cast: Play</option>
					<option value="ready_to_play">Ready to Play</option>
					<option value="ready_to_record">Ready to Record</option>
					<option value="ready-to-trim">Ready to Trim</option>
					<option value="recording_stopped">Recording Stopped</option>
					<option value="stopped">Stopped</option>
					<option value="upload_selected">Upload Selected</option>
					<option value="verified">Verified</option>
					<option value="video-trimmed">Video Trimmed</option>
				</optgroup>
			</select>
			<p class="description">You can see all of the events on the following page: <a href="https://ziggeo.com/docs/sdks/javascript/browser-interaction/events">https://ziggeo.com/docs/sdks/javascript/browser-interaction/events</a></p>
			<?php
		}

		// Function that helps us show the field to choose the event type
		function ziggeo_a_ee_event_type_field() {
			?>
			<select id="ziggeo-ee-event-type">
				<option value="alert">Alert</option>
				<option value="template">Template</option>
			</select>
			<p class="description">Alert will show a message once event fires, while template will inject the code you provide into the page. We do not check the code, we just output it for you!</p>
			<?php
		}


		// Function that helps us set the alert message
		function ziggeo_a_ee_alert_message_field() {
			?>
			<input id="ziggeo-ee-event-message" type="text">
			<p class="description">Type the message you want to show once event fires.</p>
			<?php
		}

		// Function that allows us to accept injection of custom code
		function ziggeo_a_ee_custom_code_field() {
			?>
			<code class="ziggeo-ee-custom-code-placeholder" style="display: none;">
				&lt;script&gt;function eventCall(
				<b title="reference to embedding object">embedding</b>,
				<i>attr1</i>, <i>attr2</i>, <i>attr3</i>, <i>attr4</i>) {</code>
			<textarea id="ziggeo-ee-custom-code"></textarea>
			<code class="ziggeo-ee-custom-code-placeholder" style="display: none;">}&lt;/script&gt;</code>
			<p class="description">Add code to be injected into page.</p>
			<?php
		}

		function ziggeo_a_ee_code_inject_field() {
			?>
			<label for="ziggeo-ee-onload">On Page Load</label>
			<input id="ziggeo-ee-onload" type="radio" name="ziggeo_ee_code_injection_type" checked="checked"><br>

			<label for="ziggeo-ee-onfire">On Event Activation</label>
			<input id="ziggeo-ee-onfire" type="radio" name="ziggeo_ee_code_injection_type">

			<?php
		}

		// Function that shows the buttons to generate the code and save the template
		// Note: Alerts are not templates and will not be possible to save them
		function ziggeo_a_ee_buttons_field() {
			?>
			<span id="ziggeo-ee-btn-generate" class="ziggeo-ctrl-btn">Generate Shortcode</span>
			<span id="ziggeo-ee-btn-save" class="ziggeo-ctrl-btn">Save Template</span>

			<textarea id="ziggeo-ee-shortcode" style="display: none;"></textarea>
			<?php
		}



/////////////////////////////////////////////////
//                  FRONTEND                   //
/////////////////////////////////////////////////

?>
<div>
	<h2>Events Editor</h2>

	<form action="options.php" method="post">
		<?php
		wp_nonce_field('ziggeo_nonce_action', 'ziggeo_editor_events_nonce');
		settings_errors();
		settings_fields('ziggeo_events');
		do_settings_sections('ziggeo_editor_events');
		?>
	</form>
	<div id="ziggeo_messenger"><div id="ziggeo_message"></div><div>X</div></div>
</div>