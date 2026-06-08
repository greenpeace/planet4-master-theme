<?php

namespace P4\MasterTheme\Blocks;

use WP_REST_Server;

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
}
