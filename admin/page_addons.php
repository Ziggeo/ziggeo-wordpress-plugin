<?php

//Checking if WP is running or if this is a direct call..
defined('ABSPATH') or die();

?>
<div>
	<h2>Addons</h2>

	<form action="options.php" method="post">
		<?php
		wp_nonce_field('ziggeo_nonce_action', 'ziggeo_addons_nonce');
		settings_errors();
		ziggeo_a_addons_text();
		?>
	</form>
	<div id="ziggeo_messenger"><div id="ziggeo_message"></div><div>X</div></div>
</div>
<?php

//Function to show page content
function ziggeo_a_addons_text() {

	//Lets get all of the data

	//To get the info about all of the installed integrations
	$integrations_installed = apply_filters('ziggeo_list_integration', array());

	//Attempt to get the info about the addons
	//* I dislike using @ since it hides errors, however best option here to make sure everything works right event when getting a file over URL is not allowed
	$integrations_store = @file_get_contents('https://raw.githubusercontent.com/Ziggeo/ziggeo-wordpress-plugin/master/addons.json');
	$integrations_in_store = array();

	if($integrations_store) {
		$_t_integrations_in_store = json_decode($integrations_store, true);

		foreach($_t_integrations_in_store as $addon => $info) {

			$path = str_ireplace('https://github.com/',
								'https://raw.githubusercontent.com/',
								$info['url']['github']) .
								'/master/info.json';

			$addon_details = @file_get_contents($path); //@ because of 4XY or 5XY error codes

			if($addon_details) {
				$addon_details = json_decode($addon_details, true);
				$integrations_in_store[$addon_details['plugin']['name']] = $addon_details;
			}
		}
	}

	?>
	<div class="ziggeo-addons-toolbar" id="ziggeo-addons-nav">
		<span class="ziggeo-ctrl-btn selected" data-section="store">Addon Store</span>
		<span class="ziggeo-ctrl-btn" data-section="installed">Installed</span>
		<span class="ziggeo-ctrl-btn" data-section="updates">Update Available</span>
	</div>

	<ul id="ziggeo_addons_store">
		<?php
		if(!$integrations_in_store) {
			?><b>There was an error retrieving the data</b><?php
		}
		else {
			foreach($integrations_in_store as $addon => $addon_details) {

				$active_class = 'enabled'; //for now
				?>
				<li class="ziggeo_integrations_card <?php echo $active_class; ?>">
					<h3><?php echo $addon_details['plugin']['title']; ?></h3>
					<div class="addon_logo"><img src="<?php echo $addon_details['plugin']['logo'] ?>"></div>
					<div class="addon_description"><span><?php echo $addon_details['plugin']['description'] ?></span></div>
					<p>
						<span class="author">By <?php echo $addon_details['plugin']['author']; ?></span>
					</p>
					<span class="addon_version">Version: <?php echo $addon_details['plugin']['version']; ?></span>
					
					<?php
						$url = '';
						if($addon_details['wp']['repo_url'] !== '') {
							$url = $addon_details['wp']['repo_url'];
						}
						else {
							$url = $addon_details['wp']['alternative_repo_url'];
						}
					?>
					<a class="ziggeo-ctrl-btn" target="_blank" href="<?php echo $url; ?>">Check it out</a>
				</li>
				<?php
			}
		}
		?>
	</ul>

	<ul id="ziggeo_addons_installed" class="ziggeo_integrations_cards" style="display:none;">
		<p><?php __('Showing you all of the integrations that you have installed.', 'ziggeo'); ?></p>
		<?php
			for($i = 0, $c = count($integrations_installed); $i < $c; $i++) {
				ziggeo_integration_present_me_cards($integrations_installed[$i]);
			}
		?>
	</ul>

	<ul id="ziggeo_addons_update" style="display:none;">
		<p><?php __('Only the plugins that you have installed and new version is available will be shown here.', 'ziggeo'); ?></p>
		<?php
			$found = 0;
			for($i = 0, $c = count($integrations_installed); $i < $c; $i++) {
				//This should always be the case, however during development it can be helpful having this check :)
				if(isset($integrations_in_store[$integrations_installed[$i]['slug']])) {
					if(version_compare($integrations_installed[$i]['version'],
										$integrations_in_store[$integrations_installed[$i]['slug']]['plugin']['version'] , '<') ) {
						$found++;
						ziggeo_integration_present_me_cards($integrations_installed[$i], '<div class="ziggeo-addons-update-available"></div>');
					}
				}
			}

			if($found === 0) {
				echo '<p class="ziggeo_addons_all_updated">' . __('Everything seems to be up to date :)', 'ziggeo') . '</p>';
			}
		?>
	</ul>

	<?php
}

?>