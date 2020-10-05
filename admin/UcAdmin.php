<?php

use Uploadcare\Api;
use Uploadcare\Configuration;
use Uploadcare\Interfaces\File\FileInfoInterface;
use Uploadcare\Interfaces\File\ImageInfoInterface;

class UcAdmin
{
    const WIDGET_URL = 'https://ucarecdn.com/libs/widget/3.x/uploadcare.full.min.js';
    const TAB_EFFECTS_URL = 'https://ucarecdn.com/libs/widget-tab-effects/1.x/uploadcare.tab-effects.min.js';

    /**
     * @var string
     */
    private $plugin_name;

    /**
     * @var string
     */
    private $version;

    /**
     * @var Api
     */
    private $api;

    /**
     * @var Configuration
     */
    private $ucConfig;

    public function __construct($plugin_name, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->ucConfig = Configuration::create(\get_option('uploadcare_public'), \get_option('uploadcare_secret'));
        $this->api = new Api($this->ucConfig);

    }

    /**
     * Link from plugins list to settings
     *
     * @param array $links
     * @return array
     * @noinspection HtmlUnknownTarget
     */
    public function plugin_action_links(array $links)
    {
        $url = \esc_url(\add_query_arg('page', 'uploadcare', \get_admin_url() . 'admin.php'));
        $settings_link = \sprintf('<a href=\'%s\'>%s</a>', $url, __('Settings'));

        $links[] = $settings_link;

        return $links;
    }

    public function uploadcare_plugin_init()
    {
        wp_register_script('uploadcare-widget', self::WIDGET_URL, ['jquery'], $this->version);
        wp_register_script('uploadcare-tab-effects', self::TAB_EFFECTS_URL, [], $this->version);

        $pluginDirUrl = \plugin_dir_url(\dirname(__DIR__) . '/uploadcare.php');

        wp_register_script('uploadcare-config',$pluginDirUrl . 'js/config.js',['uploadcare-widget', 'uploadcare-tab-effects'], $this->version);
        wp_localize_script('uploadcare-config', 'WP_UC_PARAMS', $this->getJsConfig());

        wp_register_script('uploadcare-main', $pluginDirUrl . 'js/main.js', ['uploadcare-config'], $this->version);
        wp_register_script('uploadcare-shortcodes', $pluginDirUrl . 'js/shortcodes.js', ['uploadcare-config'], $this->version);

        wp_register_style('uploadcare-style', $pluginDirUrl . 'css/uploadcare.css', $this->version);

//        $this->registerUserImages();
    }

    /**
     * @param string $hook
     */
    public function add_uploadcare_js_to_admin($hook)
    {
        $hooks = ['post.php', 'post-new.php', 'media-new.php', 'upload.php'];
        if (!\in_array($hook, $hooks, true)) {
            return;
        }

        wp_enqueue_script('uploadcare-main');
        wp_enqueue_style('uploadcare-style');
    }

    public function uploadcare_handle()
    {
        $id = \str_replace([
            'https://',
            \get_option('uploadcare_cdn_base')
        ], '', $_POST['file_url']);

        $file = $this->api->file()->fileInfo(\trim($id, '/'));
        $file->store();
        $attachment_id = $this->attach($file);
        $fileUrl = get_post_meta($attachment_id, '_wp_attached_file', true);
        $isLocal = false;
        if (\get_post_meta($attachment_id, '_uc_is_local_file', true)) {
            $isLocal = true;
            $uploadBaseUrl = \wp_upload_dir(false, false, false)["baseurl"];
            $fileUrl = "$uploadBaseUrl/$fileUrl";
        }

        $result = [
            'attach_id' => $attachment_id,
            'fileUrl' => $fileUrl,
            'isLocal' => $isLocal,
        ];

        echo \json_encode($result);
        \wp_die();
    }

    public function uploadcare_shortcode_handle()
    {
        // store file
        $file_id = $_POST['file_id'];
        $post_id = $_POST['post_id'];
        $file = $this->api->file()->fileInfo($file_id);
        $file->store();

        // create user image
        $this->attachUserImage($file, $post_id);
    }

