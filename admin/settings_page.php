<?php

//Checking if WP is running or if this is a direct call..
defined('ABSPATH') or die();

?>
<div>

	<h2>Ziggeo Video Posts &amp; Comments</h2>

	<form action="options.php" method="post">
		<?php
		wp_nonce_field('ziggeo_nonce_action', 'ziggeo_video_nonce');
		settings_errors();
		settings_fields('ziggeo_video');
		do_settings_sections('ziggeo_video');
		submit_button('Save Changes');
		?>
	</form>
	<div id="ziggeo_messenger"><div id="ziggeo_message"></div><div>X</div></div>
</div>
<?php

?>