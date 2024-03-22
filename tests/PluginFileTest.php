<?php declare( strict_types=1 );

namespace Tests;

class PluginFileTest extends LoadedPluginTestCase {
    private $logPath = __DIR__ . '/_output/error.log';

    public function setUp(): void {
        parent::setUp();

        if ( \is_file( $this->logPath ) ) {
            \unlink( $this->logPath );
        }
    }

    public function testClassExists(): void {
        self::assertTrue( \class_exists( \Uploadcare_Wordpress_Plugin::class ) );
    }

    public function testVersionConstantIsDefined(): void {
        self::assertTrue( \defined( 'WPINC' ) );
        self::assertTrue( \defined( 'UPLOADCARE_VERSION' ) );
    }

    public function testUploadcareUserAgent(): void {
        global $wp_version;
        self::assertTrue( \function_exists( 'UploadcareUserAgent' ) );
        $result = \UploadcareUserAgent();
        self::assertIsArray( $result );
        self::assertContains( 'Uploadcare-wordpress', $result );
        self::assertEquals( \Uploadcare_Wordpress_Plugin::UPLOADCARE_VERSION, \UPLOADCARE_VERSION );
        self::assertContains( \sprintf( '%s,%s', $wp_version, \UPLOADCARE_VERSION ), $result );
    }

    public function testClassActions(): void {
        $mockedPlugin = $this->getMockBuilder( \Uploadcare_Wordpress_Plugin::class )
                             ->getMock();
        $mockedPlugin->expects( self::once() )->method( 'init' );
        $mockedPlugin->expects( self::once() )->method( 'run_uploadcare' );

        $mockedPlugin->__construct();
    }

    public function testUlogFunction(): void {
        self::assertTrue( \function_exists( 'ULog' ) );
        \ini_set( 'error_log', $this->logPath );
        ULog( 'foo', 'bar', 'baz' );
        $contents = \file_get_contents( $this->logPath );
        self::assertStringContainsString( '[LOG::Ulog]', $contents );
        self::assertStringContainsString( 'foo', $contents );
        self::assertStringContainsString( 'bar', $contents );
        self::assertStringContainsString( 'baz', $contents );
    }

    public function testUlogWithoutArguments(): void {
        \ini_set( 'error_log', $this->logPath );
        ULog();
        self::assertFalse( \is_file( $this->logPath ) );
    }
}
