<?php

// This file is used to allow us to add toolbar button to TinyMCE when editing posts
// We are using it as PHP instead of js file simply because this allows us to load the existing templates..


include_once('./file_parser.php');

$list = ziggeo_file_read( '../../ziggeo-userData/custom_templates.php' );


if($list) {
    //If there are double quotes, it would cause issues with TinyMCE, however with templates editing as well.
    //Since this is called for templates only, we know that we are OK with changing all double quotes into single quotes..
    $list = str_replace('"', "'", $list);        
}

$start = "(function() {
    tinymce.create('tinymce.plugins.ziggeo', {
        /**
         * Initializes the plugin, this will be executed after the plugin has been created.
         * This call is done before the editor instance has finished it's initialization so use the onInit event
         * of the editor instance to intercept that event.
         *
         * @param {tinymce.Editor} ed Editor instance that the plugin is initialized in.
         * @param {string} url Absolute URL to where the plugin is located.
         */
        init : function(editor, url) {
            editor.addButton('ziggeo_templates', {
                title: 'Ziggeo Video Aid',
                cmd: 'ziggeoAddTemplate',
                image: url + '/../images/icon.png',
                type: 'menubutton',
                menu: [ //This is first level menu
                    {
                        text: 'Templates',
                        value: '[ziggeo]',
                        onclick: function() { //We will remove the onlick from here, this is just for test
                            editor.insertContent(this.value());
                        },
                        menu: [ //This is second level menu
                                    ";

$middle = '';
$base = '[ziggeo ';
if($list) {
        //player and re-recorder require token.. so it would be good to detect which base the template has and then add any required attributes to the same..
        foreach($list as $id => $template) {

                $tokenRequired = 'no';
                //We will check each since we want people to be able to add 2 or more templates ;)

                //Are we dealing with player?
                if( stripos( $template, '[ziggeoplayer' ) > -1 ) {
                    if( stripos($template, 'video') === false ) { //nope, lets add it
                        $template = str_replace( '[ziggeoplayer', '[ziggeoplayer video=\'<span id=\"ziggeo_token_range_s\"></span>YOUR_VIDEO_TOKEN<span id=\"ziggeo_token_range_e\"></span>\'', $template);
                    }
                    $tokenRequired = 'yes';
                }

                //are we dealing with the rerecorder?
                if( stripos( $template, '[ziggeorerecorder') > -1 ) {
                    //Is token already set?
                    if( stripos($template, 'video') === false ) { //nope, lets add it
                        $template = str_replace('[ziggeorerecorder', '[ziggeorerecorder video=\'<span id=\"ziggeo_token_range_s\"></span>YOUR_VIDEO_TOKEN<span id=\"ziggeo_token_range_e\"></span>\'', $template);
                    }
                    $tokenRequired = 'yes';
                }

                //If it is video wall, we should add it as ziggeowall, rather than ziggeo (because when template is removed the wall becomes recorder..)
                if( stripos($template, '[ziggeovideowall') > -1 ) {
                    $base = '[ziggeovideowall ';
                }

                if($middle !== '')      { $middle .= ', '; }
                $middle .= "{
                                text: '" . $id . "',
                                value: \"" . $template . "\",
                                requiresToken: '" . $tokenRequired . "',
                                onclick: function(e) { 
                                    e.stopPropagation();
                                    if(e.shiftKey === true) {
                                            editor.insertContent(this.value());
                                    }
                                    else {
                                        if(this.settings.requiresToken === 'yes') {
                                            editor.insertContent('" . $base . "' + this.text() + ' video=\'<span id=\"ziggeo_token_range_s\"></span>YOUR_VIDEO_TOKEN<span id=\"ziggeo_token_range_e\"></span>\' ]');                                                                     
                                        }
                                        else {
                                            editor.insertContent('" . $base . "' + this.text() + ' ]');
                                        }
                                    }
                                    ziggeo_tinymce_set_position();
                                }
                            }";
        }
}
else {
        $middle = "{
                        text: 'No templates found',
                        value: ''
                    }";
}

$end =                       "
                        ]
                    }
                ]
            });
        },
 
        /**
         * Creates control instances based in the incoming name. This method is normally not
         * needed since the addButton method of the tinymce.Editor class is a more easy way of adding buttons
         * but you sometimes need to create more complex controls like listboxes, split buttons etc then this
         * method can be used to create those.
         *
         * @param {String} n Name of the control to create.
         * @param {tinymce.ControlManager} cm Control manager to use in order to create new control.
         * @return {tinymce.ui.Control} New control instance or null if no control was created.
         */
        createControl : function(n, cm) {
            return null;
        },
 
        /**
         * Returns information about the plugin as a name/value array.
         * The current keys are longname, author, authorurl, infourl and version.
         *
         * @return {Object} Name/value array containing information about the plugin.
         */
        getInfo : function() {
            return {
                longname : 'Ziggeo Buttons',
                author : 'Bane',
                authorurl : 'https://ziggeo.com',
                infourl : 'https://ziggeo.com/',
                version : '0.1'
            };
        }
    });
 
    // Register plugin
    tinymce.PluginManager.add( 'ziggeo', tinymce.plugins.ziggeo );
})();";


echo $start . $middle . $end;

?>