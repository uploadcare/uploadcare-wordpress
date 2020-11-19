# Uploadcare WordPress File Uploader and Adaptive Delivery

Uploadcare, all-round media storage, management and delivery solution, breaks many of the standard WordPress Media Library limitations. Upload and store files of any size from any device or cloud service. Serve responsive images with lazy loading with zero coding. Improve your WordPress site performance with a single click.

This plugin allows WordPress authors upload images and other files with Uploadcare File Uploader while creating posts and pages. You can upload from local disks, camera, social media, and many other upload sources. Images and other files will be delivered with Uploadcare CDN. Serve images with Adaptive Delivery that creates responsive images and adapts them for any device.

[![Build Status][travis-img]][travis] [![Uploadcare stack on StackShare][stack-img]][stack]  

[travis-img]: https://api.travis-ci.org/uploadcare/uploadcare-wordpress.svg
[travis]: https://travis-ci.org/uploadcare/uploadcare-wordpress
[stack-img]: http://img.shields.io/badge/tech-stack-0690fa.svg?style=flat
[stack]: https://stackshare.io/uploadcare/stacks/

* [Features](#features)
* [Requirements](#requirements)
* [Install](#install)
* [Usage](#usage)
* [Useful links](#useful-links)

## Features

* Upload files of any type (image, video, document, archive) and size (up to 5 TB).
* Upload from any device or cloud: Facebook, Instagram, Flickr, Google Drive, Dropbox, and others.
* Transfer your existing Media Library to your Uploadcare storage with no risk of data loss.
* Uploadcare CDN will serve images faster in all parts of the world. Adaptive Delivery analyzes users' context and serves images in a suitable resolution, quality and compression, pixel density, etc.
* Specify your custom CDN domain, and use a Secure Uploading feature to control over who and when can upload files.
* Compatible with a standard WordPress image editor.

## Requirements

- Wordpress 5+
- PHP 5.6+
- php-curl
- php-json

## Install

### Fastest way

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

* When adding an image while editing a page or in Media Library, choose the "Upload via Uploadcare" option.
* Use a built-in image editor when needed.
* If you accidentally upload a file using a standard WordPress option, you can easily transfer them to Uploadcare to use Adaptive Delivery and other features.

## Useful links

[Uploadcare documentation](https://uploadcare.com/docs/?utm_source=github&utm_medium=referral&utm_campaign=uploadcare-wordpress)  
[Changelog](https://wordpress.org/plugins/uploadcare/#developers)  
[Contributing guide](https://github.com/uploadcare/.github/blob/master/CONTRIBUTING.md)  
[Security policy](https://github.com/uploadcare/uploadcare-wordpress/security/policy)  
[Support](https://github.com/uploadcare/.github/blob/master/SUPPORT.md)  
