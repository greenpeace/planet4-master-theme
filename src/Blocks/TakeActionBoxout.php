<?php

/**
 * TakeActionBoxout block class.
 *
 * @package P4\MasterTheme
 * @since 0.1
 */

 namespace P4\MasterTheme\Blocks;

 use WP_REST_Server;
 use WP_REST_Request;
 use WP_Query;

 /**
 * Class TakeActionBoxout
 *
 * @package P4\MasterTheme\Blocks
 */
class TakeActionBoxout extends BaseBlock
{
    /**
     * Block name.
     *
     * @const string BLOCK_NAME.
     */
    public const BLOCK_NAME = 'take-action-boxout';

    /**
     * TakeActionBoxout constructor.
     */
    public function __construct()
    {
        $this->register_takeactionboxout_block();
    }

    /**
     * Register Take action boxout block.
     */
    public function register_takeactionboxout_block(): void
    {
        register_block_type(
            self::get_full_block_name(),
            [
                'api_version' => 3,
                'editor_script' => 'planet4-blocks',
                'render_callback' => [ $this, 'render' ],
                'attributes' => [
                    'take_action_page' => [
                        'type' => 'integer',
                    ],
                    'title' => [
                        'type' => 'string',
                    ],
                    'excerpt' => [
                        'type' => 'string',
                    ],
                    'link' => [
                        'type' => 'string',
                    ],
                    'linkText' => [
                        'type' => 'string',
                    ],
                    'newTab' => [
                        'type' => 'boolean',
                        'default' => false,
                    ],
                    'tag_ids' => [
                        'type' => 'array',
                        'items' => [
                            'type' => 'integer', // Array definitions require an item type.
                        ],
                    ],
                    'imageId' => [
                        'type' => 'integer',
                    ],
                    'imageUrl' => [
                        'type' => 'string',
                    ],
                    'imageAlt' => [
                        'type' => 'string',
                    ],
                    'stickyOnMobile' => [
                        'type' => 'boolean',
                        'default' => false,
                    ],
                    'headingFontSize' => [
                        'type' => 'string',
                        'default' => 'medium',
                    ],
                ],
            ]
        );
        add_action('rest_api_init', [ self::class, 'register_endpoint' ]);
    }

    /**
     * Get all the data that will be needed to render the block correctly.
     *
     * @param array $fields This is the array of fields of this block.
     *
     * @return array The data to be passed in the View.
     */
    public function prepare_data(array $fields): array
    {

        $page_id = $fields['take_action_page'] ?? '';

        if (empty($page_id)) {
            $img_id = $fields['imageId'] ?? $fields['background_image'] ?? null;
            if (! empty($img_id)) {
                [ $src ] = wp_get_attachment_image_src($img_id, 'large');

                $src_set = wp_get_attachment_image_srcset($img_id);
                $alt_text = get_post_meta($img_id, '_wp_attachment_image_alt', true);
            }

            return [
                'boxout' => [
                    'title' => $fields['custom_title'] ?? $fields['title'] ?? '',
                    'excerpt' => $fields['custom_excerpt'] ?? $fields['excerpt'] ?? '',
                    'link' => $fields['custom_link'] ?? $fields['link'] ?? '',
                    'new_tab' => $fields['custom_link_new_tab'] ?? $fields['newTab'] ?? false,
                    'link_text' => $fields['custom_link_text'] ?? $fields['linkText'] ?? '',
                    'image' => $src ?? '',
                    'image_alt' => $alt_text ?? '',
                    'image_srcset' => $src_set ?? '',
                    'stickyMobile' => $fields['stickyOnMobile'] ?? false,
                    'headingFontSize' => $fields['headingFontSize'] ?? 'medium',
                ],
            ];
        }

        $args = [
            'p' => (int) $page_id, // ID of a page, post.
            'post_type' => 'any',
            'post_status' => 'publish',
        ];

        // Try to find the page that the user selected.
        $query = new \WP_Query($args);

        if (! $query->have_posts()) {
            return [];
        }

        // Populate the necessary fields for the block.
        $posts = $query->get_posts();
        $page = $posts[0];
        $options = get_option('planet4_options');

        if (has_post_thumbnail($page)) {
            $image = get_the_post_thumbnail_url($page, 'large');
            $img_id = get_post_thumbnail_id($page);
            $src_set = wp_get_attachment_image_srcset($img_id);
            $image_alt = get_post_meta($img_id, '_wp_attachment_image_alt', true);
        }

        $meta = get_post_meta($page_id);
        if (isset($meta['action_button_text']) && $meta['action_button_text'][0]) {
            $cover_button_text = $meta['action_button_text'][0];
        } else {
            $cover_button_text = $options['take_action_covers_button_text']
                ?? __('Take action', 'planet4-master-theme');
        }

        return [
            'boxout' => [
                'title' => null === $page ? '' : $page->post_title,
                'excerpt' => null === $page ? '' : $page->post_excerpt,
                'link' => null === $page ? '' : get_permalink($page),
                'new_tab' => false,
                'link_text' => $cover_button_text,
                'image' => $image ?? '',
                'image_alt' => $image_alt ?? '',
                'image_srcset' => $src_set ?? '',
                'stickyMobile' => $fields['stickyOnMobile'] ?? false,
                'headingFontSize' => $fields['headingFontSize'] ?? 'medium',
                'page_id' => $page_id,
            ],
        ];
    }

