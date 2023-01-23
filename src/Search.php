<?php

namespace P4\MasterTheme;

use ElasticPress\Features;
use SitePress;
use stdClass;
use Timber\Timber;
use UnexpectedValueException;
use WP_Query;
use WPML_Post_Element;
use P4\MasterTheme\Features\ActionPostType;

/**
 * Abstract Class Search
 */
abstract class Search
{
    const POSTS_PER_LOAD = 5;
    const SHOW_SCROLL_TIMES = 2;
    const DEFAULT_SORT = '_score';
    const DEFAULT_MIN_WEIGHT = 1;
    const DEFAULT_PAGE_WEIGHT = 100;
    const DEFAULT_ACTION_WEIGHT = 2000;
    const DEFAULT_MAX_WEIGHT = 3000;
    const DEFAULT_CACHE_TTL = 600;
    const DUMMY_THUMBNAIL = '/images/dummy-thumbnail.png';
    const EXCLUDE_FROM_SEARCH = 'p4_do_not_index';
    const DOCUMENT_TYPES = [
        'application/pdf',
    ];

    /**
     * Search Query
     *
     * @var string $search_query
     */
    protected $search_query;

    /**
     * Posts
     *
     * @var array|bool|null $posts
     */
    protected $posts;

    /**
     * Paged Posts
     *
     * @var array|bool|null $paged_posts
     */
    protected $paged_posts;

    /**
     * Selected sort criteria
     *
     * @var array $selected_sort
     */
    protected $selected_sort;

    /**
     * FIlters
     *
     * @var array $filters
     */
    protected $filters;

    /**
     * @var int|null The total number of matches.
     */
    protected $total_matches;

    /**
     * Templates
     *
     * @var array $templates
     */
    public $templates;

    /**
     * Context
     *
     * @var array $context
     */
    public $context;

    /**
     * Current Page
     *
     * @var int $current_page
     */
    public $current_page;

    /**
     * @var array|null Aggregations on the complete result set.
     */
    protected $aggregations;

    /**
     * @var int The time it took ElasticSearch to execute the query.
     */
    protected $query_time;

    /**
     * Initialize the class. Hook necessary actions and filters.
     */
    protected function initialize()
    {
        self::add_general_filters();
    }

