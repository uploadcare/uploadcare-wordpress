<?php

class Uploadcare_Admin
{
    const WIDGET_URL = 'https://ucarecdn.com/libs/widget/3.x/uploadcare.full.min.js';
    const TAB_EFFECTS_URL = 'https://ucarecdn.com/libs/widget-tab-effects/1.x//uploadcare.tab-effects.min.js';

    /**
     * @var string
     */
    private $plugin_name;

    /**
     * @var string
     */
    private $version;

    public function __construct($plugin_name, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    public function enqueue_styles()
    {
        \wp_enqueue_style($this->plugin_name, \plugin_dir_url(__FILE__) . 'css/uploadcare.css', [], $this->version, 'all');
    }

    public function enqueue_scripts()
    {
    }

    public function uploadcare_plugin_init()
    {
        \wp_register_script('uploadcare-widget', self::WIDGET_URL, ['jquery'], UPLOADCARE_VERSION, false);
        \wp_register_script('uploadcare-tab-effects', self::TAB_EFFECTS_URL, [], UPLOADCARE_VERSION, false);
        \wp_register_script('uploadcare-config', \plugin_dir_url(__FILE__) . 'js/config.js', ['uploadcare-widget', 'uploadcare-tab-effects'], UPLOADCARE_VERSION, false);
        \wp_localize_script('uploadcare-config', 'WP_UC_PARAMS', $this->getJsConfig());
//        $this->register_user_images();
    }

    public function uploadcare_settings_actions()
    {
        \add_options_page(\ucfirst($this->plugin_name), \ucfirst($this->plugin_name), 'upload_files', $this->plugin_name, [$this, 'uploadcare_settings']);
    }

    public function uploadcare_settings()
    {
        include \dirname(__DIR__) . '/includes/uploadcare_settings.php';
    }

    public function plugin_action_links(array $links)
    {
        $url = \esc_url(\add_query_arg('page', 'uploadcare', \get_admin_url() . 'admin.php'));
        $settings_link = \sprintf('<a href=\'%s\'>%s</a>', $url, __('Settings'));

        $links[] = $settings_link;

        return $links;
    }

    /**
     * Add js on add, edit and media pages
     * @param string $hook
     */
    public function add_uploadcare_js_to_admin($hook)
    {
        $exclude = ['post.php', 'post-new.php', 'media-new.php', 'upload.php'];
        if (!\in_array($hook, $exclude, true)) {
            return;
        }
        if (!\did_action('wp_enqueue_media')) {
            \wp_enqueue_media();
        }

        \wp_enqueue_script('uploadcare-widget');
        \wp_enqueue_script('uploadcare-tab-effects');
        \wp_enqueue_script('uploadcare-config');
        \wp_enqueue_script($this->plugin_name, \plugin_dir_url(__FILE__) . 'js/uploadcare.js', [], $this->version, false);
    }

    /**
     * @return void
     */
    public function uploadcare_add_media()
    {

    }

    /**
     * @param string $url
     * @return string
     */
    public function uc_get_attachment_url($url)
    {
        $post_id = \get_post()->ID;
        if (!($uc_url = (string) \get_post_meta($post_id, 'uploadcare_url', true))) {
            return $url;
        }

        return $uc_url;
    }

    public function uploadcare_media_menu(array $tabs)
    {
        return \array_merge([
            'uploadcare_files' => __('Uploadcare', $this->plugin_name),
        ], $tabs);
    }

    private function getJsConfig()
    {
        $tab_options = \get_option('uploadcare_source_tabs', [
            'file',
            'url',
            'facebook',
            'instagram',
            'flickr',
            'gdrive',
            'evernote',
            'box',
            'skydrive',
        ]);

        $tabs = \in_array('all', $tab_options, true) ? 'all' : \implode(' ', $tab_options);

        $effects = \get_option('uploadcare_tab_effects', [
            'crop',
            'rotate',
            'sharp',
            'enhance',
            'grayscale',
        ]);

        $preview = true;
        $noneId = \array_search('none', $effects, true);
        if ($noneId !== false)
            unset($effects[$noneId]);

        if (\count($effects) <= 1 && \in_array('none', $effects)) {
            $preview = false;
            $effects = [];
        }

        $options = [
            'public_key' => \get_option('uploadcare_public'),
            'original' => \get_option('uploadcare_original') ? "true" : "false",
            'multiple' => \get_option('uploadcare_multiupload') ? "true" : "false",
            'previewStep' => $preview,
            'effects' => \implode(',', $effects),
            'ajaxurl' => admin_url('admin-ajax.php'),
            'tabs' => $tabs,
            'cdnBase' => 'https://' . get_option('uploadcare_cdn_base', 'ucarecdn.com'),
        ];

        return \array_merge($options, $this->getSecureSignature());
    }

    private function getSecureSignature()
    {
        if (($lifetime = (int) \get_option('uploadcare_upload_lifetime')) === 0) {
            return [];
        }

        $expire = \time() + $lifetime;
        $key = \get_option('uploadcare_secret');

        return [
            'secureSignature' => \hash_hmac('sha256', (string) $expire, $key),
            'secureExpire' => $expire,
        ];
    }

    private function register_user_images()
    {
        $image_type_labels = [
            'name' => _x('User images', 'post type general name', '', $this->plugin_name),
            'singular_name' => _x('Uploadcare User Image', 'post type singular name', '', $this->plugin_name),
            'add_new' => _x('Add New User Image', 'image', '', $this->plugin_name),
            'add_new_item' => __('Add New User Image', $this->plugin_name),
            'edit_item' => __('Edit User Image', $this->plugin_name),
            'new_item' => __('Add New User Image', $this->plugin_name),
            'all_items' => __('View User Images', $this->plugin_name),
            'view_item' => __('View User Image', $this->plugin_name),
            'search_items' => __('Search User Images', $this->plugin_name),
            'not_found' => __('No User Images found', $this->plugin_name),
            'not_found_in_trash' => __('No User Images found in Trash', $this->plugin_name),
            'parent_item_colon' => '',
            'menu_name' => 'User Images'
        ];

        $image_type_args = [
            'labels' => $image_type_labels,
            'public' => true,
            'query_var' => true,
            'rewrite' => true,
            'capability_type' => 'post',
            'has_archive' => true,
            'hierarchical' => false,
            'map_meta_cap' => true,
            'menu_position' => null,
            'menu_icon' => 'dashicons-art',
            'supports' => ['title', 'author', 'thumbnail']
        ];

        $image_category_labels = [
            'name' => _x('User Image Categories', 'taxonomy general name', '', $this->plugin_name),
            'singular_name' => _x('User Image', 'taxonomy singular name', '', $this->plugin_name),
            'search_items' => __('Search User Image Categories', $this->plugin_name),
            'all_items' => __('All User Image Categories', $this->plugin_name),
            'parent_item' => __('Parent User Image Category', $this->plugin_name),
            'parent_item_colon' => __('Parent User Image Category:', $this->plugin_name),
            'edit_item' => __('Edit User Image Category', $this->plugin_name),
            'update_item' => __('Update User Image Category', $this->plugin_name),
            'add_new_item' => __('Add New User Image Category', $this->plugin_name),
            'new_item_name' => __('New User Image Name', $this->plugin_name),
            'menu_name' => __('User Image Categories', $this->plugin_name),
        ];

        $image_category_args = [
            'hierarchical' => true,
            'labels' => $image_category_labels,
            'show_ui' => true,
            'query_var' => true,
            'rewrite' => ['slug' => 'uploadcare_user_image_category'],
        ];

        \register_post_type('uc_user_image', $image_type_args);
        \register_taxonomy('uploadcare_user_image_category', ['uc_user_image'], $image_category_args);
    }
}
