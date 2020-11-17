# File Uploader by Uploadcare

<a href="https://uploadcare.com/?utm_source=github&utm_campaign=uploadcare-wordpress">
  <img align="right" width="64" height="64"
    src="https://ucarecdn.com/2f4864b7-ed0e-4411-965b-8148623aa680/uploadcare-logo-mark.svg"
    alt="">
</a>

This is a [File Uploader][uc-feature-widget] plugin by Uploadcare for [WordPress][wordpress].

The plugin allows WordPress users to upload media from their devices, camera, social media, cloud storage, and other destinations. It uses Uploadcare storage and CDN delivery with advanced features, like automatic resize and optimizations, enhance options, and more.

<!-- Misha: not sure if this article is relevant in the firsst paragraph/screen. It's a mediocre article looks like sponsored, better move it to the end -->
Check out this [article][wparena-article] for details.

[![GitHub release][badge-release-img]][badge-release-url]&nbsp;
[![Uploadcare stack on StackShare][badge-stack-img]][badge-stack-url]

* [Requirements](#requirements)
* [Install](#install)
* [Usage](#usage)
* [Configuration](#configuration)
  * [Plugin configuration](#plugin-configuration)
  * [File Uploader configuration](#widget-configuration)
* [Contributors](#contributors)
* [Security issues](#security-issues)
* [Feedback](#feedback)

## Requirements

- Wordpress 3.5+
- PHP 5.3+
- php-curl

## Install

### In Wordpress

<a href="https://wordpress.org/plugins/uploadcare/" title="Navigate to the plugin page">
  <img src="https://ucarecdn.com/a6ed4f07-46d4-45f1-9a2e-1bef04d9f21a/InstallFromWP.gif"
       width="888" alt="Installing WP plugin">
</a>

### Manual installation

1. Download the [latest release][github-releases] of a plugin source code.
1. Unzip it to `wp-content/plugins`.
1. Download the [Uploadcare PHP library][github-php]. It'll connect your WordPress media library with Uploadcare storage.
1. Unzip it to `wp-content/plugins/uploadcare/uploadcare-php`.
1. Activate the plugin in the "Plugins" menu in your WordPress admin account.
1. Create your [Uploadcare account][uc-account] and go to "Settings" -> "Uploadcare" and set your Public and Secret API Keys. These keys are used to identify an Uploadcare project your uploaded media will go to. For testing purposes, use `demopublickey` and `demoprivatekey`. All the test files will be accessible within 24 hours.

## Usage

1. Start creating a new post.
1. Click "Add Media" to insert a media with an Uploadcare [File Uploader][uc-widget-features].
1. Select files to upload.
1. When uploading images, you can [edit][uc-widget-image-processing] them right in your mobile or desktop web browser.
1. Click "Store and Insert" to add an image to the library and to your post.

## Configuration

### File Uploader configuration

You can customize the File Uploader to mach your website design. Also, you can define
allowed upload sources, implement file validation, and more.

Use the live [File Uploader sandbox][uc-widget-configure] as a starting point and check out the docs on [configuration][uc-docs-widget-config] and its [JavaScript API][uc-docs-widget-js-api].

## Contributors

This list of contributors is based on the git history of this repo.

Gray Hound <https://github.com/grayhound>

Dmitry Mukhin <https://github.com/dmitry-mukhin>

Nikolay Zherdev <https://github.com/ZNick1982>

Dmitry Petrov <https://github.com/dimaninc>

Zarema Khalilova <https://github.com/Zmoki>

Elijah <https://github.com/dayton1987>

Igor Debatur <https://github.com/igordebatur>

Roman Sedykh <https://github.com/rsedykh>

Siarhei Bautrukevich <https://github.com/bautrukevich>

[All contributors][github-contributors]

## Security issues

If you ran into any security implication when using Uploadcare libraries, please hit us up at [bugbounty@uploadcare.com][uc-email-bounty] or Hackerone.

We'll contact you shortly to fix the issue prior to any public disclosure.

## Feedback

Issues and PRs are welcome. You can provide your feedback or drop us a support
request at [hello@uploadcare.com][uc-email-hello].

[wordpress]: http://www.wordpress.org/
[wparena-article]: https://wparena.com/3-must-have-wordpress-plugins-to-start-a-photoblog/
[uc-account]: https://uploadcare.com/accounts/create/free/
[uc-widget-features]: https://uploadcare.com/features/widget/
[uc-widget-image-processing]: https://uploadcare.com/features/image_processing/
[uc-docs-widget-config]: https://uploadcare.com/docs/uploads/widget/config/?utm_source=github&utm_campaign=uploadcare-wordpress
[uc-docs-widget-js-api]: https://uploadcare.com/docs/api_reference/javascript/?utm_source=github&utm_campaign=uploadcare-wordpress
[uc-widget-configure]: https://uploadcare.com/widget/configure/?utm_source=github&utm_campaign=uploadcare-wordpress
[uc-feature-widget]: https://uploadcare.com/features/widget/?utm_source=github&utm_campaign=uploadcare-wordpress
[uc-email-bounty]: mailto:bugbounty@uploadcare.com
[uc-email-hello]: mailto:hello@uploadcare.com

[github-releases]: https://github.com/uploadcare/uploadcare-wordpress/releases
[github-php]: https://github.com/uploadcare/uploadcare-php/releases
[github-contributors]: https://github.com/uploadcare/uploadcare-wordpress/graphs/contributors

[badge-stack-img]: https://img.shields.io/badge/tech-stack-0690fa.svg?style=flat
[badge-stack-url]: https://stackshare.io/uploadcare/stacks/
[badge-release-img]: https://img.shields.io/github/release/uploadcare/uploadcare-wordpress.svg
[badge-release-url]: https://github.com/uploadcare/uploadcare-wordpress/releases
