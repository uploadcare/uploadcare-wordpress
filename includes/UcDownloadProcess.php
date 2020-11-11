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

    /**
     * @var UcAdmin
     */
    private $admin;

    public function __construct()
    {
        parent::__construct();
        $configuration = Configuration::create(\get_option('uploadcare_public'), \get_option('uploadcare_secret'), ['framework' => UploadcareUserAgent()]);
        $this->api = new Api($configuration);
        $this->admin = new UcAdmin('uploadcare', UPLOADCARE_VERSION);
    }

    /**
     * @param array|\WP_Block_Parser_Block[] $blocks
     * @param string                         $localUrl
     *
     * @return array|\WP_Block_Parser_Block[]
     */
    public static function modifyBlocks(array $blocks, $localUrl)
    {
        foreach ($blocks as $n => $block) {
            $block->innerHTML = UcUploadProcess::rgxReplace($block->innerHTML, $localUrl);
            $innerContent = $block->innerContent;
            foreach ($innerContent as $ic => $item) {
                $innerContent[$ic] = UcUploadProcess::rgxReplace($item, $localUrl);
            }
            $block->innerContent = \array_values($innerContent);
            $blocks[$n] = $block;
        }

        return \array_values($blocks);
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
            $this->admin->changeImageInPosts($item, \wp_get_attachment_image_src($post->ID, 'full')[0], [self::class, 'modifyBlocks']);
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
        if (!\function_exists('wp_generate_attachment_metadata')) {
            if (!defined('ABSPATH')) {
                require_once(\dirname(\dirname(\dirname(\dirname(__DIR__)))).'/wp-load.php');
            }

            include_once( ABSPATH . 'wp-admin/includes/image.php' );
        }

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
