<?php

/**
 * Define the internationalization functionality.
 */
class UcI18n {
    private $pluginName;

    /**
     * UcI18n constructor.
     *
     * @param string $pluginName
     */
    public function __construct( $pluginName ) {
        $this->pluginName = $pluginName;
    }

    /**
     * Load the plugin text domain for translation.
     */
    public function load_plugin_textdomain() {
        \load_plugin_textdomain(
            $this->pluginName,
            false,
            \basename( \dirname( __DIR__ ) ) . '/languages/'
        );
    }
}
