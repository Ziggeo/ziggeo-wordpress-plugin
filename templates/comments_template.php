<?php

if (file_exists(TEMPLATEPATH . '/comments.php'))
	include_once TEMPLATEPATH . '/comments.php';
elseif(file_exists(TEMPLATEPATH . '/includes/comments.php'))
	include_once TEMPLATEPATH . '/includes/comments.php';

	$current_user = wp_get_current_user();
?>

<script type="text/template" id="comment-ziggeo-template">
	<div class="comment-navigation">
		<ul class="comment-nav-menu">
			<li>
				<a id="comments-text-link">
					<span class="dashicons dashicons-text"></span>
					Text Comment
				</a>
			</li>
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

<?php
	$options = get_option('ziggeo_video');
	$default = 'ziggeo-width=480 ziggeo-height=360 ziggeo-limit=120';
	$config = @$options["recorder_config"] ? $options["recorder_config"] : $default; 
?>
	
<script type="text/template" id="ziggeo-recorder">
	<ziggeo
		<?= $config ?>
		ziggeo-form_accept="#commentform"
		ziggeo-tags="wordpress,<?= $current_user->user_login ?>"
	></ziggeo>
</script>

<script>
	jQuery("label[for='comment']").remove();
	jQuery("#comment").parent().parent().css("position", "inherit");
	jQuery("#commentform").css("padding-top", "0px");
	jQuery(jQuery("#comment").parent()).before(jQuery("#comment-ziggeo-template").html());
	jQuery("#comments-text-container").append(jQuery(jQuery("#comment").parent()));
	jQuery("#comments-text-container").append(jQuery(".form-allowed-tags"));
	jQuery("#comments-text-link").on("click", function () {
		jQuery("#comments-text-container").css("display", "");
		jQuery("#comments-video-container").css("display", "none");
		jQuery("#comments-video-container").html("");
		jQuery("#commenttype").val("text");
		jQuery("#comment").val("");
	});
	jQuery("#comments-video-link").on("click", function () {
		jQuery("#comments-video-container").css("display", "");
		jQuery("#comments-video-container").html(jQuery("#ziggeo-recorder").html());
		jQuery("#comments-text-container").css("display", "none");
		jQuery("#commenttype").val("video");
		jQuery("#comment").val("");
	});
	ZiggeoApi.Events.on("submitted", function (data) {
		jQuery("#comment").val("[ziggeo]" + data.video.token + "[/ziggeo]");
	});
</script>
