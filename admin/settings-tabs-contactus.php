<?php

//Checking if WP is running or if this is a direct call..
defined('ABSPATH') or die();

// - CONTACT US - tab fields functions
//-------------------------------------

//Function to show the frame of our tab
function ziggeo_a_s_c_text() {
	?>
	</div>
	<div class="ziggeo-frame" style="display: none;" id="ziggeo-tab_contact">
		<p><i><?php _e('Regardless where your question is posted, we are happy to assist with the same, so all you need to do is ask.', 'ziggeo'); ?></i></p>
	<?php
}
	//Function to show the contact details on Ziggeo.com
	function ziggeo_a_s_c_email_forum_field() {
		?>
		<p><?php
			_ex('We are using forum and email helpdesk to provide assistance with any questions you might have.','part of email and chat segment on contact us page 1/7', 'ziggeo');
			_ex('To contact us there, you can use any of the bellow details:', 'part of email and chat segment on contact us page 2/7', 'ziggeo'); ?>

			<ol class="ziggeo_contact_us_options">
				<li>
					<span class="title"><?php _ex('E-Mail', 'part of email and chat segment on contact us page 3/7', 'ziggeo'); ?></span>
					<p class="info">
						<a href="mailto:support@ziggeo.com">support@ziggeo.com</a>
					</p>
				</li>
				<li>
					<span class="title"><?php _ex('Our Knowledge Base system', 'part of email and chat segment on contact us page 4/7', 'ziggeo'); ?></span>
					<p class="info">
						<a href="https://support.ziggeo.com/hc/en-us" target="_blank"><?php _e('Knowledge Base', 'ziggeo'); ?></a>
					</p>
				</li>
				<li>
					<span class="title"><?php _ex('WordPress plugin section of our forum', 'part of email and chat segment on contact us page 5/7', 'ziggeo'); ?></span>
					<p class="info">
						<a href="https://support.ziggeo.com/hc/en-us/community/topics/200753347-WordPress-plugin" target="_blank"><?php _ex('WordPress Plugin forum', 'part of email and chat segment on contact us page 6/7', 'ziggeo'); ?></a> <?php _x('where you might find the answers to your questions already being answered.', 'part of email and chat segment on contact us page 7/7', 'ziggeo'); ?>
					</p>
				</li>
			</ol>
		</p>
		<?php
	}

	//Function to show the contact instructions for contacting on WordPress itself instead.
	function ziggeo_a_s_c_wp_forum_field() {
		?>
		<p><?php
			echo _x('If you prefer to contact us via WordPress, all you need is to head to:', 'contact us tab - wp forum 1/2', 'ziggeo') .
			'<a href="https://wordpress.org/support/plugin/ziggeo" target="_blank">' .
			_x('Ziggeo Plugin Support Section', 'contact us tab - wp forum 1/2', 'ziggeo') .
			'</a>'; ?>
		</p>
		<?php
	}

	//Function to show the chat :)
	function ziggeo_a_s_c_zchat_field() {
		//Waiting for Zopim support to give green light..
		//got green light, add at the end of implementation
		//Possible things with Zopim
		//https://api.zopim.com/files/meshim/widget/controllers/LiveChatAPI-js.html
		
		echo _x('You should see a button showing "Help" in the bottom right corner at this point.', 'ziggeo');
		?>
		<script>
			window.zEmbed||(function(){
				var queue = [];

				window.zEmbed = function() {
					queue.push(arguments);
				}

				window.zE = window.zE || window.zEmbed;
				document.zendeskHost = 'ziggeo.zendesk.com';
				document.zEQueue = queue;
			}());

			window.zEmbed(function () {
				$zopim(function () {
					/*$zopim.livechat.cookieLaw.comply();      
					$zopim.livechat.cookieLaw.setDefaultImplicitConsent();
*/
					<?php
						$current_user = ziggeo_p_get_current_user();
						$name = $current_user->user_login;
						$email = $current_user->user_email;

						global $wp_version;
					?>
					$zopim.livechat.setName('<?php echo $name; ?>');
					$zopim.livechat.setEmail('<?php echo $email ?>');
					$zopim.livechat.addTags('wordpress', 'plugin');

					$zopim.livechat.setOnChatStart(function() {
						$zopim.livechat.say('Helpful info:\nOur WordPress version is "<?php echo $wp_version; ?>" and our Wordpress plugin version is: <?php echo ziggeo_get_version(); ?>');
					});
				});
			});
		</script>
		<script src="https://assets.zendesk.com/embeddable_framework/main.js" data-ze-csp="true" async defer></script>
		<?php
	}
?>