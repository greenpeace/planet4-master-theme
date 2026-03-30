<?php

namespace P4\MasterTheme\Blocks;

use P4\MasterTheme\Post;

/**
 * This class is used to register blocks that because of
 * their relative simplicity do not require a separate class.
 */
class Others
{
    public function __construct()
    {
        $this->register_reading_time_block();
        $this->register_post_author_name_block();
        $this->register_post_featured_image_block();
        $this->register_related_posts_block();
        $this->register_bottom_page_navigation_block();
        $this->register_taxonomy_breadcrumb_block();
    }

    /**
     * Register the Reading Time block.
     */
    public function register_reading_time_block(): void
    {
        register_block_type(
            'p4/reading-time',
            [
                'render_callback' => [ Post::class, 'reading_time_block' ],
                'uses_context' => [ 'postId' ],
            ]
        );
    }

    /**
     * Register the Post Author Name block.
     */
    public function register_post_author_name_block(): void {
        register_block_type(
            'p4/post-author-name',
            [
                // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter -- register_block_type callback
                'render_callback' => function (array $attributes, $content, $block) {
                    $author_override = get_post_meta($block->context['postId'], 'p4_author_override', true);
                    $post_author_id = get_post_field('post_author', $block->context['postId']);

                    $is_override = ! empty($author_override);
                    $name = $author_override ?: (get_the_author_meta('display_name', $post_author_id) ?? '');

                    $link = $is_override ? '#' : get_author_posts_url($post_author_id);

                    $block_content = $author_override ? $name : "<a href='$link'>$name</a>";

                    return "<span class='article-list-item-author'>$block_content</span>";
                },
                'uses_context' => [ 'postId' ],
            ]
        );
    }

    /**
     * Register the Post Featured Image block.
     * Like the core block but with an appropriate sizes attribute.
     */
    public function register_post_featured_image_block(): void {
        register_block_type(
            'p4/post-featured-image',
            [
                // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter -- register_block_type callback
                'render_callback' => function (array $attributes, $content, $block) {
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
                },
                'uses_context' => [ 'postId' ],
            ]
        );
    }

    /**
     * Register the Related Posts block.
     * Block displays related posts using the Query Loop block
     */
    public function register_related_posts_block(): void {
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
    }

    /**
     * Register the Bottom Page Navigation block.
     */
    public function register_bottom_page_navigation_block(): void {
        register_block_type(
            'p4/bottom-page-navigation-block',
            [
                'render_callback' => [ Post::class, 'render_navigation_block' ],
            ]
        );
    }

    /**
     * Register the Taxonomy Breadcrumbs block.
     */
    public function register_taxonomy_breadcrumb_block(): void {
        register_block_type(
            'p4/taxonomy-breadcrumb',
            [
                'api_version' => 3,
                'render_callback' => function ($attributes, $block) {
                    $post_id = $block->context['postId'] ?? get_the_ID();
                    $options = get_option('planet4_options');
                    $global_taxonomy = $options['global_taxonomy_breadcrumbs'] ?? 'category';
                    $taxonomy = $attributes['post_type'] === 'p4_action' ? 'category' : $global_taxonomy;

                    $terms = get_the_terms($post_id, $taxonomy);
                    if (is_wp_error($terms) || empty($terms)) {
                        return '';
                    }

                    $first = $terms[0];
                    $term_link = get_term_link($first);

                    return sprintf('<div class="wp-block-post-terms"><a href="%s">%s</a></div>', esc_url($term_link), esc_html($first->name));
                },
                'uses_context' => ['postId'],
                'attributes' => [
                    'taxonomy' => [
                        'type' => 'string',
                        'default' => 'category',
                    ],
                    'post_type' => [
                        'type' => 'string',
                        'default' => 'post',
                    ],
                ],
            ]
        );
    }
}
