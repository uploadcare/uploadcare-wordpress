<?php

class Uploadcare
{
    /**
     * @var Uploadcare_Loader
     */
    protected $loader;

    /**
     * @var string
     */
    protected $plugin_name;

    /**
     * @var string
     */
    protected $version;

    public function __construct()
    {
        if (defined('UPLOADCARE_VERSION')) {
            $this->version = UPLOADCARE_VERSION;
        } else {
            $this->version = '3.0.0';
        }
        $this->plugin_name = 'uploadcare';

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
    }

    private function load_dependencies()
    {
        require_once __DIR__ . '/class-uploadcare-loader.php';
        require_once __DIR__ . '/class-uploadcare-i18n.php';
        require_once \dirname(__DIR__) . '/admin/class-uploadcare-admin.php';

        $this->loader = new Uploadcare_Loader();
    }

    private function set_locale()
    {
        $this->loader->add_action('plugins_loaded', new Uploadcare_i18n(), 'load_plugin_textdomain');
    }

    private function define_admin_hooks()
    {
        $plugin_admin = new Uploadcare_Admin($this->get_plugin_name(), $this->get_version());

        $this->loader->add_filter('plugin_action_links_uploadcare/uploadcare.php', $plugin_admin, 'plugin_action_links');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles', 10);
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts', 20);
        $this->loader->add_action('init', $plugin_admin, 'uploadcare_plugin_init', 30);
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'add_uploadcare_js_to_admin', 40);

        $this->loader->add_action('media_buttons', $plugin_admin, 'uploadcare_add_media', 50);
        $this->loader->add_filter('media_upload_tabs', $plugin_admin, 'uploadcare_media_menu');
        $this->loader->add_action('admin_menu', $plugin_admin, 'uploadcare_settings_actions', 60);

        $this->loader->add_filter('wp_get_attachment_url', $plugin_admin, 'uc_get_attachment_url');
    }

    /**
     * @return void
     */
    public function run()
    {
        $this->loader->run();
    }

    /**
     * @return string
     */
    public function get_plugin_name()
    {
        return $this->plugin_name;
    }

    /**
     * @return string
     */
    public function get_version()
    {
        return $this->version;
    }

    /**
     * @return Uploadcare_Loader
     */
    public function get_loader()
    {
        return $this->loader;
    }
}
