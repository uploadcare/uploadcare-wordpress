=== Uploadcare: Add media from anywhere ===
Contributors: grayhound1
Tags: media upload, file handling, cdn, storage, facebook, dropbox, instagram, google drive
Requires at least: 3.5+
Tested up to: 3.5
Stable tag: 2.0.5
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Uploadcare provides media uploading, processing and CDN for your blog. You can upload even very large images and crop them.

== Description ==

### Features ###
* Upload images of any resolution
* Crop images
* Upload documents and archives
* Choose images from Facebook, Instagram or Flickr
* Choose files from Dropbox or Google Drive
* Deliver your media through CDN, it's up to 5x times faster

### Languages ###
* English
* Latvian
* Polish
* Portuguese
* Russian
* Spanish

### More information ###
Check [Uploadcare.com](https://uploadcare.com/) to learn more.
Send us your feedback, <feedback@uploadcare.com>

== Installation ==

### Fastest way ###

1. In plugin manager, click "Add New"
1. Search for "uploadcare"
1. Click "install"
1. Activate the plugin once it is installed
1. Go to "Settings" -> "Uploadcare settings" and enter the public and secret keys for your account.

To receive your keys, create your FREE account at [Uploadcare](https://uploadcare.com/accounts/create/plan-based/2/)

### Manual ###

1. Download the latest release. The zip file contains the Wordpress plugin itself and all its dependencies
2. Unzip file to your wp-content/plugins folder
3. Activate the plugin once it is installed
4. Go to "Settings" -> "Uploadcare settings" and enter the public and secret keys for your account.

To receive your keys, create your FREE account at [Uploadcare](https://uploadcare.com/accounts/create/plan-based/2/)

### Requirements ###
* Wordpress 3.5+
* PHP 5.2+
* php-curl

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

== Changelog ==
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