<?php
//Checking if WP is running or if this is a direct call..
defined('ABSPATH') or die();

if (file_exists(TEMPLATEPATH . '/comments.php'))
	include_once TEMPLATEPATH . '/comments.php';
elseif(file_exists(TEMPLATEPATH . '/includes/comments.php'))
	include_once TEMPLATEPATH . '/includes/comments.php';

	$current_user = wp_get_current_user();
?><?php
	$options = get_option('ziggeo_video');
	$default = 'ziggeo-width=480 ziggeo-height=360 ziggeo-limit=120';
	// I am not a big fan of @ since it would hide all errors, so I would change this to isset instead
	//$config = @$options["recorder_config"] ? $options["recorder_config"] : $default;
	$config = isset($options["recorder_config"]) ? $options["recorder_config"] : $default;

?><?php
if( !isset($options["disable_video_comments"]) || (isset($options["disable_video_comments"]) && $options["disable_video_comments"] !== '1')) { ?>
	<script type="text/template" id="comment-ziggeo-template">
		<div class="comment-navigation">
			<ul class="comment-nav-menu">
				<?php //altering so that it is not using @ ?>
				<?php if( !isset($options["disable_text_comments"]) || (isset($options["disable_text_comments"]) && $options["disable_text_comments"] !== '1') ) { ?>
				<li>
					<a id="comments-text-link">
						<span class="dashicons dashicons-text"></span>
						Text Comment
					</a>
				</li>
				<?php } ?>
				<li>
					<a id="comments-video-link">
						<span class="dashicons dashicons-video-alt"></span>
						Video Comment
					</a>
				</li>
			</ul>
		</div>
		<div id="comments-text-container"></div>
		<div id="comments-video-container"></div>
	</script>

	<script type="text/template" id="ziggeo-recorder">
		<?php //Capture user comment as video ?>
		<ziggeo
			<?php echo $config; ?>
			<?php //Do not allow the form to be submitted unless video is filled out ?>
			ziggeo-form_accept="#commentform"
			<?php //Capture "wordpress" and "username" as video tags. @TODO - maybe add custom tags here? ?>
			ziggeo-tags="wordpress,<?php echo $current_user->user_login; ?>"
		></ziggeo>
	</script>

	<script>
		var elems = {};
		elems.textarea = jQuery("[name='comment']");
		elems.form = elems.textarea.closest("form");
		elems.textarea_container = elems.textarea.closest("form>");
		elems.moveover = [jQuery(".form-allowed-tags")];
		elems.garbage = [jQuery("label[for='comment']")];
		elems.form.css("position", "inherit");
		elems.form.css("padding-top", "0px");
		jQuery(elems.textarea_container).before(jQuery("#comment-ziggeo-template").html());
		jQuery("#comments-text-container").append(elems.textarea_container);
		BetaJS.Objs.iter(elems.garbage, function (elem) {
			elem.remove();
		});	
		BetaJS.Objs.iter(elems.moveover, function (elem) {
			jQuery("#comments-text-container").append(elem);
		});	
		jQuery("#comments-text-link").on("click", function () {
			jQuery("#comments-text-container").css("display", "");
			jQuery("#comments-video-container").css("display", "none");
			jQuery("#comments-video-container").html("");
			elems.textarea.val("");
		});
		jQuery("#comments-video-link").on("click", function () {
			jQuery("#comments-video-container").css("display", ""); //show video comment recorder
			jQuery("#comments-video-container").html(jQuery("#ziggeo-recorder").html());
			jQuery("#comments-text-container").css("display", "none"); //hide text comment textarea
			elems.textarea.val("");
		});
		ZiggeoApi.Events.on("submitted", function (data) {
			elems.textarea.val("[ziggeo]" + data.video.token + "[/ziggeo]"); //show video upon recording of the same
		});
		//Removing @
		<?php if( !isset($options["disable_text_comments"]) || (isset($options["disable_text_comments"]) && $options["disable_text_comments"] === '1') ) { ?>
			setTimeout(function () {
				jQuery("#comments-video-link").click();
			});
		<?php } ?>
	</script>
<?php } ?>