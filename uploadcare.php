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
  $context = '<a class="button" style="padding-left: .4em;" href="javascript: uploadcareMediaButton(); "><span class="wp-media-buttons-icon" style="vertical-align: text-bottom; background: url(\''.$img.'\') no-repeat top left"></span>Add Media</a>';
  $context .= '<a href="#" class="button insert-media add_media" data-editor="content" title="Wordpress Media Library"><span class="wp-media-buttons-icon"></span>Wordpress Media Library</a>';
  $context .= $css_hook;
  $script .= "
<script type=\"text/javascript\">UPLOADCARE_CROP = true;</script>
".$api->widget->getScriptTag()."
<script type=\"text/javascript\">
function ucEditFile(file_id) {
  try{tb_remove();}catch(e){};
  var file = uploadcare.fileFrom('uploaded', file_id);
  var dialog = uploadcare.openDialog(file).done(ucFileDone);
}  
function uploadcareMediaButton() {
  var dialog = uploadcare.openDialog().done(ucFileDone);
};  
function ucFileDone(file) {
  file.done(function(fileInfo) {
    _file_id = fileInfo.uuid;
    url = fileInfo.cdnUrl;
    var data = {
      'action': 'uploadcare_handle',
      'file_id': _file_id
    };
    jQuery.post(ajaxurl, data, function(response) {
      if (fileInfo.isImage) {
        window.send_to_editor('<a href=\"https://ucarecdn.com/'+fileInfo.uuid+'/\"><img src=\"'+url+'\" /></a>');
      } else {
        window.send_to_editor('<a href=\"'+url+'\">'+fileInfo.name+'</a>');
      }  
    });
  });  
};             
 </script>
 ";
 $context .= $script;

  return $context;
}

function uploadcare_handle() {
  global $wpdb;
  $public_key = get_option('uploadcare_public');
  $secret_key = get_option('uploadcare_secret');
  $api = new Uploadcare_Api($public_key, $secret_key);
  $file_id = $_POST['file_id'];
  $file = $api->getFile($file_id);
  $file->store();
  $result = $wpdb->insert($wpdb->prefix.'uploadcare', array('id' => 'NULL', 'file_id' => $file_id, 'filename' => $file->data['original_filename'], 'is_file' => $file->data['is_image'] ? 0 : 1));
  die();
}

function remove_media_tab($strings) {
  unset( $strings['insertMediaTitle'] ); //Insert Media
  unset( $strings['uploadFilesTitle'] ); //Upload Files
  $strings['mediaLibraryTitle'] = 'Old Media Library';
  unset( $strings['createGalleryTitle'] ); //Create Gallery
  unset( $strings['setFeaturedImageTitle'] ); //Set Featured Image
  unset( $strings['insertFromUrlTitle'] ); //Insert from URL
  return $strings;
}

function uploadcare_install() {
  global $wpdb;
  $table_name = $wpdb->prefix . "uploadcare";
  $sql = "CREATE TABLE $table_name (
  id mediumint(9) NOT NULL AUTO_INCREMENT,
  file_id varchar(200) DEFAULT '' NOT NULL,
  is_file tinyint(1) DEFAULT 0 NOT NULL,
  filename varchar(200) DEFAULT '' NOT NULL,
  UNIQUE KEY id (id)
  );";

  require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
  dbDelta($sql);
}

function uploadcare_uninstall() {
  global $wpdb;
  $thetable = $wpdb->prefix."uploadcare";
  $wpdb->query("DROP TABLE IF EXISTS $thetable");
}

function uploadcare_media_menu($tabs) {
  $newtab = array(
    'uploadcare_files' => __('Uploadcare', 'uploadcare_files')
  );
  return array_merge($newtab, $tabs);
}

function uploadcare_media_menu_default_tab() {
  return 'uploadcare_files';
}

function uploadcare_media_files() {
  global $wpdb;
  require_once 'uploadcare_media_files_menu_handle.php';
}

function uploadcare_media_files_menu_handle() {
  return wp_iframe('uploadcare_media_files');
}

register_activation_hook(__FILE__, 'uploadcare_install');
register_deactivation_hook(__FILE__, 'uploadcare_uninstall');
add_filter('media_view_strings','remove_media_tab');
add_action('media_buttons_context', 'uploadcare_add_media');
add_action('wp_ajax_uploadcare_handle', 'uploadcare_handle');
add_filter('media_upload_tabs', 'uploadcare_media_menu');
add_filter('media_upload_default_tab', 'uploadcare_media_menu_default_tab');
add_action('media_upload_uploadcare_files', 'uploadcare_media_files_menu_handle');