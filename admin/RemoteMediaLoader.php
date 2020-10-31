<?php

use Uploadcare\Api;
use Uploadcare\Configuration;
use Uploadcare\Interfaces\Response\ListResponseInterface;

class RemoteMediaLoader
{
    /**
     * @var array|string[] Array of Uploadcare CDN urls
     */
    private $files = [];

    /**
     * @var Api
     */
    private $api;

    public function __construct()
    {
        $configuration = Configuration::create(\get_option('uploadcare_public'), \get_option('uploadcare_secret'));
        $this->api = new Api($configuration);
    }

    public function getFiles()
    {
        return $this->files;
    }

    public function loadMedia()
    {
        $files = $this->api->file()->listFiles();
        $this->loadFiles($files);
    }

    private function loadFiles(ListResponseInterface $response)
    {
        foreach ($response->getResults() as $fileInfo) {
            $this->files[] = $fileInfo->getUuid();
        }

        if (($next = $this->api->file()->nextPage($response)) !== null) {
            $this->loadFiles($next);
        }
    }
}
