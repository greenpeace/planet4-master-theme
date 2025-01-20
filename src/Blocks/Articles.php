<?php

/**
 * Articles block class.
 *
 * @package P4GBKS
 */

namespace P4\MasterTheme\Blocks;

use WP_REST_Server;

/**
 * Class Articles
 *
 * @package P4GBKS\Blocks
 */
class Articles extends BaseBlock
{
    /**
     * Block name.
     *
     * @const string BLOCK_NAME.
     */
    public const BLOCK_NAME = 'articles';

    private const MAX_ARTICLES = 100;

    private const DEFAULT_POST_ARGS = [
        'orderby' => 'date',
        'post_status' => 'publish',
        'has_password' => false,
        'suppress_filters' => false,
    ];

    /**
     * Articles constructor.
     */
    public function __construct()
    {
        register_block_type(
            self::get_full_block_name(),
            [
                'editor_script' => 'planet4-blocks',
                // todo: Remove when all content is migrated.
                'render_callback' => static function ($attributes, $content) {
                    if (empty($attributes['read_more_text'])) {
                        $attributes['read_more_text'] = __('Load more', 'planet4-blocks');
                    }

                    if (empty($attributes['article_heading'])) {
                        $attributes['article_heading'] = __('Related Articles', 'planet4-blocks');
                    }

                    return self::hydrate_frontend($attributes, $content);
                },
                'attributes' => [
                    'article_heading' => [
                        'type' => 'string',
                        'default' => __('Related Articles', 'planet4-blocks'),
                    ],
                    'articles_description' => [
                        'type' => 'string',
                        'default' => '',
                    ],
                    'article_count' => [
                        'type' => 'integer',
                        'default' => 3,
                    ],
                    'tags' => [
                        'type' => 'array',
                        'default' => [],
                        'items' => [
                            'type' => 'integer', // Array definitions require an item type.
                        ],
                    ],
                    'posts' => [
                        'type' => 'array',
                        'default' => [],
                        'items' => [
                            'type' => 'integer',
                        ],
                    ],
                    'post_types' => [
                        'type' => 'array',
                        'default' => [],
                        'items' => [
                            'type' => 'integer',
                        ],
                    ],
                    'read_more_text' => [
                        'type' => 'string',
                        'default' => __('Load more', 'planet4-blocks'),
                    ],
                    'read_more_link' => [
                        'type' => 'string',
                        'default' => '',
                    ],
                    'button_link_new_tab' => [
                        'type' => 'boolean',
                        'default' => false,
                    ],
                    'ignore_categories' => [
                        'type' => 'boolean',
                        'default' => false,
                    ],
                ],
            ]
        );

        add_action('enqueue_block_editor_assets', [ self::class, 'enqueue_editor_assets' ]);
        add_action('wp_enqueue_scripts', [ self::class, 'enqueue_frontend_assets' ]);
        add_action('rest_api_init', [ self::class, 'register_endpoint' ]);
    }

    /**
     * Required by the `Base_Block` class.
     *
     * @param array $fields Unused, required by the abstract function.
     * @phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     */
    public function prepare_data(array $fields): array
    {
        return [];
    }
    //@phpcs:enable SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter

