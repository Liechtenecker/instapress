=== Instapress ===
Contributors: tkrammer, liechtenecker
Donate link: 
Tags: widgets, photos, instagram, shortcode, images, instapress, sidebar, gallery
Requires at least: 3.0
Tested up to: 3.3
Stable tag: 1.5.6

Highly customizable plugin to display Instagram photos in a sidebar widget, a post or on a page. Also an image gallery with paging functionality.

== Description ==
Display your Instagram photos in a sidebar widget, post or pages or inlcude a single Instagram photo via the Instagram URL in one of your posts. 
Furthermore create your own Instagram gallery with Instapress' paging functionality. It makes use of jQuery's fancybox plugin to display a nice slideshow for your images.

= Features =
You can choose:

* how many Instagram images you want to display
* the size of the Instagram images
* whether you want to show the Instagram picture's title or not
* whether to use fancybox or not
* whether you want to display your own Instagram feed, a friend's Instagram feed, the popular media feed, a single image from Instagram or a feed with Instagrams containing specific tags
* to enable paging
* whether you want to show your Instagram feed as an image gallery

= Demo =
You can find a demonstration of the plugin <a href="http://demo.instapress.it/" target="_blank">here</a>

= Usage =
#### Shortcode ####
You can implement an instagram feed in posts or pages via shortcode in the following format: [instapress userid="" piccount="" size=""]

* **userid**: ID or username of instagram user (leave blank for popular media, use "self" for your own media or "myfeed" to display your feed)
* **piccount**: Number of instagrams to display (Default: 9) - Note: It is highly recommended to enable caching for more than 20 pictures
* **size**: Size of instagrams in pixels (Default: 85) 
* **effect**: Name of the effect to use for the slideshow (Currently only 'fancybox' possible) - Since 0.4.3
* **url**: If this value is set, Instapress ignores the value for **userid and piccount** and displays a single Instagram (no authorization required!) - Since 1.0
* **title**: If this value is set to 1 and an **effect** was set, Instapress will show the Instagram's title - Since 1.0.1
* **paging**: If this value is set to 1, your pictures will be displayed as an image gallery. The value for **piccount** defines the number of pictures per page.
* **tag**:  Use this option to filter Instagrams by tag. Note: At the moment you can only use a single tag. The userid option is ignored if you set this option.

#### Template ####
To use Instapress in one of your templates, simply work with do_shortcode() using the instapress shortcode.<br />
e.g. `<?php echo do_shortcode('[instapress piccount="12"]'); ?>`

#### Widget #### 
Add Widget called "Instagram" to your sidebar (wp-admin > Design > Widgets) and configure it:

