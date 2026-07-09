<?php

namespace P4\MasterTheme\CustomPostType;

/**
 * Class ActionImporter
 *
 * Lets an admin/editor paste an external URL. On submit, PHP fetches the page server-side,
 * extracts its Open Graph data, and publishes a p4_action post from it directly.
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
    private const REDIRECT_ARG_WARNING = 'action_importer_redirect_warning';
    private const NOTICE_TRANSIENT_PREFIX = 'action_importer_notice_';
    private const SENTRY_EXCEPTION = '\Sentry\captureException';

    /**
     * Constructor.
     */
    public function __construct()
    {
        add_action('before_delete_post', [ $this, 'remove_redirection_on_delete' ], 10, 2);
        add_action('wp_trash_post', [ $this, 'disable_redirection_on_trash' ]);
        add_action('untrashed_post', [ $this, 'enable_redirection_on_untrash' ]);
        add_action('admin_notices', [ $this, 'render_queued_admin_notice' ]);

        if (!current_user_can('manage_options') && !current_user_can('editor')) {
            return;
        }

        add_action('admin_menu', [ $this, 'add_options_page' ], 99);
        add_action('admin_init', [ $this, 'init_import' ]);
        add_action('admin_enqueue_scripts', [ $this, 'enqueue_admin_assets' ]);
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
            [ $this, 'admin_page_display' ],
            2
        );
    }

    /**
     * Load admin assets.
     */
    public function enqueue_admin_assets(): void
    {
        wp_enqueue_script(
            'action-importer-script',
            get_template_directory_uri() . "/admin/js/action_importer.js",
            [],
            \P4\MasterTheme\Loader::theme_file_ver('admin/js/action_importer.js'),
            true
        );
    }

    /**
     * Orchestrates the import.
     */
    public function init_import(): void
    {
        try {
            $redirect_args = [
                'post_type' => self::POST_TYPE,
                'page' => self::PAGE_NAME,
            ];

            $url = $this->handle_submission($redirect_args);

            if ($url === null) {
                return;
            }

            $post_id = $this->import_from_url($url);

            if (is_wp_error($post_id)) {
                $redirect_args[self::REDIRECT_ARG_ERROR] = rawurlencode($post_id->get_error_message());
                $this->redirect_back($redirect_args);
            }

            $redirect_created = $this->create_redirection_to_source($post_id, $url);
            $this->sync_elasticpress($post_id);

            $redirect_args[self::REDIRECT_ARG_SUCCESS] = $post_id;

            if (!$redirect_created) {
                $redirect_args[self::REDIRECT_ARG_WARNING] = 1;
            }

            $this->redirect_back($redirect_args);
        } catch (\Throwable $e) {
            function_exists(self::SENTRY_EXCEPTION) && \Sentry\captureException($e);
        }
    }

    /**
     * Validate the form submission.
     */
    private function handle_submission(array $redirect_args): mixed
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
            function_exists(self::SENTRY_EXCEPTION) && \Sentry\captureException($e);
            $redirect_args[self::REDIRECT_ARG_ERROR] = rawurlencode(
                __('Something went wrong validating that URL. Please try again.', 'planet4-master-theme-backend')
            );
            $this->redirect_back($redirect_args);
            return null;
        }
    }

    /**
     * Redirect back to the import page with status query args.
     */
    private function redirect_back(array $args): void
    {
        wp_safe_redirect(add_query_arg($args, admin_url('edit.php')));
        exit;
    }

    /**
     * Look up whether a post has already been imported from a given URL.
     */
    private function find_existing_post_by_url(string $url): int
    {
        $existing = get_posts([
            'post_type' => self::POST_TYPE,
            'post_status' => 'any',
            'posts_per_page' => 1,
            'fields' => 'ids',
            'meta_query' => [
                [
                    'key' => 'action_importer_source_url',
                    'value' => $url,
                ],
            ],
        ]);

        return !empty($existing) ? (int) $existing[0] : 0;
    }

    /**
     * Derive a post slug from the last path segment of a URL.
     * e.g. https://act.greenpeace.org.au/stop-big-gas ---> "stop-big-gas"
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
            $last = end($segments);

            return sanitize_title($last);
        } catch (\Throwable $e) {
            function_exists(self::SENTRY_EXCEPTION) && \Sentry\captureException($e);
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
                'timeout' => 12,
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
                'post_type' => self::POST_TYPE,
                'post_title' => $og['title'] ?: __('No title found', 'planet4-master-theme-backend'),
                'post_content' => $og['description'],
                'post_excerpt' => $og['description'],
                'post_status' => 'publish',
                'post_name' => $this->slug_from_url($url),
                'meta_input' => [
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
            function_exists(self::SENTRY_EXCEPTION) && \Sentry\captureException($e);
            return new \WP_Error(
                'action_importer_catch_error',
                __('There was an error trying to import the remote page data.', 'planet4-master-theme-backend')
            );
        }
    }

    /**
     * Queue the newly imported post for ElasticPress indexing.
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
            function_exists(self::SENTRY_EXCEPTION) && \Sentry\captureException($e);
        }
    }

    /**
     * Get or create the "Actions" redirection group.
     */
    private function get_or_create_actions_group(): int
    {
        try {
            global $wpdb;

            $group_name = 'Actions';
            $table = $wpdb->prefix . 'redirection_groups';

            $existing = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM $table WHERE name = %s",
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
            function_exists(self::SENTRY_EXCEPTION) && \Sentry\captureException($e);
            return 1;
        }
    }

    /**
     * Create a 301 redirect from the new post's permalink to the original
     * external URL it was imported from, inside the "Actions" group.
     */
    private function create_redirection_to_source(int $post_id, string $target_url): bool
    {
        try {
            if (!class_exists('Red_Item')) {
                return false;
            }

            $group_id = $this->get_or_create_actions_group();
            $source = wp_parse_url(get_permalink($post_id), PHP_URL_PATH);

            if (!$source) {
                return false;
            }

            $redirect = \Red_Item::create([
                'url' => $source,
                'action_type' => 'url',
                'action_code' => 301,
                'match_type' => 'url',
                'action_data' => [
                    'url' => $target_url,
                ],
                'group_id' => $group_id,
            ]);

            if ($redirect instanceof \Red_Item) {
                $redirect_id = (int) $redirect->get_id();
            } elseif (is_array($redirect) && !empty($redirect['id'])) {
                $redirect_id = (int) $redirect['id'];
            } else {
                $redirect_id = 0;
            }

            if (!$redirect_id) {
                return false;
            }

            update_post_meta($post_id, self::REDIRECT_ID_META_KEY, $redirect_id);
            return true;
        } catch (\Throwable $e) {
            function_exists(self::SENTRY_EXCEPTION) && \Sentry\captureException($e);
            return false;
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
     * Enable, disable, or delete the redirection associated with a p4_action post.
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
                $this->queue_admin_notice(sprintf(
                    /* translators: 1: post ID, 2: redirect ID */
                    __('Could not find redirection #%2$d for post #%1$d.', 'planet4-master-theme-backend'),
                    $post_id,
                    $redirect_id
                ));
                return;
            }

            if ($action === 'enable') {
                $item->enable();
            } elseif ($action === 'disable') {
                $item->disable();
            } elseif ($action === 'delete') {
                $item->delete();
            }
        } catch (\Throwable $e) {
            function_exists(self::SENTRY_EXCEPTION) && \Sentry\captureException($e);
            $this->queue_admin_notice(sprintf(
                /* translators: %d is a post ID. */
                __('Could not update the redirection for post #%d.', 'planet4-master-theme-backend'),
                $post_id
            ));
        }
    }

    /**
     * Queue a one-time admin notice message for the current user.
     */
    private function queue_admin_notice(string $message): void
    {
        set_transient(self::NOTICE_TRANSIENT_PREFIX . get_current_user_id(), $message, MINUTE_IN_SECONDS);
    }

    /**
     * Render and clear any queued admin notice for the current user.
     */
    public function render_queued_admin_notice(): void
    {
        $key = self::NOTICE_TRANSIENT_PREFIX . get_current_user_id();
        $message = get_transient($key);

        if (!$message) {
            return;
        }

        delete_transient($key);

        printf(
            '<div class="notice notice-warning is-dismissible"><p>%s</p></div>',
            esc_html($message)
        );
    }

    /**
     * Parse Open Graph meta tags out of an HTML string.
     */
    private function parse_og_tags(string $html, string $source_url): array
    {
        $og = [
           'title' => '',
           'description' => '',
           'image' => '',
           'url' => $source_url,
        ];

        try {
            $dom = $this->load_html_dom($html);

            if ($dom === null) {
                 return $og;
            }


            $og = $this->extract_og_from_dom($dom, $og);
            $og = $this->apply_title_fallback($dom, $og);

            return $this->sanitize_og_data($og);
        } catch (\Throwable $e) {
            function_exists('\Sentry\captureException') && \Sentry\captureException($e);
            return $og;
        }
    }

    /**
     * Load a new DOM Document.
     */
    private function load_html_dom(string $html): ?\DOMDocument
    {
        if (trim($html) === '') {
            return null;
        }

        $dom = new \DOMDocument();

        $previous_setting = libxml_use_internal_errors(true);
        $loaded = $dom->loadHTML($html, LIBXML_NOERROR | LIBXML_NOWARNING);
        libxml_clear_errors();
        libxml_use_internal_errors($previous_setting);

        return $loaded ? $dom : null;
    }

    /**
     * Extract the Open Graph meta tags using DOM Document.
     */
    private function extract_og_from_dom(\DOMDocument $dom, array $og): array
    {
        $metas = $dom->getElementsByTagName('meta');

        foreach ($metas as $meta) {
            $property = $meta->getAttribute('property') ?: $meta->getAttribute('name');
            $content = $meta->getAttribute('content');

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
                default:
                    break;
            }
        }

        return $og;
    }

    /**
     * Apply a fallback title if no one was extracted from the Open Graph data.
     */
    private function apply_title_fallback(\DOMDocument $dom, array $og): array
    {
        if (!empty($og['title'])) {
            return $og;
        }

        $titles = $dom->getElementsByTagName('title');

        if ($titles->length > 0) {
            $og['title'] = $titles->item(0)->textContent;
        }

        return $og;
    }

    /**
     * Sanitize the Open Graph data.
     */
    private function sanitize_og_data(array $og): array
    {
        $og['title'] = sanitize_text_field($og['title']);
        $og['description'] = sanitize_text_field($og['description']);
        $og['url'] = esc_url_raw($og['url']);
        $og['image'] = esc_url_raw($og['image']);

        return $og;
    }

    /**
     * Load a remote image and set it as the post's featured image.
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
            function_exists(self::SENTRY_EXCEPTION) && \Sentry\captureException($e);
        }
    }

    /**
     * Render the admin page markup.
     */
    public function admin_page_display(): void
    {
        // phpcs:disable Generic.Files.LineLength.MaxExceeded ?>
        <div class="wrap">
            <h2><?php echo esc_html__('Import Action', 'planet4-master-theme-backend'); ?></h2>

            <?php $this->maybe_render_notice(); ?>

            <form method="post">
                <?php wp_nonce_field(self::NONCE_ACTION); ?>
                <table class="form-table">
                    <tr>
                        <th><?php echo esc_html__('External URL', 'planet4-master-theme-backend'); ?></th>
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
                                    'Verify the results on submission.
                                    This feature has mostly been tested with Hubspot landing pages.',
                                    'planet4-master-theme-backend'
                                ); ?>
                            </p>
                        </td>
                    </tr>
                </table>
                <?php submit_button(__('Import Action', 'planet4-master-theme-backend')); ?>
            </form>
        </div>
        <?php // phpcs:enable Generic.Files.LineLength.MaxExceeded
    }

    /**
     * Print a success/error admin notice based on the redirect query args.
     */
    private function maybe_render_notice(): void
    {
        if (!empty($_GET[self::REDIRECT_ARG_ERROR])) {
            $message = sanitize_text_field(wp_unslash($_GET[self::REDIRECT_ARG_ERROR]));

            if (!empty($_GET[self::REDIRECT_ARG_EXISTING])) {
                $existing_id = absint($_GET[self::REDIRECT_ARG_EXISTING]);
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

        if (empty($_GET[self::REDIRECT_ARG_SUCCESS])) {
            return;
        }

        $post_id = absint($_GET[self::REDIRECT_ARG_SUCCESS]);
        $edit_url = get_edit_post_link($post_id, 'raw');
        $view_url = get_permalink($post_id);
        printf(
            '<div class="notice notice-success"><p>%s <a href="%s">%s</a> &middot; <a href="%s">%s</a></p></div>',
            esc_html__('Post published.', 'planet4-master-theme-backend'),
            esc_url($view_url),
            esc_html__('View it', 'planet4-master-theme-backend'),
            esc_url($edit_url),
            esc_html__('Edit it', 'planet4-master-theme-backend')
        );

        if (empty($_GET[self::REDIRECT_ARG_WARNING])) {
            return;
        }

        printf(
            '<div class="notice notice-warning"><p>%s</p></div>',
            esc_html__(
                'Note: the redirect to the original URL could not be created.',
                'planet4-master-theme-backend'
            )
        );
    }
}
