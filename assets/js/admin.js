// This file holds all of the functions that would be needed for the features used on admin backend to work
// All functions that start with "ziggeoP" are marked as really needing to stay as they are (important), others might be turned into customizable functions
//
// INDEX
//********
// 1. Dashboard Helpers
//		* ziggeoPUIChangeTab()
//		* ziggeoPOnboard()
//		* ziggeoPUIFeedbackRemoval()
//		* ziggeoPUIMessenger()
//		* ziggeoCommentsToggle()
//		* ziggeoPValidateEmpty()
//		* ziggeoScrollTo()
// 2. Onload
//		* jQuery.ready()
// 3. Hooks init
//		* ziggeoPUIHooksInit()
// 4. Fields Support
//		* ziggeoDynamicFieldsSupportInit()
// 5. Templates Editor Functions
//		* ziggeoTemplatesEditorGet()             // <v3.0 ziggeoGetEditor()
//		* ziggeoTemplatesEditorSetCode()         // <v3.0 ziggeoTemplatesEditorSetText()
//		* ziggeoTemplatesEditorRePopulate()
//		* ziggeoTemplatesGetID()                 // <v3.0 ziggeoGetTemplateID()
//		* ziggeoTemplatesSetID()                 // <v3.0 ziggeoSetTemplateID()
//		* ziggeoTemplatesTemplateObjectGet()     // <v3.0 ziggeoTemplateGetTemplateObject()
//		* ziggeoTemplatesTemplateObjectSet()     // <v3.0 ziggeoTemplateSetTemplateObject()
//		* ziggeoTemplatesTemplateObjectAdd()
//		* ziggeoTemplatesParametersAdd()
//		* ziggeoTemplatesParametersRemove()
//		5.1 Templates
//			* ziggeoTemplatesManageInit()
//			* ziggeoTemplatesEdit()
//			* ziggeoTemplatesRemove()
//			* ziggeoTemplatesSave()
//			* ziggeoTemplatesUpdate()
//			* ziggeoTemplatesShortcodeGet()
//			* ziggeoPUIManageTemplate()
//			* 						ziggeoPUITemplatesManage()
//			* ziggeoPUITemplatesChange()
//			* ziggeoPUITemplatesTurnIntoNew()
//			* ziggeoTemplatesBaseGet()           // <v3.0 ziggeoTemplatesBase()
//			* ziggeoTemplatesBaseSet()
// 6. Integrations
//		6.1 Integrations Tab
//			* ziggeoPUIIntegrationStatus()
// 7. WP Editor
//		* ziggeoSetupNewWPToolbar()
//		* ziggeoSetupOverlayRecorder()
//		* ziggeoSetupOverlayTemplates()
// 8. Notifications
//		* ziggeoPUINotificationsInit()
//		* ziggeoNotificationManage()
// 9. Videos Page
//		* ziggeoPUIVideosInit()
//		* ziggeoPUIVideosMessage()
//		* ziggeoPUIVideosFilter()
//		* ziggeoVideosFindByTag()
//		* ziggeoPUIVideosNoVideos()
//		* ziggeoPUIVideosHasVideos()
//		* ziggeoPUIVideosHasVideosApproved()
//		* ziggeoPUIVideosHasVideosPending()
//		* ziggeoPUIVideosHasVideosRejected()
//		* ziggeoPUIVideosCreateTools()
//		* ziggeoPUIVideosCreateInfoSection()
//		* ziggeoVideosApprove()
//		* ziggeoVideosReject()
//		* ziggeoVideosRemove()
//		* ziggeoVideosGetURL()
//		* ziggeoVideosEditCustomData()
//		* ziggeoVideosGrabShortcodes()
//		* ziggeoPUIVideosPopupCreate()
//		* ziggeoPUIVideosPopupDestroy()
//		* ziggeoPUIVideosFilterReset()
//		* ziggeoPUIVideosPageCreateNavigation()
//		* ziggeoPUIVideosPageSwitch()
//		* ziggeoPUIVideosPageCounter()
//		* ziggeoPUIVideosIndexNotAllowed()
// 10. Addons page
//		* ziggeoPUIAddonsInit()
//		* ziggeoPUIAddonsSwitch()
// 11. SDK Page
//		* ziggeoPUISDKInit()
//		* ziggeoPUISDKImageToggle()
//		* ziggeoPUISDKDropdown()
//		* ziggeoPUISDKButtons()
//		* ziggeoPUISDKAnalyticsCreateGraphs()
//		* ziggeoPUISDKAnalyticsCreateGraphZoomedIn()
//		* ziggeoPUISDKEffectsProfileProcessList()
//		* ziggeoPUISDKEffectsProfileDelete()
//		* ziggeoPUISDKEffectsProfileCreate()
//		* ziggeoPUISDKEffectsProfileProcessCreateForm()
//		* ziggeoPUICtrlClose()
//		* ziggeoPUISDKEffectsProfileProcessWatermarkImagePreview()
//		* ziggeoPUISDKEffectProfilesButtons()
//		* ziggeoPUISDKEffectProfilesButtonForms()
//		* ziggeoPUISDKEffectProfilesButtonFormPopup()
// 12. Events Editor
//		* ziggeoPUIEEInit()
//		* ziggeoPUIEEDefauts()
//		* ziggeoPUIEESaveTemplate()
//		* ziggeoPUIEEGenerateShortcode()
// 13. Autocomplete Control
//		* ziggeoPUICAutoCompleteInit()
//		* ziggeoPUICAutoCompleteFilter()





/////////////////////////////////////////////////
// 1. DASHBOARD HELPERS                        //
/////////////////////////////////////////////////

	//Changes tabs in WordPress admin panel
	// > tab param is a text representation of the tab that we should show
	function ziggeoPUIChangeTab(tab) {
		//lets get the currently selected tab so that we can remove .current from the same
		var selectedTab = document.getElementsByClassName('ziggeo-tabName selected');
		selectedTab[0].className = 'ziggeo-tabName';

		//Now we should hide all of the frames
		var shownFrames = document.getElementsByClassName('ziggeo-frame');

		for(i = 0; i < shownFrames.length; i++)
		{
			shownFrames[i].style.display = 'none';
		}

		if(tab === 'templates') {
			document.getElementById('submit').style.display = 'none';
		}
		else {
			document.getElementById('submit').style.display = 'block';
		}

		//Now lets set the right tab to be shown as selected
		document.getElementById('ziggeo-tab_id_' + tab).className = 'ziggeo-tabName selected';
		document.getElementById('ziggeo-tab_' + tab).style.display = 'block';
	}

	//Function to help with onboarding
	//It would fire if someone has installed the plugin and did not create account, so that it makes it easy to test things out right away
	function ziggeoPOnboard(name, email, success, error) {
		jQuery.ajax({
			type: "POST",
			crossDomain: true,
			url: "https://srvapi.ziggeo.com/v1/accounts",
			data: {
				name: name,
				email: email
			},
			dataType: "json",
			success: function (result) {
				success({
					'token': result.application.token,
					'private_key': result.application.private_key,
					'encryption_key': result.application.encryption_key
				});
			},
			error: function (err) {
				var errors = "";
				for (var key in err.responseJSON)
					errors += err.responseJSON[key];                
				error(errors);
			}
		});
	}

	//Removes the feedback banner by setting the hidden option to checked and submits the form..
	// If you are seeing this comment and have not left a feedback, please do. Having the top feedback is important to make more updates and support you
	//  back with your WordPress websites.
	function ziggeoPUIFeedbackRemoval() {
		var feedback = document.getElementById('ziggeo_feedback');

		feedback.checked = true;

		//submit the form
		if(typeof document.forms[0].submit === 'function') {
			document.forms[0].submit();
		}
		else {
			//For the button itself
			document.forms[0].submit.click();
		}
		return true;
		//when reloaded, we will use the add_settings_error to show a nice thank you for that with some restyling done by CSS for that specific thank you.
	}

	//The function to show a message in the admin when something happens over AJAX
	function ziggeoPUIMessenger() {

		//possible types = 'notification' (default), 'error', 'important',
		// DEVS: any custom type is accepted as long as you add CSS for it

		function push(sms, type) {

			var _message = document.getElementById('ziggeo_message');
			_message.innerText = sms;
			_message.parentElement.className = 'ziggeo_' + type;

			var _length = 4000;

			if(type === 'error') {
				_length = 8000;
			}

			var i = 0;

			var _int = setInterval(function() {
				i += 0.20; //has to be this way

				_message.parentElement.style.opacity = i;

				if(_message.parentElement.style.opacity >= 1) {
					setTimeout(function(){
						ziggeoPUIMessenger().hide();
					}, _length);

					clearInterval(_int);
				}
			}, 40);

			return true;
		}

		function hide(method) {
			var _message = document.getElementById('ziggeo_message');

			var i = 1;

			var _inth = setInterval(function() {
				i -= 0.20;

				_message.parentElement.style.opacity = i;

				if(_message.parentElement.style.opacity < 0.01) {
					_message.innerHTML = '';
					_message.parentElement.className = '';
					clearInterval(_inth);
				}
			}, 40);
		}

		return {
			push: push,
			hide: hide
		};
	}

	// Function that helps us to hide the comment related options if the entire comments section has been disabled.
	function ziggeoCommentsToggle(e) {

		if(e.target) {
			var current = e.target;
		}
		else {
			var current = e;
		}

		var the_next = current.parentElement.parentElement.nextElementSibling;
		var to_enable = current.checked;

		while (the_next) {

			// Check if we should stop
			if(the_next.children[0].innerText === '') {
				// We should stop
				the_next = null;
			}
			else {
				// Enable fields
				if(to_enable === true) {
					the_next.className = the_next.className.replace('disabled_option', '');
					the_next = the_next.nextElementSibling;
				}
				else {
					// Disable Fields
					the_next.className += ' disabled_option';
					the_next = the_next.nextElementSibling;
				}
			}
		}
	}

	// Function to help us know if the given value is empty or not
	// used by SDK mostly
	function ziggeoPValidateEmpty(value) {
		if(value === '') {
			return true;
		}

		return false;
	}

	// Function to help us scroll the element of interest into view
	function ziggeoScrollTo(id) {
		try {

			var elm = document.getElementById(id);

			elm.scrollIntoView({ 
				behavior: 'smooth' 
			});
			return true;
		}
		catch(e) {}

		return false;
	}



/////////////////////////////////////////////////
// 2. ONLOAD                                   //
/////////////////////////////////////////////////

	//Registering onload needed to have everything run smoothly.. :)
	jQuery(document).ready( function() {

		//Are we within the post editor
		if(document.querySelector('.block-editor') !== null) {
			//The new builder uses JS to build stuff, so not much is ready on load...
			setTimeout(function() {
				if(typeof ziggeoSetupNewWPToolbar === 'function') {
					ziggeoSetupNewWPToolbar();
				}
			}, 4000); //Why 4 seconds? No reason, just seemed as enough of time for things to load up :)
		}

		// Initialize the hooks
		ziggeoPUIHooksInit();

		// Initialize support for various fields we have on different screens
		ziggeoDynamicFieldsSupportInit();

		//Lets do this only if we are in the admin panel of our plugin
		if(document.getElementById('ziggeo-tab_id_general')) {

			//Lets check if we have any integrations and show message if not:
			if(document.getElementsByClassName('ziggeo_integrations_list')[0].children.length == 0) {
				var _li = document.createElement('li');
				_li.innerText = 'Search for "Ziggeo" in Wordpress plugins repository to find other plugins that provide you integrations (bridges) between Ziggeo and other plugins.';
				document.getElementsByClassName('ziggeo_integrations_list')[0].appendChild(_li);
			}

			// Comments area on/off on page load
			var _comments_area = document.getElementById('ziggeo_modify_comments');
			_comments_area.addEventListener('change', function(e) {
				ziggeoCommentsToggle(e);
			});
			ziggeoCommentsToggle(_comments_area);
		}
		//Happens only if we are on notifications pages
		else if(document.getElementById('ziggeo-notifications')) {
			ziggeoPUINotificationsInit();
		}
		//Happens only if on videos page
		else if(document.getElementById('ziggeo-videos-filter')) {
			ziggeoPUIVideosInit();
		}
		else if(document.getElementById('ziggeo-addons-nav')) {
			ziggeoPUIAddonsInit();
		}
		else if(document.getElementById('ziggeo-sdk-pages')) {
			ziggeoPUISDKInit();
		}
		else if(document.getElementById('ziggeo-ee-event-id')) {
			ziggeoPUIEEInit();
		}
		else if(document.getElementById('ziggeo-tab_templates')) {
			ziggeoTemplatesManageInit();
		}

	});




/////////////////////////////////////////////////
// 3. HOOKS INIT                               //
/////////////////////////////////////////////////

	// Helper for setup hooks we want to listen to
	function ziggeoPUIHooksInit() {
		// A hook that helps to react to the change of the parameter name, so that we can set the type and possibly more
		ZiggeoWP.hooks.set('template_editor_autocomplete_param_name',   // hook name
		                   'template_editor_autocomplete_param_name',   // function key (needed for anonimous functions)
		                   function(obj_info) {                         // function
		                   		var type = document.getElementById('parameter-type');

		                   		type.value = obj_info.data.type;
		                   },
		                   10);                                         // priority

		// a hook that helps us detect the change of the template base
		// Then we set the editor information to the new base template (and clear parameters) + we set Template object
		ZiggeoWP.hooks.set('template_editor_template_base_change',      // hook name
		                   'template_editor_template_base_change',      // function key (needed for anonimous functions)
		                   function(obj_info) {                         // function
		                   		ziggeoTemplatesEditorSetCode(obj_info.template);

		                   		// Clear out the template object
		                   		ziggeoTemplatesTemplateObjectSet({base: obj_info.template, params:{}});
		                   },
		                   10);                                         // priority

		// showing different parameters based on template base
		// We use this one to hide and show the parameters that are usable for selected template
		ZiggeoWP.hooks.set('template_editor_template_base_change',      // hook name
		                   'template_editor_template_base_change',      // function key (needed for anonimous functions)
		                   function(obj_info) {                         // function
		                   		var base = obj_info.template.replace('[', 'for_');
		                   		var params = document.querySelectorAll('#ziggeo-embedding-parameters-list div.param');

		                   		var i, c;
		                   		for(i = 0, c = params.length; i < c; i++) {
		                   			if(params[i].className.indexOf(base) === -1) {
		                   				params[i].style.display = 'none';
		                   			}
		                   			else {
		                   				params[i].style.display = 'block';
		                   			}
		                   		}
		                   },
		                   12);                                         // priority
	}




/////////////////////////////////////////////////
// 4. FIELDS SUPPORT                           //
/////////////////////////////////////////////////

	function ziggeoDynamicFieldsSupportInit() {

		// Simple form buttons support
		////////////////////////////////

		var simple_form_btns = document.querySelectorAll('.ziggeo-ctrl-btn.ziggeo-of-simple-form');

		if(simple_form_btns.length > 0) {
			var i, c;
			for(i = 0, c = simple_form_btns.length; i < c; i++) {
				if(simple_form_btns[i].getAttribute('data-function')) {
					simple_form_btns[i].addEventListener('click', function(e) {
						var current = e.target;
						var fun = current.getAttribute('data-function');

						if(typeof window[fun] == 'function') {

							// get fields
							//data-fields="name:parameter-name,value:parameter-value"
							var t_fields = current.getAttribute('data-fields').split(',');
							var t_fields_array = [];

							var j,k;
							var obj_values = {};

							for(j = 0, k = t_fields.length; j < k; j++) {
								t_field = t_fields[j].split(':');

								t_fields_array.push(t_field[1]);

								obj_values[t_field[0]] = document.getElementById(t_field[1]).value;
							}

							var res = window[fun](obj_values);

							// We might do some additional stuff however only if it sends back true as response
							if(res) {
								var post_action = current.getAttribute('data-post-action');

								if(post_action && post_action === 'clear') {
									// We should go through all fields and clear their values
									for(j = 0, k = t_fields_array.length; j < k; j++) {
										t_field = t_fields_array[j];

										document.getElementById(t_field).value = '';
									}
								}
							}
						}
					});
				}
			}
		}

		// Autocomplete fields
		////////////////////////

		ziggeoPUICAutoCompleteInit();
	}




