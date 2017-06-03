<?php
// JobManagerResumeManager integration module

//Required for each module.
//This gives us the details needed to output the data to the Integrations tab and possibly do other things later on.
//If not present, we ignore the module as incomplete
function ZiggeoIntegration_JobManagerResumeManager_details() {
	return array(
		'author_name'       => 'Ziggeo', //author name
		'author_url'        => 'https://ziggeo.com/', //link to author website
		'requires_min'      => '1.15', //version of Ziggeo plugin required as minimum for this integration to work
								// properly. (required)
		'requires_max'      => '', //not known to not work with some version
		'plugin_name'       => 'Job Manager\'s addon "Resume Manager"', //Name of the integration shown in Integrations
								// tab (should be original plugin name as is)
		'plugin_url'        => 'https://wpjobmanager.com/add-ons/resume-manager/', //URL to the plugin to be downloaded
								// from.
		'plugin_min'        => '1.15.2', //minimum version of the plugin this module integrates to (required)
								//Resume Manager version is 1.15.3 in readme, 1.15.2 in the definition.. - which
								// requires Job Manager 1.25.2, however in the plugin, text part is updated to 1.25.2
								// while definition of JOB_MANAGER_VERSION still points to 1.25.0. 
		'plugin_max'        => '', //up to which version would module work upon which it should be disabled
		'screenshot_url'    => ZIGGEO_ROOT_URL . 'images/integrations/jobmanager-resume-manager.png', //URL to the
								// screenshot of the plugin this module integrates to, to show it in Integrations tab
		'firesOn'           => 'public' //Where does the plugin fires on? "admin", "public" or "both" - so that we only
								// run plugin where it is needed.
	);
}

//Function to call to see if the main / base plugin is present or not.
function ZiggeoIntegration_JobManagerResumeManager_checkbase() {

	if ( is_plugin_active('wp-job-manager/wp-job-manager.php') ) {
		if(defined('JOB_MANAGER_VERSION')) {
			//Job Manager is enabled, we need resume manager as well
			if(defined('RESUME_MANAGER_VERSION')) {
				return true;
			}
		}
	}

	return false;
}

//Function that we call to activate integration. Best place for hooks to be added and executed from..
function ZiggeoIntegration_JobManagerResumeManager_run() {
	//filter to change the fileds on the form
	add_filter( 'submit_resume_form_fields', 'ziggeo_JobManagerResumeManager_change_field' );
	//used to allow us to grab the "ziggeo-{token}" if present in passed content and change it with out code..
	//add_filter( 'wp_video_shortcode_override', 'ziggeo_JobManagerResumeManager_filterVideos' );
	//the above might no longer be needed...
	add_filter( 'the_candidate_video', 'ziggeo_JobManagerResumeManager_playOnResume' );
}

//returns the version of the plugin that we integrate to.
function ZiggeoIntegration_JobManagerResumeManager_getVersion() {

	if(defined('RESUME_MANAGER_VERSION')) {
		return RESUME_MANAGER_VERSION;
	}

	return 0;
}

function ziggeo_JobManagerResumeManager_playOnResume($data) {
	?>
		<script>
			ZiggeoApi.Events.on("system_ready", function () {
				//if we have already created the video embedding holder exit
				if(document.getElementById("candidate_video-emb")) { return false; }

				var videoElement = document.getElementById("candidate-video");

				//oembed is used to show the videos, so this should hold and iframe if exists
				if(videoElement) {
					//we grab the link to the video through iframe URL
					var video_token = videoElement.getElementsByTagName("iframe")[0].src.replace("https://", "")
													.replace("http://", "").replace("ziggeo.io/p/", "");
					if(video_token) {
						//video should be in place of photo, so we use this for a reference
						var photos = document.getElementsByClassName("candidate_photo");
						if (photos.length > 0 ) {
							var playerHolder = document.createElement("div");
							playerHolder.id = "candidate_video-emb";
							photos[0].parentElement.appendChild(playerHolder);
							var embedding = ZiggeoApi.Embed.embed(
								"#candidate_video-emb", {
								width:photos[0].getBoundingClientRect().width,
								height:photos[0].getBoundingClientRect().height,
								video:video_token
							});
							//lets create buttons for switching between photo and video..
							ziggeoCreateButtons(playerHolder, photos[0]);
							return true;
						}
						else {
							//photo placeholder was not available
							return false;
						}
					}
				}
			});
			//creates the buttons for switching between photo and video
			//it is used on the Resume (pre)view pages
			function ziggeoCreateButtons(playerElem, photoElem) {
				var btnHolder = document.createElement("div");
				btnHolder.id = "ziggeo-buttons-holder-JMV";
				document.getElementById("candidate_video-emb").parentNode.appendChild(btnHolder);

				var btn1 = document.createElement("span");
				btn1.className = "ziggeo-button btn-left";
				btn1["data-for"] = "candidate_photo";
				btn1.innerHTML = "Photo";
				btnHolder.appendChild(btn1);

				var btn2 = document.createElement("span");
				btn2.className = "ziggeo-button btn-right";
				btn2["data-for"] = "candidata_video-emb";
				btn2.innerHTML = "Video";
				btnHolder.appendChild(btn2);
				
				playerElem.style.display = "block";
				photoElem.style.display="none";
				
				if(document.addEventListener) {
					btn1.addEventListener("click", function() {
						photoElem.style.display="block";
						playerElem.style.display = "none";
					}, false);
					btn2.addEventListener("click", function() {
						playerElem.style.display = "block";
						photoElem.style.display="none";
					}, false);
				}
				else {
					btn1.attachEvent("onclick", function() {
						photoElem.style.display="block";
						playerElem.style.display = "none";
					});
					btn2.attachEvent("onclick", function() {
						playerElem.style.display = "block";
						photoElem.style.display="none";
					});
				}
			}
		</script>
		<?php
	return $data;
}

