<?php


class LocalMediaLoader
{
    protected $message;

    /**
     * @var array|WP_Post[]
     */
    protected $posts = [];

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
        }

        foreach ($query->posts as $post) {
            $this->posts[] = $post;
        }

        $this->message = \sprintf(__('Found %s images to sync'), $query->post_count);

        return $this->message;
    }

    public function getPosts()
    {
        return $this->posts;
    }
}
