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
        $configuration = Configuration::create(\get_option('uploadcare_public'), \get_option('uploadcare_secret'));
        $this->api = new Api($configuration);
    }

    /**
     * @param string $item uploadcare CDN url
     *
     * @inheritDoc
     */
    protected function task($item)
    {
        $uuid = \pathinfo($item, PATHINFO_BASENAME);
        if (empty($uuid)) {
            return false;
        }

        $data = \file_get_contents($item);
        $post = $this->loadPost($item);
        if ($post !== null) {
            $this->updatePost($post, $uuid, $data);
        } else {
            $this->createPost($uuid, $data);
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
            return;
        }

        $metadata = \wp_generate_attachment_metadata($post->ID, $upload['file']);
        \wp_update_attachment_metadata($post->ID, $metadata);
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
            'guid' => \wp_upload_dir()['url'] . '/' . $filename,
            'post_mime_type' => $fileInfo->getMimeType(),
            'post_title' => $filename,
            'post_content' => '',
            'post_status' => 'inherit',
        ];

        $attachment = \wp_insert_attachment($postInfo, $upload['file']);
        $metadata = \wp_generate_attachment_metadata($attachment, $upload['file']);
        \wp_update_attachment_metadata($attachment, $metadata);
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

    protected function loadPost($ucUrl)
    {
        $parameters = [
            'post_type' => 'attachment',
            'posts_per_page' => -1,
            'post_status' => 'any',
            'meta_query' => [
                'relation' => 'AND',
                [
                    'key' => 'uploadcare_url',
                    'compare' => '=',
                    'value' => $ucUrl,
                ],
            ],
        ];
        $query = new WP_Query($parameters);
        if (!$query->have_posts())
            return null;

        return $query->post;
    }
}
