=== Uploadcare: Add media from anywhere ===

Contributors: grayhound1, dmitry-mukhin
Tags: media upload, file handling, cdn, storage, facebook, dropbox, instagram, google drive, vk, evernote, box, images, flickr
Requires at least: 3.5+
Tested up to: 4.7.2
Stable tag: 2.4.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Donate link: https://uploadcare.com/pricing/

Uploadcare provides media uploading, processing and CDN for your blog. You can upload even very large images and crop them.


== Description ==

### Features ###

* Upload images of any resolution and size
* Crop images
* Upload documents and archives
* Choose images from Facebook, Instagram, Flickr or VK
* Choose files from Dropbox, Google Drive, Box, Evernote
* Deliver your media through CDN, it's up to 5x times faster

### Languages ###

* Chinese (simplified)
* Danish
* Dutch
* English
* French
* German
* Hebrew
* Latvian
* Polish
* Portuguese
* Russian
* Spanish
* Turkish

### More information ###

Check [Uploadcare.com](https://uploadcare.com/) to learn more.
Send us your feedback, <feedback@uploadcare.com>

== Installation ==

### Fastest way ###

1. In plugin manager, click "Add New"
1. Search for "uploadcare"
1. Click "install"
1. Activate the plugin once it is installed
1. Go to "Settings" -> "Uploadcare" and enter the public and secret keys for your account.

To receive your keys, create your FREE account at [Uploadcare](https://uploadcare.com/accounts/create/)

### Manual ###

1. Download the latest release. The zip file contains the Wordpress plugin itself and all its dependencies
2. Unzip file to your wp-content/plugins folder
3. Activate the plugin once it is installed
4. Go to "Settings" -> "Uploadcare" and enter the public and secret keys for your account.

To receive your keys, create your FREE account at [Uploadcare](https://uploadcare.com/accounts/create/)

### Requirements ###
* Wordpress 3.5+
* PHP 5.3+
* php-curl
* php-json

== Screenshots ==

1. "Add Media" button.
2. Uploading widget: Choose images from Facebook.
3. Uploading widget: Choose files from Google Drive.
4. Uploading widget: Crop image.

== Usage ==

1. Begin adding a new post.
2. Press "Add Media" to insert media with the Uploadcare widget.
3. Upload an image using the widget.
4. Crop the image if you wish. Only the cropped area will be inserted.
5. Press "Done". The image will be inserted into your post.

== Shortcode ==

Drop `[uploadcare]` shortcode anywhere in post to allow your readers to upload files to your Wordpress via Uploadcare.
You can see all uploaded user images in admin interface under "User Images".

== Frequently Asked Questions ==

Please read up-to-date [FAQ](https://uploadcare.com/about/faq/) on [Uploadcare.com](https://uploadcare.com)

== Upgrade Notice ==

= 2.2.0 =
If you were controlling source tabs via "finetuning" setting, you should delete it and set new "source tab" config.

= 2.1.0 =
PHP 5.2 is not supported since this version

= 2.0.11 =
Upgrade if you want to allow your readers to upload files.

= 2.0.10 =
Upgrade if you are using Uploadcare for Featured Images.

= 2.0.9 =
Access all files in your Uploadcare account via Media Library.


== Changelog ==

= 2.4.1 =
* Minor bug fix for usage jQuery.
* Test up to Wordpress 4.7.2

= 2.4.0 =
* Uploadcare widget updated to version 2.6.0
* Test up to Wordpress 4.4.2
* update uploadcare-php lib

= 2.3.2 =
* Fix "add via uploadcare" button in media library

= 2.3.1 =
* Fix plugin activation message on PHP 5.2-

= 2.3.0 =
* Uploadcare widget updated to version 1.4.2
* Test up to Wordpress 4.0
* Add 'de' locale
* Check PHP version and php-curl lib on plugin activation

= 2.2.0 =
* Uploadcare widget updated to version 1.2.0
* Add Flickr support
* Add 'da' locale
* Add source tab setting

= 2.1.0 =
* Uploadcare widget updated to version 1.0.1
* Update underlying uploadcare-php
* Ditch support of php-5.2
* Add Uploadcare button to default media library uploader

= 2.0.11 =
* Add custom post type "User Images"
* Add [uploadcare] shortcode, that shows widget for users. Uploaded images are attached to post
  and are saved as "User Images"
* Remove custom Featured Images meta box. Build in should work with attachments.
* Uploadcare widget updated to version 0.18.0

= 2.0.10 =
* Fix Featured Images.

= 2.0.9 =
* Uploadcare media library tab now shows all files from your Uploadcare account (project)
* Allow cropping when inserting images via Uploadcare media library tab
* Make Wordpress attachment when uploading file via "Add Media" button
  This should impove Uploadcare integration with Wordpress and 3rd party plugins

= 2.0.8 =
* Fix pagination in media tab

= 2.0.7 =
* Support featured images via Uploadcare
* New setting "Use Uploadcare for featured images".
* Bugfixes

= 2.0.6 =
* New setting "Allow multiupload".
* New setting "Uploadcare widget fine tuning".
* Uploadcare widget updated to version 0.12
* Bugfixes

= 2.0.5 =
* New setting "Insert image with url to the original image".

= 2.0.4 =
* Uploadcare widget updated to version 0.8.1.2

= 2.0.3 =
* Bugfixes

= 2.0.2 =
* Bugfixes

= 2.0.1 =
* Bugfixes

= 2.0 =
* New widget version, plugin refactored and ready to go.

= 1.0.5 =
* Minor fixes

= 1.0.4 =
* Minor fixes

= 1.0.3 =
* More operations form validation
* Fixed files deletion.
* "Files" are presented as Uploadcare logo.
* Header is not displayed for Wordpress 3.5, updated for new "Add Media"

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
* Initial release