    /**
     * Get the initial posts, or load more of them.
     *
     * @param array $fields This is the array of fields of this block.
     *
     * @return array The data to be passed in the View.
     */
    public static function get_posts(array $fields): array
    {
        // Four scenarios for filtering posts.
        // 1) inside tag page - Get posts that have the specific tag assigned.
        // Add extra check for post_types and posts attributes to ensure that the block is rendered from a tag page.
        // 2) post types or tags -
        // a. Get posts by post types or tags defined from select boxes - new behavior.
        // b. inside post - Get results excluding specific post.
        // 3) specific posts - Get posts by ids specified in backend - new behavior / manual override.
        // 4) issue page - Get posts based on page's tags.
        if (empty($fields['posts']) && empty($fields['post_types']) && ! empty($fields['tags']) && is_tag()) {
            $args = self::filter_posts_for_tag_page($fields);
        } elseif (! empty($fields['posts'])) {
            $args = self::filter_posts_by_ids($fields);
        } elseif (
            ! empty($fields['post_types']) ||
                ! empty($fields['tags']) ||
                ! empty($fields['exclude_post_id'])
        ) {
            $args = self::filter_posts_by_page_types_or_tags($fields);
        } else {
            $args = self::filter_posts_by_pages_tags($fields);
        }

        // These should not be applied if there is a manual override of posts.
        if (empty($fields['posts'])) {
            // Take categories into account if needed.
            if (
                true !== $fields['ignore_categories'] &&
                isset($fields['categories']) &&
                ! empty($fields['categories'])
            ) {
                $args['category__in'] = $fields['categories'];
            }

            // For posts we need to exclude the current post.
            $exclude_post_id = (int) ( $fields['exclude_post_id'] ?? '' );
            if ($exclude_post_id) {
                $args['post__not_in'] = [ $exclude_post_id ];
            }
        }

        // If there is an offset, it means that it's not a first load, but a load more action.
        // In this case we want to get only the needed amount of posts,
        // since we already got the total amount in the first load.
        $offset = $fields['offset'] ? (int) $fields['offset'] : 0;
        if ($offset > 0) {
            $args['numberposts'] = $fields['article_count'];
            $args['offset'] = $offset;
        } else {
            $args['numberposts'] = self::MAX_ARTICLES;
        }

        // Ignore rule, arguments contain suppress_filters.
		// phpcs:ignore$fields['article_count']
        $all_posts = wp_get_recent_posts($args);
        $sliced_posts = $offset ? $all_posts : array_slice($all_posts, 0, $fields['article_count']);
        $recent_posts = [];

        // Populate posts array for frontend template if results have been returned.
        if (false !== $sliced_posts) {
            $recent_posts = self::populate_post_items($sliced_posts);
        }

        // Return the posts and the amount of pages.
        $to_return = [
            'recent_posts' => $recent_posts,
        ];

        if (! $offset) {
            $total_posts = count($all_posts);
            $to_return['total_posts'] = $total_posts;
        }

        return $to_return;
    }

    /**
     * Populate selected posts for frontend template.
     *
     * @param array $posts Selected posts.
     * @phpcs:disable Generic.Files.LineLength.MaxExceeded
     *
     */
    private static function populate_post_items(array $posts): array
    {
        $recent_posts = [];

        if ($posts) {
            foreach ($posts as $recent) {
                $recent['alt_text'] = '';
                // TODO - Update this method to use P4_Post functionality to get P4_User.
                $author_override = get_post_meta($recent['ID'], 'p4_author_override', true);
                $recent['author_name'] = '' === $author_override ? get_the_author_meta('display_name', $recent['post_author']) : $author_override;
                $recent['author_url'] = '' === $author_override ? get_author_posts_url($recent['post_author']) : '#';
                $recent['author_override'] = $author_override;

                if (has_post_thumbnail($recent['ID'])) {
                    $img_id = get_post_thumbnail_id($recent['ID']);
                    $dimensions = wp_get_attachment_metadata($img_id);
                    $recent['thumbnail_ratio'] = ( isset($dimensions['height']) && $dimensions['height'] > 0 ) ? $dimensions['width'] / $dimensions['height'] : 1;
                    $recent['alt_text'] = get_post_meta($img_id, '_wp_attachment_image_alt', true);
                    $recent['thumbnail_url'] = get_the_post_thumbnail_url($recent['ID'], 'articles-medium-large');

                    $recent['thumbnail_srcset'] = wp_get_attachment_image_srcset($img_id, 'articles-medium-large');
                }

                // TODO - Update this method to use P4_Post functionality to get Tags/Terms.
                $wp_tags = wp_get_post_tags($recent['ID']);

                $tags = [];

                if ($wp_tags) {
                    foreach ($wp_tags as $wp_tag) {
                        $tags_data['name'] = $wp_tag->name;
                        $tags_data['slug'] = $wp_tag->slug;
                        $tags_data['link'] = get_tag_link($wp_tag);
                        $tags[] = $tags_data;
                    }
                }

                $recent['tags'] = $tags;
                $page_type_data = get_the_terms($recent['ID'], 'p4-page-type');
                $page_type = '';
                $page_type_id = '';

                if ($page_type_data && ! is_wp_error($page_type_data)) {
                    $page_type = $page_type_data[0]->name;
                    $page_type_id = $page_type_data[0]->term_id;
                }

                $recent['page_type'] = $page_type;
                $recent['page_type_link'] = get_term_link($page_type_id);
                $recent['link'] = get_permalink($recent['ID']);
                $recent['date_formatted'] = get_the_date('', $recent['ID']);
                $recent['reading_time'] = ( new \P4\MasterTheme\Post($recent['ID']) )->reading_time_for_display();

                $recent_posts[] = $recent;
            }
        }

        return $recent_posts;
    }
    //@phpcs:enable Generic.Files.LineLength.MaxExceeded

