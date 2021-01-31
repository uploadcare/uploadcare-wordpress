<?php

use Symfony\Component\DomCrawler\Crawler;

class UcFront
{
    const IMAGE_REGEX = '/(<img\s?.+)(src=\")(.[^\"]+)(.+)/m';

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

    public function __construct($pluginName, $pluginVersion)
    {
        $this->pluginName = $pluginName;
        $this->pluginVersion = $pluginVersion;
        $this->adaptiveDelivery = (bool) \get_option('uploadcare_adaptive_delivery');
        $this->secureUploads = (bool) \get_option('uploadcare_upload_lifetime', 0) > 0;
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

    public function prepareAttachment(array $response, \WP_Post $attachment, $meta): array
    {
        if (empty(\get_post_meta($attachment->ID, 'uploadcare_url', true)))
            return $response;

        $response['meta'] = [
            'uploadcare_url_modifiers' => \get_post_meta($attachment->ID, 'uploadcare_url_modifiers', true),
            'uploadcare_url' => \get_post_meta($attachment->ID, 'uploadcare_url', true),
            'uploadcare_uuid' => \get_post_meta($attachment->ID, 'uploadcare_uuid', true),
        ];

        return $response;
    }

    /**
     * Calls on `wp_enqueue_scripts`
     * @see UploadcareMain::defineFrontHooks()
     */
    public function frontendScripts()
    {
        $pluginDirUrl = \plugin_dir_url(\dirname(__DIR__) . '/uploadcare.php');
        if (!empty(\get_option('uploadcare_public', null))) {
            \wp_register_script('blink-loader', $pluginDirUrl . '/js/blinkLoader.js', [], $this->pluginVersion, false);
            \wp_localize_script('blink-loader', 'blinkLoaderConfig', $this->getJsConfig());
            \wp_enqueue_script('blink-loader');
        }
    }

    /**
     * Calls on `render_block`. Loads images and replace `src` to `data-blink-uuid`.
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
            $itemId = $block['blockName'] === 'core/image' ? $block['attrs']['id'] : $block['attrs']['mediaID'];

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
    protected function changeContent($content, $imageId)
    {
        $crawler = new Crawler($content);
        $collation = [];
        $crawler->filterXPath('//img')->each(function (Crawler $node) use (&$collation, $imageId) {
            $imageUrl = $node->attr('src');
            $attachedFile = \get_post_meta($imageId, '_wp_attached_file', true);
            $isLocal = true;

            if (\strpos($attachedFile, \get_option('uploadcare_cdn_base')) !== false) {
                $imageUrl = $attachedFile;
                $isLocal = false;
            }

            // If Adaptive delivery is off and we have a remote file — change file url to transformation url
            if (!$this->adaptiveDelivery) {
                $imageUrl = !$isLocal ? \sprintf(UploadcareMain::RESIZE_TEMPLATE, (\rtrim($imageUrl, '/') . '/'), '2048x2048') : null;
            }
            // If Adaptive delivery is on and Secure uploads is on too we have to change url to uuid and ignore all local files
            if ($this->adaptiveDelivery && $this->secureUploads) {
                $imageUrl = !$isLocal ? \get_post_meta($imageId, 'uploadcare_uuid', true) : null;
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
                $content = (string) \preg_replace('/src=/mu', 'data-blink-src=', $content);
            }
            // If adaptive delivery and secure uploads both enabled we have to change all sources to uuids and also change `src` attribute to `data-blink-uuid`.
            // In this case collation array values are already uuids.
            if ($this->adaptiveDelivery && $this->secureUploads) {
                $content = (string) \preg_replace('/' . \preg_quote($src, '/') . '/mu', $target, $content);
                $content = (string) \preg_replace('/src=/mu', 'data-blink-uuid=', $content);
            }
        }

        return $content;
    }

    /**
     * Calls on `post_thumbnail_html`. If thumbnail is an uploadcare image, make it an adaptive delivered.
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
    public function postFeaturedImage($html, $post_id, $post_thumbnail_id, $size, $attr)
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
            $uuid = \pathinfo($result[0], PATHINFO_BASENAME);

            return \sprintf('<img data-blink-uuid="%s" alt="post-%d">', $uuid, $post_id);
        }

        return $html;
    }

    /**
     * @param string $html
     * @param string $size
     * @return string
     */
    private function replaceImageUrl($html, $size = '2048x2048')
    {
        return \preg_replace_callback(self::IMAGE_REGEX, static function (array $data) use ($size) {
            if (\strpos($data[3], 'scale_crop') === false) {
                $data[3] = \sprintf(UploadcareMain::RESIZE_TEMPLATE, $data[3], $size);
            }

            return $data[1] . $data[2] . $data[3] . $data[4];
        }, $html);
    }

    private function getJsConfig()
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
