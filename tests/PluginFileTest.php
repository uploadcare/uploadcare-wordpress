<?php declare(strict_types=1);


class PluginFileTest extends WP_UnitTestCase
{
    public function testClassExists(): void
    {
        self::assertTrue(\class_exists(Uploadcare_Wordpress_Plugin::class));
    }
}
