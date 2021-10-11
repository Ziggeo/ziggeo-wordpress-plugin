// This file holds all of the functions that would be needed for the features used on admin backend to work
// All functions that start with "ziggeoP" are marked as really needing to stay as they are (important), others might be turned into customizable functions
//
// INDEX
//********
// 1. Dashboard
//		* ziggeoPUIChangeTab()
//
// 2. Helper functions
//		* jQuery.ready()
//		* ziggeoPUIHooksInit()
//		* ziggeoPOnboard()
//		* ziggeoPUIFeedbackRemoval()
//		* ziggeoPUIMessenger()
//		* ziggeoPUISDKInit()
// 3. Templates Editor
//		* ziggeoGetEditor()
//		3.1. Templates
//			* ziggeoPUITemplatesManageInit()
//			* ziggeoPUIManageTemplate()
//			* ziggeoPUITemplatesManage()
//			* ziggeoPUITemplatesChange()
//			* ziggeoPUITemplatesTurnIntoNew()
//			* ziggeoTemplatesBase()
//		3.2. Parameters
//			* ziggeoPUIParametersQuickAddInit()
//			* ziggeoPUIParametersShownInit()
//			* ziggeoPUIParametersQuickAdd()
//			* ziggeoPUIParametersAddSimple()
//			* ziggeoPUIParametersShownToggle()
//			* ziggeoParameterPresent()
// 4. Integrations
//		4.1 Integrations Tab
//			* ziggeoPUIIntegrationStatus()
// 5. WP Editor
//		* ziggeoSetupNewWPToolbar()
//		* ziggeoSetupOverlayRecorder()
//		* ziggeoSetupOverlayTemplates()
// 6. Notifications
//		* ziggeoPUINotificationsInit()
//		* ziggeoNotificationManage()
// 7. Videos Page
//		* ziggeoPUIVideosInit()
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
//		* ziggeoPUIVideosFilterReset()
//		* ziggeoPUIVideosPageCreateNavigation()
//		* ziggeoPUIVideosPageSwitch()
//		* ziggeoPUIVideosPageCounter()
// 8. Addons page
//		* ziggeoPUIAddonsInit()
//		* ziggeoPUIAddonsSwitch()
// 9. SDK Page
//		* 

/////////////////////////////////////////////////
// 1. DASHBOARD                                //
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




