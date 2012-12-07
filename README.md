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

[1]: https://uploadcare.com/
[2]: https://uploadcare.com/documentation/reference/basic/cdn.html
[3]: https://github.com/uploadcare/uploadcare-wordpress/downloads
[4]: https://github.com/uploadcare/uploadcare-php
[5]: http://wordpress.org/