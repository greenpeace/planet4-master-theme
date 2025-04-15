<?php

namespace P4\MasterTheme;

use P4\MasterTheme\Post;

/**
 * A data object for Spreadsheet API responses.
 */
class BlockRegisterController
{
    /**
     * BlockRegisterController constructor.
     */
    public function __construct()
    {
        add_action('init', [$this, 'register_custom_blocks']);
    }

    public function register_custom_blocks()
    {
        register_block_type(
            'p4/reading-time',
            [
                'render_callback' => [ Post::class, 'reading_time_block' ],
                'uses_context' => [ 'postId' ],
            ]
        );
        // Block displays related posts using the Query Loop block
        register_block_type(
            'p4/related-posts',
            [
                'attributes' => [
                    'query_attributes' => [
                        'type' => 'object',
                        'default' => [],
                    ],
                ],
                'render_callback' => [ Post::class, 'render_related_posts_block' ],
            ]
        );
        register_block_type(
            'p4/bottom-page-navigation-block',
            [
                'render_callback' => [ Post::class, 'render_navigation_block' ],
            ]
        );
        register_block_type(
            'p4/post-author-name',
            [
                'render_callback' => [$this, 'post_author_name_callback'],
                'uses_context' => [ 'postId' ],
            ]
        );
        // Like the core block but with an appropriate sizes attribute.
        register_block_type(
            'p4/post-featured-image',
            [
                'render_callback' => [$this, 'post_featured_image_callback'],
                'uses_context' => [ 'postId' ],
            ]
        );
        register_block_type(
            'p4/taxonomy-breadcrumb',
            [
                'api_version' => 2,
                'render_callback' => [$this, 'taxonomy_breadcrumb_callback'],
                'uses_context' => ['postId'],
                'attributes' => [
                    'taxonomy' => [
                        'type' => 'string',
                        'default' => 'category',
                    ],
                ],
            ]
        );
    }

    // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter -- register_block_type callback
    private function post_author_name_callback (array $attributes, $content, $block) {
        $author_override = get_post_meta($block->context['postId'], 'p4_author_override', true);
        $post_author_id = get_post_field('post_author', $block->context['postId']);

        $is_override = ! empty($author_override);

        $name = $is_override ? $author_override : get_the_author_meta('display_name', $post_author_id);
        $link = $is_override ? '#' : get_author_posts_url($post_author_id);

        $block_content = $author_override ? $name : "<a href='$link'>$name</a>";

        return "<span class='article-list-item-author'>$block_content</span>";
    }

    // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter -- register_block_type callback
    // phpcs:disable Generic.Files.LineLength.MaxExceeded
    private function post_featured_image_callback (array $attributes, $content, $block) {
        $post_id = $block->context['postId'];
        $post_link = get_permalink($post_id);
        $featured_image = get_the_post_thumbnail(
            $post_id,
            null,
            // For now hard coded sizes to the ones from Articles, as it's the single usage.
            // This can be made a block attribute, or even construct a sizes attr with math based on context.
            // For example, it could already access displayLayout from Query block to know how many columns are
            // being rendered. If it then also knows the flex gap and container width, it should have all needed
            // info to support a large amount of cases.
            [ 'sizes' => '(min-width: 1600px) 389px, (min-width: 1200px) 335px, (min-width: 1000px) 281px, (min-width: 780px) 209px, (min-width: 580px) 516px, calc(100vw - 24px)' ]
        );

        return "<a href='$post_link'>$featured_image</a>";
    }

    private function taxonomy_breadcrumb_callback ($attributes, $block) {
        $post_id = $block->context['postId'] ?? get_the_ID();
        $taxonomy = $attributes['taxonomy'] ?? 'category';

        $terms = get_the_terms($post_id, $taxonomy);
        if (is_wp_error($terms) || empty($terms)) {
            return '';
        }

        $first = $terms[0];
        $term_link = get_term_link($first);

        return sprintf('<div class="wp-block-post-terms"><a href="%s">%s</a></div>', esc_url($term_link), esc_html($first->name));
    }
}
