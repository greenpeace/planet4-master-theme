<?php

namespace P4\MasterTheme;

use DOMDocument;
use DOMXPath;

/**
 * Class OutputBufferManager
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
    public function manage_output_buffer(string $buffer): string
    {
        // Create a new DOM document to parse and manipulate the HTML.
        $dom = new DOMDocument();

        // Suppress XML/HTML parsing warnings.
        libxml_use_internal_errors(true);

        // Load the buffer as HTML into the DOM.
        $dom->loadHTML(
            mb_encode_numericentity($buffer, [0x80, 0x10FFFF, 0, 0x1FFFFF], 'UTF-8'),
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );

        // Discard any parsing errors accumulated above.
        libxml_clear_errors();

        // Create an XPath evaluator to allow the helper methods below to query
        // and target specific nodes within the DOM tree.
        $xpath = new DOMXPath($dom);

        $this->remove_related_section_no_posts($xpath);
        $this->remove_no_post_text($xpath);
        $this->setup_pdf_icon($xpath);
        $this->setup_external_links($xpath);

        return $dom->saveHTML();
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

    /**
     * Adds the class "pdf-link" to the links connected to PDF files.
     */
    private function setup_pdf_icon(DOMXPath $xpath): void
    {
        $links =
            $xpath->query("
                //a[contains(@href, '.pdf')
                and not(contains(@class, 'search-result-item-headline'))
                and not(contains(@class, 'cover-card-heading'))
                and not(contains(@class, 'pdf-link'))]");

        foreach ($links as $link) {
            $parent_tag = $link->parentNode->nodeName;
            $has_image = $xpath->query(".//img", $link)->length > 0;
            $text = trim($link->textContent);

            // Skip headings, image links, and empty links
            if (in_array(strtoupper($parent_tag), ['H1', 'H2', 'H3', 'H4', 'H5', 'H6']) || $has_image || $text === '') {
                continue;
            }

            $classes = $link->getAttribute('class');
            $link->setAttribute('class', trim($classes . ' pdf-link'));
            $link->setAttribute('title', __('This link will open a PDF file', 'planet4-master-theme'));
        }
    }

    /**
     * Adds the class "pdf-link" to the links connected to PDF files.
     */
    private function setup_external_links(DOMXPath $xpath): void
    {
        $excluded_classes = ['btn', 'cover-card-heading', 'wp-block-button__link', 'share-btn'];
        $containers = [
            ".//*[contains(@class, 'page-content')]//a",
            ".//article//a",
            ".//*[contains(@class, 'author-details')]//a"];

        foreach ($containers as $container_query) {
            $links = $xpath->query($container_query);

            foreach ($links as $link) {
                $href = $link->getAttribute('href');
                $classes = $link->getAttribute('class');
                $parent_tag = strtoupper($link->parentNode->nodeName);
                $text = trim($link->textContent);

                // Skip if no href
                if (empty($href)) {
                    continue;
                }

                // Skip excluded classes
                $has_excluded_class = false;
                foreach ($excluded_classes as $excluded_class) {
                    if (str_contains($classes, $excluded_class)) {
                        $has_excluded_class = true;
                        break;
                    }
                }
                if ($has_excluded_class) {
                    continue;
                }

                // Skip excluded href patterns
                if (
                    str_contains($href, parse_url(home_url(), PHP_URL_HOST)) ||
                    str_contains($href, '.pdf') ||
                    str_starts_with($href, '/') ||
                    str_starts_with($href, '#') ||
                    str_starts_with($href, 'javascript:') ||
                    str_starts_with($href, 'mailto:') ||
                    str_starts_with($href, 'tel:')
                ) {
                    continue;
                }

                // Skip links inside .boxout
                $in_boxout = $xpath->query("ancestor::*[contains(@class, 'boxout')]", $link)->length > 0;
                if ($in_boxout) {
                    continue;
                }

                // Skip headings and empty links
                if (in_array($parent_tag, ['H1', 'H2', 'H3', 'H4', 'H5', 'H6']) || $text === '') {
                    continue;
                }

                // Add external-link class
                $link->setAttribute('class', trim($classes . ' external-link'));

                // Set title with domain
                $domain = str_replace('www.', '', parse_url($href, PHP_URL_HOST));
                $link->setAttribute(
                    'title',
                    sprintf(
                        // translators: 1: URL domain
                        __('This link will lead you to %1$s', 'planet4-master-theme'),
                        $domain
                    )
                );
            }
        }
    }
}
