<?php

//This file has actual code that the plugin is using, however it is separated because it uses its own hooks to do the job, so it is a great source of examples.
//All of the examples are using anonymous functions to do something, however by replacing it with function name, you can create standard function and use it like that.
//The reason why anonymous functions are used here is because that way they are not polluting the namespace so it is easier to see all functions.


//Add support to handle management of the templates over AJAX
add_filter('ziggeo_ajax_call', function($rez, $operation) {

	if($operation === 'settings_manage_template') {
		if(isset($_POST['template_id'])) {

			$id = urldecode($_POST['template_id']);
			$code = (isset($_POST['template_code'])) ? urldecode($_POST['template_code']): '';
			$manager = (isset($_POST['manager'])) ? urldecode($_POST['manager']): '';

			$data = array(
				'templates_id'			=> $id,
				'templates_editor'		=> $code,
				'templates_manager'		=> $manager,
			);

			$rez = ziggeo_a_s_v_templates_handler($data);

			//$rez can be ('added', 'unchanged', 'updated', removed', false)
		}
		else {
			$rez = false;
		}
	}

	return $rez;
}, 10, 2);

//add support for finding "<ziggeo></ziggeo>" within content and pass it to right place.
// This does not handle the parameters, it is just a simple example
add_filter('ziggeo_content_filter_pre', function ($content) {

	$_t_pos_pre = stripos($content, '<ziggeo>');

	if($_t_pos_pre > -1) {
		//we have some embedding in here - v1 embedding..we do not change this one, we must change it to v2 since it is using v2-stable now..
		$pre_embedding = substr($content, 0, $_t_pos_pre);

		//Now we need to check if the last embedding code exists, lets not just assume it does..
		$_t_pos_post = stripos($content, '</ziggeo>');

		if($_t_pos_post > $_t_pos_pre) {
			$post_embedding = substr($content, $_t_pos_post + 9);
		}
		else {
			$post_embedding = '';
		}

		$content = $pre_embedding . '<ziggeorecorder></ziggeorecorder>' . $post_embedding;
	}

	return $content;
});


//add support for codes like the following using v1:
//<ziggeo ziggeo-width=320
//        ziggeo-height=240
//        ziggeo-popup
//        ziggeo-video="1234567890">
//</ziggeo>
add_filter('ziggeo_content_filter_pre', function ($content) {
	$_t_pos_pre = stripos($content, '<ziggeo ');

	if($_t_pos_pre > -1) {
		//we have some embedding in here - v1 embedding..we do not change this one, we must change it to v2 since it is using v2-stable now..
		$pre_embedding = substr($content, 0, $_t_pos_pre);

		//Now we need to check if the last embedding code exists, lets not just assume it does..
		$_t_pos_post = stripos($content, '</ziggeo>');

		$embedding_code = substr( $content, $_t_pos_pre, $_t_pos_post + 9);

		if($_t_pos_post > $_t_pos_pre) {
			$post_embedding = substr($content, $_t_pos_post + 9);
		}
		else {
			$post_embedding = '';
		}

		//cleanup
		$clean_code = str_ireplace('<ziggeo', '', $embedding_code);
		$clean_code = str_ireplace('</ziggeo', '', $clean_code);
		$clean_code = str_ireplace( array('<br >', '<br />', '<p>','</p', '<div>', '</div', '>'), '', $clean_code);

		//we want to pass the embedding code as original as is, however for the actual parameters that we might want to pass into
		// embedding, we do want to have clean code so that we do not pass <ziggeo and </ziggeo> as part of HTML attributes

		if( ziggeo_p_template_is_player($embedding_code) ) {
			$content = $pre_embedding . '<ziggeoplayer ' . ziggeo_template_v1_to_v2($clean_code) . '></ziggeoplayer>' . $post_embedding;
		}
		elseif( ziggeo_p_template_is_recorder($embedding_code) ) {
			$content = $pre_embedding . '<ziggeorecorder ' . ziggeo_template_v1_to_v2($clean_code) . '></ziggeorecorder>' . $post_embedding;
		}
		else {
			//fallback to?
			//v1 and v2 have a lot more options as recorders, so if it is not a video player, it is a recorder
			$content = $pre_embedding . '<ziggeorecorder ' . ziggeo_template_v1_to_v2($clean_code) . '></ziggeorecorder>' . $post_embedding;
		}

	}

	return $content;
});


