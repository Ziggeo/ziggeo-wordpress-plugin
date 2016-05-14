<?php

//Checking if WP is running or if this is a direct call..
defined('ABSPATH') or die();

	global $wp_version;

	if( version_compare( $wp_version, '4.5') >= 0 ) {
		$current_user = wp_get_current_user();
	}
	else {
		global $current_user;
		get_currentuserinfo();		
	}

	$options = get_option('ziggeo_video');
?>
<div>
	<?php if( !isset($options, $options["token"]) ) { ?>
	    <div class="update-nag">
	        <p>
	        	Haven't signed up for the Ziggeo API yet?
	        	<a href="javascript:ziggeo_onboard_submit()">
	        		Click here to automatically generate a key for <?= $current_user->user_email ?>
	        	</a>
	        </p>
	    </div>
		<?php /* We might want to add this into some js file instead and add in it head, although it is small, but just to keep it clean */ ?>
		<script type="text/javascript">
			function ziggeo_onboard_submit() {
				ziggeo_onboard(
					"<?= $current_user->user_firstname . ' ' . $current_user->user_lastname ?>",
					"<?= $current_user->user_email ?>",
					function (result) {
						alert("Success! We have obtained an API key for you.\n" +
							  "Ziggeo will send you an email with a link shortly that you need to confirm.\n" + 
							  "It will also ask you to create a password to secure your Ziggeo account.");
						jQuery("#ziggeo_app_token").val(result);
						jQuery("#ziggeo_app_token").closest("form").submit();
					},
					function (err) {
						alert("We could not create an API key for you:\n" + err);
					}
				);
			}
		</script>
	<?php } ?>
	<h2>Ziggeo Video Posts &amp; Comments</h2>

	<form action="options.php" method="post">
		<?php wp_nonce_field('ziggeo_nonce_action', 'ziggeo_video_nonce'); ?>
		<?php get_settings_errors(); ?>
		<?php settings_fields('ziggeo_video'); ?>
		<?php do_settings_sections('ziggeo_video'); ?>

		<input name="Submit" type="submit" value="<?php esc_attr_e('Save Changes'); ?>" />

	</form>
</div>
