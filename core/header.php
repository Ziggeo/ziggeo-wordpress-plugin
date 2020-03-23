<?php

//Checking if WP is running or if this is a direct call..
defined('ABSPATH') or die();


function ziggeo_p_page_header() {

	if(defined('ZIGGEO_PARSED_HEADER')) {
		return;
	}

	$options = get_option('ziggeo_video');

	//use add_filter('ziggeo_setting_header_code', 'your-function-name') to change the options on fly if wanted
	// it needs to return modified $options array.
	$options = apply_filters('ziggeo_setting_header_code', $options);

	?>
	<!-- Ziggeo API code - START -->
	<script type="text/javascript">

		//Function to just add the main entry in the namespace, so that we keep everything within it, instead of having many things outside of it as we do now.
		var ZiggeoWP = {
			//DEVS: Make sure you add comma behind your code or it would cause issue
			<?php do_action('ziggeo_add_to_ziggeowp_object'); ?>

			//Ajax nonce
			ajax_nonce: "<?php echo wp_create_nonce('ziggeo_ajax_nonce'); ?>",
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

					//have we already set this one?
					if( typeof(ZiggeoWP.hooks._hooks[hook_name]) != 'undefined') {
						for(i2 = 0, c2 = ZiggeoWP.hooks._hooks[hook_name].length; i2 < c2; i2++) {
							if(ZiggeoWP.hooks._hooks[hook_name][i2].key === f_key) {
								return false;
							}
						}
					}

					/*if(typeof(priority) === 'undefined') {
						priority = 0; //0 equals last, other numbers indicate its position
					}*/

					//if(priority == 0) {
						if(typeof(ZiggeoWP.hooks._hooks[hook_name]) == 'undefined') {
							ZiggeoWP.hooks._hooks[hook_name] = [];
						}

						//if(typeof(ZiggeoWP.hooks._hooks[hook_name][f_unction]) == 'undefined') {
							ZiggeoWP.hooks._hooks[hook_name].push( {key: f_key, func: f_unction} );
							//all set and good, so lets return true
							return true;
						//}

						//if it comes to here, hook was already set, so lets not add it one more time..
						//we indicate this by returning false.
						//return false;
					//}
					//else {
						//@ADD in next revision, we do not need priority for now
					//}
				},

				//will check all of the hooks and fire them one after another
				fire: function(hook_name, data) {
					if( typeof(ZiggeoWP.hooks._hooks[hook_name]) != 'undefined') {
						for(i = 0, c = ZiggeoWP.hooks._hooks[hook_name].length; i < c; i++) {
							//final sanity if the function is still available..
							if( typeof(ZiggeoWP.hooks._hooks[hook_name][i]) != 'undefined') {
								ZiggeoWP.hooks._hooks[hook_name][i].func(data);
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
			$str_auth = (isset($options, $options['use_auth']) &&
						$options['use_auth'] === 'true') ?
						',' . "\n" . 'auth: true' : '';
			$str_webrtc_mobile = (isset($options, $options['webrtc_for_mobile']) &&
						$options['webrtc_for_mobile'] === 'true') ?
						',' . "\n" . 'webrtc_on_mobile: true' : '';
			$str_webrtc_streaming = (isset($options, $options['webrtc_streaming']) &&
						$options['webrtc_streaming'] === 'true') ?
						',' . "\n" . 'webrtc_streaming: true' : '';
			$str_webrtc_streaming_needed = (isset($options, $options['webrtc_streaming_needed']) &&
						$options['webrtc_streaming_needed'] === 'true') ?
						',' . "\n" . 'webrtc_streaming_if_necessary: true' : '';
			$str_debug = (isset($options, $options['use_debugger']) &&
						$options['use_debugger'] === 'true') ? ',\ndebug: true' : '';
		?>

		//function to get app options
		function ziggeoGetApplicationOptions() {

			return {
				token: "<?php echo (( isset($options, $options['token']) ) ? $options['token'] : "" ); ?>"<?php

				echo $str_auth;
				echo $str_webrtc_mobile;
				echo $str_webrtc_streaming;
				echo $str_webrtc_streaming_needed;
				echo $str_debug;
				?>
			}
		}

		if(typeof ZiggeoApi !== 'undefined') {
			//Just so there is no error in cases where other plugins remove our scripts

			//Set the V2 application
			window.ziggeo_app = new ZiggeoApi.V2.Application( ziggeoGetApplicationOptions() );
			<?php
				//Language options
				//@add translations options here
				if(isset($options, $options['default_lang']) && $options['default_lang'] !== "auto") {
					?>
					ZiggeoApi.V2.Locale.setLocale("<?php echo $options['default_lang']; ?>");
					<?php
				}

				//developer feature
				if(isset($options, $options['dev_mode'])) {
					//This allows you to get some additional feedback into the console. Turning off this option is recommended in the production (not needed), since that will hide any info from the browser / dev console.
					?>
					var ziggeo_dev = <?php echo ($options['dev_mode'] === ZIGGEO_YES) ? 'true' : 'false'; ?>;
					<?php
				}
			?>
		}
	</script>
	<!-- Ziggeo API code - END -->
	<?php

	define('ZIGGEO_PARSED_HEADER', true);
}

add_action('wp_head', "ziggeo_p_page_header");
add_action('admin_head', "ziggeo_p_page_header");
