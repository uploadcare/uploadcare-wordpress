# Wordpress plugin for Uploadcare

This is a plugin for [Wordpress][5] to work with [Uploadcare][1]

It's based on a [uploadcare-php][4] library.

## Requirements

- Wordpress 3.3+
- PHP 5.2+
- php-curl

## Install 

[Download the latest release][3]. The zip file contains the Wordpress plugin itself and all dependencies.

Uzip file to your wp-content/plugins folder.

Activate plugin at "Plugins" page inside your Wordpress admin.

Go to "Settings" -> "Uploadcare settings" and provide public and secret key for your account.

## Usage

Start adding new post.

Press "Upload/Insert" to insert some media. You will see, that new tab "Uploadcare" is available.

Upload a file using widget and press "Store File". 

When the file is stored a new page with file operations will be available.

Apply operations and press "Insert Into Post". The image will be inserted in your post.

[More information on file operations can be found here][2]

## Releases

**1.0.5** ([Download](https://ucarecdn.com/cdd3a8d9-28d1-44f7-85ec-b54f0e4cf30b/uploadcare-wordpress_1.0.5.zip))
* Bugfix

**1.0.4** ([Download](https://ucarecdn.com/3cb08670-5b70-4a21-8ab9-ed5a072822a7/uploadcare-wordpress_1.0.4.zip))
* Bugfix

**1.0.3** ([Download](https://ucarecdn.com/b32c8669-a38a-48b9-8636-0aa442bba6a7/uploadcare-wordpress_1.0.3.zip))
* More operations form validation
* Fixed files deletion.
* "Files" are shown as an Uploadcare logo.
* Header is not displayed for Wordpress 3.5, updated for new "Add Media"

**1.0.2** ([Download](https://ucarecdn.com/fa548bf1-45f9-4e09-b942-bde7b5e5616e/uploadcare-wordpress_1.0.2.zip))
* Operation values no longer nulled when operations type is changed.
* Insert files, not only images.
* Opration values no longer nulled when operations type is changed.

**1.0.1** ([Download](https://ucarecdn.com/56f764a1-ce29-4417-8fae-480d97d024e5/uploadcare-wordpress_1.0.1.zip))
* Scale crop and Resize are radio buttons now, not checkboxes.
* Fixed script, showing "Store" button.

**1.0.0** ([Download](https://ucarecdn.com/13433d46-96ac-497c-a2f3-f2634fb27fcd/uploadcare-wordpress_1.0.0.zip))
* Uploadcare widget updated to 0.5.0. Added facebook and instagr.am.
* "Store" button is hidden until file is selected.
* Uploaded files are saved in database.
* Uploadcare file list uses only saved files, not all the files from Uploadcare account.
* "Crop" operations is deleted.
* "Resize" and "Scale Crop" cannot be used at the same time.
* Minor fixes.

**0.2.0** ([Download](https://ucarecdn.com/a95456f7-c407-4079-9b4e-64e7b1d8a4b3/uploadcare-wordpress-0.2.0.zip))
* New tab: Uploadcare Files - select previously uploaded files.
* "Uploadcare" admin menu: view and delet previously uploaded files.

**0.1.1** ([Download](https://ucarecdn.com/d7bf44ad-b9db-4a3f-a51a-77a25a06490c/uploadcare-wordpress_0.1.1.zip))
* readme.txt added

**0.1.0** ([Download](https://ucarecdn.com/d259b9f9-300e-43d0-9f39-53469d787a16/uploadcare-wordpress_0.1.0.zip))
* Initial release. Implements Uploadcare tab.

[1]: https://uploadcare.com/
[2]: https://uploadcare.com/documentation/reference/basic/cdn.html
[3]: https://github.com/uploadcare/uploadcare-wordpress#releases
[4]: https://github.com/uploadcare/uploadcare-php
[5]: http://wordpress.org/
