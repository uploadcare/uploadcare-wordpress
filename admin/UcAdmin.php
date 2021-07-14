<?php

use Twig\Environment;
use Uploadcare\Api;
use Uploadcare\Configuration;
use Uploadcare\Interfaces\File\FileInfoInterface;
use Uploadcare\Interfaces\File\ImageInfoInterface;
use Uploadcare\Interfaces\Response\ProjectInfoInterface;

class UcAdmin
{
    public const WIDGET_URL = 'https://ucarecdn.com/libs/widget/3.x/uploadcare.full.min.js';

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
     * @var Environment
     */
    private $twig;

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
        $this->twig = TwigFactory::create();
    }

    public function projectInfo(): ProjectInfoInterface
    {
        return $this->api->project()->getProjectInfo();
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
        $url = \esc_url(\add_query_arg('page', 'uploadcare', \get_admin_url() . 'admin.php'));
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
        $pluginDirUrl = \plugin_dir_url(\dirname(__DIR__) . '/uploadcare.php');
        \wp_register_script('uploadcare-elements', 'https://uploadcare.dev/elements.js?' . \get_option('uploadcare_public'), [], null, false);
        \wp_register_script('uploadcare-widget', self::WIDGET_URL, ['jquery'], $this->version);
        \wp_register_script('uploadcare-config', $pluginDirUrl . 'js/config.js', ['uploadcare-widget'], $this->version);
        \wp_localize_script('uploadcare-config', 'WP_UC_PARAMS', $this->getJsConfig());
        \wp_register_script('image-block', $pluginDirUrl . 'compiled-js/blocks.js', [], $this->version, true);
        \wp_localize_script('uc-config', 'WP_UC_PARAMS', $this->getJsConfig());
        \wp_register_style('uploadcare-style', $pluginDirUrl . 'css/uploadcare.css', [], $this->version);
        \wp_register_style('uc-editor', $pluginDirUrl . 'compiled-js/blocks.css', [], $this->version);

        \wp_register_script('admin-js', $pluginDirUrl . 'compiled-js/admin.js', [
            'image-edit', 'jquery', 'media', 'uploadcare-config',
        ], $this->version);
        \wp_register_style('admin-css', $pluginDirUrl . 'compiled-js/admin.css', [], $this->version);
    }

    /**
     * Calls on `admin_enqueue_scripts`.
     *
     * @param string $hook
     */
    public function add_uploadcare_js_to_admin(string $hook): void
    {
        \wp_enqueue_script('uploadcare-config');
        \wp_enqueue_script('uc-config');

        $hooks = ['post.php', 'post-new.php', 'media-new.php', 'upload.php'];
        if (!\in_array($hook, $hooks, true)) {
            return;
        }

        \wp_enqueue_script('uploadcare-main');
        \wp_enqueue_style('uploadcare-style');

        \wp_enqueue_script('uploadcare-elements');
        \wp_enqueue_style('uc-editor');

        \wp_enqueue_style('admin-css');
        \wp_enqueue_script('admin-js', null, require \dirname(__DIR__) . '/compiled-js/admin.asset.php');

        if (\in_array($hook, ['post.php', 'post-new.php'], true)) {
            $scr = \get_current_screen();
            if ($scr !== null && \method_exists($scr, 'is_block_editor') && $scr->is_block_editor()) {
                \wp_enqueue_script('image-block', null, require \dirname(__DIR__) . '/compiled-js/blocks.asset.php');
            }
        }
    }

    /**
     * Add column to posts / pages table.
     * Calls by `manage_{$type}_posts_columns` filter (`manage_post_posts_columns` and `manage_page_posts_columns` in this case).
     *
     * @param array $columns
     *
     * @return array
     */
    public function addImagesColumn(array $columns): array
    {
        $columns['uploadcare_images'] = __('Local / remote images');

        return $columns;
    }

    /**
     * Add content to transfer column.
     * Calls by `manage_{$type}_posts_custom_column` action (`manage_post_posts_columns` and `manage_page_posts_columns` in this case).
     *
     * @param string $columnId
     * @param int    $postId
     */
    public function manageImagesColumn(string $columnId, int $postId): void
    {
        if ($columnId !== 'uploadcare_images') {
            return;
        }
        $link = \admin_url(\sprintf('/admin.php?page=transfer-by-post&postId=%d', $postId));
        $count = (new MediaDataLoader())->countMediaForPost($postId);

        $localImages = 0;
        if (($count['total'] ?? 0) > 0) {
            $localImages = $count['total'] - ($count['uploadcare'] ?? 0);
        }
        $ucImages = $count['uploadcare'] ?? 0;
        if ($localImages <= 0 && $ucImages > 0) {
            echo \sprintf('All %d images transferred', $ucImages);

            return;
        }

        if ($localImages <= 0 && $ucImages <= 0) {
            echo 'No images in post';

            return;
        }

        /** @noinspection HtmlUnknownTarget */
        echo \sprintf('%d / %d <a href="%s">Transfer to remote</a>', $localImages, $ucImages, $link);
    }

    /**
     * Calls on `wp_ajax_{$action}` (in this case — `wp_ajax_uploadcare_handle`).
     *
     * @see https://developer.wordpress.org/reference/hooks/wp_ajax_action/
     */
    public function uploadcare_handle()
    {
        $cdnUrl = $_POST['file_url'];
        $uuid = $this->fileId($cdnUrl);

        $file = $this->api->file()->fileInfo($uuid);
        $modifiers = $_POST['uploadcare_url_modifiers'] ?? '';
        if ($modifiers === 'null') {
            $modifiers = '';
        }

        $attachment_id = $this->attach($file, $this->loadPostByUuid($uuid), [
            'uploadcare_url_modifiers' => $modifiers,
        ]);

        $result = [
            'attach_id' => $attachment_id,
            'fileUrl' => $cdnUrl,
            'isLocal' => false,
            'uploadcare_url_modifiers' => $modifiers,
        ];

        echo \json_encode($result);
        \wp_die();
    }

    /**
     * Calls on `wp_ajax_{$action}` (in this case — `wp_ajax_uploadcare_transfer`).
     */
    public function transferUp(): void
    {
        $postId = $_POST['postId'] ?? null;
        if ($postId === null) {
            \wp_die(__('Required parameter is not set', 'uploadcare'), '', 400);
        }
        $result = $this->transferPostUp((int) $postId);
        echo \wp_json_encode($result);
        \wp_die();
    }

    public function transferMultiplyUp(): void
    {
        $posts = $_POST['posts'] ?? null;
        if ($posts === null) {
            \wp_die(__('Required parameter is not set', 'uploadcare'), '', 400);
        }
        // JS sends array as `posts=1,2,3`
        $posts = \explode(',', $posts);

        $result = [];
        foreach ($posts as $postId) {
            $result[] = $this->transferPostUp((int) $postId);
        }
        echo \wp_json_encode($result);
        \wp_die();
    }

    private function transferPostUp(int $postId): array
    {
        if (($uuid = \get_post_meta($postId, 'uploadcare_uuid', true))) {
            try {
                $this->api->file()->fileInfo($uuid);

                return [
                    'file_url' => \wp_get_attachment_image_src($postId),
                    'uploadcare_url_modifiers' => \get_post_meta($postId, 'uploadcare_url_modifiers', true),
                    'postId' => $postId,
                ];
            } catch (\Exception $e) {
                // Do nothing
            }
        }

        $file = \get_attached_file($postId, true);
        if ($file === false || !\is_file($file)) {
            $message = \sprintf('%s %s', __('Unable to load original file', 'uploadcare'), $file);
            \wp_die($message, '', 400);
        }
        try {
            $uploadedFile = $this->api->uploader()->fromPath($file);
        } catch (\Exception $e) {
            $message = \sprintf('%s: %s', __('Unable to upload file', 'uploadcare'), $e->getMessage());
            \wp_die($message, '', 400);
        }
        $this->attach($uploadedFile, $postId);

        return [
            'fileUrl' => \wp_get_attachment_image_src($postId)[0] ?? '',
            'uploadcare_url_modifiers' => '',
            'postId' => $postId,
            'uploadcare_uuid' => $uploadedFile->getUuid(),
        ];
    }

    /**
     * Calls on `wp_ajax_{$action}` (in this case — `wp_ajax_uploadcare_down`).
     */
    public function transferDown(): void
    {
        $postId = $_POST['postId'] ?? null;
        $uuid = $_POST['uuid'] ?? null;
        if ($postId === null || $uuid === null) {
            \wp_die(__('Required parameter is not set', 'uploadcare'), '', 400);
        }

        $post = \get_post($postId);
        if (!$post instanceof \WP_Post) {
            \wp_die(__('Post not found', 'uploadcare'), '', 400);
        }

        $imageId = \get_post_meta($postId, 'uploadcare_uuid', true);
        try {
            $ucFile = $this->api->file()->fileInfo($imageId);
        } catch (\Exception $e) {
            \wp_die(__('Unable to get file file info uploadcare', 'uploadcare'), '', 400);
        }
        $originalUrl = $ucFile->getOriginalFileUrl();
        if ($originalUrl === null || ($ucFileContents = \file_get_contents($originalUrl)) === null) {
            \wp_die(__('Unable to get file from uploadcare', 'uploadcare'), '', 400);
        }

        $uploadDirData = \wp_upload_dir();

        if (($uploadDirData['path'] ?? null) === null) {
            $message = $uploadDirData['error'] ?: __('Unable to get upload directory');
            \wp_die($message, '', 400);
        }

        $filename = $this->filenameFromPostTitle($post, $ucFile->getOriginalFilename());
        $localFilePath = \rtrim($uploadDirData['path'], '/') . '/' . $filename;
        \file_put_contents($localFilePath, $ucFileContents);

        $subdir = \ltrim(($uploadDirData['subdir'] ?? ''), '/');
        \update_post_meta($postId, '_wp_attached_file', sprintf('%s/%s', $subdir, $filename));
        \update_post_meta($postId, '_wp_attachment_metadata', \wp_read_image_metadata($localFilePath));
        \delete_post_meta($postId, 'uploadcare_url');
        \delete_post_meta($postId, 'uploadcare_uuid');
        \delete_post_meta($postId, 'uploadcare_url_modifiers');

        $post->guid = $uploadDirData['url'] . '/' . $filename;
        \wp_update_post($post);

        $this->makeDefaultImageSizes($post);

        $fileUrl = \wp_attachment_is_image($postId) ? \wp_get_attachment_image_url($postId) : \get_attached_file($postId, true);
        if (!\is_string($fileUrl)) {
            \wp_die(__('Something wrong with upload to Wordpress', 'uploadcare'), '', 400);
        }

        $result = [
            'fileUrl' => $fileUrl,
            'uploadcare_url_modifiers' => '',
            'postId' => $postId,
            'uploadcare_uuid' => false,
        ];

        echo \wp_json_encode($result);

        \wp_die();
    }

    private function filenameFromPostTitle(WP_Post $post, string $originalName = null): string
    {
        if ($originalName !== null && ($originalExt = \pathinfo($originalName, PATHINFO_EXTENSION)) !== null) {
            return $post->post_title . '.' . $originalExt;
        }

        $extensions = [
            'image/bmp' => 'bmp',
            'image/gif' => 'gif',
            'image/jpeg' => 'jpeg',
            'image/png' => 'png',
            'image/tiff' => 'tiff',
        ];

        if (!array_key_exists($post->post_mime_type, $extensions)) {
            return $post->post_title;
        }
        foreach ($extensions as $mimeType => $extension) {
            if ($post->post_mime_type === $mimeType) {
                $postExtension = \substr($post->post_title, (0 - \strlen($extension)));
                if ($postExtension !== $extension) {
                    $post->post_title = sprintf('%s.%s', $post->post_title, $extension);

                    \wp_update_post($post);
                }
            }
        }

        return $post->post_title;
    }

    private function makeDefaultImageSizes(WP_Post $post): void
    {
        if (!\wp_attachment_is_image($post)) {
            return;
        }
        $file = \wp_get_original_image_path($post->ID);
        if ($file === false) {
            \wp_die(__('Unable to load uploaded file', 'uploadcare'), '', 400);
        }

        $meta = \wp_generate_attachment_metadata($post->ID, $file);
        if (\strpos($post->post_mime_type, 'image') !== 0) {
            return;
        }

        if (!isset($meta['sizes']) || empty($meta['sizes'])) {
            $meta['sizes'] = \wp_get_registered_image_subsizes();
            \wp_update_attachment_metadata($post->ID, $meta);
        }
        \wp_create_image_subsizes($file, $post->ID);
    }

    public function loadPostByUuid(string $uuid): ?WP_Post
    {
        global $wpdb;
        $query = \sprintf('SELECT post_id FROM %s WHERE meta_value=\'%s\' AND meta_key=\'uploadcare_uuid\'', \sprintf('%spostmeta', $wpdb->prefix), $uuid);
        $query = $wpdb->prepare($query);
        $result = $wpdb->get_results($query, ARRAY_A);

        if (($postId = ($result[0]['post_id'] ?? null)) === null) {
            return null;
        }

        return \get_post($postId);
    }

    /**
     * Calls on `post-upload-ui`, adds uploadcare button to media library.
     */
    public function uploadcare_media_upload()
    {
        \wp_enqueue_script('uc-config');
        \wp_enqueue_script('admin-js', null, require \dirname(__DIR__) . '/compiled-js/admin.asset.php');
        $sign = __('Click to upload any file up to 5GB from anywhere', $this->plugin_name);
        $btn = __('Upload via Uploadcare', $this->plugin_name);
        $href = '#';

        $styleDef = \get_current_screen() !== null ? \get_current_screen()->action : null;
        if (\get_current_screen() !== null && 'add' !== \get_current_screen()->action) {
            $href = \admin_url() . 'media-new.php';
            $sign .= ' <br><strong>' . __('from Wordpress upload page') . '</strong>';
            $styleDef = 'wrap-margin';
        }

        echo <<<HTML
<div class="uc-picker-wrapper $styleDef">
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
    public function uploadcare_settings_actions(): void
    {
        \add_options_page('Uploadcare', 'Uploadcare', 'upload_files', 'uploadcare', [$this, 'uploadcare_settings']);
        \add_menu_page('Transfer', 'Transfer files', 'administrator', 'uploadcare-transfer', [$this, 'transferFiles'], \plugins_url('/media/logo.png', __DIR__));
        \add_submenu_page(null, 'Transfer by post', null, 'administrator', 'transfer-by-post', [$this, 'transferByPost']);
        //\add_menu_page('Uploadcare Files', 'Uploadcare Files', 'administrator', 'uploadcare-files', [$this, 'ucFiles']);
    }

    public function transferByPost(): void
    {
        $postId = $_GET['postId'] ?? null;
        if ($postId === null || !\is_numeric($postId)) {
            /** @noinspection HtmlUnknownTarget */
            echo $this->twig->render('error.html.twig', [
                'message' => \sprintf('Post undefined. <a href="%s">Go back.</a>', \admin_url()),
            ]);

            return;
        }
        $post = \get_post($postId);
        $path = 'edit.php';
        if ($post->post_type === 'page') {
            $path .= '?post_type=page';
        }
        $linkBack = \admin_url($path);

        $loader = new MediaDataLoader();
        echo $this->twig->render('transfer-by-post.html.twig', [
            'parentPost' => \get_post($postId),
            'media' => $loader->loadMediaForPost((int) $postId),
            'linkBack' => $linkBack,
        ]);
    }

    public function transferFiles(): void
    {
        $page = isset($_GET['page_number']) ? (int) $_GET['page_number'] : 1;
        if ($page < 1) {
            $page = 1;
        }
        $mediaLoader = new MediaDataLoader();
        $media = $mediaLoader->loadMedia($page);
        $totalCount = $mediaLoader->getCount();

        echo $this->twig->render('media-list.html.twig', [
            'media' => $media,
            'totalCount' => $totalCount,
            'postPerPage' => MediaDataLoader::POST_PER_PAGE,
            'page' => $page,
            'pagesCount' => \ceil($totalCount / MediaDataLoader::POST_PER_PAGE),
            'localCount' => $mediaLoader->countImageType(true),
            'remoteCount' => $mediaLoader->countImageType(false),
        ]);
    }

    public function ucFiles(): void
    {
        echo $this->twig->render('uploadcare-list.html.twig', ['files' => $this->api->file()->listFiles()]);
    }

    public function uploadcare_settings()
    {
        include \dirname(__DIR__) . '/includes/uploadcare_settings.php';
    }

    /**
     * Calls on `delete_attachment` hook, deletes the image from Uploadcare.
     *
     * @param int      $postId
     * @param \WP_Post $post
     */
    public function attachmentDelete($postId, $post): void
    {
        $uuid = \get_post_meta($postId, 'uploadcare_uuid', true);
        if (empty($uuid)) {
            $uuid = UploadcareMain::getUuid(\get_post_meta($postId, 'uploadcare_url', true) ?: null);
        }

        if (empty($uuid)) {
            return;
        }

        try {
            $this->api->file()->deleteFile($uuid);
        } catch (\Exception $e) {
            \ULog(\sprintf('Unable to delete file %s', $uuid));
        }
    }

    protected function makeModifiedUrl(int $postId, string $modifiers = ''): ?string
    {
        $uuid = \get_post_meta($postId, 'uploadcare_uuid', true);
        if (empty($uuid)) {
            return null;
        }

        $base = \get_option('uploadcare_cdn_base');
        if (\strpos($base, 'http') !== 0) {
            $base = \sprintf('https://%s', $base);
        }

        return \sprintf('%s/%s/%s', $base, $uuid, $modifiers);
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
    public function uc_get_attachment_url(string $url, int $post_id): string
    {
        $uuid = \get_post_meta($post_id, 'uploadcare_uuid', true);
        if (empty($uuid)) {
            $ucUrl = \get_post_meta($post_id, 'uploadcare_url', true);
            if ($ucUrl) {
                $uuid = UploadcareMain::getUuid($ucUrl);
            }
            if ($uuid !== null) {
                \update_post_meta($post_id, 'uploadcare_uuid', $uuid);
            }
        }

        if (empty($uuid)) {
            return $url;
        }
        $modifiers = \get_post_meta($post_id, 'uploadcare_url_modifiers', true);

        return $this->makeModifiedUrl($post_id, !empty($modifiers) ? $modifiers : '');
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

        return $url . \urlencode($fileName);
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
        if (\strpos(\get_post($id)->post_mime_type, 'image') !== 0) {
            return false;
        }
        $uuid = \get_post_meta($id, 'uploadcare_uuid', true);
        if ($uuid === false) {
            $url = \get_post_meta($id, 'uploadcare_url', true);
            if ($url === false) {
                return false;
            }

            $uuid = UploadcareMain::getUuid($url);
        }

        if (empty($uuid)) {
            return false;
        }
        $baseUrl = $this->makeModifiedUrl($id, \get_post_meta($id, 'uploadcare_url_modifiers', true));

        $sz = $this->thumbnailSize($size);
        if ($sz) {
            $url = \sprintf(UploadcareMain::SCALE_CROP_TEMPLATE, $baseUrl, $sz);
        } else {
            $url = $baseUrl;
        }

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
        if (\strpos(\get_post($post_id)->post_mime_type, 'image') !== 0) {
            return $html;
        }
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
        if (($spl = \strpos($url, '/-')) !== false) {
            $url = \substr($url, 0, $spl);
        }

        return (string) \pathinfo(\rtrim($url, '/') . '/', PATHINFO_BASENAME);
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
            if (!isset($arr[0]) || (int) $arr[0] === 0) {
                if (isset($arr[1])) {
                    $arr[0] = $arr[1];
                }
            }
            if (!isset($arr[1]) || (int) $arr[1] === 0) {
                if (isset($arr[0])) {
                    $arr[1] = $arr[0];
                }
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
                $sizes[$s][0] = get_option($s . '_size_w');
                $sizes[$s][1] = get_option($s . '_size_h');
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
     * @param int|null          $id      existing Post ID
     * @param array             $options
     *
     * @return int|WP_Error
     */
    public function attach(FileInfoInterface $file, $id = null, $options = [])
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
        $attachment_id = \wp_insert_post($attachment, true);
        $meta = $isImage ? $this->getFinalDim($file) : ['width' => null, 'height' => null];

        $attached_file = \sprintf('https://%s/%s/', \get_option('uploadcare_cdn_base'), $file->getUuid());
        if ($id === null) {
            \add_post_meta($attachment_id, 'uploadcare_url', $attached_file, true);
            \add_post_meta($attachment_id, 'uploadcare_uuid', $file->getUuid(), true);
            \add_post_meta($attachment_id, 'uploadcare_url_modifiers', ($options['uploadcare_url_modifiers'] ?? ''), true);
            \add_post_meta($attachment_id, '_wp_attached_file', \rtrim($attached_file, '/') . '/' . $file->getOriginalFilename(), true);
            \add_post_meta($attachment_id, '_wp_attachment_metadata', $meta, true);
        }
        if ($id !== null) {
            \update_post_meta($attachment_id, 'uploadcare_url', $attached_file);
            \update_post_meta($attachment_id, 'uploadcare_uuid', $file->getUuid());
            \update_post_meta($attachment_id, 'uploadcare_url_modifiers', ($options['uploadcare_url_modifiers'] ?? ''));
            \update_post_meta($attachment_id, '_wp_attached_file', \rtrim($attached_file, '/') . '/' . $file->getOriginalFilename());
            \update_post_meta($attachment_id, '_wp_attachment_metadata', $meta);
        }

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
            'previewStep' => true,
            'ajaxurl' => \admin_url('admin-ajax.php'),
            'tabs' => $tabs,
            'cdnBase' => 'https://' . \get_option('uploadcare_cdn_base', 'ucarecdn.com'),
            'multiple' => true,
            'imagesOnly' => false,
        ];
        if (null !== \get_option('uploadcare_finetuning', null)) {
            $fineTuning = \json_decode(\stripcslashes(\get_option('uploadcare_finetuning')), true);
            if (JSON_ERROR_NONE === \json_last_error()) {
                $baseParams = \array_merge($baseParams, $fineTuning);
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
