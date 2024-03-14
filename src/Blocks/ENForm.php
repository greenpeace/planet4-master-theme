<?php

/**
 * ENForm block class
 *
 * @package P4\MasterTheme
 * @since 0.1
 */

namespace P4\MasterTheme\Blocks;

use P4\MasterTheme\Controllers\EnsapiController;
use WP_Post;
use WP_REST_Request;
use WP_Error;

/**
 * Class ENForm
 *
 * @package P4\MasterTheme\Blocks
 */
class ENForm extends BaseBlock
{
    /**
     * @const string BLOCK_NAME
     */
    protected const BLOCK_NAME = 'enform';

    /**
     * Page types for EN forms.
     *
     * @const array ENFORM_PAGE_TYPES
     */
    public const ENFORM_PAGE_TYPES = ['PET', 'EMS'];

    /**
     * Custom meta field where fields configuration is saved to.
     */
    private const FIELDS_META = 'p4enform_fields';

    /**
     * Block attributes.
     *
     * @var array $attributes Block attributes definition.
     */
    private static array $attributes = [
        'en_page_id' => ['type' => 'integer'],
        'enform_goal' => ['type' => 'string'],
        'en_form_style' => [
            'type' => 'string',
            'default' => 'side-style',
        ],
        'title' => ['type' => 'string'],
        'description' => ['type' => 'string'],
        'campaign_logo' => ['type' => 'boolean'],
        'content_title' => ['type' => 'string'],
        'content_title_size' => [
            'type' => 'string',
            'default' => 'h1',
        ],
        'content_description' => ['type' => 'string'],
        'button_text' => ['type' => 'string'],
        'text_below_button' => ['type' => 'string'],
        'thankyou_title' => ['type' => 'string'],
        'thankyou_subtitle' => ['type' => 'string'],
        'thankyou_donate_message' => ['type' => 'string'],
        'thankyou_social_media_message' => ['type' => 'string'],
        'donate_button_checkbox' => ['type' => 'boolean'],
        'donate_text' => ['type' => 'string'],
        'thankyou_url' => ['type' => 'string'],
        'custom_donate_url' => ['type' => 'string'],
        'background' => ['type' => 'integer'],
        'background_image_src' => [
            'type' => 'string',
            'default' => '',
        ],
        'background_image_srcset' => ['type' => 'string'],
        'background_image_sizes' => ['type' => 'string'],
        'background_image_focus' => [
            'type' => 'string',
            'default' => '50% 50%',
        ],
        'en_form_id' => ['type' => 'integer'],
        'en_form_fields' => [
            'type' => 'array',
            'default' => [],
        ],
        'social' => ['type' => 'object'],
        'social_accounts' => ['type' => 'object'],
    ];

    /**
     * ENForm constructor.
     */
    public function __construct()
    {
        $this->register_enform_block();
    }

    /**
     * Register block.
     */
    public function register_enform_block(): void
    {
        // Registering meta field to make it appear in REST API.
        \register_post_meta(
            'p4en_form',
            self::FIELDS_META,
            [
                'type' => 'object',
                'properties' => ['id' => ['type' => 'integer']],
                'show_in_rest' => true,
                'single' => true,
            ]
        );

        \register_block_type(
            self::get_full_block_name(),
            [
                'attributes' => static::$attributes,
                'render_callback' => function ($attributes, $content) {
                    $attributes = static::update_data($attributes);

                    return self::hydrate_frontend($attributes, $content);
                },
            ]
        );

        add_action('wp_ajax_get_en_session_token', [self::class, 'get_session_token']);
        add_action('wp_ajax_nopriv_get_en_session_token', [self::class, 'get_session_token']);

        add_action('enqueue_block_editor_assets', [self::class, 'enqueue_editor_assets']);
        add_action('wp_enqueue_scripts', [self::class, 'enqueue_frontend_assets']);
    }

