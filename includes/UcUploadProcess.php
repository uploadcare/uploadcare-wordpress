<?php

use Symfony\Component\DomCrawler\Crawler;
use Uploadcare\Api;
use Uploadcare\Configuration;
use Uploadcare\Interfaces\File\FileInfoInterface;

class UcUploadProcess extends WP_Background_Process
{
    /**
     * https://regex101.com/r/FEKvy6/401
     * In 4th group will be an image address.
     */
    const IMAGE_SEARCH_REGEX = '/(.*)(<img.+?(?=\"))(")(https?:\/\/.+?(?=\"))(")(.*)/mu';

    protected $action = 'uploadcare_upload_process';

    protected static $alreadySynced = [];

    /**
     * @var Api
     */
    private $api;

    /**
     * @var UcAdmin
     */
    private $admin;

    public function __construct()
    {
        parent::__construct();
        $ucConfig = Configuration::create(\get_option('uploadcare_public'), \get_option('uploadcare_secret'), ['framework' => UploadcareUserAgent()]);
        $this->api = new Api($ucConfig);
        $this->admin = new UcAdmin('uploadcare', UPLOADCARE_VERSION);
    }

    /**
     * @param string $htmlContent Block content
     * @param string $url         New image url
     *
     * @return string|string[]|null
     */
    public static function rgxReplace($htmlContent, $url)
    {
        $crawler = new Crawler($htmlContent);
        $collation = [];
        $crawler->filterXPath('//img')->each(function (Crawler $node) use (&$collation) {
            $imageId = (int) \preg_replace('/\D/', '', $node->attr('class'));
            $ucUrl = \get_post_meta($imageId, 'uploadcare_url', true);
            if (!empty($ucUrl)) {
                $target = \sprintf(UploadcareMain::RESIZE_TEMPLATE, $ucUrl, '2048x2048');
                $collation[$node->attr('src')] = $target;
            }
        });
        $collation = \array_filter($collation);

        foreach ($collation as $src => $target) {
            $rgx = sprintf('/src=\"%s\"/mu', \preg_quote($src, '/'));
            $htmlContent = (string) \preg_replace($rgx, \sprintf('src=%s', $target), $htmlContent);
        }

        return $htmlContent;
    }

    /**
     * @param array|\WP_Block_Parser_Block[] $blocks
     * @param FileInfoInterface              $fileInfo
     *
     * @return array|\WP_Block_Parser_Block[]
     */
    public static function modifyBlocks(array $blocks, FileInfoInterface $fileInfo)
    {
        $url = \sprintf('https://%s/%s/', \get_option('uploadcare_cdn_base'), $fileInfo->getUuid());
        foreach ($blocks as $n => $block) {
            $block->innerHTML = self::rgxReplace($block->innerHTML, $url);
            $innerContent = $block->innerContent;
            foreach ($innerContent as $c => $content) {
                $innerContent[$c] = self::rgxReplace($content, $url);
            }
            $block->innerContent = \array_values($innerContent);
            $blocks[$n] = $block;
        }

        return \array_values($blocks);
    }

    /**
     * @param int $item Existing post ID
     * @noinspection ForgottenDebugOutputInspection
     *
     * @return false
     */
    protected function task($item)
    {
        if (\in_array($item, self::$alreadySynced, false)) {
            return false;
        }

        $attachment = $this->getPost($item);
        if (!$attachment instanceof \WP_Post) {
            \error_log(\sprintf('Cannot load WP Post by %s id', $item));

            return false;
        }

        $file = \get_attached_file($item, true);
        \ULog(\sprintf('Modify attached file %s', $file));

        if (!\is_file($file)) {
            \error_log(\sprintf('Cannot load file \'%s\'', $file));

            return false;
        }

        $fileInfo = $this->tryToGetExistingFile($attachment->ID);
        if (null === $fileInfo) {
            $fileInfo = $this->tryToUploadFile($file);
        }

        if (null === $fileInfo) {
            return false;
        }

        $this->admin->attach($fileInfo, (int) $item);
        self::$alreadySynced[] = $item;

        /*
         * Commented — we don't change images in existing posts, it's not working
        $sourceFilename = $attachment->post_name;
        $directory = \pathinfo($file, PATHINFO_DIRNAME);
        $this->removeStaled($directory, $sourceFilename);
        $this->admin->changeImageInPosts($sourceFilename, $fileInfo, [self::class, 'modifyBlocks']);
        */

        return false;
    }

    protected function removeStaled($dirname, $baseName)
    {
        $files = \glob(\sprintf('%s/%s*', \rtrim($dirname, '/'), \ltrim($baseName, '/')));
        foreach ($files as $file) {
            \ULog(\sprintf('Remove %s', $file));
            \unlink($file) ? \ULog('done') : \ULog('fail');
        }
    }

    /**
     * @param $path
     *
     * @return FileInfoInterface|null
     * @noinspection ForgottenDebugOutputInspection
     */
    protected function tryToUploadFile($path)
    {
        try {
            $fileInfo = $this->api->uploader()->fromPath($path);
            $this->api->file()->storeFile($fileInfo);

            return $fileInfo;
        } catch (\Exception $e) {
            \error_log($e->getMessage());

            return null;
        }
    }

    protected function tryToGetExistingFile($id)
    {
        $uuid = \get_post_meta($id, 'uploadcare_uuid', true);
        if (empty($uuid)) {
            return null;
        }

        try {
            return $this->api->file()->fileInfo($uuid);
        } catch (\Exception $e) {
            return null;
        }
    }

    protected function removeOldThumbnails(array $sizes)
    {
        $path = \wp_upload_dir()['path'];
        foreach ($sizes as $size => $info) {
            $filePath = \sprintf('%s/%s', $path, $info['file']);
            if (isset($info['file']) && \is_file($filePath)) {
                \unlink($filePath);
            }
        }
    }

    /**
     * @noinspection ForgottenDebugOutputInspection
     */
    protected function complete()
    {
        parent::complete();
        \error_log(\sprintf('Task %s complete', $this->action));
    }

    /**
     * @param int $id
     *
     * @return \WP_Post
     */
    private function getPost($id)
    {
        return \get_post($id);
    }
}
