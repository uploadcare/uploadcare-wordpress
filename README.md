# Wordpress plugin for Uploadcare

This is a plugin for [Wordpress][5] to work with [Uploadcare][1]

It's based on a [uploadcare-php][4] library.

## Requirements

- Wordpress 3.5+
- PHP 5.2+
- php-curl

## Install 

1. [Download the latest release][3]. The zip file contains the Wordpress plugin itself and all its dependencies.
2. Unzip file to your wp-content/plugins folder.
3. Activate plugin at "Plugins" page inside your Wordpress admin.
4. Go to "Settings" -> "Uploadcare settings" and enter the public and secret keys for your account.
   To receive your keys, create your FREE account at [Uploadcare](https://uploadcare.com/accounts/create/plan-based/2/)
5. Create new post and feel yourself proud!

Send us your feedback, feedback@uploadcare.com

## Usage

1. Begin adding a new post.
2. Press "Add Media" to insert media with the Uploadcare widget.
3. Upload a file using the widget.
4. Crop the file if you wish. Only the cropped area will be inserted.
5. Press "Store and Insert". The image will be inserted into your post. 

## Releases

**2.0.7** ([Download](http://downloads.wordpress.org/plugin/uploadcare.2.0.7.zip))
* support featured images vie Uploadcare
* New setting "Use Uploadcare for featured images".
* Bugfixes

**2.0.6** ([Download](http://downloads.wordpress.org/plugin/uploadcare.2.0.6.zip))
* New setting "Allow multiupload".
* New setting "Uploadcare widget fine tuning".
* Uploadcare widget updated to version 0.12
* Bugfixes

**2.0.5** ([Download](http://downloads.wordpress.org/plugin/uploadcare.2.0.5.zip))
* New setting "Insert image with url to the original image".

**2.0** 
* New plugin with new widget (0.7) and manual crop.

**1.0.6**
* Widget update.
* Manual crop.

**1.0.5**
* Bugfix

**1.0.4**
* Bugfix

**1.0.3**
* More operations form validation
* Fixed files deletion.
* "Files" are shown as an Uploadcare logo.
* Header is not displayed for Wordpress 3.5, updated for new "Add Media"

**1.0.2**
* Operation values no longer nulled when operations type is changed.
* Insert files, not only images.
* Opration values no longer nulled when operations type is changed.

**1.0.1**
* Scale crop and Resize are radio buttons now, not checkboxes.
* Fixed script, showing "Store" button.

**1.0.0**
* Uploadcare widget updated to 0.5.0. Added facebook and instagr.am.
* "Store" button is hidden until file is selected.
* Uploaded files are saved in database.
* Uploadcare file list uses only saved files, not all the files from Uploadcare account.
* "Crop" operations is deleted.
* "Resize" and "Scale Crop" cannot be used at the same time.
* Minor fixes.

**0.2.0**
* New tab: Uploadcare Files - select previously uploaded files.
* "Uploadcare" admin menu: view and delet previously uploaded files.

**0.1.1**
* readme.txt added

**0.1.0**
* Initial release. Implements Uploadcare tab.

[1]: https://uploadcare.com/
[2]: https://uploadcare.com/documentation/reference/basic/cdn.html
[3]: https://github.com/uploadcare/uploadcare-wordpress/releases
[4]: https://github.com/uploadcare/uploadcare-php
[5]: http://wordpress.org/
