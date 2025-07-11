<?php

/**
 * Accordion block class.
 *
 * @package P4\MasterTheme
 * @since 0.1
 */

 namespace P4\MasterTheme\Blocks;

 use P4\MasterTheme\Post;

/**
 * Class Accordion
 *
 * @package P4\MasterTheme\Blocks
 */
class OtherBlocks
{
    /**
     * Accordion constructor.
     */
    public function __construct()
    {
        add_action('init', [$this, 'register_custom_blocks']);

        version_compare(get_bloginfo('version'), '5.5', '<')
            ? add_action('init', [$this, 'p4_register_core_image_block'])
            : add_filter('register_block_type_args', [$this, 'register_core_blocks_callback']);
    }

    public function register_custom_blocks(): void
    {
        $this->register_reading_time_block();
        $this->register_post_author_name_block();
        $this->register_post_featured_image_block();
        $this->register_related_posts_block();
        $this->register_bottom_page_navigation_block();
        $this->register_taxonomy_breadcrumb_block();
    }

    /**
     * Add callback function to Gutenberg core/image block.
     */
    public function p4_register_core_image_block(): void
    {
        unregister_block_type('core/image');
        register_block_type(
            'core/image',
            ['render_callback' => [$this, 'p4_core_image_block_render']]
        );
    }

    /**
     * Add callback function to Gutenberg core/image block.
     *
     * @param array $args Parameters given during block register.
     *
     * @return array Parameters of the block.
     */
    public function register_core_blocks_callback(array $args): array
    {
        if ('core/image' === $args['name']) {
            $args['render_callback'] = [$this, 'p4_core_image_block_render'];
        }

        return $args;
    }

    private function register_reading_time_block () {
        register_block_type(
            'p4/reading-time',
            [
                'render_callback' => [ Post::class, 'reading_time_block' ],
                'uses_context' => [ 'postId' ],
            ]
        );
    }

    private function register_post_author_name_block() {
        register_block_type(
            'p4/post-author-name',
            [
                // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter -- register_block_type callback
                'render_callback' => function (array $attributes, $content, $block) {
                    $author_override = get_post_meta($block->context['postId'], 'p4_author_override', true);
                    $post_author_id = get_post_field('post_author', $block->context['postId']);

                    $is_override = ! empty($author_override);

                    $name = $is_override ? $author_override : get_the_author_meta('display_name', $post_author_id);
                    $link = $is_override ? '#' : get_author_posts_url($post_author_id);

                    $block_content = $author_override ? $name : "<a href='$link'>$name</a>";

                    return "<span class='article-list-item-author'>$block_content</span>";
                },
                'uses_context' => [ 'postId' ],
            ]
        );
    }

    private function register_post_featured_image_block () {
        // Like the core block but with an appropriate sizes attribute.
        // phpcs:disable Generic.Files.LineLength.MaxExceeded
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

    private function register_related_posts_block () {
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
    }

    private function register_bottom_page_navigation_block () {
        register_block_type(
            'p4/bottom-page-navigation-block',
            [
                'render_callback' => [ Post::class, 'render_navigation_block' ],
            ]
        );
    }

    private function register_taxonomy_breadcrumb_block () {
        register_block_type(
            'p4/taxonomy-breadcrumb',
            [
                'api_version' => 2,
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
