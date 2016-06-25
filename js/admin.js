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
    document.getElementById('ziggeo-tab_id_' + tab).className = 'ziggeo-tabName selected';
    document.getElementById('ziggeo-tab_' + tab).style.display = 'block';
}

//Function to change the shortcode in the templates editor to the selected one and to show the parameters that can be applied to each
// > sel param accepts the reference to <select> input
function ziggeo_templates_change(sel) {
    //lets get the selected value
    var selected = sel.options[sel.selectedIndex].value;
    
    //Lets grab the currently set value if any from the templates editor
    var editor = document.getElementById('ziggeo_templates_editor');

    //Lets grab the parameter holders
    var wallParams = document.getElementById('ziggeo-wall-parameters');
    var embeddingParams = document.getElementById('ziggeo-embedding-parameters');

    //videowall info
    var wallInfo = document.getElementById('ziggeo_videowall_info');
    wallInfo.style.display = 'none';
 
    //If it is video wall we want to show its parameters
    if(selected === '[ziggeovideowall'){
        editor.value = selected + ' ';
        wallParams.style.display = 'block';
        embeddingParams.style.display = 'none';
        wallInfo.style.display = 'inline-block';
    }
    //otherwise lets show Ziggeo embedding parameters
    else {
        //If we were setting the video wall prior to this, we can remove all parameters
        if(editor.value.substr(0, editor.value.indexOf(' ')) === '[ziggeovideowall') {
            editor.value = selected + ' ';            
        }
        //Otherwise, lets keep the parameters, so that it is easier to set it all up :)
        else {
            editor.value = selected + ' ' + editor.value.substr(editor.value.indexOf(' ')+1);            
        }
        wallParams.style.display = 'none';
        embeddingParams.style.display = 'block';
    }
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

    //The suffix to parameter indicating what king of value it is
    var data = current.getAttribute('data-equal');

    //We got boolean value and we do not want to add it multiple times..
    if(data === '') {
        //Allows us to check the location of the parameter, if it is already added
        var attrLoc = editor.value.indexOf( ' ' + current.innerHTML + ' ' );
        
        if(attrLoc === -1) {
            if(editor.value.indexOf( ' ' + current.innerHTML + '_' ) === -1) {
                //this could set pointer behind the word in a string matching the element - such as 'autoplay'. To make the code work in such cases as well
                // - to detect parameter and if it is in the string or not, we could strip all of the strings first, then search from there and take into
                // the account how many strings were stripped before the parameter.
                // Not doing it at this time since it would add too much complexity to the same.
                attrLoc = editor.value.indexOf( ' ' + current.innerHTML );
            }
            else if(editor.value.indexOf( ' ' + current.innerHTML + ']' ) > -1) {
                attrLoc = editor.value.indexOf( ' ' + current.innerHTML + ']' );
            }
        }
    }
    else {
        //Allows us to check the location of the parameter, if it is already added
        var attrLoc = editor.value.indexOf( ' ' + current.innerHTML + '=' );
    }

//@TODO Lets turn all quotes into single quote.. otherwise we could have an issue with the checks
//we could get trouble when doing that as well..best to leave and assist customers if they happen to experience any issues

    //Did we already add the same parameter? Might be good to check it out so that we do not add it again, just do custom cursor/caret positioning
    if( attrLoc > -1 )
    {
        //current.getAttribute('data-equal') we can use it to see if it is string or something else.

        var start = attrLoc + current.innerHTML.length + 2;

        //Are we working with a string attribute?
        if(data === "=''") {
            //Yes, its a string, lets check it out then:
            var end = editor.value.indexOf( "' ", attrLoc+1); //adding plus1 since we were searching for whitespace as well (to know that attribut started and is not part of the other parameter..)            
        }
        else {
            var end = editor.value.indexOf( ' ', attrLoc+1); //adding plus1 since we were searching for whitespace as well (to know that attribut started and is not part of the other parameter..)            
        }

        //What if we had removed the quote?
        var quoteCheck = editor.value.indexOf( "'", attrLoc+1);

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
        if(data.indexOf("'") > -1)
        {
            //we got a string based parameter, lets change the start and end..    
            if(quoteCheck < 0) { // we need to add quotes as well..
                start++;
                editor.value = editor.value.substr(0, start) + "'' " + editor.value.substr(end);
                end++;
            }
            else {
                start++;
                end = editor.value.indexOf( "'", quoteCheck+1);               
            }
        }

        editor.focus();
        editor.setSelectionRange( start , end );

        return false;
    }

    //If we are editing the template, we will have it closed every time.. so lets make sure that we do not add values after the closing bracket..
    if(editor.value[editor.value.length-1] === "]") {
        editor.value = editor.value.substr(0, editor.value.length-1) + ' ' + current.innerHTML + data + ']';
    }
    //If the space is not the last character, we should add it to avoid combined parameters
    else if(editor.value[editor.value.length-1] !== " ") {
        editor.value += ' ' + current.innerHTML + data;
    }
    //If it is space, we are adding the parameters, or we were, no closing bracket in sight, so we can continue
    else {
        editor.value += current.innerHTML + data;
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

        var templateBase = editor.value.substr(0, editor.value.indexOf(' ') );

        var paramsWall = document.getElementById('ziggeo-wall-parameters');
        var paramsZiggeo = document.getElementById('ziggeo-embedding-parameters');

        //videowall info
        var wallInfo = document.getElementById('ziggeo_videowall_info');
        wallInfo.style.display = 'none';

        if(templateBase === '[ziggeovideowall') {
            //show parameters for video wall
            paramsWall.style.display = 'block';
            paramsZiggeo.style.display = 'none';
            wallInfo.style.display = 'inline-block';
        }
        else {
            //show ziggeo parameters
            paramsZiggeo.style.display = 'block';
            paramsWall.style.display = 'none';
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

//Removes the feedback banner by setting the hidden option to checked and submits the form..
function ziggeo_feedback_removal() {
    var feedback = document.getElementById('ziggeo_feedback');

    feedback.checked = true;

    //submit the form
    document.forms[0].submit();
    return true;
    //when reloaded, we will use the add_settings_error to show a nice thank you for that with some restyling done by CSS for that specific thank you.
}

//Registering onload needed to have everything run smoothly.. :)
jQuery(document).ready( function() {
        ziggeo_parameters_quick_add_init();
        ziggeo_templates_manage_init();
        
    }
);

//tinymce extension JS
function ziggeo_tinymce_set_position(searchFor) {
    if(!searchFor) {
        searchFor = 'YOUR_VIDEO_TOKEN';
    }

    if(tinyMCE && tinyMCE.activeEditor) {
        var editor = tinyMCE.activeEditor;
    }
    else {
        return false;
    }

    var range = tinyMCE.activeEditor.selection.getRng(1);

    var before = jQuery(editor.getBody()).find('span#ziggeo_token_range_s');
    var after = jQuery(editor.getBody()).find('span#ziggeo_token_range_e')

    if( before[0] ) {
        range.setStartBefore( before.get(0) );
        range.setEndBefore( after.get(0) );
        editor.selection.setRng(range);

        before.remove();
        after.remove();
        
        return true;
    }

    return false;
}