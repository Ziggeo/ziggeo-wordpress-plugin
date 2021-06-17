<?php

//Checking if WP is running or if this is a direct call..
defined('ABSPATH') or die();

$msg = '';

//Include the SDK if it is not included already;
if(ziggeo_p_include_sdk()) {
	ziggeo_p_sdk_init();
}
else {
	//The SDK was not possible to include for some reason. We should add a message about this.
	$msg = _e('The PHP SDK was not found or was not possible to include it. Please make sure that you have added it to your system and that file permissions are set properly.', 'ziggeo');
}

?>
<div>
	<h2>PHP SDK Functionality <span id="ziggeo_title_app">(Default Application)</span></h2>

	<form action="options.php" method="post">
		<?php
		wp_nonce_field('ziggeo_nonce_action', 'ziggeo_sdk_nonce');
		settings_errors();

		if($msg !== '') {
			?>
			<b><?php echo $msg; ?></b>
			<?php
		}

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

	//Here we could include the PHP SDK and have the index call made internally. This would be the safest way to do it
	//For now we go with JS implementation

	$ziggeo_sdk = ziggeo_p_sdk_get_object(true);
	$plugin_options = ziggeo_get_plugin_options();

	?>
	<div id="ziggeo-sdk-pages">
		<div class="ziggeo_tabs">
			<div class="ziggeo_tab selected" data-tab="welcome">Welcome</div>
			<div class="ziggeo_tab" data-tab="applications">Applications</div>
			<div class="ziggeo_tab" data-tab="analytics">Analytics</div>
			<div class="ziggeo_tab" data-tab="authtokens">Auth Tokens</div>
			<div class="ziggeo_tab" data-tab="effectprofiles">Effect Profiles</div>
			<div class="ziggeo_tab" data-tab="metaprofiles">Meta Profiles</div>
			<div class="ziggeo_tab" data-tab="webhooks">Webhooks</div>
		</div>
		<?php
			/*
				Effect Profiles (create, apply)
				Auth tokens (create, update, get, delete) * server side + client side
			*/
		?>
		<div class="ziggeo-frame" style="" id="ziggeo_tab_welcome">
			<p>
				<?php
					_e('Welcome to the pages utilizing Ziggeo PHP SDK in the background.', 'ziggeo');
				?>
			</p>
			<p>
				<?php
					_e('By default we are showing you the data for the application you have added into your Wordpress settings. If you want to add more than one application that will be possible through available options.', 'ziggeo');
				?>
			</p>
		</div>
		<div class="ziggeo-frame" style="display:none;" id="ziggeo_tab_applications">
			<p>
				<?php
					_e('This section will help you see details about your application(s).', 'ziggeo');
				?>
			</p>
			<?php
				$tokens = ziggeo_p_get_keys();

				//note: This code is connected to ziggeo_a_sdk_page_application_new_keys() so if any change happens to <li> bellow it should be reflected there as well.
			?>
			<select id="applications_list"
				class="ziggeo-sdk-ajax-dropdown"
			    data-action="applications_get_detail"
			    data-operation="sdk_applications"
			    data-results="applications_detail"
			    data-value="token:applications_list">
				<?php
					for($i = 0, $c = count($tokens); $i < $c; $i++) {
						//echo '<li value="' . $tokens[$i]['app_token'] . '" tabIndex="0">' . $tokens[$i]['title'] . '</li>';
						echo '<option value="' . $tokens[$i]['app_token'] . '">' . $tokens[$i]['title'] . '</option>';
					}
				?>
			</select>
			<div id="applications_detail">

				<?php echo ziggeo_a_sdk_page_applications_get(); ?>

			</div>
		</div>
		<div class="ziggeo-frame" style="display:none;" id="ziggeo_tab_analytics">
			Note: This requires Pro plan or higher. For your security we do not expose details such as to what plan some application belongs to nor if two applications are within same account. If you do not have the right plan, the bellow options will simply not work for you.

			<div>
				<input id="analytics-from" type="hidden">
				<input id="analytics-to" type="hidden">
			</div>
			<div class="calendars">
				<div>
					<label>From</label>
					<label>To</label>
				</div>
				<div id="calendar_from"></div>
				<div id="calendar_to"></div>
				<br>
				<span class="ziggeo-ctrl-btn ziggeo-sdk-ajax"
					data-action="analytics_get"
					data-operation="sdk_analytics"
					data-value="from:analytics-from,to:analytics-to,token:{app_token}"
					data-results="{ziggeoPUISDKAnalyticsCreateGraphs}"
					data-validate="notempty:analytics-from">Analize</span>
				<p>Please wait (this can take a minute or two)</p>
			</div>
			<div id="analytics_data" class="graphs hidden">
				<div class="ziggeo-graph">
					<canvas id="ziggeo_graph_device_views_by_os" data-type="view:os">
					</canvas>
				</div>
				<div class="ziggeo-graph">
					<canvas id="ziggeo_graph_device_views_by_date" data-type="view:date">
					</canvas>
				</div>
				<div class="ziggeo-graph">
					<canvas id="ziggeo_graph_total_plays_by_country" data-type="play:country">
					
					</canvas>
				</div>
				<div class="ziggeo-graph">
					<canvas id="ziggeo_graph_full_plays_by_country" data-type="full_play:country">
					</canvas>
				</div>
				<div class="ziggeo-graph">
					<canvas id="ziggeo_graph_total_plays_by_hour" data-type="play:hour">
					</canvas>
				</div>
				<div class="ziggeo-graph">
					<canvas id="ziggeo_graph_full_plays_by_hour" data-type="full_play:hour">
					</canvas>
				</div>
				<div class="ziggeo-graph">
					<canvas id="ziggeo_graph_total_plays_by_browser" data-type="play:browser">
					</canvas>
				</div>
				<div class="ziggeo-graph">
					<canvas id="ziggeo_graph_full_plays_by_browser" data-type="full_play:browser">
					</canvas>
				</div>
				<div class="ziggeo-graph big" style="display:none;">
					<canvas id="ziggeo_graph_big"></canvas>
				</div>
			</div>
		</div>
		<div class="ziggeo-frame" style="display:none;" id="ziggeo_tab_authtokens">
		</div>
		<div class="ziggeo-frame" style="display:none;" id="ziggeo_tab_effectprofiles">
			<p>
				<?php
					_e('This section will help you see all of the effect profiles, create new, update and remove the existing ones.', 'ziggeo');
				?>
			</p>

			<div id="effect_profile_create">
				<h2>Create Effect Profile</h2>
				<label for="effect_profile_create_key">Key:</label>
				<input id="effect_profile_create_key" type="text" placeholder="optional"><br>

				<label for="effect_profile_create_title" data-required="true">Title:</label>
				<input id="effect_profile_create_title" type="text" placeholder="only visible to you"><br>

				<label for="effect_profile_create_default" type="checkbox">Create default stream using this effect profile?</label>
				<input id="effect_profile_create_default" type="checkbox"><br>

				<label for="effect_profile_create_image_only" type="checkbox">Create image only stream using this effect profile?</label>
				<input id="effect_profile_create_image_only" type="checkbox"><br>

				<span class="ziggeo-ctrl-btn ziggeo-sdk-ajax-form"
				      data-keys="key, title, default, image_only"
				      data-section="effect_profile_create"
				      data-action="effect_profile_create"
				      data-operation="sdk_effect_profiles"
				      data-update-type="clear">Create Effect Profile</span>
			</div>

			<hr>

			<div id="effect_profile_list">
			</div>
			<span class="ziggeo-ctrl-btn"
			     data-action="effect_profile_get_all"
			     data-operation="sdk_effect_profiles"
			     data-results="effect_profile_list">Get All Effect Profiles</span>
		</div>
		<div class="ziggeo-frame" style="display:none;" id="ziggeo_tab_metaprofiles">
		</div>
		<div class="ziggeo-frame" style="display:none;" id="ziggeo_tab_webhooks">
		</div>
	</div>

	<?php
}






?>