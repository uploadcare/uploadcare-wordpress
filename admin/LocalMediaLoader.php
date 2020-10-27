<?php


class LocalMediaLoader
{
    protected $message;

    /**
     * @var array|WP_Post[]
     */
    protected $posts = [];

    /**
     * @var bool
     */
    private $hasLocalMedia = false;

    /**
     * @var int
     */
    private $mediaCount = 0;

    /**
     * @return string
     */
    public function loadMedia()
    {
        $queryParams = [
            'post_type' => 'attachment',
            'posts_per_page' => -1,
            'post_status' => 'any',
            'meta_query' => [
                'relation' => 'AND',
                [
                    'key' => 'uploadcare_url',
                    'compare' => 'NOT EXISTS',
                    'value' => '',
                ],
            ],
        ];

        $query = new WP_Query($queryParams);
        if (!$query->have_posts()) {
            $this->message = __('No not-synced images found in library');
            return $this->message;
        }

        foreach ($query->posts as $post) {
            $this->posts[] = $post;
        }

        $this->hasLocalMedia = true;
        $this->mediaCount = $query->post_count;
        $this->message = \sprintf(__('Found %s images to sync'), $query->post_count);

        return $this->message;
    }

    public function getHasLocalMedia()
    {
        return $this->hasLocalMedia;
    }

    public function getLocalMediaCount()
    {
        return $this->mediaCount;
    }

    public function getPosts()
    {
        return $this->posts;
    }
}
