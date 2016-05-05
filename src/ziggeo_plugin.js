/* This file is used to allows us to add toolbar button to TinyMCE when editing posts */

//NOT WORKING at this time for some reason.


function ziggeo_tinymce_plugin() {
	//@TESTING - WILL CHANGE TO DROPDOWN BUTTON OR MODAL WINDOW WITH THE LIST OF TEMPLATES
	return 'ADD THIS TO POST';
}

tinyMCE.PluginManager.add('ziggeo_tinymce_plugin', function( editor, url) {
		editor.addButton('button1', {
			text: 'ziggeo',
			icon: false,
			onclick: function() {
            // Open window
            editor.windowManager.open({
                title: 'Example plugin',
                body: [
                    {type: 'textbox', name: 'title', label: 'Title'}
                ],
                onsubmit: function(e) {
                    // Insert content when the window form is submitted
                    editor.insertContent('Title: ' + e.data.title);
                }
            });
        }
		});
	}
);
