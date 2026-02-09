<?php

namespace P4\MasterTheme;

use WP_Query;

/**
 * Class P4\MasterTheme\PostMeta
 */
class PostMeta
{
    public const POST_TYPE = 'post';

    public const EXCLUDE_FROM_SEARCH = 'ep_exclude_from_search';

    public const META_FIELDS = [
        'p4_og_title',
        'p4_og_description',
        'p4_og_image',
        'p4_og_image_id',
        'p4_seo_canonical_url',
        'p4_campaign_name',
        'p4_local_project',
        'p4_basket_name',
        'p4_department',
    ];

    /**
     * The constructor.
     */
    public function __construct()
    {
        $this->hooks();
    }

    /**
     * Class hooks.
     */
    private function hooks(): void
    {
        add_action('init', [ $this, 'register_post_meta' ]);
        add_action('pre_get_posts', [ $this, 'exclude_from_rss' ]);
    }

    /**
     * Register page meta data.
     */
    public function register_post_meta(): void
    {
        $args = [
            'show_in_rest' => true,
            'type' => 'string',
            'single' => true,
        ];

        foreach (self::META_FIELDS as $field) {
            register_post_meta(self::POST_TYPE, $field, $args);
        }
    }

    /**
     * Exclude posts from the RSS/Atom feed based on a custom meta value.
     */
    public function exclude_from_rss(WP_Query $query): void
    {
        if (!$query->is_feed()) {
            return;
        }

        $query->set(
            'meta_query',
            [
                'relation' => 'OR',

                // Meta key not set at all (older posts)
                [
                    'key' => self::EXCLUDE_FROM_SEARCH,
                    'compare' => 'NOT EXISTS',
                ],

                // Meta exists but is empty / false
                [
                    'key' => self::EXCLUDE_FROM_SEARCH,
                    'value' => '',
                    'compare' => '=',
                ],
            ]
        );
    }
}
