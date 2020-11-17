<?php

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
        return \preg_replace_callback(self::IMAGE_SEARCH_REGEX, static function (array $matches) use ($htmlContent, $url) {
            if (!isset($matches[4])) {
                return $htmlContent;
            }
            $result = null;
            foreach ($matches as $i => $match) {
                if ($i === 0) { // Match 0 is a full string
                    continue;
                }
                if (\strpos($match, 'http') === 0) { // In case match is a url, change it to new url
                    $result .= $url;
                    \ULog(\sprintf('%s changed to %s', $match, $url));
                } else {
                    $result .= $match;
                }
            }

            return $result;
        }, $htmlContent);
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

        $fullUrl = \wp_get_attachment_image_src($attachment->ID, 'full');
        $path = \parse_url($fullUrl[0], PHP_URL_PATH);
        $sourceFilename = \pathinfo($path, PATHINFO_FILENAME);

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
        if (\is_file($file) && \is_readable($file)) {
            \unlink($file);
        }
        if (isset($oldMeta['sizes'])) {
            $this->removeOldThumbnails($oldMeta['sizes']);
        }
        $this->admin->changeImageInPosts($sourceFilename, $fileInfo, [self::class, 'modifyBlocks']);

        return false;
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