/////////////////////////////////////////////////
// 5. TEMPLATES EDITOR FUNCTIONS               //
/////////////////////////////////////////////////

	//Returns the reference to the templates editor
	function ziggeoTemplatesEditorGet() {
		//Using this so that we do not need to remember the ID and can change it in one place
		return document.getElementById('ziggeo_templates_editor');
	}

	// Helps us set the code that we want to have present within the template editor
	// This will replace entrie template code with the sent one
	function ziggeoTemplatesEditorSetCode(code) {
		var editor = ziggeoTemplatesEditorGet();

		if(code === null || typeof code === 'undefined') {
			code = '';
		}

		if(editor) {
			editor.value = code;
		}
	}

	// uses the object info we have about the template to recreate the code that should be shown in
	// the template code editor
	function ziggeoTemplatesEditorRePopulate() {
		var obj_template = ziggeoTemplatesTemplateObjectGet();

		var code = obj_template.base;

		for(_param in obj_template.params) {
			if(!obj_template.params.hasOwnProperty(_param)) {
				continue;
			}

			code += ' ' + _param + '=\'' + obj_template.params[_param] + '\'';
		}

		code += ']';

		ziggeoTemplatesEditorSetCode( code );
	}


	//returns Ziggeo template ID that was set or empty string if it was not set at all
	function ziggeoTemplatesGetID() {
		var _t = document.getElementById('ziggeo_templates_id');

		if(_t) {
			return _t.value.replace(/\ /g, '_');
		}

		return '';
	}

	//Helps us set the template ID properly
	function ziggeoTemplatesSetID(new_id) {
		var _t = document.getElementById('ziggeo_templates_id');

		if(_t) {
			_t.value = new_id.replace(/\ /g, '_');
			return true;
		}

		return false;
	}


	// Returns the Object with the information about template ID, base and parameters
	function ziggeoTemplatesTemplateObjectGet() {
		if(!ZiggeoWP.template_object) {
			ZiggeoWP.template_object = {
				base: document.getElementById('ziggeo_shorttags_list').value,
				params: {}
			};
		}

		return ZiggeoWP.template_object;
	}

	// Creates the object from the template code that we pass to it
	// pass false to reset the object
	function ziggeoTemplatesTemplateObjectSet(obj) {

		if(!ZiggeoWP.template_object || obj === false) {
			// We do this just so the default values are set on the object
			ziggeoTemplatesTemplateObjectGet();
		}

		if(typeof obj === 'undefined' || obj === null || obj === false) {
			return false;
		}

		//save the object
		ZiggeoWP.template_object = {base: obj.base, params: obj.params};
	}

	// Helper to add another parameter to the template object
	function ziggeoTemplatesTemplateObjectAdd(parameter, value) {

		if(parameter === '' || typeof parameter === 'undefined' || parameter === null) {
			return false;
		}

		if(!ZiggeoWP.template_object) {
			ZiggeoWP.template_object = {
				base: document.getElementById('ziggeo_shorttags_list').value,
				params: {}
			};
		}

		// To allow us to not break the code through escaping of apostrophe
		value = value.replaceAll("'", "\'");

		ZiggeoWP.template_object.params[parameter] = value;

		return true;
	}


	// Used to add parameter to the current template. Can be used from code as well
	// devs: please use this to add your own parameters through custom actions or buttons
	// expects: `name`, `type` and `value` keys with the values
	function ziggeoTemplatesParametersAdd(obj_info) {
		// Add to object
		// "draw" in the template editor

		// Note:
		// For arrays, strings floats and integers we leave them as is
		// For JSON we validate that it is actually OK JSON string
		// for bool it has to be true or false, will be false unless it is true

		obj_info.value = obj_info.value.replaceAll("'", "\'");

		if(obj_info.type === 'bool') {
			if(obj_info.value.indexOf('true') > -1) {
				obj_info.value = "true";
			}
			else {
				obj_info.value = "false";
			}
		}
		else if(obj_info.type === 'json') {
			try {
				JSON.parse(obj_info.value);
				// All is good as is
			}
			catch(e) {
				var msg = 'Given value is not proper JSON. JSON has to start with "{" and end with "}" have key and value separated by ":" and both within double quotes' + "\n" +
					'Example: {"key":"value"}' + "\n" +
					'Additional Error info:' + JSON.stringify(e);
				ziggeoPUIMessenger().push(msg, 'error');
				return false;
			}
		}

		// Save into object
		ziggeoTemplatesTemplateObjectAdd(obj_info.name, obj_info.value);

		// save into editor
		ziggeoTemplatesEditorRePopulate();

		return true;
	}

	// We use this to remove the parameter name and value from the Object and the editor preview
	// If the parameter was not already set, nothing would happen
	function ziggeoTemplatesParametersRemove(parameter_name) {

		// We do this to make sure we have the default fields if they do not exist yet
		ziggeoTemplatesTemplateObjectGet();

		// For cases when we call this function with an object that has the name and other details
		if(typeof parameter_name === 'object' && typeof parameter_name.name !== 'undefined') {
			parameter_name = parameter_name.name;
		}

		if(typeof ZiggeoWP.template_object.params[parameter_name] !== 'undefined') {

			delete ZiggeoWP.template_object.params[parameter_name];

			// save into editor
			ziggeoTemplatesEditorRePopulate();
			ziggeoScrollTo('ziggeo_templates_editor');

			return true;
		}

		return false;
	}


	// 5.1 Templates
	/////////////////

	function ziggeoTemplatesManageInit() {
		// Allow removal of items in editor
		document.getElementById('ziggeo_templates_editor').parentElement.addEventListener('click', function(e) {
			if(e.target.tagName === 'TEXTAREA') {
				var start = e.target.selectionStart;
				var end = e.target.selectionEnd;

				if(start === end) {
					// single click
					// Likely to be somewhere on textarea, not really on a parameter itself
					if(start === e.target.value.length) {
						return false;
					}
					else {
						var t_start = e.target.value.lastIndexOf(' ', start) + 1;

						if(t_start === 0) {
							// Someone is clicking on the template base, we can ignore it
							return false;
						}

						// We use t_start here instead of end, since parameter can have a value so by end we could find
						// equal sign of a different parameter instead
						var t_end = e.target.value.indexOf('=', t_start);

						if(t_end === -1) {
							t_end = e.target.value.length - 2; // -2 == ']
						}

						// Detect if something is off (like it can be with space accepting parameter values)
						// For cases when you click on the 2nd + word in a space separated value
						if(e.target.value.indexOf(' ', end) === -1 && e.target.value.indexOf('=', end) === -1) {
							t_end = e.target.value.lastIndexOf('=', end);
							t_start = e.target.value.lastIndexOf(' ', t_end) + 1;
						}
						// In other cases
						else {
							if((e.target.value.indexOf(' ', end) > -1 &&
								e.target.value.indexOf(' ', end) < e.target.value.indexOf('=', end)) ||
								e.target.value.indexOf('=', end) === -1) {
								// If we are here, the t_start and t_end need to be changed.
								t_start = e.target.value.lastIndexOf(' ', e.target.value.lastIndexOf('=', start)) + 1;
								t_end = e.target.value.indexOf('=', t_start);
							}
						}

						// Select the parameter in the textarea for visual assistance
						e.target.selectionStart = t_start;
						e.target.selectionEnd = t_end;

						// Add the parameter into the editing field
						var t_param_name = e.target.value.substring(t_start, t_end);

						var obj = ziggeoTemplatesTemplateObjectGet();
						var t_value = obj.params[t_param_name];

						// As is the type is something we can not know with 100% certainty.
						// There are ways of knowing, however in most cases we can detect functioning types
						var t_type = 'string';

						if(t_value === 'false' || t_value === 'true') {
							t_type = 'bool';
						}
						else if(!isNaN(t_value)) {
							t_type = 'integer';
						}

						document.getElementById('parameter-name').value = t_param_name;
						document.getElementById('parameter-value').value = t_value;
						document.getElementById('parameter-type').value = t_type;

						ziggeoScrollTo('ziggeo_shorttags_list');
					}
				}
				else if(start > 0 && end < e.target.value.length) {
					// double click
					//console.log(start);
					//console.log(end);
				}
			}
		} ); 

		var i,c;

		var use = document.querySelectorAll('.ziggeo_templates .use');

		for(i = 0, c = use.length; i < c; i++) {
			use[i].addEventListener('click', function(e) {
				ziggeoTemplatesShortcodeGet(e.target);
			});
		}

		var edits = document.querySelectorAll('.ziggeo_templates .edit');

		for(i = 0, c = edits.length; i < c; i++) {
			edits[i].addEventListener('click', function(e) {
				ziggeoTemplatesEdit(e.target);
			});
		}

		var removals = document.querySelectorAll('.ziggeo_templates .delete');

		for(i = 0, c = removals.length; i < c; i++) {
			removals[i].addEventListener('click', function(e) {
				ziggeoTemplatesRemove(e.target);
			});
		}

		// Support for click to add parameters from the visual list
		var param_list = document.getElementById('ziggeo-embedding-parameters-list');

		param_list.addEventListener('click', function(e) {
			var current = e.target;
			if(current.className.indexOf('param') > -1) {
				document.getElementById('parameter-name').value = current.innerText;
				document.getElementById('parameter-type').value = current.getAttribute('data-type');
				document.getElementById('parameter-value').focus();
			}
		});

		document.getElementById('ziggeo_templates_update').addEventListener('click', ziggeoTemplatesUpdate, false );
		document.getElementById('ziggeo_templates_save').addEventListener('click', ziggeoTemplatesSave, false );

		// Run the hook for the template base to init various states on page
		ziggeoPUITemplatesChange(document.getElementById('ziggeo_shorttags_list'));
	}

	// Helper to help us start editing the template
	function ziggeoTemplatesEdit(field) {
		var root = field.parentElement.parentElement;

		var id = root.getElementsByClassName('template_id')[0].innerText;
		var codes = root.getElementsByClassName('template_code')[0];

		ziggeoTemplatesSetID(id);
		document.getElementById('ziggeo_templates_manager').value = id;

		// We need to unescape the code for editing
		var json_code = codes.getAttribute('template-json');
		json_code = json_code.replace(/(\\(?:'))/g, '&apos;');
		json_code = json_code.replace(/\'/g, '"');
		json_code = json_code.replaceAll('&apos;', '\'');
		json_code = JSON.parse(json_code);

		ziggeoTemplatesTemplateObjectSet(json_code);
		ziggeoTemplatesBaseSet(json_code.base);

		ziggeoTemplatesEditorRePopulate();

		ziggeoScrollTo('ziggeo_templates_id');

		document.getElementById('ziggeo_templates_update').style.display = 'block';
		document.getElementById('ziggeo_templates_save').style.display = 'none';

		document.getElementById('ziggeo_templates_turn_to_new').style.display = 'inline-block';

		var hook_values = {
			template: document.getElementById('ziggeo_shorttags_list').value,
		};

		ZiggeoWP.hooks.fire('template_editor_template_edit', hook_values);
	}

	// Helps us to remove the template
	function ziggeoTemplatesRemove(field) {
		var root = field.parentElement.parentElement;
		var id = root.getElementsByClassName('template_id')[0].innerText;

		ZiggeoWP.hooks.fire('dashboard_templates_pre_removal', {id: id});

		if(confirm('Are you sure that you want to remove template? It is not possible to undo the same action!')) {
			document.getElementById('ziggeo_templates_manager').value = id;

			//Just about to remove the template
			ZiggeoWP.hooks.fire('dashboard_templates_post_removal', {id: id});

			//submit the form
			ziggeoPUIManageTemplate('remove', { id:id }, root);
		}

		return false;
	}

	// Functionality to save the template
	function ziggeoTemplatesSave() {

		var id = document.getElementById('ziggeo_templates_id').value;
		var code_shortcode = document.getElementById('ziggeo_templates_editor').value;
		var code_json = ziggeoTemplatesTemplateObjectGet();

		var data = {
			id: id,
			code: {
				json: code_json,
				shortcode: code_shortcode
			}
		};

		ZiggeoWP.hooks.fire('dashboard_templates_pre_save', data);
		ziggeoPUIManageTemplate('save', data);
	}

	// Functionality to save the template
	function ziggeoTemplatesUpdate() {

		var id_new = document.getElementById('ziggeo_templates_id').value;
		var id_old = document.getElementById('ziggeo_templates_manager').value;
		var code_shortcode = document.getElementById('ziggeo_templates_editor').value;
		var code_json = ziggeoTemplatesTemplateObjectGet();

		var data = {
			id: id_new,
			id_old: id_old,
			code: {
				json: code_json,
				shortcode: code_shortcode
			}
		};

		ZiggeoWP.hooks.fire('dashboard_templates_pre_update', data);
		ziggeoPUIManageTemplate('update', data);
	}

	function ziggeoTemplatesShortcodeGet(field) {
		var root = field.parentElement.parentElement;

		var id = root.getElementsByClassName('template_id')[0].innerText;

		alert('Please use:' + "\n" + '[ziggeotemplate ' + id + ']');
	}

	//function to report over AJAX on what we should do to/with templates
	function ziggeoPUIManageTemplate(action, data, ref) {
		var obj = {};

		// Create a request to remove the template
		if(action === 'remove') {
			obj.operation = 'settings_manage_template';                 // needed for routing AJAX requests
			obj.activity = encodeURI(action);                           // needed for knowing what we want to do
			obj.template_id = encodeURI(data.id);                       // We need ID to remove by
		}
		else if(action === 'save') {
			obj.operation = 'settings_manage_template';                 // needed for routing AJAX requests
			obj.activity = encodeURI(action);                           // needed for knowing what we want to do
			obj.template_id = encodeURI(data.id);                       // We need ID to save as
			obj.code_shortcode = encodeURI(data.code.shortcode);        // shortcode code
			obj.code_json = encodeURI(JSON.stringify(data.code.json));  // JSON object to save
		}
		else if(action === 'update') {
			obj.operation = 'settings_manage_template';                 // needed for routing AJAX requests
			obj.activity = encodeURI(action);                           // needed for knowing what we want to do
			obj.template_id = encodeURI(data.id);                       // We need ID to save as
			obj.template_id_old = encodeURI(data.id_old);               // We need ID to save as
			obj.code_shortcode = encodeURI(data.code.shortcode);        // shortcode code
			obj.code_json = encodeURI(JSON.stringify(data.code.json));  // JSON object to save
		}
		else {
			return false;
		}

		ziggeoAjax(obj, function(e) {

			//We should get back an object
			e = JSON.parse(e);

			//It was sending back the quoted string so it was not 'added', rather '"added"'
			//e = e.replace(/(^\"+|\"+$)/mg, '');
			//if all is OK e ==v
			//('added', 'unchanged', 'updated', removed', false)
			//if not, it can be anything, good response is "Not Allowed" in such case
			if(e.status && e.status === 'success') {
				ziggeoPUIMessenger().push('Success', 'notification');

				if(e.message === 'added') {
					//Lets also add it into the templates list above
					var list = document.getElementsByClassName('ziggeo_templates')[0];
					var item = document.createElement('div');
					item.className = 'template';

					var item_id = document.createElement('div');
					item_id.className = 'template_id';
					item_id.innerText = e.template_id;
					item.appendChild(item_id);

					var item_code = document.createElement('div');
					item_code.className = 'template_code';
					item_code.setAttribute('template-json', JSON.stringify(ziggeoTemplatesTemplateObjectGet()));
					item_code.innerText = ziggeoTemplatesEditorGet().value;
					item.appendChild(item_code);

					var item_actions = document.createElement('div');
					item_actions.className = 'actions';
					item.appendChild(item_actions);

					var item_actions_use = document.createElement('div');
					item_actions_use.className = 'use';
					item_actions_use.innerText = 'Use';
					item_actions.appendChild(item_actions_use);

					var item_actions_edit = document.createElement('div');
					item_actions_edit.className = 'edit';
					item_actions_edit.innerText = 'Edit';
					//item_actions_edit.setAttribute('data-template', ziggeoTemplatesEditorGet().value);
					item_actions.appendChild(item_actions_edit);

					var item_actions_remove = document.createElement('div');
					item_actions_remove.className = 'delete';
					item_actions_remove.innerText = 'Remove';
					item_actions.appendChild(item_actions_remove);

					list.appendChild(item);
				}
				else if(e.message === 'updated') {
					var i, c;
					var templates = document.getElementsByClassName('template_id');

					for(i = 0, c = templates.length; i < c; i++) {
						if(templates[i].innerText === data.id_old) {
							// We found the template
							templates[i].innerText = data.id;

							var _code_elem = templates[i].parentElement.getElementsByClassName('template_code')[0];

							_code_elem.setAttribute('template-json', JSON.stringify(data.code.json));
							_code_elem.innerText = data.code.shortcode;

							i = c;
						}
					}
				}
				else if(e.message === 'removed') {
					ref.parentElement.removeChild(ref);
				}

				//Reset the screen
				document.getElementById('ziggeo_templates_manager').value = '';
				document.getElementById('ziggeo_templates_turn_to_new').style.display = 'none';
				document.getElementById('ziggeo_templates_update').style.display = 'none';
				document.getElementById('ziggeo_templates_save').style.display = 'block';
				ziggeoTemplatesSetID('');
				ziggeoTemplatesTemplateObjectSet(false);
				ziggeoTemplatesEditorSetCode( ziggeoTemplatesBaseGet() );

			}
			else {
				ziggeoDevReport('Managing templates: ' + e, 'error');
				ziggeoPUIMessenger().push('Something unexpected happened', 'error');
			}
		});
	}

	//Function to change the shortcode in the templates editor to the selected one and to show the parameters that can be applied to each
	// > sel param accepts the reference to <select> input
	function ziggeoPUITemplatesChange(sel) {
		//lets get the selected value
		var selected = sel.options[sel.selectedIndex].value;

		//Lets grab the currently set value if any from the templates editor
		var editor = ziggeoTemplatesEditorGet();

		var hook_values = {
			template: selected, //will it be player ([ziggeoplayer), recorder..
			editor: editor
		};

		ZiggeoWP.hooks.fire('template_editor_template_base_change', hook_values);
	}

	//We set the template as a new template, instead of it being edited - allowing people to click on edit to create a new template based on the old one.. ;)
	function ziggeoPUITemplatesTurnIntoNew() {
		//Clear the value indicating what was changed
		document.getElementById('ziggeo_templates_manager').value = '';

		// Change the name of the template as well, so we do not end up adding over
		var t_date = new Date();
		var template_id = document.getElementById('ziggeo_templates_id');
		template_id.value = template_id.value +'_' + t_date.getYear()+''+(t_date.getMonth()+1)+''+t_date.getDate()+''+t_date.getMilliseconds();

		//hide the button to turn it into a new template..
		document.getElementById('ziggeo_templates_turn_to_new').style.display = 'none';
		document.getElementById('ziggeo_templates_update').style.display = 'none';
		document.getElementById('ziggeo_templates_save').style.display = 'block';
	}

	//Gets the parameter base that we should use in editor, or returns the one to use
	//>> specific can be 'player' or 'recorder' which then passes back the template base
	// that you should use to start your template with
	// When no paramateter is passed it will retrieve the editor template and return that
	function ziggeoTemplatesBaseGet(specific) {
		if(specific) {
			//@here, would like to do this as hooks so you can add your own
			// until specifically asked for will leave as is
		}
		else {
			var editor_template = document.getElementById('ziggeo_shorttags_list');
			return editor_template.options[editor_template.selectedIndex].value;
		}
	}

	function ziggeoTemplatesBaseSet(value) {
		document.getElementById('ziggeo_shorttags_list').value = value;
	}




/////////////////////////////////////////////////
// 6. INTEGRATIONS                             //
/////////////////////////////////////////////////


	// 6.1 Integrations tab
	///////////////////////

	//set the integration to disable
	function ziggeoPUIIntegrationStatus(strPlugin, strStatus) {
		var toChange = document.getElementById('ziggeo_integration_change');

		if(toChange) {
			toChange.value = strPlugin+'='+strStatus;
		}
	}




/////////////////////////////////////////////////
// 7. WP EDITOR                                //
/////////////////////////////////////////////////

	function ziggeoSetupNewWPToolbar() {

		var _t = document.getElementsByClassName('edit-post-header-toolbar');

		if(_t && _t.length > 0) {
			//Gutenberg is here and available
			_t = _t[0];
		}
		else {
			//The old editor..
			return false;
		}

		var toolbar_placeholder = document.createElement('div');
		toolbar_placeholder.id = 'ziggeo_toolbar_holder';
		toolbar_placeholder.style.display = 'none';
		toolbar_placeholder.setAttribute('data-toolbar-item', true);
		toolbar_placeholder.setAttribute('dataset', {toolbarItem: false});

		_t.appendChild(toolbar_placeholder);

		ziggeoAjax({'operation':'admin_post_toolbar'}, function(result) {
			toolbar_placeholder.innerHTML = result;
			toolbar_placeholder.style.display = 'block';

			ziggeoSetupOverlayRecorder();
			ziggeoSetupOverlayTemplates();
		});
	}

	function ziggeoSetupOverlayRecorder() {

		//Handle the editor recorder
		jQuery("#insert-ziggeo-button").on("click", function (event) {
			event.preventDefault();
			if (document.getElementById('ziggeo-overlay-screen')) {
				return;
			}

			//show overlay recorder
			ziggeoShowOverlayWithRecorder();

			ZiggeoWP.hooks.set('ziggeo_overlay_popup_on_verify', 'ziggeo_post_recorder', function(recorder) {
				ziggeoInsertTextToPostEditor('[ziggeoplayer]' + recorder.get('video') + '[/ziggeoplayer]')

				//Since video is submitted, lets make sure that this is shown a bit differently - pointed out more..
				document.getElementById('ziggeo-overlay-close').style['background-color'] = "orangeRed";
			});
		});
	}

	function ziggeoSetupOverlayTemplates() {
		//Handle the insert template button click
		jQuery('#insert-ziggeo-template').on('click', function(event) {
			event.preventDefault();
			if (document.getElementById('ziggeo-overlay-screen')) {
				return;
			}

			//open a modal window showing a list of all templates
			ziggeoShowOverlayWithTemplatesList(null, true);
			// the templates should be retrieved over AJAX so we can grab the latest ones even if new are added while working on the post
		});
	}




/////////////////////////////////////////////////
// 8. NOTIFICATIONS                            //
/////////////////////////////////////////////////

	function ziggeoPUINotificationsInit() {
		var list = document.getElementById('ziggeo_notifications_list');

		var okeys = list.querySelectorAll('.ok');

		for(i = 0, c = okeys.length; i < c; i++) {
			okeys[i].addEventListener('click', function(e) {
				ziggeoNotificationManage(e.currentTarget.parentElement.getAttribute('data-id'), 'OK');
			});
		}

		var hides = list.querySelectorAll('.hide');

		for(i = 0, c = hides.length; i < c; i++) {
			hides[i].addEventListener('click', function(e) {
				ziggeoNotificationManage(e.currentTarget.parentElement.getAttribute('data-id'), 'HIDE');
			});
		}

		//Admin tools if shown
		var _prune = document.getElementById('ziggeo_notifications_prune');

		if(_prune) {
			var _clear = document.getElementById('ziggeo_notifications_clear');

			_prune.addEventListener('click', function(e) {
				_prune.className += ' disabled';
				_clear.className += ' disabled';
				ziggeoNotificationManage('all', 'PRUNE');
			});

			_clear.addEventListener('click', function(e) {
				_prune.className += ' disabled';
				_clear.className += ' disabled';
				ziggeoNotificationManage('all', 'CLEAR');
			});
		}
	}

	function ziggeoNotificationManage(id, status) {

		var obj = {
			'operation':'notification_handler',
			'id': id,
			'status': status
		};

		ziggeoAjax(obj, function(result) {
			if(result === 'true') {

				if(status === 'HIDE' || status === 'OK') {
					var _element = document.querySelector('#ziggeo_notifications_list li[data-id="' + id + '"]');

					if(status === 'OK') {
						_element.className = 'message ok';
						_element.getElementsByClassName('ok')[0].style.display = 'none';
					}
					else if(status === 'HIDE') {
						_element.className += ' hidden';
						_element.innerHTML = '';
					}
				}
				else if(status === 'PRUNE' || status === 'CLEAR') {
					location.reload();
				}
			}
			else {
				ziggeoDevReport('Unable to handle the notifications request');
			}
		});
	}




/////////////////////////////////////////////////
// 9. VIDEOS PAGE                              //
/////////////////////////////////////////////////

	function ziggeoPUIVideosInit() {

		// Lazyload support
		if(typeof ziggeoAPIGetVideo === 'undefined') {
			setTimeout(function() {
				return ziggeoPUIVideosInit();
			}, 2000);

			return null;
		}

		var _placeholder = document.getElementById('ziggeo-videos-filter');
		var _moderation_filter = _placeholder.querySelector('.moderation');
		var _token_filter = _placeholder.querySelector('.token');
		var _tags_filter = _placeholder.querySelector('.tags');
		var _sort_filter = _placeholder.querySelector('.sort');
		var _apply = _placeholder.querySelector('.ziggeo-ctrl-btn');

		//Disable on default
		_apply.className += ' disabled';
		ZiggeoWP.video_list_current = 1;

		_token_filter.addEventListener('change', function() {
			//enable
			_apply.className = _apply.className.replace('disabled', '');
		});

		_moderation_filter.addEventListener('change', function() {
			//enable
			_apply.className = _apply.className.replace('disabled', '');
		});

		_sort_filter.addEventListener('change', function() {
			//enable
			_apply.className = _apply.className.replace('disabled', '');
		});

		_tags_filter.addEventListener('change', function() {
			//enable
			_apply.className = _apply.className.replace('disabled', '');
		});

		_apply.addEventListener('click', function (event){
			_apply.className += ' disabled';
			ziggeoPUIVideosFilter(true);
		});

		ziggeoPUIVideosFilter(true);

		//Clear videos counter at this time
		ziggeoAjax({
			operation: 'video_verified_seen'
		});
	}

	//Handles the "Searching..." screen or "Not Found", etc.
	function ziggeoPUIVideosMessage(text, type, action) {

		if(action === 'hide') {
			var _screen = document.getElementById('ziggeo-videos-screen');
			_screen.parentElement.removeChild(_screen);
			return true;
		}

		var _placeholder = document.getElementById('ziggeo-videos');

		_placeholder.innerHTML = '';

		var _screen = document.createElement('div');
		_screen.id = 'ziggeo-videos-screen';

		_msg = document.createElement('div');
		_msg.innerHTML = text;

		if(type === 'error') {
			_screen.className = 'error';
		}
		else { //type === 'info'
		}

		_screen.appendChild(_msg);
		_placeholder.appendChild(_screen);
	}

	function ziggeoPUIVideosFilter(clear, skip) {
		var _filter_placeholder = document.getElementById('ziggeo-videos-filter');
		var _videos_placeholder = document.getElementById('ziggeo-videos');
		var _token_filter = _filter_placeholder.querySelector('.token');

		if(clear === true) {
			ZiggeoWP.video_list = [];
			ziggeoPUIVideosMessage('Searching...');
		}

		//If there is some token in there, let's just search for one video and ignore other fields
		if(_token_filter.value !== '') {
			_videos_placeholder.setAttribute('_clear', true);

			ziggeoAPIGetVideo(_token_filter.value,
								function(video) {
									if(video) {
										ziggeoPUIVideosHasVideos([video]);
									}
									else {
										ziggeoPUIVideosNoVideos();
									}
								},
								function(err) {
									if(err.indexOf(404) > -1) {
										ziggeoPUIVideosNoVideos();
									}
									else if(err.indexOf(401) > -1 || err.indexOf(403) > -1) {
										//TODO
									}
									else {
										ziggeoDevReport(err, 'error');
										//Also add some error happened info
									}

								}, null);
			return true;
		}

		_videos_placeholder.setAttribute('_clear', clear);

		var _moderation_filter = _filter_placeholder.querySelector('.moderation');
		var _tags_filter = _filter_placeholder.querySelector('.tags');
		var _sort_filter = _filter_placeholder.querySelector('.sort');

		var query_obj = {};

		//Limit will be default of 50 however this can be changed up to 100
		query_obj.limit = 100; //10 per page, this tells us there is more.

		//Skip allows us to do pagination. It would work together with limit. For now this should be handled automatically and left at 0 here
		if(typeof skip !== 'undefined') {
			query_obj.skip = skip;
		}

		//If we want to get the oldest first, we would reverse the data, for now we want newest first
		if(_sort_filter.value === 'old') {
			query_obj.reverse = true;
		}

		//Allows getting ready or those in processing. We only really want to get playable ones
		query_obj.states = 'ready';

		if(_tags_filter.value !== '') {
			//It needs to be comma separated so we replace all spaces with comma
			query_obj.tags = _tags_filter.value.replace(/\ /g, ',');
		}

		//No action needed if all
		if(_moderation_filter.value == 'approved') {
			//Go through the list of videos and then list them if approved
			ziggeoAPIGetVideosData(query_obj,
									function(videos) {
										ziggeoPUIVideosHasVideosApproved(videos);
									},
									'ziggeoPUIVideosNoVideos',
									function(err) {
										if(!ziggeoPUIVideosIndexNotAllowed(err)) {
											ziggeoDevReport(err, 'error');
										}
									},
									null);
		}
		else if(_moderation_filter.value == 'pending') {
			//Go through the list of videos and then list them if not approved
			ziggeoAPIGetVideosData(query_obj,
									'ziggeoPUIVideosHasVideosPending',
									'ziggeoPUIVideosNoVideos',
									function(err) {
										if(!ziggeoPUIVideosIndexNotAllowed(err)) {
											ziggeoDevReport(err, 'error');
										}
									},
									null);
		}
		else if(_moderation_filter.value == 'rejected') {
			//Go through the list of videos and then list them if not approved
			ziggeoAPIGetVideosData(query_obj,
									'ziggeoPUIVideosHasVideosRejected',
									'ziggeoPUIVideosNoVideos',
									function(err) {
										if(!ziggeoPUIVideosIndexNotAllowed(err)) {
											ziggeoDevReport(err, 'error');
										}
									},
									null);
		}
		else {
			ziggeoAPIGetVideosData(query_obj,
									'ziggeoPUIVideosHasVideos',
									'ziggeoPUIVideosNoVideos',
									function(err) {
										if(!ziggeoPUIVideosIndexNotAllowed(err)) {
											ziggeoDevReport(err, 'error');
										}
									},
									null);
		}

		ziggeoPUIVideosPageCounter(query_obj);
	}

	function ziggeoVideosFindByTag(tag) {

		var _filter_placeholder = document.getElementById('ziggeo-videos-filter');

		ziggeoPUIVideosFilterReset();

		_filter_placeholder.querySelector('.tags').value = tag;

		ziggeoPUIVideosFilter(true);
	}

	//Used for cases where no videos are found on initial request
	function ziggeoPUIVideosNoVideos() {
		ziggeoPUIVideosMessage('We searched however nothing was found.');
		var _nav = document.getElementById('ziggeo-videos-nav');
		_nav.className = _nav.className.replace('disabled', '');
	}

	//Used for cases where videos have been found and we need to create the list
	function ziggeoPUIVideosHasVideos(videos, not_fresh) {
		var _placeholder = document.getElementById('ziggeo-videos');

		ziggeoPUIVideosMessage(null, null, 'hide');

		//We will not be making pages in the real sense, however will not need to use the API if we already have the "page" in the cache
		if(typeof ZiggeoWP.video_list === 'undefined' || _placeholder.getAttribute('_clear') === true) {
			ZiggeoWP.video_list = [];
		}

		if(not_fresh !== true) {
			//We now add the new videos to the existing videos if any
			ZiggeoWP.video_list = ZiggeoWP.video_list.concat(videos);
		}

		_placeholder.innerHTML = '';

		var _tools = ziggeoPUIVideosCreateTools();
		//var _v_info = ziggeoPUIVideosCreateInfoSection(); //TODO

		var current = 0;

		if(ZiggeoWP.video_list_current > 1) {
			current = (ZiggeoWP.video_list_current - 1) * 10;
		}

		for(i = 0, c = (videos.length > 10) ? 10 : videos.length; i < c; i++) {
			_item = document.createElement('div');
			_item.className = 'video_list_item';

			_item.setAttribute('video_ref', current);

			//_item.appendChild(_v_info.cloneNode(true));

			//Set the info
			//_v_info.querySelector('.video_title').value = videos[i].title;
			//_v_info.querySelector('.video_desc').value = videos[i].description;

			_p_player = document.createElement('div');

			if(videos[i].approved === true) {
				_p_player.className = 'player approved';
			}
			else if(videos[i].approved === false) {
				_p_player.className = 'player rejected';
			}
			else {
				_p_player.className = 'player pending';
			}

			var player = new ZiggeoApi.V2.Player({
				element: _p_player,
				attrs: {
					width: 320,
					height: 180,
					theme: "modern",
					themecolor: "blue",
					video: videos[i].token,
					'stretchheight': true
				}
			});

			_item.appendChild(_p_player);
			player.activate();

			var _length = document.createElement('div');
			_length.className = 'video_length';
			_length.innerText = videos[i].duration + 's';
			_p_player.appendChild(_length);

			_item.appendChild(_tools.cloneNode(true));

			//Add video info
			var _info = document.createElement('div');
			_info.className = 'info';
			_info.setAttribute('data-token', videos[i].token);

				var _token = document.createElement('div');
				_token.innerText = 'Token: ' + videos[i].token;
				_info.appendChild(_token);

				// add download option
				_info.appendChild(ziggeoShowDownloadVideo(videos[i].token, true));

				var _tags = document.createElement('div');
				_tags.className = 'tags-field';
				if(videos[i].tags) {
					for(_i = 0, _c = videos[i].tags.length; _i < _c; _i++) {
						var _tag = document.createElement('span');
						_tag.className = 'tag';
						_tag.innerText = videos[i].tags[_i];
						_tag.title = 'Search for videos with "' + videos[i].tags[_i] + '"';
						_tags.appendChild(_tag);
					}
				}

				var _edit = document.createElement('span');
				_edit.className = 'ziggeo-ctrl-btn-inline ziggeo-btn-edit';
				_edit.setAttribute('data-action', 'tags');
				_edit.setAttribute('data-format', 'csv');
				_edit.innerText = '{edit}';

				_tags.appendChild(_edit);

				_info.appendChild(_tags);

				var _title = document.createElement('div');
				if(videos[i].title !== null) {
					_title.innerText = 'Video Title: "' + videos[i].title + '"';
				}
				else {
					_title.innerText = 'Video Title was not set.';
				}

				var _edit = document.createElement('span');
				_edit.className = 'ziggeo-ctrl-btn-inline ziggeo-btn-edit';
				_edit.setAttribute('data-action', 'title');
				_edit.setAttribute('data-format', 'text');
				_edit.innerText = '{edit}';

				_title.appendChild(_edit);

				_info.appendChild(_title);

				var _description = document.createElement('div');
				if(videos[i].description !== null) {
					_description.innerText = 'Video Description: "' + videos[i].description + '"';
				}
				else {
					_description.innerText = 'Video Description was not set.';
				}

				var _edit = document.createElement('span');
				_edit.className = 'ziggeo-ctrl-btn-inline ziggeo-btn-edit';
				_edit.setAttribute('data-action', 'description');
				_edit.setAttribute('data-format', 'text');
				_edit.innerText = '{edit}';

				_description.appendChild(_edit);

				_info.appendChild(_description);

			_item.appendChild(_info);

			_placeholder.appendChild(_item);
			current++;
		}

		ziggeoPUIVideosPageCreateNavigation(ZiggeoWP.video_list_current);

		//Now attach the events on all tools - @TODO for pagination so only new items are added
		jQuery('#ziggeo-videos .ziggeo-btn-approve').on('click', function(event){
			ziggeoVideosApprove(event.currentTarget);
		});

		jQuery('#ziggeo-videos .ziggeo-btn-reject').on('click', function(event){
			ziggeoVideosReject(event.currentTarget);
		});

		jQuery('#ziggeo-videos .ziggeo-btn-link').on('click', function(event){
			ziggeoVideosGetURL(event.currentTarget);
		});

		jQuery('#ziggeo-videos .ziggeo-btn-delete').on('click', function(event){
			ziggeoVideosRemove(event.currentTarget);
		});

		jQuery('#ziggeo-videos .ziggeo-btn-custom-data').on('click', function(event){
			ziggeoVideosEditCustomData(event.currentTarget);
		});

		jQuery('#ziggeo-videos .ziggeo-btn-shortcodes').on('click', function(event){
			ziggeoVideosGrabShortcodes(event.currentTarget);
		});

		jQuery('#ziggeo-videos .info .tag').on('click', function(event){
			ziggeoVideosFindByTag(event.currentTarget.innerText);
		});

		jQuery('#ziggeo-videos .ziggeo-btn-edit').on('click', function(event){

			var element_ref = event.currentTarget;

			var media_token = element_ref.parentElement.parentElement.getAttribute('data-token');

			var request = ziggeo_app.videos.get( media_token );

			ZiggeoWP.hooks.set('ziggeo_videolist_info_edit', 'ziggeo_videolist_info_save_on_edit',
				function(data) {
					if(data.property === 'title' || data.property === 'description') {
						//editing textNode not element
						element_ref.previousSibling.textContent = 'Video ' + data.property + ': "' + data.saved + '"';
					}
					else if(data.property === 'tags') {
						var _tags = element_ref.parentElement;
						_tags.innerHTML = '';

						if(data.saved.trim() !== '') {
							var _to_save = data.saved.split(',');

							for(_i = 0, _c = _to_save.length; _i < _c; _i++) {
								var _tag = document.createElement('span');
								_tag.className = 'tag';
								_tag.innerText = _to_save[_i];
								_tag.title = 'Search for videos with "' + _to_save[_i] + '"';
								_tags.appendChild(_tag);
							}
						}
						else {
							_to_save = '';
						}
					}
				});

			request.success( function(video) {
				ziggeoPUIVideosPopupCreate({
					data_to_show:   video[element_ref.getAttribute('data-action')],
					data_saved:     element_ref.getAttribute('data-action'),
					media_token:    media_token,
					format:         element_ref.getAttribute('data-format'),
					allowed:        'edit'
				});
			});
		});

	}

	//This is a proxy for ziggeoPUIVideosHasVideos. It first checks the videos, then sends them there
	function ziggeoPUIVideosHasVideosApproved(videos) {
		var clean_videos = [];

		for(i = 0, c = videos.length; i < c; i++) {
			if(videos[i].approved === true) {
				clean_videos.push(videos[i]);
			}
		}

		if(clean_videos.length === 0) {
			//We should at this point either request new videos or we should show a message
		}

		//Show the videos
		ziggeoPUIVideosHasVideos(clean_videos);
	}

	//This is a proxy for ziggeoPUIVideosHasVideos. It first checks the videos, then sends them there
	function ziggeoPUIVideosHasVideosPending(videos) {
		var pending_videos = [];

		for(i = 0, c = videos.length; i < c; i++) {
			if(videos[i].approved === null || videos[i].approved === '') {
				pending_videos.push(videos[i]);
			}
		}

		if(pending_videos.length === 0) {
			//We should at this point either request new videos or we should show a message
		}

		//Show the videos
		ziggeoPUIVideosHasVideos(pending_videos);
	}

	//This is a proxy for ziggeoPUIVideosHasVideos. It first checks the videos, then sends them there
	function ziggeoPUIVideosHasVideosRejected(videos) {
		var moderated_videos = [];

		for(i = 0, c = videos.length; i < c; i++) {
			if(videos[i].approved === false) {
				moderated_videos.push(videos[i]);
			}
		}

		if(moderated_videos.length === 0) {
			//We should at this point either request new videos or we should show a message
		}

		//Show the videos
		ziggeoPUIVideosHasVideos(moderated_videos);
	}

	//Function to create the buttons that allow us to handle videos in video listing page
	function ziggeoPUIVideosCreateTools() {
		var _tools = document.createElement('div');
		_tools.className = 'toolsbar';

		var _approve = document.createElement('div');
		_approve.className = 'ziggeo-btn-approve dashicons-thumbs-up';
		_approve.title = 'Approve';
		_tools.appendChild(_approve);

		var _reject = document.createElement('div');
		_reject.className = 'ziggeo-btn-reject dashicons-thumbs-down';
		_reject.title = 'Reject';
		_tools.appendChild(_reject);

		var _link = document.createElement('div');
		_link.className = 'ziggeo-btn-link dashicons-admin-links';
		_link.title = 'Get link';
		_tools.appendChild(_link);

		var _delete = document.createElement('div');
		_delete.className = 'ziggeo-btn-delete dashicons-no';
		_delete.title = 'Remove';
		_tools.appendChild(_delete);

		var _custom_data = document.createElement('div');
		_custom_data.className = 'ziggeo-btn-custom-data dashicons-edit-page';
		_custom_data.title = 'See & Edit Custom Data';
		_tools.appendChild(_custom_data);

		var _sortcodes = document.createElement('div');
		_sortcodes.className = 'ziggeo-btn-shortcodes dashicons-shortcode';
		_sortcodes.title = 'Get Shortcodes';
		_tools.appendChild(_sortcodes);

		return _tools;
	}

	//Function to create the fields with video title and description
	function ziggeoPUIVideosCreateInfoSection() {
		var _v_info = document.createElement('div');
		_v_info.className = 'video_details';

		var _lbl_title = document.createElement('label');
		_lbl_title.innerText = 'Video Title: ';

		var _input_title = document.createElement('input');
		_input_title.className = 'video_title';
		_lbl_title.appendChild(_input_title);

		var _lbl_desc = document.createElement('label');
		_lbl_desc.innerText = 'Video Description: ';

		var _input_desc = document.createElement('textarea');
		_input_desc.className = 'video_desc';
		_lbl_desc.appendChild(_input_desc);

		_v_info.appendChild(_lbl_title);
		_v_info.appendChild(_lbl_desc);

		return _v_info;
	}

	//Function to approve the videos. This type of setup allows you to extend it! You will need to have your script loaded before ours.
	if(typeof ziggeoVideosApprove !== 'function') {
		function ziggeoVideosApprove(element_ref) {
			var _video_ref = element_ref.parentElement.parentElement.getAttribute('video_ref');

			ziggeo_app.videos.update(ZiggeoWP.video_list[_video_ref].token, { 'approved': true });

			element_ref.parentElement.parentElement.firstElementChild.className = 'player approved';
		}
	}

	//Function to reject the videos. This type of setup allows you to extend it! You will need to have your script loaded before ours.
	if(typeof ziggeoVideosReject !== 'function') {
		function ziggeoVideosReject(element_ref) {
			var _video_ref = element_ref.parentElement.parentElement.getAttribute('video_ref');

			ziggeo_app.videos.update(ZiggeoWP.video_list[_video_ref].token, { 'approved': false });

			element_ref.parentElement.parentElement.firstElementChild.className = 'player rejected';
		}
	}

	//Function to reject the videos. This type of setup allows you to extend it! You will need to have your script loaded before ours.
	if(typeof ziggeoVideosRemove !== 'function') {
		function ziggeoVideosRemove(element_ref) {
			var _video_ref = element_ref.parentElement.parentElement.getAttribute('video_ref');

			if(ZiggeoWP.server_auth && ZiggeoWP.server_auth !== '') {

				// Let's be safe and confirm first
				if(confirm('Are you sure you want to remove the video?') === true) {
					var request = ziggeo_app.videos.destroy( ZiggeoWP.video_list[_video_ref].token,
															{ 'server_auth': ZiggeoWP.server_auth });

					request.success( function() {
						element_ref = element_ref.parentElement.parentElement;
						element_ref.style.transition = '2s all ease-in-out';
						element_ref.style.maxHeight = '0px';
						element_ref.style.backgroundColor = 'red';

						setTimeout(function() {
							element_ref.parentElement.removeChild(element_ref);
						}, 3800);
					});

					request.error(function(er) {
						alert('Server responded with ' + er.__status_code + ' ' + er.__status_text);
					})
				}
			}
			else {
				alert('You will need to add server auth token into settings to be able to remove videos');
			}
		}
	}

	//Function to reject the videos. This type of setup allows you to extend it! You will need to have your script loaded before ours.
	if(typeof ziggeoVideosGetURL !== 'function') {
		function ziggeoVideosGetURL(element_ref) {
			var _video_ref = element_ref.parentElement.parentElement.getAttribute('video_ref');

			//alert('URL for video is: https://' + ZiggeoWP.video_list[_video_ref].embed_video_url);
			alert('URL for video is: https://ziggeo.io/p/' + ZiggeoWP.video_list[_video_ref].token);
		}
	}

	if(typeof ziggeoVideosEditCustomData !== 'function') {
		function ziggeoVideosEditCustomData(element_ref) {
			var _video_ref = element_ref.parentElement.parentElement.getAttribute('video_ref');

			var request = ziggeo_app.videos.get( ZiggeoWP.video_list[_video_ref].token );

			request.success( function(video) {
				ziggeoPUIVideosPopupCreate({
					data_to_show:   video.data,
					data_saved:     'data',
					media_token:    ZiggeoWP.video_list[_video_ref].token,
					format:         'json',
					allowed:        'edit',
				});
			});
		}
	}

	if(typeof ziggeoVideosGrabShortcodes !== 'function') {
		function ziggeoVideosGrabShortcodes(element_ref) {
			var _video_ref = element_ref.parentElement.parentElement.getAttribute('video_ref');

			var request = ziggeo_app.videos.get( ZiggeoWP.video_list[_video_ref].token );

			request.success( function(video) {
				ziggeoPUIVideosPopupCreate({
					//data_to_show:   video.data,
					//data_saved:     'data',
					media_token:    ZiggeoWP.video_list[_video_ref].token,
					shortcodes: true
					//format:         'json',
					//allowed:        'edit',
				});
			});
		}
	}

	//Create popup
	function ziggeoPUIVideosPopupCreate(obj_data) {

		//If it exists, destroy the previous one..
		if(document.getElementsByClassName('ziggeo_videoslist_cover').length > 0) {
			ziggeoPUIVideosPopupDestroy();
		}

		var cover = document.createElement('div');
		cover.className = 'ziggeo_videoslist_popup_cover';

		var inner = document.createElement('div');
		inner.className = 'ziggeo_videoslist_popup_frame';

			var btn_save = document.createElement('div');
			btn_save.className = 'ziggeo-ctrl-btn ziggeo-btn-popup-save';
			btn_save.innerText = 'Save';

			var btn_cancel = document.createElement('div');
			btn_cancel.className = 'ziggeo-ctrl-btn ziggeo-btn-popup-cancel';
			btn_cancel.innerText = 'Cancel';

			if(obj_data.shortcodes && obj_data.shortcodes === true) {
				btn_cancel.innerText = 'Close';
				inner.appendChild(btn_cancel);

				var sub = document.createElement('div');
				sub.className = 'popupinner';

				// We just show different options
				var info1 = document.createElement('span');
				info1.innerText = '[ziggeoplayer ' + obj_data.media_token + ']';

				var info1desc = document.createElement('p');
				info1desc.className = 'description';
				info1desc.innerText = 'Shortcode for default player playing specified video';

				var info2 = document.createElement('span');
				info2.innerText = '[ziggeoplayer]' + obj_data.media_token + '[/ziggeoplayer]';

				var info2desc = document.createElement('p');
				info2desc.className = 'description';
				info2desc.innerText = 'Same as first for people preferring closed shortcodes';

				var info3 = document.createElement('span');
				info3.innerText = '[ziggeoplayer video="' + obj_data.media_token + '"]';

				var info3desc = document.createElement('p');
				info3desc.className = 'description';
				info3desc.innerText = 'Same as first, useful if you want to add more parameters to shortcode';

				var info4 = document.createElement('span');
				info4.innerText = '[ziggeotemplate {TEMPLATE_ID} ' + obj_data.media_token + ']';

				var info4desc = document.createElement('p');
				info4desc.className = 'description';
				info4desc.innerText = 'Allows to set video to be played while using specific template (replace {TEMPLATE_ID} with actual template ID';

				var info5 = document.createElement('span');
				info5.innerText = '[ziggeodownloads ' + obj_data.media_token + ']';

				var info5desc = document.createElement('p');
				info5desc.className = 'description';
				info5desc.innerText = 'Shortcode to provide download link for some video (includes all streams)';

				sub.appendChild(info1);
				sub.appendChild(info1desc);
				sub.appendChild(info2);
				sub.appendChild(info2desc);
				sub.appendChild(info3);
				sub.appendChild(info3desc);
				sub.appendChild(info4);
				sub.appendChild(info4desc);
				sub.appendChild(info5);
				sub.appendChild(info5desc);

				inner.appendChild(sub);
			}
			else {

				inner.appendChild(btn_save);
				inner.appendChild(btn_cancel);

				var _textarea = document.createElement('textarea');

				//We need to handle different types of data differently
				if(obj_data.format === 'json') {
					if(obj_data.data_to_show === null) {
						_textarea.value = '{}';
					}
					else {
						_textarea.value = JSON.stringify(obj_data.data_to_show).replace(/{/g, '{\n\t').replace(/\",\"/g, '",\n\t"').replace(/\"}/g, '"\n}');
					}
				}
				else {
					_textarea.value = obj_data.data_to_show;
				}

				_textarea.setAttribute('data-format', obj_data.format);

				if(obj_data.allowed !== 'edit') {
					_textarea.disabled = 'disabled';
				}

				inner.appendChild(_textarea);
			}

		cover.appendChild(inner);
		document.body.appendChild(cover);

		jQuery('.ziggeo_videoslist_popup_frame .ziggeo-btn-popup-save').on('click', function(event) {

			//We need to prepare the data:
			if(obj_data.format === 'json') {
				var data_to_save = JSON.stringify( JSON.parse(_textarea.value) );
			}
			else if(obj_data.format === 'csv') {
				var data_to_save = _textarea.value.replace(/ /g, ' ').replace(/\n/g, '').replace(/\t/g,'').replace(/, /g, ',');
			}
			else {
				var data_to_save = _textarea.value;
			}

			var obj_save = {};
			obj_save[obj_data.data_saved] = data_to_save;

			var result = ziggeo_app.videos.update(obj_data.media_token, obj_save);

			result.success( function() {
				_textarea.style.border = '1px solid green';
				_textarea.style.boxShadow = '0 0 10px green';

				ZiggeoWP.hooks.fire('ziggeo_videolist_info_edit', {
					property:   obj_data.data_saved,
					saved:      data_to_save,
					format:     obj_data.format
				});

				setTimeout(function() {
					ziggeoPUIVideosPopupDestroy();
				}, 1000);
			});

			result.error( function(error) {
				_textarea.style.border = '1px solid red';
				_textarea.style.boxShadow = '0 0 10px red';
				alert(error);
			});
		});

		jQuery('.ziggeo_videoslist_popup_frame .ziggeo-btn-popup-cancel').on('click', function(event) {
			ziggeoPUIVideosPopupDestroy();
		});
	}

	function ziggeoPUIVideosPopupDestroy() {
		var list = document.getElementsByClassName('ziggeo_videoslist_popup_cover');

		for(i = 0; i < list.length; i++) {
			list[i].parentElement.removeChild(list[i]);
		}
	}

	//A quick way to clear out all fields
	function ziggeoPUIVideosFilterReset() {
		var _placeholder = document.getElementById('ziggeo-videos-filter');
		_placeholder.querySelector('.token').value = '';
		_placeholder.querySelector('.moderation').value = 'all';
		_placeholder.querySelector('.tags').value = '';
		_placeholder.querySelector('.sort').value = 'new';
	}

	//Creates the page navigation buttons
	function ziggeoPUIVideosPageCreateNavigation(page_num) {

		var _nav = document.getElementById('ziggeo-videos-nav');
		_nav.className = _nav.className.replace('disabled', '');
		//Clear it up
		_nav.innerText = '';

		for(i = 0, c = Math.floor(ZiggeoWP.video_list.length / 10); i < c; i++) {
			var _btn = document.createElement('div');
			_btn.className = 'ziggeo-ctrl-btn';

			if(i+1 === page_num) {
				_btn.className += ' disabled';
			}

			_btn.innerText = i+1;

			_nav.appendChild(_btn);
		}

		ZiggeoWP.video_list_current = page_num;

		//Set the event
		jQuery('#ziggeo-videos-nav .ziggeo-ctrl-btn').on('click', function( event ) {
			ziggeoPUIVideosPageSwitch(event.currentTarget.innerText * 1);
		});
	}

	//Function that helps us move through pages
	function ziggeoPUIVideosPageSwitch(page_num) {

		ziggeoPUIVideosMessage('Taking you to a new page...');
		document.getElementById('ziggeo-videos-nav').className += ' disabled';
		document.getElementById('ziggeo-videos').removeAttribute('_clear');

		ZiggeoWP.video_list_current = page_num;

		//Detect if we have enough of the videos to show page, or if we need to get more.
		if((page_num+1) * 10 > ZiggeoWP.video_list.length) {
			ziggeoPUIVideosFilter(false, page_num * 10);
		}
		else {
			page_num--;
			ziggeoPUIVideosHasVideos(ZiggeoWP.video_list.slice(page_num*10, page_num*10+10), true);
		}
	}

	//Function to update the count of videos found and shown
	function ziggeoPUIVideosPageCounter(query_obj) {
		//Requires the PHP SDK to be added. Happy to add if it is asked for it
	}

	// Function that checks if this is a 401 error and if it is, displays the message about it and returns true,
	// if not returns false indicating it is a different error
	function ziggeoPUIVideosIndexNotAllowed(err) {
		if(err.indexOf('401') > -1) {
			// Show message
			var msg = 'Indexing seems to be disabled in your account. To turn it on, please follow next steps';
			msg += "\n";
			msg += '1. Log into your Ziggeo account';
			msg += "\n";
			msg += '2. Click on the Application you are using on your WordPress website';
			msg += "\n";
			msg += '3. Now go to <b>Manage</b> sub menu';
			msg += "\n";
			msg += '4. And then go to <b>Authorization Settings</b> section';
			msg += "\n";
			msg += '5. Make sure the "Client is allowed to perform the index operation" is checked';
			msg += "\n";
			msg += '6. Click on Save';
			msg += "\n";
			msg += '7. Refresh this page';

			ziggeoPUIVideosMessage(msg, 'error', null);
			// stop further processing
			return true;
		}

		return false;
	}




/////////////////////////////////////////////////
// 10. ADDONS PAGE                              //
/////////////////////////////////////////////////

	function ziggeoPUIAddonsInit() {
		var _nav = document.getElementById('ziggeo-addons-nav')

		_nav.querySelector('[data-section="installed"]').addEventListener('click', function() {
			ziggeoPUIAddonsSwitch('ziggeo_addons_installed', '[data-section="installed"]');
		});

		_nav.querySelector('[data-section="updates"]').addEventListener('click', function() {
			ziggeoPUIAddonsSwitch('ziggeo_addons_update', '[data-section="updates"]');
		});

		_nav.querySelector('[data-section="store"]').addEventListener('click', function() {
			ziggeoPUIAddonsSwitch('ziggeo_addons_store', '[data-section="store"]');
		});
	}

	function ziggeoPUIAddonsSwitch(show, in_nav) {
		var _installed = document.getElementById('ziggeo_addons_installed');
		_installed.style.display = 'none';

		var _update = document.getElementById('ziggeo_addons_update');
		_update.style.display = 'none';

		var _store = document.getElementById('ziggeo_addons_store');
		_store.style.display = 'none';

		_show = document.getElementById(show);
		_show.style.display = 'block';

		var _nav = document.getElementById('ziggeo-addons-nav')

		_nav.querySelector('.selected').className = _nav.querySelector('.selected').className .replace(' selected', '');

		_nav.querySelector(in_nav).className += ' selected';
	}




/////////////////////////////////////////////////
// 11. SDK PAGE                                 //
/////////////////////////////////////////////////

	//Add functionality to the butons on the SDK page
	function ziggeoPUISDKInit() {

		var i, l, j, c;

		var tabs = document.getElementsByClassName('ziggeo_tab');

		//Support for clicking on tabs
		for(i = 0, c = tabs.length; i < c; i++) {
			tabs[i].addEventListener('click', function(e) {
				var current = document.getElementsByClassName('ziggeo_tab selected')[0];
				current.className = 'ziggeo_tab';
				document.getElementById('ziggeo_tab_' + current.getAttribute('data-tab')).style.display = 'none';

				var tab = e.target;
				tab.className += ' selected';
				document.getElementById('ziggeo_tab_' + tab.getAttribute('data-tab')).style.display = 'block';
			});
		}

		// Set the click event for the fields within the application segment
		document.getElementById('ziggeo_tab_applications').addEventListener('click', function(event) {
			var current_element = event.target;

			//Quick filter
			if(current_element.tagName !== 'SPAN') {
				return false;
			}

			//Only do this on actual buttons
			if(current_element.className.indexOf('ziggeo-ctrl-btn') > -1) {
				//standard buttons
				if(current_element.className.indexOf('ziggeo-sdk-ajax') > -1) {
					ziggeoPUISDKButtons(current_element);
				}
			}
			//image radio buttons (on/off)
			else if(current_element.className.indexOf('ziggeo-ctrl-img-toggle') > -1) {
				ziggeoPUISDKImageToggle(current_element);
			}

		});

		// Set the click event for the fields within the application segment
		document.getElementById('ziggeo_tab_analytics').addEventListener('click', function(event) {
			var current_element = event.target;

			//Quick filter
			if(current_element.tagName !== 'SPAN') {
				return false;
			}

			//Only do this on actual buttons
			if(current_element.className.indexOf('ziggeo-ctrl-btn') > -1) {
				//standard buttons
				if(current_element.className.indexOf('ziggeo-sdk-ajax') > -1) {
					ziggeoPUISDKButtons(current_element);
				}
			}

		});

		// OnChange event handler for selects
		var dropdowns = document.getElementsByClassName('ziggeo-sdk-ajax-dropdown');

		for(i = 0, c = dropdowns.length; i < c; i++) {
			var _current = dropdowns[i];
			_current.addEventListener('change', function() {
				ziggeoPUISDKDropdown(_current);
			});
		}

		//Add calendars to our page
		jQuery('#calendar_from').datepicker({
			dateFormat: '@', // This makes it return Unix time.
			onSelect: function(str_date, instance) {
				document.getElementById('analytics-from').value = str_date;
			}
		});

		jQuery('#calendar_to').datepicker({
			dateFormat: '@', // This makes it return Unix time.
			buttonText: "To",
			onSelect: function(str_date, instance) {
				document.getElementById('analytics-to').value = str_date;
			}
		});

		//Set the click event
		document.getElementById('ziggeo_tab_effectprofiles').addEventListener('click', function(event) {
			var current_element = event.target;

			//Quick filter
			if(current_element.tagName !== 'SPAN') {
				return false;
			}

			//Only do this on actual buttons
			if(current_element.className.indexOf('ziggeo-ctrl-btn') > -1) {
				if(current_element.className.indexOf('ziggeo-sdk-ajax-form') > -1) {
					ziggeoPUISDKEffectProfilesButtonForms(current_element);
				}
				else if(current_element.className.indexOf('ziggeo-ctrl-form-popup') > -1) {
					ziggeoPUISDKEffectProfilesButtonFormPopup(current_element);
				}
				else if(current_element.className.indexOf('ziggeo-sdk-ajax') > -1) {
					ziggeoPUISDKEffectProfilesButtons(current_element);
				}
			}

		});

		// This allows us to change the application that is used on the SDK page
		document.getElementById('applications_list').addEventListener('change', function(e) {
			var _current = e.target;

			var text = _current[_current.selectedIndex].innerText;
			document.getElementById('ziggeo_title_app').innerText = '(' + text + ')';

			if(!ZiggeoWP.sdk) {
				ZiggeoWP.sdk = {};
			}

			ZiggeoWP.sdk.app_token = _current.value;
			ZiggeoWP.sdk.title = text;
		});
	}

	//Image toggle control on the SDK page
	function ziggeoPUISDKImageToggle(btn_current) {
		var i, c;

		var data = {
			'sdk_action' : btn_current.getAttribute('data-action'),
			'operation'  : btn_current.getAttribute('data-operation'),
			'value'      : btn_current.getAttribute('data-value')
		};

		// This allows us to get parameters that we do not hardcode into JS side.
		var options = btn_current.getAttribute('data-options');

		// This allows us to get additional options from within the control minimizing the JS codes
		if(options) {
			options = options.split(',');
			for(i = 0, c = options.length; i < c; i++) {
				var _current = options[i].split(':');
				data[_current[0]] = btn_current.getAttribute('data-' + _current[1]);
			}
		}

		//We need to switch the value. With toggle buttons it will always be current and we will only update it once we get back the response..
		data.value = (data.value === 'on') ? 'off': 'on';

		btn_current.className += ' disabled';

		ziggeoAjax(data, function(response) {

			//We should get back an object
			response = JSON.parse(response);

			if(response.status && response.status === 'success') {

				if(response.result !== '' && response.result !== 'false' && response.result !== null) {

					//To clear current status
					btn_current.className = btn_current.className.replace(' on', '');
					btn_current.className = btn_current.className.replace(' off', '');

					//We now save the new value
					btn_current.setAttribute('data-value', data.value);

					if(data.value === 'on') {
						btn_current.className += ' on';
					}
					else {
						btn_current.className += ' off';
					}
				}
				else {
					alert('The initiated request is not seen as valid.');
				}

				btn_current.className = btn_current.className.replace(' disabled', '');
			}
			else {
				ziggeoDevReport('Something wrong just happened.');
			}
		});
	}

	// Function to help us with standard dropdowns that are used to make changes within the dashboard through AJAX
	// These are standard dropdowns with class "ziggeo-sdk-ajax-dropdown"
	function ziggeoPUISDKDropdown(btn_current) {

		var i, c;

		var data = {
			'sdk_action' : btn_current.getAttribute('data-action'),
			'operation'  : btn_current.getAttribute('data-operation')
		};

		//Should we get value from sowewhere?
		var value = btn_current.getAttribute('data-value');

		//We should get the value
		if(value) {
			value = value.split(',');

			for(i = 0, c = value.length; i < c; i++) {
				var current = value[i].split(':');
				data[current[0]] = document.getElementById(current[1]).value;
			}
		}

		btn_current.className += ' disabled';

		ziggeoAjax(data, function(response) {

			//We should get back an object
			response = JSON.parse(response);

			if(response.status && response.status === 'success') {

				if(response.result !== '' && response.result !== false && response.result !== 'false' &&
					response.result !== null) {
					if(btn_current.getAttribute('data-results')) {
						//We are expected to put the returned values somewhere
						//We might want to change this to function names so we call them with data instead, at least when the data returend is HTML
						document.getElementById(btn_current.getAttribute('data-results')).innerHTML = response.result;
					}
				}
				else {
					alert('The initiated request is not seen as valid.');
				}

				btn_current.className = btn_current.className.replace(' disabled', '');
			}
			else {
				ziggeoDevReport('Something wrong just happened.');
			}
		});
	}

	//Function to handle the buttons on the SDK pages
	function ziggeoPUISDKButtons(btn_current) {

		var i, c;

		var data = {
			'sdk_action' : btn_current.getAttribute('data-action'),
			'operation'  : btn_current.getAttribute('data-operation')
		};

		//We can add validation to our values
		var validate = btn_current.getAttribute('data-validate');
		var is_valid = true;

		if(validate) {
			validate = validate.split(',');

			for(i = 0, c = validate.length; i < c; i++) {
				var _current = validate[i].split(':');
				var _validate = document.getElementById(_current[1]);

				// We remove any previous info about the error being there (if any)
				_validate.className = _validate.className.replace(' has_error', '');
				switch(_current[0]) {
					case 'notempty':
						if(ziggeoPValidateEmpty(_validate.value)) {
							is_valid = false;
							_validate.className += ' has_error';
						}
						break;
				}
			}
		}

		//This way we do not do any action if the values do not pass validation
		if(is_valid === false) {
			return false;
		}

		//Should we get value from sowewhere?
		var value = btn_current.getAttribute('data-value');

		//We should get the value
		if(value) {
			value = value.split(',');

			for(i = 0, c = value.length; i < c; i++) {
				var current = value[i].split(':');
				if(current[1].indexOf('{') > -1) {

					var segment = current[1].replace('{', '').replace('}', '');
					if(typeof ZiggeoWP.sdk === 'undefined' || typeof ZiggeoWP.sdk[segment] === 'undefined') {
						var segment_value = '';
					}
					else {
						var segment_value = ZiggeoWP.sdk[segment];
					}
					data[current[0]] = segment_value;
					//ZiggeoWP.sdk.app_token = _current.value;
  					//ZiggeoWP.sdk.title = text;
				}
				else {
					data[current[0]] = document.getElementById(current[1]).value;
				}
			}
		}

		// This allows us to get parameters that we do not hardcode into JS side.
		var options = btn_current.getAttribute('data-options');

		// This allows us to get additional options from within the control minimizing the JS codes
		if(options) {
			options = options.split(',');
			console.log('to be added');
			//for(i = 0, c = options.length; i < c; i++) {
			//	data[options[i]] = '';
			//}
		}

		btn_current.className += ' disabled';

		ziggeoAjax(data, function(response) {

			//We should get back an object
			response = JSON.parse(response);

			if(response.status && response.status === 'success') {

				if(response.result !== '' && response.result !== 'false' && response.result !== null) {
					if(btn_current.getAttribute('data-results')) {

						var _where_what = btn_current.getAttribute('data-results');

						//We have a function to call
						if(_where_what.indexOf('{') > -1) {
							_where_what = _where_what.replace('{', '').replace('}', '');
							if(typeof window[_where_what] === 'function') {
								window[_where_what](response.result);
							}
						}
						else {
							//We are expected to put the returned values somewhere
							document.getElementById(btn_current.getAttribute('data-results')).innerHTML = response.result;
						}
					}
				}
				else {
					alert('The initiated request is not seen as valid.');
				}

				btn_current.className = btn_current.className.replace(' disabled', '');
			}
			else {
				ziggeoDevReport('Something wrong just happened.');
			}
		});
	}


	// Analytics
	// ***********

	//This function creates the graphs
	// It is called when dates are changed, so everything is drawn first time, using the data we just got.
	function ziggeoPUISDKAnalyticsCreateGraphs(data) {

		var i,c,j,k,l,m;

		var available = [
			'device_views_by_os',
			'device_views_by_date',
			'total_plays_by_country',
			'full_plays_by_country',
			'total_plays_by_hour',
			'full_plays_by_hour',
			'total_plays_by_browser',
			'full_plays_by_browser'
		];

		var values = {
			'device_views_by_os': {
				label: 'os',
				value: 'event_count',
				chart_type: 'doughnut'
			},
			'device_views_by_date': {
				label: 'type',
				value: 'event_count',
				chart_type: 'polarArea'
				//date
			},
			'total_plays_by_country': {
				label: 'country',
				value: 'event_count',
				chart_type: 'polarArea'
			},
			'full_plays_by_country': {
				label: 'country',
				value: 'event_count',
				chart_type: 'radar'
			},
			'total_plays_by_hour': {
				label: 'date',
				value: 'event_count',
				chart_type: 'polarArea'
				//hour
			},
			'full_plays_by_hour': {
				label: 'date',
				value: 'event_count',
				chart_type: 'radar'
				//hour
			},
			'total_plays_by_browser': {
				label: 'device_browser',
				value: 'event_count',
				chart_type: 'polarArea'
				//device_os
			},
			'full_plays_by_browser': {
				label: 'device_browser',
				value: 'event_count',
				chart_type: 'polarArea'
				//device_os
			}
		}

		var colors = [
			'#FF6384',
			'#4BC0C0',
			'#FFCE56',
			'#E7E9ED',
			'#36A2EB',
			'#F26354',
			'#F29E54',
			'#F2E854',
			'#77F254',
			'#54F2CD',
			'#54C6F2',
			'#9C54F2',
			'#ED54F2',
			'#F2549E',
			'#F25454'
		];

		var months = [ 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

		//Sanity check
		if(typeof ZiggeoWP.sdk !== 'undefined') {

			if(typeof ZiggeoWP.sdk.charts !== 'undefined') {
				//We need to clear out the existing charts
				for(i = 0, c = ZiggeoWP.sdk.charts.length; i < c; i++) {
					ZiggeoWP.sdk.charts[i].destroy();
				}
			}

			ZiggeoWP.sdk.charts = [];
			ZiggeoWP.sdk.charts_data = {};
		}
		else {
			ZiggeoWP.sdk = {
				charts: [],
				charts_data: {}
			};
		}

		// handles per query (device_views_by_os)
		for(i = 0, c = available.length; i < c; i++) {
			var graph_info = data[available[i]];

			if(typeof graph_info === 'string') {
				//Something went wrong.
				ziggeoDevReport('Something went wrong getting analytics data');
				continue;
			}

			var graph_data = {
				datasets: [],
				labels: []
			};

			var extras = {
				data: [],
				backgroundColor: colors,
				label: available[i].replace(/\_/g, ' ') // Used as legend
			};

			//Handles the analytics data of the query segment
			for(j = 0, k = graph_info.analytics.length; j < k; j++) {
				var _current = graph_info.analytics[j];

				var _label = _current[values[available[i]].label];

				if(available[i] === 'device_views_by_os') {
					//on first go, we want to strip some data
					_label = _label.replace(_label.slice(_label.lastIndexOf(' ')), '');
				}
				else if(available[i] === 'total_plays_by_hour' ||
				        available[i] === 'full_plays_by_hour') {
					//on first go, we want to strip some data
					_label = String(_label).substr(0,4) + ', ' + months[ ((String(_label).substr(4,2)*1)-1) ];
				}

				var _found = false;
				for(l = 0, m = graph_data.labels.length; l < m; l++) {
					if(graph_data.labels[l] === _label) {
						_found = true;

						extras.data[l] += _current.event_count;
					}
				}

				if(_found === false) {
					graph_data.labels.push(_label);
					extras.data.push(_current.event_count);
				}
			}

			graph_data.datasets.push(extras);

			var ctx = document.getElementById('ziggeo_graph_' + available[i]);
			var config = {
				data: graph_data,
				type: values[available[i]].chart_type,
				options: {
					responsive: true,
					plugins: {
						legend: {
							position: 'top',
						},
						title: {
							display: true,
							text: available[i].replace(/\_/g, ' ') // Used as legend
						}
					}
				}
			};

			if(available[i] === 'device_views_by_os' || 
				available[i] === 'device_views_by_date' ||
				available[i] === 'full_plays_by_browser' ||
				available[i] === 'total_plays_by_browser' ||
				available[i] === 'full_plays_by_hour' ||
				available[i] === 'total_plays_by_country' ||
				available[i] === 'full_plays_by_country' ||
				available[i] === 'total_plays_by_hour') {

				config.options.onClick = (event, activeElements) => {
					if(activeElements.length === 0){
						return false;
					}
					chart_id = event.chart.canvas.id.replace('ziggeo_graph_', '');

					var chart = event.chart;
					var activePoints = chart.getElementsAtEventForMode(event, 'point', chart.options);
					var firstPoint = activePoints[0];
					var label = chart.data.labels[firstPoint.index];
					var value = chart.data.datasets[firstPoint.datasetIndex].data[firstPoint.index];

					//Create new "zoomed in" chart
					ziggeoPUISDKAnalyticsCreateGraphZoomedIn(ZiggeoWP.sdk.charts_data[chart_id], label, chart_id);
				};
			}

			var chart = new Chart(ctx, config);

			ctx.parentElement.setAttribute('chart_id', i);
			ZiggeoWP.sdk.charts.push(chart);
		}

		ZiggeoWP.sdk.charts_data = data;
		document.getElementById('analytics_data').className = 'graphs';
	}

	//This function is used to create the data shown in the big canvas used as a zoom in screen
	function ziggeoPUISDKAnalyticsCreateGraphZoomedIn(data, zoom_value, query_type) {

		var i,c,j,k;

		var available = [
			'device_views_by_os',
			'device_views_by_date',
			'total_plays_by_country',
			'full_plays_by_country',
			'total_plays_by_hour',
			'full_plays_by_hour',
			'total_plays_by_browser',
			'full_plays_by_browser'
		];

		var values = {
			'device_views_by_os': {
				label: 'os',
				value: 'event_count',
				chart_type: 'polarArea'
			},
			'device_views_by_date': {
				label: 'type',
				value: 'event_count',
				chart_type: 'bar',
				label_alt: 'date'
			},
			'total_plays_by_country': {
				label: 'country',
				value: 'event_count',
				chart_type: 'radar',
				label_alt: 'video_token'
			},
			'full_plays_by_country': {
				label: 'country',
				value: 'event_count',
				chart_type: 'radar',
				label_alt: 'video_token'
			},
			'total_plays_by_hour': {
				label: 'date',
				value: 'event_count',
				chart_type: 'polarArea',
				label_alt: 'hour'
			},
			'full_plays_by_hour': {
				label: 'date',
				value: 'event_count',
				chart_type: 'polarArea',
				label_alt: 'hour'
			},
			'total_plays_by_browser': {
				label: 'device_browser',
				value: 'event_count',
				chart_type: 'polarArea',
				label_alt: 'device_os'
			},
			'full_plays_by_browser': {
				label: 'device_browser',
				value: 'event_count',
				chart_type: 'polarArea',
				label_alt: 'device_os'
			}
		}

		var colors = [
			'rgba(255, 99, 132, 0.6)',  //#FF6384
			'rgba(75, 192, 192, 0.6)',  //#4BC0C0
			'rgba(255, 206, 86, 0.6)',  //#FFCE56
			'rgba(231, 233, 237, 0.6)', //#E7E9ED
			'rgba(54, 162, 235, 0.6)',  //#36A2EB
			'rgba(242, 99, 84, 0.6)',   //#F26354
			'rgba(242, 158, 84, 0.6)',  //#F29E54
			'rgba(242, 232, 84, 0.6)',  //#F2E854
			'rgba(119, 242, 84, 0.6)',  //#77F254
			'rgba(84, 242, 205, 0.6)',  //#54F2CD
			'rgba(84, 198, 242, 0.6)',  //#54C6F2
			'rgba(156, 84, 242, 0.6)',  //#9C54F2
			'rgba(237, 84, 242, 0.6)',  //#ED54F2
			'rgba(242, 84, 158, 0.6)',  //#F2549E
			'rgba(242, 84, 84, 0.6)'   //#F25454
		];

		//get the reference to the big canvas
		var big_chart = Chart.getChart('ziggeo_graph_big');

		var months = [ 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

		//If chart is not created already, this will be undefined, so we know we should destroy it if it is not undefined.
		if(typeof big_chart !== 'undefined') {
			big_chart.destroy();
		}

		var graph_info = data;

		var graph_data = {
			datasets: [],
			labels: []
		};

		var extras = {
			data: [],
			backgroundColor: colors,
			label: query_type.replace(/\_/g, ' ') // Used as legend
		};

		//Handles the analytics data of the query segment
		for(i = 0, c = graph_info.analytics.length; i < c; i++) {
			var _current = graph_info.analytics[i];

			var _label = _current[values[query_type].label];

			if(query_type === 'full_plays_by_hour' ||
				query_type === 'total_plays_by_hour') {
				_label = String(_label).substr(0,4) + ', ' + months[ ((String(_label).substr(4,2)*1)-1) ];
			}

			if(String(_label).indexOf(zoom_value) > -1) {

				//We need to do it again, since we are getting days now
				if(query_type === 'device_views_by_date') {
					var _label = _current[values[query_type].label_alt];
					_label = months[ ((String(_label).substr(4,2)*1)-1) ] + ' ' + String(_label).substr(6,2);
				}
				else if(query_type === 'full_plays_by_browser' ||
						query_type === 'total_plays_by_browser' ||
						query_type === 'full_plays_by_hour' ||
						query_type === 'total_plays_by_country' ||
						query_type === 'full_plays_by_country' ||
						query_type === 'total_plays_by_hour') {
					var _label = _current[values[query_type].label_alt];
				}

				if(query_type === 'full_plays_by_hour' ||
					query_type === 'total_plays_by_hour') {
					_label += ':00';
				}

				var _found = false;
				for(j = 0, k = graph_data.labels.length; j < k; j++) {
					if(graph_data.labels[j] === _label) {
						_found = true;
						extras.data[j] += _current.event_count;
					}
				}

				if(_found === false) {
					graph_data.labels.push(_label);
					extras.data.push(_current.event_count);
				}
			}
		}

		graph_data.datasets.push(extras);

		var ctx = document.getElementById('ziggeo_graph_big');
		var config = {
			data: graph_data,
			type: values[query_type].chart_type,
			options: {
				responsive: true,
				onClick: (event, activeElements) => {
					setTimeout(function() {
						var big_chart = Chart.getChart('ziggeo_graph_big');
						big_chart.destroy();
						ctx.parentElement.style.display = 'none';
					}, 400);

					return true;
				},
				plugins: {
					legend: {
						position: 'top',
					},
					title: {
						display: true,
						text: query_type.replace(/\_/g, ' ') + ' [' + zoom_value + ']'
					}
				}
			}
		};

		var chart = new Chart(ctx, config);
		ctx.parentElement.style.display = 'block';
	}


	// Effect Profiles
	// *****************

	function ziggeoPUISDKEffectsProfileProcessList(data, token) {
		var i, l, j, c;
		var keys = ['token', 'title', 'type', 'description', 'configuration'];

		var element_info = document.getElementById('ziggeo-sdk-effects-' + token);
		element_info.innerText = '';

		for(i = 0, l = data.length; i < l; i++) {

			var element_row = document.createElement('div');
			element_row.className = 'process';

			for(j = 0, c = keys.length; j < c; j++) {
				var span_k = document.createElement('span');
				span_k.textContent = keys[j];

				var span_t = document.createElement('span');
				span_t.textContent = data[i][keys[j]];

				element_row.appendChild(span_k);
				element_row.appendChild(span_t);
			}

			var span_kt = document.createElement('span');
			span_kt.textContent = 'Created at';

			var span_tt = document.createElement('span');
			span_tt.textContent = ziggeoUnixTimetoString(data[i].attrs.created,
			                                             ZiggeoWP.format_date + ' ' + ZiggeoWP.format_time);

			element_row.appendChild(span_kt);
			element_row.appendChild(span_tt);

			element_info.appendChild(element_row);
		}

		if(!element_row) {
			var info = document.createElement('span');
			info.textContent = 'This effect profile has no effect processes added to it.'
			element_info.appendChild(info);
		}
	}

	function ziggeoPUISDKEffectsProfileDelete(data, token) {

		var elem = document.getElementById('effect-profile-' + token);

		elem.parentElement.removeChild(elem);
	}

	function ziggeoPUISDKEffectsProfileCreate(data) {
		var list = document.getElementById('effect_profile_list').getElementsByClassName('ziggeosdk effect_profiles_list')[0];

		list.insertAdjacentHTML('afterbegin', data);
	}

	//Shows the form to create effect profile process
	function ziggeoPUISDKEffectsProfileProcessCreateForm(effect_token) {
		var _form = document.createElement('div');
		_form.className = 'ziggeo_popup_form';
		_form.setAttribute('data-token', effect_token);

		//Initial switch between effect and watermark
		var _switches = ['Effect Filter', 'Watermark'];

		var _switch_placeholder = document.createElement('div');
		_switch_placeholder.className = 'ziggeo-ctrl-radio-group';

		for(i = 0; i < _switches.length; i++) {
			var _switch = document.createElement('span');

			_switch.className = 'ziggeo-btn-radio';

			if(i > 0) {
				_switch.className += ' disabled';
			}

			_switch.textContent = _switches[i];
			_switch.setAttribute('data-section', ziggeoStringToSafe(_switches[i]));

			_switch.addEventListener('click', function(e) {
				var _current_element = e.currentTarget;

				var _all_switches = _current_element.parentElement.getElementsByClassName('ziggeo-btn-radio');

				for(j = 0, l = _all_switches.length; j < l; j++) {
					//just to be safe, we are going to remove the same if it is already present and add in either case
					_all_switches[j].className = _all_switches[j].className.replace(' disabled', '') + ' disabled';
					var _section_deselect = document.getElementById('ziggeo-group-' + _all_switches[j].getAttribute('data-section') + '-section');

					if(_section_deselect) {
						_section_deselect.style.maxHeight = '0px';
						_section_deselect.style.display = 'none';
					}

					_current_element.className = _current_element.className.replace(' disabled', '');
				}

				var _current_section = document.getElementById('ziggeo-group-' +
				                                                       _current_element.getAttribute('data-section') +
				                                                       '-section');

				if(_current_section) {
					_current_section.style.display = 'block';
					_current_section.style.maxHeight = 'auto';
				}

			});

			_switch_placeholder.appendChild(_switch);
		}

		_form.appendChild(_switch_placeholder);

		// Create filter fields
		var filter_section = document.createElement('div');
		filter_section.className = 'ziggeo-group-section';
		filter_section.id = 'ziggeo-group-effect_filter-section';

			var filter_url = 'https://ziggeo.com/assets/imgs/features/filters/filter_'; //{name}.jpg
			var filter_list = document.createElement('ul');
			var available_filters = [
				{ name: 'gray', label: 'Black & White Effect' },
				{ name: 'lucis', label: 'Lucis Art Effect' },
				{ name: 'cartoon', label: 'Cartoon Effect' },
				{ name: 'edge', label: 'Edge Highlight Effect' },
				{ name: 'dhill', label: 'Dave Hill Effect' },
				{ name: 'charcoal', label: 'Charcoal Sketch Effect' },
				{ name: 'sketch', label: 'Sketch Effect' }
			];

			for(i = 0, c = available_filters.length; i < c; i++) {
				var effect_item = document.createElement('li');
				effect_item.className = 'ziggeo_effects_list';

				var title = document.createElement('div');
				title.className = 'effect_title';
				title.textContent = available_filters[i].label;
				effect_item.appendChild(title);

				var before = document.createElement('div');
				before.className = 'panel_left';
				before.style.backgroundImage = 'url("' + filter_url + 'before.jpg")';
				effect_item.appendChild(before);

				var after = document.createElement('div');
				after.className = 'panel_right';
				after.style.backgroundImage = 'url("' + filter_url + available_filters[i].name + '.jpg")';
				effect_item.appendChild(after);

				var before_text = document.createElement('div');
				before_text.className = 'panel_right text';
				before_text.textContent = 'Original Video Sample';
				effect_item.appendChild(before_text);

				var after_text = document.createElement('div');
				after_text.className = 'panel_right text';
				after_text.textContent = 'Example after effect is applied';
				effect_item.appendChild(after_text);

				var btn_apply = document.createElement('div');
				btn_apply.className = 'ziggeo-ctrl-btn show-on-hover';
				btn_apply.textContent = 'Apply this filter';
				btn_apply.setAttribute('data-token', effect_token);
				btn_apply.setAttribute('data-effect', available_filters[i].name);
				btn_apply.addEventListener('click', function(e) {

					var current_btn = e.currentTarget;
					var token = current_btn.getAttribute('data-token');

					var data = {
						'effect_token': token,
						'effect':       current_btn.getAttribute('data-effect'),
						'operation':    'sdk_effect_profiles',
						'sdk_action':   'effect_profiles_create_filter'
					};

					ziggeoAjax(data, function(response) {
						//We should get back an object
						response = JSON.parse(response);

						if(response.status && response.status === 'success') {

							if(data.sdk_action === 'effect_profiles_create_filter') {
								//Devs do you want to see something here?
							}
						}
						else {
							ziggeoDevReport('Something wrong just happened.');
						}

						ziggeoPUICtrlClose(_form,
						                   '#effect-profile-' + token + ' .additional_options .disabled');

						var btn = document.querySelector('#effect-profile-' + token + ' .additional_options [data-action="effect_profile_processes_list"]');
						if(btn) { btn.click(); }
					});
				});

				effect_item.appendChild(btn_apply);

				filter_list.appendChild(effect_item);
			}

			filter_section.appendChild(filter_list);

		// Create watermark fields
		var watermark_section = document.createElement('div');
		watermark_section.className = 'ziggeo-group-section';
		watermark_section.id = 'ziggeo-group-watermark-section';
		watermark_section.style.display = 'none';

			var position_x = document.createElement('input');
			position_x.id = 'ziggeo-effect-profile-watermark-position-x';
			position_x.type = 'range';
			// These are all percentages and they have to be divided by 100 when sent to server
			position_x.min = 0;
			position_x.max = 100;
			position_x.value = 50;

			var position_x_label = document.createElement('label');
			position_x_label.for = position_x.id;
			position_x_label.textContent = 'Position of watermark horizontally - presented in percentages of video starting from left (0) to right (100)';

			var position_x_value = document.createElement('span');
			position_x_value.id = 'ziggeo-effect-profile-watermark-position-x-value';
			position_x_value.textContent = '0.50%';

			watermark_section.appendChild(position_x_label);
			watermark_section.appendChild(position_x);
			watermark_section.appendChild(position_x_value);

			position_x.addEventListener('input', function(e) {
				position_x_value.textContent = (e.currentTarget.value / 100) + '%';

				image_preview.setAttribute('image_data_x', e.currentTarget.value);

				ziggeoPUISDKEffectsProfileProcessWatermarkImagePreview();
			});

			var position_y = document.createElement('input');
			position_y.id = 'ziggeo-effect-profile-watermark-position-y';
			position_y.type = 'range';
			// These are all percentages and they have to be divided by 100 when sent to server
			position_y.min = 0;
			position_y.max = 100;
			position_y.value = 50;

			var position_y_label = document.createElement('label');
			position_y_label.for = position_x.id;
			position_y_label.textContent = 'Position of watermark vertically - presented in percentages of video starting from top (0) to bottom (100)';

			var position_y_value = document.createElement('span');
			position_y_value.id = 'ziggeo-effect-profile-watermark-position-y-value';
			position_y_value.textContent = '0.50%';

			position_y.addEventListener('input', function(e) {
				position_y_value.textContent = (e.currentTarget.value / 100) + '%';

				image_preview.setAttribute('image_data_y', e.currentTarget.value);

				ziggeoPUISDKEffectsProfileProcessWatermarkImagePreview();
			});

			watermark_section.appendChild(position_y_label);
			watermark_section.appendChild(position_y);
			watermark_section.appendChild(position_y_value);

			//Specify the image scale of your watermark (a value between 0.0 and 1.0)
			var scale = document.createElement('input');
			scale.id = 'ziggeo-effect-profile-watermark-scale';
			scale.type = 'range';
			// These are all percentages and they have to be divided by 100 when sent to server
			scale.min = 0;
			scale.max = 100;
			scale.value = 25;

			var scale_label = document.createElement('label');
			scale_label.for = position_x.id;
			scale_label.textContent = 'Factor to scale your image with - 1 leave as is, anything else will reduce it.';

			var scale_value = document.createElement('span');
			scale_value.id = 'ziggeo-effect-profile-watermark-scale-value';
			scale_value.textContent = '0.25%';

			scale.addEventListener('input', function(e) {
				scale_value.textContent = (e.currentTarget.value / 100) + '%';

				image_preview.setAttribute('image_data_scale', e.currentTarget.value);

				ziggeoPUISDKEffectsProfileProcessWatermarkImagePreview();
			});

			watermark_section.appendChild(scale_label);
			watermark_section.appendChild(scale);
			watermark_section.appendChild(scale_value);

			//Video resolution
			var resolution = document.createElement('select');
			resolution.id = 'ziggeo-effect-profile-watermark-resolution';
			var option_sd = document.createElement('option');
			option_sd.textContent = '640x480';
			option_sd.value = '640x480';

			var option_hd = document.createElement('option');
			option_hd.textContent = '1280x720';
			option_hd.value = '1280x720';

			resolution.appendChild(option_sd);
			resolution.appendChild(option_hd);

			resolution.addEventListener('change', function() {
				image_preview.setAttribute('image_data_resolution', this.value);

				if(this.value === '640x480') {
					document.getElementById('effect_profiles_watermark_preview').parentElement.className = 'video_preview_sd';
				}
				else {
					document.getElementById('effect_profiles_watermark_preview').parentElement.className = 'video_preview_hd';
				}

				ziggeoPUISDKEffectsProfileProcessWatermarkImagePreview();
			});

			watermark_section.appendChild(resolution);

			//File to be uploaded
			var image_file = document.createElement('input');
			image_file.id = 'ziggeo-effect-profile-watermark-image';
			image_file.type = 'file';
			image_file.accept = 'image/*';

			image_file.addEventListener('change', function(e) {
				var _fr = new FileReader();
				_fr.readAsDataURL(e.target.files[0]);
				_fr.onload = function (e2) {

					var image_file_handler = new Image();
					image_file_handler.src = e2.target.result;

					image_file_handler.onload = function () {
						//set the width and heigh of the image preview based on the actual file.
						// take into the account the scale factor as well.
						image_preview.textContent = '';
						image_preview.setAttribute('image_data_width', this.width);
						image_preview.setAttribute('image_data_height', this.height);
						image_preview.style.backgroundImage = 'url(' + e2.target.result + ')';

						ziggeoPUISDKEffectsProfileProcessWatermarkImagePreview();
					};
				};
			});

			watermark_section.appendChild(image_file);

			// Drag and drop preview
			var video_preview = document.createElement('div');
			video_preview.className = 'video_preview_sd';

			var image_preview = document.createElement('div');
			image_preview.id = "effect_profiles_watermark_preview";
			image_preview.setAttribute('image_data_width', 320);
			image_preview.setAttribute('image_data_height', 240);
			image_preview.setAttribute('image_data_resolution', '640x480');
			image_preview.setAttribute('image_data_scale', '25');
			image_preview.setAttribute('image_data_x', '0.50');
			image_preview.setAttribute('image_data_y', '0.50');
			image_preview.style.left = '50%';
			image_preview.style.top = '50%';
			image_preview.style.width = (320 * 0.25) + 'px';
			image_preview.style.height = (240 * 0.25) + 'px';
			image_preview.textContent = 'Add Image First';

			video_preview.appendChild(image_preview);
			jQuery(image_preview).draggable({
				//thanks: https://stackoverflow.com/a/11061751
				drag: function(event, ui) {
					var pos = ui.position;

					//Make it impossible to escape the box
					var sizing = image_preview.getBoundingClientRect();

					if(pos.left < 0)	{ pos.left = 0; }
					if(pos.top < 0)		{ pos.top = 0; }
					if(image_preview.parentElement.className === 'video_preview_sd') {
						if(pos.left > 640) {
							pos.left = 640;
						}
						if(pos.top > 480) {
							pos.top = 480;
						}
					}
					if(image_preview.parentElement.className === 'video_preview_hd') {
						if(pos.left > 1280) {
							pos.left = 1280;
						}
						if(pos.top > 720) {
							pos.top = 720;
						}
					}

					image_preview.setAttribute('image_data_x', pos.left);
					image_preview.setAttribute('image_data_y', pos.top);

					ziggeoPUISDKEffectsProfileProcessWatermarkImagePreview(true);
				}
			});

			var create_watermark = document.createElement('div');
			create_watermark.className = 'ziggeo-ctrl-btn';
			create_watermark.innerText = 'Create';
			create_watermark.addEventListener('click', function(e) {

				var data = new FormData();
				var token = this.parentElement.parentElement.getAttribute('data-token')
				data.append('position_x',   position_x.value / 100);
				data.append('position_y',   position_y.value / 100);
				data.append('scale',        scale.value / 100);
				data.append('file',         image_file.files[0]);
				data.append('sdk_action',   'effect_profiles_create_watermark');
				data.append('operation',    'sdk_effect_profiles');
				data.append('effect_token', token);

				e.currentTarget.className += ' disabled';

				ziggeoAjax(data, function(response) {
					//We should get back an object
					response = JSON.parse(response);

					if(response.status && response.status === 'success') {

						if(data.sdk_action === 'effect_profiles_create_watermark') {
							//Devs do you want to see something here?
						}
					}
					else {
						ziggeoDevReport('Something wrong just happened.');
					}

					ziggeoPUICtrlClose(_form,
					                   '#effect-profile-' + token + ' .additional_options .disabled');

					var btn = document.querySelector('#effect-profile-' + token + ' .additional_options [data-action="effect_profile_processes_list"]');
					if(btn) { btn.click(); }
				}, true);
			});

			watermark_section.appendChild(video_preview);
			watermark_section.appendChild(create_watermark);

		// Create a close button
		var btn_close = document.createElement('div');
		btn_close.className = 'ziggeo-ctrl-btn close';

		btn_close.addEventListener('click', function(e) {

			var token = this.parentElement.getAttribute('data-token');

			ziggeoPUICtrlClose(e.currentTarget.parentElement,
			                   '#effect-profile-' + token + ' .additional_options .ziggeo-ctrl-form-popup.disabled');
		});

		_form.appendChild(filter_section);
		_form.appendChild(watermark_section);
		_form.appendChild(btn_close);

		document.body.appendChild(_form);
	}

	// Destroys the element that is passed in _destroy and removes the "disabled" class in the elements found using the CSS query string / CSS rule in _show.
	function ziggeoPUICtrlClose(_destroy, _show) {
		var i, c = 0;
		var to_show = document.querySelectorAll(_show);

		for(i = 0, c = to_show.length; i < c; i++) {
			to_show[i].className = to_show[i].className.replace(' disabled', '');
		}

		_destroy.parentElement.removeChild(_destroy);
	}

	//This function is used to set the image preview over the video preview element based on the settings
	function ziggeoPUISDKEffectsProfileProcessWatermarkImagePreview(on_drag) {

		//Get relevant elements
		var preview = document.getElementById('effect_profiles_watermark_preview');
		var position_x = document.getElementById('ziggeo-effect-profile-watermark-position-x');
		var position_y = document.getElementById('ziggeo-effect-profile-watermark-position-y');
		var position_x_v = document.getElementById('ziggeo-effect-profile-watermark-position-x-value');
		var position_y_v = document.getElementById('ziggeo-effect-profile-watermark-position-y-value');
		//var scale = document.getElementById('ziggeo-effect-profile-watermark-scale').value;

		//get data
		var _data = {};
		_data.width = preview.getAttribute('image_data_width');
		_data.height = preview.getAttribute('image_data_height');
		_data.resolution = preview.getAttribute('image_data_resolution');
		_data.scale = preview.getAttribute('image_data_scale');

		if(on_drag === true) {
			_data.x = preview.getAttribute('image_data_x');
			_data.y = preview.getAttribute('image_data_y');
		}
		else {
			_data.x = position_x.value;
			_data.y = position_y.value;
		}

		if(_data.resolution === '640x480') {
			var resolution_factor = { x: 640, y: 480};
		}
		else { //1280x720
			var resolution_factor = { x: 1280, y: 720};
		}

		//Set the resolution of the image
		preview.style.width = (_data.width * _data.scale / 100 ) + 'px';
		preview.style.height = (_data.height * _data.scale / 100 ) + 'px';

		//Set the value of the slider to match position of preview
		if(on_drag === true) {
			position_x.value = (_data.x / resolution_factor.x * 100).toFixed(2);
			position_y.value = (_data.y / resolution_factor.y * 100).toFixed(2);

			//Update the values shown next to sliders
			position_x_v.textContent = (_data.x / resolution_factor.x).toFixed(2) + '%';
			position_y_v.textContent = (_data.y / resolution_factor.y).toFixed(2) + '%';
		}
		else {
			preview.style.left = (_data.x * resolution_factor.x / 100).toFixed(2) + 'px';
			preview.style.top = (_data.y * resolution_factor.y / 100).toFixed(2) + 'px';
		}
	}

	function ziggeoPUISDKEffectProfilesButtons(btn_current) {
		var data = {
			token        : btn_current.getAttribute('data-token'),
			'sdk_action' : btn_current.getAttribute('data-action'),
			'operation'  : btn_current.getAttribute('data-operation')
		};

		btn_current.className += ' disabled';

		ziggeoAjax(data, function(response) {

			//We should get back an object
			response = JSON.parse(response);

			if(response.status && response.status === 'success') {

				if(data.sdk_action === 'effect_profile_get_all') {

					// #effect_profile_list
					var elem_where = document.getElementById(btn_current.getAttribute('data-results'));
					elem_where.insertAdjacentHTML('afterbegin', response.result);

					btn_current.className = btn_current.className.replace(' disabled', '');
				}
				else if(data.sdk_action === 'effect_profile_processes_list') {
					ziggeoPUISDKEffectsProfileProcessList(response.result, data.token);
					btn_current.className = btn_current.className.replace(' disabled', '');
				}
				else if(data.sdk_action === 'effect_profile_delete') {
					ziggeoPUISDKEffectsProfileDelete(response.result, data.token);
				}

			}
			else {
				ziggeoDevReport('Something wrong just happened.');
			}
		});
	}

	function ziggeoPUISDKEffectProfilesButtonForms(btn_current) {

		var data = {
			'sdk_action' : btn_current.getAttribute('data-action'),
			'operation'  : btn_current.getAttribute('data-operation')
		};

		//Disable the button as we start working on it
		btn_current.className += ' disabled';

		//Lets prepare the data
		var _keys = btn_current.getAttribute('data-keys').replace(/, /g, ',').split(',');
		var _section = btn_current.getAttribute('data-section');
		var _data = {};

		for(j = 0, c = _keys.length; j < c; j++) {

			var _field = document.getElementById(_section + '_' + _keys[j]);
			if(_field.type === 'text') {
				_data[_keys[j]] = _field.value;
			}
			else if(_field.type === 'checkbox') {
				_data[_keys[j]] = (_field.checked) ? true : false
			}
		}

		data.data = _data;

		ziggeoAjax(data, function(response) {

			//We should get back an object
			response = JSON.parse(response);

			if(response.status && response.status === 'success') {

				if(data.sdk_action === 'effect_profile_create') {
					ziggeoPUISDKEffectsProfileCreate(response.result.data, response.result.token);
				}
				else if(data.sdk_action === 'effect_profile_processes_create') {
					//We just made a new profile process, let's get a fresh listing of the available processes
					// We should clear out the listed ones (if any) first
					var _target = document.getElementById('ziggeo-sdk-effects-' + response.result.token)
					_target.innerText = '';
					_target.parentElement.querySelector('[data-action="effect_profile_processes_list"]').click();
				}

			}
			else {
				ziggeoDevReport('Something wrong just happened.');
			}

			btn_current.className = btn_current.className.replace(' disabled', '');
		});
	}

	function ziggeoPUISDKEffectProfilesButtonFormPopup(btn_current) {
		switch(btn_current.getAttribute('data-form-name')) {
			case 'effect_profile_processes_create':
				ziggeoPUISDKEffectsProfileProcessCreateForm(btn_current.getAttribute('data-token'));
				break;

			default:
				ziggeoDevReport('Unsure what form should be shown');
		}

		//Disable the button as we start working on it
		btn_current.className += ' disabled';
	}




/////////////////////////////////////////////////
// 12. EVENTS EDITOR                           //
/////////////////////////////////////////////////

	// Register functions that we need to make our Events Editor a bit interactive
	function ziggeoPUIEEInit() {

		var event_type = document.getElementById('ziggeo-ee-event-type'); // select

		var event_alert_message = document.getElementById('ziggeo-ee-event-message');
		var event_template_code = document.getElementById('ziggeo-ee-custom-code');

		var event_code_inject_onload = document.getElementById('ziggeo-ee-onload');
		var event_code_inject_onfire = document.getElementById('ziggeo-ee-onfire');

		var codes_preview = document.getElementsByClassName('ziggeo-ee-custom-code-placeholder');

		var btn_generate = document.getElementById('ziggeo-ee-btn-generate');
		var btn_save = document.getElementById('ziggeo-ee-btn-save');

		var i, c;

		// Depending on what is selected different fields are shown
		event_type.addEventListener('change', function() {
			if(event_type.value === 'template') {
				event_alert_message.parentElement.parentElement.style.display = 'none';
				event_template_code.parentElement.parentElement.style.display = 'table-row';
				event_code_inject_onload.parentElement.parentElement.style.display = 'table-row';
				btn_save.style.display = 'inline-block';
			}
			else { // alert
				event_alert_message.parentElement.parentElement.style.display = 'table-row';
				event_template_code.parentElement.parentElement.style.display = 'none';
				event_code_inject_onload.parentElement.parentElement.style.display = 'none';
			}
		});

		event_code_inject_onfire.addEventListener('click', function() {
			for(i = 0, c = codes_preview.length; i < c; i++) {
				codes_preview[i].style.display = 'block';
			}
		})

		event_code_inject_onload.addEventListener('click', function() {
			for(i = 0, c = codes_preview.length; i < c; i++) {
				codes_preview[i].style.display = 'none';
			}
		})

		btn_generate.addEventListener('click', ziggeoPUIEEGenerateShortcode);
		btn_save.addEventListener('click', ziggeoPUIEESaveTemplate);

		// Set default UI
		ziggeoPUIEEDefauts();

	}

	// Helper function to set everything to initial state
	// Used on load and on save
	function ziggeoPUIEEDefauts() {

		// Event ID is important to be added for templates, however not really important for alerts
		var event_id = document.getElementById('ziggeo-ee-event-id');
		var event_to_listen_to = document.getElementById('ziggeo-ee-event'); // select
		var event_type = document.getElementById('ziggeo-ee-event-type'); // select
		var event_alert_message = document.getElementById('ziggeo-ee-event-message');
		var event_template_code = document.getElementById('ziggeo-ee-custom-code');

		var event_code_inject_onload = document.getElementById('ziggeo-ee-onload');
		var event_code_inject_onfire = document.getElementById('ziggeo-ee-onfire');

		var elem_shortcode = document.getElementById('ziggeo-ee-shortcode');

		var codes_preview = document.getElementsByClassName('ziggeo-ee-custom-code-placeholder');

		var btn_save = document.getElementById('ziggeo-ee-btn-save');

		var i, c;

		event_id.value = '';
		event_to_listen_to.value = 'verified';
		event_type.value = 'alert'
		event_alert_message.value = '';
		event_template_code.value = '';
		event_code_inject_onload.checked = 'checked';

		// Since we are hiding a row, we need to do this as page is loaded
		event_alert_message.parentElement.parentElement.style.display = 'table-row';
		event_template_code.parentElement.parentElement.style.display = 'none';
		event_code_inject_onload.parentElement.parentElement.style.display = 'none';

		btn_save.style.display = 'none';

		for(i = 0, c = codes_preview.length; i < c; i++) {
			codes_preview[i].style.display = 'none';
		}

		elem_shortcode.value = '';
		elem_shortcode.style.display = 'none';
	}

	// This function captures the data and then sends it to the server through AJAX to save it.
	// Once done, it resets the values
	function ziggeoPUIEESaveTemplate() {

		var event_id = document.getElementById('ziggeo-ee-event-id');
		var event_to_listen_to = document.getElementById('ziggeo-ee-event'); // select
		var event_template_code = document.getElementById('ziggeo-ee-custom-code');
		var event_code_inject_onload = document.getElementById('ziggeo-ee-onload');
		var event_code_inject_onfire = document.getElementById('ziggeo-ee-onfire');

		// Save the same

		var data = {
			id: event_id.value,
			event: event_to_listen_to.value,
			code: event_template_code.value, //JSON.stringify(event_template_code.value),
			inject_type: (event_code_inject_onload.checked) ? 'on_load' : 'on_fire',
			operation: 'event_editor_save_template'
		}

		ziggeoAjax(data, function(result) {

			console.log(result);

			//ziggeoPUIEEDefauts();
		});

	}

	// This function is used to capture the values from the fields and show a copy ready shortcode to be used.
	// In case of templates, they have to be saved first to be usable
	function ziggeoPUIEEGenerateShortcode() {

		var event_id = document.getElementById('ziggeo-ee-event-id');
		var event_to_listen_to = document.getElementById('ziggeo-ee-event'); // select
		var event_type = document.getElementById('ziggeo-ee-event-type'); // select

		var event_alert_message = document.getElementById('ziggeo-ee-event-message');
		var event_template_code = document.getElementById('ziggeo-ee-custom-code');

		var event_code_inject_onload = document.getElementById('ziggeo-ee-onload');
		var event_code_inject_onfire = document.getElementById('ziggeo-ee-onfire');

		var codes_preview = document.getElementsByClassName('ziggeo-ee-custom-code-placeholder');
		var elem_shortcode = document.getElementById('ziggeo-ee-shortcode');

		var shortcode = '[ziggeo_event ';
		if(event_type.value === 'alert') {
			//[ziggeo_event event=verified message="my message" type=alert]
			shortcode += 'event=' + event_to_listen_to.value;
			shortcode += ' message="' + event_alert_message.value.replace(/"/g, '&quot;').replace(/'/g, '&apos;') + '"';
			shortcode += ' type=alert]';
		}
		else { // template
			//[ziggeo_event id="event_id_set_in_editor" type=template]
			shortcode += ' id="' + event_id.value + '"';
			shortcode += ' type=template]';
		}

		elem_shortcode.value = shortcode;
		elem_shortcode.style.display = 'block';
	}




/////////////////////////////////////////////////
// 13. AUTOCOMPLETE CONTROL                     //
/////////////////////////////////////////////////


	// Initialize the autocomplete field functionality
	function ziggeoPUICAutoCompleteInit() {
		var autocomplete = document.getElementsByClassName('ziggeo-autocomplete-input');

		if(autocomplete.length > 0) {
			var i, c;

			for(i = 0, c = autocomplete.length; i < c; i++) {
				autocomplete[i].addEventListener('keyup', function(e) {
					if(e.keyCode < 65) {
						return false;
					}

					ziggeoPUICAutoCompleteFilter(e.target, true, false);
				});
			}

			for(i = 0, c = autocomplete.length; i < c; i++) {
				autocomplete[i].addEventListener('focusout', function(e) {
					ziggeoPUICAutoCompleteFilter(e.target, true, true);
				});
			}
		}
	}

	// The actual filtering for the autocomplete to work
	function ziggeoPUICAutoCompleteFilter(input, use_children, exact_match) {

		if(use_children !== true) {
			use_children = false;
		}

		// grab selection cursor position
		var start = input.selectionStart;

		// To grab the value without the part that we have selected ourselves
		var value = input.value.substring(0, input.selectionStart);

		if(value === '') {
			return false;
		}

		// Get all of the items for autocomplete
		var items = JSON.parse(input.getAttribute('field-data'));

		// We only do the rest of processing if there is data to be processed
		if(items) {

			var i, c;
			var all_items = {};

			if(use_children) {
				for(var _item in items) {
					if(!items.hasOwnProperty(_item)) {
						continue;
					}

					all_items = Object.assign(all_items, items[_item]);
				}
			}
			else {
				all_items = items;
			}

			_item = null;

			// Now we want to go through entire list we have prepared, and filter out possible values
			// (first match found only) by default

			for(var _item in all_items) {
				var t_found = false;

				if(exact_match === true && value === _item) {
					t_found = true;
				}
				else if(exact_match !== true && _item.startsWith(value)) {
					t_found = true;
				}

				if(t_found === true) {
					// Set the value
					input.value = _item;
					input.selectionStart = value.length;
					input.selectionEnd = _item.length;

					// Call hook if any is registered
					var hook = input.getAttribute('field-hook');

					if(hook) {
						ZiggeoWP.hooks.fire(hook, { 'input': input, 'key': _item, 'data': all_items[_item] });
					}
				}
			}
		}
	}