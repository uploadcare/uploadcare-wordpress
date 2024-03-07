<?php declare( strict_types=1 );

namespace Tests;

class loadPostByUuidTest extends LoadedPluginTestCase {
    /**
     * @var int
     */
    private $post;

    protected const UUID = '7e9b491a-4a68-43d8-9694-0406faed3ca7';

    public function setUp(): void {
        parent::setUp();

        $this->post = \wp_insert_post( [
            'post_author'    => '1',
            'post_date'      => date( 'Y-m-d H:i:s' ),
            'post_type'      => 'attachment',
            'post_title'     => 'Test post title',
            'post_parent'    => null,
            'post_status'    => 'inherit',
            'post_mime_type' => 'image/jpeg',
        ] );
        self::assertNotInstanceOf( \WP_Error::class, $this->post );
        \add_post_meta( $this->post, 'uploadcare_uuid', self::UUID );
    }

    public function testLoadIdFromUuid(): void {
        $admin = new \UcAdmin( 'uploadcare', 'TEST_VERSION' );
        $post  = $admin->loadPostByUuid( self::UUID );
        self::assertEquals( $this->post, $post->ID );
    }
}
