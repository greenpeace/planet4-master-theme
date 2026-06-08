<?php

namespace P4\MasterTheme;

use DOMXPath;

/**
 * Class HtmlPostProcessor
 *
 * Intercepts and transforms the final HTML output of a page before it is sent to the browser.
 *
 */
class HtmlPostProcessor
{
    /**
     * Constructor
     */
    public function __construct()
    {
        add_filter('wp_template_enhancement_output_buffer', [$this, 'manage_output_buffer']);
    }

    /**
     * Processes the page output buffer by parsing it as HTML, applying various
     * DOM transformations, and returning the modified HTML string.
     */
    public function manage_output_buffer(string $buffer): void
    {
        $buffer = $this->setup_pdf_icon($buffer);
        $buffer = $this->setup_external_links($buffer);
    }

    /**
     * Removes the "Related Posts" section when it contains no posts.
     */
    private function remove_related_section_no_posts(DOMXPath $xpath): void
    {
        $sections = $xpath->query("//section[contains(@class, 'post-articles-block')]");

        foreach ($sections as $section) {
            $has_post_template = $xpath->query(".//*[contains(@class, 'wp-block-post-template')]", $section);

            if ($has_post_template->length !== 0) {
                continue;
            }

            $section->parentNode->removeChild($section);
        }
    }

    /**
     * Removes the "No Results" inner block from a Post List or an Actions List block
     * when the title and the description of the block are empty.
     */
    private function remove_no_post_text(DOMXPath $xpath): void
    {
        $no_results =
            $xpath->query("//*[contains(@class, 'p4-query-loop')]//*[contains(@class, 'wp-block-query-no-results')]");

        foreach ($no_results as $no_results_block) {
            $query_loop = $no_results_block->parentNode;

            $post_title = $xpath->query(".//*[contains(@class, 'wp-block-heading')]", $query_loop);
            $post_description = $xpath->query(".//p", $query_loop);

            $title_is_empty = $post_title->length === 0 || trim($post_title->item(0)->textContent) === '';
            $description_is_empty =
                $post_description->length === 0 || trim($post_description->item(0)->textContent) === '';

            if (!$title_is_empty || !$description_is_empty) {
                continue;
            }

            $no_results_block->parentNode->removeChild($no_results_block);
        }
    }

    private function setup_pdf_icon(string $buffer): string
    {
        $processor = new \WP_HTML_Tag_Processor($buffer);

        while ($processor->next_tag('a')) {
            $href = $processor->get_attribute('href');

            if (!$href || !str_contains($href, '.pdf')) {
                continue;
            }

            $class = $processor->get_attribute('class') ?? '';

            if (
                str_contains($class, 'search-result-item-headline') ||
                str_contains($class, 'cover-card-heading') ||
                str_contains($class, 'pdf-link')
            ) {
                continue;
            }

            $processor->add_class('pdf-link');
            $processor->set_attribute(
                'title',
                __('This link will open a PDF file', 'planet4-master-theme')
            );
        }

        return $processor->get_updated_html();
    }

    /**
     * Adds the class "external-link" to the external links.
     */
    private function setup_external_links(string $buffer): string
    {
        $excluded_classes = ['btn', 'cover-card-heading', 'wp-block-button__link', 'share-btn'];
        $home_host = parse_url(home_url(), PHP_URL_HOST);

        $processor = new \WP_HTML_Tag_Processor($buffer);

        while ($processor->next_tag('a')) {
            $href = $processor->get_attribute('href');

            if (empty($href)) {
                continue;
            }

            // Skip excluded href patterns
            if (
                str_contains($href, $home_host) ||
                str_contains($href, '.pdf') ||
                str_starts_with($href, '/') ||
                str_starts_with($href, '#') ||
                str_starts_with($href, 'javascript:') ||
                str_starts_with($href, 'mailto:') ||
                str_starts_with($href, 'tel:')
            ) {
                continue;
            }

            // Skip excluded classes
            $class = $processor->get_attribute('class') ?? '';
            foreach ($excluded_classes as $excluded_class) {
                if (str_contains($class, $excluded_class)) {
                    continue 2;
                }
            }

            // Add external-link class and title
            $domain = str_replace('www.', '', parse_url($href, PHP_URL_HOST));
            $processor->add_class('external-link');
            $processor->set_attribute(
                'title',
                sprintf(
                    __('This link will lead you to %1$s', 'planet4-master-theme'),
                    $domain
                )
            );
        }

        return $processor->get_updated_html();
    }
}
