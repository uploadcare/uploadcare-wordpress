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

    public function __construct($pluginName, $pluginVersion)
    {
        $this->pluginName = $pluginName;
        $this->pluginVersion = $pluginVersion;
        $this->adaptiveDelivery = (bool) \get_option('uploadcare_adaptive_delivery');
    }

    /**
     * Calls on `wp_enqueue_scripts`
     * @see UploadcareMain::defineFrontHooks()
     */
    public function frontendScripts()
    {
        $pluginDirUrl = \plugin_dir_url(\dirname(__DIR__) . '/uploadcare.php');
        \wp_register_script('blink-loader', $pluginDirUrl . '/js/blinkLoader.js', [], $this->pluginVersion, false);
        if (!empty(\get_option('uploadcare_public', null))) {
            \wp_localize_script('blink-loader', 'UC_PUBLIC_KEY', \get_option('uploadcare_public'));
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
            return $this->changeContent($content, $this->adaptiveDelivery);
        }

        return $content;
    }

    /**
     * @param string $content
     * @param bool $blink
     * @return string
     */
    protected function changeContent($content, $blink = true)
    {
        $crawler = new Crawler($content);
        $collation = [];
        $crawler->filterXPath('//img')->each(function (Crawler $node) use (&$collation, $blink) {
            $imageId = (int) \preg_replace('/\D/', '', $node->attr('class'));
            $ucUrl = \get_post_meta($imageId, 'uploadcare_url', true);
            $ucUuid = \get_post_meta($imageId, 'uploadcare_uuid', true);
            if (!empty($ucUrl)) {
                $target = $blink ? $ucUuid : \sprintf(UploadcareMain::RESIZE_TEMPLATE, $ucUrl, '2048x2048');
                $collation[$node->attr('src')] = [
                    'ucUrl' => $ucUrl,
                    'target' => $target,
                    'data-full-url' => $node->extract(['data-full-url'])[0],
                ];
            } else {
                $collation[$node->attr('src')] = [
                    'ucUrl' => null,
                    'target' => $node->attr('src'),
                    'data-full-url' => $node->extract(['data-full-url'])[0],
                ];
            }

        });
        $collation = \array_filter($collation);

        foreach ($collation as $src => $targetArray) {
            $replace = $blink ? 'data-blink-uuid' : 'src';
            $rgx = sprintf('/src=\"%s\"/mu', \preg_quote($src, '/'));
            if ($targetArray['ucUrl'] === null) {
                $replace = 'data-blink-src';
            }

            $content = (string) \preg_replace($rgx, \sprintf('%s="%s"', $replace, $targetArray['target']), $content);
            if (!empty($targetArray['data-full-url'])) {
                $content = (string) \preg_replace('/' . \preg_quote($targetArray['data-full-url'], '/') . '/mu', $targetArray['ucUrl'], $content);
            }
        }

        return $content;
    }

    protected function changeAttributeContent($attributeName, $sourceValue, $targetValue, $content)
    {
        $regex = sprintf('/%s="%s"/', $attributeName, \preg_quote($sourceValue, '/'));

        return \preg_replace($regex, $targetValue, $content);
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
        if (\in_array($size, get_intermediate_image_sizes(), true)) {
            $resizeParam = \get_option(\sprintf('%s_size_w', $size), '2048') . 'x' . \get_option(\sprintf('%s_size_h', $size), '2048');
        }

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
}
