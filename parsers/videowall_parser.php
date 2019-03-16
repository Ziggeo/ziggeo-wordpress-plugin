<?php

//Checking if WP is running or if this is a direct call..
defined('ABSPATH') or die();

//===============================================================================
// @REMOVE IN NEXT VERSION OF PLUGIN
//===============================================================================


if(!function_exists('videowallsz_content_parse_videowall')) {

	//$post_code - to see if we should post the code to the page or return it back
	function videowallsz_content_parse_videowall($template, $post_code = true) {

		$current_user = ziggeo_p_get_current_user();
		$c_user = ( $current_user->user_login == "" ) ? 'Guest' : $current_user->user_login;
		// --- VIDEO WALL ---
		// ------------------

			//Since there could be several walls on the same page, it would be best to create some random id that will help distinguish the x from y
			$wallID = 'ziggeo_video_wall' . rand(2,4) . str_replace(array(' ', '.'), '', microtime()) . rand(0,5); ///ziggeo_video_wall0363734001464901560

			$ret = '<div id="' . $wallID . '" class="ziggeo_videoWall" '; //we add later the type of wall as a class as well

			$wall = videowallsz_videowall_parameter_values($template);

			//To set up the wall inline style
			$wallStyles = '';

			//It would not be possible to use pixels and percentages in the same time, so to avoid bad HTML and CSS code percentages will rule the pixels when both are set
			if(!isset($wall['scalable_width']) && isset($wall['fixed_width'])) {
				$wallStyles .= 'width:' . trim($wall['fixed_width'], " \t\n\r\0\x0B".chr(0xC2).chr(0xA0)) . 'px;';
			}

			if(!isset($wall['scalable_height']) && isset($wall['fixed_height'])) {
				$wallStyles .= 'height:' . trim($wall['fixed_height'], " \t\n\r\0\x0B".chr(0xC2).chr(0xA0)) . 'px;';
			}

			if(isset($wall['scalable_width'])) {
				$wallStyles .= 'width:' . trim($wall['scalable_width'], " \t\n\r\0\x0B".chr(0xC2).chr(0xA0)) . '%;';
			}

			if(isset($wall['scalable_height'])) {
				$wallStyles .= 'height:' . trim($wall['scalable_height'], " \t\n\r\0\x0B".chr(0xC2).chr(0xA0)) . '%;';
			}

			if(isset($wall['show'])) {
				$wallStyles .= 'display:block;';
			}
			else {
				$wallStyles .= 'display:none;';
			}

			//adding inline style
			$ret .= 'style="' . $wallStyles . '"';
			$ret .= '>'; //closing video wall starting element

			//Does wall have the title parameter set up?
			if( isset($wall['title']) ) {
				//Lets get the title then
				$wall['title'] = '<div class="ziggeo_wall_title">' . $wall['title'] . '</div>';
			}
			else {
				//will be needed because of CSS
				$wall['title'] = '<div class="ziggeo_wall_title" style="display:none"></div>';
			}

			//We will change the way the design of the wall is set to minimize the amount of parameters being one parameter with many designs, over multiple
			// parameters with different names that determine which design is used

			//show_pages is default, so if slide_wall is set, it will be used over show_pages
			if( isset($wall['slide_wall']) || ( isset($wall['wall_design']) && $wall['wall_design'] === 'slide_wall' ) ) {
				$wall['slide_wall'] = true;

				//we disable the rest
				$wall['show_pages'] = false;
				$wall['chessboard_grid'] = false;
				$wall['mosaic_grid'] = false;
				$wall['wall_design'] = 'slide_wall';

				//videos per page
				if(!isset($wall['videos_per_page'])) { $wall['videos_per_page'] = 1; }
			}
			elseif( isset($wall['chessboard_grid']) || ( isset($wall['wall_design']) && $wall['wall_design'] === 'chessboard_grid' ) ) {
				$wall['chessboard_grid'] = true;

				//we disable the rest
				$wall['show_pages'] = false;
				$wall['slide_wall'] = false;
				$wall['mosaic_grid'] = false;
				$wall['wall_design'] = 'chessboard_grid';

				 //videos per page
				if(!isset($wall['videos_per_page'])) { $wall['videos_per_page'] = 20; }
			}
			elseif( isset($wall['mosaic_grid']) || ( isset($wall['wall_design']) && $wall['wall_design'] === 'mosaic_grid' ) ) {
				$wall['mosaic_grid'] = true;

				//we disable the rest
				$wall['show_pages'] = false;
				$wall['slide_wall'] = false;
				$wall['chessboard_grid'] = false;
				$wall['wall_design'] = 'mosaic_grid';

				//videos per page
				if(!isset($wall['videos_per_page'])) { $wall['videos_per_page'] = 20; }
			 }
			 else {
				$wall['show_pages'] = true;

				//we disable the rest
				$wall['slide_wall'] = false;
				$wall['chessboard_grid'] = false;
				$wall['mosaic_grid'] = false;
				$wall['wall_design'] = 'show_pages';

				//videos per page
				if(!isset($wall['videos_per_page'])) { $wall['videos_per_page'] = 2; }
			 }

			//getting the defaults:

			//video width
			if(!isset($wall['video_width'])) { $wall['video_width'] = 320; }

			//video height
			if(!isset($wall['video_height'])) { $wall['video_height'] = 240; }

			//lets set the post ID since we will need to reference it as tag
			$wall['postID'] = get_the_ID();

			//what kind of videos to show - defaults to approved ones
			if(!isset($wall['show_videos'])) { $wall['show_videos'] = 'approved'; }

			if(!isset($wall['on_no_videos'])) { $wall['on_no_videos'] = 'showmessage'; }

			//Is there a message set in of no videos? If not, we should make some:
			if(!isset($wall['message'])) { $wall['message'] = 'Currently no videos found. We do suggest recording some first'; }

			//We are parsing template only if it is set to be shown, otherwise there is no need for it.
			if($wall['on_no_videos'] === 'showtemplate') {
				//Did we set up a template to be loaded into the videowall if there are no videos?
				if(!isset($wall['template_name'])) { $wall['template_name'] = ''; }
				else {
					$wall['template_name'] = ziggeo_p_template_params($wall['template_name']);

					//template was not found lets use the defaults
					if($wall['template_name'] === false) {
						$wall['template_name'] = ZIGGEO_RECORDER_DEFAULT;
					}
					else {
						$wall['template_name'] = str_ireplace("'", '"', $wall['template_name']);
						$wall['template_name'] = ziggeo_p_parameter_prep($wall['template_name']);
					}
				}
			}
			else {
				$wall['template_name'] = '';
			}

			//In case video wall should be hidden if empty
			if($wall['on_no_videos'] === 'hidewall') {
				$wall['hide_wall'] = true;
			}
			else {
				$wall['hide_wall'] = false;
			}

			$autoplaytype = '';

			if(!isset($wall['autoplay']))   { $wall['autoplay'] = false; }
			else {
				//autoplay is set, so we check if any of the other 2 options are set as well:
				if(isset($wall['autoplay-continue-end'])) {
					$autoplaytype = 'continue-end';
				}
				elseif(isset($wall['autoplay-continue-run'])) {
					$autoplaytype = 'continue-run';
				}
			}

			//To handle search and everything, we will use JS, otherwise we would need to include SDK (which would be OK, however it would also cause a lot more code to be present and would be hard to update if needed)
			//to use it through client side, we will now build JS templates which will be outputted to the page.


			//We want it to output this only once. It is no problem if we do it hundreds of times, since the images would only be loaded once and no conflicts would be made, however doing that would cause the page to be filled out with non required code, so this makes it nicer.
			if(!wp_style_is('ziggeo_wall_images', 'done')) {
				//Lets make sure we mark it as done..
				global $wp_styles;
				$wp_styles->done[] = 'ziggeo_wall_images';

				//Lets also add the code into the header, so it is not in the page content area..
				add_action('wp_footer', 'videowallsz_css_video_wall');
			}

			//We now allow customers to set custom tags to search videos by..This will provide them with more freedom.
			// good to note that we should search using tags, by default, this is to fine tune the results that are matching the
			// post ID tag.
			if(!isset($wall['videos_to_show'])) {
				$wall_tags = 'wordpress,comment,post_' . $wall['postID']; //default that shows the videos made in the comments of the specific post
			}
			else {
				if($wall['videos_to_show'] === '"') { $wall['videos_to_show'] = ''; }
				$wall_tags = $wall['videos_to_show'];
			}

			//added to allow the video wall to process videos of the current user without requiring the PHP code to run it
			$wall_tags = str_ireplace( '%ZIGGEO_USER%', $c_user, $wall_tags );

			?>
			<script type="text/javascript" class="runMe">
				<?php
					//This helps us create js code that works as is and uses the variable data from these outputs instead of outputting the data into the code each time - and adding JS directly to the page.
				?>
				ZiggeoWP.videowalls.walls['<?php echo $wallID; ?>'] = {
					videos: {
						width: <?php echo $wall['video_width']; ?>,
						height: <?php echo $wall['video_height']; ?>,
						autoplay: <?php echo ($wall['autoplay']) ? 'true' : 'false'; ?>,
						autoplaytype: '<?php echo $autoplaytype; ?>'
					},
					indexing: {
						perPage: <?php echo $wall['videos_per_page']; ?>,
						status: '<?php echo $wall['show_videos']; ?>',
						design: '<?php echo $wall['wall_design']; ?>',
						<?php
						/*
							,
							showPages: <?php echo ($wall['show_pages']) ? 'true' : 'false'; ?>,
							slideWall: <?php echo ($wall['slide_wall']) ? 'true' : 'false'; ?>,
							chessboardGrid: <?php echo ($wall['chessboard_grid']) ? 'true' : 'false'; ?>,
							mosaicGrid: <?php echo ($wall['mosaic_grid']) ? 'true' : 'false'; ?>
						*/
						?>
						fresh: true
					},
					onNoVideos: {
						showTemplate: <?php echo ($wall['on_no_videos'] === 'showtemplate') ? 'true' : 'false'; ?>,
						message: '<?php echo $wall['message']; ?>',
						templateName: '<?php echo $wall['template_name']; ?>',
						hideWall: <?php echo ($wall['hide_wall']) ? 'true' : 'false'; ?>
					},
					title: '<?php echo $wall['title']; ?>',
					tags: '<?php echo $wall_tags; ?>' <?php //the tags to look the video by based on template setup ?>
				};
			</script>
			<?php

			//Video wall will by default only show when the video comment is submitted, unless this is overridden by the `show` parameter
			if( !isset($wall['show']) ) {
				//wait for video submission first
				?>
				<script type="text/javascript" class="runMe">
					<?php
						//just to make sure that it is available
						//we could add to check the embedding in order to fire only if right embedding is shown..
						//@update
					?>
					if(ziggeo_app) {
						ziggeo_app.embed_events.on("verified", function (embedding) {
							videowallszUIVideoWallShow('<?php echo $wallID; ?>');
						});
					}
					<?php //lets wait for a second and try again. ?>
					else {
						setTimeout( function(){
							ziggeo_app.embed_events.on("verified", function (embedding) {
								videowallszUIVideoWallShow('<?php echo $wallID; ?>');
							});
						}, 10000 );<?php //10 seconds should be enough for page to load and we do not need to have this set up right away. ?>
					}
				</script>
				<?php
			}
			else {
				//video wall must be shown right away..
				?>
				<script type="text/javascript" class="runMe">
					jQuery(document).ready( function () {
						videowallszUIVideoWallShow('<?php echo $wallID; ?>');
					});
				</script>
				<?php
			}

			//closing videowall div
			$ret .= '</div>';


		return $ret;
		// --- VIDEO WALL END ---
		// ----------------------
	}
}


//handles the raw parameters for the ziggeo videowall..
if(!function_exists('videowallsz_prep_parameters_videowall')) {

	function videowallsz_prep_parameters_videowall($raw_parameters = null) {

		if($raw_parameters === null) {
			return '';
		}

		return $raw_parameters;
	}
}
?>