/////////////////////////////////////////////////
// 2. HELPER FUNCTIONS                         //
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

		//Lets do this only if we are in the admin panel of our plugin
		if(document.getElementById('ziggeo-tab_id_general')) {
			ziggeoPUIParametersQuickAddInit();
			ziggeoPUITemplatesManageInit();
			ziggeoPUIParametersShownInit();
			//lets always do this last
			ziggeoPUIHooksInit();

			//Lets check if we have any integrations and show message if not:
			if(document.getElementsByClassName('ziggeo_integrations_list')[0].children.length == 0) {
				var _li = document.createElement('li');
				_li.innerText = 'Search for "Ziggeo" in Wordpress plugins repository to find other plugins that provide you integrations (bridges) between Ziggeo and other plugins.';
				document.getElementsByClassName('ziggeo_integrations_list')[0].appendChild(_li);
			}

		}

		//Happens only if we are on notifications pages
		if(document.getElementById('ziggeo-notifications')) {
			ziggeoPUINotificationsInit();
		}

		//Happens only if on videos page
		if(document.getElementById('ziggeo-videos-filter')) {
			ziggeoPUIVideosInit();

			//Clear videos counter at this time
			ziggeoAjax({
				operation: 'video_verified_seen'
			});
		}

		if(document.getElementById('ziggeo-addons-nav')) {
			ziggeoPUIAddonsInit();
		}

		if(document.getElementById('ziggeo-sdk-pages')) {
			ziggeoPUISDKInit();
		}
	});

	//All the hooks that we want to set up right away as page is loaded are added here, which is better than leaving hooks "out in the open", as this makes them fire when everything is ready
	function ziggeoPUIHooksInit() {

		//Hooks to change the template editor in admin dashboard [START]
		var _hooks = ['dashboard_parameters_editor-adv', 'dashboard_parameters_editor-easy'];

		//a check in case the class is not defined (can happen in instances where header is not outputted by WP like customize page).
		if(typeof ZiggeoWP === 'undefined') {
			return false;
		}

		//Set the parameters shown based on currently selected value (on page load)
		var _sel = document.getElementById('ziggeo_shorttags_list');
		ziggeoTemplatesEditorEasyParametersCheck( _sel.options[_sel.selectedIndex].value.replace('[ziggeo', ''));

		ZiggeoWP.hooks.set(_hooks, 'ziggeo-template-change', function(data) {
			switch(data.template) {
				case '[ziggeoplayer': {}
				case '[ziggeorecorder': {}
				case '[ziggeorerecorder': {}
				case '[ziggeouploader': {
					data.editor.value = data.template + ' ' + data.editor.value.substr(data.editor.value.indexOf(' ')+1);

					if(data.editor_type == 'advanced') {
						document.getElementById('ziggeo-embedding-parameters-adv').style.display = 'block';
					}
					else {
						document.getElementById('ziggeo-embedding-parameters-easy').style.display = 'block';
					}

					ziggeoTemplatesEditorEasyParametersCheck(data.template.replace('[ziggeo', ''));

					break;
				}
				default: {
					
					if(data.editor_type == 'advanced') {
						document.getElementById('ziggeo-embedding-parameters-adv').style.display = 'none';
					}
					else {
						document.getElementById('ziggeo-embedding-parameters-easy').style.display = 'none';
					}
				}
			}
		});

		//Hook when simple templates editor is activated
		ZiggeoWP.hooks.set('dashboard_template_editor_simple_shown', 'ziggeo-template-editing', function(data) {
			document.getElementById('ziggeo-embedding-parameters-adv').style.display = 'none';
			ziggeoGetEditor().style.display = 'none';

			var embedding_params = document.getElementById('ziggeo-embedding-parameters-easy');

			if(data.template_base == '[ziggeoplayer' ||
				data.template_base == '[ziggeorecorder' ||
				data.template_base == '[ziggeorerecorder' ||
				data.template_base == '[ziggeouploader') {
				embedding_params.style.display = 'block';

				ziggeoTemplatesEditorEasyParametersCheck(data.template_base.replace('[ziggeo', ''));
			}
			else {
				embedding_params.style.display = 'none';
			}
		});

		//Hook when advanced templates editor is activated
		ZiggeoWP.hooks.set('dashboard_template_editor_advanced_shown', 'ziggeo-template-editing', function(data) {
			document.getElementById('ziggeo-embedding-parameters-easy').style.display = 'none';
			ziggeoGetEditor().style.display = 'block';

			var embedding_params = document.getElementById('ziggeo-embedding-parameters-adv');

			if(data.template_base == '[ziggeoplayer' ||
				data.template_base == '[ziggeorecorder' ||
				data.template_base == '[ziggeorerecorder' ||
				data.template_base == '[ziggeouploader') {
				embedding_params.style.display = 'block';
			}
			else {
				embedding_params.style.display = 'none';
			}
		});

		//Hook for fom being saved (when in templates editor)
		//Grab all of the values from the simple editor fields and put them into the editor to make it possible to grab them
		ZiggeoWP.hooks.set('ziggeo_editor_save_templates', 'ziggeo_save_defaults', function(data) {
			var editor = ziggeoGetEditor();

			if(editor.value === '') {
				var data_string = ziggeoTemplatesBase() + ' ';
			}
			else {
				var data_string = editor.value + ' ';
			}

			//Grab all available easy editor screens (core + videowalls + your own?)
			var easy_params_holder = document.querySelectorAll('#ziggeo_parameters_simple_section [id*="-parameters-easy"]');

			for(i = 0, l = easy_params_holder.length; i < l; i++) {

				//We only really do this for the section that is not hidden
				if(easy_params_holder[i].style.display !== 'none') {
					var fields = easy_params_holder[i].querySelectorAll('.ziggeo-field');

					for(j = 0, c = fields.length; j < c; j++) {
						//Param details
						var param_name = fields[j].children[0].innerText;
						var param_type = fields[j].children[1].children[0].getAttribute('type');
						var param_value = '';

						if(param_type === 'number' || param_type === 'text') {

							if(fields[j].children[1].children[0].value !== '') {
								param_value = fields[j].children[1].children[0].value;
							}
						}
						else if(param_type === 'checkbox') {
							if(fields[j].children[1].children[0].checked) {
								param_value = true;
							}
							else {
								param_value = false;
							}
						}
						else if(param_type === 'enum') {
							param_value = fields[j].children[1].children[0].options[fields[j].children[1].children[0].selectedIndex].value;
						}

						//Check if this value is already within the editor
						//false means we need to add it
						if(ziggeoParameterPresent(editor.value, param_name) === false) {
							//Place it all into the string that will be remembered
							if(param_value !== '') {
								data_string += param_name + "='" + param_value + "' ";
							}
						}

					}

					//Add to editor
					editor.value = data_string;
				}
			}

		});
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

	// Function to help us know if the given value is empty or not
	function ziggeoPValidateEmpty(value) {
		if(value === '') {
			return true;
		}

		return false;
	}




/////////////////////////////////////////////////
// 3. TEMPLATES EDITOR FUNCTIONS               //
/////////////////////////////////////////////////

	//Returns the reference to the templates editor
	function ziggeoGetEditor() {
		//Using this so that we do not need to remember the ID and can change it in one place
		return document.getElementById('ziggeo_templates_editor');
	}

	//returns Ziggeo template ID that was set or empty string if it was not set at all
	function ziggeoGetTemplateID() {
		var _t = document.getElementById('ziggeo_templates_id');

		if(_t) {
			return _t.value.replace(/\ /g, '_');
		}

		return '';
	}

	//Helps us set the template ID properly
	function ziggeoSetTemplateID(new_id) {
		var _t = document.getElementById('ziggeo_templates_id');

		if(_t) {
			_t.value = new_id.replace(/\ /g, '_');
			return true;
		}

		return false;
	}

	function ziggeoTemplateGetTemplateObject() {
		return (ZiggeoWP.template_object) ? ZiggeoWP.template_object : null;
	}

	function ziggeoTemplateSetTemplateObject(code) {
		//Parse the code

		//code == "[ziggeovideowall wall_design='mosaic_grid' videos_to_show='' show show_videos='all']"
		var params_groups = code.split(' ');
		var params = {};

		var id = params_groups[0];

		for(i = 1, l = params_groups.length; i < l; i++) {
			var tmp = params_groups[i].split('=');

			if(tmp[1]) {
				params[tmp[0]] = tmp[0];
			}
			else {
				params[tmp[0]] = 'true';
			}
		}

		//save the object
		ZiggeoWP.template_object = {id: id, params: params};
	}

	function ziggeoTemplatesEditorSetText(msg) {
		var editor = ziggeoGetEditor();

		if(msg === null || typeof msg === 'undefined') {
			msg = '';
		}

		editor.value = msg;
	}

	//Selects the given parameter in the textarea editor. If the always_highlight is true, it will either select value or the parameter if it is bool, otherwise it will only select the value when possible
	function ziggeoTemplatesEditorSelectText(parameter, always_highlight) {
		var editor = ziggeoGetEditor();
		var location = ziggeoParameterPresent(editor.value, parameter);

		//If the parameter is not there, just exit
		if(location === false) {
			return false;
		}

		var end = editor.value.indexOf(' ', location+2);

		if(end == -1) {
			end = editor.value.length;
		}

		//To handle the cases where we have value
		var tmp_val = editor.value.indexOf('=', location+2)+1;

		if(tmp_val <= end) {
			location = tmp_val;
		}

		editor.focus();
		editor.setSelectionRange(location , end);
	}

	//Allows us to change the value of a given parameter
	function ziggeoTemplateChangeParam(template, param, new_value, type) {
		//We do not need to recreate the code, just remove param
		template = ziggeoTemplateRemoveParam(template, param);

		//did it have ]?
		var was_finalized = false;

		//Now lets add the parameter in
		//This way even if it was not present, we add it which is desired
		if(template.indexOf(']') > -1) {
			template = template.replace(']', '');
			was_finalized = true;
		}

		//Just so that we do not make it have a large amount of whitespace
		template = template.trim();

		if(type === 'bool' && ( new_value === '' || new_value === 'on' || new_value === true)) {
			template += ' ' + param;
		}
		else if(type === 'string' || type === 'array' || type === 'enum') {
			template += ' ' + param + "='" + new_value + "'";
		}
		else {//it is int
			template += ' ' + param + '=' + new_value;
		}

		//Do we need to add back the ]?
		if(was_finalized) {
			template += ']';
		}

		return template;
	}

	//find and remove the parameter in the code we got and return the final template code
	function ziggeoTemplateRemoveParam(template, param) {

		var location = ziggeoParameterPresent(template, param);

		//If the parameter is not found, we can just return the template code
		if(location === false) {
			return template;
		}

		var end_of_param = template.indexOf(' ', location+2);

		//In case when the parameter is last in string (no space after)
		if(end_of_param == -1) {
			end_of_param = template.length;

			if(template.indexOf(']') > -1) {
				end_of_param = template.indexOf(']');
			}
		}

		//At this point it means that we do have the parameter present
		template = template.substr(0, location) + template.substr(end_of_param);

		return template;
	}

	function ziggeoTemplatesEditorEasyParametersCheck(base) {

		var embedding_params = document.getElementById('ziggeo-embedding-parameters-easy');

		//Change what options are shown based on the template_base
		var _fields = embedding_params.querySelectorAll('.ziggeo-field');

		for(i = 0, c = _fields.length; i < c; i++) {
			if(_fields[i].getAttribute('data-type').indexOf(' ' + base) > -1) {
				_fields[i].style.display = '';
			}
			else {
				_fields[i].style.display = 'none';
			}
		}
	}

	// 3.1 Templates
	/////////////////

	//Attaching events for templates management
	function ziggeoPUITemplatesManageInit() {
		//lets get the elements..
		var managingElements = document.getElementsByClassName('ziggeo-manage_list');

		if(managingElements.length === 0) {
			return false;
		}

		for(i = 0; i < managingElements.length; i++) {

			//We can capture both and process both now, since templates will have both every time..
			var meDel = document.getElementsByClassName('delete');
			var meEdit = document.getElementsByClassName('edit');

			for(j = 0; j < meDel.length; j++)
			{
				if(document.addEventListener) {
					meDel[j].addEventListener( 'click',  ziggeoPUITemplatesManage, false );
					meEdit[j].addEventListener( 'click',  ziggeoPUITemplatesManage, false );
				}
				else { //older IE..
					meDel[j].attachEvent( 'onclick', ziggeoPUITemplatesManage);
					meEdit[j].attachEvent( 'onclick', ziggeoPUITemplatesManage );
				}
			}
		}

		//add event to the button for saving templates
		document.getElementById('ziggeo_templates_update').addEventListener('click', ziggeoPUIManageTemplate, false );
	}

	//function to report over AJAX on what we should do to/with templates
	function ziggeoPUIManageTemplate(operation, data, ref) {
		var obj = {};

		//Operations are defined for some actions like delete template
		if(typeof operation !== 'undefined' && operation !== null && typeof data !== 'undefined') {
			obj.operation = encodeURI(operation);
			obj.template_id = encodeURI(data.id);
			obj.template_code = encodeURI(data.code);
			obj.manager = encodeURI(data.manager);
		}
		else {
			//We are saving the template
			ZiggeoWP.hooks.fire('ziggeo_editor_save_templates', {});

			obj.operation = 'settings_manage_template';
			obj.template_id = encodeURI(ziggeoGetTemplateID());
			obj.template_code = encodeURI(ziggeoGetEditor().value);
			obj.manager = encodeURI(document.getElementById('ziggeo_templates_manager').value);
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
					var list = document.getElementsByClassName('ziggeo-manage_list')[0];
					var item = document.createElement('li');
					item.textContent = e.template_id + ' ';

					/*
					* Had to be removed for some reason
					var _sdel = document.createElement('span');
					_sdel.className = 'delete';
					_sdel.textContent = 'x';

					var _sedit = document.createElement('span');
					_sedit.className = 'edit';
					_sedit.setAttribute('data-template', obj.template_code);
					_sedit.textContent = 'edit';

					item.appendChild(_sdel);
					item.appendChild(_sedit);
					*/

					list.appendChild(item);
				}
				else if(e.message === 'removed') {
					ref.parentElement.removeChild(ref);
				}
			}
			else {
				ziggeoDevReport('Managing templates: ' + e, 'error');
				ziggeoPUIMessenger().push('Something unexpected happened', 'error');
			}
		});

		//Reset the screen
		document.getElementById('ziggeo_templates_manager').value = '';
		ziggeoSetTemplateID('');
		ziggeoTemplatesEditorSetText( ziggeoTemplatesBase() );

	}

	//Function to manage templates. Holds both edit and delete functionality
	// > event
	function ziggeoPUITemplatesManage(event) {

		//Grabbing the option reference that will help us pass the value over to server backend.
		var elem = document.getElementById('ziggeo_templates_manager');

		var selected = event.currentTarget;

		//Get the text..
		var txt = selected.parentNode.innerText;

		//Since it holds new line breaks between the elements text lets remove them - if any..
		txt = txt.replace(/\n/g, '');

		//If we do have the elements text next to the name, lets just remove it..
		if( txt.indexOf('xedit') > 0 ) {
			txt = txt.substr(0, (txt.length - 5) );
		}

		//lets get what we should do with a fallback to edit..

		//delete a template
		if(selected.className === 'delete') {

			//Hook to notify that removing of the existing template is likely to occur
			ZiggeoWP.hooks.fire('dashboard_templates_pre_removal', {});

			if(confirm('Are you sure that you want to remove template? It is not possible to undo the same action!')) {
				//Lets set the template manager with the value that we want to remove
				elem.value = txt;

				//Since it is removal, we want to remove all data in this field..
				ziggeoGetEditor().value = "";
				ziggeoSetTemplateID('');

				//Just about to remove the template
				ZiggeoWP.hooks.fire('dashboard_templates_post_removal', {});

				//submit the form
				ziggeoPUIManageTemplate(null, { id:txt, 'code':'', 'manager': ''}, selected.parentNode);
				//document.forms[0].submit();
			}
		}
		//edit template
		else {
			elem.value = txt;

			//Add value to template ID field
			ziggeoSetTemplateID(txt);

			//Add value to templates editor field
			var editor = ziggeoGetEditor();

			//The code to work with in the background
			ziggeoTemplateSetTemplateObject(selected.getAttribute('data-template').replace(/\\'/g, "'"));

			//The code that is shown
			// * to help with apostrophes in the custom text fields
			editor.value = selected.getAttribute('data-template').replace(/\\'/g,"||").replace(/'/g,'&apos;').replace(/\|\|/g, "'")

			var template_base = editor.value.substr(0, editor.value.indexOf(' ') );

			var templates_select = document.getElementById('ziggeo_shorttags_list');

			//set up the dropdown to show the right value
			if(templates_select.value !== '[ziggeo ' && template_base !== '') {
				//the following would work, however in some cases it will not (when "[ziggeo" is used as base)
				document.getElementById('ziggeo_shorttags_list').value = template_base;
			}
			else {
				// so instead we set it as player by default and go from there
				templates_select.value = '[ziggeoplayer';
			}

			for(i = 0, c = templates_select.options.length; i < c; i++) {
				if(templates_select.options[i].value === template_base) {
					//at this point it is safe
					templates_select.value = template_base;
				}
			}

			//Hook to notify that editing the existing template is about to be started
			ZiggeoWP.hooks.fire('dashboard_templates_editing', { editor: editor, template_base: template_base, template_code: txt });

			//Turn into new button should now be shown..
			document.getElementById('ziggeo_templates_turn_to_new').style.display = 'inline-block';

			//for now every time we want to edit the template we go into advanced editor..
			ziggeoPUIParametersShownToggle('advanced');

			//Set focus on editor, as it is the most likely thing that would be edited.
			editor.focus();

			//also lets scroll a bit down to it if needed (must be after focus, or setting focus will "break" the scrolling)
			scrollTo({
				top: Math.abs(document.body.getBoundingClientRect().top) + editor.parentNode.getBoundingClientRect().top,
				behavior: "smooth"
			});
		}
	}

	//Function to change the shortcode in the templates editor to the selected one and to show the parameters that can be applied to each
	// > sel param accepts the reference to <select> input
	function ziggeoPUITemplatesChange(sel) {
		//lets get the selected value
		var selected = sel.options[sel.selectedIndex].value;
		
		//Lets grab the currently set value if any from the templates editor
		var editor = ziggeoGetEditor();

		var hook_values = {
			template: selected, //will it be player ([ziggeoplayer), recorder..
			editor: editor,
			editor_type: ''
		};

		//this helps pinpoint the specific section we are working with
		if(document.getElementById('ziggeo_parameters_advanced').className === 'active') {
			var editor_suffix = '-adv';
			hook_values.editor_type = 'advanced';
		}
		else {
			var editor_suffix = '-easy';
			hook_values.editor_type = 'easy';
		}

		ZiggeoWP.hooks.fire('dashboard_parameters_editor' + editor_suffix, hook_values);
	}

	//We set the template as a new template, instead of it being edited - allowing people to click on edit to create a new template based on the old one.. ;)
	function ziggeoPUITemplatesTurnIntoNew() {
		//Clear the value indicating what was changed
		document.getElementById('ziggeo_templates_manager').value = '';
		//hide the button to turn it into a new template..
		document.getElementById('ziggeo_templates_turn_to_new').style.display = 'none';
	}

	//Gets the parameter base that we should use in editor, or returns the one to use
	//>> specific can be 'player' or 'recorder' which then passes back the template base
	// that you should use to start your template with
	// When no paramateter is passed it will retrieve the editor template and return that
	function ziggeoTemplatesBase(specific) {
		if(specific) {
			//@here, would like to do this as hooks so you can add your own
			// until specifically asked for will leave as is
		}
		else {
			var editor_template = document.getElementById('ziggeo_shorttags_list');
			return editor_template.options[editor_template.selectedIndex].value;
		}
	}


	// 3.2. Parameters
	//////////////////

	//Attaching events to the parameters list..
	function ziggeoPUIParametersQuickAddInit() {
		//lets get the elements..
		var elementsHolders = document.getElementsByClassName('ziggeo-params');

		for(i = 0; i < elementsHolders.length; i++) {
			
			var le = document.getElementsByTagName('DT');

			for(j = 0; j < le.length; j++)
			{
				(document.addEventListener) ? (
					//true
					le[j].addEventListener( 'click',  ziggeoPUIParametersQuickAdd, false ) ) : (
					//false - for older IE only..
					le[j].attachEvent( 'onclick', ziggeoPUIParametersQuickAdd ) );
			}
		}
	}

	//attach the event to the toggle button..
	function ziggeoPUIParametersShownInit() {
		var elm = document.getElementById('ziggeo_parameters_advanced');

		if(elm){
			if(document.addEventListener) {
				elm.addEventListener( 'click',  ziggeoPUIParametersShownToggle, false );
			}
			else {
				elm.attachEvent( 'onclick',  ziggeoPUIParametersShownToggle );
			}
		}
	}

	//Function to add parameters on click..should allow much easier customer experience
	// > event
	function ziggeoPUIParametersQuickAdd(event, is_simple) {
		//Reference to textarea
		var editor = ziggeoGetEditor();

		//Reference to clicked attribute
		var current = event.currentTarget;

		//Let's get the parameter title and value
		if(is_simple) {
			var parameter_title = current.parentElement.parentElement.children[0].innerHTML;
			var parameter_value = current.value;
		}
		else {
			var parameter_title = current.innerHTML;
			var parameter_value = '';
		}

		//to know what we are working with..
		// can be `string, array` (both as strings), integer, float or bool
		var parameter_type = current.getAttribute('data-equal');

		//At this point we could just check the object, instead of working with the code...
		var template_obj = ziggeoTemplateGetTemplateObject();

		if(template_obj) {
			//Do we already have this parameter?
			if(template_obj[parameter_title]) {
				if(template_obj[parameter_title] !== parameter_value) {
					//A change has been made to the value of the parameter
					template_obj[parameter_title] = parameter_value;
				}
			}
			else {
				//This parameter is being added for the first time
				template_obj[parameter_title] = parameter_value;
			}
		}
		else {
			//The template was not created so far
			template_obj = {};
			template_obj[parameter_title] = parameter_value;
		}


		//to support the simple setup
		if(is_simple) {
			if(parameter_type === 'enum') {
				//if enum type, our value actually needs to be captured from the selected option..
				parameter_value = current.options[current.selectedIndex].value;
				//effectively going further, this should be seen as array..
				parameter_type = 'array';
			}
			else if(parameter_type === 'bool') {
				parameter_value = current.checked;
			}
		}

		if(parameter_type === 'string' || parameter_type === 'array') {

			//We should clean up the string..
			parameter_value = ziggeoCleanTextValues(parameter_value);
		}


		//In case it is advanced, we look if we should select or add it..
		if(!is_simple) {
			//in advanced editor we also select the right section
			if(ziggeoTemplatesEditorSelectText(parameter_title, true) !== false) {
				//If we made the selection, it existed so we just return from here
				return;
			}
		}

		//Lets add the parameter
		editor.value = ziggeoTemplateChangeParam(editor.value,
													parameter_title,
													parameter_value,
													parameter_type
		);

		if(!is_simple) {
			ziggeoTemplatesEditorSelectText(parameter_title, true);
		}

		//Save the template object
		ziggeoTemplateSetTemplateObject(editor.value);

		return true;
	}

	//Handles the adding of the parameters from the simple editor:
	function ziggeoPUIParametersAddSimple(event) {
		//We could call this function directly. Leaving like this for now in case we need to add some special code to be
		// run before it or after.. * such as cleanup code
		ziggeoPUIParametersQuickAdd(event, true);
	}

	//switches between simple paramaters and advanced ones
	function ziggeoPUIParametersShownToggle(force_specific) {
		var elm = document.getElementById('ziggeo_parameters_advanced');
		var section_adv = document.getElementById('ziggeo_parameters_advanced_section');
		var section_simp = document.getElementById('ziggeo_parameters_simple_section');
		var main_template = document.getElementById('ziggeo_shorttags_list');
		var template_base = main_template.options[main_template.selectedIndex].value;

		if((elm.className.indexOf('active') > -1 || force_specific === 'simple') && force_specific !== 'advanced') {
			elm.className = '';
			elm.firstChild.innerText = "Easy Setup";
			section_adv.style.display = 'none';
			section_simp.style.display = 'block';

			ZiggeoWP.hooks.fire('dashboard_template_editor_simple_shown', { template_base: template_base });

			//hide the list of parameters
			document.getElementById('ziggeo_templates_types').style.display = 'none';
		}
		else {
			elm.className = 'active';
			elm.firstChild.innerText = "Advanced View";
			section_adv.style.display = 'block';
			section_simp.style.display = 'none';

			ZiggeoWP.hooks.fire('dashboard_template_editor_advanced_shown', { template_base: template_base });

			//hide the list of parameters
			document.getElementById('ziggeo_templates_types').style.display = 'block';
		}

		//Now lets make sure that we can switch back and forth between the simple and advanced editing, while being aware
		// of the main template in use..

		//@ADD - make this work so that parameters possibly go back and forth (if the same parameters exist in easy and advanced editor)

	}

	//Checks if the parameter is present or not. If it is, it returns the position otherwise false 
	function ziggeoParameterPresent(code, parameter) {
		var location = false;

		//Most paramters has the equal to value so this is first check
		location = code.indexOf(' ' + parameter + '=');

			//Did we find it?
			if(location > -1) {
				return location+1;
			}

		//common for boolean true values
		location = code.indexOf(' ' + parameter + ' ');

			//Did we find it?
			if(location > -1) {
				return location+1;
			}

		//indicator of the parameter being at the very end of the string
		location = code.indexOf(' ' + parameter + ']');

			//Did we find it?
			if(location > -1) {
				return location+1;
			}

		//The parameter is not part of the code checked
		return false;
	}




/////////////////////////////////////////////////
// 4. INTEGRATIONS                             //
/////////////////////////////////////////////////


	// 4.1 Integrations tab
	///////////////////////

	//set the integration to disable
	function ziggeoPUIIntegrationStatus(strPlugin, strStatus) {
		var toChange = document.getElementById('ziggeo_integration_change');

		if(toChange) {
			toChange.value = strPlugin+'='+strStatus;
		}
	}




/////////////////////////////////////////////////
// 5. WP EDITOR                                //
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
// 6. NOTIFICATIONS                            //
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
// 7. VIDEOS PAGE                              //
/////////////////////////////////////////////////

	function ziggeoPUIVideosInit() {
		var _placeholder = document.getElementById('ziggeo-videos-filter');
		var _moderation_filter = _placeholder.querySelector('.moderation');
		var _tags_filter = _placeholder.querySelector('.tags');
		var _sort_filter = _placeholder.querySelector('.sort');
		var _apply = _placeholder.querySelector('.ziggeo-ctrl-btn');

		//Disable on default
		_apply.className += ' disabled';
		ZiggeoWP.video_list_current = 1;

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

		//Allows getting ready or those in processing. Makes no sense to use here, so skipping
		//query_obj.states = 'ready';

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
										ziggeoDevReport(err, 'error');
									},
									null);
		}
		else if(_moderation_filter.value == 'pending') {
			//Go through the list of videos and then list them if not approved
			ziggeoAPIGetVideosData(query_obj,
									'ziggeoPUIVideosHasVideosPending',
									'ziggeoPUIVideosNoVideos',
									function(err) {
										ziggeoDevReport(err, 'error');
									},
									null);
		}
		else if(_moderation_filter.value == 'rejected') {
			//Go through the list of videos and then list them if not approved
			ziggeoAPIGetVideosData(query_obj,
									'ziggeoPUIVideosHasVideosRejected',
									'ziggeoPUIVideosNoVideos',
									function(err) {
										ziggeoDevReport(err, 'error');
									},
									null);
		}
		else {
			ziggeoAPIGetVideosData(query_obj,
									'ziggeoPUIVideosHasVideos',
									'ziggeoPUIVideosNoVideos',
									function(err) {
										ziggeoDevReport(err, 'error');
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
	}

	//Used for cases where videos have been found and we need to create the list
	function ziggeoPUIVideosHasVideos(videos, clear) {
		var _placeholder = document.getElementById('ziggeo-videos');

		ziggeoPUIVideosMessage(null, null, 'hide');

		//We will not be making pages in the real sense, however will not need to use the API if we already have the "page" in the cache
		if(typeof ZiggeoWP.video_list === 'undefined' || _placeholder.getAttribute('_clear')) {
			ZiggeoWP.video_list = [];
		}

		if(clear !== false) {
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
		if(page_num * 10 > ZiggeoWP.video_list.length) {
			ziggeoPUIVideosFilter(false, page_num * 10);
		}
		else {
			page_num--;
			ziggeoPUIVideosHasVideos(ZiggeoWP.video_list.slice(page_num*10, page_num*10+10), false);
		}
	}

	//Function to update the count of videos found and shown
	function ziggeoPUIVideosPageCounter(query_obj) {
		//Requires the PHP SDK to be added. Happy to add if it is asked for it
	}




/////////////////////////////////////////////////
// 8. ADDONS PAGE                              //
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
// 9. SDK PAGE                                 //
/////////////////////////////////////////////////

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