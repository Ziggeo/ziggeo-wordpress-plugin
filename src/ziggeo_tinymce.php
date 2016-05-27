<?php
	//All code related to tinyMCE is stored here
	// This file is included from within ziggeo.php file

	//Registering our plugin in the list of TinyMCE list of external plugins
	add_filter('mce_external_plugins', 'ziggeo_mce_register');
	//Adding a button to the TinyMCE toolbar
	add_filter('mce_buttons', 'ziggeo_mce_add_button');

//We must register the URL to our plugin in TinyMCE, and we do that here
function ziggeo_mce_register($plugin_array) {
	//url to our plugin's js file handling tinyMCE through js code
	$url = ZIGGEO_ROOT_URL . 'src/ziggeo_tinymce_plugin.php';

	$plugin_array['ziggeo'] = $url;

	return $plugin_array;
}

//Adding the button to the list of existing TinyMCE buttons
function ziggeo_mce_add_button($buttons) {
	array_push($buttons, 'separator', 'ziggeo_templates');

	return $buttons;
}

?>