=== Plugin Name ===
Contributors: grayhound1
Tags: media upload, file handling
Requires at least: 3.5+
Tested up to: 3.5
Stable tag: 2.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Uploadcare provides media uploading, processing and CDN for your blog.
So, you can upload even very large images, crop and resize it.
And best thing ever: with Uploadcare you can choose files straight from Facebook, Instagram and Flickr, not only from your computer.

Check [Uploadcare.com](https://uploadcare.com/) for more information.


== Description ==

Plugin implements a way to upload images and files using uploadcare.com service.

== Installation ==

1. Download the latest release. The zip file contains the Wordpress plugin itself and all dependencies.

2. Unzip file to your wp-content/plugins folder.

3. Activate plugin at "Plugins" page inside your Wordpress admin.

4. Go to "Settings" -> "Uploadcare settings" and provide public and secret key for your account.
To get your keys, create your FREE account at [Uploadcare](https://uploadcare.com/accounts/create/plan-based/2/)

5. Create new post and feel yourself proud!

Give us your feedback, feedback@uploadcare.com

== Screenshots ==

1. Uploadcare Widget.

== Usage ==

1. Start adding new post.

2. Press "Add Media" to insert some media using Uploadcare widget.

3. Upload a file using widget.

4. Crop the file if you want. Only cropped area will be inserted.

5. Press "Store and Insert". The image will be inserted in your post. 

== Changelog ==

= 2.0 =
* New widget version, plugin refactored and ready to go.

= 1.0.5 = 
* Small issues fixed

= 1.0.4 =
* Small issues fixed

= 1.0.3 = 
* More operations form validation
* Fixed files deletion.
* "Files" are shown as an Uploadcare logo.
* Header is not displayed for Wordpress 3.5, updated for new "Add Media"

= 1.0.2 = 
* Operation values no longer nulled when operations type is changed.
* Insert files, not only images.
* Opration values no longer nulled when operations type is changed.

= 1.0.1 =
* Scale crop and Resize are radio buttons now, not checkboxes.
* Fixed script, showing "Store" button.

= 1.0.0 =
* Uploadcare widget updated to 0.5.0. Added facebook and instagr.am.
* "Store" button is hidden until file is selected.
* Uploaded files are saved in database.
* Uploadcare file list uses only saved files, not all the files from Uploadcare account.
* "Crop" operations is deleted.
* "Resize" and "Scale Crop" cannot be used at the same time.
* Minor fixes.


= 0.2.0 =
* New tab: Uploadcare Files - select previously uploaded files.
* "Uploadcare" admin menu: view and delet previously uploaded files.

= 0.1.1 = 
* Wordpress reame added.
* Preview is at the bottom of the form.
* Scale Crop "center" options is defaulted to "checked".

= 0.1.0 = 
* Initial release
