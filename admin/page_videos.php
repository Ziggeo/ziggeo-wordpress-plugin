<?php

//Checking if WP is running or if this is a direct call..
defined('ABSPATH') or die();

?>
<div>
	<h2>Video List</h2>

	<form action="options.php" method="post">
		<?php
		wp_nonce_field('ziggeo_nonce_action', 'ziggeo_videos_list_nonce');
		settings_errors();
		ziggeo_a_v_text();
		?>
	</form>
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
	</div>
	<div class="ziggeo-frame" id="ziggeo-videos"></div>
	<div class="ziggeo-navigation" id="ziggeo-videos-nav"></div>

	<?php
}

?>