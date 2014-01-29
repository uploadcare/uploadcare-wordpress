<?php
/*
Plugin Name: Uploadcare
Plugin URI: http://github.com/uploadcare/uploadcare-wordpress
Description: Implements a way to use Uploadcare inside you Wordpress blog.
Version: 2.0.11
Author: Uploadcare
Author URI: https://uploadcare.com/
License: GPL2
*/


define('UPLOADCARE_PLUGIN_VERSION', '2.0.11');

// FIXME: this does not work with symlinks
define('UPLOADCARE_PLUGIN_URL', plugin_dir_url( __FILE__ ));
define('UPLOADCARE_PLUGIN_PATH', plugin_dir_path(__FILE__) );

if (is_admin()) {
  require_once UPLOADCARE_PLUGIN_PATH . 'admin.php';
}

require_once UPLOADCARE_PLUGIN_PATH . 'utils.php';
require_once UPLOADCARE_PLUGIN_PATH . 'filters.php';
require_once UPLOADCARE_PLUGIN_PATH . 'actions.php';
require_once UPLOADCARE_PLUGIN_PATH . 'uploadcare-php/uploadcare/lib/5.2/Uploadcare.php';


/**
 * Get Api object
 *
 */
function uploadcare_api() {
    global $wp_version;
    $user_agent = 'Uploadcare Wordpress ' . UPLOADCARE_PLUGIN_VERSION . '/' . $wp_version;
    return new Uploadcare_Api(
        get_option('uploadcare_public'),
        get_option('uploadcare_secret'),
        $user_agent
    );
}

/*
TODO: delete table on upgrade

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
register_activation_hook(__FILE__, 'uploadcare_install');

function uploadcare_uninstall() {
  global $wpdb;
  $thetable = $wpdb->prefix."uploadcare";
  $wpdb->query("DROP TABLE IF EXISTS $thetable");
}
register_deactivation_hook(__FILE__, 'uploadcare_uninstall');
*/
