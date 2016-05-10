function ziggeo_onboard(name, email, success, error) {
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
			success(result.application.token);
		},
		error: function (err) {
			var errors = "";
			for (var key in err.responseJSON)
				errors += err.responseJSON[key];				
			error(errors);
		}
	});
}

//Changes tabs in WordPress admin panel
// > tab param is a text representation of the tab that we should show
function ziggeo_changeTab(tab)
{
	//lets get the currently selected tab so that we can remove .current from the same
	var selectedTab = document.getElementsByClassName('ziggeo-tabName selected');
	selectedTab[0].className = 'ziggeo-tabName';

	//Now we should hide all of the frames
	var shownFrames = document.getElementsByClassName('ziggeo-frame');

	for(i = 0; i < shownFrames.length; i++)
	{
		shownFrames[i].style.display = 'none';
	}

	//Now lets set the right tab to be shown as selected
	switch(tab)
	{
		case 'general': {
			document.getElementById('ziggeo-tab_id_general').className = 'ziggeo-tabName selected';
			document.getElementById('ziggeo-tab_general').style.display = 'block';
			break;
		}
		case 'templates': {
			document.getElementById('ziggeo-tab_id_templates').className = 'ziggeo-tabName selected';
			document.getElementById('ziggeo-tab_templates').style.display = 'block';
			break;
		}
	}
}

//Function to change the shortcode in the templates editor to the selected one and to show the parameters that can be applied to each
// > sel param accepts the reference to <select> input
function ziggeo_templates_change(sel) {
	//lets get the selected value
	var selected = sel.options[sel.selectedIndex].value;
	
	//Lets grab the currently set value if any from the templates editor
	var editor = document.getElementById('ziggeo_templates_editor');

	editor.value = selected + ' ' + editor.value.substr(editor.value.indexOf(' ')+1);
}

//Attaching events to the parameters list..
function ziggeo_parameters_quick_add_init() {
	//lets get the elements..
	var elementsHolders = document.getElementsByClassName('ziggeo-params');

	for(i = 0; i < elementsHolders.length; i++) {
		
		var le = document.getElementsByTagName('DT');

		for(j = 0; j < le.length; j++)
		{
			(document.addEventListener) ? (
				//true
				le[j].addEventListener( 'click',  ziggeo_parameters_quick_add, false ) ) : (
				//false - for older IE only..
				le[j].attachEvent( 'onclick', ziggeo_parameters_quick_add ) );
		}
	}
}

