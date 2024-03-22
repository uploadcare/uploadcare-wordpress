<?php

class UcActivator {
    public static function activate() {
        if ( ! \function_exists( 'curl_init' ) ) {
            exit( 'Uploadcare plugin requires <b>php-curl</b> to function.' );
        }
        if ( ! \class_exists( \DOMDocument::class ) ) {
            exit( 'Uploadcare plugin requires <a href="https://www.php.net/manual/en/class.domdocument.php" target="_blank">DOMDocument</a> class. <a href="https://www.php.net/manual/en/dom.setup.php" target="_blank">Install ext-dom</a> for your PHP to function.' );
        }
    }
}
