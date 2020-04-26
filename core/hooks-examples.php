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

// Add Server side Auth token to the page in back, only when the person that is logged in should have
// access to such option
add_action('ziggeo_add_to_ziggeowp_object', function() {

	if(is_admin() && current_user_can('moderate_comments')) {
	?>
	server_auth: "<?php echo ziggeo_get_plugin_options('sauth_token'); ?>",
	<?php
	}
});

?>