//If the location embedding is in is part of the post body, lets not just add "post" as a tag, lets also add the post ID
add_filter('ziggeo_template_parsing_tag_set', function($locationTag, $filter) {

	if($locationTag === 'post' || $locationTag === 'comment') {
		$id = get_the_ID();

		$locationTag .= ',post_' . $id;

		return $locationTag;
	}
}, 10, 2);

//Custom tags support examples

//to change %PAGE_TITLE% into actual title of the current page
add_filter('ziggeo_content_filter_pre', function ($content) {
	$content = str_ireplace('%PAGE_TITLE%', get_the_title(), $content);

	return $content;
});

//to change the %CURRENT_ID% placeholder into the post ID
add_filter('ziggeo_content_filter_pre', function ($content) {

	global $wp_query;

	$post_ID = $wp_query->get_queried_object_id();

	$content = str_replace('%CURRENT_ID%', $post_ID, $content);	

	return $content;
});


//Add Record Video button
add_action('ziggeo_toolbar_button', function($ajax) {
	//Including the file with the script handling JS codes

	echo ziggeo_create_toolbar_button('insert-ziggeo-button', 'Record Video', 'video-alt');

	$current_user = ziggeo_p_get_current_user();

	// The template we would use when creating a recorder
	?>
		<script type="text/template" id="ziggeo-recorder-template">
			<div id="ziggeo-recorder">
				<ziggeorecoder
					ziggeo-width="480"
					ziggeo-height="360"
					ziggeo-timelimit="240"
					ziggeo-form-accept="#post"
					ziggeo-tags="wordpress,<?php echo $current_user->user_login; ?>,creatingPost"
				></ziggeorecorder>
			</div>
		</script>

		<?php if($ajax !== true) { ?>
			<script type="text/javascript">
				jQuery(document).on("ready", function () {
					ziggeoSetupOverlayRecorder();
				});
			</script>
		<?php } ?>
	<?php
});

//Add Insert Template button
add_action('ziggeo_toolbar_button', function($ajax) {
	echo ziggeo_create_toolbar_button('insert-ziggeo-template', 'Insert Template', 'media-code');

	//The template that holds all of the templates
	?>
		<script type="text/template" id="ziggeo-templates-list">
			<?php
				$templates = ziggeo_p_templates_index();
				?>
					<ol id="ziggeo-templates-list-insert">
				<?php
				if($templates) {
					foreach($templates as $template_name => $template_code) {
						?>
							<li>
								<span class="ziggeo_template_name"><?php echo $template_name; ?></span>
								<span style="display:none;" class="ziggeo_template_code"><?php echo $template_code; ?></span>
							</li>
						<?php
					}
					?>
					</ol>
					<?php
				}
				else {
					?>
					<p><?php _e('Please add some templates first', 'ziggeo'); ?></p>
					<?php
				}
			?>
		</script>

		<?php // On click handlers ?>
		<?php if($ajax !== true) { ?>
			<script type="text/javascript">
				jQuery(document).on("ready", function () {
					ziggeoSetupOverlayTemplates();
				});
			</script>
		<?php } ?>
	<?php
});


