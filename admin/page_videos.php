<?php

//Checking if WP is running or if this is a direct call..
defined('ABSPATH') or die();

?>
<div>
	<h2>Video List</h2>

	<div>
		<?php
		wp_nonce_field('ziggeo_nonce_action', 'ziggeo_videos_list_nonce');
		settings_errors();
		ziggeo_a_v_text();
		?>
	</div>
	<div id="ziggeo_messenger"><div id="ziggeo_message"></div><div>X</div></div>
</div>
<?php

//Function to show page content
function ziggeo_a_v_text() {

	//Here we could include the PHP SDK and have the index call made internally. This would be the safest way to do it
	//For now we go with JS implementation

	?>
	<div id="ziggeo-videos-filter">
		<label>
			<span>Token/Key:</span>
			<input class="token" type="text">
		</label>
		<label><span>Moderation:</span>
			<select class="moderation">
				<option value="all" selected="selected">All</option>
				<option value="approved">Approved</option>
				<option value="pending">Pending</option>
				<option value="rejected">Rejected</option>
			</select>
		</label>
		<label>
			<span>Tags:</span>
			<input class="tags" type="text">
		</label>
		<label>
			<span>Sort:</span>
			<select class="sort">
				<option value="new" selected="selected">Newest First</option>
				<option value="old">Oldest First</option>
			</select>
		</label>
		<span class="ziggeo-ctrl-btn">Apply Filter</span>
		<div class="sub-filter">
			<div class="info">These filters are applied to the videos you have already retrieved using the above API filtering. Currently you will be filtering <span id="ziggeo-video-list-sub-filtering-count">100</span> videos
				<ul><li>More videos you have more time the filter might need as you start typing</li></ul></div>
			<label>
				<span>Filter Results</span>
				<input class="filter_string" type="text">
			</label>
			<label>
				<span>In Title?</span>
				<input class="filter_inc_title" type="checkbox" checked="true">
			</label>
			<label>
				<span>In Description?</span>
				<input class="filter_inc_desc" type="checkbox" checked="true">
			</label>
			<label>
				<span>In Custom Data?</span>
				<input class="filter_inc_cus_data" type="checkbox" checked="true">
			</label>
			<label>
				<span>In Moderation Reasons?</span>
				<input class="filter_inc_mod_reason" type="checkbox" checked="true">
			</label>
			<label>
				<span>In Tags?</span>
				<input class="filter_inc_in_tags" type="checkbox" checked="true">
			</label>
		</div>
		<div class="sub-filter">
			<label>
				<span>Has Tags</span>
				<input class="filter_inc_tags" type="checkbox" checked="true">
			</label>
			<label>
				<span>HD</span>
				<input class="filter_inc_hd" type="checkbox" checked="true">
			</label>
			<label>
				<span>SD</span>
				<input class="filter_inc_sd" type="checkbox" checked="true">
			</label>
			<label>
				<span>With Effects</span>
				<input class="filter_inc_w_effects" type="checkbox" checked="true">
			</label>
			<span id="ziggeo_filter_found"></span>
		</div>
	</div>
	<div class="ziggeo-frame" id="ziggeo-videos"></div>
	<div class="ziggeo-navigation" id="ziggeo-videos-nav"></div>

	<?php
}

?>