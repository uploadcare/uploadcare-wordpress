<?php declare( strict_types=1 );

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class TwigWordpressExtension extends AbstractExtension {
    protected const JS_FOLDER = 'compiled-js';
    protected const FILE_SIZES = [ 'b', 'Kb', 'Mb', 'Gb' ];

    public function getFunctions(): array {
        $getPostMeta   = new TwigFunction( 'get_post_meta', [ $this, 'getPostMeta' ] );
        $getAttachment = new TwigFunction( 'get_attachment_image', [ $this, 'getAttachment' ] );
        $getAuthor     = new TwigFunction( 'get_post_author', [ $this, 'getPostAuthor' ] );
        $getFileSize   = new TwigFunction( 'get_file_size', [ $this, 'getFileSize' ] );
        $addJs         = new TwigFunction( 'add_js', [ $this, 'addJavascript' ] );

        return [
            $getPostMeta,
            $getAttachment,
            $getAuthor,
            $addJs,
            $getFileSize,
        ];
    }

    public function getFilters(): array {
        return [
            new TwigFilter( 'trans', [ $this, 'translate' ] ),
        ];
    }

    public function addJavascript( $name ): void {
        if ( \strpos( $name, '.js' ) !== false ) {
            $name = \str_replace( '.js', '', $name );
        }
        $folder = \dirname( __DIR__ ) . '/' . self::JS_FOLDER;
        if ( ! \is_dir( $folder ) ) {
            return;
        }
        $assetsFile   = \sprintf( '%s/%s.asset.php', $folder, $name );
        $pluginDirUrl = \plugin_dir_url( \dirname( __DIR__ ) . '/uploadcare.php' );
        $jsUrl        = \sprintf( '%s/%s/%s.js', \rtrim( $pluginDirUrl, '/' ), self::JS_FOLDER, $name );
        $parameters   = \is_readable( $assetsFile ) ? require $assetsFile : [];

        \wp_register_script( $name, $jsUrl, [], ( new UploadcareMain() )->get_version(), true );
        \wp_enqueue_script( $name, null, $parameters );
    }

    public function translate( string $data ): string {
        return __( $data, 'uploadcare' );
    }

    public function getPostAuthor( WP_Post $post ): ?WP_User {
        $result = \get_userdata( $post->post_author );

        return $result === false ? null : $result;
    }

    public function getFileSize( $post ): ?string {
        $postId = $this->getPostId( $post );
        $file   = \get_attached_file( $postId );
        if ( ! $file || ! \is_file( $file ) ) {
            return null;
        }

        $fileSize = \filesize( $file );
        $e        = \floor( \log( $fileSize ) / log( 1024 ) );

        return \sprintf( '%.2f %s', ( $fileSize / ( 1024 ** floor( $e ) ) ), ( self::FILE_SIZES[ $e ] ?? '' ) );
    }

    public function getAttachment( $post ): ?string {
        $postId = $this->getPostId( $post );
        $img    = \wp_get_attachment_image_src( $postId );

        $result = $img[0] ?? false;
        if ( $result === false ) {
            return null;
        }

        if ( ! empty( ( $uuid = \get_post_meta( $postId, 'uploadcare_uuid', true ) ) ) ) {
            $result = \sprintf( 'https://%s/%s/%s', \rtrim( \get_option( 'uploadcare_cdn_base' ), '/' ), $uuid, \get_post_meta( 'uploadcare_url_modifiers' ) );

            return \sprintf( UploadcareMain::SCALE_CROP_TEMPLATE, $result, '250x250' );
        }

        return \wp_get_attachment_image_url( $postId );
    }

    public function getPostMeta( $post, string $name ): ?string {
        $result = \get_post_meta( $this->getPostId( $post ), $name, true );

        return $result === false ? null : $result;
    }

    private function getPostId( $post ): int {
        if ( $post instanceof \WP_Post ) {
            return (int) $post->ID;
        }

        return (int) $post;
    }
}
