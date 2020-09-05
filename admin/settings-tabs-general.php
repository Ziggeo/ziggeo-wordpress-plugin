<?php

//Checking if WP is running or if this is a direct call..
defined('ABSPATH') or die();

// - GENERAL - tab fields functions
//----------------------------------

//Shows instructions on how to manually get API app token and starts the general tab frame (closing the one before it)
function ziggeo_a_s_g_text() {

	$option = ziggeo_get_plugin_options('token');

	?>
	</div>
	<div class="ziggeo-frame" id="ziggeo-tab_general">
	<?php

	//Only show the instructions if the token is not already set
	if( trim($option) === '') {
		?>
		<p>
			<?php echo __('Get your Ziggeo API application token from following locations:', 'ziggeo') .
				' <a href="https://ziggeo.com/signin/" target="_blank">' .
					__('US REGION', 'ziggeo') .
				'</a>' .
				' <a href="https://eu-west-1.ziggeo.com/signin/" target="_blank">' .
					__('EU REGION', 'ziggeo') .
				'</a>' .
				'<br>';
			?>
			<span><?php _e('* Login to your account -> App -> Overview', 'ziggeo'); ?></span>
		</p>
		<?php
	}
}

	//Token input
	function ziggeo_a_s_g_app_token_field() {
		$options = ziggeo_get_plugin_options();

		?>
		<input id="ziggeo_app_token" name="ziggeo_video[token]" size="50" type="text" placeholder="<?php _ex('Your app token goes here', 'placeholder for APP token', 'ziggeo'); ?>" value="<?php echo $options['token']; ?>" />
		<?php

		//Lets check feedback. We will keep it hidden on the form so that we can show it in a nice manner ;)
		// (not as some other option)
		if( $options['feedback'] !== ZIGGEO_YES ) {
			
			//We wil also not show it right away, but only if the customer had some time to check it out, so at least a token should be set.
			
			//Only show the instructions if the token is not already set
			if( trim($options['token']) !== '' ) {
				?>
				<div class="ziggeo_hidden">
					<input id="ziggeo_feedback" name="ziggeo_video[feedback]" type="checkbox" value="1">
				</div>
				<div id="ziggeo_feedback_banner">
					<span><?php
						echo _x('We hope that you like the plugin. If you do, consider letting us know by', 'Plugin settings - general tab - feedback banner 1/X', 'ziggeo') .
						' <a target="_blank" href="https://wordpress.org/support/view/plugin-reviews/ziggeo">' .
						_x('leaving a feedback on WordPress plugin page', 'Plugin settings - general tab - feedback banner 2/X', 'ziggeo') .
						'</a> ' .
						_x('That will help us and tell us that you want us to keep improving the plugin. Already did? Great - just', 'Plugin settings - general tab - feedback banner 3/X', 'ziggeo') . 
						' <a href="javascript:ziggeoPUIFeedbackRemoval();" title="' .
							_x('This will submit this page causing it to reload so that you are no longer shown this notice', 'Plugin settings - general tab - feedback banner - title', 'ziggeo') .
						'">' .
						_x('click here', 'Plugin settings - general tab - feedback banner 4/X', 'ziggeo') .
						'</a>'; ?>
					</span>
				</div>
				<?php
			}
		}
	}

	//Creates a field that allows us to set up the default language that your embeddings should use. By default the "auto" is used, which would pick up the language of the browser and show you that one.
	//https://ziggeo.com/features/language-support
	function ziggeo_a_s_g_default_language() {

		$lang = ziggeo_get_plugin_options('default_lang');

		$languages = array(
			'auto'	=> 'Auto',
			'ar'	=> 'Arabic',
			'az'	=> 'Azerbaijani',
			'bg'	=> 'Bulgarian',
			'cat'	=> 'Catalan',
			'zh-cn'	=> 'Chinese (China)',
			'zh-tw'	=> 'Chinese (Taiwan)',
			'hr'	=> 'Croatian',
			'da'	=> 'Danish',
			'nl'	=> 'Dutch',
			'fi'	=> 'Finnish',
			'fr'	=> 'French',
			'de'	=> 'German',
			'el'	=> 'Greek',
			'hi'	=> 'Hindi',
			'hu'	=> 'Hungarian',
			'id'	=> 'Indonesian',
			'it'	=> 'Italian',
			'no'	=> 'Norwegian',
			'pl'	=> 'Polish',
			'pt'	=> 'Portuguese',
			'pt-br'	=> 'Portuguese (Brazil)',
			'ro'	=> 'Romanian',
			'ru'	=> 'Russian',
			'sr'	=> 'Serbian',
			'sk'	=> 'Slovak',
			'sl'	=> 'Slovenian',
			'es'	=> 'Spanish',
			'sv'	=> 'Swedish',
			'tl'	=> 'Tagalog',
			'tr'	=> 'Turkish'
		);

		?>
		<select id="ziggeo_default_language" name="ziggeo_video[default_lang]">
			<?php
				foreach($languages as $iso => $label) {
					if($iso === $lang) {
						?>
						<option value="<?php echo $iso; ?>" selected><?php echo $label; ?></option>
						<?php
					}
					else {
						?>
						<option value="<?php echo $iso; ?>"><?php echo $label; ?></option>
						<?php
					}
				}
			?>
		</select>
		<label for="ziggeo_default_language"><?php _e('Select the default language for your website. Leave auto unless you want to use a specific language all the time.'); ?></label>
		<?php
	}

	//Default recorder templates to be used by integrations
	function ziggeo_a_s_g_default_integrations_recorder_field() {
		$option = ziggeo_get_plugin_options('integrations_recorder_template');

		?>
		<select id="integrations_recorder_template" name="ziggeo_video[integrations_recorder_template]">
			<option value=""><?php _ex('Default', 'dropdown option of default/unchanged value', 'ziggeo'); ?></option>
			<?php
				$list = ziggeo_p_templates_index();
				if($list) {
					foreach($list as $template => $value) {
						if( $template === $option ) {
							?><option value="<?php echo $template; ?>" selected><?php echo $template; ?></option><?php
						}
						else {
							?><option value="<?php echo $template; ?>"><?php echo $template; ?></option><?php
						}
					}
				}
			?>
		</select>
		<label for="integrations_recorder_template">
			<?php __('Set up which template should integrations pick up and use.', 'ziggeo'); ?>
		</label>
		<?php
	}

	//Default player templates to be used by integrations
	function ziggeo_a_s_g_default_integrations_player_field() {
		$option = ziggeo_get_plugin_options('integrations_player_template');

		?>
		<select id="integrations_player_template" name="ziggeo_video[integrations_player_template]">
			<option value=""><?php _ex('Default', 'dropdown option of default/unchanged value', 'ziggeo'); ?></option>
			<?php
				$list = ziggeo_p_templates_index();
				if($list) {
					foreach($list as $template => $value) {
						if( $template === $option ) {
							?><option value="<?php echo $template; ?>" selected><?php echo $template; ?></option><?php
						}
						else {
							?><option value="<?php echo $template; ?>"><?php echo $template; ?></option><?php
						}
					}
				}
			?>
		</select>
		<label for="integrations_player_template">
			<?php __('Set up which template should integrations pick up and use.', 'ziggeo'); ?>
		</label>
		<?php
	}

	//Used for styling purposes only.
	function ziggeo_a_s_g_comments_html() {
		?>
		<hr class="ziggeo_linespacer">
		<span class="ziggeo-subframe"><?php _e('Comments options', 'ziggeo'); ?></span>
		<?php
	}

	//Allows us to select if video comments are accepted on a post where comments are enabled
	function ziggeo_a_s_g_accept_video_comments_field() {
		$option = ziggeo_get_plugin_options('disable_video_comments');

		?>
		<input id="ziggeo_video_comments" name="ziggeo_video[disable_video_comments]" type="checkbox" value="1"
			<?php echo checked( 1, $option, false ); ?> />
		<label for="ziggeo_video_comments">
			<?php _e('By default the comments will get activated with the feature to add videos as comments (check this to disable it)', 'ziggeo');
			?>
		</label>
		<?php
	}

	//Show video (and it is required) and the WordPress comment field next to it
	function ziggeo_a_s_g_video_comments_required_with_text() {
		$option = ziggeo_get_plugin_options('video_and_text');

		?>
		<input id="ziggeo_video_and_text" name="ziggeo_video[video_and_text]" type="checkbox" value="1"
			<?php echo checked( 1, $option, false ); ?> />
		<label for="ziggeo_video_and_text">
			<?php _e('Set video comment to be required, but allow your visitors to leave some text for you as well (next to video)', 'ziggeo');
			?>
		</label>
		<?php   
	}

	//Recorder template option to be used in comments
	function ziggeo_a_s_g_video_comments_default_recorder_field() {
		$option = ziggeo_get_plugin_options('comments_recorder_template');

		?>
		<select id="ziggeo_video_comments_template_recorder" name="ziggeo_video[comments_recorder_template]">
			<option value=""><?php _ex('Default', 'dropdown option of default/unchanged value', 'ziggeo'); ?></option>
		<?php
			$list = ziggeo_p_templates_index();
			if($list) {
				foreach($list as $template => $value)
				{
					if( $template === $option ) {
						?><option value="<?php echo $template; ?>" selected><?php echo $template; ?></option><?php
					}
					else{
						?><option value="<?php echo $template; ?>"><?php echo $template; ?></option><?php
					}
				}
			}
		?>
		</select>
		<label for="ziggeo_video_comments_template_recorder">
			<?php _e('This template will be applied to all comment recorders (it can be uploader template, recorder, ... the choice is yours)', 'ziggeo');
			?>
		</label>
		<?php
	}

	//Player template option to be used in comments
	function ziggeo_a_s_g_video_comments_default_player_field() {
		$option = ziggeo_get_plugin_options('comments_player_template');

		?>
		<select id="ziggeo_video_comments_template_player" name="ziggeo_video[comments_player_template]">
			<option value=""><?php _ex('Default', 'dropdown option of default/unchanged value', 'ziggeo'); ?></option>
			<?php
				$list = ziggeo_p_templates_index();
				if($list) {
					foreach($list as $template => $value) {
						?><option value="<?php echo $template; ?>" <?php ziggeo_echo_selected($template, $option); ?>><?php echo $template; ?></option><?php
					}
				}
			?>
		</select>
		<label for="ziggeo_video_comments_template_player"><?php _e('This template will be applied to all comment players (player, rerecorder maybe? ... the choice  is yours )'); ?></label>
		<?php
	}

	//Allows us to set so that text comments are available or disabled. Useful if one wants to have only video comments. Applied only if comments are enabled.
	function ziggeo_a_s_g_accept_video_comments_only_field() {
		$option = ziggeo_get_plugin_options('disable_text_comments');

		?>
		<input id="ziggeo_text_comments" name="ziggeo_video[disable_text_comments]" type="checkbox" value="1"
			<?php echo checked( 1, $option, false ); ?> />
		<label for="ziggeo_text_comments">
			<?php _e('Want to have video comments only? Check this to set it as such ( leave unchecked to allow video and text comments ).', 'ziggeo');
			?>
		</label>
		<?php
	}

	function ziggeo_a_s_g_video_comments_required_roles() {
		$option = ziggeo_get_plugin_options('comment_roles');

		//Roles:
		//SuperAdmin = 6
		//Admin = 5
		//Editor = 4
		//Author = 3
		//Contributor = 2
		//Subscriber = 1
		//Everyone (guest) = 0 || ""
		?>
		<select id="ziggeo_video_comments_roles" name="ziggeo_video[comment_roles]">
			<option value="0" <?php ziggeo_echo_selected('', $option) . ziggeo_echo_selected(0, $option); ?>>
				<?php _ex('Everyone is allowed', 'WP roles','ziggeo'); ?></option>
			<option value="1" <?php ziggeo_echo_selected(1, $option); ?>><?php _ex('Subscribers', 'WP roles', 'ziggeo'); ?>
			</option>
			<option value="2" <?php ziggeo_echo_selected(2, $option); ?>><?php _ex('Contributors', 'WP roles', 'ziggeo'); ?></option>
			<option value="3" <?php ziggeo_echo_selected(3, $option); ?>><?php _ex('Authors', 'WP roles', 'ziggeo'); ?></option>
			<option value="4" <?php ziggeo_echo_selected(4, $option); ?>><?php _ex('Editors', 'WP roles', 'ziggeo'); ?></option>
			<option value="5" <?php ziggeo_echo_selected(5, $option); ?>><?php _ex('Admins', 'WP roles', 'ziggeo'); ?></option>
			<option value="6" <?php ziggeo_echo_selected(6, $option); ?>><?php _ex('Super Admins', 'WP roles', 'ziggeo'); ?></option>
		</select>
		<label for="ziggeo_video_comments_roles">
			<?php _e('By selecting some role you allow people with the same role and higher one to use video comments, while it is disabled for everyone else (with lower capabilities).', 'ziggeo'); ?>
		</label>
		<?php
	}


	//Used for styling only
	function ziggeo_a_s_g_global_html() {
		?>
		<hr class="ziggeo_linespacer">
		<span class="ziggeo-subframe"><?php _e('Global & Default options', 'ziggeo'); ?></span>
		<?php
	}

	//Used as defaults - fallbacks :)
	function ziggeo_a_s_g_fallback_recorder_config_field() {
		$option = ziggeo_get_plugin_options('recorder_config');

		?>
		<input id="ziggeo_recorder_config" name="ziggeo_video[recorder_config]" size="50" type="text"
			placeholder="<?php _e('Ziggeo Recorder Config (leave blank for default settings)', 'ziggeo'); ?>"
			value="<?php echo $option; ?>" />
		<?php
	}

	//Used as defaults - fallbacks :)
	function ziggeo_a_s_g_fallback_player_config_field() {
		$option = ziggeo_get_plugin_options('player_config');

		?>
		<input id="ziggeo_player_config" name="ziggeo_video[player_config]" size="50" type="text"
			placeholder="<?php _e('Ziggeo Player Config (leave blank for default settings)','ziggeo'); ?>"
			value="<?php echo $option; ?>" />
		<?php
	}


	//VAST related settings
	function ziggeo_a_s_g_vast_global_field() {
		?>
		<hr class="ziggeo_linespacer">
		<span class="ziggeo-subframe"><?php _e('VAST (ads) options', 'ziggeo'); ?></span>
		<?php
	}

	//The URL that is used to get the ad
	function ziggeo_a_s_g_vast_adserver_field() {
		$option = ziggeo_get_plugin_options('vast_adserver');

		?>
		<input id="ziggeo_vast_adserver" name="ziggeo_video[vast_adserver]" size="50" type="text"
			placeholder="<?php _e('AdServer URL goes here','ziggeo'); ?>"
			value="<?php echo $option; ?>" />
		<?php
	}

	//The number of seconds to show the SKIP AD button after
	function ziggeo_a_s_g_vast_skipafter_field() {
		$option = ziggeo_get_plugin_options('vast_skipafter');

		?>
		<input id="ziggeo_vast_skipafter" name="ziggeo_video[vast_skipafter]" size="50" type="number"
			placeholder="<?php _e('Set in seconds','ziggeo'); ?>"
			value="<?php echo $option; ?>" />
		<?php
	}

	//Allows us to set if the ad should be muted when run or not
	function ziggeo_a_s_g_vast_muted_field() {
		$option = ziggeo_get_plugin_options('vast_muted');

		?>
		<input id="ziggeo_vast_muted" name="ziggeo_video[vast_muted]" type="checkbox" value="1"
			<?php echo checked( 1, $option, false ); ?> />
		<?php
	}

	//The Ad title
	function ziggeo_a_s_g_vast_ad_title_field() {
		$option = ziggeo_get_plugin_options('vast_ad_title');

		?>
		<input id="ziggeo_vast_ad_title" name="ziggeo_video[vast_ad_title]" size="50" type="text"
			placeholder="<?php _e('Ad Title','ziggeo'); ?>"
			value="<?php echo $option; ?>" />
		<?php
	}

	//The Ad description
	function ziggeo_a_s_g_vast_ad_description_field() {
		$option = ziggeo_get_plugin_options('vast_ad_description');

		?>
		<input id="ziggeo_vast_ad_description" name="ziggeo_video[vast_ad_description]" size="50" type="text"
			placeholder="<?php _e('Ad Description','ziggeo'); ?>"
			value="<?php echo $option; ?>" />
		<?php
	}

	//The Ad ID
	function ziggeo_a_s_g_vast_ad_id_field() {
		$option = ziggeo_get_plugin_options('vast_ad_id');

		?>
		<input id="ziggeo_vast_ad_id" name="ziggeo_video[vast_ad_id]" size="50" type="text"
			placeholder="<?php _e('Ad ID','ziggeo'); ?>"
			value="<?php echo $option; ?>" />
		<?php
	}

	//The Ad ID
	function ziggeo_a_s_g_vast_ad_advertiser_field() {
		$option = ziggeo_get_plugin_options('vast_ad_advertiser');

		?>
		<input id="ziggeo_vast_ad_advertiser" name="ziggeo_video[vast_ad_advertiser]" size="50" type="text"
			placeholder="<?php _e('Ad Advertiser','ziggeo'); ?>"
			value="<?php echo $option; ?>" />
		<?php
	}



?>