    /**
     * Filter posts based on post ids.
     *
     * @param array $fields Block fields values.
     *
     * @return array|false
     */
    private static function filter_posts_by_ids(array &$fields)
    {

        $post_ids = $fields['posts'] ?? [];

        if (! empty($post_ids)) {
            // Get all posts with arguments.
            $args = [
                'orderby' => 'date',
                'post_status' => 'publish',
                'has_password' => false,
                'post__in' => $post_ids,
                'suppress_filters' => false,
            ];

            return $args;
        }

        return false;
    }

    /**
     * Filter posts based on post types (p4_page_type terms).
     *
     * @param array $fields Block fields values.
     *
     */
    private static function filter_posts_by_page_types_or_tags(array &$fields): array
    {
        // If any p4_page_type was selected extract the term's slug to be used in the wp query below.
        // post_types attribute filtering.
        $post_types = $fields['post_types'] ?? [];
        // Get user defined tags from backend.
        $tags = $fields['tags'] ?? [];

        // Validate tag ids.
        $tags = array_filter(
            (array) $tags,
            function ($tag_id) {
                return get_tag($tag_id) instanceof \WP_Term;
            }
        );

        // If user has not provided any tag, use post's tags.
        if (empty($tags)) {
            // Get page/post tags.
            $tags = get_the_tags();

            $tags = ! is_array($tags) ? [] : array_map(
                function ($tag) {
                    return $tag->term_id;
                },
                $tags
            );
        }

        // Get all posts with arguments.
        $args = self::DEFAULT_POST_ARGS;

        // Add filter for p4-page-type terms.
        if (! empty($post_types)) {
            $args['tax_query'] = [
                [
                    'taxonomy' => 'p4-page-type',
                    'field' => 'term_id',
                    'terms' => $post_types,
                ],
            ];
        }

        if (! empty($tags)) {
            $filtered_tag = [];
            foreach ($tags as $tag_id) {
                $tag = get_tag($tag_id);
                // Check if tag exist or not.
                if (!($tag instanceof \WP_Term)) {
                    continue;
                }

                $filtered_tag[] = $tag_id;
            }
            $args['tag__in'] = $filtered_tag;
        }

        return $args;
    }

    /**
     * Filter posts based for a specific tag page.
     *
     * @param array $fields Block fields values.
     *
     * @return array|false
     */
    private static function filter_posts_for_tag_page(array &$fields)
    {

        $tag_id = $fields['tags'] ?? '';
        $tag = get_tag($tag_id[0]);

        if ($tag instanceof \WP_Term) {
            // Get all posts with arguments.
            $args = self::DEFAULT_POST_ARGS;
            $args['tag__in'] = $tag_id;

            return $args;
        }

        return false;
    }

    /**
     * Filter posts based on page's/post's tags.
     *
     */
    private static function filter_posts_by_pages_tags(): array
    {

        // Get all posts with arguments.
        $args = self::DEFAULT_POST_ARGS;

        // Get page/post tags.
        $post_tags = get_the_tags();

        if ($post_tags) {
            $args['tag__in'] = array_map(
                function ($tag) {
                        return $tag->term_id;
                },
                $post_tags
            );
        }

        return $args;
    }

    /**
     * Register endpoint to retrieve the articles for the Articles block.
     *
     * @example GET /wp-json/planet4/v1/get-posts/
     */
    public static function register_endpoint(): void
    {
        register_rest_route(
            self::REST_NAMESPACE,
            'get-posts',
            [
                [
                    'permission_callback' => static function () {
                        return true;
                    },
                    'methods' => WP_REST_Server::READABLE,
                    'callback' => static function ($request) {
                        $covers = self::get_posts($request->get_params());
                        return rest_ensure_response($covers);
                    },
                ],
            ]
        );
    }
}
