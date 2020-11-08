<?php

use Uploadcare\Api;
use Uploadcare\Configuration;
use Uploadcare\File;

class UcDownloadProcess extends WP_Background_Process
{
    protected $action = 'uploadcare_download_process';

    /**
     * @var Api
     */
    private $api;

    protected static $alreadySynced = [];

    public function __construct()
    {
        parent::__construct();
        $configuration = Configuration::create(\get_option('uploadcare_public'), \get_option('uploadcare_secret'), ['framework' => UploadcareUserAgent()]);
        $this->api = new Api($configuration);
    }

    /**
     * @param string $item uploadcare file UUID
     *
     * @inheritDoc
     */
    protected function task($item)
    {
        if (\in_array($item, self::$alreadySynced, true)) {
            return false;
        }

        $url = \sprintf('https://%s/%s/', \get_option('uploadcare_cdn_base', 'ucarecdn.com'), $item);
        $data = \file_get_contents($url);

        $post = $this->loadPost($item);
        if ($post !== null) {
            $this->updatePost($post, $item, $data);
        } else {
            $this->createPost($item, $data);
        }

        return false;
    }

    /**
     * @param WP_Post $post
     * @param string $uuid
     * @param string $data image file contents
     */
    protected function updatePost(WP_Post $post, $uuid, $data)
    {
        $fileInfo = $this->fileInfo($uuid);
        $filename = $fileInfo->getOriginalFilename();

        $upload = \wp_upload_bits($filename, null, $data);
        if (!empty($upload['error'])) {
            \ULog('Upload error', $upload);
            return;
        }
        $postInfo = \get_object_vars($post);
        $postInfo['guid'] = $upload['url'];

        $attachment = \wp_insert_attachment($postInfo, $upload['file']);
        \add_post_meta($attachment, '_wp_attached_file', $upload['url'], true);
        \add_post_meta($attachment, 'uploadcare_uuid', $uuid, true);
        \delete_post_meta($attachment, 'uploadcare_url');
        \wp_generate_attachment_metadata($attachment, $upload['file']);

        self::$alreadySynced[] = $uuid;
    }

    /**
     * @param string $uuid
     * @param string $data image file contents
     */
    protected function createPost($uuid, $data)
    {
        $fileInfo = $this->fileInfo($uuid);
        $filename = $fileInfo->getOriginalFilename();

        $upload = \wp_upload_bits($filename, null, $data);
        if (!empty($upload['error'])) {
            return;
        }

        $postInfo = [
            'post_author' => \get_current_user_id(),
            'post_date' => date('Y-m-d H:i:s'),
            'guid' => $upload['url'],
            'post_mime_type' => $fileInfo->getMimeType(),
            'post_title' => $filename,
            'post_content' => '',
            'post_status' => 'inherit',
        ];

        $attachment = \wp_insert_attachment($postInfo, $upload['file']);
        \add_post_meta($attachment, '_wp_attached_file', $upload['url'], true);
        \add_post_meta($attachment, 'uploadcare_uuid', $uuid, true);
        \wp_generate_attachment_metadata($attachment, $upload['file']);

        self::$alreadySynced[] = $uuid;
    }

    /**
     * @param $uuid
     *
     * @return File
     */
    protected function fileInfo($uuid)
    {
        return $this->api->file()->fileInfo($uuid);
    }

    /**
     * @param string $uuid Uploadcare UUID
     *
     * @return WP_Post|null
     */
    protected function loadPost($uuid)
    {
        $parameters = [
            'post_type' => 'attachment',
            'posts_per_page' => -1,
            'post_status' => 'any',
            'meta_query' => [
                'relation' => 'AND',
                [
                    'key' => 'uploadcare_uuid',
                    'compare' => '=',
                    'value' => $uuid,
                ],
            ],
        ];
        $query = new WP_Query($parameters);
        if (!$query->have_posts()) {
            return null;
        }

        return $query->post;
    }

    /**
     * @noinspection ForgottenDebugOutputInspection
     */
    protected function complete()
    {
        parent::complete();
        \error_log(\sprintf('Task %s complete', $this->action));
    }
}
