<?php

//Checking if WP is running or if this is a direct call..
defined('ABSPATH') or die();

// - INTEGRATIONS - tab fields functions
//-------------------------------------

//Function to show the integrations frame
function ziggeo_a_s_i_text() {
	//First, we end the previous tab..
	?>
	</div>
	<div class="ziggeo-frame" style="display: none;" id="ziggeo-tab_integrations">
		<p><?php __('A place where you can connect your Ziggeo plugin with different plugins you use in your WordPress website.', 'ziggeo'); ?></p>
		<p><?php __('Have something you want to see here and it is not yet shown? Let us know!', 'ziggeo'); ?></p>

	<ul class="ziggeo_integrations_list">
		<?php do_action('ziggeo_list_integration'); ?>
	</ul>

	<?php
}

	function ziggeo_a_s_i_change_status_field() {
		?>
		<div style="display:none;">
			<input id="ziggeo_integration_change" value="" name="ziggeo_video[integration_change]">
		</div>
		<?php
	}
?>