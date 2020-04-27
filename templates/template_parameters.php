<?php

//File that will hold all of the parameters that can be utilized within the plugin.
//You could add your own in order to save them with templates and to later turn them into something else..
// to do that you would create your own ziggeo_get_template_parameters_list() function

/*

	Parameters:
		1. is it used in player
		2. is it used in recorder
		3. is it used in rerecorder
		4. is it used in uploader
		5. is it common (common goes into simple and uncommon goes to advanced (which has all))

*/


function ziggeo_template_write_simple_parameters_list() {
	$fields = ziggeo_get_template_parameters_list();
	$to_attach = array();
	$sections = array('system');

	//easy is for click to set fields
	$sections = apply_filters('ziggeo_templates_editor_easy_parameters_section', $sections);

	//Return the HTML for simple editor

	for($i = 0, $l = count($sections); $i < $l; $i++) {
		$default_style = ' style="display:none;"';

		if($i === 0) {
			$default_style = '';
		}

		if($sections[$i] === 'system') {
			?>
			<div id="ziggeo-embedding-parameters-easy" <?php echo $default_style; ?>>
			<?php
			$addition = '';
		}
		else {
			?>
			<div id="ziggeo-<?php echo $sections[$i]; ?>-parameters-easy" <?php echo $default_style; ?>>
			<?php
			$addition = $sections[$i] . '-';
		}

		foreach($fields[$sections[$i]] as $field => $params) {
			if( isset($params['simple']) && $params['simple'] === true) {

				$to_attach[] = 'ziggeo-template-field-' . $addition . $field;

				?>
				<div class="ziggeo-field">
					<div title="<?php echo $params['description'] ?>"><?php echo $field ?></div>
					<div>
						<?php
							if($params['type'] == 'integer' || $params['type'] == 'float') {
								?>
								<input type="number" min="0" id="ziggeo-template-field-<?php echo $addition . $field; ?>" data-equal="int" value="<?php echo $params['default_value']; ?>">
								<?php
							}
							elseif($params['type'] == 'string' || $params['type'] == 'array') {
								?>
								<input type="text" id="ziggeo-template-field-<?php echo $addition . $field; ?>" data-equal="<?php echo $params['type']; ?>" value="<?php echo $params['default_value']; ?>">
								<?php
							}
							elseif($params['type'] == 'bool') {
								?>
								<input type="checkbox" id="ziggeo-template-field-<?php echo $addition . $field; ?>" data-equal="bool" <?php
									if($params['default_value'] === true) { echo ' checked="checked"'; }
									?>>
								<?php
							}
							elseif($params['type'] === 'enum') {
								?>
								<select data-equal="enum" id="ziggeo-template-field-<?php echo $addition . $field; ?>">
										<option value=""><?php _e('Default', 'ziggeo'); ?></option>
									<?php
										foreach($params['options'] as $option) {
											?><option value="<?php echo $option; ?>"><?php echo $option; ?></option><?php
										}
									?>
								</select>
								<?php
							}
						?>
					</div>
				</div>
				<?php
			}
		}
		//</div bellow ends the ziggeo-embedding-parameters-easy at the top (or div with different ID)
		?>
		</div>
		<?php
	}
	?>
	<script type="text/JavaScript">
		jQuery(document).ready( function() {
		<?php
			foreach($to_attach as $elem) {
				?>
					var elm = document.getElementById('<?php echo $elem; ?>');
					if(document.addEventListener) {
						elm.addEventListener( 'change', ziggeoPUIParametersAddSimple, false );
					}
					else {
						//just in case
						elm.attachEvent( 'onchange', ziggeoPUIParametersAddSimple );
					}
				<?php
			}
		?>
		});
	</script>
	<?php
}


