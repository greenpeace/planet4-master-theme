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
     */
    public array $context = [];

    /**
     * Templates
     *
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
        $this->view();
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

        // Set layout (grid or list)
        $this->context['layout'] = $_GET['layout'] ?? 'list';

        // Check if this is the News & Stories page
        $news_page_id = (int) get_option('page_for_posts');
        $current_page_id = get_queried_object_id();

        if ($news_page_id === $current_page_id) {
            $this->set_featured_posts();
            $this->set_filters();
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
     * Set the 'News & stories' page filters.
     * For now only the "category" and "post types" are available.
     */
    private function set_filters(): void
    {
        if (!is_home()) {
            return;
        }

        $all_categories = get_categories();
        $categories = [];
        // Only categories that have at least 1 Post assigned should be displayed for filtering.
        foreach ($all_categories as $cat) {
            if (!get_posts(['post_type' => 'post', 'category' => $cat->term_id])) {
                continue;
            }
            $categories[] = $cat;
        }
        $this->context['categories'] = $categories;
        $this->context['post_types'] = get_terms(['taxonomy' => 'p4-page-type']);

        $this->context['current_category'] = $_GET['category'] ?? '';
        $this->context['current_post_type'] = $_GET['post-type'] ?? '';
    }

    /**
     * Add featured posts to the context for the News & Stories page.
     */
    private function set_featured_posts(): void
    {
        if (!is_home()) {
            return;
        }

        $sticky_posts = get_option('sticky_posts');
        if (empty($sticky_posts) || count($sticky_posts) < 4) {
            return;
        }

        $sticky_posts = new \WP_Query([
            'post__in' => $sticky_posts,
            'posts_per_page' => 4,
            'post_type' => 'post',
            'post_status' => 'publish',
            'fields' => 'ids',
            'orderby' => 'modified',
            'order' => 'DESC',
        ]);

        $featured_post_ids = $sticky_posts->posts;
        $this->context['featured_post_ids'] = $featured_post_ids;

        $featured_query = new \WP_Query([
            'post__not_in' => $featured_post_ids,
            'post_type' => 'post',
            'post_status' => 'publish',
            'fields' => 'ids',
            'posts_per_page' => -1,
        ]);

        $excluded_post_ids = $featured_query->posts;
        $this->context['excluded_post_ids'] = $excluded_post_ids;

        // Get the featured posts template
        $template_path = get_template_directory() . "/templates/featured-posts.twig";
        $template = Timber::compile($template_path, $this->context);

        $this->context['featured_posts_content'] = do_blocks($template);
    }

    /**
     * View the listing page template.
     */
    public function view(): void
    {
        do_action('enqueue_listing_page_layout_switch_script');
        do_action('enqueue_google_tag_manager_script', $this->context);
        Timber::render($this->templates, $this->context);
    }
}
