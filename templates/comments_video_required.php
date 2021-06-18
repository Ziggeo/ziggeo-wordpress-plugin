<?php

//Checking if WP is running or if this is a direct call..
defined('ABSPATH') or die();



// you would change 0 to 10 so that your code overwrites ours..
add_filter('ziggeo_comments_js_template_vrto', 'ziggeo_p_default_handle_vrto_comments', 0, 2);

//This is an example of how you would hook to change the code for comments. We are actually just adding code this way that we would add either way. You can simply write your own code and change this one..
function ziggeo_p_default_handle_vrto_comments($recorder_code, $player_code) {
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
			if( stripos($recorder_code, 'form_accept') === false) {
				?> ziggeo-form-accept="#commentform" <?php
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
			//Locate comments textarea
			elems.textarea = jQuery("[name='comment']");
			//get the form it is under
			elems.form = elems.textarea.closest("form");
			//element containing the textarea
			elems.textarea_container = elems.textarea.closest("form>");
			//get allowed tags
			elems.moveover = [jQuery(".form-allowed-tags")];
			//Get comments label
			elems.garbage = [jQuery("label[for='comment']")];
			//Get submit button..
			elems.submitbtn = elems.form.find(':submit');
			//Disable submit button
			elems.submitbtn.attr('disabled', 'disabled');

			elems.form.css("position", "inherit");
			elems.form.css("padding-top", "0px");

			//Add template above the comment textarea by using template above
			jQuery(elems.textarea_container).before(jQuery("#comment-ziggeo-template").html());
			//Add comment textarea into our comments text container element
			jQuery("#comments-text-container").append(elems.textarea_container);

			//Lets make a dummy textarea..
			elems.clonedta = elems.textarea.clone().prop('id', 'ziggeo_commentTA').prop('name', 'ziggeo_commentTA').appendTo("#comments-text-container > .comment-form-comment");

			//Lets hide the original comments box:
			elems.textarea.css('display', 'none');

			//Set the field to be filled out properly for us..
			jQuery("#ziggeo_commentTA").on("keyup", function () {

				var comment = document.getElementById('comment');
				//We will always pass this to the textarea..
				comment.value = document.getElementById('ziggeo_video_token').value;

				//is the video recorded?
				if( document.getElementById('ziggeo_video_token').value.length > 0 ) {
					comment.value += "\n\n" + document.getElementById('ziggeo_commentTA').value;
					elems.submitbtn.removeAttr('disabled');
				}
				else {
					//show that video is required..
					elems.submitbtn.attr('disabled', 'disabled');
				}
			});


			//Needed to support lazy load option
			if(typeof ziggeo_app !== 'undefined') {
				//Show video after recording
				ziggeo_app.embed_events.on("verified", function (embedding) {

					var ziggeo_token = document.getElementById('ziggeo_video_token');
					<?php
					if($template_player) {
						?>
							//Lets add the default value into the box
							ziggeo_token.value = '[ziggeoplayer ' + '<?php echo $template_player ?>';
							//Now we have both the token and the template. We should search the template to see if token="" is present and if so, just insert the video token into quotes, otherwise, we would need to add the token attribute and show it up.
							if( (indexS = ziggeo_token.value.indexOf(' video') ) > -1 ||
								(indexS = ziggeo_token.value.indexOf(' ziggeo-video') ) > -1) {

								//Lets grab the ending as well
								var indexE = ziggeo_token.value.indexOf(' ', indexS);
								ziggeo_token.value = ziggeo_token.value.substr(0, indexS) + ' video="' + embedding.get('video') + '"' + ziggeo_token.value.substr(index + indexE + 2)
							}
							else {
								ziggeo_token.value += ' ziggeo-video="' + embedding.get('video') + '" ]';
							}
						<?php
					}
					//It is not tempalte that we are using, we are just saving it with the parameters..
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
					document.getElementById('comment').value = document.getElementById('ziggeo_video_token').value + "\n\n" + document.getElementById('ziggeo_commentTA').value;

					elems.submitbtn.removeAttr('disabled');
				});
			}
		</script>
		<?php
	}
}
?>