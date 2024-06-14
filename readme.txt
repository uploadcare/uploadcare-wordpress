=== Uploadcare File Uploader and Adaptive Delivery (beta) ===

Contributors: andrew72ru, rsedykh, mixar, kkeng
Tags: file upload, cdn, storage, adaptive delivery, responsive, lazy loading, optimization, performance
Requires at least: 5.0
Tested up to: 6.5.4
Requires PHP: 7.4
Stable tag: 3.1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Donate link: https://uploadcare.com/pricing/

== Description ==

Uploadcare, an all-round media upload, storage, management, and delivery solution, breaks many WordPress Media Library limitations. Upload large files from various sources, including social media and cloud services. Insert them into WordPress posts and serve optimized, responsive images with Uploadcare CDN to improve your WordPress site performance.

This plugin allows WordPress authors to upload images and other files with Uploadcare File Uploader while creating posts and pages. All files hosted on Uploadcare CDN are served with smart format and quality optimizations to ensure the smallest file size while preserving the visual quality. You can migrate your existing Media Library to Uploadcare and use various other features.

[youtube https://www.youtube.com/watch?v=7Nc7GyD6_AE]

[Read integration guide](https://uploadcare.com/docs/guides/wordpress/).

Note: This version (3.x) of the plugin may require additional work on your part due to potential conflicts with other plugins or themes. Support is available only to customers on the [Custom](https://uploadcare.com/pricing/) pricing plan.

### Features ###

* Upload files of any type (image, video, document, archive) and size (up to 5 TB).
* Upload from your device, URL, social network (Facebook, Instagram, VK, Flickr), and cloud (Dropbox, Google Drive, Google Photos, OneDrive, Box, Huddle, Evernote).
* Insert images into your posts, and place other files and archives for download.
* Uploadcare CDN serves images faster in all parts of the world. Adaptive Delivery analyzes users' context and serves images in a suitable format, resolution, compression, which closes all image-related frontend tasks.
* Transfer your existing Media Library to your Uploadcare storage with no risk of data loss.
* Specify your custom CDN domain, and use a Secure Uploading feature to control who and when can upload files.
* Compatible with a standard WordPress image editor.

### Usage ###

* To add an image while editing a post or a page, choose "Uploadcare image" block. Also, you can upload any file directly to your Media Library with “Upload with Uploadcare” button — it’ll be hosted and delivered with Uploadcare.
* Use a built-in image editor when needed.
* If you accidentally upload a file using a standard WordPress option, you can easily transfer them to Uploadcare to use Adaptive Delivery and other features.

### Uploader translations ###

* English
* Arabic
* Azerbaijani
* Catalan
* Czech
* Danish
* German
* Greek
* Spanish
* Estonian
* French
* Hebrew
* Italian
* Japanese
* Korean
* Latvian
* Norwegian Bokmål
* Dutch
* Polish
* Portuguese
* Romanian
* Russian
* Slovak
* Serbian
* Swedish
* Turkish
* Ukrainian
* Vietnamese
* Chinese (Taiwan)
* Chinese

### More information ###

Visit [Uploadcare.com](https://uploadcare.com/) to learn more.
Send us your feedback, <help@uploadcare.com>

== Installation ==

### Auto installation (recommended) ###

1. In the plugin manager, click "Add New".
2. Search for "uploadcare".
3. Click "install".
4. Activate the plugin once it's installed.
5. Go to "Settings" -> "Uploadcare" and follow the instructions.

### Manual installation ###

1. Download the [latest release][github-releases]. The zip file contains the Wordpress plugin.
2. Unzip the archive to your `wp-content/plugins` folder.
3. Run `composer install` (install [Composer](https://getcomposer.org/download/)).
4. Run `yarn && yarn build` (install [Node](https://nodejs.org/en/download/) and [Yarn](https://classic.yarnpkg.com/en/docs/install/)).
5. Activate the plugin in the "Plugins" menu in your WordPress admin account.
6. Go to "Settings" -> "Uploadcare" and follow instructions.

### Requirements ###

* Wordpress 5+
* PHP 7.4+
* php-curl
* php-json
* php-dom

== Screenshots ==

1. Insert images right into your posts.
2. Upload from local disks, cameras, URLs, clouds, and socials.
3. Edit images after upload: crop, enhance, etc.
4. Adaptive delivery: automate creating responsive images with resize, smart compression, and lazy loading.
5. Images are delivered with Uploadcare CDN.
6. Transfer existing Media Library to Uploadcare.

== Upgrade Notice ==

= 3.1.0 =
Resolved a Cross-Site Request Forgery (CSRF) vulnerability on the settings page.
Fixed behaviour when uploading and downloading current images to and from Uploadcare storage.
Added bulk download files from the server.

= 3.0.10 =
Fixed PHP 7.4 support.

= 3.0.9 =
Minimal PHP version bumped to 7.4.

= 3.0.0 =
Brand new plug-in, rewritten from scratch. Note that forks from older plugins (v2.*) won't be compatible with the new version. In addition to uploading files it now supports Adaptive Delivery which improves image appearance on all devices and increases page load speed. Transfer Media Library files to your Uploadcare storage. Intuitive settings.

= 2.2.0 =
If you were controlling source tabs via "finetuning" setting, you should delete it and set new "source tab" config.

= 2.1.0 =
PHP 5.2 is not supported since this version.

= 2.0.11 =
Upgrade if you want to allow your readers to upload files.

= 2.0.10 =
Upgrade if you are using Uploadcare for Featured Images.

= 2.0.9 =
Access all files in your Uploadcare account via Media Library.

== Changelog ==

= 3.1.0 =

Fixed:
* Fixed behaviour when uploading and downloading current images to and from Uploadcare storage.
* Autotests support.

Compatibility:
* Tested in WordPress up to version 6.4.3.

= 3.0.11 =

Fixed:
* Corrected the URL to the "upload with Uploadcare" button icon.

Compatibility:
* Tested in WordPress up to version 6.4.

= 3.0.10 =

* Fixed PHP 7.4 support.

= 3.0.9 =

* Added support for PHP 8+.
* Bumped minimal PHP version to 7.4.
* Masked secret key in plugin settings for additional security.
* Updated Uploadcare widget.

= 3.0.8

* Removed types from method (bug fix for https://wordpress.org/support/topic/fatal-error-3-0-7/).

= 3.0.7 =

* Changed plugin responsive images default mechanism to leverage default WordPress "srcset" behavior improved with our smart image quality and auto format features.
* Returned Uploadcare upload button to a default upload option.
* Added an option to transfer files to Uploadcare storage post by post.
* Created console utility to transfer all files at once.
* Minor bug fixes and improvements.

= 3.0.6 =

* Fixed an issue with missing UUIDs for previously uploaded images, which led to an error while rendering images.
* Various fixes and improvements.

= 3.0.5 =

* Now you can copy your WordPress Media Library to Uploadcare cloud storage. Files in Media Library don’t get removed.
* Image source URLs in posts don’t change. However, when a post is rendered, images get delivered with Uploadcare CDN powered by Adaptive Delivery technology.

= 3.0.4 =

* Added placeholder for Uploadcare Image caption.
* Fixed update process from 2.7.x to 3.x.x.
* Various other fixes.

= 3.0.3 =

* Fixed upload button position on "Add media" page.
* Refactored code for better performance and control.
* Now you can use Uploadcare Image Editor to modify images from Media Library.

= 3.0.2 =
* Plugin as class.
* Composer autoload.
* Unit-tests for most classes and methods.

= 3.0.1 =
* Fixed issue with plugin activation to prevent malfunction when PHP DOMDocument class is disabled.
* Various small improvements.

= 3.0.0 =
* Brand new version, rewritten from scratch.
* Adaptive image delivery system for auto responsiviness, quality optimization, and lazy loading.
* Better integration with Media Library and WYSIWYG.
* Transfer your old files to Uploadcare and back. (Risk free!)
* Updated Settings page.
* Removed "uploadcare" shortcode that allowed your readers to upload files.

= 2.7.2 =
* Default CDN base was empty, now it points to an existing host.

= 2.7.1 =
* Fix botched release.

= 2.7.0 =
* Added CDN Base option.
* Added signed uploads option.

= 2.6.1 =
* Fixed error with plugin initialization.

= 2.6.0 =
* Added the `data-integration` attribute to the widget reporting its version together
  with the library version used.
* Added User Agent reports library and integration versions used.
* Updated [uploadcare/uploadcare-php](https://github.com/uploadcare/uploadcare-php)
  to the version 2.2.1.
* User Agent reporting now uses a new default format.

= 2.5.3 =
* Fixed file duplication on upload.
* Fixed widget effects tab settings.

= 2.5.2 =
* Fixed missed "Fine tuning" setup for widget.
* Test up to Wordpress 4.9.4.

= 2.5.1 =
* Fixed initialization warnings in utils.php module.

= 2.5.0 =
* Test up to Wordpress 4.9.1.
* Uploadcare widget updated to version 3.x.
* Added In-browser image editing and filters.
* Added storing Uploadcare images locally on your WordPress host.

= 2.4.1 =
* Minor bug fix for usage jQuery.
* Test up to Wordpress 4.7.3.

= 2.4.0 =
* Uploadcare widget updated to version 2.6.0.
* Test up to Wordpress 4.4.2.
* update uploadcare-php lib.

= 2.3.2 =
* Fix "add via uploadcare" button in media library.

= 2.3.1 =
* Fix plugin activation message on PHP 5.2-.

= 2.3.0 =
* Uploadcare widget updated to version 1.4.2.
* Test up to Wordpress 4.0.
* Add 'de' locale.
* Check PHP version and php-curl lib on plugin activation.

= 2.2.0 =
* Uploadcare widget updated to version 1.2.0.
* Add Flickr support.
* Add 'da' locale.
* Add source tab setting.

= 2.1.0 =
* Uploadcare widget updated to version 1.0.1.
* Update underlying uploadcare-php.
* Ditch support of php-5.2.
* Add Uploadcare button to default media library uploader.

= 2.0.11 =
* Add custom post type "User Images".
* Add [uploadcare] shortcode, that shows widget for users. Uploaded images are attached to post
  and are saved as "User Images".
* Remove custom Featured Images meta box. Build in should work with attachments.
* Uploadcare widget updated to version 0.18.0.

= 2.0.10 =
* Fix Featured Images.

= 2.0.9 =
* Uploadcare media library tab now shows all files from your Uploadcare account (project).
* Allow cropping when inserting images via Uploadcare media library tab.
* Make Wordpress attachment when uploading file via "Add Media" button.
  This should improve Uploadcare integration with Wordpress and 3rd party plugins.

= 2.0.8 =
* Fix pagination in media tab.

= 2.0.7 =
* Support featured images via Uploadcare.
* New setting "Use Uploadcare for featured images".
* Bug fixes.

= 2.0.6 =
* New setting "Allow multiupload".
* New setting "Uploadcare widget fine tuning".
* Uploadcare widget updated to version 0.12.
* Bug fixes.

= 2.0.5 =
* New setting "Insert image with url to the original image".

= 2.0.4 =
* Uploadcare widget updated to version 0.8.1.2.

= 2.0.3 =
* Bug fixes.

= 2.0.2 =
* Bug fixes.

= 2.0.1 =
* Bug fixes.

= 2.0 =
* New widget version, plugin refactored and ready to go.

= 1.0.5 =
* Minor fixes.

= 1.0.4 =
* Minor fixes.

= 1.0.3 =
* More operations form validation.
* Fixed files deletion.
* "Files" are presented as Uploadcare logo.
* Header is not displayed for Wordpress 3.5, updated for new "Add Media".

= 1.0.2 =
* Operation values no longer nulled when operation type is changed.
* Now inserts files, not only images.

= 1.0.1 =
* Scale crop and Resize are now radio buttons instead of checkboxes.
* Fixed "Store" button image script.

= 1.0.0 =
* Uploadcare widget updated to 0.5.0. Facebook and instagram added.
* "Store" button is hidden until file is selected.
* Uploaded files are saved in database.
* Uploadcare file list uses only saved files, not all the files from an Uploadcare account.
* "Crop" operation deleted.
* "Resize" and "Scale Crop" cannot be used at the same time.
* Minor fixes.

= 0.2.0 =
* New tab: Uploadcare Files - Select previously uploaded files.
* "Uploadcare" admin menu: view and delet previously uploaded files.

= 0.1.1 =
* Wordpress readme added.
* Preview is now at the bottom of the form.
* Scale and Crop "center" option is defaulted to "checked".

= 0.1.0 =
* Initial release.
