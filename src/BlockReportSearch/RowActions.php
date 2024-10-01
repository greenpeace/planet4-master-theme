<?php

/**
 * Display post actions in table
 */

namespace P4\MasterTheme\BlockReportSearch;

/**
 * Row actions
 */
class RowActions
{
    /**
     * Add action links to a row
     *
     * @param array  $item              Item.
     * @param string $column_name       Current column name.
     * @param string $primary           Primary column name.
     * @param string $potential_post_id Primary column name.
     *
	 * phpcs:disable WordPress.WP.I18n.TextDomainMismatch
     */
    public function get_post_actions(
        array $item,
        string $column_name,
        string $primary,
        string $potential_post_id
    ): array {
        if ($column_name !== $primary) {
            return [];
        }

        $id = empty($item['post_id']) ? (int) $potential_post_id : (int) $item['post_id'];
        $title = $item['post_title'];
        $actions = [];

        $actions['edit'] = sprintf(
            '<a href="%s" aria-label="%s">%s</a>',
            get_edit_post_link($id),
            /* translators: %s: Post title. */
            esc_attr(sprintf(__('Edit &#8220;%s&#8221;', 'default'), $title)),
            __('Edit', 'default')
        );

        if (in_array($item['post_status'], [ 'pending', 'draft', 'future' ], true)) {
            $preview_link = get_preview_post_link($id);
            $actions['view'] = sprintf(
                '<a href="%s" rel="bookmark" aria-label="%s">%s</a>',
                esc_url($preview_link),
                /* translators: %s: Post title. */
                esc_attr(sprintf(__('Preview &#8220;%s&#8221;', 'default'), $title)),
                __('Preview', 'default')
            );
        } elseif ('trash' !== $item['post_status']) {
            $actions['view'] = sprintf(
                '<a href="%s" rel="bookmark" aria-label="%s">%s</a>',
                get_permalink($id),
                /* translators: %s: Post title. */
                esc_attr(sprintf(__('View &#8220;%s&#8221;', 'default'), $title)),
                __('View', 'default')
            );
        }

        $actions['clone'] = '<a href="' . duplicate_post_get_clone_post_link($id, 'display', false) .
            '" aria-label="' . esc_attr(
            /* translators: %s: Post title. */
                sprintf(__('Clone &#8220;%s&#8221;', 'duplicate-post'), $title)
            ) . '">' .
            esc_html_x('Clone', 'verb', 'duplicate-post') . '</a>';

        $actions['edit_as_new_draft'] = '<a href="' . duplicate_post_get_clone_post_link($id) .
            '" aria-label="' . esc_attr(
            /* translators: %s: Post title. */
                sprintf(__('New draft of &#8220;%s&#8221;', 'duplicate-post'), $title)
            ) . '">' .
            esc_html__('New Draft', 'duplicate-post') .
            '</a>';

        return $actions;
    }
}
