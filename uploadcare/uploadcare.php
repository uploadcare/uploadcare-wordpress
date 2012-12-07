<?php
/*
Plugin Name: Uploadcare
Plugin URI: http://github.com/uploadcare/uploadcare-php-wordpress
Description: Implements a way to use Uploadcare inside you Wordpress blog.
Version: 1.0.0
Author: Uploadcare
Author URI: http://uploadcare.com/
License: GPL2
*/

if ( is_admin() )
	require_once dirname( __FILE__ ) . '/admin.php';

require_once 'uploadcare-php/uploadcare/lib/5.2/Uploadcare.php';

function uploadcare_media_menu($tabs) {
	$newtab = array('uploadcare' => __('Uploadcare', 'uploadcare'));
	return array_merge($newtab, $tabs);
}
function uploadcare_media_menu_default_tab() {
	return 'uploadcare';
}
add_filter('media_upload_tabs', 'uploadcare_media_menu');
add_filter('media_upload_default_tab', 'uploadcare_media_menu_default_tab');

function uploadcare_media_process() {
	require_once 'uploadcare_media_menu_handle.php';
}
function uploadcare_media_menu_handle() {
	return wp_iframe('uploadcare_media_process');
}
add_action('media_upload_uploadcare', 'uploadcare_media_menu_handle');