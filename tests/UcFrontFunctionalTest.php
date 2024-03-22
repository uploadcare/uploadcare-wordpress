<?php declare( strict_types=1 );

namespace Tests;

class UcFrontFunctionalTest extends LoadedPluginTestCase {
    /**
     * @var \UcFront|\WP_UnitTest_Factory|null
     */
    private $service;

    public function setUp(): void {
        parent::setUp();
        $this->service = new \UcFront( 'uploadcare', \UPLOADCARE_VERSION );
    }

    public static function getParagraph(): array {
        return [
            'blockName'    => 'core/paragraph',
            'attrs'        => [],
            'innerBlocks'  => [],
            'innerHTML'    => 'Welcome to WordPress. This is your first post. Edit or delete it, then start writing!',
            'innerContent' => [
                'Welcome to WordPress. This is your first post. Edit or delete it, then start writing!',
            ],
        ];
    }

    public static function getEmpty(): array {
        return [
            'blockName'    => null,
            'attrs'        => [],
            'innerBlocks'  => [],
            'innerHTML'    => '',
            'innerContent' => [],
        ];
    }

    public static function getUploadcareImage(): array {
        return [
            'blockName'    => 'uploadcare/image',
            'attrs'        => [
                'mediaID'  => '5',
                'mediaUid' => 'b3669764-2932-4bb1-a1df-c8feda55a959',
            ],
            'innerBlocks'  => [],
            'innerHTML'    => '<figure class="wp-block-uploadcare-image"><img id="5" src="https://ucarecdn.com/b3669764-2932-4bb1-a1df-c8feda55a959/" class="uploadcare-image"/><figcaption></figcaption></figure>',
            'innerContent' => [
                '<figure class="wp-block-uploadcare-image"><img id="5" src="https://ucarecdn.com/b3669764-2932-4bb1-a1df-c8feda55a959/" class="uploadcare-image"/><figcaption></figcaption></figure>',
            ],
        ];
    }

    public static function getCoreImage(): array {
        $site_url = get_site_url();
        return [
            'blockName'    => 'core/image',
            'attrs'        => [
                'id'              => 12,
                'sizeSlug'        => 'large',
                'linkDestination' => 'none',
            ],
            'innerBlocks'  => [],
            'innerHTML'    => '<figure class="wp-block-image size-large"><img src="'.$site_url.'/wp-content/uploads/2021/01/photo_2020-08-21-08.42.40-874x1024.jpeg" alt="" class="wp-image-8"/><figcaption>Some</figcaption></figure>',
            'innerContent' => [
                '<figure class="wp-block-image size-large"><img src="'.$site_url.'/wp-content/uploads/2021/01/photo_2020-08-21-08.42.40-874x1024.jpeg" alt="" class="wp-image-8"/><figcaption>Some</figcaption></figure>',
            ],
        ];
    }

    public function testClassExists(): void {
        self::assertInstanceOf( \UcFront::class, $this->service );
        $pluginName = ( new \ReflectionObject( $this->service ) )->getProperty( 'pluginName' );
        $pluginName->setAccessible( true );
        self::assertEquals( 'uploadcare', $pluginName->getValue( $this->service ) );

        $pluginVersion = ( new \ReflectionObject( $this->service ) )->getProperty( 'pluginVersion' );
        $pluginVersion->setAccessible( true );
        self::assertNotEmpty( $pluginVersion->getValue( $this->service ) );
        self::assertEquals( \UPLOADCARE_VERSION, $pluginVersion->getValue( $this->service ) );
    }

    public function testRenderBlockWithoutOptionsIsSet(): void {
        \update_option( 'uploadcare_public', null );
        \update_option( 'uploadcare_secret', null );

        $content = self::getCoreImage()['innerHTML'];

        $result = $this->service->renderBlock( $content, self::getCoreImage() );
        self::assertEquals( $content, $result );
    }

