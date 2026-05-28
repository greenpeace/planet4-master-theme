<?php

namespace P4\MasterTheme;

/**
 * Class MediaCleaner
 */
class MediaCleaner
{
    private string $key = 'planet4_media_cleaner';
    private string $title;
    private int $per_page = 20;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->title = 'Media Cleaner';
        $this->hooks();
    }

    /**
     * Register our setting to WP.
     */
    public function init(): void
    {
        register_setting($this->key, $this->key);
    }

    /**
     * Initiate our hooks
     */
    public function hooks(): void
    {
        add_action('admin_init', [ $this, 'init' ]);
        add_action('admin_menu', [ $this, 'add_options_page' ], 99);
    }

    /**
     * Add menu options page.
     */
    public function add_options_page(): void
    {
        add_submenu_page(
            'planet4_settings_navigation',
            $this->title,
            $this->title,
            'manage_options',
            $this->key,
            [ $this, 'admin_page_display' ]
        );
    }

    /**
     * Admin page markup.
     */
    public function admin_page_display(): void
    {
        $current_page = max(1, (int) ($_GET['paged'] ?? 1));

        $results = $this->get_unused_attachments(
            $current_page,
            $this->per_page
        );

        $total = $results['total'];
        $attachments = $results['items'];

        echo '<div class="wrap">';
        echo '<h1>Media Cleaner</h1>';
        echo '<p>' . esc_html($total) . ' unused attachment(s) found.</p>';

        echo '
        <table class="wp-list-table widefat striped table-view-list media">
            <thead>
                <tr>
                    <th scope="col" class="manage-column column-title column-primary">
                        File
                    </th>
                    <th scope="col" class="manage-column column-author">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody id="the-list">';

        foreach ($attachments as $attachment) {
            $url = esc_url($attachment['url']);
            $img = esc_url($attachment['img']);
            $post_id = (int) $attachment['id'];
            $title = esc_html($attachment['title']);

            $edit_url = esc_url(
                admin_url("post.php?post={$post_id}&action=edit")
            );

            $delete_url = esc_url(
                wp_nonce_url(
                    admin_url("post.php?action=delete&post={$post_id}"),
                    "delete-post_{$post_id}"
                )
            );

            echo "
            <tr id=\"post-{$post_id}\" class=\"author-other status-inherit\">
                <td class=\"title column-title has-row-actions column-primary\">
                    <strong class=\"has-media-icon\">
                        <a href=\"{$edit_url}\">
                            <span class=\"media-icon image-icon\">
                                <img
                                    width=\"60\"
                                    height=\"60\"
                                    src=\"{$img}\"
                                    class=\"attachment-60x60 size-60x60\"
                                    alt=\"\"
                                    loading=\"lazy\"
                                >
                            </span>
                            {$title}
                        </a>
                    </strong>

                    <p class=\"filename\">
                        {$url}
                    </p>

                    <button type=\"button\" class=\"toggle-row\">
                        <span class=\"screen-reader-text\">
                            Show more details
                        </span>
                    </button>
                </td>

                <td class=\"date column-date\">
                    <span class=\"edit\">
                        <a href=\"{$edit_url}\">Edit</a> |
                    </span>

                    <span class=\"delete\">
                        <a
                            href=\"{$delete_url}\"
                            class=\"submitdelete\"
                            onclick=\"return confirm('Delete permanently?');\"
                        >
                            Delete Permanently
                        </a>
                    </span>
                </td>
            </tr>";
        }

        echo '</tbody></table>';

        $pagination = paginate_links([
            'base' => admin_url(
                'admin.php?page=' . $this->key . '%_%'
            ),
            'format' => '&paged=%#%',
            'current' => $current_page,
            'total' => max(1, ceil($total / $this->per_page)),
            'prev_text' => '&laquo;',
            'next_text' => '&raquo;',
        ]);

        if ($pagination) {
            echo '
            <div class="tablenav bottom">
                <div class="tablenav-pages">';
            echo wp_kses_post($pagination);
            echo '
                </div>
            </div>';
        }

        echo '</div>';
    }

    /**
     * Get unused attachments with pagination.
     */
    private function get_unused_attachments(
        int $page = 1,
        int $per_page = 20
    ): array {

        $used_ids = $this->get_used_attachment_ids();

        $query = new \WP_Query([
            'post_type' => 'attachment',
            'post_status' => 'inherit',
            'posts_per_page' => $per_page,
            'paged' => $page,
            'post__not_in' => $used_ids,
            'fields' => 'ids',
            'orderby' => 'ID',
            'order' => 'DESC',
        ]);

        $items = array_map(function ($attachment_id) {

            return [
                'id' => $attachment_id,
                'title' => get_the_title($attachment_id),
                'img' => wp_get_attachment_thumb_url($attachment_id),
                'url' => wp_get_attachment_url($attachment_id),
                'mime_type' => get_post_mime_type($attachment_id),
            ];
        }, $query->posts);

        return [
            'total' => (int) $query->found_posts,
            'items' => $items,
        ];
    }

    /**
     * Get all used attachment IDs.
     */
    private function get_used_attachment_ids(): array
    {
        global $wpdb;

        $used_ids = [];

        // Featured images
        $thumbnail_ids = $wpdb->get_col("
            SELECT meta_value
            FROM {$wpdb->postmeta}
            WHERE meta_key = '_thumbnail_id'
        ");

        $used_ids = array_merge($used_ids, $thumbnail_ids);

        // Parse all post content
        $posts = get_posts([
            'post_type' => 'any',
            'post_status' => 'any',
            'posts_per_page' => -1,
            'fields' => 'ids',
        ]);

        foreach ($posts as $post_id) {
            $content = get_post_field(
                'post_content',
                $post_id
            );

            // Gutenberg blocks
            $blocks = parse_blocks($content);

            $this->extract_attachment_ids_from_blocks(
                $blocks,
                $used_ids
            );

            // Classic editor images
            preg_match_all(
                '/wp-image-([0-9]+)/',
                $content,
                $matches
            );

            if (empty($matches[1])) {
                continue;
            }

            $used_ids = array_merge(
                $used_ids,
                $matches[1]
            );
        }

        $used_ids = array_unique(
            array_map('intval', $used_ids)
        );

        return array_filter($used_ids);
    }

    /**
     * Extract attachment IDs from Gutenberg blocks.
     */
    private function extract_attachment_ids_from_blocks(
        array $blocks,
        array &$used_ids
    ): void {

        foreach ($blocks as $block) {
            if (!empty($block['attrs'])) {
                // Core image block
                if (!empty($block['attrs']['id'])) {
                    $used_ids[] = (int) $block['attrs']['id'];
                }

                // Gallery block
                if (
                    !empty($block['attrs']['ids']) &&
                    is_array($block['attrs']['ids'])
                ) {
                    $used_ids = array_merge(
                        $used_ids,
                        $block['attrs']['ids']
                    );
                }

                // Cover block
                if (!empty($block['attrs']['backgroundId'])) {
                    $used_ids[] = (int) $block['attrs']['backgroundId'];
                }
            }

            if (empty($block['innerBlocks'])) {
                continue;
            }

            $this->extract_attachment_ids_from_blocks(
                $block['innerBlocks'],
                $used_ids
            );
        }
    }
}