    /**
     * Endpoint to retrieve the data for the Take Action Boxout block
     *
     * @example GET wp-json/planet4/v1/action-pages?&parent=9&isNewIA=true
     */
    public static function register_endpoint(): void
    {
        $p4_get_action_pages = static function (WP_REST_Request $request) {
            $exclude = (int) $request->get_param('exclude');
            $parent = (int) $request->get_param('parent');
            $is_new_ia = (bool) $request->get_param('isNewIA');
            $per_page = (int) $request->get_param('per_page');

            $posts = [];
            $parent_id = (int) $parent;

            // Pages
            $children = get_children([
                'post_type' => 'page',
                'post_parent' => $parent_id,
                'fields' => 'ids',
            ]);

            $post_ids = array_merge([$parent_id], $children);

            $query = new WP_Query([
                'post_type' => 'page',
                'post__in' => $post_ids,
                'posts_per_page' => $per_page,
                'orderby' => 'title',
                'order' => 'ASC',
                'post__not_in' => $exclude ? [$exclude] : [],
                'fields' => 'ids',
            ]);

            foreach ($query->posts as $id) {
                $posts[] = [
                    'id' => $id,
                    'title' => get_the_title($id),
                    'type' => 'page',
                ];
            }

            // Actions
            if ($is_new_ia) {
                $action_query = new WP_Query([
                    'post_type' => 'p4_action',
                    'posts_per_page' => $per_page,
                    'orderby' => 'title',
                    'order' => 'ASC',
                    'post__not_in' => $exclude ? [ $exclude ] : [],
                    'fields' => 'ids',
                ]);

                foreach ($action_query->posts as $id) {
                    $posts[] = [
                        'id' => $id,
                        'title' => get_the_title($id),
                        'type' => 'p4_action',
                         'excerpt' => get_the_excerpt($id),
                        'link' => get_permalink($id),
                        'featured_media' => (int) get_post_thumbnail_id($id),
                        'meta' => [
                            'action_button_text' => get_post_meta(
                                $id,
                                'action_button_text',
                                true
                            ),
                        ],
                    ];
                }
            }

            usort($posts, fn ($a, $b) => strcasecmp($a['title'], $b['title']));
            return rest_ensure_response($posts);
        };

        register_rest_route(
            self::REST_NAMESPACE,
            '/action-pages',
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => $p4_get_action_pages,
                'permission_callback' => static function () {
                    return true;
                },
                'args' => [
                    'exclude' => [
                        'type' => 'integer',
                        'required' => false,
                    ],
                    'parent' => [
                        'type' => 'integer',
                        'required' => true,
                    ],
                    'isNewIA' => [
                        'type' => 'boolean',
                        'required' => true,
                    ],
                    'per_page' => [
                        'type' => 'integer',
                        'default' => 100,
                    ],
                ],
            ]
        );
    }
}
