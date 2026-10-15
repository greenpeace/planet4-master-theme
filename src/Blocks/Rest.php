<?php

namespace P4\MasterTheme\Blocks;

use WP_REST_Server;
use WP_REST_Request;
use WP_Rest_Response;
use WP_Query;

/**
 * This class is a place for blocks REST endpoints to live.
 */
class Rest
{
    // Variables for the Spreadsheet block functions.
    private const SPREADSHEET_MAX_ROWS = 10000;
    private const SPREADSHEET_CACHE_LIFETIME = 30;

    public function load(): void
    {
        add_action('rest_api_init', function (): void {
            /**
             * Endpoint to retrieve the data for the Happy Point block.
             *
             * @example GET /wp-json/planet4/v1/get-happypoint-data
             */
            register_rest_route(
                BaseBlock::REST_NAMESPACE,
                '/get-happypoint-data',
                [
                    [
                        'permission_callback' => static function () {
                            return true;
                        },
                        'methods' => WP_REST_Server::READABLE,
                        'callback' => static function ($fields) {
                            return rest_ensure_response(self::get_happypoint_data($fields));
                        },
                    ],
                ]
            );

            /**
             * Endpoint to retrieve a Spreadsheet data and cache it.
             */
            register_rest_route(
                BaseBlock::REST_NAMESPACE,
                '/get-spreadsheet-data',
                [
                    [
                        'permission_callback' => static function () {
                            return true;
                        },
                        'methods' => WP_REST_Server::READABLE,
                        'callback' => static function () {
                            $sheet_id = filter_input(
                                INPUT_GET,
                                'sheet_id',
                                FILTER_VALIDATE_REGEXP,
                                [
                                    'options' => [
                                        'regexp' => '/[\w\d\-]+/',
                                    ],
                                ]
                            );

                            $sheet_data = self::get_spreadsheet($sheet_id, false);

                            return rest_ensure_response($sheet_data);
                        },
                    ],
                ]
            );

            /**
             * Endpoint to retrieve the data for the Take Action Boxout block.
             *
             * @example GET /wp-json/planet4/v1/action-pages
             */
            register_rest_route(
                BaseBlock::REST_NAMESPACE,
                '/action-pages',
                [
                    'methods' => WP_REST_Server::READABLE,
                    'callback' => [self::class, 'get_action_pages'],
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
        });
    }

    /**
     * Get the required data for the Happy Point block frontend.
     *
     * @param object $fields This object contains image search params, such as `id`.
     *
     * @return array Image data.
     */
    private static function get_happypoint_data(object $fields): array
    {
        $options = get_option('planet4_options');
        $image_id = $fields['id'] ?? $options['happy_point_bg_image_id'] ?? '';
        $img_meta = wp_get_attachment_metadata($image_id);
        $image_alt = get_post_meta($image_id, '_wp_attachment_image_alt', true);

        $data = [];
        $data['background_src'] = wp_get_attachment_image_src($image_id, 'retina-large')[0] ?? false;
        $data['background_srcset'] = wp_get_attachment_image_srcset($image_id, 'retina-large', $img_meta);
        $data['background_sizes'] = wp_calculate_image_sizes('retina-large', null, null, $image_id);
        $data['default_content_provider'] = $options['happy_point_content_provider'] ?? 'iframe_url';
        $data['engaging_network_id'] = $options['engaging_network_form_id'] ?? '';
        $data['default_image'] = get_template_directory_uri() . '/images/happy-point-block-bg.jpg';
        $data['background_alt'] = empty($image_alt) ? __('Background image', 'planet4-master-theme') : $image_alt;
        $data['default_embed_code'] = $options['happy_point_embed_code'] ?? '';

        return $data;
    }

    /**
     * Fetch a Google sheet by its ID. Used by the Spreadsheet block.
     *
     * @param string|null $sheet_id The ID of the Google sheet.
     * @param bool        $skip_cache Should the sheet be fetched from cache.
     * @return array|null The sheet or null if nothing was found.
     */
    public static function get_spreadsheet(?string $sheet_id, bool $skip_cache): ?array
    {
        if (! $sheet_id) {
            return null;
        }

        $cache_key = "spreadsheet_{$sheet_id}";

        if (! $skip_cache) {
            $from_cache = wp_cache_get($cache_key);

            if (false !== $from_cache) {
                return $from_cache;
            }
        }

        $url = "https://docs.google.com/spreadsheets/d/e/{$sheet_id}/pub?output=csv";

        $headers = get_headers($url);

        // Handle 500, 404 errors.
        if (! $headers || strpos($headers[0], '500') || strpos($headers[0], '404')) {
            return null;
        }

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fopen
        $handle = fopen($url, 'rb');

        if (false === $handle) {
            return null;
        }

        $rows = [];
        while (
            // phpcs:ignore Squiz.PHP.DisallowSizeFunctionsInLoops,WordPress.CodeAnalysis.AssignmentInCondition
            ( $data = fgetcsv($handle, 1000, ',') ) !== false
        ) {
            if (count($rows) > self::SPREADSHEET_MAX_ROWS) {
                break;
            }

            $rows[] = $data;
        }
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fclose
        fclose($handle);

        if (0 === count($rows)) {
            $sheet = null;
        } else {
            $sheet = [
                'header' => $rows[0],
                'rows' => array_slice($rows, 1),
            ];
        }

        wp_cache_add($cache_key, $sheet, null, self::SPREADSHEET_CACHE_LIFETIME);

        return $sheet;
    }

    /**
     * Endpoint to retrieve the data for the Take Action Boxout block.
     * Returns an array of Action pages.
     *
     * @example GET wp-json/planet4/v1/action-pages?&parent=9&isNewIA=true
     */
    public static function get_action_pages(WP_REST_Request $request): WP_Rest_Response
    {
        $exclude = (int) $request->get_param('exclude');
        $parent = (int) $request->get_param('parent');
        $is_new_ia = (bool) $request->get_param('isNewIA');
        $per_page = (int) $request->get_param('per_page');
        $posts = [];

        // Pages
        if ($parent !== -1) {
            $children = get_children([
                'post_type' => 'page',
                'post_parent' => $parent,
                'fields' => 'ids',
            ]);

            $post_ids = array_merge([$parent], $children);

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
    }
}
