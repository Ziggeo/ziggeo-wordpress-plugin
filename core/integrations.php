<?php

//Checking if WP is running or if this is a direct call..
defined('ABSPATH') or die();



//Helper function to make it easier to showcase the module under Integrations tab in a uniform fashion.
function ziggeo_integration_present_me($data = null) {
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

//Need to have it for a while because of the typo :(
function zigeo_integration_present_me($data) {
	return ziggeo_integration_present_me($data);
}

function ziggeo_integration_present_me_cards($data, $end_output = '') {
	// $data = array(
	// 	//This section is related to the plugin that we are combining with the Ziggeo, not the plugin/module that does it
	// 	'integration_title'		=> 'Ziggeo Video Posts and Comments', //Name of the plugin
	// 	'integration_origin'	=> 'https://wordpress.org/plugins/ziggeo', //Where you can download it from

	// 	//This section is related to the plugin or module that is making the connection between Ziggeo and the other plugin.
	// 	'title'					=> 'Videowalls for Ziggeo', //the name of the module
	// 	'author'				=> 'Ziggeo', //the name of the author
	// 	'author_url'			=> 'https://ziggeo.com/', //URL for author website
	// 	'message'				=> 'Add videowalls to your pages by extending Ziggeo core plugin (At this time Ziggeo core supports videowalls directly, so you can not disable them. Direct core support will be removed and only this plugin will offer the same functionality)', //Any sort of message to show to customers
	// 	'status'				=> true, //Is it turned on or off?
	// 	'slug'					=> 'videowalls-for-ziggeo', //slug of the module
	// 	//URL to image (not path). Can be of the original plugin, or the bridge
	// 	'logo'					=> VIDEOWALLSZ_ROOT_URL . 'assets/images/logo.png',
	//	'version'				=> 1.0
	// );

	$active_class = 'disabled';
	if($data['status'] === true || $data['status'] === 'ON') {
		$active_class = 'enabled';
	}

	?>
	<li class="ziggeo_integrations_card <?php echo $active_class; ?>">
		<h3><?php echo $data['title']; ?></h3>
		<div><img src="<?php echo $data['logo'] ?>"></div>
		<p>
			<span class="author">By <a href="<?php echo $data['author_url']; ?>"><?php echo $data['author']; ?></a></span>
		</p>
		<a class="ziggeo-ctrl-btn" href="<?php echo $data['integration_origin']; ?>">Check out</a>
		<?php echo $end_output; ?>
	</li>
	<?php
}


//Allows a quick check if the administrator has seleted to turn off the module / bridge plugin so we know not to run it.
//Returns true by default, unless it is disabled.
function ziggeo_integration_is_enabled($slug = null) {

	if($slug === null) {
		return false;
	}

	$integrations = ziggeo_get_plugin_options('integrations');

	if(isset($integrations[$slug])) {
		//It is present in settings
		$status = $integrations[$slug]['active'];

		if($status === false || $status === 'false') {
			return false;
		}
	}

	return true;
}

?>