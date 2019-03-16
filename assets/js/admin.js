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
//		3.1. Templates
//			* ziggeoPUITemplatesManageInit()
//			* ziggeoPUIManageTemplate()
//			* ziggeoPUITemplatesManage()
//			* ziggeoPUITemplatesChange()
//			* ziggeoPUITemplatesTurnIntoNew()
//		3.2. Parameters
//			* ziggeoPUIParametersQuickAddInit()
//			* ziggeoPUIParametersShownInit()
//			* ziggeoPUIParametersQuickAdd()
//			* ziggeoPUIParametersAddSimple()
//			* ziggeoPUIParametersShownToggle()
// 4. Integrations
//		4.1 Integrations Tab
//			* ziggeoPUIIntegrationStatus()
//
// 5. Functions to be removed in next version
//		* ziggeoPUIIntegrationAJAX()
//		* ziggeo_integration_gravityforms_admin_select()
//




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
			ziggeoPUIParametersQuickAddInit();
			ziggeoPUITemplatesManageInit();
			ziggeoPUIParametersShownInit();
			//lets always do this last
			ziggeoPUIHooksInit();
	});

	//All the hooks that we want to set up right away as page is loaded are added here, which is better than leaving hooks "out in the open", as this makes them fire when everything is ready
	function ziggeoPUIHooksInit() {

		//Hooks to change the template editor in admin dashboard [START]
		var _hooks = ['dashboard_parameters_editor-adv', 'dashboard_parameters_editor-easy'];

		//a check in case the class is not defined (can happen in instances where header is not outputted by WP like customize page).
		if(typeof ZiggeoWP === 'undefined') {
			console.log('happened');
			return false;
		}

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

		//@REMOVE IN NEXT VERSION
		ZiggeoWP.hooks.set(_hooks, 'videowallsz-template-change', function(data) {
			switch(data.template) {
				//If it is video wall we want to show its parameters
				case '[ziggeovideowall': {
					
					var wallInfo = document.getElementById('ziggeo_videowall_info');
					wallInfo.style.display = 'inline-block';

					data.editor.value = data.template + ' ';

					if(data.editor_type == 'advanced') {
						document.getElementById('ziggeo-wall-parameters-adv').style.display = 'block';
					}
					else {
						document.getElementById('ziggeo-wall-parameters-easy').style.display = 'block';
					}

					break;
				}
				default: {
					
					var wallInfo = document.getElementById('ziggeo_videowall_info');
					wallInfo.style.display = 'none';

					if(data.editor_type == 'advanced') {
						document.getElementById('ziggeo-wall-parameters-adv').style.display = 'none';
					}
					else {
						document.getElementById('ziggeo-wall-parameters-easy').style.display = 'none';
					}
				}
			}
		});
		//Hooks to change the template editor in admin dashboard [END]

		//Hook to remove the videowall warning [START] * TO BE @REMOVED IN NEXT VERSION
		ZiggeoWP.hooks.set('dashboard_templates_editing', 'videowallsz-template-editing', function() {
			var wallInfo = document.getElementById('ziggeo_videowall_info');
			wallInfo.style.display = 'none';
		});
		//Hook to remove the videowall warning [END]

		//Hook when simple templates editor is activated
		ZiggeoWP.hooks.set('dashboard_template_editor_simple_shown', 'ziggeo-template-editing', function(data) {
			document.getElementById('ziggeo-embedding-parameters-adv').style.display = 'none';
			document.getElementById('ziggeo_templates_editor').style.display = 'none';

			var embedding_params = document.getElementById('ziggeo-embedding-parameters-easy');

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
		//@Remove in next version
		ZiggeoWP.hooks.set('dashboard_template_editor_simple_shown', 'videowallsz-template-editing', function(data) {
			document.getElementById('ziggeo-wall-parameters-adv').style.display = 'none';

			var wall_params = document.getElementById('ziggeo-wall-parameters-easy');

			if(data.template_base == '[ziggeovideowall') {
				wall_params.style.display = 'block';
			}
			else {
				wall_params.style.display = 'none';
			}
		});

		//Hook when advanced templates editor is activated
		ZiggeoWP.hooks.set('dashboard_template_editor_advanced_shown', 'ziggeo-template-editing', function(data) {
			document.getElementById('ziggeo-embedding-parameters-easy').style.display = 'none';
			document.getElementById('ziggeo_templates_editor').style.display = 'block';

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
		//@REMOVE in next version
		ZiggeoWP.hooks.set('dashboard_template_editor_advanced_shown', 'videowallsz-template-editing', function(data) {
			document.getElementById('ziggeo-wall-parameters-easy').style.display = 'none';

			var wall_params = document.getElementById('ziggeo-wall-parameters-adv');

			if(data.template_base == '[ziggeovideowall') {
				wall_params.style.display = 'block';
			}
			else {
				wall_params.style.display = 'none';
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
		document.forms[0].submit();
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

		if(typeof operation !== 'undefined' && operation !== null && typeof data !== 'undefined') {
			obj.operation = encodeURI(operation);
			obj.template_id = encodeURI(data.id);
			obj.template_code = encodeURI(data.code);
			obj.manager = encodeURI(data.manager);
		}
		else {
			obj.operation = 'settings_manage_template';
			obj.template_id = encodeURI(document.getElementById('ziggeo_templates_id').value);
			obj.template_code = encodeURI(document.getElementById('ziggeo_templates_editor').value);
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
				ziggeoPUIMessenger().push('Something unexpected happened', 'error');
			}
		});

		//@here. add nice notification box and clear out the fields
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
				document.getElementById('ziggeo_templates_editor').value = "";
				document.getElementById('ziggeo_templates_id').value = "";

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
			document.getElementById('ziggeo_templates_id').value = txt;

			//Add value to templates editor field
			var editor = document.getElementById('ziggeo_templates_editor');
			editor.value = selected.getAttribute('data-template');

			var template_base = editor.value.substr(0, editor.value.indexOf(' ') );

			var templates_select = document.getElementById('ziggeo_shorttags_list');

			//set up the dropdown to show the right value
			//the following would work, however in some cases it will not (when "[ziggeo" is used as base)
			//document.getElementById('ziggeo_shorttags_list').value = template_base;
			// so instead we set it as player by default and go from there
			templates_select.value = '[ziggeoplayer';

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
		var editor = document.getElementById('ziggeo_templates_editor');

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
		var editor = document.getElementById('ziggeo_templates_editor');

		//Reference to clicked attribute
		var current = event.currentTarget;

		//the parameter name (like width, or height, etc.)
		var parameter_title = current.innerHTML;

		//the value to add.. (always empty in advanced view, often filled out in simple setup)
		var parameter_value = '';

		//equal sign helpers
		var equal_start = '';
		var equal_end = '';

		//is simple or advanced editor used?
		//var is_simple = true; //only since it will be default..

		//to know what we are working with..
		// can be `string, array` (both as strings), integer, float or bool
		var parameter_type = current.getAttribute('data-equal');

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
		}

		if(parameter_type === 'string' || parameter_type === 'array') {
			equal_start = "='";
			equal_end = "'";

			//We should clean up the string..
			parameter_value = parameter_value.replace(/\'/g, '&apos;');
			parameter_value = parameter_value.replace(/\"/g, '&quot;');
		}
		else if(parameter_type === 'int' || parameter_type === 'float') {
			equal_start = "=";
		}

		//If we got boolean value we do not want to add it multiple times..
		if(parameter_type === 'bool') {
			//since we will be adding the value into the editor, we want to keep it clean..
			parameter_value = '';

			//Allows us to check the location of the parameter, if it is already added
			var parameter_location = editor.value.indexOf( ' ' + parameter_title + ' ' );

			//It was not added so far..
			if(parameter_location === -1) {
				//just in case it is not followed by the space, rather the closing bracket (if it was last entry)
				if(editor.value.indexOf( ' ' + parameter_title + ']' ) > -1) {
					parameter_location = editor.value.indexOf( ' ' + parameter_title + ']' );
				}
			}
		}
		//if the parameter is non bool value..
		else {
			//Allows us to check the location of the parameter, if it is already added
			var parameter_location = editor.value.indexOf( ' ' + parameter_title + '=' );
		}

		//Did we already add the same parameter? Might be good to check it out so that we do not add it again, just do
		// custom cursor/caret positioning
		if( parameter_location > -1 ) {
			if(parameter_type !== 'bool') {
				var start = parameter_location + parameter_title.length + 2;
			}
			else {
				var start = parameter_location;
			}

			//Are we working with a string attribute?
			if(parameter_type === 'string' || parameter_type === 'array') {
				//Yes, its a string, lets check it out then:
				var end = editor.value.indexOf( "' ", parameter_location+1); //adding plus 1 since we were searching for whitespace as
																	// well (to know that attribute started and is not part
																	// of the other parameter..)            
			}
			else {
				var end = editor.value.indexOf( ' ', parameter_location+1); //adding plus 1 since we were searching for whitespace as
																	// well (to know that attribute started and is not part
																	// of the other parameter..)            
			}

			//What if we had removed the quote?
			var quote_check = editor.value.indexOf( "'", parameter_location+1);

			//We got to the end of the editor value and did not find space, so this is the last parameter..
			if(end === -1) {
				//we need to check for ', ] and = to know the correct positioning at this time
				if(editor.value[editor.value.length-1] === "]") {
					end = editor.value.length-1;
				}
				else if(editor.value[editor.value.length-1] === "'") {
					end = editor.value.length;
				}
				else { //if(editor.value[editor.value.length-1] === "=") {
					end = editor.value.length;
				}
			}

			//are we adding string parameter?
			if(parameter_type === 'string' || parameter_type === 'array')
			{
				//we got a string based parameter, lets change the start and end..    
				if(quote_check < 0) { // we need to add quotes as well..
					start++;
					editor.value = editor.value.substr(0, start) + "'' " + editor.value.substr(end);
					end++;
				}
				else {
					start++;
					end = editor.value.indexOf( "'", quote_check+1);               
				}
			}

			//If it is the simple editor, we would replace the value
			if(is_simple) {
				//lets check if we got empty string as value. If we did, we can remove it if it is not bool
				if(parameter_type !== 'bool' && parameter_value === '') {
					start = start - (parameter_title.length + 1);

					//we need to remove 1 one count from the start for the string and array types (yet not for ints..)
					if(parameter_type === 'string' || parameter_type === 'array') {
						start--;
					}

					end++;
					editor.value = editor.value.substring(0, start) + editor.value.substring(end);
				}
				else {
					//replaces the value with the new value			
					editor.value = editor.value.substring(0, start) + parameter_value + editor.value.substring(end);
				}

				//just to remove any additional whitespaces that should not be there..still we want the last one there
				editor.value = editor.value.trim() + ' ';
			}
			// ...while if it is advanced editor we will highlight it instead so that it is easy to manually change it..
			else {
				editor.focus();
				editor.setSelectionRange( start , end );
		
			}

			return false;
		}
		//It was not added before, we need to add it now..
		else {
			//If we are editing the template, we will have it closed every time.. so lets make sure that we do not add
			// values after the closing bracket..
			if(editor.value[editor.value.length-1] === "]") {
				editor.value = editor.value.substr(0, editor.value.length-1) + ' ' + parameter_title +
								equal_start + parameter_value + equal_end + ' ]';
			}
			//If the space is not the last character (after which we are adding new text), we should add it to avoid
			// combined parameters
			else if(editor.value[editor.value.length-1] !== " ") {
				if(is_simple || parameter_type === 'bool') {
					editor.value += ' ' + parameter_title +
									equal_start + parameter_value + equal_end + ' ';
				}
				else {
					editor.value += ' ' + parameter_title +
									equal_start + parameter_value + equal_end;
				}
			}
			//If it is space that we are adding the parameter after we can continue
			else {
				if(is_simple) {
					editor.value += ' ' + parameter_title +
									equal_start + parameter_value + equal_end + ' ';
				}
				else {
					editor.value += ' ' + parameter_title +
									equal_start + parameter_value + equal_end;
				}
			}

			if(is_simple !== true) {
				//Set the focus to the editor
				editor.focus();

				//Lets find where we want the cursor / caret to be positioned at
				if(editor.value[editor.value.length-1] === "]") {
					//Did we just add a string based parameter?
					if(editor.value[editor.value.length-2] === "'") {
						editor.setSelectionRange(editor.value.length-2, editor.value.length-2);
					}
					else {
						editor.setSelectionRange(editor.value.length-1, editor.value.length-1);
					}
				}
				else if(editor.value[editor.value.length-1] === "'") {
					editor.setSelectionRange(editor.value.length-1, editor.value.length-1);
				}
				else {
					editor.setSelectionRange(editor.value.length, editor.value.length);
				}
			}
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
// 5. WP Editor                                //
/////////////////////////////////////////////////

	function ziggeoSetupNewWPToolbar() {

		var _t = document.getElementsByClassName('edit-post-header-toolbar');

		if(_t && _t.length > 0) {
			_t = _t[0];
		}
		else {
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
				//in case we want to force not to use the mce
				window.parent.send_to_editor('[ziggeoplayer]' + recorder.get('video') + '[/ziggeoplayer]');

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


//============================================================================
// 6. @REMOVE EVERYTHING THAT FOLLOWS IN THE NEXT VERSION OF PLUGIN
//============================================================================

//Function to call our AJAX handler and return a response as needed.
// > data object - {action: functionToCall, valueName: 'valueValue'}
function ziggeoPUIIntegrationAJAX(data, inner) {

	data.action = 'ziggeo_integrations';

	if(data.integration === 'GravityForms') {
		Setziggeo_template_settingSetting(data.template);
	}

	jQuery.post(ajaxurl, data, function(response) {
		inner.innerHTML = response;
		if(inner.getElementsByClassName('runMe'))
		{
			var el = inner.getElementsByClassName('runMe');
			var scr = document.createElement('script');

			for(i = 0, c = el.length; i < c; i++) {
				var tmp = el[i].innerHTML.toString().replace(/(\n)+/g, ' ').replace(/  +/g, ' ');
				scr.innerHTML += tmp;
			}
			document.body.appendChild(scr);
		}
	});
}

function ziggeo_integration_gravityforms_admin_select(sel) {

	ziggeoDevReport('`ziggeo_integration_gravityforms_admin_select` is going to be renamed in next version of plugin. Write to us on forum if you are using it to stay updated.');

	var field = document.getElementById('field_settings').parentElement.id;
	field = field.replace('field_', 'input_');

	//Lets grab the element that we will update..
	var elem = document.getElementById(field);

	//It should be ziggeo element, but just in case, better not to change the wrong element, than to do so
	if(elem)
	{
		elem.innerHTML = '<h3>Processing template</h3>'; //maybe replace this with graphical process bar

		var template = sel.options[sel.selectedIndex].value;

		//prep data to make ajax request
		var ajax = { integration: 'GravityForms', template: template };
		
		//calling ajax request to get the right data..
		ziggeoPUIIntegrationAJAX(ajax, elem);
	}
	else {
		ziggeoDevReport('seems that something went wrong here..');
	}
}
