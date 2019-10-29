//JS code to handle videowalls on client side


// Index
//*******
//	1. Events
//		1.1. DOM ready
//	2. General functionality
//		2.1. videowallszUIVideoWallShow()
//		2.2. videowallszUIVideoWallNoVideos()
//		2.2. videowallszUISetupAutoplay()
//	3. Endless Walls
//		3.1. videowallszUIVideoWallEndlessAddVideos()
//		3.2. videowallszUIVideoWallEndlessOnScroll()
//	4. "Static" Walls
//		4.1. videowallszUIVideoWallPagedAddVideos()
//		4.2. videowallszUIVideoWallPagedShowPage()
//	5. Polyfill
//		5.1. .matches
//		5.1. .closest


/////////////////////////////////////////////////
// 1. EVENTS
/////////////////////////////////////////////////

	//This is to make sure that the walls is added to the ZiggeoWP object. Needed until the core plugin no longer has the videowall codes.
	//When the system is loaded
	jQuery(document).ready( function() {
		//Sanity check - we do need the core Ziggeo plugin to be active
		if(typeof ZiggeoWP === 'undefined') {
			return false;
		}

		if(typeof ZiggeoWP.videowalls === 'undefined') {
			ZiggeoWP.videowalls = {
				//the array to hold all videowall
				//under each videowall data would be loaded_data for specific wall since each can have different data
				walls: [],
				endless: ''
			};
		}
	});




