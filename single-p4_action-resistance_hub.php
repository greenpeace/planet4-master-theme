<?php

/**
 * Template Name: Resistance Hub Campaign
 * Template Post Type: p4_action
 * The template for displaying Resistance Hub campaign actions.
 *
 * @package  WordPress
 * @subpackage  Timber
 * @since    Timber 0.1
 */

$features = get_option('planet4_features');

// Enqueue Resistance Hub styles and script only for this template
add_action('wp_enqueue_scripts', function (): void {
    $template = get_page_template();
    if (!$template) {
        return;
    }

    $template_data = get_file_data($template, array('Template Name' => 'Template Name'));
    if ($template_data['Template Name'] !== 'Resistance Hub Campaign') {
        return;
    }

    wp_enqueue_style(
        'p4-action-resistance-hub-styles',
        get_template_directory_uri() . '/assets/build/resistance-hub-campaign-styles.css',
        [],
        true,
    );

    wp_enqueue_script(
        'p4-action-resistance-hub-script',
        get_template_directory_uri() . '/assets/build/resistance-hub-campaign.js',
        ['wp-i18n'],
        true,
    );
});

add_filter(
    'render_block',
    function ($block_content, $block) {
        global $post;
        $post_id = $post->ID;

        if (!isset($block['blockName'])) {
            return $block_content;
        }

        $block_name = $block['blockName'];

        if (
            $block_name === "planet4-blocks/take-action-boxout" &&
            isset($block['attrs']['take_action_page'])
        ) {
            $post_id = (int) $block['attrs']['take_action_page'];
        }

        if (
            $block_name === 'planet4-blocks/action-button-text' ||
            $block_name === "planet4-blocks/take-action-boxout"
        ) {
            $actions_task_type_value = get_post_meta($post_id, 'actions_task_type', true);
            $actions_deadline_value = get_post_meta($post_id, 'actions_deadline', true);

            if ($actions_task_type_value && $actions_deadline_value) {
                $block_content = preg_replace(
                    '/(<a[^>]*class="[^"]*btn[^"]*"[^>]*)/',
                    // phpcs:disable Generic.Files.LineLength.MaxExceeded
                    '$1 data-tasktype="' . esc_attr($actions_task_type_value) . '"' . ' data-deadline="' . esc_attr($actions_deadline_value) . '"',
                    $block_content,
                    1
                );
            }
        }
        return $block_content;
    },
    10,
    2
);

include_once get_template_directory() . '/single-p4_action.php';