//Advanced editor parameters printout happens here..
function ziggeo_template_write_advanced_parameters_list($paged = true, $per_page = 'half', $page_when_over = 20) {

	$fields = ziggeo_get_template_parameters_list();
	$sections = array('system');

	//advanced is for adding parameters one by one into textarea
	$sections = apply_filters('ziggeo_templates_editor_advanced_parameters_section', $sections);

	for($i = 0, $c = count($sections); $i < $c; $i++) {
		$default_style = ' style="display:none;"';

		if($i === 0) {
			$default_style = '';
		}

		if($sections[$i] === 'system') {
			?>
			<div id="ziggeo-embedding-parameters-adv" <?php echo $default_style; ?>>
			<?php
			$default_class = '';
		}
		else {
			?>
			<div id="ziggeo-<?php echo $sections[$i]; ?>-parameters-adv" <?php echo $default_style; ?>>
			<?php
			$default_class = $sections[$i];
		}

			?>
			<dl class="ziggeo-params">
				<?php
				//pagination code
				$j = 0;
				if($paged === true && count($fields[$sections[$i]]) > $page_when_over) {
					if($per_page === 'third') {
						$total = round( count($fields[$sections[$i]]) / 3.3);
					}
					else { //if($per_page === 'half')
						$total = round( count($fields[$sections[$i]]) / 2);
					}
				}
				else {
					$total = count($fields[$sections[$i]]) + 10; //just so that we never reach it..
				}

				foreach($fields[$sections[$i]] as $field => $params) {

					if($j == $total) {
						?>
							</dl>
							<dl class="ziggeo-params">
						<?php
						$j = 0;
					}

					//Parameter fields code
					//@ADD - the class should say if it is aimed to be used in player, recorder, rerecorder, etc.
					if($params['type'] === 'enum') {
						?>
							<dt class="<?php echo $default_class; ?>" data-equal="string"><?php echo $field; ?></dt>
								<dd><?php echo $params['description']; ?><br>
									Possible options are: `<?php echo join('`, `', $params['options']); ?>`
								</dd>
						<?php
					}
					elseif($params['type'] === 'integer') {
						?>
							<dt class="<?php echo $default_class; ?>" data-equal="int"><?php echo $field; ?></dt>
								<dd><?php echo $params['description']; ?></dd>
						<?php
					}
					else {
						?>
							<dt class="<?php echo $default_class; ?>" data-equal="<?php echo $params['type']; ?>"><?php echo $field; ?></dt>
								<dd><?php echo $params['description']; ?></dd>
						<?php
					}

					$j++;
				}
				?>
			</dl>
		</div>
		<?php
	}
		/*
			<dt class="wall" data-equal="=">show_video_comments</dt>
				<dd>Boolean value to show the comments of each video - if available (under each video)</dd>
			<dt class="wall" data-equal="=">show_video_rating</dt>
				<dd>Boolean value to show the collected video rating - if available (under each video)</dd>
			<dt class="wall" data-equal="=">param</dt>
				<dd>desc</dd>
		*/
}

