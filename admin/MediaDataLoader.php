<?php declare( strict_types=1 );

class MediaDataLoader {
    public const POST_PER_PAGE = 20;

    public function loadMedia( int $page = 1, int $postPerPage = null ): iterable {
        $params = $this->imagesQueryParams( $page, $postPerPage ?? self::POST_PER_PAGE );

        return $this->query( $params );
    }

    public function loadMediaForPost( int $postId ): iterable {
        $parameters                = $this->imagesQueryParams( 1, - 1 );
        $parameters['post_parent'] = $postId;

        return $this->query( $parameters );
    }

    public function countMediaForPost( int $postId ): array {
        $parameters                = $this->imagesQueryParams( 1, - 1 );
        $parameters['post_parent'] = $postId;
        $totalCount                = ( new \WP_Query( $parameters ) )->found_posts;

        $parameters['meta_query'] = [
            'relation' => 'AND',
            [
                'key'     => 'uploadcare_uuid',
                'compare' => 'EXISTS',
            ],
            [
                'key'     => 'uploadcare_uuid',
                'value'   => null,
                'compare' => '!=',
            ],
        ];
        $ucCount                  = ( new \WP_Query( $parameters ) )->found_posts;

        return [
            'total'      => $totalCount,
            'uploadcare' => $ucCount,
        ];
    }

    protected function query( array $params ): iterable {
        $query  = new \WP_Query( $params );
        $result = [];

        if ( ! $query->have_posts() ) {
            return $result;
        }

        foreach ( $query->posts as $post ) {
            $result[] = $post;
        }

        return $result;
    }

    public function countImageType( bool $local = false ): int {
        $parameters = $this->imagesQueryParams( 1, - 1 );
        $metaQuery  = [
            'relation' => 'AND',
            [
                'key'     => 'uploadcare_uuid',
                'compare' => 'EXISTS',
            ],
            [
                'key'     => 'uploadcare_uuid',
                'value'   => null,
                'compare' => '!=',
            ],
        ];

        if ( $local === true ) {
            $metaQuery = [
                'relation' => 'OR',
                [
                    'key'     => 'uploadcare_uuid',
                    'compare' => 'NOT EXISTS',
                    'value'   => '',
                ],
                [
                    'key'     => 'uploadcare_uuid',
                    'value'   => null,
                    'compare' => '=',
                ],
            ];
        }
        $parameters['meta_query'] = $metaQuery;

        return ( new \WP_Query( $parameters ) )->found_posts;
    }

    public function getCount(): int {
        return ( new \WP_Query( [ 'post_type'      => 'attachment',
                                  'post_status'    => 'any',
                                  'posts_per_page' => - 1
        ] ) )->found_posts;
    }

    protected function imagesQueryParams( int $page, int $postPerPage ): array {
        return [
            'post_type'      => 'attachment',
            'posts_per_page' => $postPerPage,
            'paged'          => $page,
            'post_status'    => 'any',
        ];
    }
}

