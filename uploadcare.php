<?php
/*
Plugin Name: Uploadcare
Plugin URI: http://github.com/uploadcare/uploadcare-wordpress
Description: Implements a way to use Uploadcare inside you Wordpress blog.
Version: 2.999.dev
Author: Uploadcare
Author URI: https://uploadcare.com/
License: GPL2
*/

if ( is_admin() )
  require_once dirname( __FILE__ ) . '/admin.php';

require_once 'uploadcare-php/uploadcare/lib/5.2/Uploadcare.php';

function add_uploadcare_js_to_admin($hook) {
  if('post.php' != $hook && 'post-new.php' != $hook) {
    // add js only on add and edit pages
    return;
  }
  // FIXME: this does not work with symlinks
  wp_enqueue_script('my_custom_script', plugins_url('uploadcare-wp.js', __FILE__));
}
add_action( 'admin_enqueue_scripts', 'add_uploadcare_js_to_admin' );

// function uploadcare_gallery_func($attrs, $content, $tag) {
//   $out  = "<div class=\"fotorama\">";
//   foreach(explode("\n", strip_tags($content)) as $url) {
//     $out .= '<img src="' . $url . '" />';
//   }
//   $out .= "</div>";
//   return $out;
// }
// add_shortcode('uc_gallery', 'uploadcare_gallery_func');

function uploadcare_add_media($context) {
  $public_key = get_option('uploadcare_public');
  $secret_key = get_option('uploadcare_secret');
  $original = get_option('uploadcare_original');
  $multiupload = get_option('uploadcare_multiupload');
  $finetuning = get_option('uploadcare_finetuning');
  $api = new Uploadcare_Api($public_key, $secret_key);

  $img = plugins_url('logo.png', __FILE__);
  $css_hook = '<style tyle="text/css">#wp-content-media-buttons>a:first-child { display: none }</style>';
  $context = '<div style="float: left"><a class="button" style="padding-left: .4em;" href="javascript: uploadcareMediaButton(); "><span class="wp-media-buttons-icon" style="padding-right: 2px; vertical-align: text-bottom; background: url(\''.$img.'\') no-repeat 0px 0px;"></span>Add Media</a></div>';
  $context .= '<div style="float: left"><a href="#" class="button insert-media add_media" data-editor="content" title="Wordpress Media Library"><span class="wp-media-buttons-icon"></span>Wordpress Media Library</a></div>';
  $context .= $css_hook;
  $script .= "
<script type=\"text/javascript\">
UPLOADCARE_CROP = true;\n";
  if ($original) {
    $script .= "UPLOADCARE_WP_ORIGINAL = true;\n";
  } else {
    $script .= "UPLOADCARE_WP_ORIGINAL = false;\n";
  }
  if ($multiupload) {
    $script .= "UPLOADCARE_MULTIPLE = true;\n";
  } else {
    $script .= "UPLOADCARE_MULTIPLE = false;\n";
  }
  if($finetuning) {
    $script .= stripcslashes($finetuning);
  }
  $script .= "</script>" . $api->widget->getScriptTag();
  $context .= $script;
  return $context;
}

function uploadcare_handle() {
  // save uploadcare file to wp db
  global $wpdb;
  $public_key = get_option('uploadcare_public');
  $secret_key = get_option('uploadcare_secret');
  $api = new Uploadcare_Api($public_key, $secret_key);
  $file_id = $_POST['file_id'];
  $file = $api->getFile($file_id);
  $file->store();
  $result = $wpdb->insert($wpdb->prefix.'uploadcare',
      array('id' => 'NULL',
            'file_id' => $file_id,
            'filename' => $file->data['original_filename'],
            'is_file' => $file->data['is_image'] ? 0 : 1));
  die();
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
add_action('media_buttons_context', 'uploadcare_add_media');
add_action('wp_ajax_uploadcare_handle', 'uploadcare_handle');
add_filter('media_upload_tabs', 'uploadcare_media_menu');
add_action('media_upload_uploadcare_files', 'uploadcare_media_files_menu_handle');