    /**
     * @param array $attributes Block attributes.
     */
    public static function update_data(array $attributes): array
    {
        $form_id = (int) ($attributes['en_form_id'] ?? 0);
        $post = get_post($form_id);

        $attributes['content_description'] = isset($attributes['content_description'])
            ? wpautop($attributes['content_description'])
            : '';

        if (empty($attributes['en_form_fields']) && $form_id) {
            $attributes['en_form_fields'] = get_post_meta($form_id, self::FIELDS_META, true);
        }

        if (isset($attributes['background']) && empty($attributes['background_src'])) {
            $attributes = array_merge($attributes, self::get_background_data($attributes));
        }

        if ('campaign' === get_post_type() && isset($attributes['campaign_logo']) && $attributes['campaign_logo']) {
            $attributes = array_merge($attributes, self::get_campaign_data($post));
        }
        $post_id = get_the_ID();
        $attributes['social_accounts'] = self::get_social_accounts();
        $attributes['social'] = $post_id ? self::get_shareable_data($post_id) : [];

        $attributes['donatelink'] = !empty($attributes['custom_donate_url'])
            ? $attributes['custom_donate_url']
            : planet4_get_option('donate_button', '');

        return $attributes;
    }

    /**
     * Return camelized version of block name.
     */
    public static function get_camelized_block_name(): string
    {
        return 'ENForm';
    }

    /**
     * Load assets for the EN block frontend.
     */
    public static function enqueue_frontend_assets(): void
    {
        parent::enqueue_frontend_assets();

        wp_localize_script(
            'plugin-engagingnetworks',
            'p4_vars',
            ['ajaxurl' => admin_url('admin-ajax.php')]
        );

        wp_localize_script(
            'engagingnetworks-submit',
            'en_vars',
            ['ajaxurl' => admin_url('admin-ajax.php')]
        );
    }

    /**
     * Additional background image data.
     *
     * @param array $attributes Block attributes.
     */
    private static function get_background_data(array $attributes): array
    {
        $image_id = empty($attributes['background']) ? 0 : $attributes['background'];
        if (empty($image_id)) {
            $opts = get_option('planet4_options');
            $image_id = empty($opts['happy_point_bg_image_id']) ? 0 : $opts['happy_point_bg_image_id'];
        }
        $img_meta = wp_get_attachment_metadata($image_id);

        return [
            'background_image_src' => wp_get_attachment_image_src($image_id, 'retina-large')[0],
            'background_image_srcset' => wp_get_attachment_image_srcset($image_id, 'retina-large', $img_meta),
            'background_image_sizes' => wp_calculate_image_sizes('retina-large', null, null, $image_id),
        ];
    }

    /**
     * Get the campaign data.
     *
     * @param WP_Post $post The post.
     *
     * @return array The campaign data.
     */
    private static function get_campaign_data(WP_Post $post): array
    {
        $page_meta_data = get_post_meta($post->ID);
        if (!empty($page_meta_data['theme'])) {
            $campaign_template = $page_meta_data['theme'];
        } else {
            $campaign_template = !empty($page_meta_data['_campaign_page_template'][0])
                ? $page_meta_data['_campaign_page_template'][0]
                : null;
        }

        if (empty($campaign_template)) {
            return [];
        }

        $logo_path = get_bloginfo('template_directory') . '/images/' . $campaign_template . '/logo-light.png';
        if (!file_exists($logo_path)) {
            return [];
        }

        return [
            'campaign_template' => $campaign_template,
            'campaign_logo_path' => $logo_path,
        ];
    }

    /**
     * Get post data to share via social sharing functionalities.
     *
     * @param int $post_id Post ID.
     */
    private static function get_shareable_data(int $post_id): array
    {
        $og_title = '';
        $og_description = '';
        $link = '';
        if ($post_id > 0) {
            $og_title = get_post_meta($post_id, 'p4_og_title', true);
            if (empty($og_title)) {
                $og_title = get_the_title($post_id);
            }
            $og_description = get_post_meta($post_id, 'p4_og_description', true);
            $link = get_permalink($post_id);
        }

        $page_meta_data = get_post_meta($post_id);

        return [
            'title' => esc_attr($og_title),
            'description' => esc_attr(wp_strip_all_tags($og_description)),
            'link' => $link ? esc_url($link) : '',
            'utm_content' => 'postid-' . $post_id,
            'utm_campaign' => $page_meta_data['p4_local_project'] ?? null,
        ];
    }

