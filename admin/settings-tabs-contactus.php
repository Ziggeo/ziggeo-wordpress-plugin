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
			/*
				Translators: The part info is left as is as we removed some of the code in here. The reason for this is
				             to avoid issues where if we reduced the "id" a wrong translation is shown. In hindsight
				             we should have used better IDs that would have allowed for the same.
			*/
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
					<span class="title"><?php _ex('Create your own feature suggestions', 'Contact us page', 'ziggeo'); ?></span>
					<p class="info">
						<a href="https://feedback.ziggeo.com/wordpress" target="_blank"><?php _ex('Feedback Pages', 'Contact Us page', 'ziggeo'); ?></a> <?php _x('where you can upvote other suggestions or add your own', 'Contact Us', 'ziggeo'); ?>
					</p>
				</li>
				
			</ol>
		</p>
		<?php
	}

?>