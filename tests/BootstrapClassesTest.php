<?php declare( strict_types=1 );

namespace Tests;

class BootstrapClassesTest extends LoadedPluginTestCase {
    public function testActivateClassExists(): void {
        self::assertTrue( \class_exists( \UcActivator::class ) );
        self::assertTrue( \method_exists( \UcActivator::class, 'activate' ) );
    }

    public function testDeactivateClassExists(): void {
        self::assertTrue( \class_exists( \UcDeactivator::class ) );
        self::assertTrue( \method_exists( \UcDeactivator::class, 'deactivate' ) );
    }
}