    /**
     * Social accounts.
     *
     * @return array List of social accounts.
     */
    private static function get_social_accounts(): array
    {
        $social_accounts = [];
        $social_menu = wp_get_nav_menu_items('Footer Social');

        if (!isset($social_menu) || !is_iterable($social_menu)) {
            return $social_accounts;
        }

        $brands = [
            'facebook',
            'twitter',
            'youtube',
            'instagram',
        ];
        foreach ($social_menu as $social_menu_item) {
            $url_parts = explode('/', rtrim($social_menu_item->url, '/'));
            foreach ($brands as $brand) {
                if (false === strpos($social_menu_item->url, $brand)) {
                    continue;
                }
                $social_accounts[$brand] = count($url_parts) > 0 ? $url_parts[count($url_parts) - 1] : '';
            }
        }

        return $social_accounts;
    }

    /**
     * Get all the data that will be needed to render the block correctly.
     *
     * @param array $attributes This is the array of fields of this block.
     * @phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     */
    public function prepare_data(array $attributes): array
    {
        return [];
    }
    // @phpcs:enable SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter

    /**
     * Get en session token for frontend api calls.
     */
    public static function get_session_token(): string
    {
        $main_settings = get_option('p4en_main_settings');
        $ens_private_token = $main_settings['p4en_frontend_private_api'];
        $ens_api = new EnsapiController($ens_private_token, false);

        return $ens_api->get_public_session_token();
    }

    /**
     * Function to send an ENForm.
     *
     * @param WP_REST_Request $request the form data.
     */
    public static function send_enform(WP_REST_Request $request): array|WP_Error
    {
        $form = $request->get_json_params();
        $token = self::get_session_token();
        $en_page_id = (int) $request['en_page_id'] ?? null;
        if (! $en_page_id) {
            self::log_message('Invalid EN page ID', ['page_id' => $en_page_id]);
            return new WP_Error(
                'no_en_page_id',
                'Invalid EN page ID',
                ['status' => 404]
            );
        }

        $form = apply_filters('planet4_enform_data', $form, $en_page_id);
        $request = [
            'url' => 'https://e-activist.com/ens/service/page/' . $en_page_id . '/process',
            'args' => [
                'headers' => [
                    'content-type' => 'application/json',
                    'ens-auth-token' => $token,
                ],
                'body' => wp_json_encode($form),
            ],
        ];
        $response = wp_remote_post($request['url'], $request['args']);

        if (is_wp_error($response)) {
            self::log_message(
                'Error submitting EN form',
                [
                    'en_api_request' => $request,
                    'wp_error' => $response->get_all_error_data(),
                ]
            );

            return $response;
        }

        $response_code = $response['response']['code'] ?? 0;
        if (200 !== $response_code) {
            self::log_message(
                'Error submitting EN form',
                [
                    'en_api_request' => $request,
                    'en_api_response' => $response ?? [],
                ]
            );

            return new WP_Error(
                'submit_error',
                'Error submitting EN form',
                [
                    'status' => $response['response']['code'],
                    'response' => $response['response'],
                ]
            );
        }

        return rest_ensure_response([]);
    }

    /**
     * Log API response to Sentry.
     *
     * @param string $message Message.
     * @param array  $data    Data to log.
     */
    private static function log_message(string $message, array $data = []): void
    {
        if (! function_exists('\\Sentry\\withScope')) {
            return;
        }

        \Sentry\withScope(
            function (\Sentry\State\Scope $scope) use ($message, $data): void {
                foreach ($data as $key => $val) {
                    $scope->setContext($key, $val);
                }
                \Sentry\captureMessage($message);
            }
        );
    }
}
