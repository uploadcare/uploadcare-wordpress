=== Plugin Name ===
Contributors: grayhound1
Tags: media upload, file handling
Requires at least: 3.3+
Tested up to: 3.4
Stable tag: 1.0.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin adds a way to work with uploadcare.com service and to insert images to your post using it.

== Description ==

This plugin adds a new tab "Uploadcare" to your "Upload/Insert" at post edit/creating.

Plugin implements a way to upload images and files using uploadcare.com service.

It also gives a way to use CDN operations after file is uploaded.

You can find more information about [Uploadcare](http://uploadcare.com/).

== Installation ==

Create your personal account at [Uploadcare](http://uploadcare.com/)

Download the latest release. The zip file contains the Wordpress plugin itself and all dependencies.

Uzip file to your wp-content/plugins folder.

Activate plugin at "Plugins" page inside your Wordpress admin.

Go to "Settings" -> "Uploadcare settings" and provide public and secret key for your account.

== Screenshots ==

1. Uploadcare Widget.

2. Uploadcare file operations forms with preview.

3. Uploadcare file list.

== Usage ==

Start adding new post.

Press "Upload/Insert" to insert some media. You will see, that new tab "Uploadcare" is available.

Upload a file using widget and press "Store File". 

When the file is stored a new page with file operations will be available.

Apply operations and press "Insert Into Post". The image will be inserted in your post.

== Changelog ==

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