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

    public function __construct($pluginName, $pluginVersion)
    {
        $this->pluginName = $pluginName;
        $this->pluginVersion = $pluginVersion;
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
        $q = \sprintf('SELECT meta_value FROM `%s` WHERE meta_key=\'uploadcare_url\' AND post_id=%d', \sprintf('%spostmeta', $wpdb->prefix), $post_thumbnail_id);
        $result = $wpdb->get_col($q);
        if (\array_key_exists(0, $result) && \strpos($result[0], \get_option('uploadcare_cdn_base')) !== false) {
            return \sprintf('<img data-blink-src="%s" alt="post-%d">', $result[0], $post_id);
        }

        return $html;
    }
}
