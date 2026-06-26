<?php

namespace P4\MasterTheme\ActionPage;

use DOMDocument;
use DOMXPath;
use Red_Item;
use Red_Group;

/**
 * Class P4\MasterTheme\ActionPage\ImportAction
 */
class ImportAction
{
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

        add_action('admin_menu', function (): void {
            add_submenu_page(
                'edit.php?post_type=p4_action',
                __('Import Action', 'planet4-master-theme-backend'),
                __('Import Action', 'planet4-master-theme-backend'),
                'manage_options',
                'import-action',
                [ $this, 'import_action_page' ]
            );
        });
        add_action('admin_init', [$this, 'handle_import_action']);
    }

    public function import_action_page(): void
    {
        ?>
        <div class="wrap">
            <h1><?php echo __('Import Action', 'planet4-master-theme-backend'); ?></h1>

            <form method="post">
                <?php wp_nonce_field('import_action'); ?>

                <table class="form-table">
                    <tr>
                        <th><?php echo __('URL', 'planet4-master-theme-backend'); ?></th>
                        <td>
                            <input
                                type="url"
                                name="import_url"
                                class="regular-text"
                                required
                                placeholder="https://example.com/article"
                            >
                        </td>
                    </tr>
                </table>

                <?php submit_button(__('Import Action', 'planet4-master-theme-backend')); ?>
            </form>
        </div>
        <?php
    }

    public function handle_import_action(): void
    {
        if (
            empty($_POST['import_url']) ||
            !isset($_POST['_wpnonce'])
        ) {
            return;
        }

        if (!wp_verify_nonce($_POST['_wpnonce'], 'import_action')) {
            wp_die('Security check failed');
        }

        $url = esc_url_raw(trim($_POST['import_url']));

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            wp_die('Invalid URL.');
        }

        $post_id = $this->action_import_from_url($url);

        if (is_wp_error($post_id)) {
            wp_die($post_id->get_error_message());
        }

        wp_safe_redirect(
            admin_url('edit.php?post_type=p4_action')
        );
        exit;
    }

    public function action_import_from_url(string $url): int|WP_Error
    {
        $response = wp_remote_get($url, [
            'timeout' => 20,
            'user-agent' => 'WordPress Action Importer',
        ]);

        if (is_wp_error($response)) {
            return $response;
        }

        $html = wp_remote_retrieve_body($response);

        if (!$html) {
            return new WP_Error(
                'fetch_failed',
                'Could not fetch URL.'
            );
        }

        $og = $this->action_extract_og_data($html);

        if (
            empty($og['title']) ||
            empty($og['description']) ||
            empty($og['image'])
        ) {
            return new WP_Error(
                'missing_og',
                'Required OpenGraph metadata missing.'
            );
        }

        return $this->action_create_post(
            $url,
            $og['title'],
            $og['description'],
            $og['image']
        );
    }

    public function action_extract_og_data(string $html): array
    {
        libxml_use_internal_errors(true);

        $dom = new DOMDocument();
        $dom->loadHTML($html);

        $xpath = new DOMXPath($dom);

        $meta = [];

        foreach ($xpath->query('//meta[@property]') as $node) {
            $property = $node->getAttribute('property');
            $content = $node->getAttribute('content');

            $meta[$property] = $content;
        }

        return [
            'title' => $meta['og:title'] ?? '',
            'description' => $meta['og:description'] ?? '',
            'image' => $meta['og:image'] ?? '',
        ];
    }

    public function action_create_post(
        string $original_url,
        string $title,
        string $description,
        string $image_url
    ): int|WP_Error {

        $post_id = wp_insert_post([
            'post_type' => 'p4_action',
            'post_title' => wp_strip_all_tags($title),
            'post_content' => wp_kses_post($description),
            'post_excerpt' => wp_strip_all_tags($description),
            'post_status' => 'publish',
        ]);

        if (is_wp_error($post_id)) {
            return $post_id;
        }

        $this->action_attach_featured_image(
            $image_url,
            $post_id
        );

        update_post_meta(
            $post_id,
            '_source_url',
            $original_url
        );

        $this->action_create_redirection(
            $post_id,
            $original_url
        );

        $this->action_sync_elasticpress($post_id);

        return $post_id;
    }

    public function action_attach_featured_image(
        string $image_url,
        int $post_id
    ): void {

        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';

        $attachment_id = media_sideload_image(
            $image_url,
            $post_id,
            null,
            'id'
        );

        if (is_wp_error($attachment_id)) {
            return;
        }

        set_post_thumbnail(
            $post_id,
            $attachment_id
        );
    }

    /**
     * Adds a new redirections group with the name "Actions" if it does not exist yet.
     * Returns the group ID.
     */
    public function create_new_group(): int
    {
        global $wpdb;

        $group_name = 'Actions';
        $table_name = 'redirection_groups';
        $table = $wpdb->prefix . $table_name;

        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM $table WHERE name = %s",
            $group_name
        ));

        if ($existing) {
            return (int) $existing;
        }

        if (!class_exists('Red_Group')) {
            return 1; // Return the default group ID
        }

        $group = Red_Group::create($group_name, 1, true);

        if ($group instanceof Red_Group) {
            return (int) $group->get_id();
        }

        return 1; // Return the default group ID
    }

    public function action_create_redirection(
        int $post_id,
        string $target_url
    ): void {

        $group_id = $this->create_new_group();

        if (!class_exists('Red_Item')) {
            return;
        }

        $source = wp_parse_url(
            get_permalink($post_id),
            PHP_URL_PATH
        );

        Red_Item::create([
            'url' => $source,
            'action_type' => 'url',
            'action_code' => 301,
            'match_type' => 'url',
            'action_data' => [
                'url' => $target_url,
            ],
            'group_id' => $group_id,
        ]);
    }

    public function action_sync_elasticpress(int $post_id): void
    {
        if (!class_exists('\ElasticPress\Indexables')) {
            return;
        }

        try {
            $indexable =
                \ElasticPress\Indexables::factory()
                    ->get('post');

            $indexable->sync_manager
                ->add_to_queue($post_id);
        } catch (Exception $e) {
            function_exists('\Sentry\captureException') && \Sentry\captureException($e);
        }
    }
}
