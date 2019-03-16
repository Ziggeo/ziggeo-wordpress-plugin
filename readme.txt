=== Ziggeo ===
Contributors: oliverfriedmann, baned, carloscsz409
Tags: video, video service, record video, video playback, gallery, ziggeo
Requires at least: 3.0.1
Tested up to: 5.0.3
Stable tag: 2.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin allows you to bring video to your Wordpress website or blog, through use of powerful Ziggeo API. Since we all know that video is future, make sure you are part of it.

== Who is this plugin for? ==

Are you looking to showcase some videos?
This plugin can help with that!

Are you looking to have themed galleries of videos on your website?
This plugin can help with that!

Want to allow people to submit their own videos?
Yup, this can also be done by this plugin.

Want to submit a post and attach video in it?
This plugin can handle it.

Want to know quickly what it can do?
There are plenty of features:
1. Screen Recording
2. Webcam recording
3. Playback from powerful Ziggeo servers and CDN or from your WP directory, S3, etc.
4. Mobile friendly
5. Easy to setup and use
6. PLenty ready to use themes yet easy to restyle
7. AI powered video analysis
8. AI powered audio transcription
9. AI powered (Not) Safe to use filter
10. Filter effects (Instagram like effects and watermarks / logos)

..and many many more features. Check them all out at https://ziggeo.com/features/


== Quick Description ==

You can use this plugin to spice up your blog, community or store with video posts. Want so share a video of yourself? Simply start by creating a new post, click on the *Record Video* button and take a video of yourself. It will then be shown to your audience once your post goes live.

Want to allow your community to share videos between them? Utilize this plugin and its various hooks, or alternatively enable helper plugin for your specific community plugin such as bbPress or BuddyPress. Read more about it in the helper plugins section.

You can also invite people to leave video comments. Folks will have the option to either leave a traditional text comment or take a short video of themselves. These comments might be viewable by everybody on your site, or not, depending on your own setup.

The plugin is based on the powerful Ziggeo API. While it is easy to add by yourself, this plugin adds it for you. All that you are left to do is to click around and set it all up (very simple and quite quick).

= Why Ziggeo? =

Ziggeo video playback and recording is built upon its own framework. It allows you to record and play videos on various platforms out of the box. Stop thinking about the video type specific to browsers or technology available on specific platform. Ziggeo API is unifying the design of your video player and recorder and makes all processes just work.

Ziggeo is designed to do that using same calls and methods while our backend works out all of the specifics for you. And plugin? Well, plugin just packs the power of Ziggeo and allows you to have the same on your WordPress website in just few clicks, regardless if you are looking for video recorder, video player or video gallery.

= Support =

