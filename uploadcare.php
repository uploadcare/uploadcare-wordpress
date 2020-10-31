<?php
/**
 * @link              https://uploadcare.com
 * @since             3.0.0
 * @package           Uploadcare
 *
 * @wordpress-plugin
 * Plugin Name:       Uploadcare WordPress Plugin
 * Plugin URI:        https://github.com/uploadcare/uploadcare-wordpress
 * Description:       Uploadcare let's you upload anything from anywhere (Instagram, Facebook, Dropbox, etc.)
 * Version:           3.0.0
 * Author:            Uploadcare
 * Author URI:        https://uploadcare.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       uploadcare
 * Domain Path:       /languages
 */

if (!defined('WPINC')) {
    die();
}

if (PHP_VERSION_ID < 50600) {
    exit("Uploadcare plugin requires PHP version <b>5.6+</b>, you've got <b>" . PHP_VERSION . "</b>");
}

define('UPLOADCARE_VERSION', '3.0.0');

require_once __DIR__ . '/vendor/autoload.php';

function activate_uploadcare()
{
    require_once __DIR__ . '/includes/UcActivator.php';
    UcActivator::activate();
}

function deactivate_uploadcare()
{
    require_once __DIR__ . '/includes/UcDeactivator.php';
    UcDeactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_uploadcare');
register_deactivation_hook(__FILE__, 'deactivate_uploadcare');

require __DIR__ . '/includes/UploadcareMain.php';

function run_uploadcare()
{
    $plugin = new UploadcareMain();
    $plugin->run();
}

/** @noinspection ForgottenDebugOutputInspection */
function ULog(...$args)
{
    foreach ($args as $arg) {
        \error_log("\t[LOG::Ulog]\n".\var_export($arg, true)."\n");
    }
}

run_uploadcare();
