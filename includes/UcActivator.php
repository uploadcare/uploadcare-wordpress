<?php

class UcActivator
{
    public static function activate()
    {
        if (!\function_exists('curl_init')) {
            exit("Uploadcare plugin requires <b>php-curl</b> to function");
        }
        if (!\class_exists(\DOMDocument::class)) {
            exit("Uploadcare plugin requires <b>DOMDocument</b> class. Install ext-dom for activate this.");
        }
    }
}