/////////////////////////////////////////////////
// 2. GENERAL FUNCTIONALITY
/////////////////////////////////////////////////

	//in case there are multiple walls on the same page, we want to be sure not to cause issues.
	// This should catch it and not declare the function again.
	if(typeof videowallszUIVideoWallShow !== 'function') {
		//show video wall based on its ID
		function videowallszUIVideoWallShow(id, searchParams) {

			if(searchParams === undefined || searchParams === "undefined" || searchParams === null || 
				typeof(searchParams) != "object") {
				searchParams = {};
			}

			var search_obj = {
				limit: 100,
				tags: (ZiggeoWP.videowalls.walls[id].tags) ? ZiggeoWP.videowalls.walls[id].tags : "",
				skip: (searchParams.skip) ? searchParams.skip : 0,
				approved: ZiggeoWP.videowalls.walls[id].status
			}

			//reference to wall
			var wall = document.getElementById(id);

			//lets check if wall is existing or not. If not, we break out and report it.
			if(!wall) {
				ziggeoDevReport('Exiting function. Specified wall is not present');
				return false;
			}

			if(!ZiggeoWP.videowalls.walls[id]) {
				ziggeoDevReport('Wall found, however no data found for the same');
				return false;
			}

			if(ZiggeoWP.videowalls.walls[id].indexing.fresh === true) {
				//a fresh wall

				var wallClass = (ZiggeoWP.videowalls.walls[id].indexing.design == 'show_pages') ? 'ziggeo-wall-showPages' : (
					(ZiggeoWP.videowalls.walls[id].indexing.design == 'slide_wall') ? 'ziggeo-wall-slideWall' : (
						(ZiggeoWP.videowalls.walls[id].indexing.design == 'chessboard_grid') ? 'ziggeo-wall-chessboardGrid' : (
							(ZiggeoWP.videowalls.walls[id].indexing.design == 'mosaic_grid') ? 'ziggeo-wall-mosaicGrid' : ""
						)
					)
				);

				wall.className = "ziggeo_videoWall " + wallClass;
			}

			//To show the page we must first index videos..
			//We are making it get 100 videos data per call
			var _index = ziggeo_app.videos.index( search_obj );

			_index.success( function (data) {
				//note: this will fire on any success results (so most of the time).
				//      This will also fire even if you do not have any videos.. (else handles that)
				if(data.length > 0) {
					//we got some videos back
					//go through videos

					//Lets set up the autoplay if it should be set
					if(ZiggeoWP.videowalls.walls[id].videos.autoplay === true) {
						videowallszUISetupAutoplay(id);
					}

					//if showPages or slideWall are true, then we use the original walls with pages
					if(ZiggeoWP.videowalls.walls[id].indexing.design == 'show_pages' ||
						ZiggeoWP.videowalls.walls[id].indexing.design == 'slide_wall') {

						//HTML output buffer
						var html = '';

						//set the video wall title
						html += ZiggeoWP.videowalls.walls[id].title;

						videowallszUIVideoWallPagedAddVideos(wall, id, html, data);
					}
					//if we are here, then this is one of the newer walls with endless scroll..
					else {
						//lets attach the event listener..
						(document.addEventListener) ? (
							window.addEventListener( 'scroll',  videowallszUIVideoWallEndlessOnScroll, false ) ) : (
							window.attachEvent( 'onscroll', videowallszUIVideoWallEndlessOnScroll) );

						if( ZiggeoWP.videowalls.walls[id]['continueFrom'] ) {
							ZiggeoWP.videowalls.walls[id]['continueFrom'] += data.length;
						}
						else {
							ZiggeoWP.videowalls.walls[id]['continueFrom'] = data.length;
						}

						videowallszUIVideoWallEndlessAddVideos(wall, id, data, true);
					}
				}
				else {
					//no results

					if(ZiggeoWP.videowalls.walls[id].indexing.fresh === false) {
						//We had some videos already..
						var tmp = document.getElementById('ziggeo-endless-loading_more');

						if(tmp) {
							tmp.innerHTML = "No more videos..";
						}
					}
					else {
						//This is the first request
						//follow the procedure for no videos (on no videos)
						ziggeoDevReport('No videos found matching the requested: ' + JSON.stringify(search_obj));

						//Lets process no videos which will return false or built HTML code.
						var html = videowallszUIVideoWallNoVideos(id, ZiggeoWP.videowalls.walls[id].title);
					}

					//cancel the scrolling event when we have no more videos to load..
					if(ZiggeoWP.videowalls.endless === id) {
						ZiggeoWP.videowalls.endless = null;
					}

					//function returns false if it should break out from the possition call was made.
					if(html === false) { return false; }

					if(ZiggeoWP.videowalls.walls[id].indexing.fresh === true) {
						wall.innerHTML = html;
					}
				}

				//The videowall is no longer fresh, so all initial actions should no longer be caried out..
				ZiggeoWP.videowalls.walls[id].indexing.fresh = false;

				//We are currently not processing any videos (in video wall context)
				ZiggeoWP.videowalls.walls[id].processing = false;
			});

			_index.error(function (args, error) {
				ziggeoDevReport('This was the error that we got back when searching for ' + JSON.stringify(args) +  ':' + error, 'error');
			});
		}
	}

	//handler for the cases when either no videos are found or videos found do not match the status requested
	// (not to be mistaken with 'video status').
	if(typeof videowallszUIVideoWallNoVideos !== 'function') {
		function videowallszUIVideoWallNoVideos(id, html) {

			//Is the vall set up to be hidden when there are no videos?
			if(ZiggeoWP.videowalls.walls[id].onNoVideos.hideWall) {
				//Lets still leave a note about it in console.
				ziggeoDevReport('VideoWall is hidden');
				return false;
			}

			//adding page - has additional (empty) class to allow nicer styling
			html += '<div id="' + id + '_page_1' + '" class="ziggeo_wallpage empty">';

			//Should we show some template?
			if(ZiggeoWP.videowalls.walls[id].onNoVideos.showTemplate) {
				html += '<ziggeoplayer ' + ZiggeoWP.videowalls.walls[id].onNoVideos.templateName + '></ziggeoplayer>';
			}
			else { //or a message instead?
				html += ZiggeoWP.videowalls.walls[id].onNoVideos.message;
			}
			//closing the page.
			html += '</div>';

			return html; //return the code we built..
		}
	}

	//Sets up the events so that we can handle the autoplay in videowalls
	if(typeof videowallszUISetupAutoplay !== 'function') {
		function videowallszUISetupAutoplay(wall_id) {

			//We need to make sure that autoplay either:
			//1. always goes from one played video to the next regardless if some video is played manually
			//2. continue playing only from the video that was last played
			//3. should check right at start if the autoplay is even allowed there..
			//* otherwise you would be starting an autoplay of next video every time you click to play one

			if(ZiggeoWP.videowalls.walls[wall_id].videos.autoplay === true) {
				//Lets see when the video stops playing
				ziggeo_app.embed_events.on('ended', function (embedding) {

					//current player
					var current_player_ref = embedding.element()[0].parentElement;

					//current wall that player is part of
					var current_wall_ref = current_player_ref.closest(".ziggeo_videoWall");

					//If we are not part of the videowall we just exit
					if(current_wall_ref === null || typeof current_wall_ref === 'undefined') {
						return false;
					}

					//If the video ID does not exist for some reason or the autoplay is turned off, exit
					if(!ZiggeoWP.videowalls.walls[current_wall_ref.id] ||
						ZiggeoWP.videowalls.walls[current_wall_ref.id].videos.autoplay !== true) {
						return false;
					}

					//Get the current wall reference
					var current_wall = ZiggeoWP.videowalls.walls[current_wall_ref.id];

					//Sanity check - is this the player from which we should continue
					if(ZiggeoWP.videowalls.walls[current_wall_ref.id].current_player &&
						ZiggeoWP.videowalls.walls[current_wall_ref.id].current_player !== embedding) {
						return false;
					}

					//Find next video player
					//There are different designs. Each design requires different approach.
					var next_player = null;

					//This will work for 'Mosaic Grid' and 'Chessboard Grid' designs as well as for videos on same page on 'Show Pages' design
					if(current_player_ref.nextElementSibling &&
						current_player_ref.nextElementSibling.tagName === 'ZIGGEOPLAYER') {

						next_player = ZiggeoApi.V2.Player.findByElement( current_player_ref.nextElementSibling );
						next_player.play();
						return true;

					}
					else {

						if(current_wall.indexing.design === 'show_pages' ) {
							if(current_player_ref.parentElement.id.indexOf('_page_') > -1) {

								var _num = ((current_player_ref.parentElement.id.replace(current_wall_ref.id + '_page_', '') *1) + 1);

								if(document.getElementById(current_wall_ref.id + '_page_' + _num)) {
									//Switch the page
									videowallszUIVideoWallPagedShowPage(current_wall_ref.id, _num);

									//find and play the video
									next_player = ZiggeoApi.V2.Player.findByElement(current_player_ref.parentElement.nextElementSibling.children[0]);
									next_player.play();
									return true;
								}
								else {
									if(current_wall.videos.autoplaytype === 'continue-run') {
										//Go back to first page
										videowallszUIVideoWallPagedShowPage(current_wall_ref.id, 1);

										//Find and play the video
										next_player = ZiggeoApi.V2.Player.findByElement(document.getElementById( current_wall_ref.id + '_page_1').children[0]);
										next_player.play();
										return true;
									}
								}

							}
						}
						else if(current_wall.indexing.design === 'slide_wall') {
							if(current_player_ref.parentElement.nextElementSibling) {
								current_player_ref.parentElement.nextElementSibling.style.display = 'block';
								current_player_ref.parentElement.style.display = 'none';

								var _next = current_player_ref.parentElement.nextElementSibling.children;

								if(_next[0] && _next[0].tagName === 'ZIGGEOPLAYER') {
									_next = _next[0];
								}
								else if(_next[1] && _next[1].tagName === 'ZIGGEOPLAYER') {
									_next = _next[1];
								}
								else {
									return false;
								}

								next_player = ZiggeoApi.V2.Player.findByElement(_next);
								next_player.play();
								return true;
							}
							else {
								if(current_wall.videos.autoplaytype === 'continue-run') {

									document.getElementById(current_wall_ref.id + '_page_1').style.display = 'block';
									current_player_ref.parentElement.style.display = 'none';

									var _next = document.getElementById(current_wall_ref.id + '_page_1').children;

									if(_next[0] && _next[0].tagName === 'ZIGGEOPLAYER') {
										_next = _next[0];
									}
									else if(_next[1] && _next[1].tagName === 'ZIGGEOPLAYER') {
										_next = _next[1];
									}
									else {
										return false;
									}

									next_player = ZiggeoApi.V2.Player.findByElement(_next);
									next_player.play();
									return true;
								}
							}
						}
						else if(current_wall.indexing.design === 'mosaic_grid') {
							if(current_player_ref.parentElement.nextElementSibling) {
								next_player = ZiggeoApi.V2.Player.findByElement(current_player_ref.parentElement.nextElementSibling.children[0]);
								next_player.play();
								return true;
							}
							else {
								if(current_wall.videos.autoplaytype === 'continue-run') {
									next_player = ZiggeoApi.V2.Player.findByElement(current_wall_ref.getElementsByClassName('mosaic_col')[0].children[0]);
									next_player.play();
									return true;
								}
							}
						}
						else if(current_wall.indexing.design === 'chessboard_grid') {
							if(current_wall.videos.autoplaytype === 'continue-run') {
								next_player = ZiggeoApi.V2.Player.findByElement(current_player_ref.parentElement.children[0]);
								next_player.play();
								return true;
							}
						}
					}
				});

				ziggeo_app.embed_events.on('playing', function (embedding) {

					//current player
					var current_player_ref = embedding.element()[0].parentElement;

					//current wall that player is part of
					var current_wall_ref = current_player_ref.closest(".ziggeo_videoWall");

					//If we are not part of the videowall we just exit
					if(current_wall_ref === null || typeof current_wall_ref === 'undefined') {
						return false;
					}

					//If the video ID does not exist for some reason or the autoplay is turned off, exit
					if(!ZiggeoWP.videowalls.walls[current_wall_ref.id] ||
						ZiggeoWP.videowalls.walls[current_wall_ref.id].videos.autoplay !== true) {
						return false;
					}

					ZiggeoWP.videowalls.walls[current_wall_ref.id].current_player = embedding;
				});

				return true;
			}

			return false;
		}
	}




