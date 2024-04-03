<?php

namespace P4\MasterTheme\Search;

use P4\MasterTheme\PostArchive;
use Timber\Timber;
use Timber\Loader;
use WP_Query;

class SearchPage
{
    public const DEFAULT_SORT = '_score';
    //public const DEFAULT_CACHE_TTL = 600;
    public const DEFAULT_CACHE_TTL = 0;
    public const POSTS_PER_LOAD = 5;
    public const TEMPLATES = ['search.twig', 'archive.twig', 'index.twig'];
    public const DUMMY_THUMBNAIL = '/images/dummy-thumbnail.png';
    public const SHOW_SCROLL_TIMES = 2;

    public array $posts = [];
    public array $context = [];

    public static ?array $aggregations;
    public static ?int $query_time;

    public static function hooks(): void
    {
        if (!ElasticSearch::is_active()) {
            return;
        }

        add_action(
            'ep_valid_response',
            function ($response): void {
                static::$aggregations = $response['aggregations'] ?? null;
                static::$query_time = $response['took'] ?? null;
            },
            1,
            10
        );
    }

    public function __construct(WP_Query $query)
    {
        $this->context = Timber::get_context();

        $this->populate_context($query);
    }

    public function populate_context(WP_Query $query): void
    {
        do_action('qm/debug', $query);
        // Search context.
        $this->context['posts'] = $query->posts;
        $this->context['posts'] = $this->populate_posts($this->context['posts']);

        $this->context['paged_posts'] = array_slice($query->posts, 0, self::POSTS_PER_LOAD);
        $this->context['current_page'] = $query->current_page;
        $this->context['search_query'] = $query->query_vars['s'];
        $this->context['selected_sort'] = $query->query['orderby'] ?? self::DEFAULT_SORT;
        $this->context['default_sort'] = self::DEFAULT_SORT;
        //$context['filters'] = $this->filters;
        $this->context['found_posts'] = $query->found_posts;
        $this->context['page_category'] = 'Search Page';
        $this->context['sort_options'] = [
            '_score' => [
                'name' => __('Most relevant', 'planet4-master-theme'),
                'order' => 'DESC',
            ],
            'post_date' => [
                'name' => __('Newest', 'planet4-master-theme'),
                'order' => 'DESC',
            ],
            'post_date_asc' => [
                'name' => __('Oldest', 'planet4-master-theme'),
                'order' => 'ASC',
            ],
        ];

        if ($query->search_query) {
            $this->context['page_title'] = sprintf(
                // translators: %1$d = Number of results.
                _n(
                    '%1$d result for \'%2$s\'',
                    '%1$d results for \'%2$s\'',
                    $context['found_posts'],
                    'planet4-master-theme'
                ),
                $this->context['found_posts'],
                $query->search_query
            );
        } else {
            $this->context['page_title'] = sprintf(
                // translators: %d = Number of results.
                _n('%d result', '%d results', $this->context['found_posts'], 'planet4-master-theme'),
                $this->context['found_posts']
            );
        }

        if (is_user_logged_in()) {
            $this->context['query_time'] = static::$query_time ?? 0;
        }


        $this->context['load_more'] = [
            'posts_per_load' => self::POSTS_PER_LOAD,
            // Translators: %s = number of results per page.
            'button_text' => sprintf(__('Show %s more results', 'planet4-master-theme'), self::POSTS_PER_LOAD),
        ];

        $paged = (int) get_query_var('paged');
        $this->context['current_page'] = $paged >= 1 ? $paged : 1;
    }

    /**
     * Add information to posts before display
     */
    public function populate_posts(array $posts): array
    {
        foreach ($posts as &$post) {
            $thumbnail = get_the_post_thumbnail_url($post->ID, 'thumbnail');
            $post->thumbnail_alt = get_the_post_thumbnail_caption($post->ID);
            $post->thumbnail = $thumbnail;
            if (PostArchive::POST_TYPE === $post->post_type) {
                $post->link = $post->guid;
            } else {
                $post->link = $post->permalink;
            }
        }
        return $posts;
    }

    public function render_partial(): void
    {
        $paged_context = $this->context;
        $paged_context['dummy_thumbnail'] = get_template_directory_uri() . self::DUMMY_THUMBNAIL;

        foreach ($this->context['paged_posts'] as $index => $post) {
            $paged_context['post'] = $post;
            $paged_context['first_of_the_page'] = 0 === $index % self::POSTS_PER_LOAD;
            Timber::render(
                ['tease-search.twig'],
                $paged_context,
                self::DEFAULT_CACHE_TTL,
                Loader::CACHE_OBJECT
            );
        }
    }

    public function render(): void
    {
        Timber::render(
            self::TEMPLATES,
            $this->context,
            self::DEFAULT_CACHE_TTL,
            Loader::CACHE_OBJECT
        );
    }
}
