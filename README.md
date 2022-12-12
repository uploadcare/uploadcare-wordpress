# Uploadcare WordPress File Uploader and Adaptive Delivery

Uploadcare, all-round media upload, storage, management, and delivery solution, breaks many WordPress Media Library limitations. Upload large files from many sources including social media and cloud services. Insert them to WordPress posts and serve responsive lazy loaded images with Uploadcare CDN to improve your WordPress site performance.

[![Build Status][travis-img]][travis] [![Uploadcare stack on StackShare][stack-img]][stack]

[travis-img]: https://api.travis-ci.com/uploadcare/uploadcare-wordpress.svg
[travis]: https://travis-ci.com/uploadcare/uploadcare-wordpress
[stack-img]: http://img.shields.io/badge/tech-stack-0690fa.svg?style=flat
[stack]: https://stackshare.io/uploadcare/stacks/

* [Features](#features)
* [Requirements](#requirements)
* [Install](#install)
* [Usage](#usage)
* [Useful links](#useful-links)

## Features

* Upload files of any type (image, video, document, archive) and size (up to 5 TB).
* Upload from your device, URL, social network (Facebook, Instagram, VK, Flickr), and cloud (Dropbox, Google Drive, Google Photos, OneDrive, Box, Huddle, Evernote).
* Insert images into your posts, and place other files and archives for download.
* Uploadcare CDN serves images faster in all parts of the world. Adaptive Delivery analyzes users' context and serves images in a suitable format, resolution, compression, which closes all image-related frontend tasks.
* Transfer your existing Media Library to your Uploadcare storage with no risk of data loss.
* Specify your custom CDN domain, and use a Secure Uploading feature to control who and when can upload files.
* Compatible with a standard WordPress image editor.

## Requirements

- Wordpress 5+
- PHP 7.4+
- php-curl
- php-json
- php-dom

## Install

### The fastest way

Install from the [WordPress plugins directory](https://wordpress.org/plugins/uploadcare/).

<a href="https://wordpress.org/plugins/uploadcare/" title="Navigate to the plugin page">
  <img src="https://ucarecdn.com/a6ed4f07-46d4-45f1-9a2e-1bef04d9f21a/InstallFromWP.gif"
       width="888" alt="Installing WP plugin">
</a>

### Manual installation

1. Download the [latest release][github-releases]. The zip file contains the Wordpress plugin.
2. Unzip the archive to your `wp-content/plugins` folder.
3. Run `composer install` (install [Composer](https://getcomposer.org/download/)).
4. Run `yarn && yarn build` (install [Node](https://nodejs.org/en/download/) and [Yarn](https://classic.yarnpkg.com/en/docs/install/)).
5. Activate the plugin in the "Plugins" menu in your WordPress admin account.
6. Go to "Settings" -> "Uploadcare" and follow the instructions.

## Usage

* To add an image while editing a post or a page, choose "Uploadcare image" block. Also, you can upload any file directly to your Media Library with “Upload with Uploadcare” button — it’ll be hosted and delivered with Uploadcare.
* Use a built-in image editor when needed.
* If you accidentally upload a file using a standard WordPress option, you can easily transfer them to Uploadcare to use Adaptive Delivery and other features.

## Useful links

[Uploadcare documentation](https://uploadcare.com/docs/?utm_source=github&utm_medium=referral&utm_campaign=uploadcare-wordpress)  
[Changelog](https://wordpress.org/plugins/uploadcare/#developers)  
[Contributing guide](https://github.com/uploadcare/.github/blob/master/CONTRIBUTING.md)  
[Security policy](https://github.com/uploadcare/uploadcare-wordpress/security/policy)  
[Support](https://github.com/uploadcare/.github/blob/master/SUPPORT.md)
