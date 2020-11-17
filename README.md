# Uploadcare WordPress File Uploader and Adaptive Delivery

Uploadcare, all-round media storage, management and delivery solution, breaks many of the standard WordPress Media Library limitations.

This plugin allows WordPress authors upload images and other files with Uploadcare File Uploader while creating posts and pages.

File Uploader supports local disk, camera, social media, and many other upload sources. Once uploaded, your files will be delivered with Uploadcare CDN along with Adaptive Delivery that automatically creates responsive images and adapts them for any device and browser.

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
* Every image you upload and insert in a post will load faster. Adaptive Delivery automatically analyzes users' context and serves images tailored to their screens: resolution, quality and compression, pixel density, etc.
* Transfer your existing media library to Uploadcare risk free.
* Upload from any device or cloud: Facebook, Instagram, Flickr, Google Drive, Evernote, Box, Skydrive, Dropbox, VK.
* Custom CDN domain, secure control over who and when can upload files.
* Includes in-browser image editor for uploaded files, where you can crop, enhance, etc.

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
6. Go to "Settings" -> "Uploadcare" and follow instructions.

## Usage

* Whenever you add an image (via Image or Gallery block or via Media Library) — choose Upload via Uploadcare.
* You can use an image editor to modify images.
* See that every image you add to articles is automatically optimized: small resolution for mobile, lower quality for poor connection, etc. It makes sure that each and every user gets the page content fast.
* If you accidentally uploaded file to a local Media Library, you'll see a notification on top of the Admin Dashboard — just click it. Files from local Media Library won't work with the adaptive image delivery system.

## Useful links

[Uploadcare documentation](https://uploadcare.com/docs/?utm_source=github&utm_medium=referral&utm_campaign=uploadcare-wordpress)  
[Changelog](https://wordpress.org/plugins/uploadcare/#developers)  
[Contributing guide](https://github.com/uploadcare/.github/blob/master/CONTRIBUTING.md)  
[Security policy](https://github.com/uploadcare/uploadcare-wordpress/security/policy)  
[Support](https://github.com/uploadcare/.github/blob/master/SUPPORT.md)  