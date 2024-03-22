<?php declare( strict_types=1 );

namespace Tests;

class LoadByMetaTest extends LoadedPluginTestCase {
    protected const UUID = '6c5b97ee-4ce9-490f-92e9-50cba0271917';

    public function testPostByUuid(): void {
        $post = \wp_insert_post( [
            'post_author'    => '1',
            'post_date'      => date( 'Y-m-d H:i:s' ),
            'post_type'      => 'attachment',
            'post_title'     => 'Test post title',
            'post_parent'    => null,
            'post_status'    => 'inherit',
            'post_mime_type' => 'image/jpeg',
        ] );
        self::assertNotInstanceOf( \WP_Error::class, $post );
        \add_post_meta( $post, 'uploadcare_uuid', self::UUID );

        self::assertFalse( \get_post_meta( 'uploadcare_url', $post, true ) );
    }
}
