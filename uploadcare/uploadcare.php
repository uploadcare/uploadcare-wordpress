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

function uploadcare_install() {
	global $wpdb;
	$table_name = $wpdb->prefix . "uploadcare";
	$sql = "CREATE TABLE $table_name (
	id mediumint(9) NOT NULL AUTO_INCREMENT,
	file_id varchar(200) DEFAULT '' NOT NULL,
	UNIQUE KEY id (id)
	);";
	
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($sql);		
}

register_activation_hook(__FILE__,'uploadcare_install');

function uploadcare_media_menu($tabs) {
	$newtab = array(
			'uploadcare' => __('Uploadcare', 'uploadcare'),
			'uploadcare_files' => __('Uploadcare Files', 'uploadcare_files')
	);
	return array_merge($newtab, $tabs);
}
function uploadcare_media_menu_default_tab() {
	return 'uploadcare';
}
add_filter('media_upload_tabs', 'uploadcare_media_menu');
add_filter('media_upload_default_tab', 'uploadcare_media_menu_default_tab');

////
function uploadcare_media_process() {
	global $wpdb;
	require_once 'uploadcare_media_menu_handle.php';
}
function uploadcare_media_menu_handle() {
	return wp_iframe('uploadcare_media_process');
}
add_action('media_upload_uploadcare', 'uploadcare_media_menu_handle');
/////

/////
function uploadcare_media_files() {
	global $wpdb;
	require_once 'uploadcare_media_files_menu_handle.php';
}
function uploadcare_media_files_menu_handle() {
	return wp_iframe('uploadcare_media_files');
}
add_action('media_upload_uploadcare_files', 'uploadcare_media_files_menu_handle');
/////


function uploadcare_files() {
	global $wpdb;
	if (!current_user_can('upload_files'))  {
		wp_die( __('You do not have sufficient permissions to access this page.') );
	}
	require_once 'uploadcare_files.php';
}
function uploadcare_files_menu() {
	add_menu_page('Uploadcare', 'Uploadcare', 'upload_files', 'uploadcare-files', 'uploadcare_files', plugins_url('uploadcare/logo.png'), 15);
}
add_action('admin_menu', 'uploadcare_files_menu');