    /*
    public function uploadcare_media_files_menu_handle()
    {
        global $wpdb;
        wp_iframe([$this, 'uploadcare_media_files']);
    }

    public function uploadcare_media_files()
    {
        global $wpdb;
        require_once \dirname(__DIR__) . '/includes/uploadcare_media_files_menu_handle.php';
    }
    */

    public function uploadcare_media_upload()
    {
        $sign = __('Upload file size 100MB or more', $this->plugin_name);

        print <<<HTML
<div class="uc-picker-wrapper">
    <p class="uploadcare-picker">
        <a id="uploadcare-post-upload-ui-btn"
           class="button button-hero"
           style="background: url('https://ucarecdn.com/assets/images/logo.png') no-repeat 5px 5px; padding-left: 44px;"
           href="javascript:ucPostUploadUiBtn();">
            Upload via Uploadcare
        </a>
    </p>
    <p class="max-upload-size">$sign</p>
</div>
HTML;
    }

    public function uploadcare_display_thumbnail_column($col)
    {
        if (!\function_exists('the_post_thumbnail'))
            echo '-';

        if ($col === 'uploadcare_post_thumb') {
            the_post_thumbnail('thumbnail');
        }
    }

    public function uploadcare_settings_actions()
    {
        add_options_page('Uploadcare', 'Uploadcare', 'upload_files', 'uploadcare', [$this, 'uploadcare_settings']);
    }

    public function uploadcare_settings()
    {
        include \dirname(__DIR__) . '/includes/uploadcare_settings.php';
    }

    // filters
   public function uploadcare_get_attachment_url($url, $post_id)
   {
       if (!$uc_url = get_post_meta($post_id, 'uploadcare_url', true)) {
           return $url;
       }
       return $uc_url;
   }

    public function uploadcare_image_downsize($value, $id, $size = 'medium')
    {
        if (!$uc_url = get_post_meta($id, 'uploadcare_url', true)) {
            return false;
        }

        $sz = $this->thumbnailSize($size);
        if ($sz) {
            // chop filename part
            $url = preg_replace('/[^\/]*$/', '', $uc_url);

            $uploadcare_dont_scale_crop = get_option('uploadcare_dont_scale_crop');
            if ($uploadcare_dont_scale_crop) {
                $url .= '-/stretch/off/-/preview/' . $sz . '/';
            } else {
                $url .= '-/stretch/off/-/scale_crop/' . $sz . '/center/';
            }
        } else {
            $url = $uc_url;
        }
        return [
            $url,
            0, // width
            0, // height
            true,
        ];
    }

    public function uploadcare_post_thumbnail_html($html, $post_id, $post_thumbnail_id, $size, $attr)
    {
        if (!\get_option('uploadcare_replace_featured_image')) {
            return $html;
        }

        $meta = get_post_meta($post_id, 'uploadcare_featured_image');
        if (empty($meta)) {
            return $html;
        }
        $url = $meta[0];
        $sz = $this->thumbnailSize($size);
        if ($sz) {
            $src = "{$url}-/stretch/off/-/scale_crop/$sz/center/";

            $uploadcare_dont_scale_crop = \get_option('uploadcare_dont_scale_crop');
            if ($uploadcare_dont_scale_crop) {
                $src = "{$url}-/stretch/off/-/preview/{$sz}/";
            }
        } else {
            $src = $url;
        }

        /** @noinspection HtmlUnknownTarget */
        return \sprintf(\sprintf('<img src="%s" alt="%s">', $src, __('Preview', $this->plugin_name)));
    }

    /*
    public function uploadcare_media_menu($tabs)
    {
        $newtab = array(
            'uploadcare_files' => __('Uploadcare', 'uploadcare_files')
        );
        return array_merge($newtab, $tabs);
    }
    */

