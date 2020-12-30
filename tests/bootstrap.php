<?php
/**
 * PHPUnit bootstrap file
 *
 * @package Uploadcare
 */

$_tests_dir = \dirname(\dirname(\dirname(\dirname(__DIR__)))) . '/wordpress-tests-lib';
// Give access to tests_add_filter() function.
require_once $_tests_dir . '/includes/functions.php';

/**
 * Manually load the plugin being tested.
 */
function _manually_load_plugin() {
	require dirname(__DIR__) . '/uploadcare.php';
}
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

// Start up the WP testing environment.
require $_tests_dir . '/includes/bootstrap.php';
