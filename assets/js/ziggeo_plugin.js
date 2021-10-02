//This holds all of the functions that are going to be called in from the public side
//
// INDEX
//********
// 1. Popups and overlays
//		* ziggeoShowOverlay()
//		* ziggeoRemoveOverlay()
//		* ziggeoShowOverlayWithRecorder()
//		* ziggeoShowOverlayWithPlayer()
//		* ziggeoTemplatesListPrepCode()
//		* ziggeoShowOverlayWithTemplatesList()
// 2. Helper functions
//		* ziggeoDevReport()
//		* ziggeoAjax()
//		* ziggeoInsertTextToPostEditor()
//		* ziggeoStringToSafe()
//		* ziggeoUnixTimetoString()
// 3. API
//		* ziggeoAPIGetVideo()
//		* ziggeoAPIGetVideosData()
//		* ziggeoDAPIRegisterVideos()
// 4. Cleanup and preparation functions
//		* ziggeoCleanTextValues()
//		* ziggeoRestoreTextValues()
// 5. Events
//		* jQuery.ready()


/////////////////////////////////////////////////
// 1. POPUPS AND OVERLAYS                      //
/////////////////////////////////////////////////

	//This just creates the basic overlay
	function ziggeoShowOverlay() {
		//create element covering entire screen
		var o = document.createElement('div');
		o.id = "ziggeo-overlay-screen";
		o.addEventListener( 'click', ziggeoRemoveOverlay, false );
		document.body.appendChild(o);

		//lets create element that will close this..
		var c = document.createElement('div');
		c.id="ziggeo-overlay-close";
		c.innerHTML = "x";
		c.addEventListener( 'click', ziggeoRemoveOverlay, false );
		document.getElementById('ziggeo-overlay-screen').appendChild(c);
	}

	//destroys overlay and recorder over it
	//TODO maybe make it possible to close only if the video was uploaded (when called through withRecorder), for now, lets just close it.
	function ziggeoRemoveOverlay() {
		//If recorder or player is shown this one will be available
		jQuery("#ziggeo-video-screen").remove();
		//If templates modal is shown this one will be available
		jQuery("#ziggeo-templates-list-holder").remove();
		//General overlay screen element which is always present
		jQuery("#ziggeo-overlay-screen").remove();

		//add hook to close other screens as well maybe @consider
	}

	//shows overlay with the recorder element
	function ziggeoShowOverlayWithRecorder(type) {

		ziggeoShowOverlay();

		//now the element that will hold our recorder (we make sure that it will be fully displayed on mobile and desktop screens)..
		var s = document.createElement('div');
		s.id="ziggeo-video-screen";
		document.body.appendChild(s);

		//Recorder element parameters
		_attrs = {
				width: 300,
				height: 300,
				theme: "modern",
				themecolor: "red"
		};

		if(type !== null && typeof type !== 'undefined') {
			if(type === 'screen') {
				_attrs.allowscreen = true;
			}
			else if(type === 'audio') {
				//@here - not now
			}
		}

		//create recorder using v2 recorder code
		var recorder = new ZiggeoApi.V2.Recorder({
		element: document.getElementById('ziggeo-video-screen'),
			attrs: _attrs
		});

		recorder.activate();

		//add event handler
		recorder.on("verified", function () {
			ZiggeoWP.hooks.fire('ziggeo_overlay_popup_on_verify', recorder);
		});
	}

	//shows overlay with the player element
	function ziggeoShowOverlayWithPlayer(token_or_key, link) {

		if(typeof token_or_key === 'undefined' && typeof link === 'undefined') {
			return false;
		}

		ziggeoShowOverlay();

		//now the element that will hold our player (we make sure that it will be fully displayed on mobile and desktop screens)..
		var s = document.createElement('div');
		s.id="ziggeo-video-screen";
		document.body.appendChild(s);

		//Player element parameters
		_attrs = {
				width: 300,
				height: 300,
				theme: "modern",
				themecolor: "red"
		};

		if(typeof link === 'undefined') {
			_attrs.video = token_or_key;	
		}
		else {
			_attrs.source = link;
		}

		//create player using v2 player code
		var player = new ZiggeoApi.V2.Player({
		element: document.getElementById('ziggeo-video-screen'),
			attrs: _attrs
		});

		player.activate();
	}

	//Prepares the code with a simple code highlighting
	//>code - raw code
	//>code_preview - the element reference in which the code will be added
	function ziggeoTemplatesListPrepCode(code, code_preview) {
		var _code = code;

		_code = _code.replace(/\=/g, '<span class="ziggeo_code_a">=</span>');
		_code = _code.replace(/\'/g, '<span class="ziggeo_code_a">' + "'" + '</span>');
		_code = _code.substr(0,1) +
				'<span class="ziggeo_code_t">' +
				_code.substr(1, _code.indexOf(' ')-1) +
				'</span>' +
				_code.substr(_code.indexOf(' '));
		_code = _code.replace(/\[/g, '<span class="ziggeo_code_sb">[</span>');
		_code = _code.replace(/\]/g, '<span class="ziggeo_code_sb">]</span>');

		code_preview.innerHTML = _code;
		code_preview.original_code = code;
	}

	//Show overlay with the list of embeddings
	function ziggeoShowOverlayWithTemplatesList() {

		ziggeoShowOverlay();

		var list_modal = document.createElement('div');
		list_modal.id = 'ziggeo-templates-list-holder';
		document.body.appendChild(list_modal);

		var list_holder = document.createElement('div');
		list_holder.innerHTML = document.getElementById('ziggeo-templates-list').innerHTML;
		list_modal.appendChild(list_holder);

		var label = document.createElement('label');
		label.textContent = "Template Code Preview:";
		label.htmlFor = 'ziggeo-template-code';
		list_holder.appendChild(label);

		var explanation = document.createElement('div');
		explanation.id = 'ziggeo-template-code';
		explanation.setAttribute('locked', false);
		list_holder.appendChild(explanation);

		var msg = '';

		//Set the events handling for mouse move
		jQuery('#ziggeo-templates-list-insert li').on('hover', function() {
			if(explanation.getAttribute('locked') == 'true') {
				return;
			}

			//prepare the code highlight
			ziggeoTemplatesListPrepCode(this.getElementsByClassName('ziggeo_template_code')[0].innerHTML, explanation, );

			//set the template name
			explanation.template_name = this.getElementsByClassName('ziggeo_template_name')[0].innerHTML;
		});

		//Sets the event handling for the mouse click
		jQuery('#ziggeo-templates-list-insert li').on('click', function() {

			//Make all buttons clickable
			jQuery('#ziggeo-templates-list-holder button')[0].className = '';
			jQuery('#ziggeo-templates-list-holder button')[1].className = '';

			if(explanation.getAttribute('locked') == 'true') {
				explanation.setAttribute('locked', 'false');
			}
			else {
				explanation.setAttribute('locked', 'true');
			}

			//Lets also set this one in the preview
			ziggeoTemplatesListPrepCode(this.getElementsByClassName('ziggeo_template_code')[0].innerHTML, explanation);

			//set the template name
			explanation.template_name = this.getElementsByClassName('ziggeo_template_name')[0].innerHTML;
		});

		//Create insert and cancel buttons
		var _btn_cancel = document.createElement('button');
		_btn_cancel.addEventListener( 'click', function() {
			jQuery("#ziggeo-templates-list-holder").remove();
			ziggeoRemoveOverlay();
		}, false );
		_btn_cancel.innerHTML = 'Cancel';

		var _btn_add = document.createElement('button');
		_btn_add.className = 'ziggeo_nonclickable';
		_btn_add.addEventListener('click', function(event) {

			if(event.altKey || event.shiftKey) {
				ziggeoInsertTextToPostEditor(explanation.original_code);
			}
			else {
				ziggeoInsertTextToPostEditor('[ziggeo ' + explanation.template_name + ']');
			}

			//close it all
			jQuery("#ziggeo-templates-list-holder").remove();
			ziggeoRemoveOverlay();
		}, false);
		_btn_add.innerHTML = 'Insert';

		list_holder.appendChild(_btn_add);
		list_holder.appendChild(_btn_cancel);

		var info = document.createElement('p');
		info.textContent = 'You can hover over template name to preview its code.' +
							'Once you find template you want to insert click to lock it.' +
							'Then click on "Insert" button.' +
							'You can always change your mind by clicking "Cancel".' +
							'Hint: press Shift or Alt when clicking Insert to insert template code';
		list_holder.appendChild(info);
	}




/////////////////////////////////////////////////
// 2. HELPER FUNCTIONS                         //
/////////////////////////////////////////////////

	//simplifier to allow us to report in console, however only if the developer mode is turned on.
	//useful for IE for example to avoid issues when console is not open and non standard methods (log always fires instead)
	function ziggeoDevReport(msg, type) {

		//Only works if the dev mode is on..
		if( typeof(ziggeo_dev) !== 'undefined' && typeof(console) !== 'undefined') {

			if(ziggeo_dev === true) {
				if(type === 'error') {
					if(console.error) {
						console.error(msg);
					}
					else {
						console.log(msg);
					}
				}
				else if(type === 'log' || type === undefined) {
					console.log(msg);
				}
			}

			return true;
		}

		return false;
	}

	//AJAX handler
	//data = the data to send
	//callback = function to call once the response is received
	//is_form_data = true if FormData is set as data, if it is an object, just ignore the same.
	function ziggeoAjax(data, callback, is_form_data) {

		if(typeof ajaxurl === 'undefined') {
			var ajaxurl = ZiggeoWP.ajax_url;
		}

		if(is_form_data === true) {
			data.append('action', 'ziggeo_ajax');
			data.append('ajax_nonce', ZiggeoWP.ajax_nonce);

			jQuery.ajax({
				url : ajaxurl,
				type: "POST",
				data : data,
				processData: false,
				contentType: false,
				success:function(response) {
					if(typeof callback !== 'undefined') {
						callback(response);
					}
				},
				error: function(response, error_info) {
				}
			});
		}
		else {
			data.action = 'ziggeo_ajax';
			data.ajax_nonce = ZiggeoWP.ajax_nonce;

			jQuery.post(ajaxurl, data, function(response) {
				if(typeof callback !== 'undefined') {
					callback(response);
				}
			});
		}
	}

	//Used to be able to just pass the text into editor, while one place holds all
	// the logic needed to actually do that
	function ziggeoInsertTextToPostEditor(msg) {
		//How can it be delivered?

		//w5 / Gutenberg approach
		if(wp.blocks) {
			var t_block = wp.blocks.createBlock( 'core/paragraph', { content: msg } );
			wp.data.dispatch( 'core/editor' ).insertBlocks( t_block );
		}
		//This should be available
		else if(wpActiveEditor) {
			window.parent.send_to_editor(msg);
		}
		//Unsuported editor found
		else {
			ziggeoDevReport('Unsupported editor detected. Can not pass the message that should be passed', 'error');
		}
	}

	//Function used to change spaces into underscores and all letters to lowercase
	function ziggeoStringToSafe(str) {
		return str.toLocaleLowerCase().replace(/ /g, '_');
	}

	//Converts the UNIX timestamp into a readable time format using the format provided.
	// If not provided it will return time in  YYYY/MM/DD HH:MM:SS format
	// tried making it follow PHP file format: https://www.php.net/manual/en/datetime.format.php
	//
	//	Y    A full numeric representation of a year, 4 digits                      Examples: 1999 or 2003
	//	y    A two digit representation of a year                                   Examples: 99 or 03
	//	n    Numeric representation of a month, without leading zeros               1 through 12
	//	m    Numeric representation of a month, with leading zeros                  01 through 12
	//	M    A short textual representation of a month, three letters               Jan through Dec
	//	F    A full textual representation of a month, such as January or March     January through December
	//	j    Day of the month without leading zeros                                 1 to 31
	//	d    Day of the month, 2 digits with leading zeros                          01 to 31
	//	D    A textual representation of a day, three letters                       Mon through Sun
	//	l    (lowercase 'L') A full textual representation of the day of the week   Sunday through Saturday
	//	g    12-hour format of an hour without leading zeros                        1 through 12
	//	h    12-hour format of an hour with leading zeros                           01 through 12
	//	G    24-hour format of an hour without leading zeros                        0 through 23
	//	H    24-hour format of an hour with leading zeros                           00 through 23
	//	i    Minutes with leading zeros                                             00 to 59
	//	s    Seconds with leading zeros                                             00 through 59
	//	a    Lowercase Ante meridiem and Post meridiem                              am or pm
	//	A    Uppercase Ante meridiem and Post meridiem                              AM or PM
	function ziggeoUnixTimetoString(unix_timestamp, format){

		if(typeof unix_timestamp === 'undefined') {
			ziggeoDevReport('no timestamp was provided', 'error');
			return '';
		}

		if(typeof format !== 'string') {
			format = 'Y/M/d H:i:s';
		}

		var months_s = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
		var months_l = ['January','February','March','April','May','June','July','August','September','October','November','December'];
		var days_s = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
		var days_l = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
		var meridiem_s = ['am', 'pm'];
		var meridiem_l = ['AM', 'PM'];

		var time = new Date(unix_timestamp * 1000);

		// year
		var yyyy = time.getFullYear();                              // 2021
		var yy = yyyy.toString().slice(2);                          // 21

		// month
		var m = time.getMonth()+1;                                  // 4
		var mm = (m < 10) ? '0' + m.toString() : m;                 // 04
		var mmm = months_s[m-1];                                    // Apr
		var mmmm = months_l[m-1];                                   // April

		// day
		var d = time.getDate();				                        // 4
		var dd = (d < 10) ? '0' + d.toString() : d;                 // 04
		var ddd = days_s[time.getDay()];                            // Sun
		var dddd = days_l[time.getDay()];                           // Sunday

		// hour
		var h_24 = time.getHours();                                 // 8
		var hh_24 = (h_24 < 10) ? '0' + h_24.toString() : h_24;     // 08

		var h_12 = (h_24 > 12) ? h_24 - 12: h_24;                   // 8
		var hh_12 = (h_12 < 10) ? '0' + h_12.toString() : h_12;     // 08

		// meridiem mark
		var meridiem_mark = (hh_24 > 12) ? 1 : 0;

		// minute
		var min = time.getMinutes();                                // 8
		min = (min > 10) ? min : '0' + min.toString();              // 08

		// seconds
		var sec = time.getSeconds();                                // 8
		sec = (sec > 10) ? sec : '0' + sec.toString();              // 08

		//We need to do this in order that will not catch any numbers given in format.
		var result = format.replace('Y', 'xx_01').replace('y', 'xx_02');
		result = result.replace('n', 'xx_03').replace('m', 'xx_04');
		result = result.replace('j', 'xx_05').replace('d', 'xx_06');
		result = result.replace('g', 'xx_07').replace('h', 'xx_08');
		result = result.replace('G', 'xx_09').replace('H', 'xx_10');
		result = result.replace('i', 'xx_11').replace('s', 'xx_12');
		result = result.replace('a', 'xx_13').replace('A', 'xx_14');
		result = result.replace('l', 'xx_15').replace('F', 'xx_16');
		result = result.replace('M', 'xx_17').replace('D', 'xx_18');

		// Now to get actual values..
		result = result.replace('xx_01', yyyy).replace('xx_02', yy);
		result = result.replace('xx_03', m).replace('xx_04', mm);
		result = result.replace('xx_05', d).replace('xx_06', dd);
		result = result.replace('xx_07', h_12).replace('xx_08', hh_12);
		result = result.replace('xx_09', h_24).replace('xx_10', hh_24);
		result = result.replace('xx_11', min).replace('xx_12', sec);
		result = result.replace('xx_13', meridiem_s[meridiem_mark]).replace('xx_14', meridiem_l[meridiem_mark]);
		result = result.replace('xx_15', dddd).replace('xx_16', mmmm);
		result = result.replace('xx_17', mmm).replace('xx_18', ddd);

		return result;
	}




/////////////////////////////////////////////////
// 3. API                                      //
/////////////////////////////////////////////////

	//retrieves the information about one specific video based on its video token or video key
	//ziggeoAPIGetVideo(
	//	'VIDEO_TOKEN',
	//	function(data) { console.log('success'); console.log(data); },
	//	function(args, error) { console.log('something happened'); console.log(args, error) });
	function ziggeoAPIGetVideo(token_or_key, callback_on_data, callback_error, application) {

		if(typeof token_or_key === 'undefined') {
			ziggeoDevReport('Unable to find anything if token nor key is passed over.', 'error');
			return false;
		}

		var result = ziggeo_app.videos.get(token_or_key);

		result.success( function (data) {
			//We got result
			if(typeof callback_on_data !== 'undefined') {
				callback_on_data(data);
			}
		});

		result.error(function (args, error) {
			//there was an error
			if(typeof callback_error !== 'undefined') {
				callback_error(args.__message);
			}
		});
	}

	//ziggeoAPIGetVideosData(
	//	{}, //Index (search) object stating what you are searching for
	//	function(data) { console.log('success, we found something'); console.log(data); },
	//	function(data) { console.log('success, however nothing was found'); console.log(data); },
	//	function(error) { console.log('Ups, something happened'); console.log(error)}
	//);
	function ziggeoAPIGetVideosData(query_obj, callback_on_data, callback_no_data, callback_error, application) {

		if(typeof query_obj === 'undefined') {
			ziggeoDevReport('Unable to find anything if query is not passed over.', 'error');
			return false;
		}

		var index = ziggeo_app.videos.index(query_obj);

		index.success( function (data) {
			//We got results

			if(data.length > 0) {
				if(typeof callback_on_data !== 'undefined') {
					if(typeof callback_on_data === 'function') {
						callback_on_data(data);
					}
					else if(typeof window[callback_on_data] === 'function') {
						window[callback_on_data](data);
					}
					else {
						ziggeoDevReport('Could not call: ' + callback_on_data);
					}
				}
			}
			else {
				if(typeof callback_no_data !== 'undefined') {
					if(typeof callback_no_data === 'function') {
						callback_no_data(data);
					}
					else if(typeof window[callback_no_data] === 'function') {
						window[callback_no_data](data);
					}
					else {
						ziggeoDevReport('Could not call: ' + callback_no_data);
					}
				}
			}
		});

		index.error(function (args, error) {
			//there was an error
			if(typeof callback_error !== 'undefined') {
				callback_error(args.__message);
			}
		});
	}

	/* - requires server side SDK (will be added in future)
	function ziggeoAPIGetVideosCount(query_obj, callback_on_data, callback_error, application) {

		if(typeof query_obj === 'undefined') {
			ziggeoDevReport('Unable to find anything if query is not passed over.', 'error');
			return false;
		}

		var result = ziggeo_app.videos.count(query_obj);

		result.success( function (data) {
			//We got result
			if(typeof callback_on_data !== 'undefined') {
				callback_on_data(data);
			}
		});

		result.error(function (args, error) {
			//there was an error
			if(typeof callback_error !== 'undefined') {
				callback_error(args.__message);
			}
		});
	}
	*/

	//DAPI / Dashboard API

	//Funtion that helps us mention that there is some new video recorded on Wordpress
	function ziggeoDAPIRegisterVideos(token) {
		ziggeoAjax({
			operation: 'video_verified',
			recorded_video: true,
			token: token
		});
	}




/////////////////////////////////////////////////
// 4. CLEANUP AND PREPARATION FUNCTIONS        //
/////////////////////////////////////////////////

	//This is to allow us to remove characters that would cause issues while saving or showing the info
	function ziggeoCleanTextValues(value, replace_array) {
		//replace '
		value = value.replace(/\'/g, '&apos;');

		//replace "
		value = value.replace(/\"/g, '&quot;');

		if(typeof replace_array !== 'undefined') {
			//go through array and replace additional characters
		}

		return value;
	}

	//Used to remove entities and put them as original characters instead, so it looks right
	function ziggeoRestoreTextValues(value, restore_array) {
		//restore '
		value = value.replace(/\&apos\;/g, "'");

		//restore "
		value = value.replace(/\&quot\;/g, '"');

		//Be mindful of what you are using to search for, you might want or need to escape special characters..
		if(typeof restore_array !== 'undefined') {
			//go through array and replace additional characters
			for(i = 0, c = restore_array.length; i < c; i++) {
				value = value.replace(new RegExp(restore_array[i].from, "g"), restore_array[i].to);
				//dyn regex: Thank you acdcjunior https://stackoverflow.com/a/17886301
			}
		}

		return value;
	}




/////////////////////////////////////////////////
// 5. EVENTS                                   //
/////////////////////////////////////////////////

	jQuery(document).ready( function() {
		if(typeof ziggeo_app !== 'undefined') {
			ziggeo_app.embed_events.on("verified", function (embedding) {
				ziggeoDAPIRegisterVideos(embedding.get('video'));
			});
		}
	});