//Add the initial templates
add_filter('ziggeo_setting_available_templates', function($templates) {
	//Lets make it easy to change (add or remove) the list of options that are available
	$templates = array(
		array(
			'value' => '[ziggeoplayer',
			'string' => __('Ziggeo Player', 'ziggeo')
		),
		array(
			'value' => '[ziggeorecorder',
			'string' => __('Ziggeo Recorder', 'ziggeo')
		),
		array(
			'value' => '[ziggeorerecorder',
			'string' => __('Ziggeo ReRecorder', 'ziggeo')
		),
		array(
			'value' => '[ziggeouploader',
			'string' => __('Ziggeo Uploader', 'ziggeo')
		)
	);

	return $templates;
}, 1);

//Remove duplicates if any are present
add_filter('ziggeo_setting_available_templates', function($templates) {

	//It is fine to edits some entry for some reason, however we do not want duplicates to be present
	$templates = array_unique($templates, SORT_REGULAR);

	return $templates;
}, 100);

//================================================================================================
// @REMOVE - to be removed from here in future versions
//================================================================================================

//Add videowall JS and CSS file (so that calls work) and it all looks right
add_action('ziggeo_assets_post', function() {
	//Lets add JS
	wp_register_script('videowallsz-plugin-js', ZIGGEO_ROOT_URL . 'assets/js/videowalls-client.js', array());
	wp_enqueue_script('videowallsz-plugin-js');

	//Lets add CSS
	wp_register_style('videowallsz-styles-css', ZIGGEO_ROOT_URL . 'assets/css/videowall-styles.css', array());
	wp_enqueue_style('videowallsz-styles-css');

	//Note: Anyone editing this. The IDs need to be same as in the videowalls plugin. If not you would be loading 2 of the same files 2 times, this way WP handles it so it is only one
});

//Add the videowall template to the list of templates that are available in the admin (plugin settings)
add_filter('ziggeo_setting_available_templates', function($templates) {
	//lets add videowall template..
	$templates[] = array(
						'value' => '[ziggeovideowall',
						'string' => __('Ziggeo VideoWall', 'ziggeo')
	);

	return $templates;
});

//add videowall parameter into the list of content parsers available
add_action('ziggeo_manage_template_options_pre', function($existing_templates) {
	$existing_templates[] = array(
								'name'			=> 'ziggeovideowall',
								'func_pre'		=> 'videowallsz_prep_parameters_videowall',
								'func_final'	=> 'videowallsz_content_parse_videowall'
	);

	return $existing_templates;
});

