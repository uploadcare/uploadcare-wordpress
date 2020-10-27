<?php
/**
 * Plugin Name: Uploadcare
 * Plugin URI: http://github.com/uploadcare/uploadcare-wordpress
 * Description: Uploadcare let's you upload anything from anywhere (Instagram, Facebook, Dropbox, etc.)
 * Version: 2.7.2
 * Author: Uploadcare
 * Author URI: https://uploadcare.com/
 * License: GPL2
 */

if ( version_compare( PHP_VERSION, '5.3', '<' ) ) {
    exit("Uploadcare plugin requires PHP version <b>5.3+</b>, you've got <b>" . PHP_VERSION . "</b>");
}

define('UPLOADCARE_PLUGIN_VERSION', '2.7.2');
define('UPLOADCARE_WIDGET_VERSION', '3.x');
define('UPLOADCARE_TAB_EFFECTS_VERSION', '1.x');

define('UPLOADCARE_PLUGIN_URL', plugin_dir_url(__FILE__));
define('UPLOADCARE_PLUGIN_PATH', plugin_dir_path(__FILE__));

require_once UPLOADCARE_PLUGIN_PATH . 'inc/utils.php';
require_once UPLOADCARE_PLUGIN_PATH . 'inc/filters.php';
require_once UPLOADCARE_PLUGIN_PATH . 'inc/actions.php';
require_once UPLOADCARE_PLUGIN_PATH . 'inc/shortcodes.php';

require_once UPLOADCARE_PLUGIN_PATH . 'uploadcare-php/src/Uploadcare/Api.php';
require_once UPLOADCARE_PLUGIN_PATH . 'uploadcare-php/src/Uploadcare/File.php';
require_once UPLOADCARE_PLUGIN_PATH . 'uploadcare-php/src/Uploadcare/PagedDataIterator.php';
require_once UPLOADCARE_PLUGIN_PATH . 'uploadcare-php/src/Uploadcare/FileIterator.php';
require_once UPLOADCARE_PLUGIN_PATH . 'uploadcare-php/src/Uploadcare/Group.php';
require_once UPLOADCARE_PLUGIN_PATH . 'uploadcare-php/src/Uploadcare/GroupIterator.php';
require_once UPLOADCARE_PLUGIN_PATH . 'uploadcare-php/src/Uploadcare/Helper.php';
require_once UPLOADCARE_PLUGIN_PATH . 'uploadcare-php/src/Uploadcare/Uploader.php';
require_once UPLOADCARE_PLUGIN_PATH . 'uploadcare-php/src/Uploadcare/Widget.php';
require_once UPLOADCARE_PLUGIN_PATH . 'uploadcare-php/src/Uploadcare/Signature/SignatureInterface.php';
require_once UPLOADCARE_PLUGIN_PATH . 'uploadcare-php/src/Uploadcare/Signature/SecureSignature.php';

// TODO: delete table on upgrade
register_activation_hook(__FILE__, 'uploadcare_install');
function uploadcare_install() {
    if ( ! function_exists("curl_init") ) {
        exit("Uploadcare plugin requires <b>php-curl</b> to function");
    }
    /*
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
    */
}


register_deactivation_hook(__FILE__, 'uploadcare_uninstall');
function uploadcare_uninstall() {
    /*
    global $wpdb;
    $thetable = $wpdb->prefix."uploadcare";
    $wpdb->query("DROP TABLE IF EXISTS $thetable");
    */
}
