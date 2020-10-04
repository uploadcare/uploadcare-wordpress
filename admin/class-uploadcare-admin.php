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
        \wp_enqueue_script($this->plugin_name, \plugin_dir_url(__FILE__) . 'js/uploadcare.js', [], $this->version, false);
    }

    public function uploadcare_plugin_init()
    {
        \wp_register_script('uploadcare-widget', self::WIDGET_URL, ['jquery'], UPLOADCARE_VERSION, false);
        \wp_register_script('uploadcare-tab-effects', self::TAB_EFFECTS_URL, [], UPLOADCARE_VERSION, false);
        \wp_register_script('uploadcare-config', \plugin_dir_url(__FILE__) . 'admin/js/config.js', ['uploadcare-widget', 'uploadcare-tab-effects'], UPLOADCARE_VERSION, false);
        \wp_localize_script('uploadcare-config', 'WP_UC_PARAMS', $this->getJsConfig());
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
        if (\in_array($hook, $exclude))
            return;

        \wp_enqueue_script('uploadcare-widget');
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

        return [
            'public_key' => \get_option('uploadcare_public'),
            'original' => \get_option('uploadcare_original') ? "true" : "false",
            'multiple' => \get_option('uploadcare_multiupload') ? "true" : "false",
            'previewStep' => $preview,
            'effects' => \implode(',', $effects),
            'ajaxurl' => admin_url('admin-ajax.php'),
            'tabs' => $tabs,
            'cdnBase' => 'https://' . get_option('uploadcare_cdn_base', 'ucarecdn.com'),
        ];
    }

    private function register_user_images()
    {
        $image_type_labels = [
            'name' => _x('User images', 'post type general name'),
            'singular_name' => _x('Uploadcare User Image', 'post type singular name'),
            'add_new' => _x('Add New User Image', 'image'),
            'add_new_item' => __('Add New User Image'),
            'edit_item' => __('Edit User Image'),
            'new_item' => __('Add New User Image'),
            'all_items' => __('View User Images'),
            'view_item' => __('View User Image'),
            'search_items' => __('Search User Images'),
            'not_found' =>  __('No User Images found'),
            'not_found_in_trash' => __('No User Images found in Trash'),
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

        \register_post_type('uc_user_image', $image_type_args);

        $image_category_labels = [
            'name' => _x( 'User Image Categories', 'taxonomy general name' ),
            'singular_name' => _x( 'User Image', 'taxonomy singular name' ),
            'search_items' =>  __( 'Search User Image Categories' ),
            'all_items' => __( 'All User Image Categories' ),
            'parent_item' => __( 'Parent User Image Category' ),
            'parent_item_colon' => __( 'Parent User Image Category:' ),
            'edit_item' => __( 'Edit User Image Category' ),
            'update_item' => __( 'Update User Image Category' ),
            'add_new_item' => __( 'Add New User Image Category' ),
            'new_item_name' => __( 'New User Image Name' ),
            'menu_name' => __( 'User Image Categories' ),
        ];

        $image_category_args = [
            'hierarchical' => true,
            'labels' => $image_category_labels,
            'show_ui' => true,
            'query_var' => true,
            'rewrite' => ['slug' => 'uploadcare_user_image_category'],
        ];

        \register_taxonomy('uploadcare_user_image_category', array('uc_user_image'), $image_category_args);
    }
}
