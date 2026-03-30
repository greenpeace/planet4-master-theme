<?php

namespace P4\MasterTheme\Blocks;

use Timber\Timber;
use WP_Block;

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

    // phpcs:disable Generic.Files.LineLength.MaxExceeded
    // phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter

    /**
     * Register the Reading Time block.
     */
    public function register_reading_time_block(): void
    {
        register_block_type(
            'p4/reading-time',
            [
                'render_callback' => [ $this, 'reading_time_block' ],
                'uses_context' => [ 'postId' ],
            ]
        );
    }

    /**
     * Register the Post Author Name block.
     */
    public function register_post_author_name_block(): void
    {
        register_block_type(
            'p4/post-author-name',
            [
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
    public function register_post_featured_image_block(): void
    {
        register_block_type(
            'p4/post-featured-image',
            [
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
    public function register_related_posts_block(): void
    {
        register_block_type(
            'p4/related-posts',
            [
                'attributes' => [
                    'query_attributes' => [
                        'type' => 'object',
                        'default' => [],
                    ],
                ],
                'render_callback' => [ $this, 'render_related_posts_block' ],
            ]
        );
    }

    /**
     * Register the Bottom Page Navigation block.
     */
    public function register_bottom_page_navigation_block(): void
    {
        register_block_type(
            'p4/bottom-page-navigation-block',
            [
                'render_callback' => [ $this, 'render_navigation_block' ],
            ]
        );
    }

    /**
     * Register the Taxonomy Breadcrumbs block.
     */
    public function register_taxonomy_breadcrumb_block(): void
    {
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

                    return $attributes['isLink'] ?
                        sprintf('<div class="wp-block-post-terms"><a href="%s">%s</a></div>',
                            esc_url($term_link), esc_html($first->name)
                        ) :
                        sprintf('<div class="wp-block-post-terms">%s</div>', esc_html($first->name));
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
                    'isLink' => [
                        'type' => 'boolean',
                        'default' => true,
                    ],
                ],
            ]
        );
    }

    /**
     * Custom block render function for Related posts
     *
     * @param array  $attributes Array of dynamic attributes to render section.
     *
     * @return string HTML markup for front end.
     */
    public static function render_related_posts_block(array $attributes): string
    {
        // Encode the query attributes to JSON for the block template
        $query_json = wp_json_encode($attributes['query_attributes'], JSON_UNESCAPED_SLASHES);

        // Dynamically render link to News & Stories page
        $news_stories_url = '';
        $news_stories_page = (int) get_option('page_for_posts');

        if ($news_stories_page) {
            $news_stories_url = get_permalink($news_stories_page);

            $post_page_filters = $attributes['query_attributes']['query']['taxQuery'];
            $tag_id = isset($post_page_filters['post_tag']) ? (int) $post_page_filters['post_tag'][0] : null;
            $category_id = isset($post_page_filters['category']) ? (int) $post_page_filters['category'][0] : null;
            $post_type_id = isset($post_page_filters['p4-page-type']) ? (int) $post_page_filters['p4-page-type'][0] : null;

            $query_args = [];

            // Add post type filter
            if ($post_type_id) {
                $post_type = get_term_by('id', $post_type_id, 'p4-page-type');
                if ($post_type && !is_wp_error($post_type)) {
                    $query_args['post-type'] = $post_type->slug;
                }
            }

            // Add category filter
            if ($category_id) {
                $category = get_term_by('id', $category_id, 'category');
                if ($category && !is_wp_error($category)) {
                    $query_args['category'] = $category->slug;
                }
            }

            // Add tag filter
            if ($tag_id) {
                $tag = get_term_by('id', $tag_id, 'post_tag');
                if ($tag && !is_wp_error($tag)) {
                    $query_args['tag'] = $tag->slug;
                }
            }

            if (!empty($query_args)) {
                $news_stories_url = add_query_arg($query_args, $news_stories_url);
            }
        }

        $see_all_link_group = !empty($news_stories_url) ?
            '<!-- wp:navigation-link {"label":"' . __('See all posts', 'planet4-master-theme') . '","url":"' . $news_stories_url . '","className":"see-all-link"} /-->'
        : '';

        // Define the HTML output for the block
        $output = '<!-- wp:query ' . $query_json . ' -->
            <div class="wp-block-query posts-list p4-query-loop is-custom-layout-list">
                <!-- wp:group {"layout":{"type":"flex","justifyContent":"space-between"}} -->
                    <div class="wp-block-group">
                        <!-- wp:heading -->
                            <h2 class="wp-block-heading">' . __('Related Posts', 'planet4-master-theme') . '</h2>
                        <!-- /wp:heading -->
                    </div>
                <!-- /wp:group -->
                <!-- wp:post-template -->
                    <!-- wp:columns -->
                        <div class="wp-block-columns">
                            <!-- wp:post-featured-image {"isLink":true} /-->
                            <!-- wp:group -->
                                <div class="wp-block-group">
                                    <!-- wp:group {"layout":{"type":"flex"}} -->
                                        <div class="wp-block-group">
                                            <!-- wp:p4/taxonomy-breadcrumb {"taxonomy":"category"} /-->
                                            <!-- wp:post-terms {"term":"post_tag","separator":" "} /-->
                                        </div>
                                    <!-- /wp:group -->
                                    <!-- wp:post-title {"isLink":true, "level": 4} /-->
                                    <!-- wp:post-excerpt /-->
                                    <!-- wp:group {"className":"posts-list-meta"} -->
                                        <div class="wp-block-group posts-list-meta">
                                            <!-- wp:p4/post-author-name /-->
                                            <!-- wp:post-date /-->
                                        </div>
                                    <!-- /wp:group -->
                                </div>
                            <!-- /wp:group -->
                        </div>
                    <!-- /wp:columns -->
                <!-- /wp:post-template -->
                ' . $see_all_link_group . '
            </div>
        <!-- /wp:query -->';

        return do_blocks($output);
    }

    /**
     * Custom block render function for Bottom page navigation
     *
     * @param array  $attributes Array of dynamic attributes to render section.
     *
     * @return string HTML markup for front end.
     */
    public static function render_navigation_block(array $attributes): string
    {
        global $post;

        $menu = Timber::get_menu('navigation-bar-menu');
        if ($menu) {
            $menu_items = $menu->get_items();
        } else {
            $menu_items = [];
        }

        // Check if the current page is in the menu
        $nav_menu_item = null;
        foreach ($menu_items as $item) {
            if ((int) $item->object_id === (int) $post->ID) {
                $nav_menu_item = $item;
                break;
            }
        }


        // Check if the current page is a submenu item
        $submenu_page = null;
        foreach ($menu_items as $item) {
            if (empty($item->children)) {
                continue;
            }

            foreach ($item->children as $child) {
                if ((int) $child->object_id === (int) $post->ID) {
                    $submenu_page = $child;
                    break 2;
                }
            }
        }


        // Omit the block if the page is not in the menu or submenu
        if (!$nav_menu_item && !$submenu_page) {
            return '';
        }

        // For parent pages, get the previous and next siblings in the menu order
        $output = '';
        $siblings = array_filter($menu_items, function ($item) use ($nav_menu_item) {
            return isset($item, $nav_menu_item, $item->menu_item_parent, $nav_menu_item->menu_item_parent)
                && $item->menu_item_parent === $nav_menu_item->menu_item_parent;
        });

        $siblings = array_values($siblings);

        $current_index = array_search($nav_menu_item, $siblings);

        if ($current_index !== false) {
            $prev_item = $siblings[$current_index - 1] ?? null;
            $next_item = $siblings[$current_index + 1] ?? null;
            if ($prev_item) {
                $output .= '<a href="' . esc_url($prev_item->url) . '" class="bottom-navigation-prev"><span class="bottom-navigation-link-text">' . esc_html($prev_item->title) . '</span></a>';
            }
            if ($next_item) {
                $output .= '<a href="' . esc_url($next_item->url) . '" class="bottom-navigation-next"><span class="bottom-navigation-link-text">' . esc_html($next_item->title) . '</span></a>';
            }
        }

        // For child pages, only show link to the parent
        if (isset($submenu_page, $submenu_page->menu_item_parent)) {
            $parent_item = array_filter($menu_items, function ($item) use ($submenu_page) {
                return (int) $item->ID === (int) $submenu_page->menu_item_parent;
            });

            $parent_item = reset($parent_item);
            if ($parent_item) {
                $output = '<a href="' . esc_url($parent_item->url) . '" class="bottom-navigation-prev sub-nav-item"><span class="bottom-navigation-link-text">' . esc_html($parent_item->title) . '</span></a>';
            }
        }

        return '<nav aria-label="' . esc_html__("Pages", "planet4-master-theme") . '" class="container bottom-navigation">' . $output . '</nav>';
    }

    /**
     * Server side render for the reading time block.
     *
     * @param array    $attributes Block attributes, unused.
     * @param string   $content Content which apparently no core block uses.
     * @param WP_Block $block With all block properties.
     *
     * @return string Formatted reading time.
     */
    public static function reading_time_block(
        array $attributes,
        string $content,
        WP_Block $block
    ): string {
        $time = (Timber::get_post($block->context['postId'] ?? false))->reading_time_for_display();
        return $time ?
            '<span class="article-list-item-readtime">'
            // translators: reading time in min.
            . sprintf(__('%d min read', 'planet4-master-theme'), $time) .
            '</span>'
            : '';
    }
    // phpcs:enable SlevomatCodingStandard.Functions.UnusedParameter
    // phpcs:enable Generic.Files.LineLength.MaxExceeded
}
