<?php declare(strict_types=1);

class BootstrapClassesTest extends WP_UnitTestCase
{
    public function testActivateClassExists(): void
    {
        self::assertTrue(\class_exists(UcActivator::class));
        self::assertTrue(\method_exists(UcActivator::class, 'activate'));
    }

    public function testDeactivateClassExists(): void
    {
        self::assertTrue(\class_exists(UcDeactivator::class));
        self::assertTrue(\method_exists(UcDeactivator::class, 'deactivate'));
    }
}
