=== Plugin Name ===
Contributors: grayhound1
Tags: media upload, file handling
Requires at least: 3.5+
Tested up to: 3.5
Stable tag: 2.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Uploadcare provides media uploading, processing and CDN for your blog.

== Description ==

Uploadcare provides media uploading, processing and CDN for your blog.

You can upload even very large images and crop and resize them.

And the best thing of all: with Uploadcare you can select files directly from Facebook, Instagram and Flickr, not only from your computer.

Check [Uploadcare.com](https://uploadcare.com/) for more information.

== Installation ==

1. Download the latest release. The zip file contains the Wordpress plugin itself and all its dependencies.

2. Unzip file to your wp-content/plugins folder.

3. Activate plugin at "Plugins" page inside your Wordpress admin.

4. Go to "Settings" -> "Uploadcare settings" and enter the public and secret keys for your account.
To receive your keys, create your FREE account at [Uploadcare](https://uploadcare.com/accounts/create/plan-based/2/)

5. Create new post and feel yourself proud!

Send us your feedback, feedback@uploadcare.com

== Screenshots ==

1. Uploadcare Widget.

== Usage ==

1. Begin adding a new post.

2. Press "Add Media" to insert media with the Uploadcare widget.

3. Upload a file using the widget.

4. Crop the file if you wish. Only the cropped area will be inserted.

5. Press "Store and Insert". The image will be inserted into your post. 

== Changelog ==

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
