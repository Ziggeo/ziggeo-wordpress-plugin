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
				_li.innerHTML = 'Search for "Ziggeo" in Wordpress plugins repository to find other plugins that provide you integrations (bridges) between Ziggeo and other plugins.';
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
						var param_name = fields[j].children[0].innerHTML;
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
			_message.innerHTML = sms;
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

	function ziggeoTemplateGetTempateObject() {
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
		if(location == -1) {
			return false;
		}

		var end = editor.value.indexOf(' ', location+2);

		if(end == -1) {
			end = editor.value.length;
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
			editor.value = selected.getAttribute('data-template').replace(/\\'/g, "'");

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

		//the parameter name (like width, or height, etc.)
		var parameter_title = current.innerHTML;

		//the value to add.. (always empty in advanced view, often filled out in simple setup)
		var parameter_value = '';

		//to know what we are working with..
		// can be `string, array` (both as strings), integer, float or bool
		var parameter_type = current.getAttribute('data-equal');

		//At this point we could just check the object, instead of working with the code...

		var template_obj = ziggeoTemplateGetTempateObject();

		if(template_obj) {
			//Do we already have this parameter?
			if(template_obj[parameter_title]) {
				if(template_obj[parameter_title] !== current.value) {
					//A change has been made to the value of the parameter
					template_obj[parameter_title] = current.value;
				}
			}
			else {
				//This parameter is being added for the first time
				template_obj[parameter_title] = current.value;
			}
		}
		else {
			//The template was not created so far
		}


		//to support the simple setup
		if(is_simple) {
			parameter_title = current.parentElement.parentElement.children[0].innerHTML;
			parameter_value = current.value;

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
			elm.firstChild.innerHTML = "Easy Setup";
			section_adv.style.display = 'none';
			section_simp.style.display = 'block';

			ZiggeoWP.hooks.fire('dashboard_template_editor_simple_shown', { template_base: template_base });

			//hide the list of parameters
			document.getElementById('ziggeo_templates_types').style.display = 'none';
		}
		else {
			elm.className = 'active';
			elm.firstChild.innerHTML = "Advanced View";
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
				return location;
			}

		//common for boolean true values
		location = code.indexOf(' ' + parameter + ' ');

			//Did we find it?
			if(location > -1) {
				return location;
			}

		//indicator of the parameter being at the very end of the string
		location = code.indexOf(' ' + parameter + ']');

			//Did we find it?
			if(location > -1) {
				return location;
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
	}

	function ziggeoNotificationManage(id, status) {

		var obj = {
			'operation':'notification_handler',
			'id': id,
			'status': status
		}

		ziggeoAjax(obj, function(result) {
			if(result === 'true') {
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
			else {
				
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
					popup: true,
					'popup-width': 640,
					'popup-height': 480
				}
			});


			_item.appendChild(_p_player);
			player.activate();

			var _length = document.createElement('div');
			_length.className = 'video_length';
			_length.innerHTML = videos[i].duration + 's';
			_p_player.appendChild(_length);

			_item.appendChild(_tools.cloneNode(true));

			//Add video info
			var _info = document.createElement('div');
			_info.className = 'info';

				var _token = document.createElement('div');
				_token.innerHTML = 'Token: ' +  videos[i].token;
				_info.appendChild(_token);

				var _tags = document.createElement('div');
				_tags.className = 'tags-field';
				if(videos[i].tags) {
					for(_i = 0, _c = videos[i].tags.length; _i < _c; _i++) {
						var _tag = document.createElement('span');
						_tag.className = 'tag';
						_tag.innerHTML = videos[i].tags[_i];
						_tag.title = 'Search for videos with "' + videos[i].tags[_i] + '"';
						_tags.appendChild(_tag);
					}
				}
				_info.appendChild(_tags);

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

		jQuery('#ziggeo-videos .info .tag').on('click', function(event){
			ziggeoVideosFindByTag(event.currentTarget.innerHTML);
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

		return _tools;
	}

	//Function to create the fields with video title and description
	function ziggeoPUIVideosCreateInfoSection() {
		var _v_info = document.createElement('div');
		_v_info.className = 'video_details';

		var _lbl_title = document.createElement('label');
		_lbl_title.innerHTML = 'Video Title: ';

		var _input_title = document.createElement('input');
		_input_title.className = 'video_title';
		_lbl_title.appendChild(_input_title);

		var _lbl_desc = document.createElement('label');
		_lbl_desc.innerHTML = 'Video Description: ';

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

			ZiggeoApi.Videos.update( ZiggeoWP.video_list[_video_ref].token, {
				'approved': true
			});

			element_ref.parentElement.parentElement.firstElementChild.className = 'player approved';
		}
	}

	//Function to reject the videos. This type of setup allows you to extend it! You will need to have your script loaded before ours.
	if(typeof ziggeoVideosReject !== 'function') {
		function ziggeoVideosReject(element_ref) {
			var _video_ref = element_ref.parentElement.parentElement.getAttribute('video_ref');

			ZiggeoApi.Videos.update(ZiggeoWP.video_list[_video_ref].token, {
				'approved': false
			});

			element_ref.parentElement.parentElement.firstElementChild.className = 'player rejected';
		}
	}

	//Function to reject the videos. This type of setup allows you to extend it! You will need to have your script loaded before ours.
	if(typeof ziggeoVideosRemove !== 'function') {
		function ziggeoVideosRemove(element_ref) {
			var _video_ref = element_ref.parentElement.parentElement.getAttribute('video_ref');

			if(ZiggeoWP.server_auth && ZiggeoWP.server_auth !== '') {

				ZiggeoApi.Videos.destroy(ZiggeoWP.video_list[_video_ref].token,
					{ 'server_auth': ZiggeoWP.server_auth } ).success( function() {
						element_ref.style.visibility = 'hidden';
					});
			}
			else {
				//This will not work through JS without server auth.., we can still try (would need the auth token for this)
				ZiggeoApi.Videos.destroy(ZiggeoWP.video_list[_video_ref].token);
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
		_nav.innerHTML = '';

		for(i = 0, c = Math.floor(ZiggeoWP.video_list.length / 10); i < c; i++) {
			var _btn = document.createElement('div');
			_btn.className = 'ziggeo-ctrl-btn';

			if(i+1 === page_num) {
				_btn.className += ' disabled';
			}

			_btn.innerHTML = i+1;

			_nav.appendChild(_btn);
		}

		ZiggeoWP.video_list_current = page_num;

		//Set the event
		jQuery('#ziggeo-videos-nav .ziggeo-ctrl-btn').on('click', function( event ) {
			ziggeoPUIVideosPageSwitch(event.currentTarget.innerHTML * 1);
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
