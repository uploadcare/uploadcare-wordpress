<?php declare( strict_types=1 );

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class TwigFactory {
    public static function create( string $templatePath = null ): Environment {
        if ( $templatePath === null || ! \is_dir( $templatePath ) ) {
            $templatePath = \dirname( __DIR__ ) . '/templates';
        }

        $loader = new FilesystemLoader( $templatePath );

        $twig = new Environment( $loader, [
            'cache' => \dirname( __DIR__ ) . '/cache',
        ] );
        static::setDebug( $twig );
        $twig->addExtension( new TwigWordpressExtension() );

        return $twig;
    }

    private static function setDebug( Environment $twig ): void {
        $debug = \filter_var( \getenv( 'APP_DEBUG' ), FILTER_VALIDATE_BOOLEAN );
        if ( $debug === false ) {
            return;
        }

        $twig->enableDebug();
        $twig->enableAutoReload();
        $twig->addExtension( new TwigDebugExtension() );
    }
}
