<?php

use Symfony\Component\DomCrawler\Crawler;

class UcFront
{
    public const IMAGE_REGEX = '/(<img\s?.+)(src=\")(.[^\"]+)(.+)/m';

    /**
     * @var string
     */
    private $pluginName;

    /**
     * @var string
     */
    private $pluginVersion;

    /**
     * @var bool Adaptive delivery enabled
     */
    private $adaptiveDelivery;

    /**
     * @var bool Secure uploads enabled
     */
    private $secureUploads;

    public function __construct(string $pluginName, string $pluginVersion)
    {
        $this->pluginName = $pluginName;
        $this->pluginVersion = $pluginVersion;
        $this->adaptiveDelivery = (bool) \get_option('uploadcare_adaptive_delivery');
        $this->secureUploads = (bool) \get_option('uploadcare_upload_lifetime', 0) > 0;
    }

    public function getPluginName(): string
    {
        return $this->pluginName;
    }

    public function editorPostMeta(): void
    {
        $parameters = [
            'show_in_rest' => true,
            'type' => 'string',
        ];

        \register_post_meta('attachment', 'uploadcare_url_modifiers', $parameters);
        \register_post_meta('attachment', 'uploadcare_url', $parameters);
        \register_post_meta('attachment', 'uploadcare_uuid', $parameters);
    }

    public function prepareAttachment(array $response, WP_Post $attachment, $meta): array
    {
        $ucUrl = \get_post_meta($attachment->ID, 'uploadcare_url_modifiers', true);
        if ($ucUrl) {
            return $response;
        }

        $uuid = \get_post_meta($attachment->ID, 'uploadcare_uuid', true);
        if (empty($uuid)) {
            $uuid = UploadcareMain::getUuid($ucUrl);
        }

        $response['meta'] = [
            'uploadcare_url_modifiers' => \get_post_meta($attachment->ID, 'uploadcare_url_modifiers', true),
            'uploadcare_url' => $ucUrl,
            'uploadcare_uuid' => $uuid,
        ];

        return $response;
    }

    /**
     * Calls on `wp_enqueue_scripts`.
     *
     * @see UploadcareMain::defineFrontHooks()
     */
    public function frontendScripts(): void
    {
        $pluginDirUrl = \plugin_dir_url(\dirname(__DIR__) . '/uploadcare.php');
        if (!empty(\get_option('uploadcare_public', null))) {
            \wp_register_script('blink-loader', \trim($pluginDirUrl, '/') . '/js/blinkLoader.js', [], $this->pluginVersion, false);
            \wp_localize_script('blink-loader', 'blinkLoaderConfig', $this->getJsConfig());
            \wp_enqueue_script('blink-loader');
        }
    }

    /**
     * Calls on `wp_calculate_image_srcset`
     * @see UploadcareMain::defineFrontHooks()
     *
     * @param array $sources
     * @param array $sizeArray
     * @param string $src
     * @param array $imageMeta
     * @param int $attachmentId
     * @return array
     */
    public function imageSrcSet(array $sources, array $sizeArray, string $src, array $imageMeta, int $attachmentId): array
    {
        if (empty(\get_post_meta($attachmentId, 'uploadcare_uuid', true))) {
            return $sources;
        }

        $up = \wp_get_upload_dir();
        if (($baseUrl = $up['baseurl'] ?? null) === null) {
            return $sources;
        }
        $cdnUrl = \sprintf('https://%s', \get_option('uploadcare_cdn_base'));
        foreach ($sources as $sourceKey => $source) {
            $url = $source['url'] ?? null;
            if ($url === null) {
                continue;
            }

            $url = \str_replace([$baseUrl, $cdnUrl], '', $url);

            $sources[$sourceKey]['url'] = \sprintf('%s/%s', $cdnUrl, \ltrim($url, '/'));
        }

        return $sources;
    }

    /**
     * Calls on `wp_get_attachment_metadata`
     * @see UploadcareMain::defineFrontHooks()
     *
     * @param array $data
     * @param int $attachmentId
     * @return array
     */
    public function imageAttachmentMetadata(array $data, int $attachmentId): array
    {
        if (($data['sizes'] ?? null) !== null || $this->adaptiveDelivery) {
            return $data;
        }

        $item = \get_post($attachmentId);
        if (!$item instanceof \WP_Post || $item->post_type !== 'attachment') {
            return $data;
        }

        $uuid = \get_post_meta($attachmentId, 'uploadcare_uuid', true);
        if (empty($uuid)) {
            return $data;
        }

        $sizes = \wp_get_registered_image_subsizes();
        $transform = (string) \get_post_meta($attachmentId, 'uploadcare_url_modifiers', true);
        $imageUrl = \sprintf('https://%s/%s/', \get_option('uploadcare_cdn_base'), $uuid);
        foreach ($sizes as $definition => $sizeArray) {
            $wh = \sprintf('%sx%s', ($sizeArray['width'] ?? '1024'), ($sizeArray['height'] ?? '1024'));
            $modifiedUrl = \sprintf(UploadcareMain::SMART_TEMPLATE, $imageUrl, $wh);
            $sizes[$definition]['file'] = $modifiedUrl . $transform;
        }
        $data['sizes'] = $sizes;
        $data['file'] = $imageUrl;

        return $data;
    }