    public function uploadcare_add_uc_user_image_thumbnail_column(array $cols)
    {
        $cols['uploadcare_post_thumb'] = __('Thumb', $this->plugin_name);

        return $cols;
    }

    private function thumbnailSize($size = 'thumbnail')
    {
        $arr = $this->getSizeArray($size);
        if (!empty($arr)) {
            return false;
        }
        return implode('x', $arr);
    }

    private function getSizeArray($size)
    {
        if (is_array($size)) {
            return $size;
        }

        $sizes = $this->getSizes();
        if (array_key_exists($size, $sizes)) {
            $arr = $sizes[$size];

            // handle "unlimited" width
            // 9999 -> 2048
            // WP uses 9999 to indicate unlimited width for images,
            // at the moment max width for ucarecdn operaions is 2048
            if ($arr[1] === 9999) {
                $arr[1] = 2048;
            }
            return $arr;
        }
        return [];
    }

    private function getSizes()
    {
        global $_wp_additional_image_sizes;
        $sizes = [];
        foreach (get_intermediate_image_sizes() as $s) {
            $sizes[$s] = array(0, 0);
            if (in_array($s, ['thumbnail', 'medium', 'large'])) {
                $sizes[$s][0] = get_option($s . '_size_w');
                $sizes[$s][1] = get_option($s . '_size_h');
            } else {
                if (isset($_wp_additional_image_sizes[$s])) {
                    $sizes[$s] = [$_wp_additional_image_sizes[$s]['width'], $_wp_additional_image_sizes[$s]['height'],];
                }
            }
        }
        return $sizes;
    }

    private function attachUserImage(FileInfoInterface $file, $post_id)
    {
        $attachment_id = $this->attach($file);
        $user = \get_current_user_id();

        $filename = $file->getOriginalFilename();
        $title = $filename;

        $user_image = array(
            'post_author' => $user,
            'post_date' => date('Y-m-d H:i:s'),
            'post_type' => 'uc_user_image',
            'post_title' => $title,
            'post_parent' => $post_id,
            'post_status' => 'private',
            'post_mime_type' => $file->getMimeType(),
        );

        $user_image_id = \wp_insert_post($user_image, true);
        \set_post_thumbnail($user_image_id, $attachment_id);
    }

    private function attach(FileInfoInterface $file)
    {
        $userId = get_current_user_id();
        $filename = $file->getOriginalFilename();
        $title = $filename;

        $attachment = array(
            'post_author' => $userId,
            'post_date' => date('Y-m-d H:i:s'),
            'post_type' => 'attachment',
            'post_title' => $title,
            'post_parent' => (!empty($_REQUEST['post_id']) ? $_REQUEST['post_id'] : null),
            'post_status' => 'inherit',
            'post_mime_type' => $file->getMimeType(),
        );
        $isImage = $file->isImage();
        $attachment_id = wp_insert_post($attachment, true);
        $meta = $isImage ? $this->getFinalDim($file) : array('width' => null, 'height' => null);

        if (get_option('uploadcare_download_to_server')) {
            $attached_file = $this->download($file);
            add_post_meta($attachment_id, '_uc_is_local_file', true, true);
        } else {
            $attached_file = \sprintf('https://%s/%s/', \get_option('uploadcare_cdn_base'), $file->getUuid());
            add_post_meta($attachment_id, 'uploadcare_url', $attached_file, true);
        }

        add_post_meta($attachment_id, '_wp_attached_file', $attached_file, true);
        add_post_meta($attachment_id, '_wp_attachment_metadata', $meta, true);

        return $attachment_id;
    }

    private function download(FileInfoInterface $file)
    {
        // downloading contents of image
        $contents = \wp_remote_get($file->getUrl());

        $dirInfo = \wp_upload_dir();
        $absPath = $dirInfo['basedir'] . '/';
        $localFilename = sprintf('uploadcare-%s/%s.%s', $dirInfo['subdir'], $file->getUuid(), $file->getOriginalFilename());

        // creating folders tree
        \wp_mkdir_p($absPath . \dirname($localFilename));

        // saving image
        \file_put_contents($absPath . $localFilename, $contents['body']);

        return $localFilename;
    }

