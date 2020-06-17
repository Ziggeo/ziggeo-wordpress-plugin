<?php

//Checking if WP is running or if this is a direct call..
defined('ABSPATH') or die();

?>
<div>
	<h2>PHP SDK Functionality</h2>

	<form action="options.php" method="post">
		<?php
		wp_nonce_field('ziggeo_nonce_action', 'ziggeo_sdk_nonce');
		settings_errors();
		ziggeo_a_sdk_text();
		?>
	</form>
	<div id="ziggeo_messenger"><div id="ziggeo_message"></div><div>X</div></div>
</div>
<?php

//Function to show page content
function ziggeo_a_sdk_text() {

	//This page should give us access to various options available through SDK.

	//Devs: Let us know what you would like to see here. We want to offer you the power of PHP SDK however only if / how you want it present.

	// Benefits would include grabbing and doing stuff from the background, such as getting the list of videos into your WP website (tokens), creating auth tokens on fly, capturing webhook calls, etc.

}

?>