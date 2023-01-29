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

				$used_by = '';

				if($params['used_by_player']) {
					$used_by .= ' player';
				}
				if($params['used_by_recorder']) {
					$used_by .= ' recorder';
				}
				if($params['used_by_rerecorder']) {
					$used_by .= ' rerecorder';
				}
				if($params['used_by_uploader']) {
					$used_by .= ' uploader';
				}

				?>
				<div class="ziggeo-field" data-type="<?php echo $used_by; ?>">
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

	// Count for the list
	$total = 0;
	for($i = 0, $c = count($sections); $i < $c; $i++) {
		if($paged === true && count($fields[$sections[$i]]) > $page_when_over) {
			if($per_page === 'quarter') {
				$total += floor( count($fields[$sections[$i]]) / 4);
			}
			elseif($per_page === 'third') {
				$total += floor( count($fields[$sections[$i]]) / 3.3);
			}
			else { //if($per_page === 'half')
				$total += floor( count($fields[$sections[$i]]) / 2);
			}
		}
		else {
			$total += count($fields[$sections[$i]]) + 10; //just so that we never reach it..
		}
	}

	?>
	<div id="ziggeo-embedding-parameters-list">
	<?php

	for($i = 0; $i < $c; $i++) {

		// To sort the keys by name A->Z
		ksort($fields[$sections[$i]]);

		foreach($fields[$sections[$i]] as $field => $params) {
			$default_class = 'param';

			if($params['used_by_player'] === true) {
				$default_class .= ' for_ziggeoplayer';
			}
			if($params['used_by_recorder'] === true) {
				$default_class .= ' for_ziggeorecorder';
			}
			if($params['used_by_rerecorder'] === true) {
				$default_class .= ' for_ziggeorerecorder';
			}
			if($params['used_by_uploader'] === true) {
				$default_class .= ' for_ziggeouploader';
			}
			if(isset($params['custom_used_by'])) {
				// This allows us to add custom used_by values when needed
				if(is_array($params['custom_used_by'])) {
					foreach($params['custom_used_by'] as $t_param) {
						$default_class .= ' for_' . $t_param;
					}
				}
				else {
					$default_class .= ' for_' . $params['custom_used_by'];
				}
			}

			//Parameter fields code
			if($params['type'] === 'enum') {
				?>
					<div class="<?php echo $default_class; ?>" data-type="<?php echo $params['type']; ?>"><?php echo $field; ?></div>
					<div class="param_description"><?php echo $params['description']; ?><br>
						Possible options are: `<?php echo join('`, `', $params['options']); ?>`
					</div>
				<?php
			}
			else {
				?>
					<div class="<?php echo $default_class; ?>" data-type="<?php echo $params['type']; ?>"><?php echo $field; ?></div>
					<div class="param_description"><?php echo $params['description']; ?></div>
				<?php
			}
		}
	}
	?>
	</div>
	<?php
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
			'used_by_uploader'		=> false,
			'default_value'			=> false,
			'media_type'			=> 'video'
		),
		'width' => array(
			'type'					=> 'integer',
			'description'			=> __('Integer value representing the width of player or recorder that you are setting this for. This will not change the width of recording, just of the video screen size', 'ziggeo'),
			'used_by_player'		=> true,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'used_by_uploader'		=> true,
			'default_value'			=> 640,
			'media_type'			=> 'video,audio',
			'custom_used_by'		=> array('ziggeoaudiorecorder', 'ziggeoaudioplayer')
		),
		'height' => array(
			'type'					=> 'integer',
			'description'			=> __('Integer value representing the height of player or recorder that you are setting this for. This will not change the height of recording, just of the video screen size', 'ziggeo'),
			'used_by_player'		=> true,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'used_by_uploader'		=> true,
			'default_value'			=> 480,
			'media_type'			=> 'video,audio',
			'custom_used_by'		=> array('ziggeoaudiorecorder', 'ziggeoaudioplayer')
		),
		'recordingwidth' => array(
			'type'					=> 'integer',
			'description'			=> __('Integer value representing the width of the recording. This will request specific width of recording, regardless of the size of the preview.', 'ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'used_by_uploader'		=> false,
			'default_value'			=> 640,
			'media_type'			=> 'video'
		),
		'recordingheight' => array(
			'type'					=> 'integer',
			'description'			=> __('Integer value representing the height of the recording. This will request specific height of recording, regardless of the size of the preview.', 'ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'used_by_uploader'		=> false,
			'default_value'			=> 480,
			'media_type'			=> 'video'
		),
		'popup' => array(
			'type'					=> 'bool', //data-equal=""
			'description'			=> __('Boolean value that says if this is popup or standard embedding.', 'ziggeo'),
			'used_by_player'		=> true,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'used_by_uploader'		=> false,
			'default_value'			=> false,
			'media_type'			=> 'video'
		),
		'popup-width' => array(
			'type'					=> 'integer',
			'description'			=> __('Integer value setting up the width of the popup holding the embedding.', 'ziggeo'),
			'used_by_player'		=> true,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'used_by_uploader'		=> false,
			'default_value'			=> '',
			'media_type'			=> 'video'
		),
		'popup-height' => array(
			'type'					=> 'integer', //data-equal="="
			'description'			=> __('Integer value setting up the height of the popup holding the embedding.', 'ziggeo'),
			'used_by_player'		=> true,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'used_by_uploader'		=> false,
			'default_value'			=> '',
			'media_type'			=> 'video'
		),
		'video' => array(
			'type'					=> 'string', //data-equal="=''"
			'description'			=> __('String representation of a video token or video key.', 'ziggeo'),
			'used_by_player'		=> true,
			'used_by_recorder'		=> false,
			'used_by_rerecorder'	=> true,
			'used_by_uploader'		=> false,
			'default_value'			=> '',
			'media_type'			=> 'video'
		),
		'faceoutline' => array(
			'type'					=> 'bool',
			'description'			=> __('Boolean value setting if face outline would be shown on the video or not.', 'ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'used_by_uploader'		=> false,
			'default_value'			=> false,
			'media_type'			=> 'video'
		),
		'stream' => array(
			'type'					=> 'string',
			'description'			=> __('String representing stream token or stream key.', 'ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'used_by_uploader'		=> false,
			'default_value'			=> '',
			'media_type'			=> 'video,audio',
			'custom_used_by'		=> 'ziggeoaudioplayer'
		),
		'tags' => array(
			'type'					=> 'array',//data-equal="=''"
			'description'			=> __('Array holding the tags that the new video should be associated with. By default it will add \"wordpress, {username}\".', 'ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'used_by_uploader'		=> true,
			'default_value'			=> '',
			'media_type'			=> 'video,audio',
			'custom_used_by'		=> 'ziggeoaudiorecorder'
		),
		'effect-profile' => array(
			'type'					=> 'array',
			'description'			=> __('Array allowing you to select what effects to be applied to recorder, or which video stream to get when playing (the one with the same effects applied).', 'ziggeo'),
			'used_by_player'		=> true,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'used_by_uploader'		=> true,
			'default_value'			=> '',
			'media_type'			=> 'video'
		),
		'custom-data' => array(
			'type'					=> 'json',
			'description'			=> __('String of JSON formatted data that you wish to pass with the video.', 'ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'used_by_uploader'		=> true,
			'default_value'			=> '',
			'media_type'			=> 'video,audio',
			'custom_used_by'		=> 'ziggeoaudiorecorder'
		),
		'skipinitial' => array(
			'type'					=> 'bool',
			'description'			=> __('Boolean value to disable recorder\'s initial screen.', 'ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'used_by_uploader'		=> false,
			'default_value'			=> false,
			'media_type'			=> 'video,audio',
			'custom_used_by'		=> array('ziggeoaudiorecorder', 'ziggeoaudioplayer')
		),
		'audio-test-mandatory' => array(
			'type'					=> 'bool',
			'description'			=> __('Boolean value to disable the camera and microphone tests prior to recording.', 'ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'used_by_uploader'		=> false,
			'default_value'			=> false,
			'media_type'			=> 'video,audio',
			'custom_used_by'		=> 'ziggeoaudiorecorder'
		),
		'display-timer' => array(
			'type'					=> 'bool',
			'description'			=> __('Boolean value to show (or hide) the duration of recording on the recorder.', 'ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'used_by_uploader'		=> false,
			'default_value'			=> true,
			'media_type'			=> 'video,audio',
			'custom_used_by'		=> 'ziggeoaudiorecorder'
		),
		'picksnapshots' => array(
			'type'					=> 'bool',
			'description'			=> __('Enables/disables the selection of snapshots after the recording.', 'ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'used_by_uploader'		=> false,
			'default_value'			=> true,
			'media_type'			=> 'video'
		),
		'early-rerecord' => array(
			'type'					=> 'bool',
			'description'			=> __('Boolean value to hide rerecord option while picking snapshots.', 'ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'used_by_uploader'		=> false,
			'default_value'			=> false,
			'media_type'			=> 'video,audio',
			'custom_used_by'		=> 'ziggeoaudiorecorder'
		),
		'auto-crop' => array(
			'type'					=> 'bool',
			'description'			=> __('Boolean value to automatically crop videos to specific resolution (this cuts all the parts that are bigger than set resolution).', 'ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'used_by_uploader'		=> false,
			'default_value'			=> '',
			'media_type'			=> 'video'
		),
		'auto-pad' => array(
			'type'					=> 'bool',
			'description'			=> __('Boolean value to automatically add black surface padding if video does not match set resolution - can only be applied to recorder.', 'ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'used_by_uploader'		=> false,
			'default_value'			=> '',
			'media_type'			=> 'video'
		),
		'key' => array(
			'type'					=> 'string',
			'description'			=> __('String that tells recorder under which key the video should be saved under.', 'ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'used_by_uploader'		=> true,
			'default_value'			=> '',
			'media_type'			=> 'video,audio',
			'custom_used_by'		=> 'ziggeoaudiorecorder'
		),
		'timelimit' => array(
			'type'					=> 'integer',
			'description'			=> __('Integer value limiting the number of seconds that video / recording can be.', 'ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'used_by_uploader'		=> true,
			'default_value'			=> 0, //Equal to unlimited
			'media_type'			=> 'video,audio',
			'custom_used_by'		=> 'ziggeoaudiorecorder'
		),
		'countdown' => array(
			'type'					=> 'integer',
			'description'			=> __('Integer value to set when the recording should start after selecting same. Defaults to 3 seconds. Use 0 to disable countdown.', 'ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'used_by_uploader'		=> false,
			'default_value'			=> 3,
			'media_type'			=> 'video,audio',
			'custom_used_by'		=> 'ziggeoaudiorecorder'
		),
		'input-bind' => array(
			'type'					=> 'string',
			'description'			=> __('String value representing form field name to which video token would be passed over.', 'ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'used_by_uploader'		=> true,
			'default_value'			=> '',
			'media_type'			=> 'video,audio',
			'custom_used_by'		=> 'ziggeoaudiorecorder'
		),
		'form-accept' => array(
			'type'					=> 'string',
			'description'			=> __('String value holding jQuery selector to disable form submission until video is created.', 'ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'used_by_uploader'		=> true,
			'default_value'			=> '',
			'media_type'			=> 'video,audio',
			'custom_used_by'		=> 'ziggeoaudiorecorder'
		),
		'localplayback' => array(
			'type'					=> 'bool',
			'description'			=> __('Boolean value to tell if the video should start playing right away after recording.', 'ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'used_by_uploader'		=> false,
			'v1_only'				=> true,
			'default_value'			=> '',
			'media_type'			=> 'video,audio',
			'custom_used_by'		=> 'ziggeoaudiorecorder'
		),
		'autoplay' => array(
			'type'					=> 'bool',
			'description'			=> __('Boolean value to indicate if the video should automatically play back in player.', 'ziggeo'),
			'used_by_player'		=> true,
			'used_by_recorder'		=> false,
			'used_by_rerecorder'	=> false,
			'used_by_uploader'		=> false,
			'default_value'			=> false,
			'media_type'			=> 'video,audio',
			'custom_used_by'		=> 'ziggeoaudioplayer'
		),
		'loop' => array(
			'type'					=> 'bool',
			'description'			=> __('Boolean value to set if you wish for the player to play the video indefinitely.', 'ziggeo'),
			'used_by_player'		=> true,
			'used_by_recorder'		=> false,
			'used_by_rerecorder'	=> false,
			'used_by_uploader'		=> false,
			'default_value'			=> false,
			'media_type'			=> 'video,audio',
			'custom_used_by'		=> 'ziggeoaudioplayer'
		),
		'server-auth' => array(
			'type'					=> 'string',
			'description'			=> __('String representing authorization token retrieved from the server side.', 'ziggeo'),
			'used_by_player'		=> true,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'used_by_uploader'		=> true,
			'default_value'			=> '',
			'media_type'			=> 'video,audio',
			'custom_used_by'		=> array('ziggeoaudiorecorder', 'ziggeoaudioplayer')
		),
		'client-auth' => array(
			'type'					=> 'string',
			'description'			=> __('String representing authorization token for use on client side.', 'ziggeo'),
			'used_by_player'		=> true,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'used_by_uploader'		=> true,
			'default_value'			=> '',
			'media_type'			=> 'video,audio',
			'custom_used_by'		=> array('ziggeoaudiorecorder', 'ziggeoaudioplayer')
		),
		'recordings' => array(
			'type'					=> 'integer',
			'description'			=> __('Integer value indicating how many (re)recordings you would allow to be made.', 'ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'used_by_uploader'		=> true,
			'default_value'			=> 0, //equal to unlimited
			'media_type'			=> 'video,audio',
			'custom_used_by'		=> 'ziggeoaudiorecorder'
		),
		'expiration-days' => array(
			'type'					=> 'integer',
			'description'			=> __('Integer value to set after how many days you want to delete the recorded video (by default, never).', 'ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'used_by_uploader'		=> true,
			'default_value'			=> 0, //same as no expiration
			'media_type'			=> 'video,audio',
			'custom_used_by'		=> 'ziggeoaudiorecorder'
		),
		'video-profile' => array(
			'type'					=> 'string',
			'description'			=> __('String value holding key or token of your video profile that you want to use.', 'ziggeo'),
			'used_by_player'		=> true,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'used_by_uploader'		=> true,
			'default_value'			=> '',
			'media_type'			=> 'video'
		),
		'meta-profile' => array(
			'type'					=> 'string',
			'description'			=> __('String value holding key or token of your meta profile that you want to use.', 'ziggeo'),
			'used_by_player'		=> true,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'used_by_uploader'		=> true,
			'default_value'			=> '',
			'media_type'			=> 'video,audio',
			'custom_used_by'		=> 'ziggeoaudiorecorder'
		),
		'stream-width' => array(
			'type'					=> 'integer',
			'description'			=> __('Integer value setting the optimal width of the stream.', 'ziggeo'),
			'used_by_player'		=> true,
			'used_by_recorder'		=> false,
			'used_by_rerecorder'	=> false,
			'used_by_uploader'		=> false,
			'default_value'			=> '',
			'media_type'			=> 'video'
		),
		'stream-height' => array(
			'type'					=> 'integer',
			'description'			=> __('Integer value setting the optimal height of the stream.', 'ziggeo'),
			'used_by_player'		=> true,
			'used_by_recorder'		=> false,
			'used_by_rerecorder'	=> false,
			'used_by_uploader'		=> false,
			'default_value'			=> '',
			'media_type'			=> 'video'
		),
		'title' => array(
			'type'					=> 'string',
			'description'			=> __('String value to set title of the video being recorded.', 'ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'used_by_uploader'		=> true,
			'default_value'			=> '',
			'media_type'			=> 'video,audio',
			'custom_used_by'		=> 'ziggeoaudiorecorder'
		),
		'description' => array(
			'type'					=> 'string',
			'description'			=> __('String value to set the description of the video.', 'ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'used_by_uploader'		=> true,
			'default_value'			=> '',
			'media_type'			=> 'video,audio',
			'custom_used_by'		=> 'ziggeoaudiorecorder'
		),
		'allowedextensions' => array(
			'type'					=> 'string',
			'description'			=> __('String value to limit the uploads to only specific extensions (all allowed by default).', 'ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'used_by_uploader'		=> true,
			'default_value'			=> '',
			'media_type'			=> 'video,audio',
			'custom_used_by'		=> 'ziggeoaudiorecorder'
		),
		'enforce-duration' => array(
			'type'					=> 'bool',
			'description'			=> __('Boolean value to reject videos if they are too long.', 'ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'used_by_uploader'		=> true,
			'default_value'			=> false,
			'media_type'			=> 'video,audio',
			'custom_used_by'		=> 'ziggeoaudiorecorder'
		),
		'filesizelimit' => array(
			'type'					=> 'integer',
			'description'			=> __('Integer value to limit the size of videos being uploaded in bytes (no limit by default).', 'ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'used_by_uploader'		=> true,
			'default_value'			=> 0, //no limit
			'media_type'			=> 'video,audio',
			'custom_used_by'		=> 'ziggeoaudiorecorder'
		),
		'framerate-warning' => array(
			'type'					=> 'bool',
			'description'			=> __('Boolean value to set a warning to be shown if framerate is too low.', 'ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'used_by_uploader'		=> false,
			'default_value'			=> false,
			'media_type'			=> 'video'
		),
		'nofullscreen' => array(
			'type'					=> 'bool',
			'description'			=> __('Boolean value to disable fullscreen option in player.', 'ziggeo'),
			'used_by_player'		=> true,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'used_by_uploader'		=> false,
			'default_value'			=> false,
			'media_type'			=> 'video'
		),
		/* // Deprecated, available only in older revisions you should upgrade from
		'stretch' => array(
			'type'					=> 'bool',
			'description'			=> __('Boolean value to set the player to play video in full width (regardless if hight gets cut or not).', 'ziggeo'),
			'used_by_player'		=> true,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'used_by_uploader'		=> false,
			'default_value'			=> false
		),*/
		'theme' => array(
			'type'					=> 'string',
			'description'			=> __('String value of the name of the theme that you wish to have applied to your player.', 'ziggeo'),
			'used_by_player'		=> true,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'used_by_uploader'		=> true,
			'default_value'			=> 'modern',
			'media_type'			=> 'video,audio',
			'custom_used_by'		=> array('ziggeoaudiorecorder', 'ziggeoaudioplayer')
		),
		'audiobitrate' => array(
			'type'					=> 'string',
			'description'			=> __('String value of the name of the theme that you wish to have applied to your player.', 'ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'used_by_uploader'		=> true,
			'default_value'			=> '',
			'media_type'			=> 'video,audio',
			'custom_used_by'		=> 'ziggeoaudiorecorder'
		),

		// Audio parameters
		'visualeffectheight' => array(
			'type'					=> 'integer',
			'description'			=> __('Height of the visual effects in player and recorder.', 'ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> false,
			'used_by_rerecorder'	=> false,
			'used_by_uploader'		=> false,
			'default_value'			=> '',
			'media_type'			=> 'audio',
			'custom_used_by'		=> array('ziggeoaudiorecorder', 'ziggeoaudioplayer')
		),
		'visualeffectminheight' => array(
			'type'					=> 'integer',
			'description'			=> __('Minimal height of the visual effects', 'ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> false,
			'used_by_rerecorder'	=> false,
			'used_by_uploader'		=> false,
			'default_value'			=> 120,
			'media_type'			=> 'audio',
			'custom_used_by'		=> array('ziggeoaudiorecorder', 'ziggeoaudioplayer')
		),
		'visualeffecttheme' => array(
			'type'					=> 'string',
			'description'			=> __('A theme for visual effects. Choose the visual effect of your preference. It can be `balloon` or `red-bars`.', 'ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> false,
			'used_by_rerecorder'	=> false,
			'used_by_uploader'		=> false,
			'default_value'			=> 'red-bars',
			'media_type'			=> 'audio',
			'custom_used_by'		=> array('ziggeoaudiorecorder', 'ziggeoaudioplayer')
		),
		'visualeffectvisible' => array(
			'type'					=> 'bool',
			'description'			=> __('String value of the name of the theme that you wish to have applied to your player.', 'ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> false,
			'used_by_rerecorder'	=> false,
			'used_by_uploader'		=> false,
			'default_value'			=> false,
			'media_type'			=> 'audio',
			'custom_used_by'		=> array('ziggeoaudiorecorder', 'ziggeoaudioplayer')
		),
		'audio' => array(
			'type'					=> 'string',
			'description'			=> __('String value of the name of the theme that you wish to have applied to your player.', 'ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> false,
			'used_by_rerecorder'	=> false,
			'used_by_uploader'		=> false,
			'default_value'			=> '',
			'media_type'			=> 'audio',
			'custom_used_by'		=> 'ziggeoaudioplayer'
		)
	);

	//hook to easily add your own parameter to list if you wanted..

	$parameters_list = apply_filters('ziggeo_template_parameters_list', array('system' => $system_parameters));

	return $parameters_list;
}

?>