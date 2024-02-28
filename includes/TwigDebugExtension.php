<?php declare( strict_types=1 );

use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class TwigDebugExtension extends AbstractExtension {
    public function getFunctions(): array {
        $isDumpOutputHtmlSafe = ( \extension_loaded( 'xdebug' ) &&
                                  ( \ini_get( 'xdebug.overload_var_dump' ) === false || \ini_get(
                                          'xdebug.overload_var_dump'
                                      ) ) &&
                                  ( \ini_get( 'html_errors' ) === false || \ini_get( 'html_errors' ) ) ) ||
                                \PHP_SAPI === 'cli';

        return [
            new TwigFunction( 'dump', [ $this, 'sf_dump' ], [
                'is_safe'           => $isDumpOutputHtmlSafe ? [ 'html' ] : [],
                'needs_context'     => true,
                'needs_environment' => true,
                'is_variadic'       => true,
            ] ),
        ];
    }

    public function sf_dump( Environment $env, $context, ...$vars ) {
        if ( ! $env->isDebug() ) {
            return null;
        }
        $dumper = new \Symfony\Component\VarDumper\Dumper\HtmlDumper();
        $cloner = new \Symfony\Component\VarDumper\Cloner\VarCloner();

        if ( ! $vars ) {
            $vars = [];
            foreach ( $context as $key => $value ) {
                if ( ! $value instanceof \Twig\Template && ! $value instanceof \Twig\TemplateWrapper ) {
                    $vars[ $key ] = $value;
                }
            }
        }

        foreach ( $vars as $var ) {
            $dumper->dump( $cloner->cloneVar( $var ) );
        }
    }
}

