<?php


//Function to tell us how many new videos (recorded through Wordpress) have been recorded.
//With the addiotion of PHP SDK we could make it a total count
function ziggeo_videoslist_count() {
	$video_count = (int)ziggeo_get_video_notices();

	return $video_count;
}

function ziggeo_videoslist_count_clear() {
	update_option('ziggeo_videos_count', 0);
}


function ziggeo_videoslist_count_add() {
	update_option('ziggeo_videos_count', ziggeo_videoslist_count()+1);
}

add_filter('ziggeo_ajax_call_client', function($rez, $operation) {

	if($operation === 'video_verified') {
		ziggeo_videoslist_count_add();
	}

	return $rez;
}, 10, 2);

add_filter('ziggeo_ajax_call', function($rez, $operation) {

	if($operation === 'video_verified') {
		ziggeo_videoslist_count_add();
	}

	return $rez;
}, 10, 2);

add_filter('ziggeo_ajax_call', function($rez, $operation) {

	if($operation === 'video_verified_seen') {

		//Can only be done by someone that can see the page
		if(current_user_can('moderate_comments')) {
			ziggeo_videoslist_count_clear();
			$rez = true;
		}
		else {
			$rez = false;
		}
	}

	return $rez;
}, 10, 2);

?>