//Hook got activated, so we can just run our code as JobManagerResumeManager should already be available for us.
function ziggeo_integration_JobManagerResumeManager_start() {
	
}

//Outputs the script code within the footer..
function ziggeo_JobManagerResumeManager_submissionForm() {
	?>
		<script>
			ZiggeoApi.Events.on("system_ready", function () {
				function ziggeo_jobmanager_resumemanager_changeVideoField() {
					var videoElement = document.getElementById("candidate_video");
					//we check if the element exists. If it does not, videoElement is null, so lets just break out..
					if(!videoElement) {
						//this got activated on the profile page
						var photos = document.getElementsByClassName("candidate_photo");
						if (photos.length > 0 ) {
							//we are on resume page, lets show video..
							//since the JobManager docs do not say how you can get the value, we can use local storage
							// for the preview..
							var token = ziggeoGetStoredToken();
							var playerHolder = document.createElement("div");
							playerHolder.id = "candidate_video-emb";
							photos[0].parentElement.appendChild(playerHolder);
							if(token) {
								var embedding = ZiggeoApi.Embed.embed(
									"#candidate_video-emb", {
									width:photos[0].getBoundingClientRect().width,
									height:photos[0].getBoundingClientRect().height,
									video:token
								});
							}
							ziggeoCreateButtons(playerHolder, photos[0]);
							return true;
						}
						else {
							return false;
						}
						return false;
					}
					//we hide the input field and will add token to it later on
					videoElement.style.display = "none";
					//We need to make sure that we show recorder if the input is empty (no video yet) and to actually
					// show it within the video rerecorder if the video token is present..
					if(videoElement.value !== "") {
						//we have some data here..
						if(videoElement.value.indexOf("ziggeo") > -1) {
							//Ziggeo video token is present
							var _token = videoElement.value.substr(videoElement.value.indexOf("/p/")+3);
							//Now we add it to local Storage for safe keeping
							ziggeoStoreToken(_token);
						}
						else {
							//most likely some URL is shown..lets leave everything as is..
							return false;
						}
					}
					else {
						var _token = null;
					}
					var recHolder = document.createElement("div");
					recHolder.id = "candidate_video-emb";
					videoElement.parentNode.appendChild(recHolder);
					//we should show the rerecorder
					if(_token) {
						//we now attach emebdding to this new element
						//For now we are showing rerecorder, however we must add some check to see if this is the same
						// person that had recorded the video. If they are not, we need to use player instead - we do
						// not want to allow rerecording to other people..
						var embedding = ZiggeoApi.Embed.embed(
							"#candidate_video-emb", {
							width:640,
							height:480,
							perms: "allowupload",
							modes: "rerecorder",
							video: _token
						});
					}
					//we should show the recorder
					else {
						//we now attach emebdding to this new element
						var embedding = ZiggeoApi.Embed.embed(
							"#candidate_video-emb", {
							<?php
								$options = get_option('ziggeo_video');
								$template_used = false;
								if($options['integrations_recorder_template']) {
									if($template = ziggeo_template_params_as_object($options['integrations_recorder_template'])) {
										echo $template;
										$template_used = true;
									}
								}

								if($template_used === false){
									//if no template is specified, we can run template
									?>
										width:640,
										height:480,
										perms: "allowupload"
									<?php
								}
							?>
						});
					}
				}
				//execute the function above
				ziggeo_jobmanager_resumemanager_changeVideoField();
			});
			//make the submission of video save the token into the video filed..
			//we add "ziggeo-" before the token so that we check if the value contains "ziggeo" and if it does, we know
			// that token is what follows, making sure that if someone had submitted the URL before, we do not try to
			// see that as a video..
			ZiggeoApi.Events.on("submitted", function ( data ) {
				//document.getElementById("candidate_video").value = "ziggeo-" + data.video.token;
				//this does require however that iframe embed allows this domain for it to work.
				document.getElementById("candidate_video").value = "https://ziggeo.io/p/" + data.video.token;
				ziggeoStoreToken(data.video.token);
			});
			//if local storage is not available we use cookies..
			function ziggeoStoreToken(token) {
				try {
					sessionStorage.setItem("ziggeo-token", token);
					sessionStorage.getItem("ziggeo-token");
					//since we are still here, lets go on..
					return true;
				}
				catch(e) {
					//cookies
					return false;
				}
			}
			//returns the video token from storage or cookies
			function ziggeoGetStoredToken() {
				try {
					return sessionStorage.getItem("ziggeo-token");
				}
				catch(e) {
					//cookies
					return false;
				}
			}
			function ziggeoCreateButtons(playerElem, photoElem) {
				var btnHolder = document.createElement("div");
				btnHolder.id = "ziggeo-buttons-holder-JMV";
				document.getElementById("candidate_video-emb").parentNode.appendChild(btnHolder);

				var btn1 = document.createElement("span");
				btn1.className = "ziggeo-button btn-left";
				btn1["data-for"] = "candidate_photo";
				btn1.innerHTML = "Photo";
				btnHolder.appendChild(btn1);

				var btn2 = document.createElement("span");
				btn2.className = "ziggeo-button btn-right";
				btn2["data-for"] = "candidata_video-emb";
				btn2.innerHTML = "Video";
				btnHolder.appendChild(btn2);
				
				playerElem.style.display = "block";
				photoElem.style.display="none";
				
				if(document.addEventListener) {
					btn1.addEventListener("click", function() {
						photoElem.style.display="block";
						playerElem.style.display = "none";
					}, false);
					btn2.addEventListener("click", function() {
						playerElem.style.display = "block";
						photoElem.style.display="none";
					}, false);
				}
				else {
					btn1.attachEvent("onclick", function() {
						photoElem.style.display="block";
						playerElem.style.display = "none";
					});
					btn2.attachEvent("onclick", function() {
						playerElem.style.display = "block";
						photoElem.style.display="none";
					});
				}
			}
		</script>;
	<?php
}