function ziggeo_get_template_parameters_list() {

	//parameters that are part of the Ziggeo API / Ziggeo service
	$system_parameters = array(
		'allowscreen' => array(
			'type'					=> 'bool',
			'description'			=> __('Allows you to activate screen recording in your recorder.', 'ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'advanced'				=> true,
			'simple'				=> true,
			'default_value'			=> false
		),
		'width' => array(
			'type'					=> 'integer',
			'description'			=> __('Integer value representing the width of player or recorder that you are setting this for. This will not change the width of recording, just of the video screen size', 'ziggeo'),
			'used_by_player'		=> true,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'advanced'				=> true,
			'simple'				=> true,
			'default_value'			=> 640
		),
		'height' => array(
			'type'					=> 'integer',
			'description'			=> __('Integer value representing the height of player or recorder that you are setting this for. This will not change the height of recording, just of the video screen size', 'ziggeo'),
			'used_by_player'		=> true,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'advanced'				=> true,
			'simple'				=> true,
			'default_value'			=> 480
		),
		'recordingwidth' => array(
			'type'					=> 'integer',
			'description'			=> __('Integer value representing the width of the recording. This will request specific width of recording, regardless of the size of the preview.', 'ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'advanced'				=> true,
			'simple'				=> true,
			'default_value'			=> 640
		),
		'recordingheight' => array(
			'type'					=> 'integer',
			'description'			=> __('Integer value representing the height of the recording. This will request specific height of recording, regardless of the size of the preview.', 'ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'advanced'				=> true,
			'simple'				=> true,
			'default_value'			=> 480
		),
		'responsive' => array(
			'type'					=> 'bool',
			'description'			=> __('Boolean value that allows you to make embedding capture the full size of the bounding box (applied on load only).', 'ziggeo'),
			'used_by_player'		=> true,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'advanced'				=> true,
			'simple'				=> true,
			'default_value'			=> true
		),
		'popup' => array(
			'type'					=> 'bool', //data-equal=""
			'description'			=> __('Boolean value that says if this is popup or standard embedding.', 'ziggeo'),
			'used_by_player'		=> true,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'advanced'				=> true,
			'simple'				=> true,
			'default_value'			=> false
		),
		'popup-width' => array(
			'type'					=> 'integer',
			'description'			=> __('Integer value setting up the width of the popup holding the embedding.', 'ziggeo'),
			'used_by_player'		=> true,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'advanced'				=> true,
			'simple'				=> true,
			'default_value'			=> ''
		),
		'popup-height' => array(
			'type'					=> 'integer', //data-equal="="
			'description'			=> __('Integer value setting up the height of the popup holding the embedding.', 'ziggeo'),
			'used_by_player'		=> true,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'advanced'				=> true,
			'simple'				=> true,
			'default_value'			=> ''
		),
		'video' => array(
			'type'					=> 'string', //data-equal="=''"
			'description'			=> __('String representation of a video token or video key.', 'ziggeo'),
			'used_by_player'		=> true,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'advanced'				=> true,
			'simple'				=> true,
			'default_value'			=> ''
		),
		'faceoutline' => array(
			'type'					=> 'bool',
			'description'			=> __('Boolean value setting if face outline would be shown on the video or not.', 'ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'advanced'				=> true,
			'simple'				=> true,
			'default_value'			=> false
		),
		'stream' => array(
			'type'					=> 'string',
			'description'			=> __('String representing stream token or stream key.', 'ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'advanced'				=> true,
			'simple'				=> false,
			'default_value'			=> ''
		),
		'modes' => array(
			'type'					=> 'array',
			'description'			=> __('Array value determining how the embedding is used. Possible values are "recorder", "player", "rerecorder" For more modes, separate values with comma.', 'ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'advanced'				=> true,
			'simple'				=> false,
			'v1_only'				=> true,
			'default_value'			=> ''
		),
		'tags' => array(
			'type'					=> 'array',//data-equal="=''"
			'description'			=> __('Array holding the tags that the new video should be associated with. By default it will add \"wordpress, {username}\".', 'ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'advanced'				=> true,
			'simple'				=> true,
			'default_value'			=> ''
		),
		'effect-profile' => array(
			'type'					=> 'array',
			'description'			=> __('Array allowing you to select what effects to be applied to recorder, or which video stream to get when playing (the one with the same effects applied).', 'ziggeo'),
			'used_by_player'		=> true,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'advanced'				=> true,
			'simple'				=> true,
			'default_value'			=> ''
		),
		'custom-data' => array(
			'type'					=> 'string',
			'description'			=> __('String of JSON formatted data that you wish to pass with the video.', 'ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'advanced'				=> true,
			'simple'				=> true,
			'default_value'			=> ''
		),
		'perms' => array(
			'type'					=> 'array',
			'description'			=> __('Array value with video permissions that you could apply: "<span title="Enables uploading of videos for your customer">allowupload</span>", "<span title="Disables recording of video">forbidrecord</span>", "<span title="Disables switching between uploading and recording">forbidswitch</span>", "<span title="Disables rerecording completely">forbidrerecord</span>", "<span title="Overwrites the video if a video with the same key already exists">forceoverwrite</span>".', 'ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'advanced'				=> true,
			'simple'				=> false,
			'v1_only'				=> true,
			'default_value'			=> ''
		),
		'skipinitial' => array(
			'type'					=> 'bool',
			'description'			=> __('Boolean value to disable recorder\'s initial screen.', 'ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'advanced'				=> true,
			'simple'				=> false,
			'default_value'			=> false
		),
		'audio-test-mandatory' => array(
			'type'					=> 'bool',
			'description'			=> __('Boolean value to disable the camera and microphone tests prior to recording.', 'ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'advanced'				=> true,
			'simple'				=> false,
			'default_value'			=> false
		),
		'display-timer' => array(
			'type'					=> 'bool',
			'description'			=> __('Boolean value to show (or hide) the duration of recording on the recorder.', 'ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'advanced'				=> true,
			'simple'				=> false,
			'default_value'			=> true
		),
		'picksnapshots' => array(
			'type'					=> 'bool',
			'description'			=> __('Enables/disables the selection of snapshots after the recording.', 'ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'advanced'				=> true,
			'simple'				=> false,
			'default_value'			=> true
		),
		'early-rerecord' => array(
			'type'					=> 'bool',
			'description'			=> __('Boolean value to hide rerecord option while picking snapshots.', 'ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'advanced'				=> true,
			'simple'				=> false,
			'default_value'			=> false
		),
		'auto-crop' => array(
			'type'					=> 'bool',
			'description'			=> __('Boolean value to automatically crop videos to specific resolution (this cuts all the parts that are bigger than set resolution).', 'ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'advanced'				=> true,
			'simple'				=> false,
			'default_value'			=> ''
		),
		'auto-pad' => array(
			'type'					=> 'bool',
			'description'			=> __('Boolean value to automatically add black surface padding if video does not match set resolution - can only be applied to recorder.', 'ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'advanced'				=> true,
			'simple'				=> false,
			'default_value'			=> ''
		),
		'key' => array(
			'type'					=> 'string',
			'description'			=> __('String that tells recorder under which key the video should be saved under.', 'ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'advanced'				=> true,
			'simple'				=> false,
			'default_value'			=> ''
		),
		'timelimit' => array(
			'type'					=> 'integer',
			'description'			=> __('Integer value limiting the number of seconds that video / recording can be.', 'ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'advanced'				=> true,
			'simple'				=> true,
			'default_value'			=> 0 //Equal to unlimited
		),
		'countdown' => array(
			'type'					=> 'integer',
			'description'			=> __('Integer value to set when the recording should start after selecting same. Defaults to 3 seconds. Use 0 to disable countdown.', 'ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'advanced'				=> true,
			'simple'				=> true,
			'default_value'			=> 3
		),
		'input-bind' => array(
			'type'					=> 'string',
			'description'			=> __('String value representing form field name to which video token would be passed over.', 'ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'advanced'				=> true,
			'simple'				=> false,
			'default_value'			=> ''
		),
		'form-accept' => array(
			'type'					=> 'string',
			'description'			=> __('String value holding jQuery selector to disable form submission until video is created.', 'ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'advanced'				=> true,
			'simple'				=> false,
			'default_value'			=> ''
		),
		'id' => array(
			'type'					=> 'string',
			'description'			=> __('String value representing desired ID of embedding element so that it can be looked up using JavaScript code.', 'ziggeo'),
			'used_by_player'		=> true,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'advanced'				=> true,
			'simple'				=> false,
			'v1_only'				=> true,
			'default_value'			=> ''
		),
		'immediate_playback' => array(
			'type'					=> 'bool',
			'description'			=> __('Boolean value to tell if the video should start playing right away after recording.', 'ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'advanced'				=> true,
			'simple'				=> false,
			'v1_only'				=> true,
			'default_value'			=> ''
		),
		'autoplay' => array(
			'type'					=> 'bool',
			'description'			=> __('Boolean value to indicate if the video should automatically play back in player.', 'ziggeo'),
			'used_by_player'		=> true,
			'used_by_recorder'		=> false,
			'used_by_rerecorder'	=> false,
			'advanced'				=> true,
			'simple'				=> false,
			'default_value'			=> false
		),
		'loop' => array(
			'type'					=> 'bool',
			'description'			=> __('Boolean value to set if you wish for the player to play the video indefinitely.', 'ziggeo'),
			'used_by_player'		=> true,
			'used_by_recorder'		=> false,
			'used_by_rerecorder'	=> false,
			'advanced'				=> true,
			'simple'				=> false,
			'default_value'			=> false
		),
		'server-auth' => array(
			'type'					=> 'string',
			'description'			=> __('String representing authorization token retrieved from the server side.', 'ziggeo'),
			'used_by_player'		=> true,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'advanced'				=> true,
			'simple'				=> false,
			'default_value'			=> ''
		),
		'client-auth' => array(
			'type'					=> 'string',
			'description'			=> __('String representing authorization token for use on client side.', 'ziggeo'),
			'used_by_player'		=> true,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'advanced'				=> true,
			'simple'				=> true,
			'default_value'			=> ''
		),
		'recordings' => array(
			'type'					=> 'integer',
			'description'			=> __('Integer value indicating how many (re)recordings you would allow to be made.', 'ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'advanced'				=> true,
			'simple'				=> true,
			'default_value'			=> 0 //equal to unlimited
		),
		'expiration-days' => array(
			'type'					=> 'integer',
			'description'			=> __('Integer value to set after how many days you want to delete the recorded video (by default, never).', 'ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'advanced'				=> true,
			'simple'				=> false,
			'default_value'			=> 0 //same as no expiration
		),
		'video-profile' => array(
			'type'					=> 'string',
			'description'			=> __('String value holding key or token of your video profile that you want to use.', 'ziggeo'),
			'used_by_player'		=> true,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'advanced'				=> true,
			'simple'				=> true,
			'default_value'			=> ''
		),
		'meta-profile' => array(
			'type'					=> 'string',
			'description'			=> __('String value holding key or token of your meta profile that you want to use.', 'ziggeo'),
			'used_by_player'		=> true,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'advanced'				=> true,
			'simple'				=> true,
			'default_value'			=> ''
		),
		'stream-width' => array(
			'type'					=> 'integer',
			'description'			=> __('Integer value setting the optimal width of the stream.', 'ziggeo'),
			'used_by_player'		=> true,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'advanced'				=> true,
			'simple'				=> false,
			'default_value'			=> ''
		),
		'stream-height' => array(
			'type'					=> 'integer',
			'description'			=> __('Integer value setting the optimal height of the stream.', 'ziggeo'),
			'used_by_player'		=> true,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'advanced'				=> true,
			'simple'				=> false,
			'default_value'			=> ''
		),
		'title' => array(
			'type'					=> 'string',
			'description'			=> __('String value to set title of the video being recorded.', 'ziggeo'),
			'used_by_player'		=> true,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'advanced'				=> true,
			'simple'				=> true,
			'default_value'			=> ''
		),
		'description' => array(
			'type'					=> 'string',
			'description'			=> __('String value to set the description of the video.', 'ziggeo'),
			'used_by_player'		=> true,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'advanced'				=> true,
			'simple'				=> true,
			'default_value'			=> ''
		),
		'allowedextensions' => array(
			'type'					=> 'string',
			'description'			=> __('String value to limit the uploads to only specific extensions (all allowed by default).', 'ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'advanced'				=> true,
			'simple'				=> false,
			'default_value'			=> ''
		),
		'enforce-duration' => array(
			'type'					=> 'bool',
			'description'			=> __('Boolean value to reject videos if they are too long.', 'ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'advanced'				=> true,
			'simple'				=> false,
			'default_value'			=> false
		),
		'filesizelimit' => array(
			'type'					=> 'bool',
			'description'			=> __('Integer value to limit the size of videos being uploaded in bytes (no limit by default).', 'ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'advanced'				=> true,
			'simple'				=> false,
			'default_value'			=> 0 //no limit
		),
		'framerate-warning' => array(
			'type'					=> 'bool',
			'description'			=> __('Boolean value to set a warning to be shown if framerate is too low.', 'ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'advanced'				=> true,
			'simple'				=> false,
			'default_value'			=> false
		),
		'nofullscreen' => array(
			'type'					=> 'bool',
			'description'			=> __('Boolean value to disable fullscreen option in player.', 'ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'advanced'				=> true,
			'simple'				=> false,
			'default_value'			=> false
		),
		'stretch' => array(
			'type'					=> 'bool',
			'description'			=> __('Boolean value to set the player to play video in full width (regardless if hight gets cut or not).', 'ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'advanced'				=> true,
			'simple'				=> false,
			'default_value'			=> false
		),
		'theme' => array(
			'type'					=> 'string',
			'description'			=> __('String value of the name of the theme that you wish to have applied to your player.', 'ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'advanced'				=> true,
			'simple'				=> false,
			'default_value'			=> 'modern'
		)
	);

	//hook to easily add your own parameter to list if you wanted..

	$parameters_list = apply_filters('ziggeo_template_parameters_list', array('system' => $system_parameters));

	return $parameters_list;
}

?>