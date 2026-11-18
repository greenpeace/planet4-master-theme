<?php

namespace P4\MasterTheme;

use WP_Post_Type;

/**
 * Class ShortcakeAllowed
 *
 * Admin page listing the Shortcake (Shortcode UI) blocks allowed for each post type.
 */
class ShortcakeAllowed
{
    /**
     * Admin page slug.
     */
    private const SLUG = 'planet4_shortcake_allowed';

    /**
     * Constructor
     */
    public function __construct()
    {
        add_action('admin_menu', [ $this, 'add_admin_page' ], 99);
    }

    /**
     * Add the page to the Planet 4 menu.
     */
    public function add_admin_page(): void
    {
        add_submenu_page(
            'planet4_settings_navigation',
            __('Shortcake allowed', 'planet4-master-theme-backend'),
            __('Shortcake allowed', 'planet4-master-theme-backend'),
            'manage_options',
            self::SLUG,
            [ $this, 'admin_page_display' ]
        );
    }

    /**
     * Post types with an editor, where shortcodes can be inserted.
     *
     * @return WP_Post_Type[] Post type objects, keyed by name.
     */
    private function get_post_types(): array
    {
        $post_types = get_post_types([ 'show_ui' => true ], 'objects');

        return array_filter(
            $post_types,
            fn (WP_Post_Type $post_type) => post_type_supports($post_type->name, 'editor')
        );
    }

    /**
     * Whether a shortcode UI is available for a post type.
     * Same rule as Shortcode UI: no `post_type` argument means all post types.
     *
     * @param array  $args Shortcode UI arguments.
     * @param string $post_type The post type name.
     */
    private function is_allowed(array $args, string $post_type): bool
    {
        $allowed_post_types = (array) ( $args['post_type'] ?? [] );

        return empty($allowed_post_types) || in_array($post_type, $allowed_post_types, true);
    }

    /**
     * Count the posts using a shortcode, per post type.
     * Revisions, auto-drafts and trashed posts are not counted.
     *
     * @param string $shortcode_tag The shortcode tag.
     *
     * @return int[] Number of posts, keyed by post type.
     */
    private function get_usage_counts(string $shortcode_tag): array
    {
        global $wpdb;

        // Match the exact tag, so `[shortcake_hero_set` doesn't count `[shortcake_hero_set_donation`.
        $pattern = '\\[' . preg_quote($shortcode_tag) . '([[:space:]]|\\]|/)';

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT post_type, COUNT(ID) AS total FROM {$wpdb->posts}
                WHERE post_status IN ('publish', 'draft', 'pending', 'private', 'future')
                AND post_content LIKE %s
                AND post_content REGEXP %s
                GROUP BY post_type",
                '%' . $wpdb->esc_like('[' . $shortcode_tag) . '%',
                $pattern
            )
        );

        return wp_list_pluck($results, 'total', 'post_type');
    }

    /**
     * Number of posts using the shortcode, linked to the post list filtered by that shortcode.
     *
     * @param string $shortcode_tag The shortcode tag.
     * @param string $post_type The post type name.
     * @param int    $count Number of posts.
     *
     * @return string Escaped HTML.
     */
    private function usage_count_link(string $shortcode_tag, string $post_type, int $count): string
    {
        $url = add_query_arg(
            [
                'post_type' => $post_type,
                's' => $shortcode_tag,
            ],
            admin_url('edit.php')
        );

        return '<a class="usage-count" href="' . esc_url($url) . '">'
            // translators: %d = Number of posts using the shortcode.
            . esc_html(sprintf(_n('%d ' . $post_type, '%d ' . $post_type . 's', $count, 'planet4-master-theme-backend'), $count))
            . '</a>';
    }

    /**
     * Admin page markup.
     */
    public function admin_page_display(): void
    {
        echo '<div class="wrap blocks-allowed">';
        echo '<h1>' . esc_html__('Shortcake allowed', 'planet4-master-theme-backend') . '</h1>';

        if (!class_exists('Shortcode_UI')) {
            echo '<div class="notice notice-warning inline"><p>' . esc_html__(
                'The Shortcode UI (Shortcake) plugin is not active.',
                'planet4-master-theme-backend'
            ) . '</p></div></div>';
            return;
        }

        $shortcodes = \Shortcode_UI::get_instance()->get_shortcodes();
        ksort($shortcodes);
        $post_types = $this->get_post_types();

        echo '<p>' . esc_html__(
            'Shortcake blocks that can be inserted in the editor, for each post type with an editor, '
            . 'and the number of posts using them (published, draft, pending, private and scheduled).',
            'planet4-master-theme-backend'
        ) . '</p>';

        if (empty($shortcodes)) {
            echo '<div class="notice notice-info inline"><p>' . esc_html__(
                'No Shortcake blocks are registered.',
                'planet4-master-theme-backend'
            ) . '</p></div></div>';
            return;
        }

        BlocksAllowed::render_styles();

        echo '<div class="blocks-allowed-table-wrapper"><table class="widefat striped">';

        // Header.
        echo '<thead><tr>';
        echo '<th scope="col">' . esc_html__('Shortcake block', 'planet4-master-theme-backend') . '</th>';
        foreach ($post_types as $name => $post_type) {
            echo '<th scope="col" class="post-type">' . esc_html($post_type->labels->singular_name)
                . '<br><code>' . esc_html($name) . '</code></th>';
        }
        echo '</tr></thead>';

        // Totals.
        echo '<tbody><tr class="totals"><th scope="row">'
            . esc_html__('Total allowed', 'planet4-master-theme-backend') . '</th>';
        foreach (array_keys($post_types) as $post_type) {
            $total = count(array_filter($shortcodes, fn (array $args) => $this->is_allowed($args, $post_type)));
            echo '<td class="post-type">' . esc_html((string) $total) . '</td>';
        }
        echo '</tr>';

        // One row per shortcode.
        foreach ($shortcodes as $shortcode_tag => $args) {
            echo '<tr><th scope="row">';
            if (!empty($args['label'])) {
                echo esc_html(wp_strip_all_tags($args['label'])) . '<br>';
            }
            echo '<code>' . esc_html($shortcode_tag) . '</code>';
            echo '</th>';

            $usage_counts = $this->get_usage_counts($shortcode_tag);
            foreach (array_keys($post_types) as $post_type) {
                $is_allowed = $this->is_allowed($args, $post_type);
                $count = (int) ( $usage_counts[ $post_type ] ?? 0 );

                BlocksAllowed::render_allowed_cell(
                    $is_allowed,
                    $is_allowed ? $this->usage_count_link($shortcode_tag, $post_type, $count) : ''
                );
            }
            echo '</tr>';
        }

        echo '</tbody></table></div></div>';
    }
}
