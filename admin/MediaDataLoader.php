<?php declare(strict_types=1);

class MediaDataLoader
{
    public const POST_PER_PAGE = 20;

    public function loadMedia(int $page = 1, int $postPerPage = null): iterable
    {
        $params = $this->imagesQueryParams($page, $postPerPage ?? self::POST_PER_PAGE);

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

    public function getCount(): int
    {
        return (new \WP_Query(['post_type' => 'attachment', 'post_status' => 'any', 'posts_per_page' => -1]))->found_posts;
    }

    protected function imagesQueryParams(int $page, int $postPerPage): array
    {
        return [
            'post_type' => 'attachment',
            'posts_per_page' => $postPerPage,
            'paged' => $page,
            'post_status' => 'any',
        ];
    }
}
