<?php

namespace P4\MasterTheme;

use P4\MasterTheme\Migrations\Utils\ShortcakeMigration;
use WP_Query;

/**
 * Class ShortcakeMigrated
 *
 * Admin page listing the posts where Shortcake blocks were migrated to Planet 4 blocks.
 */
class ShortcakeMigrated
{
    /**
     * Admin page slug.
     */
    private const SLUG = 'planet4_shortcake_migrated';

    /**
     * Number of posts per page.
     */
    private const PER_PAGE = 50;

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
            __('Shortcake migrated', 'planet4-master-theme-backend'),
            __('Shortcake migrated', 'planet4-master-theme-backend'),
            'manage_options',
            self::SLUG,
            [ $this, 'admin_page_display' ]
        );
    }

    /**
     * View, Edit and Last revision links of a post.
     * The last revision is the one saved by the migration, unless the post was edited since.
     *
     * @param \WP_Post    $post The post.
     * @param string|null $edit_link The post edit link.
     * @return string Escaped HTML.
     */
    private function row_actions(\WP_Post $post, ?string $edit_link): string
    {
        $links = [
            '<a href="' . esc_url(get_permalink($post)) . '" target="_blank" rel="noopener noreferrer">'
                . esc_html__('View', 'planet4-master-theme-backend') . '</a>',
        ];

        if ($edit_link) {
            $links[] = '<a href="' . esc_url($edit_link) . '">'
                . esc_html__('Edit', 'planet4-master-theme-backend') . '</a>';
        }

        $revisions_url = wp_get_post_revisions_url($post);
        if ($revisions_url) {
            $links[] = '<a href="' . esc_url($revisions_url) . '">'
                . esc_html__('Last revision', 'planet4-master-theme-backend') . '</a>';
        }

        return implode(' | ', $links);
    }

    /**
     * Admin page markup.
     */
    public function admin_page_display(): void
    {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read only pagination.
        $paged = max(1, (int) ( $_GET['paged'] ?? 1 ));

        $query = new WP_Query([
            'post_type' => 'any',
            'post_status' => 'any',
            'meta_key' => ShortcakeMigration::META_KEY, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_query_meta_key
            'meta_compare' => 'EXISTS',
            'orderby' => 'ID',
            'order' => 'ASC',
            'posts_per_page' => self::PER_PAGE,
            'paged' => $paged,
        ]);

        echo '<div class="wrap blocks-allowed">';
        echo '<h1>' . esc_html__('Shortcake migrated', 'planet4-master-theme-backend') . '</h1>';
        echo '<p>' . sprintf(
            // translators: %s = The post meta key.
            esc_html__('List of pages where Shortcake blocks were migrated to Planet 4 blocks,The list display from the %s post meta.', 'planet4-master-theme-backend'),
            '<code>' . esc_html(ShortcakeMigration::META_KEY) . '</code>'
        ) . '</p>';

        if (!$query->have_posts()) {
            echo '<div class="notice notice-info inline"><p>' . esc_html__(
                'No posts have been migrated yet.',
                'planet4-master-theme-backend'
            ) . '</p></div></div>';
            return;
        }

        BlocksAllowed::render_styles();

        echo '<p><strong>' . esc_html(sprintf(
            // translators: %d = Number of posts.
            _n('%d post', '%d posts', $query->found_posts, 'planet4-master-theme-backend'),
            $query->found_posts
        )) . '</strong></p>';

        echo '<div class="blocks-allowed-table-wrapper"><table class="widefat striped">';
        echo '<thead><tr>';
        echo '<th scope="col">' . esc_html__('Page ID', 'planet4-master-theme-backend') . '</th>';
        echo '<th scope="col">' . esc_html__('Page name', 'planet4-master-theme-backend') . '</th>';
        echo '<th scope="col">' . esc_html__('Post type', 'planet4-master-theme-backend') . '</th>';
        echo '<th scope="col">' . esc_html__('Status', 'planet4-master-theme-backend') . '</th>';
        echo '<th scope="col">' . esc_html(ShortcakeMigration::META_KEY) . '</th>';
        echo '</tr></thead><tbody>';

        foreach ($query->posts as $post) {
            $title = get_the_title($post) ?: __('(no title)', 'planet4-master-theme-backend');
            $edit_link = get_edit_post_link($post);
            $tags = array_filter(explode(',', (string) get_post_meta($post->ID, ShortcakeMigration::META_KEY, true)));

            echo '<tr><td>' . esc_html((string) $post->ID) . '</td>';
            echo '<th scope="row">';
            echo $edit_link
                ? '<a href="' . esc_url($edit_link) . '"><strong>' . esc_html($title) . '</strong></a>'
                : '<strong>' . esc_html($title) . '</strong>';
            echo '<br>' . $this->row_actions($post, $edit_link); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in row_actions().
            echo '</th>';
            echo '<td><code>' . esc_html($post->post_type) . '</code></td>';
            echo '<td>' . esc_html(get_post_status_object($post->post_status)->label ?? $post->post_status) . '</td>';
            echo '<td><code>' . esc_html(implode(',', $tags)) . '</code></td>';
            echo '</tr>';
        }

        echo '</tbody></table></div>';

        $pagination = paginate_links([
            'base' => add_query_arg('paged', '%#%'),
            'format' => '',
            'current' => $paged,
            'total' => $query->max_num_pages,
        ]);
        if ($pagination) {
            echo '<div class="tablenav"><div class="tablenav-pages">' . wp_kses_post($pagination) . '</div></div>';
        }

        echo '</div>';
    }
}
