<?php

//
//	This file holds the functionality needed to show the toolbar and add buttons to it.
//	This toolbar is shown above the Post and Pages editor.
//	All plugins utilizing the same (like WooCommerce) should have the toolbar shown as well.
//

// INDEX:
// 1. Hooks
//		1.1. hook:'edit_form_after_title'
// 2. Functionality
//		2.1. ziggeo_p_pre_editor()
//		2.2. ziggeo_create_toolbar_button()
//


//Checking if WP is running or if this is a direct call..
defined('ABSPATH') or die();



/////////////////////////////////////////////////
// 1. HOOOKS
/////////////////////////////////////////////////

	//Hook after the title and right before the editor
	global $wp_version;

	//only fire this if appropriate version, which is currently < 5.0.0
	if( version_compare( $wp_version, '5.0') < 0 ) {
		add_filter( 'edit_form_after_title', 'ziggeo_p_pre_editor' );
	}
	else {
		//Lets see if it is gutenber or not
		add_action('admin_enqueue_scripts', function() {

			//This is only for the gutenberg plugin (the 4.9 version..)
			//if( function_exists( 'is_gutenberg_page' ) && is_gutenberg_page() ) { 
			//We want to actally include or call a specific function at this time
			?>
				<script>window.addEventListener('load', function() { ziggeoSetupNewWPToolbar(); });</script>
			<?php
		});

		//Handling the AJAX request
		add_filter('ziggeo_ajax_call', function($result, $operation) {

			if($operation === 'admin_post_toolbar') {
				ziggeo_p_pre_editor(true);
				wp_die();
			}

			return $result;
		}, 10, 2);

	}



/////////////////////////////////////////////////
// 2. FUNCTIONALITY
/////////////////////////////////////////////////

	//Create the toolbar
	function ziggeo_p_pre_editor($ajax = false) {
		?>
		<div id="ziggeo-post-editor-toolbar">
			<?php echo do_action('ziggeo_toolbar_button', $ajax); ?>
		</div>
		<?php
	}

	//Creates the button in the toolbar, allowing us to quickly create uniform buttons
	function ziggeo_create_toolbar_button($id='', $title = '', $icon='video-alt', $visible = true, $type = 'button') {

		$code = '<a href="#" id="' . $id . '" class="button" title="' . $title . '" ';
		if($visible !== true) {
			$code .= ' style="display:none;" ';
		}
		$code .= ' onclick="return false;"';
		$code .= '>';
		$code .= '<span class="dashicons dashicons-' . $icon . '"></span> ' . $title . ' </a>';

		return $code;
	}



?>