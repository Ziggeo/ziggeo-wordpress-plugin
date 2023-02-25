<?php

// This file is used when comment modification is turned on and the video comments are set as required


//Checking if WP is running or if this is a direct call..
defined('ABSPATH') or die();


// you would change 0 to 10 so that your code overwrites ours..
add_filter('ziggeo_comments_js_template_vrto', 'ziggeo_p_default_handle_vrto_comments', 0, 2);

//This is an example of how you would hook to change the code for comments. We are actually just adding code this way that we would add either way. You can simply write your own code and change this one..
function ziggeo_p_default_handle_vrto_comments($recorder_code, $player_code) {
	$options = ziggeo_get_plugin_options();
	?>
	<div class="comment-navigation">
		<span class="dashicons dashicons-video-alt"></span> <?php _e('Video Comment with optional text comment', 'ziggeo'); ?>
	</div>
	<div id="comments-video-container">
		<?php
			//We use this field to get the video token into it, allowing us more freedom with the overall comment manipulation
		?>
		<input type="hidden" id="ziggeo_video_token" value="">
		<?php //Capture user comment as video ?>
		<ziggeorecorder
			<?php echo $recorder_code;
			//Do not allow the form to be submitted unless video is filled out, but also allow custom form_accept setup
			if( stripos($recorder_code, 'form_accept') === false && stripos($recorder_code, 'form-accept') === false) {
				?> ziggeo-form-accept="#<?php echo $options['comments_form_id']; ?>" <?php
			}
			//Capture "wordpress" and "username" as video tags, but only if the tags are not set in the template
			if( stripos($recorder_code, 'tags') === false) {
				//for video wall to work nicely, we will collect which post the recording is made from now on as well (by default).
				//Lets get details of the current user to use them in tags
				$current_user = ziggeo_p_get_current_user();

				//If a guest was to record a reply, we still want to add the comments tag, but it seems nice to indicate that that was done by some guest instead of not having that tag - which makes it look like it is missing.
				if(empty($current_user->user_login)) {
					$current_username = 'guest';
				}
				else {
					$current_username = $current_user->user_login;
				}
				?> ziggeo-tags="wordpress,<?php echo $current_username; ?>,post_<?php echo get_the_ID(); ?>,comment" <?php
			}
			?>
			>
		</ziggeorecorder>
	</div>
	<div id="comments-text-container"></div>
	<?php
	//return $code;
}

// ziggeoParseCommentVRTO = Parse Comments when Video is Required and Text is Optional
// we allow it to be completely overwritten by a plugin or through functions.php in theme..
if(!function_exists('ziggeo_comment_vrto_js_code')) {
	function ziggeo_comment_vrto_js_code($template_recorder, $template_player) {

		$options = ziggeo_get_plugin_options();

		$code = "";
		?>
		<script type="text/template" id="comment-ziggeo-template">
			<?php
				//someone can output the code directly, or return it to us, if they return it, we will print it out..
				echo apply_filters('ziggeo_comments_js_template_vrto', $code, $template_recorder);
			?>
		</script>

		<script type="text/javascript">
			var elems = {};

			elems.textarea = document.getElementById('<?php echo $options['comments_text_id']; ?>');
			elems.form = document.getElementById('<?php echo $options['comments_form_id']; ?>');

			// If there is no form to be found, we do not do anything
			if(elems.form) {
				elems.btn_submit = elems.form.querySelector('#submit');

				if(elems.btn_submit) {
					elems.btn_submit.setAttribute('disabled', 'disabled');
				}

				elems.comments_container = jQuery(elems.textarea).closest('form >')[0];

				elems.comments_container.insertAdjacentHTML('beforebegin', document.getElementById('comment-ziggeo-template').innerHTML);

				function ziggeoCommentsVerifiedListener() {

					// Added to support lazy load option
					if(typeof ziggeo_app === 'undefined') {
						setTimeout(function() {
							ziggeoCommentsVerifiedListener();
						}, 400);
						return false;
					}

					ziggeo_app.embed_events.on("verified", function (embedding) {

						var ziggeo_token = document.getElementById('ziggeo_video_token');
						<?php
						if($template_player) {
							?>
								//Lets add the default value into the box
								ziggeo_token.value = '[ziggeoplayer ' + '<?php echo $template_player ?>';
								//Now we have both the token and the template. We should search the template to see if token="" is present and if so, just insert the video token into quotes, otherwise, we would need to add the token attribute and show it up.
								if( (index_s = ziggeo_token.value.indexOf(' video') ) > -1 ||
									(index_s = ziggeo_token.value.indexOf(' ziggeo-video') ) > -1) {

									//Lets grab the ending as well
									var index_e = ziggeo_token.value.indexOf(' ', index_s);
									ziggeo_token.value = ziggeo_token.value.substr(0, index_s) + ' video="' + embedding.get('video') + '"' + ziggeo_token.value.substr(index + index_e + 2)
								}
								else {
									ziggeo_token.value += ' ziggeo-video="' + embedding.get('video') + '" ]';
								}
							<?php
						}
						//It is not template that we are using, we are just saving it with the parameters..
						elseif( !empty($template_player) ) {
							?>
							ziggeo_token.value = '[ziggeoplayer <?php echo $template_player; ?> video="' + embedding.get('video') + '" ]';
							<?php
							//[ziggeo ziggeo-width=480 ziggeo-height=360 video="token" ]
						}
						//Fallback to the previous method of embedding. Should not come to it, but just in case it does, we have it here, ready to capture the same.
						else {
							?>
							ziggeo_token.value = '[ziggeoplayer]' + embedding.get('video') + '[/ziggeoplayer]';
							<?php
						}
						?>
						if(elems.textarea.value === '') {
							elems.textarea.value = ziggeo_token.value + "\n\n"
						}
						else {
							elems.textarea.value += "\n\n" + ziggeo_token.value;
						}

						elems.btn_submit.removeAttribute('disabled');
					});
				}

				ziggeoCommentsVerifiedListener();
			}
		</script>
		<?php
	}
}
?>