/////////////////////////////////////////////////
// 3. ENDLESS WALLS
/////////////////////////////////////////////////

	// function to handle the video walls without the pagination, having the endless scroll implementation base..
	if(typeof videowallszUIVideoWallEndlessAddVideos !== 'function') {
		function videowallszUIVideoWallEndlessAddVideos(wall, id, data, _new) {

			var html = wall;

			var usedVideos = 0;
			var j = data.length;
			
			if(ZiggeoWP.videowalls.walls[id]['loadedData'] && _new === true) {
				j -= ZiggeoWP.videowalls.walls[id]['loadedData'].length;
			}

			//Chessboard grid
			if(ZiggeoWP.videowalls.walls[id].indexing.design === 'chessboard_grid') {
				var _width = (html.getBoundingClientRect().width / 8) - 4;
				_width = Math.round(_width);
			}

			//Mosaic grid codes..
			if(ZiggeoWP.videowalls.walls[id].indexing.design === 'mosaic_grid') {

				if(!ZiggeoWP.videowalls.walls[id].indexing.max_row) {
					//variable holding the maximum number of videos that will be in the mosaic row
					var _mosaic_row_max = Math.floor(Math.random() * 3) + 2;

					var cols = wall.getElementsByClassName('mosaic_col');

					if(cols === undefined || cols.length == 0) {
						//This is already made, otherwise we need to do it now..
						for(_mi = 0; _mi < _mosaic_row_max; _mi++) {	
							var _m_col = document.createElement('div');
							_m_col.className = 'mosaic_col';
							wall.appendChild(_m_col);
						}
					}

					ZiggeoWP.videowalls.walls[id].indexing.max_row = _mosaic_row_max;

					//set the class on wall with the number of rows we have..
					wall.className += ' wall_' + _mosaic_row_max + '_' + (Math.floor(Math.random() * 4)+1) + '_cols';
				}
				else {
					var _mosaic_row_max = ZiggeoWP.videowalls.walls[id].indexing.max_row;
				}
			}

			//variable holding the current video (position) in the current row
			var _mosaic_row_count = 0;

			for(i = 0, tmp=''; i < j; i++, tmp='', _mosaic_row_count++) {

				//break once we load enough of videos
				if(i >= ZiggeoWP.videowalls.walls[id].indexing.perPage) {
					break;
				}

				var tmp_embedding = '<ziggeoplayer ';

				if(ZiggeoWP.videowalls.walls[id].indexing.design === 'chessboard_grid') {

					tmp_embedding += ' ziggeo-width="' + _width + '"';
				}
				else if(ZiggeoWP.videowalls.walls[id].indexing.design === 'mosaic_grid') {
					//See if we need to go to new row
					if(_mosaic_row_max === _mosaic_row_count) {
						_mosaic_row_count = 0;
					}

					tmp_embedding += ' ziggeo-responsive';
				}
				else {
					tmp_embedding += ' ziggeo-width=' + ZiggeoWP.videowalls.walls[id].videos.width +
									' ziggeo-height=' + ZiggeoWP.videowalls.walls[id].videos.height;
				}

				tmp_embedding += ' ziggeo-video="' + data[i].token + '"' +
								( (usedVideos === 0 && ZiggeoWP.videowalls.walls[id].videos.autoplay &&
									ZiggeoWP.videowalls.walls[id].indexing.fresh === true) ? ' ziggeo-autoplay ' : '' );

				//in case we need to add the class to it
				if(ZiggeoWP.videowalls.walls[id].videos.autoplaytype !== "") {
					tmp_embedding += ' class="ziggeo-autoplay-' +
						( ( ZiggeoWP.videowalls.walls[id].videos.autoplaytype === 'continue-end' ) ? 'continue-end' : 'continue-run' ) +
						'"';
				}

				//finalize the embedding
				tmp_embedding += '></ziggeoplayer>';

				if(ZiggeoWP.videowalls.walls[id].indexing.design === 'mosaic_grid') {
					//@ADD - sort option as bellow, this is just a quick test

					html.children[_mosaic_row_count].insertAdjacentHTML('beforeend', tmp_embedding);
					usedVideos++;
					data[i] = null;//so that it is not used by other ifs..
				}
				else {

					//show all videos
					if(ZiggeoWP.videowalls.walls[id].indexing.status.indexOf('all') > -1 ) {
						html.insertAdjacentHTML('beforeend', tmp_embedding);
						usedVideos++;
						data[i] = null;//so that it is not used by other ifs..
					}
					//show only rejected videos
					if(ZiggeoWP.videowalls.walls[id].indexing.status.indexOf('rejected') > -1 ) {
					   if(data[i] !== null && data[i].approved === false) {
							html.insertAdjacentHTML('beforeend', tmp_embedding);
							usedVideos++;
							data[i] = null;//so that it is not used by other ifs..
					   }
					}
					//show only pending videos
					if(ZiggeoWP.videowalls.walls[id].indexing.status.indexOf('pending') > -1 ) {
					   if(data[i] !== null && (data[i].approved === null || data[i].approved === '') ) {
							html.insertAdjacentHTML('beforeend', tmp_embedding);
							usedVideos++;
							data[i] = null;//so that it is not used by other ifs..
					   }
					}
					//show approved videos 
					if(ZiggeoWP.videowalls.walls[id].indexing.status === '' || ZiggeoWP.videowalls.walls[id].indexing.status.indexOf('approved') > -1 ) {
						if(data[i] !== null && data[i].approved === true) {
							html.insertAdjacentHTML('beforeend', tmp_embedding);
							usedVideos++;
						}
					}
				}
			}

			var tmp = document.getElementById('ziggeo-endless-loading_more');

			if(tmp) {
				tmp.parentNode.removeChild(tmp);
			}
			else {
				var loadingElm = document.createElement('div');
				loadingElm.id = "ziggeo-endless-loading_more";
				loadingElm.innerHTML = "Loading More Videos..";
				//@HERE - make this string translatable for people using WPML.
				//It will have two strings - loading more and no more videos..
				wall.parentNode.appendChild(loadingElm, wall);
			}

			ZiggeoWP.videowalls.endless = id;

			for(i = -1, j = data.length; i < j; j--) {
				//break once we load enought of videos
				if(data[j] === null) {
					data.splice(j, 1);
				}
			}

			if(data.length > 0) {
				ZiggeoWP.videowalls.walls[id]['loadedData'] = data;
			}
		}
	}

	//handler for the scroll event, so that we can do our stuff for the endless scroll templates
	if(typeof videowallszUIVideoWallEndlessOnScroll !== 'function') {
		function videowallszUIVideoWallEndlessOnScroll() {

			var wall = null;

			//get reference to the wall..
			if( ZiggeoWP && ZiggeoWP.videowalls.walls && ZiggeoWP.videowalls.endless &&
				(wall = document.getElementById(ZiggeoWP.videowalls.endless)) ) {
				//all good
				var id = ZiggeoWP.videowalls.endless;
			}
			else {
				//OK so there is obviously no wall. Instead of recreating the same check each time, lets clean up..
				(document.removeEventListener) ? (
					window.removeEventListener( 'scroll',  videowallszUIVideoWallEndlessOnScroll ) ) : (
					window.detachEvent( 'onscroll', videowallszUIVideoWallEndlessOnScroll) );
				return false;
			}

			//lets go out if we are already processing the same request and scroll happened again..
			if(ZiggeoWP.videowalls.walls[id].processing === true) {
				return false;
			}

			//lets check the position of the bottom of the video wall from the top of the screen and then, if the same is equal to or lower than 80% of our video wall, we need to do some new things
			if(wall.getBoundingClientRect().bottom <= ( wall.getBoundingClientRect().height * 0.20 )) {
				//lets lock the indexing to not be called more than once for same scroll action..
				ZiggeoWP.videowalls.walls[id].processing = true;

				if(ZiggeoWP.videowalls.walls[id]['loadedData']) {
					//do we have more data than we need to show? if we do, lets show it right away, if not, we should load more data and show what we have as well..
					if(ZiggeoWP.videowalls.walls[id]['loadedData'].length > ZiggeoWP.videowalls.walls[id].indexing.perPage) {
						//we use the data we already got from our servers
						videowallszUIVideoWallEndlessAddVideos(wall, id, ZiggeoWP.videowalls.walls[id]['loadedData']);
						ZiggeoWP.videowalls.walls[id].processing = false;
					}
					else {
						//we are using any data that we already have and create a call to grab new ones as well.
						videowallszUIVideoWallEndlessAddVideos(wall, id, ZiggeoWP.videowalls.walls[id]['loadedData']);
						videowallszUIVideoWallShow(id, { skip: ZiggeoWP.videowalls.walls[id]['continueFrom'] });
					}
				}
			}
		}
	}