    /**
     * Add filters that are needed by both the initial page load and subsequent ajax page loads.
     */
    public static function add_general_filters(): void
    {
        // Call apply filters to catch issue in WPML's ElasticPress integration, which uses the wrong filter name.
        add_filter(
            'ep_formatted_args',
            function ($args) {
                return apply_filters('ep_search_args', $args);
            },
            10,
            1
        );
        // Not sure if still needed, but there were cases in which the filters were not suppressed, causing content
        // to be unintentionally translated while syncing.
        add_filter(
            'ep_index_posts_args',
            function ($args) {
                return array_merge($args, [ 'suppress_filters' => true ]);
            },
            20
        );
        // Certain attachments could have these meta keys many times over with the same value, which can cause OOM
        // when syncing ElasticSearch. We don't need `sm_cloud` in ES and we only need one of `_wp_attachment_image_alt`.
        add_filter(
            'ep_prepare_meta_data',
            function ($meta, $post) {
                if (isset($meta['sm_cloud'])) {
                    unset($meta['sm_cloud']);
                }
                if (! empty($meta['_wp_attachment_image_alt'])) {
                    $meta['_wp_attachment_image_alt'] = [ $meta['_wp_attachment_image_alt'][0] ];
                }

                return $meta;
            },
            20,
            2
        );
        // Switch the language to the language of the current post while doing a sync of a post.
        // This is needed because otherwise WPML black magic will translate some parts of synced data for this post
        // into the language of the current admin, causing search results in the wrong language. As these syncs are
        // queued and then executed in random wp admin requests, this language is pretty much random.
        // It's safe to switch the language in this filter, as the ES queue is only processed at the very end of the
        // request, and calling `switch_lang` has no effect outside the request.
        // We need to abuse the `ep_ignore_invalid_dates` filter as it's the only one available to switch the
        // language in time that has the post available.
        add_filter(
            'ep_ignore_invalid_dates',
            function ($ignore, $post_id, $post) {
                /**
                 * @var SitePress
                 */
                global $sitepress;
                if ($sitepress) {
                    $lang_code = ( new WPML_Post_Element($post->ID, $sitepress) )->get_language_code();
                    $sitepress->switch_lang($lang_code);
                }
            },
            20,
            3
        );
        // Specify which fields to fetch so that we don't need to fetch the post content.
        add_filter(
            'ep_formatted_args',
            function ($formatted_args, $args) {
                $formatted_args['_source'] = [
                    'post_id',
                    'ID',
                    'post_author',
                    'post_date',
                    'post_date_gmt',
                    'post_title',
                    'post_excerpt',
                    'post_name',
                    'post_modified',
                    'post_modified_gmt',
                    'post_content',
                    'post_parent',
                    'post_type',
                    'post_mime_type',
                    'permalink',
                    'post_slug',
                    'terms',
                    'date_terms',
                    'comment_count',
                    'comment_status',
                    'guid',
                    'post_lang',
                ];

                return $formatted_args;
            },
            10,
            2
        );
        // Make it return the post guid as that's where we put the external url for the archive post type.
        add_filter(
            'ep_search_post_return_args',
            function ($args) {
                $args[] = 'guid';
                $args[] = 'terms';

                return $args;
            }
        );
        // Because of how the search is currently set up (using admin-ajax) this ElasticPress filter was not being
        // applied for subsequent page loads, only for the initial one.
        if (( ! isset($_GET['orderby']) || '_score' === $_GET['orderby'] ) && wp_doing_ajax()) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            add_filter(
                'ep_formatted_args',
                [ new \ElasticPress\Feature\Search\Search(), 'weight_recent' ],
                10,
                2
            );
        }
        // Only invoked if the page for posts is set.
        if (null !== get_option('page_for_posts')) {
            add_filter(
                'pre_get_posts',
                [ self::class, 'exclude_page_for_posts' ],
                10,
                1
            );
        }
        remove_filter(
            'pre_get_posts',
            [ Features::factory()->get_registered_feature('documents'), 'setup_document_search' ],
            10
        );
        add_filter(
            'ep_post_query_db_args',
            [ self::class, 'exclude_unwanted_attachments' ],
            10,
            1
        );
        add_filter(
            'ep_post_query_db_args',
            [ self::class, 'hide_password_protected_content' ],
            10,
            1
        );
    }

    /**
     * Conducts the actual search.
     *
     * @param string $search_query The searched term.
     * @param string $selected_sort The selected order_by.
     * @param array  $filters The selected filters.
     */
    public function load(
        $search_query,
        $selected_sort = self::DEFAULT_SORT,
        $filters = []
    ) {
        $this->initialize();
        $this->search_query = $search_query;
        $this->templates = [ 'search.twig', 'archive.twig', 'index.twig' ];

        $this->context = Timber::get_context();

        // Validate user input (sort, filters, etc).
        if ($this->validate($selected_sort, $filters, $this->context)) {
            $this->selected_sort = $selected_sort;
            $this->filters = $filters;
        }

        $this->posts = $this->get_posts();

        if ($this->posts) {
            $this->paged_posts = array_slice($this->posts, 0, self::POSTS_PER_LOAD);
        }

        $this->current_page = ( 0 === get_query_var('paged') ) ? 1 : get_query_var('paged');

        $this->set_context($this->context);
    }

    /**
     * Gets the paged posts that belong to the next page/load and are to be used with the twig template.
     */
    public static function get_paged_posts(): void
    {
        $paged = filter_input(INPUT_GET, 'paged', FILTER_SANITIZE_STRING);

        $search_async = new static();
        $search_async->set_context($search_async->context);
        $search_async->search_query = urldecode(filter_input(INPUT_GET, 'search_query', FILTER_SANITIZE_STRING));

        // Get the decoded url query string and then use it as key for redis.
        $query_string_full = urldecode(filter_input(INPUT_SERVER, 'QUERY_STRING', FILTER_SANITIZE_STRING));
        $query_string = str_replace('&query-string=', '', strstr($query_string_full, '&query-string='));

        $search_async->current_page = $paged;

        parse_str($query_string, $filters_array);
        $selected_sort = $filters_array['orderby'] ?? self::DEFAULT_SORT;
        $selected_filters = $filters_array['f'] ?? [];
        $filters = [];

        // Handle submitted filter options.
        if ($selected_filters && is_array($selected_filters)) {
            foreach ($selected_filters as $type => $filter_type) {
                if (! is_array($filter_type)) {
                    continue;
                }
                foreach ($filter_type as $name => $id) {
                    $filters[ $type ][] = [
                        'id' => $id,
                        'name' => $name,
                    ];
                }
            }
        }

        // Validate user input (sort, filters, etc).
        if ($search_async->validate($selected_sort, $filters, $search_async->context)) {
            $search_async->selected_sort = $selected_sort;
            $search_async->filters = $filters;
        }

        $search_async->paged_posts = $search_async->get_posts($search_async->current_page);

        // If there are paged results then set their context and send them back to client.
        if ($search_async->paged_posts) {
            $search_async->set_results_context($search_async->context);
            $search_async->view_paged_posts();
        }

        wp_die();
    }

    /**
     * Gets the respective Timber Posts, to be used with the twig template.
     * If there are not then uses Timber's get_posts to retrieve all of them (up to the limit set).
     *
     * @param int $paged The number of the page of the results to be shown when using pagination.
     *
     * @return array The respective Timber Posts.
     */
    protected function get_posts($paged = 1): array
    {
        $template_posts = [];

        $posts = $this->query_posts($paged);

        if (empty($posts)) {
            add_action('wp_head', 'wp_no_robots');
            if (! headers_sent()) {
                header('P4-Search: no-results');
            }
            return [];
        }

        foreach ($posts as $post) {
            if (PostArchive::POST_TYPE === $post->post_type) {
                $archive_post = new stdClass();
                $archive_post->id = $post->ID;
                $archive_post->post_title = $post->post_title;
                $archive_post->link = $post->guid;
                $archive_post->post_type = PostArchive::POST_TYPE;
                $archive_post->post_date = $post->post_date_gmt;
                $archive_post->post_excerpt = $post->post_excerpt;

                if (current_user_can('edit_posts')) {
                    $archive_post->edit_link = get_edit_post_link($post->ID);
                }

                $template_posts[] = $archive_post;
            } else {
                $template_post = $post;
                $template_post->id = $post->ID;
                $template_post->link = $post->permalink;
                $template_post->preview = $post->excerpt;
                $thumbnail = get_the_post_thumbnail_url($post->ID, 'thumbnail');
                $template_post->thumbnail_alt = get_the_post_thumbnail_caption($post->ID);
                $template_post->thumbnail = $thumbnail;

                $tags = $post->terms['post_tag'] ?? [];
                $p4_page_types = $post->terms['p4-page-type'] ?? [];
                // @todo Ensure the term link is synced to ElasticSearch so we don't have to fetch it here.
                $template_post->tags = self::filter_existing_terms($tags);
                $template_post->p4_page_types = self::filter_existing_terms($p4_page_types);

                if ('p4_action' === $post->post_type) {
                    $options = get_option('planet4_options');
                    $post_meta = get_post_meta($post->ID);

                    if (isset($post_meta['action_button_text']) && $post_meta['action_button_text'][0]) {
                        $template_post->button_text = $post_meta['action_button_text'][0];
                    } else {
                        $template_post->button_text = $options['take_action_covers_button_text'] ?? __('Take action', 'planet4-master-theme');
                    }
                }

                $template_posts[] = $template_post;
            }
        }

        return $template_posts;
    }

    /**
     * Applies user selected filters to the search if there are any and gets the filtered posts.
     *
     * @param int $paged The number of the page of the results to be shown when using pagination.
     *
     * @return array The posts of the search.
     */
    public function query_posts($paged = 1): array
    {
        // Set General Query arguments.
        $args = $this->get_general_args($paged);

        // Set Filters Query arguments.
        try {
            $this->set_filters_args($args);
        } catch (UnexpectedValueException $e) {
            $this->context['exception'] = $e->getMessage();

            return [];
        }

        // Set Engine Query arguments.
        $this->set_engines_args($args);
        add_action(
            'ep_valid_response',
            function ($response) {
                $this->aggregations = $response['aggregations'];
                $this->query_time = $response['took'];
            }
        );

        $query = ( new WP_Query() );
        $posts = $query->query($args);

        $this->total_matches = $query->found_posts;

        return (array) $posts;
    }

    /**
     * Sets arguments for the WP_Query that are related to the application.
     *
     * @param int $paged The number of the page of the results to be shown when using pagination.
     *
     * @return array
     */
    protected function get_general_args($paged): array
    {
        $args = [
            'posts_per_page' => self::POSTS_PER_LOAD,
            'no_found_rows' => true,
            'post_type' => self::get_post_types(),
            'post_status' => [ 'publish', 'inherit' ],
            'has_password' => false, // Skip password protected content.
        ];

        if ($paged > 1) {
            $args['paged'] = $paged;
        }

        if ($this->search_query) {
            $args['s'] = $this->search_query;
            $args['meta_query'] = [
                [
                    'key' => self::EXCLUDE_FROM_SEARCH,
                    'compare' => 'NOT EXISTS',
                ],
            ];
        } else {
            $args2 = [
                'orderby' => 'meta_value date', // If we search for everything then order first by 'weight' and then by 'post_date'.
                'order' => 'DESC DESC',
                'meta_query' => [
                    'relation' => 'AND',
                    [
                        'relation' => 'OR',
                        [
                            'key' => 'weight',
                            'compare' => 'NOT EXISTS',
                        ],
                        [
                            'key' => 'weight',
                            'compare' => 'EXISTS',
                        ],
                    ],
                    [
                        'key' => self::EXCLUDE_FROM_SEARCH,
                        'compare' => 'NOT EXISTS',
                    ],
                ],
            ];
            $args = array_merge($args, $args2);
        }

        $args['s'] = $this->search_query;
        // Add sort by date.
        if (wp_doing_ajax()) {
            // Get the decoded url query string and then use it as key for redis.
            $query_string_full = urldecode(filter_input(INPUT_SERVER, 'QUERY_STRING', FILTER_SANITIZE_STRING));
            $query_string = str_replace(
                '&query-string=',
                '',
                strstr($query_string_full, '&query-string=')
            );
            parse_str($query_string, $filters_array);
            $selected_sort = $filters_array['orderby'] ?? self::DEFAULT_SORT;
        } else {
            $selected_sort = filter_input(INPUT_GET, 'orderby', FILTER_SANITIZE_STRING);
        }
        $selected_sort = sanitize_sql_orderby($selected_sort);

        if ($selected_sort && self::DEFAULT_SORT !== $selected_sort) {
            $args['orderby'] = 'date';
            // Order by post_date = Newest[desc]/Oldest[asc].
            $args['order'] = 'post_date' === $selected_sort ? 'desc' : 'asc';
        }

        $args['search_fields'] = [
            'post_title',
            'post_content',
            'post_excerpt',
            'post_author.display_name',
        ];

        return $args;
    }

    /**
     * Get the post types that should be available in search.
     *
     * @return array The post types that should be in search.
     */
    private static function get_post_types()
    {
        $types = [
            'page',
            'campaign',
            'post',
            'attachment',
        ];

        if (self::should_include_archive()) {
            $types[] = PostArchive::POST_TYPE;
        }

        if (ActionPostType::is_active()) {
            $types[] = ActionPage::POST_TYPE;
        }

        return $types;
    }

    /**
     * Whether archived content should be in the results.
     *
     * @return bool Whether archived content should be in the results.
     */
    private static function should_include_archive(): bool
    {
        $setting = planet4_get_option('include_archive_content_for');

        return 'all' === $setting || ( 'logged_in' === $setting && is_user_logged_in() );
    }

    /**
     * Return only existing terms with their link.
     * We need to do this as the term might have been removed but ES could still have it.
     *
     * @param array $terms The terms to filter.
     *
     * @return mixed|null All existing terms, with link.
     */
    private static function filter_existing_terms(array $terms)
    {
        return array_reduce(
            $terms,
            static function ($carry, $term) {
                $link = get_term_link($term['term_id']);

                if (! is_wp_error($link)) {
                    $term['link'] = $link;
                    $carry[] = $term;
                }

                return $carry;
            },
            []
        );
    }

    /**
     * Adds arguments for the WP_Query that are related only to the search engine.
     *
     * @param array $args The search query args.
     */
    abstract public function set_engines_args(&$args);

    /**
     * Applies user selected filters to the search if there are any and gets the filtered posts.
     *
     * @param array $args The search query args.
     *
     * @throws UnexpectedValueException When filter type is not recognized.
     */
    public function set_filters_args(&$args)
    {
        if ($this->filters) {
            foreach ($this->filters as $type => $filter_type) {
                foreach ($filter_type as $filter) {
                    switch ($type) {
                        case 'cat':
                            $args['tax_query'][] = [
                                'taxonomy' => 'category',
                                'field' => 'term_id',
                                'terms' => $filter['id'],
                            ];
                            break;
                        case 'tag':
                            $args['tax_query'][] = [
                                'taxonomy' => 'post_tag',
                                'field' => 'term_id',
                                'terms' => $filter['id'],
                            ];
                            break;
                        case 'ptype':
                            // This taxonomy is used only for Posts.
                            $args['post_type'] = 'post';
                            $args['tax_query'][] = [
                                'taxonomy' => 'p4-page-type',
                                'field' => 'term_id',
                                'terms' => $filter['id'],
                            ];
                            break;
                        case 'atype':
                            // This taxonomy is used only for Posts.
                            $args['post_type'] = ActionPage::POST_TYPE;
                            $args['tax_query'][] = [
                                'taxonomy' => ActionPage::TAXONOMY,
                                'field' => 'term_id',
                                'terms' => $filter['id'],
                            ];
                            break;
                        case 'ctype':
                            switch ($filter['id']) {
                                case 1:
                                    $args['post_type'] = 'attachment';
                                    $args['post_status'] = 'inherit';
                                    $args['post_mime_type'] = self::DOCUMENT_TYPES;
                                    break;
                                case 2:
                                    $args['post_type'] = 'page';
                                    $args['post_status'] = 'publish';
                                    break;
                                case 3:
                                    $args['post_type'] = 'post';
                                    $args['post_status'] = 'publish';
                                    break;
                                case 4:
                                    $args['post_type'] = 'campaign';
                                    $args['post_status'] = 'publish';
                                    break;
                                case 5:
                                    $args['post_type'] = 'archive';
                                    break;
                                case 6:
                                    $args['post_type'] = ActionPage::POST_TYPE;
                                    $args['post_status'] = 'publish';
                                    break;
                                default:
                                    throw new UnexpectedValueException('Unexpected content type!');
                            }
                            break;
                        default:
                            throw new UnexpectedValueException('Unexpected filter!');
                    }
                }
            }
        }
    }

    /**
     * Sets the P4 Search page context.
     *
     * @param array $context Associative array with the data to be passed to the view.
     */
    protected function set_context(&$context)
    {
        $this->set_general_context($context);
        try {
            $this->set_filters_context($context);
        } catch (UnexpectedValueException $e) {
            $this->context['exception'] = $e->getMessage();
        }
        $this->set_results_context($context);
    }

    /**
     * Sets the general context for the Search page.
     *
     * @param array $context Associative array with the data to be passed to the view.
     */
    protected function set_general_context(&$context)
    {

        // Search context.
        $context['posts'] = $this->posts;
        $context['paged_posts'] = $this->paged_posts;
        $context['current_page'] = $this->current_page;
        $context['search_query'] = $this->search_query;
        $context['selected_sort'] = $this->selected_sort;
        $context['default_sort'] = self::DEFAULT_SORT;
        $context['filters'] = $this->filters;
        $context['found_posts'] = $this->total_matches;
        $context['page_category'] = 'Search Page';
        $context['sort_options'] = [
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

        if ($this->search_query) {
            $context['page_title'] = sprintf(
                // translators: %1$d = Number of results.
                _n('%1$d result for \'%2$s\'', '%1$d results for \'%2$s\'', $context['found_posts'], 'planet4-master-theme'),
                $context['found_posts'],
                $this->search_query
            );
        } else {
            // translators: %d = Number of results.
            $context['page_title'] = sprintf(_n('%d result', '%d results', $context['found_posts'], 'planet4-master-theme'), $context['found_posts']);
        }

        if (is_user_logged_in()) {
            $context['query_time'] = $this->query_time;
        }
    }

    /**
     * Set filters context
     *
     * @param mixed $context Context.
     *
     * @throws UnexpectedValueException When filter type is not recognized.
     */
    protected function set_filters_context(&$context)
    {
        // Categories.
        $context['categories'] = Search\Filters\Categories::get_filters();
        uasort($context['categories'], fn ($a, $b) => strcmp($a['name'], $b['name']));

        // Post Types.
        $context['post_types'] = Search\Filters\PostTypes::get_filters();

        // Action Types.
        $context['action_types'] = ActionPostType::is_active()
            ? Search\Filters\ActionTypes::get_filters()
            : [];

        // Content Types.
        $context['content_types'] = Search\Filters\ContentTypes::get_filters(
            self::should_include_archive(),
            ActionPostType::is_active()
        );

        // Tag <-> Campaign.
        $context['tags'] = Search\Filters\Tags::get_filters();
        uasort($context['tags'], fn ($a, $b) => strcmp($a['name'], $b['name']));

        // Keep track of which filters are already checked.
        if ($this->filters) {
            foreach ($this->filters as $type => $filter_type) {
                foreach ($filter_type as $filter) {
                    switch ($type) {
                        case 'cat':
                            $context['categories'][ $filter['id'] ]['checked'] = 'checked';
                            break;
                        case 'tag':
                            $context['tags'][ $filter['id'] ]['checked'] = 'checked';
                            break;
                        case 'ptype':
                            $context['post_types'][ $filter['id'] ]['checked'] = 'checked';
                            break;
                        case 'ctype':
                            $context['content_types'][ $filter['id'] ]['checked'] = 'checked';
                            break;
                        case 'atype':
                            $context['action_types'][ $filter['id'] ]['checked'] = 'checked';
                            break;
                        default:
                            throw new UnexpectedValueException('Unexpected filter!');
                    }
                }
            }
        }
    }

    /**
     * Sets the context for the results of the Search.
     *
     * @param array $context Associative array with the data to be passed to the view.
     */
    protected function set_results_context(&$context)
    {

        $posts = $this->posts ?? $this->paged_posts;

        // Retrieve P4 settings in order to check that we add only categories that are children of the Issues category.
        $options = get_option('planet4_options');

        // Pass planet4 settings.
        $context['settings'] = $options;

        // Set default thumbnail.
        $context['dummy_thumbnail'] = get_template_directory_uri() . self::DUMMY_THUMBNAIL;

        if (! empty($this->aggregations)) {
            $aggs = $this->aggregations['with_post_filter'];
            foreach ($aggs['post_type']['buckets'] as $post_type_agg) {
                if ('attachment' === $post_type_agg['key']) {
                    $context['content_types']['1']['results'] = $post_type_agg['doc_count'];
                }
                if (Search\Filters\ContentTypes::PAGE === $post_type_agg['key']) {
                    $context['content_types']['2']['results'] = $post_type_agg['doc_count'];
                }
                if (Search\Filters\ContentTypes::POST === $post_type_agg['key']) {
                    $context['content_types']['3']['results'] = $post_type_agg['doc_count'];
                }
                if (Search\Filters\ContentTypes::CAMPAIGN === $post_type_agg['key']) {
                    $context['content_types']['4']['results'] = $post_type_agg['doc_count'];
                }
                if (Search\Filters\ContentTypes::ARCHIVE === $post_type_agg['key'] && self::should_include_archive()) {
                    $context['content_types']['5']['results'] = $post_type_agg['doc_count'];
                }
                if (Search\Filters\ContentTypes::ACTION === $post_type_agg['key']) {
                    $context['content_types']['6']['results'] = $post_type_agg['doc_count'];
                }
            }

            foreach ($aggs['p4-page-type']['buckets'] as $p4_post_type_agg) {
                if (! isset($context['post_types'][ $p4_post_type_agg['key'] ])) {
                    continue;
                }
                $context['post_types'][ $p4_post_type_agg['key'] ]['results'] = $p4_post_type_agg['doc_count'];
            }

            foreach ($aggs[ ActionPage::TAXONOMY ]['buckets'] as $p4_action_type_agg) {
                if (! isset($context['action_types'][ $p4_action_type_agg['key'] ])) {
                    continue;
                }
                $context['action_types'][ $p4_action_type_agg['key'] ]['results'] = $p4_action_type_agg['doc_count'];
            }

            foreach ($aggs['categories']['buckets'] as $category_agg) {
                if (! isset($context['categories'][ $category_agg['key'] ])) {
                    continue;
                }
                $context['categories'][ $category_agg['key'] ]['results'] = $category_agg['doc_count'];
            }

            foreach ($aggs['tags']['buckets'] as $tag_agg) {
                if (! isset($context['tags'][ $tag_agg['key'] ])) {
                    continue;
                }
                $context['tags'][ $tag_agg['key'] ]['results'] = $tag_agg['doc_count'];
            }
        }

        $content_types = Search\Filters\ContentTypes::get_filters();
        foreach ((array) $posts as $post) {
            $type = $content_types[ $post->post_type ] ?? null;
            if (! $type) {
                continue;
            }

            if (! isset($post->ID)) {
                $post->ID = $post->link;
            }

            // Page Type <-> Category. This taxonomy is used only for Posts.
            if ('post' === $post->post_type && ! empty($post->terms['p4-page-type'])) {
                $post->page_types = [
                    self::get_p4_post_type(
                        $post->terms['p4-page-type'][0]
                    ),
                ];
            }
        }
    }

    /**
     * Load the p4 page type.
     *
     * @param string|int $id The ID of the p4 page type.
     * @return mixed|null The p4 page type.
     * @todo Get this from ES.
     */
    private static function get_p4_post_type($id)
    {
        $p4_post_types = get_terms('p4-page-type');

        foreach ($p4_post_types as $p4_post_type) {
            if ($id === $p4_post_type->term_id) {
                return $p4_post_type;
            }
        }

        return null;
    }

    /**
     * Validates the input.
     *
     * @param string $selected_sort The selected orderby to be validated.
     * @param array  $filters The selected filters to be validated.
     * @param array  $context Associative array with the data to be passed to the view.
     *
     * @return bool True if validation is ok, false if validation fails.
     */
    public function validate(&$selected_sort, &$filters, $context): bool
    {
        $selected_sort = filter_var($selected_sort, FILTER_SANITIZE_STRING);

        if (
            ! isset($context['sort_options'])
            || ! array_key_exists($selected_sort, $context['sort_options'])
        ) {
            $selected_sort = self::DEFAULT_SORT;
        }

        if ($filters) {
            foreach ($filters as &$filter_type) {
                foreach ($filter_type as &$filter) {
                    $filter['id'] = filter_var($filter['id'], FILTER_VALIDATE_INT);
                    if (false === $filter['id'] || null === $filter['id'] || $filter['id'] < 0) {
                        return false;
                    }
                }
            }
        }

        return true;
    }

    /**
     * Adds a section with a Load more button.
     */
    public function add_load_more()
    {
        $this->context['load_more'] = [
            'posts_per_load' => self::POSTS_PER_LOAD,
            // Translators: %s = number of results per page.
            'button_text' => sprintf(__('Show %s more results', 'planet4-master-theme'), self::POSTS_PER_LOAD),
        ];
    }

    /**
     * View the Search page template.
     */
    public function view()
    {
        Timber::render($this->templates, $this->context, self::DEFAULT_CACHE_TTL, \Timber\Loader::CACHE_OBJECT);
    }

    /**
     * View the paged posts of the next page/load.
     */
    public function view_paged_posts()
    {
        // TODO - The $paged_context related code should be transferred to set_results_context method for better separation of concerns.
        if ($this->paged_posts) {
            $paged_context['dummy_thumbnail'] = get_template_directory_uri() . self::DUMMY_THUMBNAIL;

            foreach ($this->paged_posts as $index => $post) {
                $paged_context['post'] = $post;
                if (0 === $index % self::POSTS_PER_LOAD) {
                    $paged_context['first_of_the_page'] = true;
                } else {
                    $paged_context['first_of_the_page'] = false;
                }
                Timber::render([ 'tease-search.twig' ], $paged_context, self::DEFAULT_CACHE_TTL, \Timber\Loader::CACHE_OBJECT);
            }
        }
    }

    /**
     * Fetch all attachments that we don't want to include in search,
     * so that we can exclude them from ElasticPress sync.
     *
     * @param mixed[] $args The args ElasticPress will use to fetch the ids of posts that will be synced.
     *
     * @return mixed The args with exclusion of unwanted ids.
     * @throws Exception\SqlInIsEmpty Well it really won't unless we make self::DOCUMENT_TYPES into an empty array.
     */
    public static function exclude_unwanted_attachments($args)
    {
        global $wpdb;

        $params = new SqlParameters();

        $sql = 'SELECT id FROM ' . $params->identifier($wpdb->posts)
            . ' WHERE post_type = "attachment"'
            . ' AND post_mime_type NOT IN ' . $params->string_list(self::DOCUMENT_TYPES);

        $unwanted_attachment_ids = $wpdb->get_col(
            $wpdb->prepare($sql, $params->get_values()) // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        );

        $args['post__not_in'] = $unwanted_attachment_ids;

        return $args;
    }

    /**
     * Exclude the page for posts set through the Settings > Reading page.
     *
     * @param query $query The Query ElasticPress will use to fetch the ids of posts.
     *
     * @return query The Query with exclusion of the page for posts.
     */
    public static function exclude_page_for_posts($query)
    {
        $page_for_posts = get_option('page_for_posts');

        if ($page_for_posts) {
            $query->set('post__not_in', [ $page_for_posts ]);
        }

        return $query;
    }

    /**
     * Exclude password protected content from ElasticPress sync.
     *
     * @param mixed[] $args The args ElasticPress will use to fetch the ids of posts that will be synced.
     *
     * @return mixed The args with exclusion of password protected content.
     */
    public static function hide_password_protected_content($args)
    {
        $args['has_password'] = false;

        return $args;
    }
}
