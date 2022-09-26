<?php

//Checking if WP is running or if this is a direct call..
defined('ABSPATH') or die();


function ziggeo_p_page_header() {

	if(defined('ZIGGEO_PARSED_HEADER')) {
		return;
	}

	$options = ziggeo_get_plugin_options();

	//use add_filter('ziggeo_setting_header_code', 'your-function-name') to change the options on fly if wanted
	// it needs to return modified $options array.
	$options = apply_filters('ziggeo_setting_header_code', $options);

	?>
	<!-- Ziggeo API code - START -->
	<script type="text/javascript">

		//Function to just add the main entry in the namespace, so that we keep everything within it, instead of having many things outside of it as we do now.
		window.ZiggeoDefer = true; // We use this to stop loading of embeddings right away
		var ZiggeoWP = {
			//DEVS: Make sure you add comma behind your code or it would cause issue
			<?php do_action('ziggeo_add_to_ziggeowp_object'); ?>

			//Ajax nonce
			ajax_nonce: "<?php echo wp_create_nonce('ziggeo_ajax_nonce'); ?>",
			ajax_url: "<?php echo admin_url('admin-ajax.php'); ?>",

			//Integrations info
			integrations_code_recorder: "<?php echo ziggeo_p_get_template_code_safe($options['integrations_recorder_template']); ?>",
			integrations_code_player: "<?php echo ziggeo_p_get_template_code_safe($options['integrations_player_template']); ?>",
			format_date: "<?php echo get_option('date_format') ?>",
			format_time: "<?php echo get_option('time_format') ?>",

			//Hooks object
			hooks: {
				_hooks: {},

				//hook_name	=> hook identifier that associated functions and hooks / execution places (it can be string or array of strings)
				//				* when it is an array it will use each hook to associate the very same function with the hook
				//f_key		=> unique function name or made up key. Helps with anonimous functions so they are not set multiple times
				//f_unction	=> anonimous function to execute
				//priority	=> what priority should the function fire at (@ADD - not yet added)
				set: function(hook_name, f_key, f_unction, priority) {

					if(typeof(hook_name) == 'object') {
						for(i = 0, c = hook_name.length; i < c; i++) {
							this.set(hook_name[i], f_key, f_unction, priority);
						}

						return true;
					}

					if(typeof(priority) === 'undefined' || priority === null || isNaN(priority) || priority > 100) {
						//Core < 10 (anything that has to run first)
						//normal/default === 10
						//late > 10 (anything that should run last)
						priority = 10;
					}

					//have we already set this one?
					if( typeof ZiggeoWP.hooks._hooks[hook_name] != 'undefined' &&
						typeof ZiggeoWP.hooks._hooks[hook_name][priority] != 'undefined') {

						for(i2 = 0, c2 = ZiggeoWP.hooks._hooks[hook_name][priority].length; i2 < c2; i2++) {
							if(ZiggeoWP.hooks._hooks[hook_name][priority][i2].key === f_key) {
								return false;
							}
						}
					}

					if(typeof(ZiggeoWP.hooks._hooks[hook_name]) == 'undefined') {
						ZiggeoWP.hooks._hooks[hook_name] = {};
					}

					if(typeof ZiggeoWP.hooks._hooks[hook_name][priority] === 'undefined') {
						ZiggeoWP.hooks._hooks[hook_name][priority] = [];
					}

					ZiggeoWP.hooks._hooks[hook_name][priority].push( {key: f_key, func: f_unction} );

					//all set and good, so lets return true
					return true;
				},

				//will check all of the hooks and fire them one after another
				fire: function(hook_name, __data) {
					if( typeof(ZiggeoWP.hooks._hooks[hook_name]) != 'undefined') {

						var i, c; //leave this or "i" will be broken

						for(priority in ZiggeoWP.hooks._hooks[hook_name]) {

							for(i = 0, c = ZiggeoWP.hooks._hooks[hook_name][priority].length; i < c; i++) {

								//final sanity if the function is still available..
								if( typeof(ZiggeoWP.hooks._hooks[hook_name][priority][i]) != 'undefined') {
									try {
										//__data is passed by reference. This allows you to modify it in one of the hooks
										// It also means that you should not use __data or it will clear entire object.
										//ZiggeoWP.hooks._hooks[hook_name][i].func(__data);
										ZiggeoWP.hooks._hooks[hook_name][priority][i].func(__data);
									}
									catch(error) {
										ziggeoDevReport(error, 'error');
									}
								}
							}
						}
					}
				},

				//remove the specific hook and function
				remove: function(hook_name, function_name) {

				}
			}
		};

		<?php
			//Lets check everything so that our header is pre-set with defaults and
			// only the over rides are output. Also helps pre-define recommended settings
			$str_auth = '';
			$str_webrtc_mobile = '';
			$str_webrtc_streaming = '';
			$str_webrtc_streaming_needed = '';
			$str_debug = '';

			if($options['use_auth'] === ZIGGEO_YES) {
				$str_auth = ',' . "\n\t" . 'auth: true';
			}

			if($options['webrtc_for_mobile'] === ZIGGEO_YES) {
				$str_webrtc_mobile = ',' . "\n\t" . 'webrtc_on_mobile: true';
			}

			if($options['webrtc_streaming'] === ZIGGEO_YES) {
				$str_webrtc_streaming = ',' . "\n\t" . 'webrtc_streaming: true';
			}

			if($options['webrtc_streaming_needed'] === ZIGGEO_YES) {
				$str_webrtc_streaming_needed = ',' . "\n\t" . 'webrtc_streaming_if_necessary: true';
			}

			if($options['use_debugger'] === ZIGGEO_YES) {
				$str_debug = ',' . "\n\t" . 'debug: true';
			}
		?>

		//function to get app options
		function ziggeoGetApplicationOptions() {

			return {
				token: "<?php echo $options['token']; ?>"<?php

				echo $str_auth;
				echo $str_webrtc_mobile;
				echo $str_webrtc_streaming;
				echo $str_webrtc_streaming_needed;
				echo $str_debug;

				//Action to add your own codes into application creation. This could be used for adding screen recording plugins, URL to flash and any other setting that might not be available in our Wordpress plugin yet is available by Ziggeo system itself.
				//DO NOT use this for your own options. Instead use ZiggeoWP for that.
				do_action('ziggeo_echo_application_settings');
				?>
			}
		}

		if(typeof ZiggeoApi !== 'undefined') {
			//Just so there is no error in cases where other plugins remove our scripts

			//Set the V2 application
			window.ziggeo_app = new ZiggeoApi.V2.Application( ziggeoGetApplicationOptions() );
			ZiggeoApi.V2.Application.undefer();

			<?php
			//Language options
			//@add translations options here
			if($options['default_lang'] !== "auto") {
				?>
				ZiggeoApi.V2.Locale.setLocale("<?php echo $options['default_lang']; ?>");
				<?php
			}

			//developer feature
			if($options['dev_mode']) {
				//This allows you to get some additional feedback into the console. Turning off this option is recommended in the production (not needed), since that will hide any info from the browser / dev console.
				?>
				var ziggeo_dev = <?php echo ($options['dev_mode'] === ZIGGEO_YES) ? 'true' : 'false'; ?>;
				<?php
			}

			//Set up VAST header if we have the info for it
			if(!empty($options['vast_adserver'])) {
				?>
				//Set up VAST
				var ziggeo_vast = new ZiggeoApi.V2.Ads.AdSenseVideoAdProvider({
					provider: 'ziggeo_vast',
					adTagUrl: '<?php echo $options['vast_adserver']; ?>',
					muted: <?php echo ((int)$options['vast_muted']) === 1 ? 'true' : 'false' ?>,
					skipAfter: <?php echo (int)$options['vast_skipafter']; ?>,
					<?php
					if (!empty($options['vast_ad_title'])) {
						?>
						title: '<?php echo $options['vast_ad_title']; ?>',
						<?php
					}
					if (!empty($options['vast_ad_description'])) {
						?>
						description: '<?php echo $options['vast_ad_description']; ?>',
						<?php
					}
					if (!empty($options['vast_ad_id'])) {
						?>
						id: '<?php echo $options['vast_ad_id']; ?>',
						<?php
					}
					if (!empty($options['vast_ad_advertiser'])) {
						?>
						advertiser: '<?php echo $options['vast_ad_advertiser']; ?>',
						<?php
					}
					?>
				});

				ziggeo_vast.register("ziggeo_vast"); // name_of_provider can be anything
				<?php
			}
			?>
		}
		else {
			//Fallback for strange cases when the ziggeo.js does not get loaded yet the above is executed.
			jQuery(document).ready( function() {
				//Final check in case it was blocked (like some plugins do)
				ziggeoReInitApp();				
			});

			//Needed in cases of lazy load
			function ziggeoReInitApp() {
				if(typeof ZiggeoApi !== 'undefined') {
					//Set the V2 application

					window.ziggeo_app = ZiggeoApi.V2.Application.instanceByToken( ziggeoGetApplicationOptions().token , ziggeoGetApplicationOptions());
					ZiggeoApi.V2.Application.undefer();
					<?php
					//Language options
					//@add translations options here
					if($options['default_lang'] !== "auto") {
						?>
						ZiggeoApi.V2.Locale.setLocale("<?php echo $options['default_lang']; ?>");
						<?php
					}

					//developer feature
					if($options['dev_mode']) {
						//This allows you to get some additional feedback into the console. Turning off this option is recommended in the production (not needed), since that will hide any info from the browser / dev console.
						?>
						var ziggeo_dev = <?php echo ($options['dev_mode'] === ZIGGEO_YES) ? 'true' : 'false'; ?>;
						<?php
					}

					//Set up VAST header if we have the info for it
					if(!empty($options['vast_adserver'])) {
						?>
						//Set up VAST
						var ziggeo_vast = new ZiggeoApi.V2.Ads.AdSenseVideoAdProvider({
							provider: 'ziggeo_vast',
							adTagUrl: '<?php echo $options['vast_adserver']; ?>',
							muted: <?php echo ((int)$options['vast_muted']) === 1 ? 'true' : 'false' ?>,
							skipAfter: <?php echo $options['vast_skipafter']; ?>,
							<?php
							if (!empty($options['vast_ad_title'])) {
								?>
								title: '<?php echo $options['vast_ad_title']; ?>',
								<?php
							}
							if (!empty($options['vast_ad_description'])) {
								?>
								description: '<?php echo $options['vast_ad_description']; ?>',
								<?php
							}
							if (!empty($options['vast_ad_id'])) {
								?>
								id: '<?php echo $options['vast_ad_id']; ?>',
								<?php
							}
							if (!empty($options['vast_ad_advertiser'])) {
								?>
								advertiser: '<?php echo $options['vast_ad_advertiser']; ?>',
								<?php
							}
							?>
						});

						ziggeo_vast.register("ziggeo_vast"); // name_of_provider can be anything
						<?php
					}
					?>
				}
				else {
					setTimeout(function(){ziggeoReInitApp();}, 500);
				}
			}
		}
	</script>
	<!-- Ziggeo API code - END -->
	<?php

	define('ZIGGEO_PARSED_HEADER', true);
}

add_action('wp_head', "ziggeo_p_page_header");
add_action('admin_head', "ziggeo_p_page_header");
