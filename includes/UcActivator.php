<?php

class UcActivator
{
    public static function activate()
    {
        if (!\function_exists('curl_init')) {
            exit("Uploadcare plugin requires <b>php-curl</b> to function");
        }
    }
}
