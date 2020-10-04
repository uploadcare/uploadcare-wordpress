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

    /**
     * @var \Uploadcare\Api
     */
    private $api;

    public function __construct($plugin_name, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $config = \Uploadcare\Configuration::create(\get_option('uploadcare_public'), \get_option('uploadcare_secret'));
        $this->api = new \Uploadcare\Api($config);

    }

    public function enqueue_styles()
    {
        \wp_enqueue_style('uploadcare-style', \plugin_dir_url(__FILE__) . 'css/uploadcare.css', [], $this->version, 'all');
    }

    public function enqueue_scripts()
    {
    }

    public function uploadcare_plugin_init()
    {
        \wp_register_script('uploadcare-widget', self::WIDGET_URL, ['jquery'], UPLOADCARE_VERSION, false);
        \wp_register_script('uploadcare-tab-effects', self::TAB_EFFECTS_URL, [], UPLOADCARE_VERSION, false);
        \wp_register_script('uploadcare-config', \plugin_dir_url(__FILE__) . 'js/config.js', ['uploadcare-widget', 'uploadcare-tab-effects'], UPLOADCARE_VERSION, false);
        \wp_register_script('uploadcare-shortcodes', \plugin_dir_url(__FILE__) . 'js/shortcodes.js', ['uploadcare-config'], UPLOADCARE_VERSION, false);
        \wp_localize_script('uploadcare-config', 'WP_UC_PARAMS', $this->getJsConfig());
        $this->register_user_images();
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
        \wp_enqueue_script('uploadcare-main', \plugin_dir_url(__FILE__) . 'js/uploadcare.js', [], $this->version, false);
    }

    /**
     * @return string
     */
    public function uploadcare_add_media()
    {
        $img = plugins_url('media/logo.png', __DIR__);
        $finetuning = stripcslashes(get_option('uploadcare_finetuning', ''));

        $context = <<<HTML
<div style="float: left">
  <a class="button" style="padding-left: .4em;" href="javascript: uploadcareMediaButton();">
    <span class="wp-media-buttons-icon" style="padding-right: 2px; vertical-align: text-bottom; background: url('{$img}') no-repeat 0px 0px;">
    </span>Add Media</a>
</div>
<div style="float: left">
  <a href="#" class="button insert-media add_media" data-editor="content" title="Wordpress Media Library">
    <span class="wp-media-buttons-icon"></span>Wordpress Media Library
  </a>
</div>
<style type="text/css">#wp-content-media-buttons>a:first-child { display: none }</style>
<script type="text/javascript">{$finetuning}</script>
HTML;
        return $context;
    }

    /**
     * @param string $url
     * @return string
     */
    public function uc_get_attachment_url($url)
    {
        $post = \get_post();
        if (!$post instanceof WP_Post) {
            return $url;
        }

        if (!($uc_url = (string) \get_post_meta($post->ID, 'uploadcare_url', true))) {
            return $url;
        }

        return $uc_url;
    }

    public function uploadcare_media_menu(array $tabs)
    {
        $tabs['uploadcare_files'] = __('Uploadcare', $this->plugin_name);

        return $tabs;
    }

    public function uploadcare_media_files_menu_handle()
    {
        return \wp_iframe(require \dirname(__DIR__) . '/includes/uploadcare_media_files_menu_handle.php');
    }

    public function uploadcare_handle()
    {
        $file_url = $_POST['file_url'];
        $file = $this->api->file()->fileInfo($file_url);

        $attachment_id = $this->attach($file);
        $fileUrl = \get_post_meta($attachment_id, '_wp_attached_file', true);
        $isLocal = false;
        if(\get_post_meta($attachment_id, '_uc_is_local_file', true)) {
            $isLocal = true;
            $uploadBaseUrl = \wp_upload_dir(false, false, false)["baseurl"];
            $fileUrl = "$uploadBaseUrl/$fileUrl";
        }

        $result = [
            'attach_id' => $attachment_id,
            'fileUrl' => $fileUrl,
            'isLocal' => $isLocal,
        ];

        return \json_encode($result);
    }

    public function uploadcare_media_upload()
    {
        $fineTuning = stripcslashes(get_option('uploadcare_finetuning', ''));

        $html = <<<HTML
<p class="uploadcare-picker">
      <a  id="uploadcare-post-upload-ui-btn"
          class="button button-hero"
          style="background: url('https://ucarecdn.com/assets/images/logo.png') no-repeat 5px 5px; padding-left: 44px;"
          href="javascript:ucPostUploadUiBtn();">
        Upload via Uploadcare
      </a>
    </p>
    <p class="max-upload-size">Maximum upload file size: 100MB (or more).</p>
    <script type="text/javascript">$fineTuning</script>
HTML;

        return $html;
    }

    private function attach(\Uploadcare\Interfaces\File\FileInfoInterface $file)
    {
        $user = \get_current_user();
        $fileName = $file->getOriginalFilename();
        $title = $fileName;

        $attachment = [
            'post_author'    => $user,
            'post_date'      => date('Y-m-d H:i:s'),
            'post_type'      => 'attachment',
            'post_title'     => $title,
            'post_parent'    => (!empty($_REQUEST['post_id']) ? $_REQUEST['post_id'] : null),
            'post_status'    => 'inherit',
            'post_mime_type' => $file->getMimeType(),
        ];

        $isImage = $file->isImage();
        $attachment_id = \wp_insert_post($attachment, true);
        $meta = $isImage ? $this->get_final_dim($file) : ['width' => null, 'height' => null];

        if (get_option('uploadcare_download_to_server')) {
            $attached_file = $this->download($file);
            \add_post_meta($attachment_id, '_uc_is_local_file', true, true);
        } else {
            $attached_file = $file->getUrl();
            \add_post_meta($attachment_id, 'uploadcare_url', $attached_file, true);
        }

        \add_post_meta($attachment_id, '_wp_attached_file', $attached_file, true);
        \add_post_meta($attachment_id, '_wp_attachment_metadata', $meta, true);

        return $attachment_id;
    }

    private function get_final_dim(\Uploadcare\Interfaces\File\FileInfoInterface $file)
    {
        $imageInfo = $file->getImageInfo();
        if (!$imageInfo instanceof \Uploadcare\Interfaces\File\ImageInfoInterface)
            return ['width' => null, 'height' => null];

        return [
            'width' => $imageInfo->getWidth(),
            'height' => $imageInfo->getHeight(),
        ];
    }

    private function download(\Uploadcare\Interfaces\File\FileInfoInterface $file)
    {
        $contents = \wp_remote_get($file->getUrl());
        $dir = \wp_upload_dir();

        $localFilename = 'uploadcare' . $dir['subdir'] . '/' . $file->getUuid() . '.' . $file->getOriginalFilename();
        \wp_mkdir_p($dir['basedir'] . \dirname($localFilename));

        \file_put_contents($dir['basedir'] . '/' . $localFilename, $contents['body']);

        return $localFilename;
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
