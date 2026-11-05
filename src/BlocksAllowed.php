<?php

namespace P4\MasterTheme;

use WP_Block_Editor_Context;
use WP_Block_Type_Registry;
use WP_Post;
use WP_Post_Type;

/**
 * Class BlocksAllowed
 *
 * Admin page listing the blocks allowed in the editor for each post type.
 */
class BlocksAllowed
{
    /**
     * Admin page slug.
     */
    private const SLUG = 'planet4_blocks_allowed';

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
            __('Blocks allowed', 'planet4-master-theme-backend'),
            __('Blocks allowed', 'planet4-master-theme-backend'),
            'manage_options',
            self::SLUG,
            [ $this, 'admin_page_display' ]
        );
    }

    /**
     * Post types that use the block editor.
     *
     * @return WP_Post_Type[] Post type objects, keyed by name.
     */
    private function get_post_types(): array
    {
        $post_types = get_post_types([ 'show_ui' => true ], 'objects');

        return array_filter(
            $post_types,
            fn (WP_Post_Type $post_type) => use_block_editor_for_post_type($post_type->name)
        );
    }

    /**
     * Allowed blocks for a post type, as the editor would compute them.
     *
     * @param string $post_type The post type name.
     *
     * @return bool|string[] True if all blocks are allowed, false if none, or the list of allowed block names.
     */
    private function get_allowed_blocks(string $post_type)
    {
        $context = new WP_Block_Editor_Context([
            'post' => new WP_Post((object) [
                'ID' => 0,
                'post_type' => $post_type,
            ]),
        ]);

        return get_allowed_block_types($context);
    }

    /**
     * Admin page markup.
     */
    public function admin_page_display(): void
    {
        $post_types = $this->get_post_types();
        $registered_blocks = WP_Block_Type_Registry::get_instance()->get_all_registered();

        $allowed = [];
        foreach (array_keys($post_types) as $post_type) {
            $allowed[ $post_type ] = $this->get_allowed_blocks($post_type);
        }

        // Rows: every block allowed in at least one post type.
        // If a post type allows all blocks, list all blocks registered on the server.
        $block_names = [];
        foreach ($allowed as $allowed_blocks) {
            if (true === $allowed_blocks) {
                $block_names = array_merge($block_names, array_keys($registered_blocks));
            } elseif (is_array($allowed_blocks)) {
                $block_names = array_merge($block_names, $allowed_blocks);
            }
        }
        $block_names = array_unique($block_names);
        sort($block_names);

        echo '<div class="wrap blocks-allowed">';
        echo '<h1>' . esc_html__('Blocks allowed', 'planet4-master-theme-backend') . '</h1>';
        echo '<p>' . esc_html__(
            'Blocks that can be inserted in the editor, for each post type using the block editor.',
            'planet4-master-theme-backend'
        ) . '</p>';

        if (in_array(true, $allowed, true)) {
            echo '<div class="notice notice-info inline"><p>' . esc_html__(
                'Some post types allow all blocks (for example when the "Allow all blocks" feature is enabled).',
                'planet4-master-theme-backend'
            ) . '</p></div>';
        }

        self::render_styles();

        echo '<div class="blocks-allowed-table-wrapper"><table class="widefat striped">';

        // Header.
        echo '<thead><tr>';
        echo '<th scope="col">' . esc_html__('Block', 'planet4-master-theme-backend') . '</th>';
        foreach ($post_types as $name => $post_type) {
            echo '<th scope="col" class="post-type">' . esc_html($post_type->labels->singular_name)
                . '<br><code>' . esc_html($name) . '</code></th>';
        }
        echo '</tr></thead>';

        // Totals.
        echo '<tbody><tr class="totals"><th scope="row">'
            . esc_html__('Total allowed', 'planet4-master-theme-backend') . '</th>';
        foreach (array_keys($post_types) as $post_type) {
            $allowed_blocks = $allowed[ $post_type ];
            if (true === $allowed_blocks) {
                $total = __('All', 'planet4-master-theme-backend');
            } else {
                $total = is_array($allowed_blocks) ? count(array_unique($allowed_blocks)) : 0;
            }
            echo '<td class="post-type">' . esc_html((string) $total) . '</td>';
        }
        echo '</tr>';

        // One row per block.
        foreach ($block_names as $block_name) {
            $block_type = $registered_blocks[ $block_name ] ?? null;

            echo '<tr><th scope="row">';
            if ($block_type && $block_type->title) {
                echo esc_html($block_type->title) . '<br>';
            }
            echo '<code>' . esc_html($block_name) . '</code>';
            if (!$block_type) {
                echo '<br><em>' . esc_html__('Not registered on the server', 'planet4-master-theme-backend') . '</em>';
            }
            echo '</th>';

            foreach (array_keys($post_types) as $post_type) {
                $allowed_blocks = $allowed[ $post_type ];
                $is_allowed = true === $allowed_blocks
                    || ( is_array($allowed_blocks) && in_array($block_name, $allowed_blocks, true) );

                self::render_allowed_cell($is_allowed);
            }
            echo '</tr>';
        }

        echo '</tbody></table></div></div>';
    }

    /**
     * Table cell showing whether an item is allowed.
     *
     * @param bool   $is_allowed Whether the item is allowed.
     * @param string $extra Escaped HTML to show after the "allowed" icon.
     */
    public static function render_allowed_cell(bool $is_allowed, string $extra = ''): void
    {
        echo '<td class="post-type">';
        if ($is_allowed) {
            echo '<span class="dashicons dashicons-yes allowed" aria-hidden="true"></span>'
                . '<span class="screen-reader-text">'
                . esc_html__('Allowed', 'planet4-master-theme-backend') . '</span>'
                . $extra; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by the caller.
        } else {
            echo '<span class="dashicons dashicons-minus" aria-hidden="true"></span>'
                . '<span class="screen-reader-text">'
                . esc_html__('Not allowed', 'planet4-master-theme-backend') . '</span>';
        }
        echo '</td>';
    }

    /**
     * Inline styles for the table, scoped to a `.blocks-allowed` wrapper.
     */
    public static function render_styles(): void
    {
        echo '<style>
            .blocks-allowed-table-wrapper { overflow-x: auto; margin-top: 16px; }
            .blocks-allowed table { width: auto; min-width: 100%; }
            .blocks-allowed .post-type { text-align: center; white-space: nowrap; }
            .blocks-allowed tbody th { font-weight: normal; }
            .blocks-allowed tr.totals { font-weight: 600; }
            .blocks-allowed tr.totals th { font-weight: 600; }
            .blocks-allowed .dashicons.allowed { color: #008a20; }
            .blocks-allowed .dashicons-minus { color: #a7aaad; }
            .blocks-allowed .usage-count { display: block; font-size: 12px; }
        </style>';
    }
}
