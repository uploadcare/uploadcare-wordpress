<?php
/**
 * PHPUnit bootstrap file.
 */

// Path to WP test environment
$_tests_dir = dirname( __DIR__, 4 ) . '/wordpress-tests-lib';

// Give access to tests_add_filter() function.
require_once $_tests_dir . '/includes/functions.php';

/**
 * Manually load the plugin being tested.
 */
function _manually_load_plugin() {
    require_once dirname( __DIR__ ) . '/uploadcare.php';
}

//tests_add_filter('muplugins_loaded', '_manually_load_plugin');

// Start up the WP testing environment.
require $_tests_dir . '/includes/bootstrap.php';
\update_option( 'uploadcare_cdn_base', 'ucarecdn.com' );
\update_option( 'uploadcare_public', 'demopublickey' );
\update_option( 'uploadcare_secret', 'demosecretkey' );