    public function testRenderParagraphBlock(): void {
        $content = self::getParagraph()['innerHTML'];
        $result  = $this->service->renderBlock( $content, self::getParagraph() );
        self::assertEquals( $content, $result );
    }

    public function testCoreImageWithoutAdaptiveDelivery(): void {
        $post = \wp_insert_post( [
            'post_date'   => date( 'Y-m-d H:i:s' ),
            'post_type'   => 'attachment',
            'post_status' => 'inherit',
        ] );
        \add_post_meta( $post, '_wp_attached_file', '2021/01/photo_2020-08-21-08.42.40.jpeg' );


        $content = self::getCoreImage()['innerHTML'];
        $result  = $this->service->renderBlock( $content, self::getCoreImage() );
        self::assertEquals( $content, $result );
    }

    public function testCoreImageWithAdaptiveDelivery(): void {
        self::assertNotEmpty( \get_option( 'uploadcare_public' ) );
        self::assertNotEmpty( \get_option( 'uploadcare_secret' ) );
        $adaptiveDelivery = ( new \ReflectionObject( $this->service ) )->getProperty( 'adaptiveDelivery' );
        $adaptiveDelivery->setAccessible( true );
        $adaptiveDelivery->setValue( $this->service, true );

        $post = \wp_insert_post( [
            'post_date'   => date( 'Y-m-d H:i:s' ),
            'post_type'   => 'attachment',
            'post_status' => 'inherit',
            'post_mime_type' => 'image/jpeg'
        ] );
        \add_post_meta( $post, '_wp_attached_file', '2024/03/photo_2020-08-21-08.42.40.jpeg' );

        $mock_data  = self::getCoreImage();
        $mock_data['attrs']['id'] = $post;

        $content = self::getCoreImage()['innerHTML'];
        $result  = $this->service->renderBlock( $content, $mock_data );

        self::assertNotEquals( $result, $content );
        self::assertStringContainsString( 'data-blink-src', $result );
    }

    public function testUploadcareImageWithoutAdaptiveDelivery(): void {
        $adaptiveDelivery = ( new \ReflectionObject( $this->service ) )->getProperty( 'adaptiveDelivery' );
        $adaptiveDelivery->setAccessible( true );
        $adaptiveDelivery->setValue( $this->service, true );

        $post = \wp_insert_post( [
            'post_date'   => date( 'Y-m-d H:i:s' ),
            'post_type'   => 'attachment',
            'post_status' => 'inherit',
            'post_mime_type' => 'image/jpeg'
        ] );

        $mock_data  = self::getUploadcareImage();
        $mock_data['attrs']['mediaID'] = $post;
        \add_post_meta( $post, '_wp_attached_file', 'https://ucarecdn.com/b3669764-2932-4bb1-a1df-c8feda55a959/' );

        $content = self::getUploadcareImage()['innerHTML'];
        $result  = $this->service->renderBlock( $content, $mock_data );

        self::assertNotEquals( $result, $content );
        self::assertStringContainsString( 'data-blink-src', $result );
    }

    public function testJsConfigMethod(): void {
        $getJsConfig = ( new \ReflectionObject( $this->service ) )->getMethod( 'getJsConfig' );
        $getJsConfig->setAccessible( true );
        $result = $getJsConfig->invoke( $this->service );

        self::assertIsArray( $result );
        foreach ( [ 'pubkey', 'fadeIn', 'lazyload', 'smartCompression', 'responsive', 'retina', 'webp' ] as $item ) {
            self::assertArrayHasKey( $item, $result );
        }
    }

    public function testJsConfigWithOptions(): void {
        $options = '{"retina": false}';
        \update_option( 'uploadcare_blink_loader', $options );

        $getJsConfig = ( new \ReflectionObject( $this->service ) )->getMethod( 'getJsConfig' );
        $getJsConfig->setAccessible( true );
        $result = $getJsConfig->invoke( $this->service );

        self::assertArrayHasKey( 'retina', $result );
        self::assertFalse( $result['retina'] );
    }
}
