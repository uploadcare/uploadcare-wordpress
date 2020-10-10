<?php

use Uploadcare\Api;
use Uploadcare\Configuration;
use Uploadcare\Interfaces\File\FileInfoInterface;
use Uploadcare\Interfaces\File\ImageInfoInterface;

class UcAdmin
{
    const WIDGET_URL = 'https://ucarecdn.com/libs/widget/3.x/uploadcare.full.min.js';

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

        $pluginDirUrl = \plugin_dir_url(\dirname(__DIR__) . '/uploadcare.php');

        wp_register_script('uploadcare-config', $pluginDirUrl . 'js/config.js', ['uploadcare-widget'], $this->version);
        wp_localize_script('uploadcare-config', 'WP_UC_PARAMS', $this->getJsConfig());

        wp_register_script('uploadcare-main', $pluginDirUrl . 'js/main.js', ['uploadcare-config'], $this->version);
        wp_register_script('uploadcare-shortcodes', $pluginDirUrl . 'js/shortcodes.js', ['uploadcare-config'], $this->version);

        wp_register_style('uploadcare-style', $pluginDirUrl . 'css/uploadcare.css', $this->version);
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
        $id = $this->fileId($_POST['file_url']);

        $file = $this->api->file()->fileInfo(\trim($id, '/'));
        $file->store();
        $attachment_id = $this->attach($file);
        $fileUrl = \get_post_meta($attachment_id, '_wp_attached_file', true);
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


    public function uploadcare_media_upload()
    {
        $sign = __('Upload file size 100MB or more', $this->plugin_name);
        $btn = __('Upload via Uploadcare', $this->plugin_name);

        print <<<HTML
<div class="uc-picker-wrapper">
    <p class="uploadcare-picker">
        <a id="uploadcare-post-upload-ui-btn"
           class="button button-hero"
           style="background: url('https://ucarecdn.com/assets/images/logo.png') no-repeat 5px 5px; padding-left: 44px;"
           href="javascript:ucPostUploadUiBtn();">
            $btn
        </a>
    </p>
    <p class="max-upload-size">$sign</p>
</div>
HTML;
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

    /**
     * Calls by `wp_get_attachment_url` filter
     *
     * @param string $url
     * @param int $post_id
     * @return string
     */
    public function uc_get_attachment_url($url, $post_id)
    {
        if (!($uc_url = get_post_meta($post_id, 'uploadcare_url', true))) {
            return $url;
        }

        return $uc_url;
    }

    /**
     * Calls by `load_image_to_edit_attachmenturl` filter
     *
     * @param string $url
     * @param int $id
     * @return string
     */
    public function uc_load($url, $id)
    {
        if (!get_post_meta($id, 'uploadcare_url', true)) {
            return $url;
        }

        $file = $this->api->file()->fileInfo($this->fileId($url));
        $fileName = $file->getOriginalFilename();

        return $url . \urlencode($fileName);
    }

    /**
     * Calls by `wp_save_image_editor_file` filter
     *
     * @param bool|null $override
     * @param string $filename
     * @param WP_Image_Editor $image
     * @param string $mime_type
     * @param int $post_id
     * @return mixed
     * @throws Exception
     */
    public function uc_save_image_editor_file($override, $filename, $image, $mime_type, $post_id)
    {
        if (!$image instanceof WP_Image_Editor) {
            return $override;
        }
        if (!\get_post_meta($post_id, 'uploadcare_url', true)) {
            return $override;
        }

        $cdn = \sprintf('https://%s', \get_option('uploadcare_cdn_base'));
        $start = \strpos($filename, $cdn);
        $url = \substr($filename, $start, 36 + \strlen($cdn) + 1); // UUID length is 36

        $id = $this->fileId($url);
        $info = $this->api->file()->fileInfo($id);
        $tempFile = $this->storeTempFile($image, $info);
        $this->api->file()->deleteFile($id);
        $newFile = $this->api->uploader()->fromPath($tempFile);
        \update_post_meta($post_id, 'uploadcare_url', \sprintf('%s/%s/', $cdn, $newFile->getUuid()));

        return true;
    }

    /**
     * @param WP_Image_Editor $editor
     * @param FileInfoInterface $info
     *
     * @return string
     * @throws Exception
     */
    private function storeTempFile($editor, FileInfoInterface $info)
    {
        $filename = $info->getOriginalFilename();
        $path = sprintf('%s/%s', \sys_get_temp_dir(), $filename);
        $save = $editor->save($path);

        if (!$save) {
            throw new \Exception('Cannot save file');
        }

        return $path;
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
            $url .= '-/stretch/off/-/scale_crop/' . $sz . '/center/';
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
        } else {
            $src = $url;
        }

        /** @noinspection HtmlUnknownTarget */
        return \sprintf(\sprintf('<img src="%s" alt="%s">', $src, __('Preview')));
    }

    private function fileId($url)
    {
        return \trim(\str_replace(['https://', \get_option('uploadcare_cdn_base')], '', $url), '/');
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
        if (\is_array($size)) {
            return $size;
        }

        $sizes = $this->getSizes();
        if (\array_key_exists($size, $sizes)) {
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

        $attached_file = \sprintf('https://%s/%s/', \get_option('uploadcare_cdn_base'), $file->getUuid());
        \add_post_meta($attachment_id, 'uploadcare_url', $attached_file, true);

        \add_post_meta($attachment_id, '_wp_attached_file', $attached_file, true);
        \add_post_meta($attachment_id, '_wp_attachment_metadata', $meta, true);

        return $attachment_id;
    }

    private function getFinalDim(FileInfoInterface $file)
    {
        $imageInfo = $file->getImageInfo();
        if (!$imageInfo instanceof ImageInfoInterface) {
            return ['width' => null, 'height' => null];
        }

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

        $baseParams = array(
            'public_key' => get_option('uploadcare_public'),
            'previewStep' => false,
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
}
