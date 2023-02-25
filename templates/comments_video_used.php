<?php

// This file is used when video and text are allowed, not required. It will add 2 tabs one for each option and allow switching between to record the video.
// Note: This will allow switching however it is either video or text comment, choose one and submit


//Checking if WP is running or if this is a direct call..
defined('ABSPATH') or die();

// ziggeo_comment_vat_js_code = Parse Comments when Video is used (not required, just present)
// we allow it to be completely overwritten by a plugin or through functions.php in theme..
if(!function_exists('ziggeo_comment_vat_js_code')) {
	function ziggeo_comment_vat_js_code($recorder_code, $player_code = null) {
		$options = ziggeo_get_plugin_options();

		$has_text_comment = false;

		if( !isset($options["disable_text_comments"]) || (isset($options["disable_text_comments"]) && $options["disable_text_comments"] !== '1') ) {
			$has_text_comment = true;
		}

		?>
		<script type="text/template" id="comment-ziggeo-template">
			<div class="comment-navigation">
				<ul class="comment-nav-menu">
					<?php 
					//If text comments are not disabled
					if($has_text_comment === true) {
						?>
						<li>
							<a id="comments-text-link">
								<span class="dashicons dashicons-text"></span>
								Text Comment
							</a>
						</li>
					<?php } ?>
					<li>
						<a id="comments-video-link" class="selected">
							<span class="dashicons dashicons-video-alt"></span>
							Video Comment
						</a>
					</li>
				</ul>
			</div>
			<?php if($has_text_comment === true) { ?>
				<div id="comments-text-container"></div>
			<?php } ?>
			<div id="comments-video-container"></div>
		</script>

		<?php //This should be left as default and a call made to retrieve the setup made by customer in the admin panel ?>
		<script type="text/template" id="ziggeo-recorder">
			<?php //Capture user comment as video ?>
			<ziggeorecorder id="comments_recorder" 
				<?php echo $recorder_code;
				//Do not allow the form to be submitted unless video is filled out, but also allow custom form_accept setup
				if( stripos($recorder_code, 'form_accept') === false && stripos($recorder_code, 'form-accept') === false) {
					?> ziggeo-form_accept="#<?php echo $options['comments_form_id']; ?>" <?php // #commentform
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
			></ziggeorecorder>
		</script>

		<?php //JavaScript code using jQuery to switch between video and text comments ?>
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

				var tab_btn_text = document.getElementById('comments-text-link');
				var tab_btn_video = document.getElementById('comments-video-link');
				var tab_text = elems.comments_container;
				var tab_video = document.getElementById('comments-video-container');

				// Insert recorder code
				tab_video.insertAdjacentHTML('beforeend', document.getElementById('ziggeo-recorder').innerHTML);

				if(tab_btn_text) {
					tab_btn_text.addEventListener('click', function() {
						tab_video.style.display = 'none';
						tab_text.style.display = 'block';

						tab_btn_video.className = tab_btn_video.className.replace('selected');
						tab_btn_text.className += ' selected';
					});
				}

				tab_btn_video.addEventListener('click', function() {
					tab_video.style.display = 'block';
					if(tab_text) {
						tab_text.style.display = 'none';
					}

					if(tab_btn_text) {
						tab_btn_text.className = tab_btn_text.className.replace('selected');
					}
					tab_btn_video.className += ' selected';
				});

				if(!tab_btn_text) {
					tab_text.style.display = 'none';
				}

				//since it is just added now, we need to make some timeout
				function ziggeoCommentsVerifiedListener() {

					// Added to support lazy load option
					if(typeof ziggeo_app === 'undefined') {
						setTimeout(function() {
							ziggeoCommentsVerifiedListener();
						}, 400);
						return false;
					}

					var recorder = ZiggeoApi.V2.Recorder.findByElement( document.getElementById('comments_recorder') );

					if(recorder) {
						recorder.on('verified' , function() {

							var tmp_player_code = '';

							<?php
							if($player_code) {
								?>
								tmp_player_code = '[ziggeoplayer ' + '<?php echo $player_code ?>';
								//Now we have both the token and the template. We should search the template to see if token="" is present and if so, just insert the video token into quotes, otherwise, we would need to add the token attribute and show it up.
								if( (index_s = tmp_player_code.indexOf(' video') ) > -1 ||
									(index_s = tmp_player_code.indexOf(' ziggeo-video') ) > -1) {

									//Lets grab the ending as well
									var index_e = tmp_player_code.indexOf(' ', index_s);
									tmp_player_code = tmp_player_code.substr(0, index_s) + ' video="' + recorder.get('video') + '"' + tmp_player_code.substr(index + index_e + 2)
								}
								else {
									tmp_player_code += ' ziggeo-video="' + recorder.get('video') + '" ]';
								}
								<?php
							}
							//It is not tempalte that we are using, we are just saving it with the parameters..
							elseif( !empty($player_code) ) {
								?>
								tmp_player_code = '[ziggeoplayer <?php echo $player_code; ?> video="' + recorder.get('video') + '" ]';
								<?php
							}
							//Fallback to the previous method of embedding. Should not come to it, but just in case it does, we have it here, ready to capture the same.
							else {
								?>
								tmp_player_code = '[ziggeoplayer]' + recorder.get('video') + '[/ziggeoplayer]';
								<?php
							}
							?>

							elems.textarea.value = tmp_player_code;
							elems.btn_submit.removeAttribute('disabled');
						});
					}
					else {
						setTimeout(function() {
							ziggeoCommentsVerifiedListener();
						}, 400);
						return false;
					}
				}

				ziggeoCommentsVerifiedListener();
			}
		</script>
		<?php
	}
}