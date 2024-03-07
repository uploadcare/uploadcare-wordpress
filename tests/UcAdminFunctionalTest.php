<?php declare( strict_types=1 );

namespace Tests;

use Uploadcare\Interfaces\Api\FileApiInterface;
use Uploadcare\Interfaces\File\FileInfoInterface;
use Uploadcare\Interfaces\RestApiInterface;

class UcAdminFunctionalTest extends LoadedPluginTestCase {
    private $logPath = __DIR__ . '/_output/error.log';

    /**
     * @var \UcAdmin|\WP_UnitTest_Factory|null
     */
    private $service;

    public function setUp(): void {
        parent::setUp();
        $this->service = new \UcAdmin( 'uploadcare', 'UPLOADCARE_TEST' );
        if ( \is_file( $this->logPath ) ) {
            \unlink( $this->logPath );
        }
        \ini_set( 'error_log', $this->logPath );
    }

    protected function getLog(): ?string {
        return \is_file( $this->logPath ) ? \file_get_contents( $this->logPath ) : null;
    }

    protected function getPost( int $id = null ): int {
        $postData = [
            'post_date'   => date( 'Y-m-d H:i:s' ),
            'post_type'   => 'attachment',
            'post_status' => 'inherit',
        ];
        if ( $id !== null ) {
            $postData['id'] = $id;
        }

        return \wp_insert_post( $postData );
    }

    public function testAttachmentDeleteAction(): void {
        $id = $this->getPost();
        \add_post_meta( $id, 'uploadcare_url', 'https://ucarecdn.com/bb88d7fd-7343-45ff-8f6b-880eee7a0500/-/preview/2048x2048/-/quality/lightest/-/format/auto/' );

        $fileApi = $this->getMockBuilder( FileApiInterface::class )
                        ->getMock();
        $fileApi->expects( self::once() )->method( 'deleteFile' );
        $api = $this->getMockBuilder( RestApiInterface::class )
                    ->disableOriginalConstructor()
                    ->getMock();
        $api->expects( self::once() )->method( 'file' )->willReturn( $fileApi );
        $serviceApi = ( new \ReflectionObject( $this->service ) )->getProperty( 'api' );
        $serviceApi->setAccessible( true );
        $serviceApi->setValue( $this->service, $api );

        $this->service->attachmentDelete( $id, new \WP_Post( (object) [] ) );
    }

    public function testAttachmentDeleteActionError(): void {
        $id = $this->getPost();
        \add_post_meta( $id, 'uploadcare_url', 'https://ucarecdn.com/bb88d7fd-7343-45ff-8f6b-880eee7a0500/-/preview/2048x2048/-/quality/lightest/-/format/auto/' );

        $fileApi = $this->getMockBuilder( FileApiInterface::class )
                        ->getMock();
        $fileApi->expects( self::once() )->method( 'deleteFile' )
                ->willThrowException( new \Exception() );
        $api = $this->getMockBuilder( RestApiInterface::class )
                    ->disableOriginalConstructor()
                    ->getMock();
        $api->expects( self::once() )->method( 'file' )->willReturn( $fileApi );
        $serviceApi = ( new \ReflectionObject( $this->service ) )->getProperty( 'api' );
        $serviceApi->setAccessible( true );
        $serviceApi->setValue( $this->service, $api );

        $this->service->attachmentDelete( $id, new \WP_Post( (object) [] ) );
        self::assertNotNull( $this->getLog() );
        self::assertStringContainsString( 'Unable to delete file', $this->getLog() );
    }

    public function testGetDefaultAttachmentUrl(): void {
        $postId = $this->getPost();
        $url    = 'https://example.com/image';
        self::assertSame( $this->service->uc_get_attachment_url( $url, $postId ), $url );
    }

    public function testGetUploadcareAttachmentUrl(): void {
        $postId = $this->getPost();
        $url    = 'https://example.com/image';
        $ucUrl  = 'https://ucarecdn.com/bb88d7fd-7343-45ff-8f6b-880eee7a0500/';
        \add_post_meta( $postId, 'uploadcare_url', $ucUrl );

        self::assertSame( $this->service->uc_get_attachment_url( $url, $postId ), $ucUrl );
    }

    public function testUcLoadMethod(): void {
        $fileInfo = $this->getMockBuilder( FileInfoInterface::class )
                         ->getMock();
        $fileInfo->expects( self::once() )->method( 'getOriginalFilename' )
                 ->willReturn( 'foo-bar-baz.png' );

        $fileApi = $this->getMockBuilder( FileApiInterface::class )
                        ->getMock();
        $fileApi->expects( self::once() )->method( 'fileInfo' )
                ->willReturn( $fileInfo );
        $api = $this->getMockBuilder( RestApiInterface::class )
                    ->disableOriginalConstructor()
                    ->getMock();
        $api->expects( self::once() )->method( 'file' )->willReturn( $fileApi );
        $serviceApi = ( new \ReflectionObject( $this->service ) )->getProperty( 'api' );
        $serviceApi->setAccessible( true );
        $serviceApi->setValue( $this->service, $api );

        $postId = $this->getPost();
        $ucUrl  = 'https://ucarecdn.com/bb88d7fd-7343-45ff-8f6b-880eee7a0500/';
        \add_post_meta( $postId, 'uploadcare_url', $ucUrl );

        $result = $this->service->uc_load( $ucUrl, $postId );
        self::assertStringContainsString( $ucUrl, $result );
        self::assertStringContainsString( 'foo-bar-baz.png', $result );
    }
}
