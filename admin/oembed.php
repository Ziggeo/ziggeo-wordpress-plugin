<?php

//Checking if WP is running or if this is a direct call..
defined('ABSPATH') or die();

// oEmbed related codes

add_action( 'init', 'ziggeo_p_oembed_add_provider' );

//We have oEmbed, so we can register it here for everyone:
// Register oEmbed providers
function ziggeo_p_oembed_add_provider() {
	//for single video hosted page
	//http://ziggeo.io/p/video_token
	//http://ziggeo.io/services/oembed?format=json&url=ziggeo.io/p/video_token
	wp_oembed_add_provider( 'https://ziggeo.io/p/*', 'https://ziggeo.io/services/oembed/?format=json&url=', false );
	//requires that this website domain URL is allowed to play back or record videos using oEmbed which is done
	//through Manage > Iframe Embed
}
?>