<?php

class UploadcareMain
{
    /**
     * @var UcLoader
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
        require_once __DIR__ . '/UcLoader.php';
        require_once __DIR__ . '/UcI18n.php';
        require_once \dirname(__DIR__) . '/admin/UcAdmin.php';

        $this->loader = new UcLoader();
    }

    private function set_locale()
    {
        $this->loader->add_action('plugins_loaded', new UcI18n($this->plugin_name), 'load_plugin_textdomain');
    }

    private function define_admin_hooks()
    {
        $plugin_admin = new UcAdmin($this->get_plugin_name(), $this->get_version());

        $this->loader->add_filter('plugin_action_links_uploadcare/uploadcare.php', $plugin_admin, 'plugin_action_links');
        $this->loader->add_action('init', $plugin_admin, 'uploadcare_plugin_init');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'add_uploadcare_js_to_admin');
        $this->loader->add_action('wp_ajax_uploadcare_handle', $plugin_admin, 'uploadcare_handle');
        $this->loader->add_action('post-upload-ui', $plugin_admin, 'uploadcare_media_upload');
        $this->loader->add_action('admin_menu', $plugin_admin, 'uploadcare_settings_actions');

        $this->loader->add_filter('load_image_to_edit_attachmenturl', $plugin_admin, 'uc_load', 10, 2);
        $this->loader->add_filter('wp_get_attachment_url', $plugin_admin, 'uc_get_attachment_url', 8, 2);
        $this->loader->add_filter('image_downsize', $plugin_admin, 'uploadcare_image_downsize', 9, 3);
        $this->loader->add_filter('post_thumbnail_html', $plugin_admin, 'uploadcare_post_thumbnail_html', 10, 5);
        $this->loader->add_filter('wp_save_image_editor_file', $plugin_admin, 'uc_save_image_editor_file', 10, 5);
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
     * @return UcLoader
     */
    public function get_loader()
    {
        return $this->loader;
    }
}
