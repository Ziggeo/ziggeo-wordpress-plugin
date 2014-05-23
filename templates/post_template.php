<?php
	global $current_user;
	get_currentuserinfo();
?>

<script type="text/template" id="ziggeo-insert-button-template">
	<a href="#" id="insert-ziggeo-button" class="button" title="Record Video">
		<span class="dashicons dashicons-video-alt"></span> Record Video
	</a>
	<a href="#" id="revert-ziggeo-button" class="button" title="Discard Video" style="display: none">
		<span class="dashicons dashicons-editor-break"></span> Discard Video
	</a>
</script>

<script type="text/template" id="ziggeo-recorder-template">
	<div id="ziggeo-recorder">
		<ziggeo
			ziggeo-width=480
			ziggeo-height=360
			ziggeo-limit=240
			ziggeo-form_accept="#post"
			ziggeo-tags="wordpress,<?= $current_user->user_login ?>"
		></ziggeo>
	</div>
</script>

<script type="text/template" id="ziggeo-rerecorder-template">
	<div id="ziggeo-recorder">
		<ziggeo
			ziggeo-width=480
			ziggeo-height=360
			ziggeo-limit=240
			ziggeo-video="VIDEOTOKEN"
			ziggeo-modes="rerecorder"
			ziggeo-form_accept="#post"
			ziggeo-tags="wordpress,<?= $current_user->user_login ?>"
		></ziggeo>
	</div>
</script>

<script>
	jQuery(document).on("ready", function () {
		jQuery(".wp-media-buttons").prepend(jQuery("#ziggeo-insert-button-template").html());
		var editor_elements = [".wp-editor-tabs", ".wp-editor-container", "#post-status-info tr", "#insert-media-button"];
		var video_mode = false;
		var content = jQuery("#content").val();
		var regex = /\[ziggeo\](.*)\[\/ziggeo\]/g;
		var matches = regex.exec(content);
		if (matches) {
			video_mode = true;
			jQuery("#content-html").click();
			for (var i = 0; i < editor_elements.length; ++i)
				jQuery(editor_elements[i]).css("display", "none");
			jQuery("#revert-ziggeo-button").css("display", "");
			jQuery("#post-body-content").append(jQuery("#ziggeo-rerecorder-template").html().replace("VIDEOTOKEN", matches[1]));
		}
		jQuery("#insert-ziggeo-button").on("click", function () {
			if (video_mode)
				return;
			video_mode = true;
			jQuery("#content-html").click();
			for (var i = 0; i < editor_elements.length; ++i)
				jQuery(editor_elements[i]).css("display", "none");
			jQuery("#revert-ziggeo-button").css("display", "");
			jQuery("#post-body-content").append(jQuery("#ziggeo-recorder-template").html());
		});
		jQuery("#revert-ziggeo-button").on("click", function () {
			if (!video_mode)
				return;
			if (!confirm("Are you sure that you want to remove your video?"))
				return;
			video_mode = false;
			jQuery("#content").val("");
			jQuery("#content-tmce").click();
			for (var i = 0; i < editor_elements.length; ++i)
				jQuery(editor_elements[i]).css("display", "");
			jQuery("#revert-ziggeo-button").css("display", "none");
			jQuery("#ziggeo-recorder").remove();
		});
		ZiggeoApi.Events.on("submitted", function (data) {
			jQuery("#content").val("[ziggeo]" + data.video.token + "[/ziggeo]");
		});
	});
</script>