    /**
     * Calls on `wp_image_src_get_dimensions`
     * @see UploadcareMain::defineFrontHooks()
     *
     * @param $dimensions
     * @param string $src
     * @param array $imageMeta
     * @param int $attachmentId
     * @return int[]|mixed
     */
    public function imageGetDimensions($dimensions, string $src, array $imageMeta, int $attachmentId)
    {
        if (empty(\get_post_meta($attachmentId, 'uploadcare_uuid', true))) {
            return $dimensions;
        }
        $sizes = $imageMeta['sizes'] ?? null;
        if ($sizes === null) {
            return $dimensions;
        }

        return [1024, 1024];
    }

    /**
     * Calls on `wp_get_attachment_image_src`
     *
     * @param $image
     * @param int $attachmentId
     * @param $size
     * @param bool $icon
     * @return array|mixed
     */
    public function getImageSrc($image, int $attachmentId, $size, bool $icon)
    {
        if (!\is_array($image)) {
            return $image;
        }

        $src = $image[0] ?? null;
        if ($src === null || \strpos($src, \get_option('uploadcare_cdn_base')) === false) {
            return $image;
        }

        $currentWidth = $image[1] ?? null;
        if ($currentWidth !== null && (int) $currentWidth !== 0) {
            return $image;
        }

        $meta = \wp_get_attachment_metadata($attachmentId);
        $existingMeta = $meta['sizes'][$size] ?? null;
        if ($existingMeta === null) {
            $existingMeta = ['width' => $meta['width'] ?? 0, 'height' => $meta['height'] ?? 0];
        }

        $image[1] = $existingMeta['width'] ?? 0;
        $image[2] = $existingMeta['height'] ?? 0;

        return $image;
    }

    /**
     * Calls on `render_block`. Loads images and replace `src` to `data-blink-uuid`.
     *
     * @see UploadcareMain::defineFrontHooks()
     *
     * @param $content
     * @param array $block
     *
     * @return string|string[]
     */
    public function renderBlock($content, array $block)
    {
        if (empty(\get_option('uploadcare_public')) || empty(\get_option('uploadcare_secret'))) {
            return $content;
        }

        if (!isset($block['blockName'])) {
            return $content;
        }
        if (\is_admin()) {
            return $content;
        }
        $blocks = ['core/image', 'uploadcare/image'];

        if (\in_array($block['blockName'], $blocks, true)) {
            $itemId = $block['blockName'] === 'core/image' ? ($block['attrs']['id'] ?? null) : ($block['attrs']['mediaID'] ?? null);
            if ($itemId === null) {
                return $content;
            }

            return $this->changeContent($content, (int) $itemId);
        }

        return $content;
    }

    /**
     * @param string $content
     * @param int    $imageId
     *
     * @return string
     */
    protected function changeContent(string $content, int $imageId): string
    {
        $crawler = new Crawler($content);
        $collation = [];
        $modifiers = \get_post_meta($imageId, 'uploadcare_url_modifiers', true);

        $crawler->filterXPath('//img')->each(function (Crawler $node) use (&$collation, $imageId, $modifiers) {
            $attachedFile = \get_post_meta($imageId, '_wp_attached_file', true);
            $isLocal = true;

            if (\strpos($attachedFile, \get_option('uploadcare_cdn_base')) !== false) {
                $imageUrl = \sprintf('https://%s/%s/', \get_option('uploadcare_cdn_base'), $this->getUuid($imageId));
                $isLocal = false;
            } else {
                $imageUrl = \wp_get_attachment_image_url($imageId, 'large');
            }

            // If Adaptive delivery is off and we have a remote file — change file url to transformation url
            if (!$this->adaptiveDelivery) {
                $imageUrl = !$isLocal ? \sprintf(UploadcareMain::SMART_TEMPLATE, (\rtrim($imageUrl, '/') . '/'), '2048x2048') : null;
            }
            // If Adaptive delivery is on and Secure uploads is on too we have to change url to uuid and ignore all local files
            if ($this->adaptiveDelivery && $this->secureUploads) {
                $imageUrl = !$isLocal ? $this->getUuid($imageId) : null;
            }
            if ($imageUrl !== null && $isLocal === false) {
                $imageUrl = \sprintf('%s/%s', \rtrim($imageUrl, '/'), $modifiers);
            }

            $collation[$node->attr('src')] = $imageUrl;
        });
        $collation = \array_filter($collation);

        foreach ($collation as $src => $target) {
            // If Adaptive delivery is off we already have only remote images in collation, and all array values has already changed to transformation urls
            if (!$this->adaptiveDelivery) {
                $content = (string) \preg_replace('/' . \preg_quote($src, '/') . '/mu', $target, $content);
            }
            // If Adaptive delivery is on and Secure uploads is off it changes everything to `data-blink-src`.
            // In this case, the `collation` array contains all images (remote and local) and all this images can be shown throw Adaptive delivery.
            if ($this->adaptiveDelivery && !$this->secureUploads) {
                $content = (string) \preg_replace('/' . \preg_quote($src, '/') . '/mu', $target, $content);
                $content = (string) \preg_replace('/src=/mu', 'data-blink-src=', $content);
            }
            // If adaptive delivery and secure uploads both enabled we have to change all sources to uuids and also change `src` attribute to `data-blink-uuid`.
            // In this case collation array values are already uuids.
            if ($this->adaptiveDelivery && $this->secureUploads) {
                $content = (string) \preg_replace('/' . \preg_quote($src, '/') . '/mu', $target, $content);
                $content = (string) \preg_replace('/src=/mu', 'data-blink-uuid=', $content);
            }
        }
        if (!$this->adaptiveDelivery) {
            $content = \wp_image_add_srcset_and_sizes($content, \wp_get_attachment_metadata($imageId), $imageId);
        }

        return $content;
    }

