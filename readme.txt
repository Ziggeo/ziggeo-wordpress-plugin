=== Ziggeo ===
Contributors: oliverfriedmann, baned
Donate link: https://ziggeo.com/
Tags: video, comments, posts, video comments, crowdsourced video, crowdsourced video plugin, page, recorder, user generated content, user generated content plugin, user generated video, video comments, video posts, video recorder, video recording, video reviews, video submission, video submission plugin, video testimonial plugin, video testimonials, video upload, video widget, webcam, webcam recorder
Requires at least: 3.0.1
Tested up to: 4.5.3
Stable tag: 1.14
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin allows you to do video posts and accept video comments through use of powerful Ziggeo API.


== Description ==

You can use this plugin to spice up your blog with video posts. Simply start by creating a new post, click on the *Record Video* button and take a video of yourself. It'll then be shown to your audience once your post goes live.

You can also invite people to leave video comments. Folks will have the option to either leave a traditional text comment or take a short video of themselves that'll be viewable by everybody on your site.

The plugin is based on the Ziggeo API which allows you to integrate video recording and playback with only two lines of code in your site, service or app.

= Why Ziggeo? =

Ziggeo video playback and recording is built upon its own framework which allows you to record and play videos on various platforms without thinking about the video type specific to browsers or technology available on platform while in the same time unifying the design of your video player and recorder.

Ziggeo is designed to do that using same calls and methods while our backend works out all of the specifics for you. And plugin? Well, plugin just packs the power of Ziggeo and allows you to have the same on your WordPress website in just few clicks, regardless if you are looking for video recorder, video player or video gallery.

= Support =

We provide active support to all that have any questions or need any assistance with our plugin or our service.
To submit your questions simply go to our [Help Center](https://support.ziggeo.com/hc/en-us)

= Recording =

You control the length of the videos, the tags associated with the same (by default we pre-set the tags to "wordpress", username of the submitter as reported by WordPress and (new) where the video was made - "post"/"comment" )

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

= Playback =

You just tell us the video that you wish to play and how you want it to look - full customization possible - allowing your videos to blend into your WordPress website and to amaze your website visitors.

A sample of your video player call was as simple as:

`
[ziggeo]VIDEO_TOKEN[/ziggeo]
`

This is still the same, but you can also use the following:

`
[ziggeoplayer video="VIDEO_TOKEN"]
`

or

`
[ziggeo video="VIDEO_TOKEN"]
`

All of them will load a player on your website. Want to customize it? With templates, your WordPress video player is created by simply adding the following:

- seeing that myVideoPlayer is the name of the template:

`
[ziggeoplayer myVideoPlayer video="VIDEO_TOKEN"]
`

Do you love simple [ziggeo] tag instead? That is OK. It will work like that as well. Just change it to:

`
[ziggeo myVideoPlayer video="VIDEO_TOKEN"]
`

- Starting with version 1.13 you can also view your videos in video wall template. To see more about it, do check out its section bellow.

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

`
[ziggeo uploads]
`

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
[Help Center](https://support.ziggeo.com/hc/en-us)
[Introduction to VideoWall on our blog](http://blog.ziggeo.com/2016/06/13/videowall-the-best-way-to-easily-show-a-video-gallery-on-your-wordpress-based-website/)
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


== Screenshots ==

1. General tab
2. Templates Tab
3. Contact us tab

= Templates =
4. Working with templates - nice layout of parameters and their description
5. Working with templates - managing templates
6. Working with templates - editing templates
7. Working with templates - beta templates (if you are not on beta by default)
8. Working with templates - videowall templates setup
9. videowall (defaults - page numbers)
10. videowall (defaults with slidewall turned on)

= Global Defaults =
11. Your old setup is now used as fallback

= Comments =
12. More options for comments.

= Editing Post =
13. TinyMCE button


== Upgrade notice ==

= 1.13 =
* Introducing VideoWall template


== Changelog ==

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
