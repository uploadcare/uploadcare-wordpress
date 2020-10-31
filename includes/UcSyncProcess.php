<?php

use Uploadcare\Api;
use Uploadcare\Configuration;

class UcSyncProcess extends WP_Background_Process
{
    protected $action = 'uploadcare_upload_process';

    protected static $alreadySynced = [];

    /**
     * @var Api
     */
    private $api;

    public function __construct()
    {
        parent::__construct();
        $ucConfig = Configuration::create(\get_option('uploadcare_public'), \get_option('uploadcare_secret'));
        $this->api = new Api($ucConfig);
    }

    /**
     * @param int $item
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

        try {
            $fileInfo = $this->api->uploader()->fromPath($file);
        } catch (\Exception $e) {
            \error_log($e->getMessage());
            return false;
        }
        $attachedFile = \sprintf('https://%s/%s/', \get_option('uploadcare_cdn_base'), $fileInfo->getUuid());
        $meta = $fileInfo->isImage() ?
            [
                'width' => ($imgInfo = $fileInfo->getImageInfo()) !== null ? $imgInfo->getWidth() : null,
                'height' => ($imgInfo = $fileInfo->getImageInfo()) !== null ? $imgInfo->getHeight() : null,
            ] :
            ['width' => null, 'height' => null];

        self::$alreadySynced[] = $item;
        $attachment->guid = $attachedFile;
        \add_post_meta($item, 'uploadcare_url', $attachedFile, true);
        \add_post_meta($item, '_wp_attached_file', $attachedFile, true);
        \add_post_meta($item, '_wp_attachment_metadata', $meta, true);
        \error_log(\sprintf('File uploaded, post metadata updated with new URL \'%s\'', $attachedFile));

        return false;
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
