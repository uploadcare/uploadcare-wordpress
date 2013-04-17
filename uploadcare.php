<?php
/*
Plugin Name: Uploadcare
Plugin URI: http://github.com/uploadcare/uploadcare-php-wordpress
Description: Implements a way to use Uploadcare inside you Wordpress blog.
Version: dev
Author: Uploadcare
Author URI: http://uploadcare.com/
License: GPL2
*/

if ( is_admin() )
	require_once dirname( __FILE__ ) . '/admin.php';

require_once 'uploadcare-php/uploadcare/lib/5.2/Uploadcare.php';

function uploadcare_add_media($context) {
  $public_key = get_option('uploadcare_public');
  $secret_key = get_option('uploadcare_secret');
  $api = new Uploadcare_Api($public_key, $secret_key);
  
  $img = plugins_url('uploadcare/logo.png');
  $css_hook = '<style tyle="text/css">#wp-content-media-buttons>a:first-child { display: none }</style>';
  $context = '<a class="button" style="padding-left: .4em;" href="javascript: uploadcareMediaButton(); "><span class="wp-media-buttons-icon" style="background: url(\''.$img.'\') no-repeat top left"></span>Add Media</a>';
  $context .= '<a href="#" class="button insert-media add_media" data-editor="content" title="Wordpress Media Library"><span class="wp-media-buttons-icon"></span>Wordpress Media Library</a>';
  $context .= $css_hook;
  $script .= "
    <script type=\"text/javascript\">
    UPLOADCARE_CROP = true;
    </script>
    ".$api->widget->getScriptTag()."
    <script type=\"text/javascript\">
    function uploadcareMediaButton() {  
      var dialog = uploadcare.openDialog().done(function(file) {
        file.done(function(fileInfo) {
          _file_id = fileInfo.uuid;
          url = fileInfo.cdnUrl;
          jQuery(document).ready(function($) {
          	var data = {
          		action: 'uploadcare_handle',
          		file_id: _file_id
          	};
          	jQuery.post(ajaxurl, data, function(response) {
              window.send_to_editor('<img src=\"'+url+'\" />', 'unfiltered_html');
          	});
          });      
        });
      });   
    };
    </script>
  ";
  $context .= $script;
  
  return $context;
}

add_action('wp_ajax_uploadcare_handle', 'uploadcare_handle');

function uploadcare_handle() {
  $public_key = get_option('uploadcare_public');
  $secret_key = get_option('uploadcare_secret');
  $api = new Uploadcare_Api($public_key, $secret_key);
  $file_id = $_POST['file_id'];
  $file = $api->getFile($file_id);
  $file->store();
  die();
}

function uploadcare_media_menu_default_tab() {
  return 'library';
}

function remove_media_tab($strings) {
    unset( $strings['insertMediaTitle'] ); //Insert Media
    unset( $strings['uploadFilesTitle'] ); //Upload Files
    unset( $strings['mediaLibraryTitle'] ); //Media Library
    unset( $strings['createGalleryTitle'] ); //Create Gallery
    unset( $strings['setFeaturedImageTitle'] ); //Set Featured Image
    unset( $strings['insertFromUrlTitle'] ); //Insert from URL
    return $strings;
}

add_filter('media_view_strings','remove_media_tab');
add_action('media_buttons_context', 'uploadcare_add_media');
add_filter('media_upload_default_tab', 'uploadcare_media_menu_default_tab');