//Function to add parameters on click..should allow much easier customer experience
// > event
function ziggeo_parameters_quick_add(event) {
	//Reference to textarea
	var editor = document.getElementById('ziggeo_templates_editor');

	//Reference to clicked attribute
	var current = event.currentTarget;

	//Allows us to check the location of the parameter, if it is already added
	var attrLoc = editor.value.indexOf( ' ' + current.innerHTML + '=' );

	//Did we already add the same parameter? Might be good to check it out so that we do not add it again, just do custom cursor/caret positioning
	if( attrLoc > -1 )
	{
		//current.getAttribute('data-equal') we can use it to see if it is string or something else.

		var start = attrLoc + current.innerHTML.length + 2;
		var end = editor.value.indexOf( ' ', attrLoc+1); //adding plus1 since we were searching for whitespace as well (to know that attribut started and is not part of the other parameter..)

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

		if(current.getAttribute('data-equal').indexOf("'") > -1)
		{
			//we got a string based parameter, lets change the start and end..
			start++;
			end--;
		}

		editor.focus();
		editor.setSelectionRange( start , end );

		return false;
	}

	//If we are editing the template, we will have it closed every time.. so lets make sure that we do not add values after the closing bracket..
	if(editor.value[editor.value.length-1] === "]") {
		editor.value = editor.value.substr(0, editor.value.length-1) + ' ' + current.innerHTML + current.getAttribute('data-equal') + ']';
	}
	//If the space is not the last character, we should add it to avoid combined parameters
	else if(editor.value[editor.value.length-1] !== " ") {
		editor.value += ' ' + current.innerHTML + current.getAttribute('data-equal');
	}
	//If it is space, we are adding the parameters, or we were, no closing bracket in sight, so we can continue
	else {
		editor.value += current.innerHTML + current.getAttribute('data-equal');
	}

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

//Attaching events for templates management
function ziggeo_templates_manage_init() {
	//lets get the elements..
	var managingElements = document.getElementsByClassName('ziggeo-manage_list');

	for(i = 0; i < managingElements.length; i++) {

		//We can capture both and process both now, since templates will have both every time..
		var meDel = document.getElementsByClassName('delete');
		var meEdit = document.getElementsByClassName('edit');

		for(j = 0; j < meDel.length; j++)
		{

			(document.addEventListener) ? (
				//true
				meDel[j].addEventListener( 'click',  ziggeo_templates_manage, false ) ) : (
				//false - for older IE only..
				meDel[j].attachEvent( 'onclick', ziggeo_templates_manage) );

			(document.addEventListener) ? (
				//true
				meEdit[j].addEventListener( 'click',  ziggeo_templates_manage, false ) ) : (
				meEdit[j].attachEvent( 'onclick', ziggeo_templates_manage ) );
		}
	}
}

//Function to manage templates. Holds both edit and delete functionality
// > event
function ziggeo_templates_manage(event) {

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
		if(confirm('Are you sure that you want to remove template? It is not possible to undo the same action!')) {
			//Lets set the template manager with the value that we want to remove
			elem.value = txt;

			//Since it is removal, we want to remove all data in this field..
			document.getElementById('ziggeo_templates_editor'). value = "";
			document.getElementById('ziggeo_templates_id').value = "";

			//submit the form
			document.forms[0].submit();
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

		//Move the screen up, so that both are shown:
		if(document.location.hash !== "") {
			document.location.hash = "";
			document.location += 'ziggeo_editing';
		}
		else {
			document.location += '#ziggeo_editing';
		}

		//Turn into new button should now be shown..
		document.getElementById('ziggeo_templates_turn_to_new').style.display = 'inline-block';

		//turn off the beta option for now
		ziggeo_templates_turn_into_beta(true);

		//Set focus on editor, as it is the most likely thing that would be edited.
		editor.focus();
	}
}

//We set the template as a new template, instead of it being edited - allowing people to click on edit to create a new template based on the old one.. ;)
function ziggeo_templates_turn_into_new() {
	//Clear the value indicating what was changed
	document.getElementById('ziggeo_templates_manager').value = '';
	//hide the button to turn it into a new template..
	document.getElementById('ziggeo_templates_turn_to_new').style.display = 'none';
}

//Adds specific tag that makes the template use beta - this is not ziggeo tag, just WP plugin tag!
function ziggeo_templates_turn_into_beta(check) {
	//Adds/removes "_wpbeta_" to the editor..

	var betaOption = document.getElementById('ziggeo_turn_to_beta');
	var editor = document.getElementById('ziggeo_templates_editor');

	//called when some other action is initiated to check if the beta should be turned on or off
	if(check === true) {
		//beta is not enabled for specific template
		if( editor.value.indexOf('_wpbeta_') === -1 ) {
			betaOption.value = 0;
			betaOption.checked = false;
		}
		//beta is set for this template
		else {
			betaOption.value = 1;
			betaOption.checked = true;
		}
	}
	else {
		if(betaOption.value === 1 || betaOption.value === '1') {
			editor.value = editor.value.replace('_wpbeta_', '');
			betaOption.value = 0;
		}
		else {
			if( editor.value[editor.value.length-1] === "]" ) {
				//Trim to avoid there being a lot of space characters when we remove / add the option several times..
				editor.value = editor.value.substr(0, editor.value.length-1).trim() + ' _wpbeta_]';
			}
			else {
				editor.value = editor.value.trim() + ' _wpbeta_';
			}

			betaOption.value = 1;
		}		
	}

}

//Registering onload needed to have everything run smoothly.. :)
jQuery(document).ready( function() {
		ziggeo_parameters_quick_add_init();
		ziggeo_templates_manage_init();
		
	}
);

/*TODO:
+1. line 169 - make sure that only the text name is shown, the rest should be removed ("xedit")
+2. line 110 - Add another check for adding string based attributes when ] is the last character, otherwise it will not do the focus in the right location.
+3. Change the form.submit() to actual form submit, things should be finished in JS segment, lets finish up the php end of the same as well.
+4. Add the button to save the template as new, instead of changing the other one - for example if someone was to click on the edit, but then change their mind, we should do that, and we would be able to do that by clearing out the value in the ziggeo_templates_manager field.
5. If a parameter is being added that would change the template start tag, it would be good to correct / change the same - this would require some logic behind it so best to check with Oliver if we should do it and if so, when
+6. If parameter that is being added is already present, we should highlight the value of the same so that it can be adjusted
7. Next to the #6 we could gray out the currently used parameters..
*/