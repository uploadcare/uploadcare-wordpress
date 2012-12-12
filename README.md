# Wordpress plugin for Uploadcare

This is a plugin for [Wordpress][5] to work with [Uploadcare][1]

It's based on a [uploadcare-php][4] library.

## Requirements

- Wordpress 3.3+
- PHP 5.2+

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

**0.2.0** ([Download](https://ucarecdn.com/a95456f7-c407-4079-9b4e-64e7b1d8a4b3/uploadcare-wordpress-0.2.0.zip))
* New tab: Uploadcare Files - select previously uploaded files.
* "Uploadcare" admin menu: view and delet previously uploaded files.

**0.1.1** ([Download](https://ucarecdn.com/d7bf44ad-b9db-4a3f-a51a-77a25a06490c/uploadcare-wordpress_0.1.1.zip))
* readme.txt added

**0.1.0** ([Download](https://ucarecdn.com/d259b9f9-300e-43d0-9f39-53469d787a16/uploadcare-wordpress_0.1.0.zip))
* Initial release. Implements Uploadcare tab.

[1]: https://uploadcare.com/
[2]: https://uploadcare.com/documentation/reference/basic/cdn.html
[3]: https://github.com/uploadcare/uploadcare-wordpress/#releases
[4]: https://github.com/uploadcare/uploadcare-php
[5]: http://wordpress.org/