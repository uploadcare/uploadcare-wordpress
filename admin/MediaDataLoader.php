<?php declare(strict_types=1);

class MediaDataLoader
{
    public function loadMedia(): iterable
    {
        $params = $this->imagesQueryParams();

        return $this->query($params);
    }

    protected function query(array $params): iterable
    {
        $query = new \WP_Query($params);
        $result = [];

        if (!$query->have_posts()) {
            return $result;
        }

        foreach ($query->posts as $post) {
            $result[] = $post;
        }

        return $result;
    }

    protected function imagesQueryParams(): array
    {
        return [
            'post_type' => 'attachment',
            'posts_per_page' => -1,
            'post_status' => 'any',
        ];
    }
}
