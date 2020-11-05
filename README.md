# Uploadcare WordPress File Uploader and Adaptive Delivery

Upload and store any file of any size from any device or cloud. No more slow downs when serving your images with automatic responsiviness and lazy loading. Improve your WP performance to boost Customer Experience and SEO.

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

* Upload any file (image, document, archive) of any size.
* Every uploaded image added to an article loads faster because Uploadcare automatically analyzes users' context and serve image tailored to their screens: resolution, quality, compression, density.
* Transfer your existing media library to Uploadcare risk free.
* Upload from any device or cloud: Facebook, Instagram, Flickr, Google Drive, Evernote, Box, Skydrive, Dropbox, VK.
* Custom CDN domain, secure control over who and when can upload files.
* Compatible with default image editor.

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

1. Download the [latest release][github-releases]. The zip file contains the Wordpress plugin itself.
2. Unzip file to your `wp-content/plugins` folder.
3. Run `composer install` (install [Composer](https://getcomposer.org/download/)).
4. Run `yarn && yarn build` (install [Node](https://nodejs.org/en/download/) and [Yarn](https://classic.yarnpkg.com/en/docs/install/)).
5. Activate the plugin once it is installed.
6. Go to "Settings" -> "Uploadcare" and follow instructions.

## Usage

* Whenever you add an image (via Image or Gallery block or via Media Library) — choose Upload via Uploadcare.
* You can use default image editor to modify images.
* See that every image you add to articles is automatically optimized: small resolution for mobile, lower quality for poor connection, etc. It makes sure that each and every user gets your contect fast.
* If you accidentally uploaded file to a local Media Library, you'll see notification on the top of Admin Dashboard — just click it. Files from local Media Library don't work with adaptive image delivery system.

## Useful links

[Uploadcare documentation](https://uploadcare.com/docs/?utm_source=github&utm_medium=referral&utm_campaign=uploadcare-wordpress)  
[Changelog](https://wordpress.org/plugins/uploadcare/#developers)  
[Contributing guide](https://github.com/uploadcare/.github/blob/master/CONTRIBUTING.md)  
[Security policy](https://github.com/uploadcare/uploadcare-wordpress/security/policy)  
[Support](https://github.com/uploadcare/.github/blob/master/SUPPORT.md)  