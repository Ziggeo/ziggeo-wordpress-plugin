<?php

//Checking if WP is running or if this is a direct call..
defined('ABSPATH') or die();


// ziggeo_comment_vat_js_code = Parse Comments when Video is used (not required, just present)
// we allow it to be completely overwritten by a plugin or through functions.php in theme..
if(!function_exists('ziggeo_comment_vat_js_code')) {
	function ziggeo_comment_vat_js_code($template_recorder, $template_player) {
		?>
		<script type="text/template" id="comment-ziggeo-template">
			<div class="comment-navigation">
				<ul class="comment-nav-menu">
					<?php if( !isset($options["disable_text_comments"]) || (isset($options["disable_text_comments"]) && $options["disable_text_comments"] !== '1') ) {
					//If text comments are not disabled    
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
			<div id="comments-text-container"></div>
			<div id="comments-video-container"></div>
		</script>

		<?php //This should be left as default and a call made to retrieve the setup made by customer in the admin panel ?>
		<script type="text/template" id="ziggeo-recorder">
			<?php //Capture user comment as video ?>
			<ziggeorecorder id="comments_recorder" 
				<?php echo $default_recorder;
				//Do not allow the form to be submitted unless video is filled out, but also allow custom form_accept setup
				if( stripos($default_recorder, 'form_accept') === false) {
					?> ziggeo-form_accept="#commentform" <?php
				}
				//Capture "wordpress" and "username" as video tags, but only if the tags are not set in the template
				if( stripos($default_recorder, 'tags') === false) {
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
			elems.textarea = jQuery("[name='comment']");
			elems.form = elems.textarea.closest("form");

			elems.textarea_container = elems.textarea.closest("form>");
			elems.moveover = document.querySelectorAll('.form-allowed-tags'); //[jQuery(".form-allowed-tags")];
			elems.garbage = document.querySelectorAll('label[for="comment"]'); //[jQuery("label[for='comment']")];
			elems.form.css("position", "inherit");
			elems.form.css("padding-top", "0px");
			jQuery(elems.textarea_container).before(jQuery("#comment-ziggeo-template").html());
			jQuery("#comments-text-container").append(elems.textarea_container);
			for(i = 0; i < elems.garbage.length; i++) {
				elems.garbage[i].parentElement.removeChild(elems.garbage[i]);
			}
			for(i = 0; i < elems.moveover.length; i++) {
				jQuery("#comments-text-container").append(elems.moveover[i]);
			}
			jQuery("#comments-text-link").on("click", function () {
				jQuery("#comments-text-container").css("display", "");
				jQuery("#comments-video-container").css("display", "none");
				jQuery("#comments-video-container").html("");
				//Lets make it clear what is selected
				jQuery('#comments-text-link').addClass('selected');
				jQuery('#comments-video-link').removeClass('selected');
				elems.textarea.val("");
			});
			jQuery("#comments-video-link").on("click", function () {
				jQuery("#comments-video-container").css("display", ""); //show video comment recorder
				jQuery("#comments-video-container").html(jQuery("#ziggeo-recorder").html());
				jQuery("#comments-text-container").css("display", "none"); //hide text comment textarea
				//Lets make it clear what is selected
				jQuery('#comments-video-link').addClass('selected');
				jQuery('#comments-text-link').removeClass('selected');
				elems.textarea.val("");

				//since it is just added now, we need to make some timeout
				setTimeout(function() {
					var recorder = ZiggeoApi.V2.Recorder.findByElement( document.getElementById('comments_recorder') );

					recorder.on('verified' , function() {
						//show video upon recording of the same
						console.log('verified');
						console.log("[ziggeoplayer]" + recorder.get('video') + "[/ziggeoplayer]");
						elems.textarea.val("[ziggeoplayer]" + recorder.get('video') + "[/ziggeoplayer]");
					});
				}, 1000);
			});
			<?php if( !isset($options["disable_text_comments"]) || (isset($options["disable_text_comments"]) && $options["disable_text_comments"] === '1') ) { ?>
				setTimeout(function () {
					jQuery("#comments-video-link").click();
				});
			<?php } ?>
		</script>
		<?php
	}
}