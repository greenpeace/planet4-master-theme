<?php

namespace P4\MasterTheme;

use Timber\Timber;

/**
 * Class ListingPage
 */
class ListingPage
{
    /**
     * Context
     *
     * @var array $context
     */
    public array $context = [];

    /**
     * Templates
     *
     * @var array $templates
     */
    protected array $templates = [];

    /**
     * ListingPage constructor.
     *
     * @param array $templates An indexed array with template file names. The first to be found will be used.
     * @param array $context An associative array with all the context needed to render the template found first.
     */
    public function __construct(array $templates = [ 'archive.twig', 'index.twig' ], array $context = [])
    {
        $this->templates = $templates;
        $this->context = $context;

        $this->update_context();
    }

    /**
     * Add needed data to the context.
     */
    private function update_context(): void
    {
        global $wp;

        $this->context['canonical_link'] = home_url($wp->request);
        $this->context['page_category'] = is_home() ? 'News' : 'Listing Page';
        $this->context['og_type'] = isset($this->context['author']) ? 'profile' : 'website';

        // For the News & Stories page we don't need more context.
        if (is_home()) {
            return;
        }

        $this->add_listing_page_content();
        $this->set_featured_action();
        $this->set_news_page_link();
    }

    /**
     * Add listing page content to the context.
     */
    private function add_listing_page_content(): void
    {
        $template = file_get_contents(get_template_directory() . "/parts/query-listing-page.html");
        $content = do_blocks($template);
        $this->context['listing_page_content'] = $content;
    }

    /**
     * Add featured action to the context. This only applies to Category and Tag pages.
     */
    private function set_featured_action(): void
    {
        $taxonomy = $this->context['taxonomy'] ?? null;
        if (
            !$taxonomy
            || ($taxonomy->taxonomy !== 'category' && ! is_tag())
        ) {
            return;
        }

        $featured_action = get_posts([
            'post_type' => 'p4_action',
            'category' => $taxonomy->term_id,
            'orderby' => 'date',
            'order' => 'DESC',
            'numberposts' => 1,
        ])[0] ?? null;
        $featured_action_id = $featured_action->ID ?? null;

        $this->context['featured_action'] = $featured_action;
        $this->context['featured_action_image'] = has_post_thumbnail($featured_action_id) ?
            get_the_post_thumbnail($featured_action_id, 'medium') : null;
        $this->context['featured_action_url'] = get_permalink($featured_action_id);
    }

    /**
     * Set the 'News & stories' page in the context, needed for some listing pages.
     */
    private function set_news_page_link(): void
    {
        $news_page = (int) get_option('page_for_posts');
        if (!$news_page) {
            return;
        }

        $news_page_link = get_permalink($news_page);
        $this->context['news_page_link'] = $news_page_link;
    }

    /**
     * View the listing page template.
     */
    public function view(): void
    {
        Timber::render($this->templates, $this->context);
    }

    /**
     * Return posts to be dynamically rendered in the frontend.
     */
    public static function get_posts(): array
    {
        $posts = wp_get_recent_posts([
            'orderby' => 'date',
            'post_status' => 'publish',
            'has_password' => false,
            'suppress_filters' => false,
            'numberposts' => -1,
        ]);

        $to_return = [];

        foreach ($posts as $post) {
            $post['alt_text'] = '';
            $author_override = get_post_meta($post['ID'], 'p4_author_override', true);
            $post['author_name'] = '' === $author_override ?
                get_the_author_meta('display_name', $post['post_author']) : $author_override;
            $post['author_url'] = '' === $author_override ? get_author_posts_url($post['post_author']) : '#';

            if (has_post_thumbnail($post['ID'])) {
                $img_id = get_post_thumbnail_id($post['ID']);
                $post['alt_text'] = get_post_meta($img_id, '_wp_attachment_image_alt', true);
                $post['thumbnail_url'] = get_the_post_thumbnail_url($post['ID'], 'articles-medium-large');
                $post['thumbnail_srcset'] = wp_get_attachment_image_srcset($img_id, 'articles-medium-large');
            }

            $wp_tags = wp_get_post_tags($post['ID']);
            $tags = [];

            if ($wp_tags) {
                foreach ($wp_tags as $wp_tag) {
                    $tags_data['name'] = $wp_tag->name;
                    $tags_data['link'] = get_tag_link($wp_tag);
                    $tags_data['id'] = $wp_tag->term_id;
                    $tags[] = $tags_data;
                }
            }

            $post['tags'] = $tags;
            $page_type_data = get_the_terms($post['ID'], 'p4-page-type');
            $page_type = '';
            $page_type_id = '';

            if ($page_type_data && ! is_wp_error($page_type_data)) {
                $page_type = $page_type_data[0]->name;
                $page_type_id = $page_type_data[0]->term_id;
            }

            $post['page_type'] = $page_type;
            $post['page_type_link'] = get_term_link($page_type_id);
            $post['page_type_id'] = $page_type_id;
            $post['link'] = get_permalink($post['ID']);
            $post['date_formatted'] = get_the_date('', $post['ID']);
            $post['reading_time'] = (new Post($post['ID']))->reading_time();

            $to_return[] = $post;
        }

        return $to_return;
    }
}
