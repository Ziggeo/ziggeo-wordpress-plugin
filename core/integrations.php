<?php

//Checking if WP is running or if this is a direct call..
defined('ABSPATH') or die();



//Helper function to make it easier to showcase the module under Integrations tab in a uniform fashion.
function zigeo_integration_present_me($data = null) {
	if($data === null) {
		return false;
	}

	?>
		<li class="ziggeo_integrations_row">
			<div class="ziggeo_integration_left">
				<img class="ziggeo_integration_logo" src="<?php echo $data['logo']; ?>">
			</div>
			<div class="integration">
				<b><?php _e('Integration for:', 'ziggeo'); ?></b> <strong><?php echo $data['integration_title']; ?></strong><?php _e(' - you can get it from ', 'ziggeo'); ?><a href="<?php echo $data['integration_origin']; ?>" target="_blank"><?php echo $data['integration_origin']; ?></a>
				<br>
			</div>
			<div class="bridge">
			</div>
			<div class="author">
				<b><?php _e('Author:', 'ziggeo'); ?></b> <?php echo $data['author']; ?>
				<?php
					if( isset($data['author_url']) && $data['author_url'] !== '') {
						?>
						@ <a href="<?php echo $data['author_url']; ?>" target="_blank"><?php echo $data['author_url']; ?></a><br>
					<?php
					}
				?>
			</div>
			<div class="message">
				<?php
					if( isset($data['message']) && $data['message'] !== '') {
						?>
						<b><?php _e('Author message:', 'ziggeo'); ?></b>
							<?php echo $data['message']; ?>
						<?php
					}
				?>
			</div>
			<div>
				<?php 
				if($data['status'] === true || $data['status'] === 'ON') {
					?>
					<button class="integration_button active" disabled="disabled">Active</button>
					<button class="integration_button" onclick="ziggeoPUIIntegrationStatus('<?php echo $data['slug']; ?>', 'disable');">Disable</button>
					<?php
				}
				else {
					?>
					<button class="integration_button" onclick="ziggeoPUIIntegrationStatus('<?php echo $data['slug']; ?>', 'activate');">Activate</button>
					<button class="integration_button disabled" disabled="disabled">Disabled</button>
					<?php
				}
				?>
			</div>
		</li>
	<?php
}

//Allows a quick check if the administrator has seleted to turn off the module / bridge plugin so we know not to run it.
//Returns true by default, unless it is disabled.
function ziggeo_integration_is_enabled($slug = null) {

	if($slug === null) {
		return false;
	}

	$opts = get_option('ziggeo_video');

	if(isset($opts['integrations'], $opts['integrations'][$slug])) {
		//It is present in settings
		$status = $opts['integrations'][$slug]['active'];

		if($status === false || $status === 'false') {
			return false;
		}
	}

	return true;
}

?>