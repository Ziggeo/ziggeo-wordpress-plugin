<?php
//Checking if WP is running or if this is a direct call..
defined('ABSPATH') or die();

if (file_exists(TEMPLATEPATH . '/comments.php'))
	include_once TEMPLATEPATH . '/comments.php';
elseif(file_exists(TEMPLATEPATH . '/includes/comments.php'))
	include_once TEMPLATEPATH . '/includes/comments.php';

	//Lets get details of the current user to use them in tags
	$current_user = wp_get_current_user();

	//Getting our options
	$options = get_option('ziggeo_video');

	//Setting up defaults

	//Recorder defaults to be used in comments.
	$default_recorder = ( isset($options["recorder_config"]) && !empty($options["recorder_config"]) ) ? $options["recorder_config"] : 'ziggeo-width=480 ziggeo-height=360 ziggeo-limit=120';

	//Just so that we know if we are using template or not..
	$template_recorder = ( isset($options['comments_recorder_template'])  && !empty($options["comments_recorder_template"]) );

	//Final recorder template that we will be using
	$default_recorder = ( $template_recorder ) ? $options['comments_recorder_template'] : $default_recorder;

	//Player defaults to be used in comments.
	$default_player = ( isset($options["player_config"]) && !empty($options["player_config"]) ) ? $options["player_config"] : 'ziggeo-width=480 ziggeo-height=360';

	//Just so that we know if we are using template or not..
	$template_player = ( isset($options['comments_recorder_template'])  && !empty($options["comments_recorder_template"]) );

	//Final player template that we will be using
	$default_player = ( $template_player ) ? $options['comments_player_template'] : $default_player;



	//@TODO - remove, present for testing purposes only 
	$default_recorder .= ' ziggeo-perms="allowupload, forbidrecord"';
/* TODO

	1. Play videos using the default template (the previous setup), or by using the selected template
	2. Record videos using the default template (the previous setup), or by using the selected template
	3. respect the options in the General tab:
		3.1. Are video comments disabled?
			- if this is checked (disable comment), it should uncheck the 3.3
		3.2. Are text comments disabled?
			- if this is checked (disable comment), it should uncheck the 3.3
		3.3. Is video required and text optional?
			- If this is selected, JS should uncheck the 3.1. and 3.2 (if they are checked)

			Upon recording the [ziggeo]ea8b8fcb6ce027293d2e345607c9c502[/ziggeo] is added to the #comment textarea. If we switch to it, the same is deleted.
			It might be good to not have the same shown to visitors, but to include the same within the comment upon posting of the form..
			Solution for 3.3:
			Show 2 different fields, one is required and only once it is filled out, will the comment2 field be combined with it and inserted into the comments textarea. This allows us to hide the key and to capture the comments input without any issue, regardless of the changes that they are making on the same..

	4. Reorganize js codes..
*/


//If video is set as required and text as optional..
if( isset($options['video_and_text']) && $options['video_and_text'] === '1' ) {
	?>
	<script type="text/template" id="comment-ziggeo-template">
		<div class="comment-navigation">
			<span class="dashicons dashicons-video-alt"></span> Video Comments with optional text comment
		</div>
		<div id="comments-video-container">

			<input type="hidden" id="ziggeo_video_token" value="">
			<ziggeo
				<?php echo $default_recorder; ?>
				<?php //Do not allow the form to be submitted unless video is filled out ?>
				ziggeo-form_accept="#commentform"
				<?php //Capture "wordpress" and "username" as video tags. ?>
				ziggeo-tags="wordpress,<?php echo $current_user->user_login; ?>,comment">
			</ziggeo>
		</div>
		<div id="comments-text-container"></div>
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

		//Show video after recording
		ZiggeoApi.Events.on("submitted", function (data) {

			var ziggeoToken = document.getElementById('ziggeo_video_token');
			<?php
			if($template_player) {
				?>
					//Lets add the default value into the box
					ziggeoToken.value = "<?php echo $default_player ?>";
					//Now we have both the token and the template. We should search the template to see if token="" is present and if so, just insert the video token into quotes, otherwise, we would need to add the token attribute and show it up.
					if( (indexS = ziggeoToken.value.indexOf(' video') ) > -1 ||
						(indexS = ziggeoToken.value.indexOf(' ziggeo-video') ) > -1) {

						//Lets grab the ending as well
						var indexE = ziggeoToken.value.indexOf(' ', indexS);
						ziggeoToken.value = ziggeoToken.value.substr(0, indexS) + ' video="' + data.video.token + '"' + ziggeoToken.value.substr(index + indexE + 2)
					}
				<?php
			}
			//It is not tempalte that we are using, we are just saving it with the parameters..
			elseif( !empty($default_player) ) {
				?>
				ziggeoToken.value = '[ziggeo <?php echo $default_player; ?> video="' + data.video.token + '" ]';
				<?php
				//[ziggeo ziggeo-width=480 ziggeo-height=360 video="token" ]
			}
			//Fallback to the previous method of embedding. Should not come to it, but just in case it does, we have it here, ready to capture the same.
			else {
				?>
				ziggeoToken.value = '[ziggeo]' + data.video.token + '[/ziggeo]';
				<?php
			}
			?>
			document.getElementById('comment').value = document.getElementById('ziggeo_video_token').value + "\n\n" + document.getElementById('ziggeo_commentTA').value;

			elems.submitbtn.removeAttr('disabled');
		});
	</script>
	<?php
}









//If video comments are not disabled...
elseif( !isset($options["disable_video_comments"]) || (isset($options["disable_video_comments"]) && $options["disable_video_comments"] !== '1')) { ?>
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

	<?php //This should be left as default and a call made to retrieve the setup made by customer in the admin panel ?>
	<script type="text/template" id="ziggeo-recorder">
		<?php //Capture user comment as video ?>
		<ziggeo
			<?php echo $default_recorder; ?>
			<?php //Do not allow the form to be submitted unless video is filled out ?>
			ziggeo-form_accept="#commentform"
			<?php //Capture "wordpress" and "username" as video tags. ?>
			ziggeo-tags="wordpress,<?php echo $current_user->user_login; ?>,comment"
		></ziggeo>
	</script>

	<?php //JavaScript code using jQuery to switch between video and text comments ?>
	<script type="text/javascript">
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

		<?php if( !isset($options["disable_text_comments"]) || (isset($options["disable_text_comments"]) && $options["disable_text_comments"] === '1') ) { ?>
			setTimeout(function () {
				jQuery("#comments-video-link").click();
			});
		<?php } ?>
	</script>
<?php } ?>