<?php

namespace P4\MasterTheme\CustomPostType;

/**
 * Class ActionImporter
 *
 * Lets an admin/editor paste an external URL. On submit, PHP fetches the
 * page server-side, extracts its Open Graph data, and publishes a
 * p4_action post from it directly (no client-side preview step). URLs
 * that have already been imported are rejected. When an imported post is
 * permanently deleted, its associated redirection is removed too.
 */
class ActionImporter
{
    private const POST_TYPE = 'p4_action';
    private const PAGE_NAME = 'import-action';
    private const NONCE_ACTION = 'import_action';
    private const FORM_ACTION = 'import_url';
    private const REDIRECT_ID_META_KEY = 'action_importer_redirect_id';
    private const REDIRECT_ARG_SUCCESS = 'action_importer_success';
    private const REDIRECT_ARG_ERROR = 'action_importer_error';
    private const REDIRECT_ARG_EXISTING = 'action_importer_existing';

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->hooks();
    }

    /**
     * Hooks actions and filters.
     */
    public function hooks(): void
    {
        add_action('before_delete_post', [ $this, 'remove_redirection_on_delete' ], 10, 2);
        add_action('wp_trash_post', [ $this, 'disable_redirection_on_trash' ]);
        add_action('untrashed_post', [ $this, 'enable_redirection_on_untrash' ]);

        if (!current_user_can('manage_options') && !current_user_can('editor')) {
            return;
        }

        add_action('admin_menu', [ $this, 'add_options_page' ], 99);
        add_action('admin_init', [ $this, 'init_import' ]);
    }

    /**
     * Add menu options page.
     */
    public function add_options_page(): void
    {
        add_submenu_page(
            'edit.php?post_type=' . self::POST_TYPE,
            __('Import Action', 'planet4-master-theme-backend'),
            __('Import Action', 'planet4-master-theme-backend'),
            'manage_options',
            self::PAGE_NAME,
            [ $this, 'admin_page_display' ]
        );
    }

    /**
     * Orchestrates the import: validate submission, create the post,
     * wire up its side effects (redirect, search indexing), and redirect
     * back to the admin screen with a success/error notice.
     */
    public function init_import(): void
    {
        try {
            $redirect_args = [
                'post_type' => self::POST_TYPE,
                'page'      => self::PAGE_NAME,
            ];

            $url = $this->maybe_handle_submission($redirect_args);

            if ($url === null) {
                return;
            }

            $post_id = $this->import_from_url($url);

            if (is_wp_error($post_id)) {
                $redirect_args[self::REDIRECT_ARG_ERROR] = rawurlencode($post_id->get_error_message());
                $this->redirect_back($redirect_args);
            }

            $this->create_redirection_to_source($post_id, $url);
            $this->sync_elasticpress($post_id);

            $redirect_args[self::REDIRECT_ARG_SUCCESS] = $post_id;
            $this->redirect_back($redirect_args);
        } catch (\Throwable $e) {
            function_exists('\Sentry\captureException') && \Sentry\captureException($e);
        }
    }

    /**
     * Validate the form submission on admin_init, before any output.
     *
     * @param array<string,mixed> $redirect_args Base redirect query args
     *                                            (post_type, page) used for
     *                                            any error redirects.
     * @return string|null The validated URL, or null if there was no
     *                      submission to handle.
     */
    private function maybe_handle_submission(array $redirect_args): string|null
    {
        try {
            if (empty($_POST[self::FORM_ACTION])) {
                return null;
            }

            check_admin_referer(self::NONCE_ACTION);

            if (!current_user_can('manage_options') && !current_user_can('editor')) {
                wp_die(esc_html__('You are not allowed to do this.', 'planet4-master-theme-backend'));
            }

            $url = esc_url_raw(wp_unslash($_POST[self::FORM_ACTION]));

            if (!$url || !filter_var($url, FILTER_VALIDATE_URL)) {
                $redirect_args[self::REDIRECT_ARG_ERROR] = rawurlencode(
                    __('Please provide a valid URL.', 'planet4-master-theme-backend')
                );
                $this->redirect_back($redirect_args);
            }

            if (wp_parse_url($url, PHP_URL_SCHEME) !== 'https') {
                $redirect_args[self::REDIRECT_ARG_ERROR] = rawurlencode(
                    __('Only HTTPS URLs are allowed.', 'planet4-master-theme-backend')
                );
                $this->redirect_back($redirect_args);
            }

            $existing_post_id = $this->find_existing_post_by_url($url);

            if ($existing_post_id) {
                $redirect_args[self::REDIRECT_ARG_ERROR] = rawurlencode(
                    __('This URL has already been imported.', 'planet4-master-theme-backend')
                );
                $redirect_args[self::REDIRECT_ARG_EXISTING] = $existing_post_id;
                $this->redirect_back($redirect_args);
            }

            return $url;
        } catch (\Throwable $e) {
            function_exists('\Sentry\captureException') && \Sentry\captureException($e);
            return null;
        }
    }

    /**
     * Redirect back to the import page with status query args, then exit.
     *
     * @param array<string,mixed> $args Query args to append.
     */
    private function redirect_back(array $args): void
    {
        wp_safe_redirect(add_query_arg($args, admin_url('edit.php')));
        exit;
    }

    /**
     * Look up whether a post has already been imported from a given URL.
     *
     * @param string $url URL to check.
     * @return int Post ID if found, 0 otherwise.
     */
    private function find_existing_post_by_url(string $url): int
    {
        try {
            $existing = get_posts([
                'post_type'      => self::POST_TYPE,
                'post_status'    => 'any',
                'posts_per_page' => 1,
                'fields'         => 'ids',
                'meta_query'     => [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
                    [
                        'key'   => 'action_importer_source_url',
                        'value' => $url,
                    ],
                ],
            ]);

            return !empty($existing) ? (int) $existing[0] : 0;
        } catch (\Throwable $e) {
            function_exists('\Sentry\captureException') && \Sentry\captureException($e);
            return 0;
        }
    }

    /**
     * Derive a post slug from the last path segment of a URL.
     *
     * e.g. https://act.greenpeace.org.au/stop-big-gas -> "stop-big-gas"
     *
     * @param string $url Source URL.
     * @return string Sanitized slug, or empty string if none could be derived
     *                (wp_insert_post() will then fall back to the title).
     */
    private function slug_from_url(string $url): string
    {
        try {
            $path = (string) wp_parse_url($url, PHP_URL_PATH);
            $path = trim($path, '/');

            if ($path === '') {
                return '';
            }

            $segments = explode('/', $path);
            $last     = end($segments);

            return sanitize_title($last);
        } catch (\Throwable $e) {
            function_exists('\Sentry\captureException') && \Sentry\captureException($e);
            return '';
        }
    }

    /**
     * Fetch a URL, parse its Open Graph tags, and create a published post.
     */
    private function import_from_url(string $url): int|\WP_Error
    {
        try {
            $response = wp_remote_get($url, [
                'timeout'    => 12,
                'user-agent' => 'Planet4 Action Importer',
            ]);

            if (is_wp_error($response)) {
                return $response;
            }

            $code = wp_remote_retrieve_response_code($response);
            if ($code < 200 || $code >= 300) {
                return new \WP_Error(
                    'action_importer_http_error',
                    sprintf(
                        /* translators: %d is an HTTP status code. */
                        __('The remote server responded with status %d.', 'planet4-master-theme-backend'),
                        $code
                    )
                );
            }

            $body = wp_remote_retrieve_body($response);

            if (trim($body) === '') {
                return new \WP_Error(
                    'action_importer_empty_body',
                    __('The remote page returned no content.', 'planet4-master-theme-backend')
                );
            }

            $og = $this->parse_og_tags($body, $url);

            if (empty($og['title']) && empty($og['description'])) {
                return new \WP_Error(
                    'action_importer_no_og_data',
                    __('No Open Graph data could be found on that page.', 'planet4-master-theme-backend')
                );
            }

            $post_id = wp_insert_post([
                'post_type'    => self::POST_TYPE,
                'post_title'   => $og['title'] ?: __('(No title found)', 'planet4-master-theme-backend'),
                'post_content' => $og['description'],
                'post_status'  => 'publish',
                'post_name'    => $this->slug_from_url($url),
                'meta_input'   => [
                    'action_importer_source_url' => $url,
                ],
            ], true);

            if (is_wp_error($post_id)) {
                return $post_id;
            }

            if ($og['image']) {
                $this->set_featured_image_from_url($post_id, $og['image'], $og['title']);
            }

            return $post_id;
        } catch (\Throwable $e) {
            function_exists('\Sentry\captureException') && \Sentry\captureException($e);
            return new \WP_Error(
                    'action_importer_catch_error',
                    __($e, 'planet4-master-theme-backend')
                );
        }
    }

    /**
     * Queue the newly imported post for ElasticPress indexing.
     *
     * @param int $post_id Post ID to sync.
     */
    private function sync_elasticpress(int $post_id): void
    {
        if (!class_exists('\ElasticPress\Indexables')) {
            return;
        }

        try {
            $indexable = \ElasticPress\Indexables::factory()->get('post');
            $indexable->sync_manager->add_to_queue($post_id);
        } catch (\Throwable $e) {
            function_exists('\Sentry\captureException') && \Sentry\captureException($e);
        }
    }

    /**
     * Get (or create) the "Actions" redirection group.
     *
     * @return int Group ID. Falls back to the default group (1) if the
     *             Redirection plugin isn't active or group creation fails.
     */
    private function get_or_create_actions_group(): int
    {
        try {
            global $wpdb;

            $group_name = 'Actions';
            $table      = $wpdb->prefix . 'redirection_groups';

            $existing = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM $table WHERE name = %s", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
                $group_name
            ));

            if ($existing) {
                return (int) $existing;
            }

            if (!class_exists('Red_Group')) {
                return 1;
            }

            $group = \Red_Group::create($group_name, 1, true);

            if ($group instanceof \Red_Group) {
                return (int) $group->get_id();
            }

            return 1;
        } catch (\Throwable $e) {
            function_exists('\Sentry\captureException') && \Sentry\captureException($e);
            return 1;
        }
    }

    /**
     * Create a 301 redirect from the new post's permalink to the original
     * external URL it was imported from, inside the "Actions" group. The
     * created redirect's ID is stored as post meta so it can be cleaned up
     * later if the post is deleted.
     *
     * @param int    $post_id    Newly created post ID.
     * @param string $target_url Original external URL.
     */
    private function create_redirection_to_source(int $post_id, string $target_url): void
    {
        try {
            if (!class_exists('Red_Item')) {
                return;
            }

            $group_id = $this->get_or_create_actions_group();
            $source   = wp_parse_url(get_permalink($post_id), PHP_URL_PATH);

            if (!$source) {
                return;
            }

            $redirect = \Red_Item::create([
                'url'         => $source,
                'action_type' => 'url',
                'action_code' => 301,
                'match_type'  => 'url',
                'action_data' => [
                    'url' => $target_url,
                ],
                'group_id'    => $group_id,
            ]);

            if ($redirect instanceof \Red_Item) {
                $redirect_id = (int) $redirect->get_id();
            } else if (is_array($redirect) && !empty($redirect['id'])) {
                $redirect_id = (int) $redirect['id'];
            } else {
                $redirect_id = 0;
            }

            if ($redirect_id) {
                update_post_meta($post_id, self::REDIRECT_ID_META_KEY, $redirect_id);
            }
        } catch (\Throwable $e) {
            function_exists('\Sentry\captureException') && \Sentry\captureException($e);
        }
    }

    /**
     * Disable the redirection when a p4_action post is moved to Trash.
     */
    public function disable_redirection_on_trash(int $post_id): void
    {
        $this->set_redirection_status($post_id, 'disable');
    }

    /**
     * Re-enable the redirection when a p4_action post is restored from Trash.
     */
    public function enable_redirection_on_untrash(int $post_id): void
    {
        $this->set_redirection_status($post_id, 'enable');
    }

    /**
     * Remove the redirection associated with a p4_action post when that post is permanently deleted.
     */
    public function remove_redirection_on_delete(int $post_id): void
    {
        $this->set_redirection_status($post_id, 'delete');
    }

    /**
     * Enable or disable the redirection associated with a p4_action post.
     *
     * @param int  $post_id Post ID.
     * @param string $action The type of action to be executed.
     */
    private function set_redirection_status(int $post_id, string $action): void
    {
        $post = get_post($post_id);

        if (!$post || $post->post_type !== self::POST_TYPE) {
            return;
        }

        if (!class_exists('Red_Item')) {
            return;
        }

        $redirect_id = (int) get_post_meta($post_id, self::REDIRECT_ID_META_KEY, true);

        if (!$redirect_id) {
            return;
        }

        try {
            $item = \Red_Item::get_by_id($redirect_id);

            if (!$item instanceof \Red_Item) {
                return;
            }

            if ($action === 'enable') {
                $item->enable();
            } else if ($action === 'disable') {
                $item->disable();
            } else if ($action === 'delete') {
                $item->delete();
            }
        } catch (\Throwable $e) {
            function_exists('\Sentry\captureException') && \Sentry\captureException($e);
        }
    }

    /**
     * Parse Open Graph meta tags out of an HTML string.
     *
     * @param string $html       Raw HTML.
     * @param string $source_url Original URL, used as a fallback for og:url.
     * @return array{title:string, description:string, image:string, url:string}
     */
    private function parse_og_tags(string $html, string $source_url): array
    {
        $og = [
            'title'       => '',
            'description' => '',
            'image'       => '',
            'url'         => $source_url,
        ];

        try {
            // Guard against the PHP 8 ValueError thrown by loadHTML() on an empty string.
            if (trim($html) === '') {
                return $og;
            }

            $dom = new \DOMDocument();

            $previous_setting = libxml_use_internal_errors(true);
            $loaded            = $dom->loadHTML($html, LIBXML_NOERROR | LIBXML_NOWARNING);
            libxml_clear_errors();
            libxml_use_internal_errors($previous_setting);

            if (!$loaded) {
                return $og;
            }

            $metas = $dom->getElementsByTagName('meta');

            foreach ($metas as $meta) {
                /** @var \DOMElement $meta */
                $property = $meta->getAttribute('property') ?: $meta->getAttribute('name');
                $content  = $meta->getAttribute('content');

                if (!$property || $content === '') {
                    continue;
                }

                switch ($property) {
                    case 'og:title':
                        $og['title'] = $content;
                        break;
                    case 'og:description':
                        $og['description'] = $content;
                        break;
                    case 'og:image':
                    case 'og:image:secure_url':
                        if (empty($og['image'])) {
                            $og['image'] = $content;
                        }
                        break;
                    case 'og:url':
                        $og['url'] = $content;
                        break;
                }
            }

            // Fall back to <title> if og:title is missing.
            if (empty($og['title'])) {
                $titles = $dom->getElementsByTagName('title');
                if ($titles->length > 0) {
                    $og['title'] = $titles->item(0)->textContent;
                }
            }

            $og['title']       = sanitize_text_field($og['title']);
            $og['description'] = sanitize_text_field($og['description']);
            $og['url']          = esc_url_raw($og['url']);
            $og['image']        = esc_url_raw($og['image']);

            return $og;
        } catch (\Throwable $e) {
            function_exists('\Sentry\captureException') && \Sentry\captureException($e);
            return $og;
        }
    }

    /**
     * Sideload a remote image and set it as the post's featured image.
     *
     * @param int    $post_id   Target post ID.
     * @param string $image_url Remote image URL.
     * @param string $desc      Image description / alt text.
     */
    private function set_featured_image_from_url(int $post_id, string $image_url, string $desc): void
    {
        try {
            if (!function_exists('media_sideload_image')) {
                require_once ABSPATH . 'wp-admin/includes/media.php';
                require_once ABSPATH . 'wp-admin/includes/file.php';
                require_once ABSPATH . 'wp-admin/includes/image.php';
            }

            $media_id = media_sideload_image($image_url, $post_id, $desc, 'id');

            if (!is_wp_error($media_id)) {
                set_post_thumbnail($post_id, $media_id);
            }
        } catch (\Throwable $e) {
            function_exists('\Sentry\captureException') && \Sentry\captureException($e);
        }
    }

    /**
     * Render the admin page markup, including any success/error notice.
     */
    public function admin_page_display(): void
    {
        ?>
        <div class="wrap">
            <h2><?php echo esc_html__('Import Action', 'planet4-master-theme-backend'); ?></h2>

            <?php $this->maybe_render_notice(); ?>

            <form method="post">
                <?php wp_nonce_field(self::NONCE_ACTION); ?>
                <table class="form-table">
                    <tr>
                        <th><?php echo esc_html__('URL', 'planet4-master-theme-backend'); ?></th>
                        <td>
                            <input
                                type="url"
                                name="<?php echo self::FORM_ACTION; ?>"
                                class="regular-text"
                                required
                                pattern="https://.+"
                                title="<?php echo esc_attr__('Please enter a URL starting with https://', 'planet4-master-theme-backend'); ?>"
                                placeholder="https://act.greenpeace.org/landing-page"
                            />
                            <p>
                                <?php echo esc_html__(
                                    'Verify the results on submission. This feature has mostly been tested with Hubspot landing pages.',
                                    'planet4-master-theme-backend'
                                ); ?>
                            </p>
                        </td>
                    </tr>
                </table>
                <?php submit_button(__('Import Action', 'planet4-master-theme-backend')); ?>
            </form>
        </div>
        <?php
    }

    /**
     * Print a success/error admin notice based on the redirect query args.
     */
    private function maybe_render_notice(): void
    {
        if (!empty($_GET[self::REDIRECT_ARG_ERROR])) {
            $message = sanitize_text_field(wp_unslash($_GET[self::REDIRECT_ARG_ERROR]));

            if (!empty($_GET[self::REDIRECT_ARG_EXISTING])) {
                $existing_id  = absint($_GET[self::REDIRECT_ARG_EXISTING]);
                $existing_url = get_edit_post_link($existing_id, 'raw');
                printf(
                    '<div class="notice notice-error"><p>%s <a href="%s">%s</a></p></div>',
                    esc_html($message),
                    esc_url($existing_url),
                    esc_html__('View existing post', 'planet4-master-theme-backend')
                );
                return;
            }

            printf(
                '<div class="notice notice-error"><p>%s</p></div>',
                esc_html($message)
            );
            return;
        }

        if (!empty($_GET[self::REDIRECT_ARG_SUCCESS])) {
            $post_id   = absint($_GET[self::REDIRECT_ARG_SUCCESS]);
            $edit_url  = get_edit_post_link($post_id, 'raw');
            $view_url  = get_permalink($post_id);
            printf(
                '<div class="notice notice-success"><p>%s <a href="%s">%s</a> &middot; <a href="%s">%s</a></p></div>',
                esc_html__('Post published.', 'planet4-master-theme-backend'),
                esc_url($view_url),
                esc_html__('View it', 'planet4-master-theme-backend'),
                esc_url($edit_url),
                esc_html__('Edit it', 'planet4-master-theme-backend')
            );
        }
    }
}
