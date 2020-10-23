<?php


class UcFront
{
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
        \wp_localize_script('blink-loader', 'UC_PUBLIC_KEY', \get_option('uploadcare_public'));

        \wp_enqueue_script('blink-loader');
    }

    /**
     * Calls on `render_block`. Loads images and replace `src` to `data-blink-src`.
     * @see UploadcareMain::defineFrontHooks()
     *
     * @param $content
     * @param array $block
     *
     * @return string|string[]
     */
    public function renderBlock($content, array $block)
    {
        if (\is_admin()) {
            return $content;
        }

        if (!isset($block['blockName']) || $block['blockName'] !== 'core/image') {
            return $content;
        }
        if (\strpos($content, \get_option('uploadcare_cdn_base')) === false) {
            return $content;
        }
        if (!$this->adaptiveDelivery) {
            return $this->replaceImageUrl($content);
        }

        return \str_replace('<img src', '<img data-blink-src', $content);
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
        $resizeParam = '1024x';
        if (\in_array($size, get_intermediate_image_sizes(), true)) {
            $resizeParam = \get_option(\sprintf('%s_size_w', $size), $resizeParam);
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
    private function replaceImageUrl($html, $size = '1024x')
    {
        $regex = '/(<img\s?.+)(src=\")(.[^\"]+)(.+)/m';
        return \preg_replace_callback($regex, static function (array $data) use ($size) {
            if (\strpos($data[3], 'scale_crop') === false) {
                $data[3] = \sprintf(UploadcareMain::RESIZE_TEMPLATE, $data[3], $size);
            }

            return $data[1] . $data[2] . $data[3] . $data[4];
        }, $html);
    }
}
