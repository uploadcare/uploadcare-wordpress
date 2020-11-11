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

    /**
     * UcAdmin constructor.
     *
     * @param $plugin_name
     * @param $version
     */
    public function __construct($plugin_name, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;

        $this->ucConfig = Configuration::create(\get_option('uploadcare_public'), \get_option('uploadcare_secret'), ['framework' => UploadcareUserAgent()]);
        $this->api = new Api($this->ucConfig);
    }

    public function loadAdminCss()
    {
        \wp_enqueue_style('uc-editor');
    }

    /**
     * Link from plugins list to settings.
     *
     * @return array
     * @noinspection HtmlUnknownTarget
     */
    public function plugin_action_links(array $links)
    {
        $url = \esc_url(\add_query_arg('page', 'uploadcare', \get_admin_url().'admin.php'));
        $settings_link = \sprintf('<a href=\'%s\'>%s</a>', $url, __('Settings', $this->plugin_name));

        $links[] = $settings_link;

        return $links;
    }

    /**
     * Admin-part initialization.
     * Calls on `init` hook.
     *
     * @see UploadcareMain::define_admin_hooks()
     */
    public function uploadcare_plugin_init()
    {
        $pluginDirUrl = \plugin_dir_url(\dirname(__DIR__).'/uploadcare.php');
        \wp_register_script('uploadcare-widget', self::WIDGET_URL, ['jquery'], $this->version);
        \wp_register_script('uploadcare-config', $pluginDirUrl.'js/config.js', ['uploadcare-widget'], $this->version);
        \wp_localize_script('uploadcare-config', 'WP_UC_PARAMS', $this->getJsConfig());
        \wp_register_script('uploadcare-main', $pluginDirUrl.'js/main.js', ['uploadcare-config'], $this->version);
        \wp_register_script('image-block', $pluginDirUrl.'/compiled-js/blocks.js', [], $this->version, true);
        \wp_localize_script('uc-config', 'WP_UC_PARAMS', $this->getJsConfig());
        \wp_register_style('uploadcare-style', $pluginDirUrl.'css/uploadcare.css', [], $this->version);
        \wp_register_style('uc-editor', $pluginDirUrl.'/compiled-js/blocks.css', [], $this->version);
    }

    /**
     * Calls on `admin_enqueue_scripts`.
     *
     * @param string $hook
     */
    public function add_uploadcare_js_to_admin($hook)
    {
        $hooks = ['post.php', 'post-new.php', 'media-new.php', 'upload.php'];
        if (!\in_array($hook, $hooks, true)) {
            return;
        }

        \wp_enqueue_script('uploadcare-main');
        \wp_enqueue_style('uploadcare-style');

        \wp_enqueue_script('uc-config');
        \wp_enqueue_script('image-block', null, require \dirname(__DIR__).'/compiled-js/blocks.asset.php');
        \wp_enqueue_style('uc-editor');
    }

    /**
     * Calls on `wp_ajax_{$action}` (in this case — `wp_ajax_uploadcare_handle`).
     *
     * @see https://developer.wordpress.org/reference/hooks/wp_ajax_action/
     */
    public function uploadcare_handle()
    {
        $id = $this->fileId($_POST['file_url']);

        $file = $this->api->file()->fileInfo($id);
        $attachment_id = $this->attach($file);
        $fileUrl = \get_post_meta($attachment_id, '_wp_attached_file', true);

        $result = [
            'attach_id' => $attachment_id,
            'fileUrl' => $fileUrl,
            'isLocal' => false,
        ];

        echo \json_encode($result);
        \wp_die();
    }

    /**
     * Calls on `post-upload-ui`, adds uploadcare button to media library.
     */
    public function uploadcare_media_upload()
    {
        $sign = __('Click to upload any file from anywhere', $this->plugin_name);
        $btn = __('Upload via Uploadcare', $this->plugin_name);
        $href = 'javascript:ucPostUploadUiBtn();';

        if ('add' !== \get_current_screen()->action) {
            $href = \admin_url().'media-new.php';
            $sign .= ' '.__('from Wordpress upload page');
        }

        echo <<<HTML
<div class="uc-picker-wrapper">
    <p class="uploadcare-picker">
        <a id="uploadcare-post-upload-ui-btn"
           class="button button-hero"
           style="background: url('https://ucarecdn.com/assets/images/logo.png') no-repeat 5px 5px; padding-left: 44px;"
           href="$href">
            $btn
        </a>
    </p>
    <p class="max-upload-size">$sign</p>
</div>
HTML;
    }

    /**
     * Render the plugin settings menu.
     * Calls on `admin_menu` hook.
     */
    public function uploadcare_settings_actions()
    {
        \add_options_page('Uploadcare', 'Uploadcare', 'upload_files', 'uploadcare', [$this, 'uploadcare_settings']);
    }

    public function uploadcare_settings()
    {
        include \dirname(__DIR__).'/includes/uploadcare_settings.php';
    }

    /**
     * Calls on `delete_attachment` hook, deletes the image from Uploadcare.
     *
     * @param int      $postId
     * @param \WP_Post $post
     */
    public function attachmentDelete($postId, $post)
    {
        if (!$post instanceof \WP_Post || !($url = \get_post_meta($postId, 'uploadcare_url', true))) {
            return;
        }

        $uuid = $this->fileId($url);
        $this->api->file()->deleteFile($uuid);
    }

    // filters

    /**
     * Calls by `wp_get_attachment_url` filter.
     *
     * @param string $url
     * @param int    $post_id
     *
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
     * Calls by `load_image_to_edit_attachmenturl` filter.
     *
     * @param string $url
     * @param int    $id
     *
     * @return string
     */
    public function uc_load($url, $id)
    {
        if (!get_post_meta($id, 'uploadcare_url', true)) {
            return $url;
        }

        $file = $this->api->file()->fileInfo($this->fileId($url));
        $fileName = $file->getOriginalFilename();

        return $url.\urlencode($fileName);
    }

    /**
     * Calls by `wp_save_image_editor_file` filter.
     *
     * @param bool|null       $override
     * @param string          $filename
     * @param WP_Image_Editor $image
     * @param string          $mime_type
     * @param int             $post_id
     *
     * @return mixed
     *
     * @throws Exception
     */
    public function uc_save_image_editor_file($override, $filename, $image, $mime_type, $post_id)
    {
        if (!$image instanceof WP_Image_Editor || !\get_post_meta($post_id, 'uploadcare_url', true)) {
            return $override;
        }

        $id = $this->fileId(\get_post_meta($post_id, 'uploadcare_url', true));
        $oldFile = $this->api->file()->deleteFile($id);

        $tempFile = $this->storeTempFile($image, $oldFile);
        $newFile = $this->api->uploader()->fromPath($tempFile);
        $this->api->file()->storeFile($newFile);

        $updatedUrl = \sprintf('https://%s/%s/', \get_option('uploadcare_cdn_base'), $newFile->getUuid());

        \update_post_meta($post_id, 'uploadcare_url', $updatedUrl);
        \update_post_meta($post_id, '_wp_attached_file', $updatedUrl);
        \unlink($tempFile);

        $this->changeImageInPosts($oldFile, $newFile);
        $this->changePostMeta($oldFile, $newFile);

        return true;
    }

    private function changePostMeta(FileInfoInterface $oldFile, FileInfoInterface $newFile)
    {
        global $wpdb;
        $query = \sprintf('SELECT post_id, meta_key, meta_value FROM `%s` WHERE meta_value LIKE \'%%%s%%\'', \sprintf('%spostmeta', $wpdb->prefix), $oldFile->getUuid());
        $result = $wpdb->get_results($query, ARRAY_A);
        foreach ($result as $value) {
            $postId = isset($value['post_id']) ? $value['post_id'] : null;
            $metaKey = isset($value['meta_key']) ? $value['meta_key'] : null;
            $metaValue = isset($value['meta_value']) ? $value['meta_value'] : null;

            if (null === $postId || null === $metaKey || null === $metaValue) {
                continue;
            }

            $metaValue = \str_replace($oldFile->getUuid(), $newFile->getUuid(), $metaValue);
            \update_post_meta($postId, $metaKey, $metaValue);
        }
    }

    /**
     * @see https://wordpress.stackexchange.com/questions/310301/check-what-gutenberg-blocks-are-in-post-content
     *
     * @param FileInfoInterface|string $oldFile  Old file UUID
     * @param FileInfoInterface|string $newFile  new file
     * @param callable                 $callback
     */
    public function changeImageInPosts($oldFile, $newFile, $callback = null)
    {
        $from = $oldFile instanceof FileInfoInterface ? $oldFile->getUuid() : $oldFile;
        $to = $newFile instanceof FileInfoInterface ? $newFile->getUuid(): $newFile;

        global $wpdb;
        $query = \sprintf('SELECT ID FROM `%s` WHERE post_content LIKE \'%%%s%%\'', \sprintf('%sposts', $wpdb->prefix), $from);
        $result = $wpdb->get_col($query);
        if (!\is_array($result) || empty($result)) {
            return;
        }
        foreach ($result as $postId) {
            $post = \get_post((int) $postId);
            if (!$post instanceof WP_Post || !\has_blocks($post)) {
                continue;
            }

            $blocksArray = \parse_blocks($post->post_content);
            $blocksArray = \array_map(function (array $block) {
                return $this->blockArrayToClass($block);
            }, $blocksArray);
            $blocksArray = \array_values(\array_filter($blocksArray));

            if (null === $callback) {
                $blocks = $this->modifyBlocks($blocksArray, $from, $to);
            } else {
                $blocks = $callback($blocksArray, $newFile);
            }

            $post->post_content = \serialize_blocks(\array_map(function (WP_Block_Parser_Block $block) {
                return $this->blockClassToArray($block);
            }, $blocks));

            \wp_update_post($post, true);
        }
    }

    /**
     * @param array|\WP_Block_Parser_Block[] $blocks
     * @param string                         $from
     * @param string                         $changeTo
     *
     * @return array|\WP_Block_Parser_Block[]
     */
    private function modifyBlocks(array $blocks, $from, $changeTo)
    {
        foreach ($blocks as $n => $block) {
            $block->innerHTML = \str_replace($from, $changeTo, $block->innerHTML);
            $innerContent = $block->innerContent;
            foreach ($innerContent as $c => $contentItem) {
                $innerContent[$c] = \str_replace($from, $changeTo, $contentItem);
            }
            $block->innerContent = \array_values($innerContent);
            $blocks[$n] = $block;
        }

        return \array_values($blocks);
    }

    /**
     * @param array $item
     *
     * @return WP_Block_Parser_Block|null
     */
    private function blockArrayToClass(array $item)
    {
        $attributes = [
            'blockName' => 'string',
            'attrs' => 'array',
            'innerBlocks' => 'array',
            'innerHTML' => 'string',
            'innerContent' => 'array',
        ];

        foreach ($attributes as $name => $type) {
            if (!isset($item[$name]) || \gettype($item[$name]) !== $type) {
                return null;
            }
        }

        return new \WP_Block_Parser_Block($item['blockName'], $item['attrs'], $item['innerBlocks'], $item['innerHTML'], $item['innerContent']);
    }

    /**
     * @param \WP_Block_Parser_Block $block
     *
     * @return array
     */
    private function blockClassToArray(WP_Block_Parser_Block $block)
    {
        return (array) $block;
    }

    /**
     * @param WP_Image_Editor $editor
     *
     * @return string
     *
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

    /**
     * Calls on `image_downsize` hook.
     *
     * @param $value
     * @param $id
     * @param string $size
     *
     * @return array|false
     */
    public function uploadcare_image_downsize($value, $id, $size = 'medium')
    {
        if (!$uc_url = get_post_meta($id, 'uploadcare_url', true)) {
            return false;
        }

        $sz = $this->thumbnailSize($size);
        if ($sz) {
            // chop filename part
            $url = preg_replace('/[^\/]*$/', '', $uc_url);
            $url = \sprintf(UploadcareMain::SCALE_CROP_TEMPLATE, $url, $sz);
        } else {
            $url = $uc_url;
        }
        \ULog($size);

        return [
            $url,
            0, // width
            0, // height
            true,
        ];
    }

    /**
     * Calls on `post_thumbnail_html`.
     *
     * @param $html
     * @param $post_id
     * @param $post_thumbnail_id
     * @param $size
     * @param $attr
     *
     * @return string
     */
    public function uploadcare_post_thumbnail_html($html, $post_id, $post_thumbnail_id, $size, $attr)
    {
        if (!$url = get_post_meta($post_id, 'uploadcare_url', true)) {
            return $html;
        }

        $sz = $this->thumbnailSize($size);
        if ($sz) {
            $src = \sprintf(UploadcareMain::SCALE_CROP_TEMPLATE, $url, $sz);
        } else {
            $src = $url;
        }

        /* @noinspection HtmlUnknownTarget */
        return \sprintf(\sprintf('<img src="%s" alt="%s">', $src, __('Preview', $this->plugin_name)));
    }

    /**
     * @param $url
     *
     * @return string
     */
    private function fileId($url)
    {
        return (string) \pathinfo($url, PATHINFO_BASENAME);
    }

    private function thumbnailSize($size = 'thumbnail')
    {
        $arr = $this->getSizeArray($size);
        if (empty($arr)) {
            return false;
        }

        return \implode('x', $arr);
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
            if (9999 === $arr[1]) {
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
            $sizes[$s] = [0, 0];
            if (in_array($s, ['thumbnail', 'medium', 'large'])) {
                $sizes[$s][0] = get_option($s.'_size_w');
                $sizes[$s][1] = get_option($s.'_size_h');
            } else {
                if (isset($_wp_additional_image_sizes[$s])) {
                    $sizes[$s] = [$_wp_additional_image_sizes[$s]['width'], $_wp_additional_image_sizes[$s]['height']];
                }
            }
        }

        return $sizes;
    }

    /**
     * @param FileInfoInterface $file
     * @param int|null          $id existing Post ID
     *
     * @return int|WP_Error
     */
    public function attach(FileInfoInterface $file, $id = null)
    {
        $userId = get_current_user_id();
        $filename = $file->getOriginalFilename();
        $title = $filename;

        $attachment = [
            'post_author' => $userId,
            'post_date' => date('Y-m-d H:i:s'),
            'post_type' => 'attachment',
            'post_title' => $title,
            'post_parent' => (!empty($_REQUEST['post_id']) ? $_REQUEST['post_id'] : null),
            'post_status' => 'inherit',
            'post_mime_type' => $file->getMimeType(),
        ];
        if (null !== $id) {
            $exists = \get_post($id, ARRAY_A);

            if (\is_array($exists)) {
                $attachment = \array_merge($attachment, $exists);
            }
            $attachment['id'] = $id;
        }

        $isImage = $file->isImage();
        $attachment_id = wp_insert_post($attachment, true);
        $meta = $isImage ? $this->getFinalDim($file) : ['width' => null, 'height' => null];

        $attached_file = \sprintf('https://%s/%s/', \get_option('uploadcare_cdn_base'), $file->getUuid());
        \add_post_meta($attachment_id, 'uploadcare_url', $attached_file, true);
        \add_post_meta($attachment_id, 'uploadcare_uuid', $file->getUuid(), true);

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
            $tabs = \implode(' ', $tab_options);
        }

        $baseParams = [
            'public_key' => get_option('uploadcare_public'),
            'previewStep' => false,
            'ajaxurl' => \admin_url('admin-ajax.php'),
            'tabs' => $tabs,
            'cdnBase' => 'https://'.\get_option('uploadcare_cdn_base', 'ucarecdn.com'),
        ];
        if (null !== \get_option('uploadcare_finetuning', null)) {
            $fineTuning = \json_decode(\stripcslashes(\get_option('uploadcare_finetuning')), true);
            if (JSON_ERROR_NONE === \json_last_error()) {
                $baseParams = \array_merge($fineTuning, $baseParams);
            }
        }

        if (get_option('uploadcare_upload_lifetime') > 0) {
            $secureSignature = $this->ucConfig->getSecureSignature();

            return \array_merge($baseParams, [
                    'secureSignature' => $secureSignature->getSignature(),
                    'secureExpire' => $secureSignature->getExpire()->getTimestamp(),
                ]);
        }

        return $baseParams;
    }
}