//Changes the video input field into video Ziggeo embedding
function ziggeo_JobManagerResumeManager_change_field($fields) {
	//We could change the type of the video field here to be something other than text, however if we did that it would
	// require us to add the same type in a way that Job Manager plugin would understand on all pages. Instead, lets
	// just output the JS code that will detect and change the filed as we like it - to Ziggeo Video. Then even if the
	// Ziggeo plugin is disabled nothing really changes, this will immediately start showing video input field.

	//$fields['resume_fields']['candidate_video']['type'] = "video";

	//instead of that, lets enque the script instead
	add_action('wp_footer', 'ziggeo_JobManagerResumeManager_submissionForm');

	return $fields;
};

//checks the content passed through wp_video_shortcode() function and changes the "ziggeo-{token}" into the HTML for
// the video to be played..
//$override = apply_filters( 'wp_video_shortcode_override', '', $attr, $content, $instance );
function ziggeo_JobManagerResumeManager_filterVideos($html, $attr, $content, $instance) {
	//if $html !== empty it was already handled by some plugin
	$x = stripos($html,'ziggeo-');

	if($x !== false) {
		//it exists, lets check when the token ends..
		// "ziggeo-" = 7
		// video token = 32 characters
		$video_token = substr($html, $x+7, 32);
		
		$default = 'responsive width=320 height=240';

		$options = get_option('ziggeo_video');

		//Lets grab the default player template for the video comments.. We might change this to use a different player
		// specific to this integration..Would require a bit different setup of the integrations page so that we can
		// speciy this.
		$template_player = ( isset($options['comments_player_template'])  &&
							!empty($options["comments_player_template"]) );

		//Final player template that we will be using
		if($template_player) {
			//get the template parameters based on the name..
			$tempParams = ziggeo_template_params($options['comments_player_template']);

			//do we have anything to be used..
			$default = ( $tempParams ) ? $tempParams : $default;

		}

		//Make sure that template is parsed and prefix added if needed.
		$html = '<ziggeo ' . ziggeo_parameter_prep($default) . 'ziggeo-video="' . $video_token . '"></ziggeo>';
	}

	//we always return $html, either as we got it or after the change we made..
	return $html;
}

?>