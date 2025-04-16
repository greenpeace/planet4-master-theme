<?php

namespace P4\MasterTheme;

/**
 * A class to control custom blocks registration.
 */
class BlockRegisterController
{
    /**
     * BlockRegisterController constructor.
     * Registers custom blocks on WordPress 'init' action.
     */
    public function __construct()
    {
        add_action('init', [$this, 'register_custom_blocks']);
    }

    /**
     * Registers all custom blocks used by the theme.
     */
    public function register_custom_blocks(): void
    {
        // Register a block that displays estimated reading time for a post.
        register_block_type(
            'p4/reading-time',
            [
                'render_callback' => [ Post::class, 'reading_time_block' ],
                'uses_context' => [ 'postId' ],
            ]
        );

        // Register a block that displays related posts using Query Loop.
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

        // Register a block for displaying navigation links at the bottom of the page.
        register_block_type(
            'p4/bottom-page-navigation-block',
            [
                'render_callback' => [ Post::class, 'render_navigation_block' ],
            ]
        );

        // Register a block that displays the post author's name, with support for an override.
        register_block_type(
            'p4/post-author-name',
            [
                'render_callback' => [$this, 'post_author_name_callback'],
                'uses_context' => [ 'postId' ],
            ]
        );

        // Register a block similar to core Post Featured Image block, but with a better sizes attribute.
        register_block_type(
            'p4/post-featured-image',
            [
                'render_callback' => [$this, 'post_featured_image_callback'],
                'uses_context' => [ 'postId' ],
            ]
        );

        // Register a block that displays a breadcrumb link to the first taxonomy term (e.g., category).
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

    /**
     * Renders the author name or override for the current post.
     * If an override is found in post meta, uses that instead.
     *
     * @param array  $attributes Block attributes.
     * @param string $content    Block content.
     * @param object $block      Block context, used to access the post ID.
     * @return string Rendered HTML output for the author name.
     */
    // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter -- register_block_type callback
    private function post_author_name_callback(array $attributes, string $content, object $block): string
    {
        $author_override = get_post_meta($block->context['postId'], 'p4_author_override', true);
        $post_author_id = get_post_field('post_author', $block->context['postId']);

        $is_override = ! empty($author_override);

        $name = $is_override ? $author_override : get_the_author_meta('display_name', $post_author_id);
        $link = $is_override ? '#' : get_author_posts_url($post_author_id);

        $block_content = $author_override ? $name : "<a href='$link'>$name</a>";

        return "<span class='article-list-item-author'>$block_content</span>";
    }

    /**
     * Renders the featured image for the current post with appropriate sizes attribute.
     *
     * @param array  $attributes Block attributes.
     * @param string $content    Block content.
     * @param object $block      Block context, used to access the post ID.
     * @return string Rendered HTML output for the featured image.
     */
    // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter -- register_block_type callback
    private function post_featured_image_callback(array $attributes, string $content, object $block): string
    {
        $post_id = $block->context['postId'];
        $post_link = get_permalink($post_id);
        $featured_image = get_the_post_thumbnail(
            $post_id,
            null,
            [
                'sizes' => '(min-width: 1600px) 389px, (min-width: 1200px) 335px, (min-width: 1000px) 281px, (min-width: 780px) 209px, (min-width: 580px) 516px, calc(100vw - 24px)', // phpcs:ignore Generic.Files.LineLength.MaxExceeded
            ]
        );

        return "<a href='$post_link'>$featured_image</a>";
    }

    /**
     * Renders a breadcrumb link to the first taxonomy term of the post.
     *
     * @param array  $attributes Block attributes, including 'taxonomy'.
     * @param object $block      Block context, used to access the post ID.
     * @return string Rendered HTML output for the taxonomy breadcrumb.
     */
    private function taxonomy_breadcrumb_callback(array $attributes, object $block): string
    {
        $post_id = $block->context['postId'] ?? get_the_ID();
        $taxonomy = $attributes['taxonomy'] ?? 'category';

        $terms = get_the_terms($post_id, $taxonomy);
        if (is_wp_error($terms) || empty($terms)) {
            return '';
        }

        $first = $terms[0];
        $term_link = get_term_link($first);

        return sprintf('<div class="wp-block-post-terms"><a href="%s">%s</a></div>', esc_url($term_link), esc_html($first->name)); // phpcs:ignore Generic.Files.LineLength.MaxExceeded
    }
}
