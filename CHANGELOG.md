This file contains the change log info for the `Ziggeo` (Ziggeo Core) plugin.

= 2.10 =
* Fix: The analytics date was not correctly shown. This has been corrected now
* New Feature: Ziggeo events can now be used in WP. Just add [ziggeo_event event=verified message="my message" type=alert] and as soon as the verified event fires, your message will be shown. See all events you can use on the following page: https://ziggeo.com/docs/sdks/javascript/browser-interaction/events. (to be extended for more types)
* Added support for `rerecordableifexists` and `playermodeifexists`

= 2.9 =
* Fix: In some scenarios the comments would get broken video player code. Now this has been fixed.
* Improvement: Added SDK pages to the plugin (requires you to download the SDK manually - for security reasons)

= 2.8 =
* Fix: Toolbar buttons showing up again
* Fix: Some CSS errors were failing silently, these are now fixed.
* Improvement: Handling of parameters that accept custom text is now better
* Improvement: Changed the way the code parsing is done allowing the templates to be picked up on faster
* Improvement: Parsing templates by template ID has been improved

= 2.7 =
* Addition: Added 2 new properties to ZiggeoWP on JS side, where `integrations_code_recorder` holds the template codes (RAW) for recorder and `integrations_code_player` for player. Some parsing might be needed for adding it on page depending on the workflow.
* Improvement: Slight change of CSS to make the snapshots shown better in the video list page, regardless of their orientation or aspect ratio.
* Improvement: The duration of video is no longer going behind videos rather it is shown above them.
* Improvement: Added a check if the constant was already defined allowing multiple shortcodes to run in same time without issue. Devs - the shortcode constant will be removed in future versions.
* Support: Added support for Elementor Text with Ziggeo shortcodes. In some area Elementor applies additional details to the content making ID not picked properly as it would within the Elementor shortcode field. The added support helps with the default alterations done by Elementor.
* Fix: When providing a filter hook wrong function name was used, which could have resulted in an error. Spotted with Advanced Custom Fields bridge plugin.

= 2.6 =
* Added: VAST settings have been added to the general tab. To use VAST in your player, just set them up per your requirements and add `preroll adprovider="ziggeo_vast"` to your player template.
* Added: Videolist page now shows additional details (video title and video description).
* Added: Videolist page now has the option to see and edit custom data
* Added: Edit option has been added to the videolist page videos, allowing you to edit tags, title and description from within your Wordpress admin dashboard.

= 2.5 =
* Fixed: One of the hook examples had undeclared value notice shown. This update resolves it.
* Fixed: The filter which was used to get tags, however ended up clearing the tags
* Improvement: Added priority to JS hooks to allow us to set when something should fire
* Improved JS Hooks fire/trigger handler.
* Moved the changelog info to a new file, reducing the size of readme file.
* Fixed a bug that showed up when running system that minimizes the files of all plugins.
* Videolist is now using stretchheight parameter for better playback of portrait videos and no longer uses popups
* All API calls are now using V2 flavor.
* Improvement: If API token is not present a warning is shown, to help guide you to plugin settings and allow you to quickly and easily start using our plugin

= 2.4.4 =
* Improved widgets parser to help when not all of the expected data is passed to it. Videos received from a widget that did not pass all details will be marked with "no_id" tag.

= 2.4.3 =
* Improved tags parsing where wrong type of quote was used causing PHP Notice being raised about it. Thank you Igor for reporting it.
* Added improvment to widgets parsing where hooks used fired while the expected core functionality was not available. The imporvement makes the code work only when it is available.

= 2.4.2 =
* Notifications prune and clear are set to be disabled on click, to prevent clicking on the same multiple times while waiting for the server to respond.
* Added the option for default player for the integrations allowing you to simply choose the template you wish to use right from the settings.
* Added a change in how we do parsing so that the content does not "move" where before it could end up "moving" itself out of place in certain scenarios.
* Added support for parsing templates in widget title and widget content. All videos recorded within the widget, will also get a "widget", "widget_title" or "widget_content" tag as well as the unique ID of the widget, allowing you to easily find those videos.
* Added location info when parsing custom tags, in case that is needed when parsing

= 2.4.1 =
* Note: Please check changelog for 2.4 if you are updating from older version
* Moved some functions to resolve the issue where people using bbPress would have an error instead of toolbar shown.

