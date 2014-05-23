<?php
	global $current_user;
	get_currentuserinfo();
	$options = get_option('ziggeo_video');
?>
<div>
	<?php if (!@$options || !@$options["token"]) { ?>
	    <div class="update-nag">
	        <p>
	        	Haven't signed up for the Ziggeo API yet?
	        	<a href="javascript:ziggeo_onboard_submit()">
	        		Click here to automatically generate a key for <?= $current_user->user_email ?>
	        	</a>
	        </p>
	    </div>
	<?php } ?>
	<h2>Ziggeo Video Posts &amp; Comments</h2>		
    <script>
    	function ziggeo_onboard_submit() {
    		ziggeo_onboard(
    			"<?= $current_user->user_firstname . ' ' . $current_user->user_lastname ?>",
				"<?= $current_user->user_email ?>",
				function (result) {
					alert("Success! We have obtained an API key for you.\n" +
					      "Ziggeo will send you an email with a link shortly that you need to confirm.\n" + 
					      "It will also aks you to create a password to secure your Ziggeo account.");
					jQuery("#ziggeo_app_token").val(result);
					jQuery("#ziggeo_app_token").closest("form").submit();
				},
				function (err) {
					alert("We could not create an API key for you:\n" + err);
				}
			);
    	}
    </script>
	<form action="options.php" method="post">
		<?php settings_fields('ziggeo_video'); ?>
		<?php do_settings_sections('ziggeo_video'); ?>
		<input name="Submit" type="submit" value="<?php esc_attr_e('Save Changes'); ?>" />
	</form>
</div>
