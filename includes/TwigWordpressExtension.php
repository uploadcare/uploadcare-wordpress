<?php declare(strict_types=1);

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class TwigWordpressExtension extends AbstractExtension
{
    protected const JS_FOLDER = 'compiled-js';

    public function getFunctions(): array
    {
        $getPostMeta = new TwigFunction('get_post_meta', [$this, 'getPostMeta']);
        $getAttachment = new TwigFunction('get_attachment_image', [$this, 'getAttachment']);
        $getAuthor = new TwigFunction('get_post_author', [$this, 'getPostAuthor']);
        $addJs = new TwigFunction('add_js', [$this, 'addJavascript']);

        return [
            $getPostMeta,
            $getAttachment,
            $getAuthor,
            $addJs,
        ];
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('trans', [$this, 'translate']),
        ];
    }

    public function addJavascript($name): void
    {
        if (\strpos($name, '.js') !== false) {
            $name = \str_replace('.js', '', $name);
        }
        $folder = \dirname(__DIR__) . '/' . self::JS_FOLDER;
        if (!\is_dir($folder)) {
            return;
        }
        $assetsFile = \sprintf('%s/%s.asset.php', $folder, $name);
        $pluginDirUrl = \plugin_dir_url(\dirname(__DIR__) . '/uploadcare.php');
        $jsUrl = \sprintf('%s/%s/%s.js', \rtrim($pluginDirUrl, '/'), self::JS_FOLDER, $name);
        $parameters = \is_readable($assetsFile) ? require $assetsFile : [];

        \wp_register_script($name, $jsUrl, [], (new UploadcareMain())->get_version(), true);
        \wp_enqueue_script($name, null, $parameters);
    }

    public function translate(string $data): string
    {
        return __($data, 'uploadcare');
    }

    public function getPostAuthor(WP_Post $post): ?WP_User
    {
        $result = \get_userdata($post->post_author);

        return $result === false ? null : $result;
    }

    public function getAttachment($post): ?string
    {
        $img = \wp_get_attachment_image_src($this->getPostId($post));

        $result = $img[0] ?? false;
        if ($result === false) {
            return null;
        }

        if (!empty(\get_post_meta('uploadcare_uuid'))) {
            $result = \sprintf('%s%s', $result, \get_post_meta('uploadcare_url_modifiers'));

            return \sprintf(UploadcareMain::PREVIEW_TEMPLATE, $result);
        }

        return $result;
    }

    public function getPostMeta($post, string $name): ?string
    {
        $result = \get_post_meta($this->getPostId($post), $name, true);

        return $result === false ? null : $result;
    }

    private function getPostId($post): int
    {
        if ($post instanceof \WP_Post) {
            return (int) $post->ID;
        }

        return (int) $post;
    }
}