We provide active support to all that have any questions or need any assistance with our plugin or our service.
To submit your questions simply go to our [Help Center](https://support.ziggeo.com/hc/en-us). Alternatively we have also added contact us page into our plugin so feel free to use that one as well.

= Recording =

You control the length of the videos, the tags associated with the same (by default we pre-set the tags to "wordpress", username of the submitter as reported by WordPress and where the video was made - "post"/"comment" )

Recordings can be processed on our end to include any effects or filters that you have previously set, while you are still using only few lines of human readable short codes.

Recording videos is as simple as:

`
[ziggeo]
`

- This will load our default video recorder and if any defaults are set through plugin, they would be used.

If you create template with name 'mytemplate', which includes video_profile, effects_profile, width, height and tags, instead of these few lines:

`
<ziggeo ziggeo-video_profile="_my_video_profile"
        ziggeo-effect_profile='my_effects'
        ziggeo-width=640
        ziggeo-height=480
        ziggeo-tags='mytag1,mytag2'>
</ziggeo>
`

You can simply use the following, even shorter call:

`
[ziggeo mytemplate]
`

This allows you to easily make change in one place and have it reflected everywhere else.

= Playback =

You just tell us the video that you wish to play and how you want it to look - full customization possible - allowing your videos to blend into your WordPress website and to amaze your website visitors.

A sample of your video player call was as simple as:

`
[ziggeoplayer video="VIDEO_TOKEN"]
`

All of them will load a player on your website. Want to customize it? With templates, your WordPress video player is created by simply adding the following:

- seeing that myVideoPlayer is the name of the template:

`
[ziggeoplayer myVideoPlayer video="VIDEO_TOKEN"]
`


= Video Uploads =

You want to allow uploads? Want to turn them into playable videos right after uploading? This has never been easier - you simply set the recorder to allow uploads, or use our predefined uploading template (*[ziggeouploader]*) and you are good to go.
All videos uploaded through the same would be possible to be played back for you.

Instead of setting up the Ziggeo embedding to allow video uploads to your WordPress plugin you can also simply call the uploader (plugin) template as so:

`
[ziggeouploader]
`

Do you want to style it to some specific setup? That is possible, simply set it up with a template as so (seeing that our "uploads" is the name of the template):

`
[ziggeouploader uploads]
`

As with video player and Ziggeo video recorder, you can set up your uploader using the base template:


= Video Wall =

Were you interested in having an option not only to collect videos in your comments, but to show them as well? Something like a video gallery?

Well, if you are thinking "oh, that would be so nice" - we hear you! We also want to say that that is exactly what we did. As per your requests, we have thought of a way to introduce video walls that work with just a few lines in any part of your post or page.

So what happens is that you add a call to your video wall template like so:

`
[ziggeovideowall myTemplate]
`

As you do, your post will show the wall as per template setup, which means that you could do one of the following:
1. Show video gallery / video wall as soon as the page finishes loading
1. Request for video to be posted as a comment on the post to see the video wall
1. Show a message if no videos are present - or show another template instead.

* Yes, you read that correctly. If you show your video wall, and you want to show a template within it - that is possible allowing you to quickly add more videos.

By default the video wall will show you the videos made on the specific post (the one it is on), however if you wish to show videos from other posts or that are not associated yet with your WordPress, you can do that as well through videos_to_show parameter.

You can read more about Video Wall templates on the following useful links:
[Introduction to VideoWall on our blog](http://blog.ziggeo.com/2016/06/13/videowall-the-best-way-to-easily-show-a-video-gallery-on-your-wordpress-based-website/)
and
[Introduction to showing videos from other post on our forum](https://support.ziggeo.com/hc/en-us/community/posts/212117427-VideoWall-parameters-introducing-new-changes)

= Templates =

Ziggeo plugin is now powered by templates allowing you to quickly set up the template in the "template builder" part of the plugin and then simply reference from your posts, pages and comments. There is no limit in the amount of templates you can have and it is up to you how you will use them.

You can also set default templates for your comments - for playback and recording so we got you covered there as well.

You have already seen some of the examples above, and to see their full power, you should definitely check them out for size.

= How templates work =

Templates are an easy way for you to set your parameters for recorder, player or uploader (at this time) and set it only in one place. In the same time this is done through a simple to use 'tool' next to the templates editor.

When the embedding is detected in your posts, pages or comments, our plugin will go through it and find what you wanted to show and how you wanted to show it.

There are several base templates:

1. Any embedding
 `[ziggeo]`

2. Ziggeo Video Player
 `[ziggeoplayer]`

3. Ziggeo Video Recorder
 `[ziggeorecorder]`

4. Ziggeo Video ReRecorder
 `[ziggeorerecorder]`

5. Ziggeo Video Uploader base
 `[ziggeouploader]`

6. Ziggeo Video Wall base (Video Gallery)
 `[ziggeovideowall]`

The only reason why we created them is to allow you to use a simple tag in your post to specify what you are after with specific parameters being loaded for you.

Please check FAQ section for some of the questions related to the same.

= Improvements and Feedback =

If you experience any issues with the plugin, please let us know. You can do that through options shown in plugins *Contact Us* tab, or if you were in contact with us before, just let us know in the same manner as before, or over our [Ziggeo Forum in WordPress Plugin section](https://support.ziggeo.com/hc/en-us/community/topics/200753347-WordPress-plugin).

We value your suggestions in regards to all aspects of our service and plugin as well, so use this and don't be afraid to help us help you.

== Installation ==

There are several ways to power your WordPress with video recording and video playback features.

= Manually =
1. Upload the directory to your plugins folder (by default this is) `/wp-content/plugins/` directory and click on *Ziggeo Video* in the Settings menu.

= Automatically =
1. Go to Admin panel of your WordPress website and click on Plugins -> Add New
 1. Search for "Ziggeo"
 1. Click *Install* on "Ziggeo Video Posts and Comments"
 1. Activate plugin
 1. Go to *Ziggeo Video* under Settings menu

* That is it, your plugin is installed.


== Frequently asked questions ==

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

= Is using templates better than using previous method of embedding? =

Do you happen to have hundreds of posts with Ziggeo embeddings?
What if you wanted to change all of them? Well, if your first thought was about opening each and every one of those great posts on your websites or thinking about the SQL statements that you would use, stop. With templates that is not the case. You can just edit the template and that is it, all posts get updated with that one edit! How amazing is that?

It is of course up to you to decide which method works best for you - this is just an option that you can use if you need it.

= What happens when you delete the template? =

Well, the template will no longer be found and used, however the defaults that you have set under your *General* tab are what we will use to show your videos / recorder.

= What happens if we delete all templates and did not have any defaults set? =

Even if unlikely to happen, we thought about that and are always using our own defaults in case we can not find anything else.

= How are templates stored? =

By default we try to write them into a file. This has some concerns that you should be aware of if you are on shared hosting, but otherwise it means that there are no extra DB calls made when saving and reading templates (it is faster). See the *We are getting the error that the template can not be saved* segment for more.

= We are getting the error that the template can not be saved =

That simply means that you are using different 'user' to run WordPress and a different one to create files. To work around this all you should do is to:

1. create a folder/directory under your plugins directory and call it **ziggeo-userData** (if it is not already existing)
1. create a file named **custom_templates.php**
1. set its permissions to *766* or *666*. Our plugin will now try to read it and change the permissions once it is done saving the file (as such 766 will not stay the same all the time, only for a moment when plugin needs it to write to the same).

= Why are there 'ziggeo' and 'ziggeo-userData' directories in our plugins folder? =

In order to store data in files and read the same on-fly without loosing the data once the plugin is updated, we are storing the data in another folder - `ziggeo-userData`. That means that we have moved the actual data from the plugin itself. As such `ziggeo` directory holds the plugin functionality code and `ziggeo-userData` holds the data saved by the plugin.

This is probably a good place to mention that we are still keeping the data that we previously did in your database, it is just that new data is moved from it.

= Why are the options on General tab hiding and showing when we move our mouse? =

We added a lot of changes to this version and we are planning on adding more. In order to keep it all clean and simple we made few style decisions which we hope you will like, where the option explanation will only show itself once you are over it and for the rest of the time your dashboard is nice and clean.

= What happens when we set template for recording on a template base for playing videos, or some other combination? =

We try to detect what you might want to do. As such, even if you are using tag that is not specific to the template we will try to do what we can with it, but the template base will prevail.

We do however recommend that you set up any number of templates that you might want to have and use each as desired to make sure that all is loaded per your preferences.

= Is there a way to load templates without going to settings to see their names? =

Yes, we added *Ziggeo Video Aid* button to the toolbar of your TinyMCE shown right above the Post editing. It will allow you to quickly add your templates to your post without ever leaving it.

If it detects that it needs to get the video token set it will show the same to you and allow you to quickly set it up by pre-selecting it for you.

= Is there an easier way to get the body of a template / template structure into our post instead of the template id without doing edit to get it? =

Yes. To add the template you use *Ziggeo Video Aid* button in the toolbar. To get the body, you use the same button, and click on the same option. The only difference is that to get the body of the template loaded, you need to press *Shift* on your keyboard while clicking on the template ID.

= We have few plugins that show the Post editing toolbar in public as well and do not want Ziggeo button to be shown as well =

It is not. We have added a check to see if it is opened in public or by admin and show it only when it seems to be opened by admin. If you do have some specific plugin installed and by some chance it shows it in non admin places for you, just let us know, we will check it and add support for that plugin as well.

= What is "Turn into beta" option in templates tab? =

If you want to use beta everywhere, the global options are the way to go with. However if you want to use beta calls on some templates only (which is for example available to your developers only, or your testers), the best option would be to use the template. Once you click on the button, it will add a custom tag to indicate that it is beta embedding.

Clicking on it again will stop it from being 'beta'.

= Can we re-style our videowall / video gallery? =

Yes. There are few classes that you can use in your video walls
`.ziggeo_videoWall` - To style videowall template (video gallery if you prefer) 
`.ziggeo_wall_title` - To style the wall title if any is given
`.ziggeo_wallpage` - to style video wall pages
`.ziggeo_wallpage > ziggeo` - to style the embedings within the video wall (from here standard Ziggeo embedding CSS codes will work properly)
`.ziggeo_wallpage_number` - to style the page number buttons
`.ziggeo_videowall_slide_previous` - to style the < (previous arrow)
`.ziggeo_videowall_slide_next` - to style > (next arrow)

You can of course use your own CSS code, and with classes available for each element of the videowall this should be something very simple.

= Why there are some videos that can not be loaded in VideoWall? =

If you notice in your console the following error: `NetworkError: 403 Forbidden - link to video snapshot` or if you check the link directly and you see `This video is currently under moderation` it means that your video wall was able to load the video, however you have checked `Client cannot view unaccepted videos` in your dashboard - that is why you are shown the same.

If you are still not sure about how to resolve that, just let us know.

= We open a page with video wall, however no videos are shown even with video wall set to load right away =

To show videos you need to have videos on that specific page. This is done to allow you to show any videos from within your Ziggeo account that are specific to the post you are currently on. To show some videos, you can record your video in the post, or by recording it in the comments. All others that are added as video comments will be shown after new recording is made (to those that do it) or for all those that come to your page (depending on your setup).

= How do we enable integrations? =

This is done through your Integrations tab, however we do suggest checking out the *Other Notes* tab above for more details specific to integrations.


== Screenshots ==

1. General tab
2. Templates tab
3. Integrations tab
4. Contact us tab

= Templates =
5. Working with templates - nice layout of parameters and their description
6. Working with templates - managing templates
7. Working with templates - editing templates
8. Working with templates - beta templates (if you are not on beta by default)
9. Working with templates - videowall templates setup
10. videowall (defaults - page numbers)
11. videowall (defaults with slidewall turned on)

= Global Defaults =
12. Your old setup is now used as fallback

= Comments =
13. More options for comments.

= Editing Post =
14. TinyMCE button

= Integrations =
15. Ziggeo Video Field in Gravity Forms builder
16. Selecting template in Gravity Forms builder
17. Saved result tokens from the form submission


== Integrations ==

Since version 1.15 the integrations are available. It is now rather easy to get the Ziggeo video and power of its API into various other plugins and make your goals easy to accomplish on your WordPress website.

What integration means is that you can use our own created integrations or you could even create your own and if you want, share it with the others on our forum under [WordPress forum](https://support.ziggeo.com/hc/en-us/community/topics/200753347-WordPress-plugin).

The way the integrations work is as follows:

1. We make a quick check if the integration could work based on the required details given in the code of each integration module
2. If it can work we give you the option of making it active or disabling it - so the entire code module for integration only fires if you want it
3. what happens when integration is active is all up to the integration itself

As a first integration, we are introducing *Gravity Forms* integration. You will of course need the Gravity Forms installed on your system and our tests have been made with version 2.0 to version 2.0.2. It uses the latest recommended code and as such should work properly for many new versions to come (as is) and we will keep it updated.

It allows you to add any template that you have created to your Gravity Form.

So in just a few clicks, you can turn your Gravity Form into a form accepting videos, or a Gravity Form with video gallery, all up to you!

= Integrations FAQ =
*Q:* What happens to Ziggeo form fields if Gravity Forms integration is disabled at a later time?
*A:* If you disable the integration, you will be stopping it from working, and as such it would show only the parts created by Gravity Forms - such as form label. Everything else will no longer be shown, on admin, in preview nor on publick side.

*Q:* Can we create our own integrations?
*A:* Sure! Just let us know on our forum [under WordPress plugin topic](https://support.ziggeo.com/hc/en-us/community/topics/200753347-WordPress-plugin). and we would be happy to help you get started. It is also a place where we will be posting the steps on how to do it soon as well.

*Q:* How do we add Ziggeo Video Field to our Gravity Form?
*A:* To do it, you should open a form in the Gravity Forms builder. Once you do, open the `Advanced fields` section and you should see the *Ziggeo Video Field* in the same.

*Q:* Can we give you our feedback?
*A:* Of course! We welcome all feedback and suggestions, that is how we got to here, so do share with us your thoughts.



== Upgrade notice ==

= 1.15 =
* Introducing - Integrations
* Available Integration: GravityForms

== Changelog ==

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
