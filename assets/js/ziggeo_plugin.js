//This holds all of the functions that are going to be called in from the public side
//
// INDEX
//********
// 1. Popups and overlays
//		1.1. ziggeoShowOverlay()
//		1.2. ziggeoRemoveOverlay()
//		1.3. ziggeoShowOverlayWithRecorder()
//		1.4. ziggeoShowOverlayWithPlayer()
//		1.5. ziggeoTemplatesListPrepCode()
//		1.6. ziggeoShowOverlayWithTemplatesList()
// 2. Helper functions
//		2.1. ziggeoDevReport()
//		2.2. ziggeoAjax()
// 3. API
//		3.1. ziggeoAPIGetVideo()
//		3.2. ziggeoAPIGetVideosData()
// 4. Cleanup and preparation functions
//		* ziggeoCleanTextValues()
//		* ziggeoRestoreTextValues()



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
							'You can always change your mind by clicking "Cancel".'
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
	function ziggeoAjax(data, callback) {

		data.action = 'ziggeo_ajax';
		data.ajax_nonce = ZiggeoWP.ajax_nonce;

		jQuery.post(ajaxurl, data, function(response) {
			callback(response);
		});
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

	//ziggeoAPIGetVideosCount(
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
					callback_on_data(data);
				}
			}
			else {
				if(typeof callback_on_data !== 'undefined') {
					callback_no_data(data);
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