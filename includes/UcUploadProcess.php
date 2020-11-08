<?php

use Uploadcare\Api;
use Uploadcare\Configuration;
use Uploadcare\Interfaces\File\FileInfoInterface;

class UcUploadProcess extends WP_Background_Process
{
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
        $file = \get_attached_file($item);
        if (!\is_file($file)) {
            \error_log(\sprintf('Cannot load file \'%s\'', $file));
            return false;
        }

        $fileInfo = $this->tryToGetExistingFile($attachment->ID);
        if ($fileInfo === null) {
            $this->tryToUploadFile($file);
        }

        if ($fileInfo === null) {
            return false;
        }

        \wp_delete_attachment($attachment->ID, true);

        $this->admin->attach($fileInfo, (int) $item);
        self::$alreadySynced[] = $item;
        if (isset($oldMeta['sizes'])) {
            $this->removeOldThumbnails($oldMeta['sizes']);
        }

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
     * @return \WP_Post
     */
    private function getPost($id)
    {
        return \get_post($id);
    }
}
