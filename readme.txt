=== Ziggeo ===
Contributors: oliverfriedmann, baned
Donate link: http://ziggeo.com/
Tags: comments, posts, video comments, crowdsourced video, crowdsourced video plugin, page, recorder, user generated content, user generated content plugin, user generated video, video comments, video posts, video recorder, video recording, video reviews, video submission, video submission plugin, video testimonial plugin, video testimonials, video upload, video widget, webcam, webcam recorder
Requires at least: 3.0.1
Tested up to: 4.5
Stable tag: 1.8
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin allows you to do video posts and accept video comments through use of powerfull Ziggeo API.


== Description ==

You can use this plugin to spice up your blog with video posts. Simply start by creating a new post,
click on the *Record Video* button and take a video of yourself. It'll then be shown to your audience
once your post goes live.

You can also invite people to leave video comments. Folks will have the option to either leave a traditional
text comment or take a short video of themselves that'll be viewable by everybody on your site.

The plugin is based on the Ziggeo API which allows you to integrate video recording and playback with only
two lines of code in your site, service or app.

= Support =

We provide active support to all that have any questions or need any assistance with our plugin or our service.
To submit your questions simply go to our [Help Center](https://ziggeo.zendesk.com/hc/en-us)

= Recording =

You control the length of the videos, the tags associated witht the same (by default we pre-set the tags to "wordpress",
username of the submitter as reported by WordPress and (new) where the video was made - "post"/"comment" )

Recordings can be processed on our end to include any effects or filters that you have previously set, while you are
still using only few lines of human readable short codes.

= Playback =

You just tell us the video that you wish to play and how you want it to look - full customization possible - allowing
your videos to blend into your WordPress website and to amaze your website visitors.

= Video Uploads =

You want to allow uploads? Want to turn them into playable videos right after uploading? This has never been easier - you
simply set the recorder to allow uploads, or use our predefined uploading template (*[ziggeouploader]*) and you are good to go.
All videos uploaded through the same would be possible to be played back for you.

= Templates =

Ziggeo plugin is now powered by templates allowing you to quickly set up the template in the "template builder" part of the
plugin and then simply reference from your posts, pages and comments. There is no limit in the amount of templates you can have
and it is up to you how you will use them.

You can also set default templates for your comments - for playback and recording so we got you covered there as well.

= How templates work =

Templates are an easy way for you to set your parameters for recorder, player or uploader (at this time) and set it only in one place. In the same time this is done through a simple to use 'tool' next to the templates editor.

When the embedding is detected in your posts, pages or comments, our plugin will go through it and find what you wanted to show and how you wanted to show it.

= Improvements and Feedback =

If you experience any issues with the plugin, please let us know. You can do that through options shown in plugins *Contact Us* tab, or if
you were in contact with us before, just let us know in the same manner as before.

We value your suggestions in regards to all aspects of our service and plugin as well, so use this and don't be afraid to help us help you.


== Installation ==

There are several ways to power your WordPress with video recording and video playback features.

= Manually -
1. Upload the directory to your plugins folder (by default this is) `/wp-content/plugins/` directory and click on *Ziggeo Video* in the Settings menu.

= Automatically =
1. Go to Admin panel of your WordPress website and click on Plugins -> Add New
 1. Search for "Ziggeo"
 1. Click *Install* on "Ziggeo Video Posts and Comments"
 1. Activate plugin
 1. Go to *Ziggeo Video* under Settings menu

* That is it, your plugin is installed :)

== Frequently asked questions ==

= How to pass ID from plugin XY to Ziggeo tag = @TODO
Do step 1, 2, 3..

= How to show videos to only some people when using WordPress Groups plugin? =

This is the plugin: [WordPress Groups plugin](https://wordpress.org/plugins/groups/)

You would be able to do that either by using:

`
[groups_member group="YourLimitedGroup"]
 [ziggeo][/ziggeo]
[/groups_member]
`

or by using

`
[groups_non_member group="YourLimitedGroup"]
 [ziggeo][/ziggeo]
[/groups_non_member]
`


= Why would we use templates? =

Do you happen to have hundreds of posts with Ziggeo embeddings?
What if you wanted to change all of them? Well, if your first thought was about opening each and every one of those great posts on your websites or thinking about the SQL statements that you would use, stop. With templates that is not the case. You can just edit the template and that is it, all posts get updated with that one edit! How amazing is that? :)

= What happens when you delete the template? =

Well, the template will no longer be found and used, however the defaults that you have set under your *General* tab are what we will use to show your videos / recorder.

= What happens if we delete all templates and did not have any defaults set? =

Even if unlikely to happen, we thought about that and are always using our own defaults in case we can not find anything else.

= How are templates stored? =

By default we try to write them into a file. This has some concerns that you should be aware of if you are on shared hosting, but otherwise it means that there are no extra DB calls made when saving and reading templates. See the *We are getting the error that the template can not be saved* segment for more.

= We are getting the error that the template can not be saved =

That simply means that you are using different 'user' to run WordPress and a different one to create files. To work around this all you should do is to:

1. create a folder/directory under /ziggeo/ (Ziggeo plugin directory) named **userData** (if it is not existing)
1. create a file named **custom_templates.php**
1. set its permissions to *766* or *666*. Our plugin will now try to read it and change the permissions once it is done saving the file (as such 766 will not stay the same all the time, only for a moment when plugin needs it to write to the same).

= Why are the options on General tab hidding and showing when we move our mouse? =

We added a lot of changes to this version and we are planning on adding more. In order to keep it all clean and simple we made few style decisions which we hope you will like, where the option explanation will only show itself onec you are over it and for the rest of the time your dashboard is nice and clean.

== Screenshots ==

1. General tab
2. Templates Tab
3. Contact us tab

= Templates =
4. Working with templates - nice layout of parameters and their description
5. Working with templates - managing templates
6. Working with templates - editing templates
7. Working with templates - beta templates (if you are not on beta by default)

= Global Defaults =
8. Your old setup is now used as fallback

9. //@todo - maybe we could add segments =general tab=, =templates tab=, etc and then screenshots..

== Upgrade notice ==

= 1.11 =
* Templates are now available in Ziggeo plugin

== Changelog ==

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