/////////////////////////////////////////////////
// 4. "STATIC" WALLS
/////////////////////////////////////////////////

	// function to handle the video walls with the pagination
	if(typeof videowallszUIVideoWallPagedAddVideos !== 'function') {
		function videowallszUIVideoWallPagedAddVideos(wall, id, html, data) {

			//number of videos per page currently
			var currentVideosPageCount = 0;
			//total number of videos that will be shown
			var usedVideos = 0;
			//What page are we on?
			var currentPage = 0;
			//did any videos match the checks while listing them - so that we do not place multiple pages since the count stays on 0
			var newPage = true;

			for(i = 0, j = data.length, tmp=''; i < j; i++, tmp='') {

				var tmp_embedding = '<ziggeoplayer ' +
								' ziggeo-width=' + ZiggeoWP.videowalls.walls[id].videos.width +
								' ziggeo-height=' + ZiggeoWP.videowalls.walls[id].videos.height +
								' ziggeo-video="' + data[i].token + '"' +
								( (usedVideos === 0 && ZiggeoWP.videowalls.walls[id].videos.autoplay) ? ' ziggeo-autoplay ' : '' );

				//in case we need to add the class to it
				if(ZiggeoWP.videowalls.walls[id].videos.autoplaytype !== "") {
					tmp_embedding += ' class="ziggeo-autoplay-' +
						( ( ZiggeoWP.videowalls.walls[id].videos.autoplaytype === 'continue-end' ) ? 'continue-end' : 'continue-run' ) +
						'"';
				}

				//finalize the embedding
				tmp_embedding += '></ziggeoplayer>';

				//show all videos
				if(ZiggeoWP.videowalls.walls[id].indexing.status.indexOf('all') > -1 ) {
					tmp += tmp_embedding;
					usedVideos++;
					currentVideosPageCount++;
					data[i] = null;//so that it is not used by other ifs..
				}
				//show only rejected videos
				if(ZiggeoWP.videowalls.walls[id].indexing.status.indexOf('rejected') > -1 ) {
					if(data[i] !== null && data[i].approved === false) {
						tmp += tmp_embedding;
						usedVideos++;
						currentVideosPageCount++;
						data[i] = null;//so that it is not used by other ifs..
					}
				}
				//show only pending videos
				if(ZiggeoWP.videowalls.walls[id].indexing.status.indexOf('pending') > -1 ) {
					if(data[i] !== null && (data[i].approved === null || data[i].approved === '') ) {
						tmp += tmp_embedding;
						usedVideos++;
						currentVideosPageCount++;
						data[i] = null;//so that it is not used by other ifs..
					}
				}
				//show approved videos 
				if(ZiggeoWP.videowalls.walls[id].indexing.status === '' || ZiggeoWP.videowalls.walls[id].indexing.status.indexOf('approved') > -1 ) {
					if(data[i] !== null && data[i].approved === true) {
						tmp += tmp_embedding;
						usedVideos++;
						currentVideosPageCount++;
					}
				}

				//Do we need to create a new page?
				//We only create new page if there were any videos to add, otherwise if 1 video per page is set, we would end up with empty pages when videos are not added..
				if(currentVideosPageCount === 1 && newPage === true) {
					//we do
					currentPage++;

					//For slidewall we add next right away..
					if(ZiggeoWP.videowalls.walls[id].indexing.design == 'slide_wall') {
						if(currentPage > 1) {
							html += '<div class="ziggeo_videowall_slide_next"  onclick="videowallszUIVideoWallPagedShowPage(\'' + id + '\', ' + currentPage + ');"></div>';
							html += '</div>';
						}
					}

					html += '<div id="' + id + '_page_' + currentPage + '" class="ziggeo_wallpage" ';

					if(currentPage > 1) {
						html += ' style="display:none;" ';
					}

					html += '>';

					//For slidewall we add back right away as well
					if(ZiggeoWP.videowalls.walls[id].indexing.design == 'slide_wall') {
						if(currentPage > 1) {
							html += '<div class="ziggeo_videowall_slide_previous"  onclick="videowallszUIVideoWallPagedShowPage(\'' + id + '\', ' + (currentPage-1) + ');"></div>';
						}
					}

					html += tmp;
					tmp = '';
					newPage = false;
				}

				//combining the code if any
				if(tmp !== '') {
					html += tmp;
				}

				//Do we have enough of vidoes on this page and its time to create a new one?
				if(currentVideosPageCount === ZiggeoWP.videowalls.walls[id].indexing.perPage) {
					//Yup, we do
					if(ZiggeoWP.videowalls.walls[id].indexing.design == 'show_pages') {
						html += '</div>';
					}
					currentVideosPageCount = 0;
					newPage = true;
				}
			}

			//Sometimes we will have videos, however due to calling parameters the same might not be added.
			//At this time we would need to show the log in console about the same and show the on_no_videos message / setup
			if(usedVideos === 0 && i > 0) {
				html = videowallszUIVideoWallNoVideos(id, html);

				//leaving a note of this
				ziggeoDevReport('You have videos, just not the ones matching your request');

				if(html === false) {
					return false;
				}
			}

			//In case last page has less videos than per page limit, we need to apply the closing tag
			if(currentVideosPageCount < ZiggeoWP.videowalls.walls[id].indexing.perPage && newPage === false) {
				html += '</div>';
			}

			//Lets add pages if showPages is set
			if(ZiggeoWP.videowalls.walls[id].indexing.design == 'show_pages') {
				for(i = 0; i < currentPage; i++) {
					html += '<div class="ziggeo_wallpage_number' + ((i===0) ? ' current' : '') + '" onclick="videowallszUIVideoWallPagedShowPage(\'' + id + '\', ' + (i+1) + ',this);">' + (i+1) + '</div>';
				}
				html += '<br class="clear" style="clear:both;">';
			}

			//Lets add everything so that it is shown..
			wall.innerHTML = html;

			//lets show it:
			wall.style.display = 'block';
		}
	}

	//Shows the selected page and hides the rest of the specific video wall.
	if(typeof videowallszUIVideoWallPagedShowPage !== 'function') {
		function videowallszUIVideoWallPagedShowPage(id, page, current) {
			//reference to wall
			var wall = document.getElementById(id);

			//lets check if wall is existing or not. If not, we break out and report it.
			if(!wall) {
				ziggeoDevReport('Exiting function. Specified wall is not present');
				return false;
			}

			var pageID = id + '_page_' + page;

			var newPage = document.getElementById(pageID);

			//Get all pages under current wall
			var pages = wall.getElementsByClassName('ziggeo_wallpage');

			//Hide all of the pages
			for(i = 0, j = pages.length; i < j; i++) {
				pages[i].style.display = 'none';
			}

			//set the visual indicator of what page is selected
			var pageNumbers = wall.getElementsByClassName('ziggeo_wallpage_number');

			if(current === null || typeof current === 'undefined') {
				current = wall.getElementsByClassName('ziggeo_wallpage_number')[page-1];
			}

			//This is only active if we show page numbers / page buttons
			if(current) {
				//reset style of the page number buttons
				for(i = 0, j = pageNumbers.length; i < j; i++) {
					pageNumbers[i].className = 'ziggeo_wallpage_number';
				}

				//adding .current class to the existing list of classes
				current.className = 'ziggeo_wallpage_number current';
			}

			newPage.style.display = 'block';
		}
	}




/////////////////////////////////////////////////
// 5. POLYFILL
/////////////////////////////////////////////////

	//Polyfill for .closest()
	if (!Element.prototype.matches) {
		Element.prototype.matches = Element.prototype.msMatchesSelector || 
									Element.prototype.webkitMatchesSelector;
	}

	if (!Element.prototype.closest) {
		Element.prototype.closest = function(s) {
			var el = this;

			do {
				if (el.matches(s)) return el;
				el = el.parentElement || el.parentNode;
			} while (el !== null && el.nodeType === 1);

			return null;
		};
	}
