<?php declare( strict_types=1 );

namespace Tests;

class LoadedPluginTestCase extends \WP_UnitTestCase {
    public function setUp():void {
        require_once \dirname( __DIR__ ) . '/uploadcare.php';
        \tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );
        parent::setUp();
    }
}