    private function getFinalDim(FileInfoInterface $file)
    {
        $imageInfo = $file->getImageInfo();
        if (!$imageInfo instanceof ImageInfoInterface)
            return ['width' => null, 'height' => null];

        return [
            'width' => $imageInfo->getWidth(),
            'height' => $imageInfo->getHeight(),
        ];
    }

    private function getJsConfig()
    {
        $tab_options = get_option('uploadcare_source_tabs', [
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
        if (in_array('all', $tab_options, true)) {
            $tabs = 'all';
        } else {
            $tabs = implode(' ', $tab_options);
        }

        $effects = \get_option('uploadcare_tab_effects', [
            'crop',
            'rotate',
            'sharp',
            'enhance',
            'grayscale',
        ]);
        if (count($effects) === 1 && in_array('none', $effects, true)) {
            $previewStep = "false";
            $effects = array();
        } else {
            $previewStep = "true";
            $noneInd = array_search('none', $effects, true);
            if ($noneInd) {
                unset($effects[$noneInd]);
            }
        }

        $baseParams = array(
            'public_key' => get_option('uploadcare_public'),
            'original' => get_option('uploadcare_original') ? "true" : "false",
            'multiple' => get_option('uploadcare_multiupload') ? "true" : "false",
            'previewStep' => $previewStep,
            'effects' => \implode(',', $effects),
            'ajaxurl' => \admin_url('admin-ajax.php'),
            'tabs' => $tabs,
            'cdnBase' => 'https://' . \get_option('uploadcare_cdn_base', 'ucarecdn.com'),
        );

        if (get_option('uploadcare_upload_lifetime') > 0) {
            $secureSignature = $this->ucConfig->getSecureSignature();

            return array_merge($baseParams, array(
                'secureSignature' => $secureSignature->getSignature(),
                'secureExpire' => $secureSignature->getExpire()->getTimestamp(),
            ));
        }

        return $baseParams;
    }

    /*
    private function registerUserImages()
    {
        $image_type_labels = array(
            'name' => _x('User images', 'post type general name'),
            'singular_name' => _x('Uploadcare User Image', 'post type singular name'),
            'add_new' => _x('Add New User Image', 'image'),
            'add_new_item' => __('Add New User Image'),
            'edit_item' => __('Edit User Image'),
            'new_item' => __('Add New User Image'),
            'all_items' => __('View User Images'),
            'view_item' => __('View User Image'),
            'search_items' => __('Search User Images'),
            'not_found' => __('No User Images found'),
            'not_found_in_trash' => __('No User Images found in Trash'),
            'parent_item_colon' => '',
            'menu_name' => 'User Images'
        );

        $image_type_args = array(
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
            'supports' => array('title', 'author', 'thumbnail')
        );

        $type = register_post_type('uc_user_image', $image_type_args);

        $image_category_labels = array(
            'name' => _x('User Image Categories', 'taxonomy general name'),
            'singular_name' => _x('User Image', 'taxonomy singular name'),
            'search_items' => __('Search User Image Categories'),
            'all_items' => __('All User Image Categories'),
            'parent_item' => __('Parent User Image Category'),
            'parent_item_colon' => __('Parent User Image Category:'),
            'edit_item' => __('Edit User Image Category'),
            'update_item' => __('Update User Image Category'),
            'add_new_item' => __('Add New User Image Category'),
            'new_item_name' => __('New User Image Name'),
            'menu_name' => __('User Image Categories'),
        );

        $image_category_args = array(
            'hierarchical' => true,
            'labels' => $image_category_labels,
            'show_ui' => true,
            'query_var' => true,
            'rewrite' => array('slug' => 'uploadcare_user_image_category'),
        );

        register_taxonomy('uploadcare_user_image_category', array('uc_user_image'), $image_category_args);
    }
    */
}