    protected function getUuid(int $postId): string
    {
        $uuid = \get_post_meta($postId, 'uploadcare_uuid', true);
        if (empty($uuid)) {
            $uuid = UploadcareMain::getUuid(\get_post_meta($postId, 'uploadcare_url', true));
            \update_post_meta($postId, 'uploadcare_uuid', $uuid);
        }

        return $uuid;
    }

    /**
     * Calls on `post_thumbnail_html`. If thumbnail is an uploadcare image, make it an adaptive delivered.
     *
     * @see UploadcareMain::defineFrontHooks()
     *
     * @param $html
     * @param $post_id
     * @param $post_thumbnail_id
     * @param $size
     * @param $attr
     *
     * @return string
     */
    public function postFeaturedImage($html, $post_id, $post_thumbnail_id, $size, $attr): string
    {
        global $wpdb;
        $resizeParam = '2048x2048';
        if (\in_array($size, \get_intermediate_image_sizes(), true)) {
            $resizeParam = \get_option(\sprintf('%s_size_w', $size), '2048') . 'x' . \get_option(\sprintf('%s_size_h', $size), '2048');
        }

        // It is impossible to direct get post meta for a featured image
        $q = \sprintf('SELECT meta_value FROM `%s` WHERE meta_key=\'uploadcare_url\' AND post_id=%d', \sprintf('%spostmeta', $wpdb->prefix), $post_thumbnail_id);
        $result = $wpdb->get_col($q);

        if (\array_key_exists(0, $result) && \strpos($result[0], \get_option('uploadcare_cdn_base')) !== false) {
            if ($this->adaptiveDelivery === false) {
                return $this->replaceImageUrl(\sprintf('<img src="%s" />', $result[0]), $resizeParam);
            }
            $uuid = UploadcareMain::getUuid($result[0]);
            $modifiers = \get_post_meta($post_id, 'uploadcare_url_modifiers', true);
            if (!empty($modifiers)) {
                $uuid = \sprintf('%s/%s', $uuid, $modifiers);
            }

            /** @noinspection RequiredAttributes */
            return \sprintf('<img data-blink-uuid="%s" alt="post-%d">', $uuid, $post_id);
        }

        return $html;
    }

    /**
     * @param string $html
     * @param string $size
     *
     * @return string
     */
    private function replaceImageUrl(string $html, string $size = '2048x2048'): string
    {
        return \preg_replace_callback(self::IMAGE_REGEX, static function (array $data) use ($size) {
            if (\strpos($data[3], 'scale_crop') === false) {
                $data[3] = \sprintf(UploadcareMain::SMART_TEMPLATE, $data[3], $size);
            }

            return $data[1] . $data[2] . $data[3] . $data[4];
        }, $html);
    }

    private function getJsConfig(): array
    {
        $baseParams = [
            'pubkey' => \get_option('uploadcare_public'),
            'fadeIn' => true,
            'lazyload' => true,
            'smartCompression' => true,
            'responsive' => true,
            'retina' => true,
            'webp' => true,
        ];

        if (\get_option('uploadcare_blink_loader', null) !== null) {
            $userParams = \json_decode(\stripslashes(\get_option('uploadcare_blink_loader', [])), true);
            if (\json_last_error() === JSON_ERROR_NONE) {
                $baseParams = \array_merge($baseParams, $userParams);
            }
        }

        return $baseParams;
    }
}