= 2.4 =
* Added support for do_shortcodes() for all core template types
* New constant used when shortcodes are run ZIGGEO_SHORTCODE_RUN allowing you to check for the same (since some actions and hooks might be sending you different info or will not fire).
* Added a new way of showing addons as well as to make it easy for you to add your own through our new Addons page. Reach out to us to have your plugin 'advertised' in our plugin.
* Added initial support for the PHP SDK page once included. Preparing to add it in a way that does not stop anyone
* Added logo for our addons listing
* Made it easier to recognize the videos of different status per Natasha's recommendation
* Fixed typo spotted by Natasha that cause your own hooks to not make much of a difference in the code output. Affecting those using `ziggeo_get_template_recorder_integrations` and `ziggeo_get_template_player_integrations` filters.
* Added new user tags for easy substitute in templates per Karan's suggestion. You can now use `%USER_ID%`, `%USER_NAME_FIRST%`, `%USER_NAME_LAST%`, `%USER_NAME_FULL%`, `%USER_NAME_DISPLAY%`, `%USER_EMAIL%` and `%USER_USERNAME%` in your templates.
* Added `ziggeo_p_integrations_field_add_custom_tag` to help add custom attributes / codes within the embedding code. Useful to add class, id or some other attribute and you have the parsed template code. Mostly for integrations.
* Fixed an issue where the advanced editor would not add the parameter into the editor nor select the value if one was present.
* Added buttons to prune and clear the notifications for administrators. Prune allows you to remove all duplicates so you can see only single notification about something, while clear just clears them all out.
* Added the options for version and revision into the settings section (under Expert Settings) allowing easier change of both version and revision.

= 2.3.4 =
* Dropdown values in the dashboard reflected true state of settings, however the values were in wrong format, causing some settings to not be added to the page. This way they now are.

= 2.3.3 =
* Changed the way we output scripts to page in order to work better in different pages when specific setup is present that would cause codes to be out before they should be.

= 2.3.2 =
* Added a wait for ZiggeoApi just in case when due to load it might cause error.

= 2.3.1 =
* Added filters when retrieving the default codes for player and recorder, allowing you to dynamically modify them
* Added Notifications system and admin page allowing plugin to report things to admin
* Introduced new way we handle plugin settings
* Fixed a bug on REST requests - thank you Maxie C.
* Added Video Listing page for admins to see all videos, sort them and moderate them
* Added global AJAX request to allow us to detect for videos recorded on WP pages. Available as a hook for your code.
* IMPORTANT: Removed support for videowalls as announced since 2.0 Please use Videowalls plugin instead
* Added better support for AJAX requests
* Added auth init option
* Added a field for server auth token
* Added option for screen recording and `ziggeo_echo_application_settings` hook to add options for Ziggeo application initialization even if they are not yet supported by the Wordpress plugin.
* Fixed validation error that would clear some values
* Fixed comments error that would happen with v2.3
* Fixed defaults recorder typos which would result in recorder not being shown

= 2.2 =
* Template names fix - The names will be saved as lowercase, names will be checked so empty names are not present
* Additional functions to help other plugins to integrate with (get assets, clean values, etc.)
* Tested to work with our plugins that integrate to Gravity Forms, WPForms, Videowalls, bbPress, Job Manager
* Better handling for your own template designs in editor

= 2.1 =
* Fixing SVN issue
* Few minor updates to the code

= 2.0 =
* Overhaul bringing new possibilities
* Hooks and examples of how to use them
* Support for very latest of Ziggeo

= 1.15 =
* Added option to have integrations to other plugins
* New tab is added so you can easily manage the integration modules
* Added first integration - to Gravity Forms - by adding templates to your Gravity Forms form (per your setup)
* Integrations structure allows you to easily create your own integration modules and activate them through the plugin settings

= 1.14 =
* Added option to show videos from different posts in a video wall
* added option to show multiple types of videos (approved,rejected,pending) instead of single status.

= 1.13 =
* Fixed a small bug where additional spaces after template name would not allow you to delete/edit it easily
* Added VideoWall template with its various parameters to set it all up
* Made tinyMCE button load up when editing is done by Contributer or higher (in case the toolbar is shown in public)

= 1.12 =
* Fixed issue with double quotes stopping TinyMCE button in toolbar to not work properly
* Fixed issue with double quotes breaking template editing option
* Fixed template parsing in comments to allow template to be used and custom parameters
* Added option to remove Ziggeo Video Aid button from TinyMCE toolbar
* Modified templates builder layout

= 1.11 =
* Templates tab added to the plugin to expand its possibilities/features
* Previous setup now set as global defaults / fallback for templates
* Added more control for video recording and playback in comments 
* Added TinyMCE button in toolbar for easier usage of your templates

= 1.10 =
* Options to enable/disable video/text comments

= 1.9 =
* Use newest version of the Ziggeo assets

= 1.8 =
* Allow video posts from namespaced urls

= 1.7 =
* Allows integration of recorder in blog posts by using [ziggeo][/ziggeo]

= 1.6 =
* Improved Compatability with different commenting systems

= 1.5 =
* Enabled WebRTC and Resumable Uploads

= 1.4 =
* Allow admin to specify Ziggeo options

= 1.3 =
* Allow users to upload videos

= 1.2 =
* Allow to record multiple videos in a post
* Allow to combine videos with text in a post

= 1.1 =
* Fix comments for non-standard themes

= 1.0 =
* First version