* **Title**: Widget-Title (use CSS to hide it)
* **Picture size**: Size of pictures in pixels
* **Username**: Username of user whose media to display
* **Address/Coordinates**: Address of e.g. your bar, shop,... or coordinates in the format latitude,longitude (If this value is set, the user's feed will be ignored)
* **Pics**: How many pics will be displayed? - Note: It is highly recommended to enable caching for more than 20 pictures
* **Effect**: Fancybox uses jQuery Fancybox to display image gallery, otherwise the pictures link to their instagr.am page
* **Show title**: Shows the title of the image in Fancybox slideshow
* **Image container**: Individual HTML-Container for the Instagram image takes 2 parameters at the moment (*%index%* = Index of image starting with 1, *%image%* = the instagram image, *%title%* = the instagram's title - since 1.3.5)
* **Tag**:  Use this option to filter Instagrams by tag. Note: At the moment you can only use a single tag. The options Username and Address are ignored if you set this option.

#### Editor ####
Click on the Instapress icon in TinyMCE and use the form that pops up to insert images or feeds (see <a href="screenshots#tinymce">screenshots section</a>).


= Language support =
This plugin is currently available in the following languages:

* German
* English
* French (Thanks to: <a href="http://www.lantredekag.fr" target="_blank">Olivier MONTBAZET</a>)
* Turkish (Thanks to: <a href="http://ali.riza.esin.net" target="_blank">Ali Riza Esin</a>)
* Romanian (Thanks to: <a href="http://www.nobelcom.com/" target="_blank">Luke Tyler</a>)
* Russian (Thanks to: <a href="http://www.iflexion.com/" target="_blank">Iflexion</a>)
* Hungarian (Thanks to: Kristof Gruber) 
* Italian (Thanks to: Francesco Benanti)
* Swedish (Thanks to: <a href="http:/www.simondahla.com" target="_blank">Simon Dahla</a>)
* Spanish (Thanks to: <a href="http://laotraboladecristal.com" target="_blank">Maribel</a>)
* Dutch (Thanks to:  Huub Oosterbroek)

If you've created your own language pack please let me know at <a href="mailto:office@liechtenecker.at">office [at] liechtenecker.at</a><br />
Download the latest POT-File <a href="http://plugins.svn.wordpress.org/instapress/trunk/languages/instapress.pot">here</a>

For a german description of the plugin visit our blog at <a href="http://liechtenecker.at/instapress-das-instagram-plugin-fur-wordpress/" target="_blank">http://liechtenecker.at/instapress-das-instagram-plugin-fur-wordpress/</a>

Instapress is based on <a href="http://www.mauriciocuenca.com/blog/2011/02/instagram-api-implementation-in-php/" target="_blank">Mauricio Cuenca's PHP SDK</a>

== Screenshots ==
1. Authorization via username and password, no application needed (simply enter your login data) - Since 1.0
2. Instapress Widget Settings
3. Instapress Widget Demo
4. Instapress Fancybox
5. Instapress Shortcode Usage
6. Dialog for adding an Instagram to a post (does not need authorization!)
7. An Instagram added via rich editor (just like any other image)<a href="#" name="tinymce" style="text-decoration: none;"> </a>
8. Add an Instagram feed via rich editor (generates shortcode)

== Installation ==

1. Download the plugin and unzip it to your /wp-content/plugins/ folder.
1. Activate the plugin through the 'Plugins' menu in WordPress.
1. Enter username and password in the Instapress settings.
1. Add the widget to your sidebar from Appearance->Widgets and configure the widget options, use TinyMCE or insert the shortcode into your articles/pages
1. <a href="https://www.facebook.com/instapress" target="_blank">Tell your friends about Instapress on Facebook</a>

== Frequently Asked Questions ==
= Do I need an account on Instagram? =
You only need an Instagram account to embed feeds but not to embed a single Instagram.

= Where do I get an Instagram account? =
To register for Instagram you've got to download the Instagramm App for iPhone at <a href="http://instagr.am" target="_blank">http://instagr.am</a>.

= Is there a way to style Instapress? =
Yes, there are several ways to style Instapress:

**<em>Widget</em>**

**Professionals only:**

Each Instagram in the widget is surrounded by a container you can define yourself, by default the container looks like that:
<div class="instagram-image" id="instagram-image-%index%">%image%</div>
You can customize the container by using %image% as placeholder for the HTML image and %index% as placeholder for the position of the image starting with 1.

If you do not want to change the container you can simply use the following CSS selectors:

* **.instagram-image**: Class of the div container around the image ==> Use **.instagram-image img** to style the image
* **#instagram-image-INDEX**: ID of the div containing the image with the given index (e.g. #instagram-image-3 to style the third image's container)

**<em>Shortcode/Rich editor</em>**

* Instagram feeds embedded via shortcode are placed in a div having the class **instapress-shortcode**
* To define different styles for odd/even images use the classes **odd** and **even** (e.g. .instapress-shortcode-image.odd)

= How can I show the caption of the pictures? =
Just enabled the check the checkbox labelled "Title" in the widget or set the value for title to 1 in shortcode.

= Does Instapress require other plugins? =
No, Instapress doesn't require other plugins, all effects provided by Instapress are included in the plugin.

= Does Instapress run on PHP 4? =
No, Instapress requires at least PHP 5 to work properly.

= After installing I get "Parse error: syntax error, unexpected T_OBJECT_OPERATOR in...", why? =
This is mostly because you are running on PHP 4 and Instapress requires PHP 5. Please install/activate PHP 5 or contact your provider.

= Everytime I try to connect with my Instagram account I get "Fatal error: Uncaught exception 'Zend_Http_Client_Adapter_Exception' with message 'Unable to Connect to ssl://api.instagram.com:443.", why? =
This is because you need to activate OpenSSL on your server to interact with the Instagram API. Please <a href="http://www.nowfromhome.com/activate-openssl-extension-in-php/" target="_blank">activate</a> OpenSSL or contact your provider. (More information on that <a href="http://wordpress.org/support/topic/fatal-error-plugin-instapress-version-131" target="_blank">here</a>)

= I get the following error with Instapress: "Fatal error: require_once() [function.require]: Failed opening required 'Zend/Loader.php' (include_path='YOUR-INCLUDE-PATH') in wp-content/plugins/instapress/instagram-php-api/Zend/Uri.php on line 130". =
This issue may be caused by other plugins that restore php's include path. Please try deactivating plugins that may conflict with Instapress to find the plugin causing that error.

= Fancybox does not work properly since I installed Instapress, what now? =
Try disabling all effects for Instapress in the admin section (yourblog/wp-admin/options-general.php?page=instagram.php)

If you've got any issues with this plugin please post it to the <a href="http://wordpress.org/tags/instapress?forum_id=10">worpdress development forum</a>, we'll answer your question as soon as possible!

= Instapress only displays a blank page after entering my login data =
If Instapress just displays a blank page after you entered your Instagram login data in your Wordpress backend this is mostly caused by a PHP error.
This means that you should check if you are running on PHP 5 and activated OpenSSL on your webserver (see FAQ above). 

== Upgrade Notice ==
= 1.5.3 =
* Fixed troubles with SVN checkin for 1.5.0

= 1.5.0 =
* Please upgrade to use gallery functionality when effects are disabled

= 1.4.9 =
* Only shortcode: If you've changed the appearance of your Instapress feed using the id attributes you need to add the instance number now (e.g. id of an image in version 1.4.8 "instapress-shortcode-image-1" is now "instapress-shortcode-1-image-1")
* Fixed issues with entities in title 

= 1.4.1 =
* !IMPORTANT! Bug fix: Instagrams of users weren't displayed anymore

= 1.4 =
* Possibility to filter pictures by a tag

= 1.0 =
* Authentication via **xAuth**, hence **no need** for a developer account and an application anymore. Just authenticate using **username and password**!!

= 0.6.1 =
* Important Update: Fixes problems with API request limit and adds cache

= 0.4.2 =
* Fixed bug for own media via "self" in shortcode

= 0.4.1 =
* Added language support for german

= 0.4 =
* Initial upload to WordPress plugin directory

== Changelog ==

= 1.5.6 =
* Spanish (Thanks to: <a href="http://laotraboladecristal.com" target="_blank">Maribel</a>)
* Dutch (Thanks to:  Huub Oosterbroek)

= 1.5.5 =
* Added language support fur Swedish (Thanks to: <a href="http:/www.simondahla.com" target="_blank">Simon Dahla</a>)

= 1.5.4 =
* Added language support for Italian (Thanks to Francesco Benanti)

= 1.5.0 =
* Added possibility to filter you own Instagrams by one tag
* Loading bar in gallery does now only hide if all pictures are ready
* Added language support for hungarian
* Bug fix: Gallery did not work, when effects were disabled
* Bug fix: Previous-Link did only work for one shortcode gallery

= 1.4.9 =
* Support for multiple shortcode instances of Instapress on 1 page

= 1.4.8 =
* Bug fix: Option 'myfeed' did not display the user's feed anymore

= 1.4.7 =
* added possibility to use "highslide" as effect - NOTE: This requires a <a href="http://wordpress.org/extend/plugins/highslide-4-wordpress-reloaded/" target="_blank">highslide plugin</a>
* possibility to disable cache

= 1.4.6 =
* Bug fix from 1.4.5 also for the Instagram widget (not only shortcode)

= 1.4.5 =
* Added check for openssl (Issues with deactivated OpenSSL)
* Fancybox stylesheet will not be added unnecessarily when effects are disabled
* Bug fix: Instagram titles containing double quotes were cut (Thanks Katherine) 

= 1.4.4 =
* Updated language support for Turkish (Thanks to Ali Riza Esin - http://www.esin.net)

= 1.4.3 =
* Added language support for Russian (Thanks to Iflexion - http://www.iflexion.com/)

= 1.4.2 =
* Added language support for Romanian (Thanks to Luke Tyler - http://www.nobelcom.com/)

= 1.4.1 =
* Bug fix: Instagrams of users weren't displayed anymore

= 1.4 =
* Possibility to filter pictures by a tag
* Improved german language support

= 1.3.7 =
* Improved german language support
* Added option to show backlink to instapress.it

= 1.3.6 =
* Version number as CSS class in frontend DOM objects

= 1.3.5 =
* Added placeholder for title in widget

= 1.3.4 =
* Improved turkish language support
* Added possibility to disable width and height for img-Tags

= 1.3.3 =
* Added language support for turkish (thanks to: <a href="http://ali.riza.esin.net" target="_blank">Ali Riza Esin</a>)
* Fixed issue with inlcude-path

= 1.3.2 =
* Updated language support for french

= 1.3.1 =
* Fixed bug that caused geocoding issue on plugin activation
* Added check for geocoding functionality
* Changed check for php version

= 1.3 =
* Added paging funcionality (Demo <a href="http://work-liechtenecker.at/fb/instagram/wp/?p=59" target="_blank">here</a>)
* Performance improvements
* Added option to add a Facebook-Like-Button to single Instagrams
* Checks for the correct PHP version and cURL extension

= 1.2 =
* Fixed bug with PHP short codes (caused problems since PHP 5.3)
* Fixed bug causing simplexml_load_file Error
* Option to disable automatic integration of javascript effect plugins
* Option for random order in widget

= 1.1.1 =
* Improved german language support
* Added PayPal button in backend

= 1.1 =
* No upper limit for piccount anymore - It is now possible to display as many Instagrams as you want!

= 1.0.3 =
* Language Support French (Thanks to: Olivier MONTBAZET <a href="http://www.lantredekag.fr" target="_blank">http://www.lantredekag.fr</a>)

= 1.0.2 =
* Performance improvements when title-option is enabled

= 1.0.1 =
* Added possibility to show titles in shortcode version

= 1.0 =
##### New features #####
* Added **cache functionality** for image titles
* High **performance** improvements
* Embed single images **via Instagram URI** without authorization
* Authentication via **xAuth**, hence no need for a developer account and an application anymore. Just authenticate using **username and password**!!
* Images from widget and shortcode are no longer combined in one fancybox gallery
* Embed single Instagrams via rich-editor (TinyMCE)
* Embed Instagram feed via rich-editor (TinyMCE)
* Improved styling options (added css classes) for shortcode version
##### Bug fixes #####
* Image titles in widget showed widget title
* Widget content was drawn outside widget container
* Clearfix by default for widget

= 0.6.3 =
* Improved language support (German)

= 0.6.2 =
* Performance improvements

= 0.6.1 =
* Added cache functionality to prevent exceeds of request limit

= 0.5.1 =
* Fixed problem with geocoder

= 0.5 =
* It is now possible to show a location based feed

= 0.4.4 =
* Improved layout for images bigger than 150px
* Added default stylesheet for widget and shortcode

= 0.4.3 =
* Fancybox-Effect in shortcode is now possible

= 0.4.2 =
* Fixed bug for own media via "self" in shortcode

= 0.4.1 =
* Added language support for german

= 0.4 =
* First official release
* Provides user's feed, friend's feed and popular media feed