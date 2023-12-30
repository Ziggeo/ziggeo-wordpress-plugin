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
					<div class="<?php echo $default_class; ?>"
					     data-type="<?php echo $params['type']; ?>"
					     data-options="<?php echo join(',', $params['options']); ?>"><?php echo $field; ?></div>
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

		'addstreamminheight' => array(
			'type'					=> 'integer',
			'description'			=> __('Specify the minimal height of the additional stream embedding.', 'ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'used_by_uploader'		=> false,
			'default_value'			=> 95,
			'media_type'			=> 'video'
		),
		'addstreamminwidth' => array(
			'type'					=> 'integer',
			'description'			=> __('Specify the minimal width of the additional stream embedding.', 'ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'used_by_uploader'		=> false,
			'default_value'			=> 120,
			'media_type'			=> 'video'
		),
		'addstreampositionheight' => array(
			'type'					=> 'integer',
			'description'			=> __('Specify the height of the additional stream embedding.', 'ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'used_by_uploader'		=> false,
			'default_value'			=> 95,
			'media_type'			=> 'video'
		),
		'addstreampositionwidth' => array(
			'type'					=> 'integer',
			'description'			=> __('Specify the width of the additional stream embedding.', 'ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'used_by_uploader'		=> false,
			'default_value'			=> 120,
			'media_type'			=> 'video'
		),
		'addstreampositionx' => array(
			'type'					=> 'integer',
			'description'			=> __('Specify the position where the additional stream should be embedded at on the x-axis', 'ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'used_by_uploader'		=> false,
			'default_value'			=> 5,
			'media_type'			=> 'video'
		),
		'addstreampositiony' => array(
			'type'					=> 'integer',
			'description'			=> __('Specify the position where the additional stream should be embedded at on the x-axis', 'ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'used_by_uploader'		=> false,
			'default_value'			=> 5,
			'media_type'			=> 'video'
		),
		'addstreamproportional' => array(
			'type'					=> 'bool',
			'description'			=> __('Keep the aspect ratio of the additional stream. Best to set height or with and use this parameter for the other one.', 'ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'used_by_uploader'		=> false,
			'default_value'			=> true,
			'media_type'			=> 'video'
		),
		'airplay' => array(
			'type'					=> 'bool',
			'description'			=> __('When set to true allows Airplay to be used', 'ziggeo'),
			'used_by_player'		=> true,
			'used_by_recorder'		=> false,
			'used_by_rerecorder'	=> false,
			'used_by_uploader'		=> false,
			'default_value'			=> '',
			'media_type'			=> 'video'
		),
		'allowcancel' => array(
			'type'					=> 'bool',
			'description'			=> __('Allows user to cancel the upload of a video.', 'ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'used_by_uploader'		=> true,
			'default_value'			=> false,
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
		'allowmultistreams' => array(
			'type'					=> 'bool',
			'description'			=> __('Allows user to use multiple streams in their recording (like screen and video)', 'ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'used_by_uploader'		=> false,
			'default_value'			=> false,
			'media_type'			=> 'video'
		),
		'allowpip' => array(
			'type'					=> 'bool',
			'description'			=> __('Allow user to activate picture-in-picture playback', 'ziggeo'),
			'used_by_player'		=> true,
			'used_by_recorder'		=> false,
			'used_by_rerecorder'	=> false,
			'used_by_uploader'		=> false,
			'default_value'			=> false,
			'media_type'			=> 'video,audio',
			'custom_used_by'		=> 'ziggeoaudioplayer'
		),
		'allowrecord' => array(
			'type'					=> 'bool',
			'description'			=> __('Allow recording to be made with your customer\'s web cam through the embedding', 'ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'used_by_uploader'		=> true,
			'default_value'			=> true,
			'media_type'			=> 'video,audio',
			'custom_used_by'		=> 'ziggeoaudiorecorder'
		),
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
		'allowtexttrackupload' => array(
			'type'					=> 'bool',
			'description'			=> __('Allow users to upload subtitles', 'ziggeo'),
			'used_by_player'		=> true,
			'used_by_recorder'		=> false,
			'used_by_rerecorder'	=> false,
			'used_by_uploader'		=> false,
			'default_value'			=> false,
			'media_type'			=> 'video,audio',
			'custom_used_by'		=> 'ziggeoaudioplayer'
		),
		'allowtrim' => array(
			'type'					=> 'bool',
			'description'			=> __('Allow user to trim time from the start and/or end of video after recording. Trimming will be skipped if the video format is not supported by the browser.', 'ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'used_by_uploader'		=> false,
			'default_value'			=> false,
			'media_type'			=> 'video'
		),
		'allowupload' => array(
			'type'					=> 'bool',
			'description'			=> __('Allow uploading of custom user videos through your embedding.', 'ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'used_by_uploader'		=> true,
			'default_value'			=> true,
			'media_type'			=> 'video,audio',
			'custom_used_by'		=> 'ziggeoaudiorecorder'
		),
		'application' => array(
			'type'					=> 'string',
			'description'			=> __('Lets you use one general application token on your page as well as another token for this specific embedding.', 'ziggeo'),
			'used_by_player'		=> true,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'used_by_uploader'		=> true,
			'default_value'			=> '',
			'media_type'			=> 'video,audio',
			'custom_used_by'		=> array('ziggeoaudiorecorder', 'ziggeoaudioplayer')
		),
		'aspectratio' => array(
			'type'					=> 'float',
			'description'			=> __('Useful to be able to keep specific aspect ratio of your embedding and specify only width or height.', 'ziggeo'),
			'used_by_player'		=> true,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'used_by_uploader'		=> false,
			'default_value'			=> 1.33,
			'media_type'			=> 'video'
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
		),
		'audiobitrate' => array(
			'type'					=> 'integer',
			'description'			=> __('Overwrite the automatic audio bitrate in kbs', 'ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'used_by_uploader'		=> true,
			'default_value'			=> '',
			'media_type'			=> 'video,audio',
			'custom_used_by'		=> 'ziggeoaudiorecorder'
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
		'audio-transcription-as-subtitles' => array(
			'type'					=> 'bool',
			'description'			=> __('Show audio transcription as subtitles (if available)', 'ziggeo'),
			'used_by_player'		=> true,
			'used_by_recorder'		=> false,
			'used_by_rerecorder'	=> false,
			'used_by_uploader'		=> false,
			'default_value'			=> false,
			'media_type'			=> 'video,audio',
			'custom_used_by'		=> 'ziggeoaudioplayer'
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
		'autorecord' => array(
			'type'					=> 'bool',
			'description'			=> __('(try to) start the recording as soon as the embedding is shown', 'ziggeo'),
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

		'cache' => array(
			'type'					=> 'bool',
			'description'			=> __('The cached media can then be accessed from other embeds on the same page using the use-cache parameter', 'ziggeo'),
			'used_by_player'		=> true,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'used_by_uploader'		=> false,
			'default_value'			=> false,
			'media_type'			=> 'video'
		),
		'camerafacefront' => array(
			'type'					=> 'bool',
			'description'			=> __('Should the front face camera be used', 'ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'used_by_uploader'		=> false,
			'default_value'			=> false,
			'media_type'			=> 'video'
		),
		'chromecast' => array(
			'type'					=> 'bool',
			'description'			=> __('Allow Chromecast', 'ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'used_by_uploader'		=> false,
			'default_value'			=> false,
			'media_type'			=> 'video'
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
		'cpu-friendly' => array(
			'type'					=> 'bool',
			'description'			=> __('When set as true it will attempt to use the less CPU intensive encoders, which might increase size or sometimes reduce quality', 'ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'used_by_uploader'		=> false,
			'default_value'			=> false,
			'media_type'			=> 'video'
		),
		'custom-covershots' => array(
			'type'					=> 'bool',
			'description'			=> __('Allow user to upload any image to use as covershot.', 'ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'used_by_uploader'		=> true,
			'default_value'			=> false,
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

		'default-image-selector' => array(
			'type'					=> 'float',
			'description'			=> __('Specify in percentages at which point cover shot should be taken from video. 0 indicates the very start; 0.5 indicates the middle of the video; 1 indicates the end of the video.', 'ziggeo'),
			'used_by_player'		=> true,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'used_by_uploader'		=> true,
			'default_value'			=> 0.1,
			'media_type'			=> 'video'
		),
		'default-fallback' => array(
			'type'					=> 'bool',
			'description'			=> __('If you are using video with effect player set and the stream with the effect profile does not exist this will make it show default stream instead of showing error.', 'ziggeo'),
			'used_by_player'		=> true,
			'used_by_recorder'		=> false,
			'used_by_rerecorder'	=> false,
			'used_by_uploader'		=> false,
			'default_value'			=> false,
			'media_type'			=> 'video'
		),
		'delete-old-streams' => array(
			'type'					=> 'bool',
			'description'			=> __('Delete old streams after re-recording', 'ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'used_by_uploader'		=> true,
			'default_value'			=> false,
			'media_type'			=> 'video'
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
		'disablepause' => array(
			'type'					=> 'bool',
			'description'			=> __('Disables pausing in the player', 'ziggeo'),
			'used_by_player'		=> true,
			'used_by_recorder'		=> false,
			'used_by_rerecorder'	=> false,
			'used_by_uploader'		=> false,
			'default_value'			=> false,
			'media_type'			=> 'video,audio',
			'custom_used_by'		=> 'ziggeoaudioplayer'
		),
		'disableseeking' => array(
			'type'					=> 'bool',
			'description'			=> __('Disables seeking in the player', 'ziggeo'),
			'used_by_player'		=> true,
			'used_by_recorder'		=> false,
			'used_by_rerecorder'	=> false,
			'used_by_uploader'		=> false,
			'default_value'			=> false,
			'media_type'			=> 'video,audio',
			'custom_used_by'		=> 'ziggeoaudioplayer'
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
		'fittodimensions' => array(
			'type'					=> 'bool',
			'description'			=> __('Fix precisely to recordingwidth and recordingheight even if not supported natively', 'ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'used_by_uploader'		=> false,
			'default_value'			=> false,
			'media_type'			=> 'video'
		),
		'flipscreen' => array(
			'type'					=> 'bool',
			'description'			=> __('Toggle screen flipping when you are doing a screen-recording', 'ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'used_by_uploader'		=> false,
			'default_value'			=> false,
			'media_type'			=> 'video'
		),
		'flip-camera' => array(
			'type'					=> 'bool',
			'description'			=> __('This enables a mirrored screen which allows for natural orientation during recording.  Note: videos are saved in original orientation for easy review.', 'ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'used_by_uploader'		=> false,
			'default_value'			=> false,
			'media_type'			=> 'video'
		),
		'force-overwrite' => array(
			'type'					=> 'bool',
			'description'			=> __('If the video with same key already exist, you can still overwrite it by specifying this parameter. It allows you to avoid accidental overwrites and have a better control of what happens with your videos. Only needed with use of keys during recording.', 'ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'used_by_uploader'		=> true,
			'default_value'			=> false,
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
		'framerate' => array(
			'type'					=> 'integer',
			'description'			=> __('Set the number of frames that should be captured in a single second.', 'ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'used_by_uploader'		=> true,
			'default_value'			=> 25,
			'media_type'			=> 'video'
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
		'fullscreenmandatory' => array(
			'type'					=> 'bool',
			'description'			=> __('Allow fullscreen on devices where custom controls are not supported', 'ziggeo'),
			'used_by_player'		=> true,
			'used_by_recorder'		=> false,
			'used_by_rerecorder'	=> false,
			'used_by_uploader'		=> false,
			'default_value'			=> false,
			'media_type'			=> 'video'
		),

		'gallerysnapshots' => array(
			'type'					=> 'integer',
			'description'			=> __('Maximum number of snapshots shown', 'ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'used_by_uploader'		=> true,
			'default_value'			=> 3,
			'media_type'			=> 'video'
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
		'hidebarafter' => array(
			'type'					=> 'integer',
			'description'			=> __('Tells the embedding how long to wait before hiding the controls', 'ziggeo'),
			'used_by_player'		=> true,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'used_by_uploader'		=> true,
			'default_value'			=> 500,
			'media_type'			=> 'video,audio',
			'custom_used_by'		=> array('ziggeoaudiorecorder', 'ziggeoaudioplayer')
		),
		'hideoninactivity' => array(
			'type'					=> 'bool',
			'description'			=> __('Hides the controls of the embedding if the activity was not present for a specific amount of time. This includes the activity from mouse as well as keyboard. Contorls are shown again on activity', 'ziggeo'),
			'used_by_player'		=> true,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'used_by_uploader'		=> true,
			'default_value'			=> true,
			'media_type'			=> 'video,audio',
			'custom_used_by'		=> array('ziggeoaudiorecorder', 'ziggeoaudioplayer')
		),
		'hidevolumebar' => array(
			'type'					=> 'bool',
			'description'			=> __('Hides volume bar permanently', 'ziggeo'),
			'used_by_player'		=> true,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'used_by_uploader'		=> true,
			'default_value'			=> true,
			'media_type'			=> 'video,audio',
			'custom_used_by'		=> array('ziggeoaudiorecorder', 'ziggeoaudioplayer')
		),

		'initialseek' => array(
			'type'					=> 'integer',
			'description'			=> __('When starting playback do initial seek to a position in the video', 'ziggeo'),
			'used_by_player'		=> true,
			'used_by_recorder'		=> false,
			'used_by_rerecorder'	=> false,
			'used_by_uploader'		=> false,
			'default_value'			=> 0,
			'media_type'			=> 'video,audio',
			'custom_used_by'		=> 'ziggeoaudioplayer'
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

		'lazy-application' => array(
			'type'					=> 'bool',
			'description'			=> __('Allow an application to be defined after the embeddings are initialized. In most cases not needed with the plugin 3.0 and up.', 'ziggeo'),
			'used_by_player'		=> true,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'used_by_uploader'		=> true,
			'v1_only'				=> true,
			'default_value'			=> '',
			'media_type'			=> 'video,audio',
			'custom_used_by'		=> array('ziggeoaudioplayer', 'ziggeoaudiorecorder')
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
		'loopall' => array(
			'type'					=> 'bool',
			'description'			=> __('Loop video playlist indefinitely', 'ziggeo'),
			'used_by_player'		=> true,
			'used_by_recorder'		=> false,
			'used_by_rerecorder'	=> false,
			'used_by_uploader'		=> false,
			'default_value'			=> false,
			'media_type'			=> 'video,audio',
			'custom_used_by'		=> 'ziggeoaudioplayer'
		),

		/*
		// This one requires custom code so not the best choice for WP.
		// It is still possible to add it to the template, this just removes it from autocomplete
		'manual-upload' => array(
			'type'					=> 'bool',
			'description'			=> __('This will cause your embedding to allow file to be selected, however it will not start the upload itself. To start the upload, call the `embedding.upload();` method', 'ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'used_by_uploader'		=> true,
			'default_value'			=> false,
			'media_type'			=> 'video'
		),*/
		'manualsubmit' => array(
			'type'					=> 'bool',
			'description'			=> __('Provide the user an option to confirm the submission of their video by clicking a button.', 'ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'used_by_uploader'		=> true,
			'default_value'			=> false,
			'media_type'			=> 'video'
		),
		'maxheight' => array(
			'type'					=> 'integer',
			'description'			=> __('Maximum height of the embedding (in pixels)', 'ziggeo'),
			'used_by_player'		=> true,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'used_by_uploader'		=> true,
			'default_value'			=> '',
			'media_type'			=> 'video'
		),
		'maxuploadingheight' => array(
			'type'					=> 'integer',
			'description'			=> __('Maximal allowed height of uploaded video', 'ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'used_by_uploader'		=> true,
			'default_value'			=> 0,
			'media_type'			=> 'video'
		),
		'maxuploadingwidth' => array(
			'type'					=> 'integer',
			'description'			=> __('Maximal allowed width of uploaded video', 'ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'used_by_uploader'		=> true,
			'default_value'			=> 0,
			'media_type'			=> 'video'
		),
		'maxwidth' => array(
			'type'					=> 'integer',
			'description'			=> __('Maximum width of the embedding (in pixels)', 'ziggeo'),
			'used_by_player'		=> true,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'used_by_uploader'		=> true,
			'default_value'			=> '',
			'media_type'			=> 'video'
		),
		'media-orientation' => array(
			'type'					=> 'string',
			'description'			=> __('Will allow record only provided option landscape or portrait mode', 'ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'used_by_uploader'		=> false,
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
		'microphone-volume' => array(
			'type'					=> 'float',
			'description'			=> __('Microphone volume gain - from 0 to 1', 'ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'used_by_uploader'		=> false,
			'default_value'			=> 1,
			'media_type'			=> 'video'
		),
		'minheight' => array(
			'type'					=> 'integer',
			'description'			=> __('Minimum height of the embedding (in pixels)', 'ziggeo'),
			'used_by_player'		=> true,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'used_by_uploader'		=> true,
			'default_value'			=> 240,
			'media_type'			=> 'video'
		),
		'minuploadingheight' => array(
			'type'					=> 'integer',
			'description'			=> __('Minimal allowed height of uploaded video', 'ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'used_by_uploader'		=> true,
			'default_value'			=> 0,
			'media_type'			=> 'video'
		),
		'minuploadingwidth' => array(
			'type'					=> 'integer',
			'description'			=> __('Minimal allowed width of uploaded video', 'ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'used_by_uploader'		=> true,
			'default_value'			=> 0,
			'media_type'			=> 'video'
		),
		'minwidth' => array(
			'type'					=> 'integer',
			'description'			=> __('Minimum width of the embedding (in pixels)', 'ziggeo'),
			'used_by_player'		=> true,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'used_by_uploader'		=> true,
			'default_value'			=> 320,
			'media_type'			=> 'video'
		),
		'multistreamdraggable' => array(
			'type'					=> 'bool',
			'description'			=> __('Allow dragging of stream-in-stream', 'ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'used_by_uploader'		=> false,
			'default_value'			=> false,
			'media_type'			=> 'video'
		),
		'multistreamresizeable' => array(
			'type'					=> 'bool',
			'description'			=> __('Allow resizing of stream-in-stream', 'ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'used_by_uploader'		=> false,
			'default_value'			=> false,
			'media_type'			=> 'video'
		),
		'multistreamreversable' => array(
			'type'					=> 'bool',
			'description'			=> __('Allow reversability of stream-in-stream', 'ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'used_by_uploader'		=> false,
			'default_value'			=> false,
			'media_type'			=> 'video'
		),

		'noaudio' => array(
			'type'					=> 'bool',
			'description'			=> __('Set recorder to not record any sound.', 'ziggeo'),
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

		'orientation' => array(
			'type'					=> 'string',
			'description'			=> __('Force particular device orientation on mobile (`portrait` or `landscape`)', 'ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'used_by_uploader'		=> false,
			'default_value'			=> '',
			'media_type'			=> 'video'
		),
		// outsource-selectors - can be used, however for WP it requires additional custom codes so it is not
		// part of the autocomplete

		'pausable' => array(
			'type'					=> 'bool',
			'description'			=> __('Allow user to pause the recording', 'ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'used_by_uploader'		=> false,
			'default_value'			=> false,
			'media_type'			=> 'video,audio',
			'custom_used_by'		=> 'ziggeoaudiorecorder'
		),
		'pauseonclick' => array(
			'type'					=> 'bool',
			'description'			=> __('Pause video when user clicks on player if the video is playing', 'ziggeo'),
			'used_by_player'		=> true,
			'used_by_recorder'		=> false,
			'used_by_rerecorder'	=> false,
			'used_by_uploader'		=> false,
			'default_value'			=> false,
			'media_type'			=> 'video,audio',
			'custom_used_by'		=> 'ziggeoaudioplayer'
		),
		'pauseonplay' => array(
			'type'					=> 'bool',
			'description'			=> __('Pause playback when another video is being played back', 'ziggeo'),
			'used_by_player'		=> true,
			'used_by_recorder'		=> false,
			'used_by_rerecorder'	=> false,
			'used_by_uploader'		=> false,
			'default_value'			=> false,
			'media_type'			=> 'video,audio',
			'custom_used_by'		=> 'ziggeoaudioplayer'
		),
		'playonclick' => array(
			'type'					=> 'bool',
			'description'			=> __('Play video when user clicks on player if the video is paused', 'ziggeo'),
			'used_by_player'		=> true,
			'used_by_recorder'		=> false,
			'used_by_rerecorder'	=> false,
			'used_by_uploader'		=> false,
			'default_value'			=> false,
			'media_type'			=> 'video,audio',
			'custom_used_by'		=> 'ziggeoaudioplayer'
		),
		'playwhenvisible' => array(
			'type'					=> 'bool',
			'description'			=> __('Automatically play video once it becomes visible', 'ziggeo'),
			'used_by_player'		=> true,
			'used_by_recorder'		=> false,
			'used_by_rerecorder'	=> false,
			'used_by_uploader'		=> false,
			'default_value'			=> false,
			'media_type'			=> 'video,audio',
			'custom_used_by'		=> 'ziggeoaudioplayer'
		),
		'pickcovershotframe' => array(
			'type'					=> 'bool',
			'description'			=> __('ELet user pick any frame from video to use as covershot, as opposed to selecting from snapshots gallery', 'ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'used_by_uploader'		=> false,
			'default_value'			=> false,
			'media_type'			=> 'video'
		),
		'picksnapshotmandatory' => array(
			'type'					=> 'bool',
			'description'			=> __('User has to pick snapshot', 'ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'used_by_uploader'		=> false,
			'default_value'			=> false,
			'media_type'			=> 'video'
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
		'playermodeifexists' => array(
			'type'					=> 'bool',
			'description'			=> __('Run recorder in player mode if video already exists', 'ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> false,
			'used_by_uploader'		=> false,
			'default_value'			=> false,
			'media_type'			=> 'video'
		),
		'playfullscreenonmobile' => array(
			'type'					=> 'bool',
			'description'			=> __('Automatically play fullscreen on mobile devices', 'ziggeo'),
			'used_by_player'		=> true,
			'used_by_recorder'		=> false,
			'used_by_rerecorder'	=> false,
			'used_by_uploader'		=> false,
			'default_value'			=> false,
			'media_type'			=> 'video'
		),
		'playlist' => array(
			'type'					=> 'array',
			'description'			=> __('Allows you to add multiple video tokens which are turned into playlist where one video plays after another automatically', 'ziggeo'),
			'used_by_player'		=> true,
			'used_by_recorder'		=> false,
			'used_by_rerecorder'	=> false,
			'used_by_uploader'		=> false,
			'default_value'			=> '',
			'media_type'			=> 'video,audio',
			'custom_used_by'		=> 'ziggeoaudioplayer'
		),
		'popup' => array(
			'type'					=> 'bool', //data-equal=""
			'description'			=> __('Boolean value that says if this is popup or standard embedding.', 'ziggeo'),
			'used_by_player'		=> true,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'used_by_uploader'		=> true,
			'default_value'			=> false,
			'media_type'			=> 'video,audio',
			'custom_used_by'		=> 'ziggeoaudiorecorder'
		),
		'popup-height' => array(
			'type'					=> 'integer', //data-equal="="
			'description'			=> __('Integer value setting up the height of the popup holding the embedding.', 'ziggeo'),
			'used_by_player'		=> true,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'used_by_uploader'		=> true,
			'default_value'			=> '',
			'media_type'			=> 'video'
		),
		'popup-stretch' => array(
			'type'					=> 'bool',
			'description'			=> __('Allow the popup player to stretch and fill out the full width of the area that it has been given.', 'ziggeo'),
			'used_by_player'		=> true,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'used_by_uploader'		=> true,
			'default_value'			=> false,
			'media_type'			=> 'video'
		),
		'popup-width' => array(
			'type'					=> 'integer',
			'description'			=> __('Integer value setting up the width of the popup holding the embedding.', 'ziggeo'),
			'used_by_player'		=> true,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'used_by_uploader'		=> true,
			'default_value'			=> '',
			'media_type'			=> 'video'
		),
		'poster' => array(
			'type'					=> 'string',
			'description'			=> __('The image that is pointed to will be shown with the play icon when embedding loads and before the video is played. This will usually be using image from our server, however you can override it using this parameter and show your own', 'ziggeo'),
			'used_by_player'		=> true,
			'used_by_recorder'		=> false,
			'used_by_rerecorder'	=> false,
			'used_by_uploader'		=> false,
			'default_value'			=> '',
			'media_type'			=> 'video'
		),
		'posterfitstrategy' => array(
			'type'					=> 'string',
			'description'			=> __('Define how poster image should fit inside container. Can be `crop`, `pad` or `original`', 'ziggeo'),
			'used_by_player'		=> true,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'used_by_uploader'		=> true,
			'default_value'			=> 'crop',
			'media_type'			=> 'video'
		),
		'preload' => array(
			'type'					=> 'bool',
			'description'			=> __('Instead of loading the media such as video on click, you can start loading it as soon as page opens. This way your video playback, especially of higher resolutions or longer times begins instantly. At the moment it requires `skipinitial` parameter as well.', 'ziggeo'),
			'used_by_player'		=> true,
			'used_by_recorder'		=> false,
			'used_by_rerecorder'	=> false,
			'used_by_uploader'		=> false,
			'default_value'			=> false,
			'media_type'			=> 'video'
		),
		'preroll' => array(
			'type'					=> 'bool',
			'description'			=> __('Ad - Should ad be prerolled?. Requires VAST options to be set for your ad', 'ziggeo'),
			'used_by_player'		=> true,
			'used_by_recorder'		=> false,
			'used_by_rerecorder'	=> false,
			'used_by_uploader'		=> false,
			'default_value'			=> false,
			'media_type'			=> 'video'
		),
		'preventinteraction' => array(
			'type'					=> 'bool',
			'description'			=> __('Shows the player, however makes it impossible for someone to click to pause or seek through. Useful in workflows or environments where you want to play the video like in video theaters, background video setups, exams and alike', 'ziggeo'),
			'used_by_player'		=> true,
			'used_by_recorder'		=> false,
			'used_by_rerecorder'	=> false,
			'used_by_uploader'		=> false,
			'default_value'			=> false,
			'media_type'			=> 'video'
		),
		'preview-effect-profile' => array(
			'type'					=> 'string',
			'description'			=> __('If applied to recorder, it specifies the effect profile that should be played back in preview after recording', 'ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'used_by_uploader'		=> true,
			'default_value'			=> '',
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
		'rerecordable' => array(
			'type'					=> 'bool',
			'description'			=> __('Sets embedding in a special mode so re-recordings can be made. This is true by default for recorders/capture embeddings and by default false for players/viewer embeddings.', 'ziggeo'),
			'used_by_player'		=> true,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'used_by_uploader'		=> true,
			'default_value'			=> true,
			'media_type'			=> 'video,audio',
			'custom_used_by'		=> array('ziggeoaudioplayer', 'ziggeoaudiorecorder')
		),
		'rerecordableifexists' => array(
			'type'					=> 'bool',
			'description'			=> __('By default if you use a recorder that is set with rerecording options it will allow you to rerecord over it. If you set this parameter as false, it will not allow you to take a new video over existing one.', 'ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'used_by_uploader'		=> true,
			'default_value'			=> true,
			'media_type'			=> 'video,audio',
			'custom_used_by'		=> 'ziggeoaudiorecorder'
		),

		'screenrecordmandatory' => array(
			'type'					=> 'string',
			'description'			=> __('Make screen recording mandatory for the user.', 'ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'used_by_uploader'		=> false,
			'default_value'			=> false,
			'media_type'			=> 'video'
		),
		'selectfirstcovershotonskip' => array(
			'type'					=> 'bool',
			'description'			=> __('When the gallery of snapshots is shown and the person that had recorded the video clicks on skip button and this option is set as true, it will cause the very first frame to be used as cover image.', 'ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'used_by_uploader'		=> false,
			'default_value'			=> false,
			'media_type'			=> 'video'
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
		'sharevideo' => array(
			'type'					=> 'bool',
			'description'			=> __('Show share buttons for social networks. Supported values: `facebook`, `twitter`, `gplus`', 'ziggeo'),
			'used_by_player'		=> true,
			'used_by_recorder'		=> false,
			'used_by_rerecorder'	=> false,
			'used_by_uploader'		=> false,
			'default_value'			=> false,
			'media_type'			=> 'video'
		),
		'showaddstreambutton' => array(
			'type'					=> 'bool',
			'description'			=> __('Enable the end-user to select multiple streams in one video, e.g. screen recording and camera', 'ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'used_by_uploader'		=> false,
			'default_value'			=> false,
			'media_type'			=> 'video'
		),
		'showduration' => array(
			'type'					=> 'bool',
			'description'			=> __('Shows duration of the video before the playback starts. Instead of waiting for video to start, your video duration is shown on the image itself', 'ziggeo'),
			'used_by_player'		=> true,
			'used_by_recorder'		=> false,
			'used_by_rerecorder'	=> false,
			'used_by_uploader'		=> false,
			'default_value'			=> false,
			'media_type'			=> 'video'
		),
		'showplayersettingsmenu' => array(
			'type'					=> 'bool',
			'description'			=> __('As a property show/hide after recorder player settings from users', 'ziggeo'),
			'used_by_player'		=> true,
			'used_by_recorder'		=> false,
			'used_by_rerecorder'	=> false,
			'used_by_uploader'		=> false,
			'default_value'			=> true,
			'media_type'			=> 'video'
		),
		'showsettings' => array(
			'type'					=> 'bool',
			'description'			=> __('Allow user to change player settings, like playback speed', 'ziggeo'),
			'used_by_player'		=> true,
			'used_by_recorder'		=> false,
			'used_by_rerecorder'	=> false,
			'used_by_uploader'		=> false,
			'default_value'			=> true,
			'media_type'			=> 'video'
		),
		'showsettingsmenu' => array(
			'type'					=> 'bool',
			'description'			=> __('As a property show/hide settings from users', 'ziggeo'),
			'used_by_player'		=> true,
			'used_by_recorder'		=> false,
			'used_by_rerecorder'	=> false,
			'used_by_uploader'		=> false,
			'default_value'			=> true,
			'media_type'			=> 'video'
		),
		'simulate' => array(
			'type'					=> 'bool',
			'description'			=> __('Simulate recording experience without actually recording it', 'ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'used_by_uploader'		=> false,
			'default_value'			=> false,
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
		'skipinitialonrerecord' => array(
			'type'					=> 'bool',
			'description'			=> __('Disable initial screen of recorder when re-recording', 'ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'used_by_uploader'		=> false,
			'default_value'			=> false,
			'media_type'			=> 'video,audio',
			'custom_used_by'		=> 'ziggeoaudiorecorder'
		),
		'skipseconds' => array(
			'type'					=> 'integer',
			'description'			=> __('This parameter allows you to set up how many seconds you want to use for the keyboard left and keyboard right keys to skip the video. This operates like seek(current playback time + time you set to skip)', 'ziggeo'),
			'used_by_player'		=> true,
			'used_by_recorder'		=> false,
			'used_by_rerecorder'	=> false,
			'used_by_uploader'		=> false,
			'default_value'			=> 5,
			'media_type'			=> 'video,audio',
			'custom_used_by'		=> 'ziggeoaudioplayer'
		),
		'snapshotfrommobilecapture' => array(
			'type'					=> 'bool',
			'description'			=> __('Let user pick a snapshot from mobile video capture', 'ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'used_by_uploader'		=> true,
			'default_value'			=> false,
			'media_type'			=> 'video'
		),
		'snapshotfromuploader' => array(
			'type'					=> 'bool',
			'description'			=> __('Let user pick a snapshot from uploaded video files', 'ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'used_by_uploader'		=> true,
			'default_value'			=> false,
			'media_type'			=> 'video'
		),
		'snapshotmax' => array(
			'type'					=> 'integer',
			'description'			=> __('Maximum number of snapshots of the recorded video to be collected', 'ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'used_by_uploader'		=> true,
			'default_value'			=> 15,
			'media_type'			=> 'video'
		),
		'source' => array(
			'type'					=> 'string',
			'description'			=> __('URL to your own media that you want to show', 'ziggeo'),
			'used_by_player'		=> true,
			'used_by_recorder'		=> false,
			'used_by_rerecorder'	=> false,
			'used_by_uploader'		=> false,
			'default_value'			=> '',
			'media_type'			=> 'video,audio',
			'custom_used_by'		=> 'ziggeoaudioplayer'
		),
		'sticky' => array(
			'type'					=> 'bool',
			'description'			=> __('The sticky video can be moved around by the user so it doesn\'t block content from the page.', 'ziggeo'),
			'used_by_player'		=> true,
			'used_by_recorder'		=> false,
			'used_by_rerecorder'	=> false,
			'used_by_uploader'		=> false,
			'default_value'			=> false,
			'media_type'			=> 'video,audio',
			'custom_used_by'		=> 'ziggeoaudioplayer'
		),
		'stream' => array(
			'type'					=> 'string',
			'description'			=> __('String representing stream token or stream key.', 'ziggeo'),
			'used_by_player'		=> true,
			'used_by_recorder'		=> false,
			'used_by_rerecorder'	=> false,
			'used_by_uploader'		=> false,
			'default_value'			=> '',
			'media_type'			=> 'video,audio',
			'custom_used_by'		=> 'ziggeoaudioplayer'
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
		'timeminlimit' => array(
			'type'					=> 'integer',
			'description'			=> __('If video length should be at least some minimum amount of time, use this parameter. The value presented refers to number of seconds the video should last.', 'ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'used_by_uploader'		=> true,
			'default_value'			=> 0, //Equal to no min limit
			'media_type'			=> 'video,audio',
			'custom_used_by'		=> 'ziggeoaudiorecorder'
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
		'themecolor' => array(
			'type'					=> 'string',
			'description'			=> __('Name of the color to use. Can be red, blue or green', 'ziggeo'),
			'used_by_player'		=> true,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'used_by_uploader'		=> true,
			'default_value'			=> 'blue',
			'media_type'			=> 'video,audio',
			'custom_used_by'		=> array('ziggeoaudiorecorder', 'ziggeoaudioplayer')
		),
		'tracktags' => array(
			'type'					=> 'json',
			'description'			=> __('Set the player to show subtitles from your own servers. Accepts .vtt subtitle file format and it is an array of objects. objects are made out of different parameters you need to pass. Requires `lang`, `kind`, `label` and `src` attributes to be set', 'ziggeo'),
			'used_by_player'		=> true,
			'used_by_recorder'		=> false,
			'used_by_rerecorder'	=> false,
			'used_by_uploader'		=> false,
			'default_value'			=> '',
			'media_type'			=> 'video,audio',
			'custom_used_by'		=> 'ziggeoaudioplayer'
		),
		'tracktagsstyled' => array(
			'type'					=> 'bool',
			'description'			=> __('Show subtitles in Ziggeo created style. If you set it to false every browser will have its own style shown which will create different UX, so we suggest to leave it on default.', 'ziggeo'),
			'used_by_player'		=> true,
			'used_by_recorder'		=> false,
			'used_by_rerecorder'	=> false,
			'used_by_uploader'		=> false,
			'default_value'			=> true,
			'media_type'			=> 'video,audio',
			'custom_used_by'		=> 'ziggeoaudioplayer'
		),
		'transcript-language' => array(
			'type'					=> 'string',
			'description'			=> __('Select language for audio transcription', 'ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'used_by_uploader'		=> true,
			'default_value'			=> 'en-US',
			'media_type'			=> 'video,audio',
			'custom_used_by'		=> 'ziggeoaudiorecorder'
		),
		'trimoverlay' => array(
			'type'					=> 'bool',
			'description'			=> __('Define if embedding should show trimming overlay or not when allowtrim is set to true.', 'ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'used_by_uploader'		=> true,
			'default_value'			=> true,
			'media_type'			=> 'video,audio',
			'custom_used_by'		=> 'ziggeoaudiorecorder'
		),

		'uploadlocales' => array(
			'type'					=> 'bool',
			'description'			=> __('This can be used to allow only specific translations to be uploaded. For example: `[{lang: \'en\', label: \'English\'}, {lang: \'de\', label: \'Deutsch\'}]` would allow English and Deutsch language files to be uploaded.', 'ziggeo'),
			'used_by_player'		=> true,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'used_by_uploader'		=> true,
			'default_value'			=> false,
			'media_type'			=> 'video'
		),
		'use-cache' => array(
			'type'					=> 'bool',
			'description'			=> __('If available, access media from local cache instead of servers. This is only valid for media cached using the cache parameter.', 'ziggeo'),
			'used_by_player'		=> true,
			'used_by_recorder'		=> false,
			'used_by_rerecorder'	=> false,
			'used_by_uploader'		=> false,
			'default_value'			=> false,
			'media_type'			=> 'video,audio',
			'custom_used_by'		=> array('ziggeoaudioplayer')
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
		'videobitrate' => array(
			'type'					=> 'integer',
			'description'			=> __('Overwrite the automatic video bitrate in kbs', 'ziggeo'),
			'used_by_player'		=> true,
			'used_by_recorder'		=> false,
			'used_by_rerecorder'	=> true,
			'used_by_uploader'		=> false,
			'default_value'			=> '',
			'media_type'			=> 'video'
		),
		'videofitstrategy' => array(
			'type'					=> 'string',
			'description'			=> __('Define how video should fit inside container. It can be `crop`, `pad` or `original` resolution', 'ziggeo'),
			'used_by_player'		=> true,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'used_by_uploader'		=> true,
			'default_value'			=> 'pad',
			'media_type'			=> 'video'
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
		'visibilityfraction' => array(
			'type'					=> 'integer',
			'description'			=> __('Define in percentage, what part of the player has to be visible player start automatically start to play video', 'ziggeo'),
			'used_by_player'		=> true,
			'used_by_recorder'		=> false,
			'used_by_rerecorder'	=> false,
			'used_by_uploader'		=> false,
			'default_value'			=> 0.8,
			'media_type'			=> 'video'
		),
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
		'volume' => array(
			'type'					=> 'float',
			'description'			=> __('You can use this to set the volume of your video when playback starts. With `0` as a value you would have muted video and with `1` you would have 100% sound volume.', 'ziggeo'),
			'used_by_player'		=> true,
			'used_by_recorder'		=> false,
			'used_by_rerecorder'	=> false,
			'used_by_uploader'		=> false,
			'default_value'			=> 1,
			'media_type'			=> 'audio',
			'custom_used_by'		=> 'ziggeoaudioplayer'
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
		)
	);

	//hook to easily add your own parameter to list if you wanted..

	$parameters_list = apply_filters('ziggeo_template_parameters_list', array('system' => $system_parameters));

	return $parameters_list;
}

?>