//Add videowall parameters to the plugin
//IMPORTANT: This has priority of 1 so that it fires as soon as possible. That way we add video wall parameters right away. If you want to add additional video wall parameters, you should have those set on the default priority instead.
add_filter('ziggeo_template_parameters_list', function($parameters_list) {

	$wall_parameters = array(
		'fixed_width' => array(
			'type'					=> 'integer',
			'description'			=> __('Integer value representing fixed width of the video wall', 'videowalls-for-ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> false,
			'used_by_rerecorder'	=> false,
			'advanced'				=> true,
			'simple'				=> false,
			'default_value'			=> ''
		),
		'fixed_height' => array(
			'type'					=> 'integer',
			'description'			=> __('Integer value representing fixed height of the video wall', 'videowalls-for-ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> false,
			'used_by_rerecorder'	=> false,
			'advanced'				=> true,
			'simple'				=> false,
			'default_value'			=> ''
		),
		'scalable_width' => array(
			'type'					=> 'float',
			'description'			=> __('Float value representing width of the video wall in percentages of the available space', 'videowalls-for-ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> false,
			'used_by_rerecorder'	=> false,
			'advanced'				=> true,
			'simple'				=> false,
			'default_value'			=> ''
		),
		'scalable_height' => array(
			'type'					=> 'float',
			'description'			=> __('Float value representing height of the video wall in percentages of the available space', 'videowalls-for-ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> false,
			'used_by_rerecorder'	=> false,
			'advanced'				=> true,
			'simple'				=> false,
			'default_value'			=> ''
		),
		'title' => array(
			'type'					=> 'string',
			'description'			=> __('String value representing title of the video wall - always shown on top', 'videowalls-for-ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> false,
			'used_by_rerecorder'	=> false,
			'advanced'				=> true,
			'simple'				=> true,
			'default_value'			=> ''
		),
		'wall_design' => array(
			'type'					=> 'enum',
			'description'			=> __('This property allows you to change the initial design of your video wall. Default is show_pages', 'videowalls-for-ziggeo'),
			'options'				=> array('show_pages', 'slide_wall', 'chessboard_grid', 'mosaic_grid'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> false,
			'used_by_rerecorder'	=> false,
			'advanced'				=> true,
			'simple'				=> true,
			'default_value'			=> 'show_pages'
		),
		'videos_per_page' => array(
			'type'					=> 'integer',
			'description'			=> __('Integer value determining how many videos should be shown per page (defaults: 1 with slide_wall and 2 with show_pages)', 'videowalls-for-ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> false,
			'used_by_rerecorder'	=> false,
			'advanced'				=> true,
			'simple'				=> true,
			'default_value'			=> 2
		),
		'videos_to_show' => array(
			'type'					=> 'array',
			'description'			=> __('Array to setup which videos should be shown. Default video wall shows videos made on post it is on. This accepts comma separated values of post IDs (format: `post_ID`) or any other tags. Adding just &apos;&apos; (two single quotes) will show all videos in your account (videos_to_show=&apos;&apos;)', 'videowalls-for-ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> false,
			'used_by_rerecorder'	=> false,
			'advanced'				=> true,
			'simple'				=> true,
			'default_value'			=> '%CURRENT_ID%'
		),
		'video_width' => array(
			'type'					=> 'integer',
			'description'			=> __('Integer value representing the width of each video in the wall', 'videowalls-for-ziggeo'),
			'used_by_player'		=> true,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'advanced'				=> true,
			'simple'				=> true,
			'default_value'			=> ''
		),
		'video_height' => array(
			'type'					=> 'integer',
			'description'			=> __('Integer value representing the height of each video in the wall', 'videowalls-for-ziggeo'),
			'used_by_player'		=> true,
			'used_by_recorder'		=> true,
			'used_by_rerecorder'	=> true,
			'advanced'				=> true,
			'simple'				=> true,
			'default_value'			=> ''
		),
		'on_no_videos' => array(
			'type'					=> 'enum',
			'description'			=> __('Array value representing what should happen if there are no videos.', 'videowalls-for-ziggeo'),
			'options'				=> array('showmessage', 'showtemplate', 'hidewall'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> false,
			'used_by_rerecorder'	=> false,
			'advanced'				=> true,
			'simple'				=> true,
			'default_value'			=> 'showmessage'
		),
		'message' => array(
			'type'					=> 'string',
			'description'			=> __('String value that will be shown if `on_no_videos` is set to `showmessage`', 'videowalls-for-ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> false,
			'used_by_rerecorder'	=> false,
			'advanced'				=> true,
			'simple'				=> true,
			'default_value'			=> ''
		),
		'template_name' => array(
			'type'					=> 'string',
			'description'			=> __('String value holding the name of the video template that you want to show if the `on_no_videos` is set to `showtemplate` (if it does not exist default is loaded)', 'videowalls-for-ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> false,
			'used_by_rerecorder'	=> false,
			'advanced'				=> true,
			'simple'				=> true,
			'default_value'			=> ''
		),
		'show_videos' => array(
			'type'					=> 'enum',
			'description'			=> __('Array value stating which videos will be shown.', 'videowalls-for-ziggeo'),
			'options'				=> array('all', 'approved', 'rejected', 'pending'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> false,
			'used_by_rerecorder'	=> false,
			'advanced'				=> true,
			'simple'				=> true,
			'default_value'			=> 'approved'
		),
		'autoplay' => array(
			'type'					=> 'bool',
			'description'			=> __('Boolean value indicating if first video should be played automatically.', 'videowalls-for-ziggeo'),
			'used_by_player'		=> true,
			'used_by_recorder'		=> false,
			'used_by_rerecorder'	=> false,
			'advanced'				=> true,
			'simple'				=> true,
			'default_value'			=> false
		),
		'autoplay-continue-end' => array(
			'type'					=> 'bool',
			'description'			=> __('Boolean value indicating that you want the autoplay of second video to start when playback of first one ends and to continue until the end of the (first) page (requires `autoplay`)', 'videowalls-for-ziggeo'),
			'used_by_player'		=> true,
			'used_by_recorder'		=> false,
			'used_by_rerecorder'	=> false,
			'advanced'				=> true,
			'simple'				=> false,
			'default_value'			=> false
		),
		'autoplay-continue-run' => array(
			'type'					=> 'bool',
			'description'			=> __('Boolean value indicating that you want the autoplay of second video to start when playback of first one ends and to continue until the end of the (first) page is met, then start again (looping through all videos on the page one by one) - (requires `autoplay`)', 'videowalls-for-ziggeo'),
			'used_by_player'		=> true,
			'used_by_recorder'		=> false,
			'used_by_rerecorder'	=> false,
			'advanced'				=> true,
			'simple'				=> false,
			'default_value'			=> false
		),
		'show' => array(
			'type'					=> 'bool',
			'description'			=> __('Boolean value indicating if video wall is shown even if the video is not submitted (defaults to waiting for submission of a video to show the video wall, adding this shows it right away)', 'videowalls-for-ziggeo'),
			'used_by_player'		=> false,
			'used_by_recorder'		=> false,
			'used_by_rerecorder'	=> false,
			'advanced'				=> true,
			'simple'				=> true,
			'default_value'			=> false
		)
	);

	$parameters_list['wall'] = $wall_parameters;

	return $parameters_list;
}, 1);


//Shows a message about the VideoWall parameters right above the template editor
add_action('ziggeo_settings_before_editor', function($templates) {
	?>
	<span id="ziggeo_videowall_info" class="ziggeo_info" style="display:none;"><?php
		_ex('Video Wall template (by default) shows videos made on the post the videwall template is on. If you wish to change it to show other videos, just add', 'videowall info 1/3', 'ziggeo');
		?> <b onclick="ziggeoPUIParametersQuickAdd({ currentTarget:this});" data-equal="=''"><?php
			_ex('videos_to_show', 'videowall info 2/3', 'ziggeo');
		?></b> <?php
		_ex('and modify it to your needs', 'videowall info 3/3', 'ziggeo'); ?></span>
	<?php
});

//Adds videowall parameters into the easy template builder
add_action('ziggeo_templates_editor_easy_parameters_section', function($sections) {
	if(!in_array('wall', $sections)) {
		$sections[] = 'wall';
	}

	return $sections;
});

//Adds videowall parameters into the advanced tempalte builder
add_action('ziggeo_templates_editor_advanced_parameters_section', function($sections) {
	if(!in_array('wall', $sections)) {
		$sections[] = 'wall';
	}

	return $sections;
});

//Adds the videowall to the list of template tags that should be skipped in processing
add_filter('ziggeo_parameter_prep_skip_list', function($list) {
	$list[] = '[ziggeovideowall';

	return $list;
});

//Add the walls array within the ZiggeoWP object so videowalls work fine
add_action('ziggeo_add_to_ziggeowp_object', function() {
	?>
	videowalls: {
		endless: '',
		walls: {}
	},
	<?php
});

//We can add any number of options into one hook, this is added as is just to separate it as we will introduce it in later versions
// that is if we get feedback on our plugin and have some people asking for it.
/*
add_action('ziggeo_manage_template_options_pre', function($existing_templates) {
	$existing_templates[] = array(
								'name'			=> 'ziggeoform',
								'func_pre'		'ziggeoPPrepParametersZiggeoform',
								'func_final'	=> 'ziggeoContentParseZiggeoform'
	);

	return $existing_templates;
});
*/
?>