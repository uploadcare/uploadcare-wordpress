<?php /** @noinspection AutoloadingIssuesInspection */

/**
 * @link              https://uploadcare.com
 * @since             3.0.0
 * @package           Uploadcare
 *
 * @wordpress-plugin
 * Plugin Name:       Uploadcare File Uploader and Adaptive Delivery
 * Plugin URI:        https://github.com/uploadcare/uploadcare-wordpress
 * Description:       Upload and store any file of any size from any device or cloud. No more slow downs when serving your images with automatic responsiviness and lazy loading. Improve your WP performance to boost Customer Experience and SEO.
 * Version:           3.0.11
 * Author:            Uploadcare
 * Author URI:        https://uploadcare.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       uploadcare
 * Domain Path:       /languages
 */
class Uploadcare_Wordpress_Plugin {
    public const UPLOADCARE_VERSION = '3.0.11';

    public function __construct() {
        if ( ! \defined( 'WPINC' ) ) {
            exit();
        }
        if ( PHP_VERSION_ID < 70400 ) {
            exit( "Uploadcare plugin requires PHP version <b>7.4.0+</b>, you've got <b>" . PHP_VERSION . '</b>' );
        }
        \defined( 'UPLOADCARE_VERSION' ) or \define( 'UPLOADCARE_VERSION', self::UPLOADCARE_VERSION );

        $this->init();
        $this->run_uploadcare();
    }

    public function activate_uploadcare(): void {
        UcActivator::activate();
    }

    public function deactivate_uploadcare(): void {
        UcDeactivator::deactivate();
    }

    public function run_uploadcare(): void {
        $plugin = new UploadcareMain();
        $plugin->run();
    }

    public function init(): void {
        require_once __DIR__ . '/vendor/autoload.php';

        \register_activation_hook( __FILE__, [ $this, 'activate_uploadcare' ] );
        \register_deactivation_hook( __FILE__, [ $this, 'deactivate_uploadcare' ] );
    }
}

/** @noinspection ForgottenDebugOutputInspection */
function ULog( ...$args ) {
    if ( ! \is_array( $args ) || empty( $args ) ) {
        return;
    }

    foreach ( $args as $arg ) {
        $data = [
            "\t[LOG::Ulog]",
            "\n",
            \var_export( $arg, true ),
            "\n",
        ];

        \error_log( \implode( '', $data ) );
    }
}

function UploadcareUserAgent(): array {
    global $wp_version;

    return [
        'Uploadcare-wordpress',
        \sprintf( '%s,%s', $wp_version, Uploadcare_Wordpress_Plugin::UPLOADCARE_VERSION )
    ];
}

new Uploadcare_Wordpress_Plugin();
