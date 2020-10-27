# Changelog

All notable changes to this project will be documented in this file.

The format is based now on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).


## [2.7.2] — 2020-05-13

### Fixed

* Default CDN base was empty, now it points to an existing host.


## [2.7.1] — 2020-02-03

### Fixed

* Fix botched release.


## [2.7.0] — 2020-02-03

### Added

* CDN Base option
* Signed uploads option


## [2.6.1] — 2019-03-14

### Fixed

* Error with plugin initialization.


## 2.6.0 — 2018-06-07

### Added

* The `data-integration` attribute to the widget reporting its version together
  with the library version used.
* User Agent reports library and integration versions used.

### Changed

* Updated [uploadcare/uploadcare-php] to the version 2.2.1.
* User Agent reporting now uses a new default format.


## 2.5.3

### Fixed

* File duplication on upload
* Widget effects tab settings


## 2.5.2

* Fixed missed "Fine tuning" setup for widget
* Test up to Wordpress 4.9.4


## 2.5.1

* Fixed initialization warnings in utils.php module


## 2.5.0

* Test up to Wordpress 4.9.1
* Uploadcare widget updated to version 3.x
* Added In-browser image editing and filters
* Added storing Uploadcare images localy on your WordPress host


## 2.4.1

* Minor bug fix for usage jQuery.
* Test up to Wordpress 4.7.2


## 2.4.0

* Uploadcare widget updated to version 2.6.0
* Test up to Wordpress 4.4.2
* update uploadcare-php lib


## 2.3.2

* Fix "add via uploadcare" button in media library


## 2.3.1

* Fix plugin activation message on PHP 5.2-


## 2.3.0

* Uploadcare widget updated to version 1.4.2
* Test up to Wordpress 4.0
* Add 'de' locale
* Check PHP version and php-curl lib on plugin activation


## 2.2.0

* Uploadcare widget updated to version 1.2.0
* Add Flickr support
* Add 'da' locale
* Add source tab setting


## 2.1.0

* Uploadcare widget updated to version 1.0.1
* Update underlying uploadcare-php
* Ditch support of php-5.2
* Add Uploadcare button to default media library uploader


## 2.0.11

* Add custom post type "User Images"
* Add `[uploadcare]` shortcode, that shows widget for users. Uploaded images are attached to post
  and are saved as "User Images"
* Remove custom Featured Images meta box. Build in should work with attachments.
* Uploadcare widget updated to version 0.18.1


## 2.0.10

* Fix Featured Images.


## 2.0.9

* Uploadcare media library tab now shows all files from your Uploadcare account (project)
* Allow cropping when inserting images via Uploadcare media library tab
* Make Wordpress attachment when uploading file via "Add Media" button
  This should impove Uploadcare integration with Wordpress and 3rd party plugins


## 2.0.8

* Fix pagination in media tab


## 2.0.7

* Support featured images via Uploadcare
* New setting "Use Uploadcare for featured images".
* Bugfixes


## 2.0.6

* New setting "Allow multiupload".
* New setting "Uploadcare widget fine tuning".
* Uploadcare widget updated to version 0.12
* Bugfixes


## 2.0.5

* New setting "Insert image with url to the original image".


## 2.0.0

* New plugin with new widget (0.7) and manual crop.


## 1.0.6

* Widget update.
* Manual crop.


## 1.0.5

* Bugfix


## 1.0.4

* Bugfix


## 1.0.3

* More operations form validation
* Fixed files deletion.
* "Files" are shown as an Uploadcare logo.
* Header is not displayed for Wordpress 3.5, updated for new "Add Media"


## 1.0.2

* Operation values no longer nulled when operations type is changed.
* Insert files, not only images.
* Opration values no longer nulled when operations type is changed.


## 1.0.1

* Scale crop and Resize are radio buttons now, not checkboxes.
* Fixed script, showing "Store" button.


## 1.0.0

* Uploadcare widget updated to 0.5.0. Added facebook and instagr.am.
* "Store" button is hidden until file is selected.
* Uploaded files are saved in database.
* Uploadcare file list uses only saved files, not all the files from Uploadcare account.
* "Crop" operations is deleted.
* "Resize" and "Scale Crop" cannot be used at the same time.
* Minor fixes.


## 0.2.0

* New tab: Uploadcare Files * select previously uploaded files.
* "Uploadcare" admin menu: view and delet previously uploaded files.


## 0.1.1

* Added readme.txt


## 0.1.0

* Initial release. Implements Uploadcare tab.


<!--links -->

[uploadcare/uploadcare-php]: https://github.com/uploadcare/uploadcare-php

[Unreleased]: https://github.com/uploadcare/uploadcare-wordpress/compare/v2.7.2..HEAD
[2.7.2]: https://github.com/uploadcare/uploadcare-wordpress/compare/v2.7.1..v2.7.2
[2.7.1]: https://github.com/uploadcare/uploadcare-wordpress/compare/v2.7.0..v2.7.1
[2.7.0]: https://github.com/uploadcare/uploadcare-wordpress/compare/v2.6.1..v2.7.0
[2.6.1]: https://github.com/uploadcare/uploadcare-wordpress/compare/v2.6.0..v2.6.1
