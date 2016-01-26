<?php
/*
Plugin Name: Uploadcare
Plugin URI: http://github.com/uploadcare/uploadcare-wordpress
Description: Uploadcare let's you upload anything from anywhere (Instagram, Facebook, Dropbox, etc.)
Version: 2.3.2
Author: Uploadcare
Author URI: https://uploadcare.com/
License: GPL2
*/


if ( version_compare( PHP_VERSION, '5.3', '<' ) ) {
    exit("Uploadcare plugin requires PHP version <b>5.3+</b>, you've got <b>" . PHP_VERSION . "</b>");
}

define('UPLOADCARE_PLUGIN_VERSION', '2.3.3');
define('UPLOADCARE_WIDGET_VERSION', '2.5.5');

define('UPLOADCARE_PLUGIN_URL', plugin_dir_url(__FILE__));
define('UPLOADCARE_PLUGIN_PATH', plugin_dir_path(__FILE__));


require_once UPLOADCARE_PLUGIN_PATH . 'inc/utils.php';
require_once UPLOADCARE_PLUGIN_PATH . 'inc/filters.php';
require_once UPLOADCARE_PLUGIN_PATH . 'inc/actions.php';
require_once UPLOADCARE_PLUGIN_PATH . 'inc/shortcodes.php';

require_once UPLOADCARE_PLUGIN_PATH . 'uploadcare-php/src/Uploadcare/Api.php';
require_once UPLOADCARE_PLUGIN_PATH . 'uploadcare-php/src/Uploadcare/File.php';
require_once UPLOADCARE_PLUGIN_PATH . 'uploadcare-php/src/Uploadcare/FileIterator.php';
require_once UPLOADCARE_PLUGIN_PATH . 'uploadcare-php/src/Uploadcare/Group.php';
require_once UPLOADCARE_PLUGIN_PATH . 'uploadcare-php/src/Uploadcare/Uploader.php';
require_once UPLOADCARE_PLUGIN_PATH . 'uploadcare-php/src/Uploadcare/Widget.php';


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

/**
 * Welcome screen
 */

register_activation_hook(__FILE__, 'welcome_screen_activate');
function welcome_screen_activate() {
    set_transient('_welcome_screen_activation_redirect', true, 30);
}

add_action('admin_init', 'welcome_screen_do_activation_redirect');
function welcome_screen_do_activation_redirect() {
    // Bail if no activation redirect
    if (!get_transient('_welcome_screen_activation_redirect')) {
        return;
    }

    // Delete the redirect transient
    delete_transient('_welcome_screen_activation_redirect');

    // Bail if activating from network, or bulk
    if (is_network_admin() || isset($_GET['activate-multi'])) {
        return;
    }

    // Redirect to bbPress about page
    wp_safe_redirect(add_query_arg(array('page' => 'welcome-screen-about'), admin_url('index.php')));
}

add_action('admin_menu', 'welcome_screen_pages');

function welcome_screen_pages() {
    add_dashboard_page(
        'Welcome To Welcome Screen',
        'Welcome To Welcome Screen',
        'read',
        'welcome-screen-about',
        'welcome_screen_content'
    );
}

function welcome_screen_content() {
?>
    <div class="wrap">
        <h2>Uploadcare Plugin</h2>

        <p>
            Here is a text about this plugin.
        </p>

        <div id="icon-options-general" class="icon32"><br></div>
        <h2>Let's configure it</h2>

        <?php print_base_settings("/wp-admin/options-general.php?page=uploadcare"); ?>

    </div>
<?php
}

add_action('admin_head', 'welcome_screen_remove_menus');

function welcome_screen_remove_menus() {
    remove_submenu_page('index.php', 'welcome-screen-about');
}