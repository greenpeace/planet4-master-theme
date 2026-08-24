<?php

namespace P4\MasterTheme;

/**
 * Class HtmlPostProcessor
 *
 * Intercepts and transforms the final HTML output of a page before it is sent to the browser.
 */
class HtmlPostProcessor
{
    private string $home_host;

    public function __construct()
    {
        $this->home_host = parse_url(home_url(), PHP_URL_HOST);
        add_filter('wp_template_enhancement_output_buffer', [$this, 'manage_output_buffer']);
    }

    /**
     * Processes the page output buffer, applying various transformations,
     * and returning the modified HTML string.
     *
     * Adds the class "pdf-link" to the links connected to PDF files.
     *
     * Adds the class "external-link" to the external links.
     */
    public function manage_output_buffer(string $buffer): string
    {
        $processor = new \WP_HTML_Tag_Processor($buffer);

        while ($processor->next_tag('a')) {
            $href = $processor->get_attribute('href') ?? '';
            $classes = $processor->get_attribute('class') ?? '';
            $class_list = preg_split('/\s+/', trim($classes));

            $is_pdf = $this->is_pdf_link($href, $class_list);
            $is_external_link = $this->is_external_link($href, $class_list);
            $is_google_news_link = $this->is_google_news_link($href, $class_list);

            if (empty($href) || (!$is_pdf && !$is_external_link || $is_google_news_link)) {
                continue;
            }

            if ($is_pdf) {
                $processor->add_class('pdf-link');
                $processor->set_attribute(
                    'title',
                    __('This link will open a PDF file', 'planet4-master-theme')
                );
            } else {
                $processor->add_class('external-link');
                $domain = str_replace('www.', '', parse_url($href, PHP_URL_HOST) ?? '');
                $processor->set_attribute(
                    'title',
                    sprintf(
                        // translators: 1: URL domain
                        __('This link will lead you to %1$s', 'planet4-master-theme'),
                        $domain
                    )
                );
            }
        }

        return $processor->get_updated_html();
    }

    /**
     * Checks if a link is linked to a PDF file, considering some exceptions.
     */
    private function is_pdf_link(string $href, array $class_list): bool
    {
        if (!str_contains($href, '.pdf')) {
            return false;
        }

        $excluded = ['search-result-item-headline', 'cover-card-heading', 'pdf-link'];
        foreach ($excluded as $class) {
            if (in_array($class, $class_list, true)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Checks if a link is linked to the "Preferred Source in Google Search" feature.
     */
    private function is_google_news_link(string $href, array $class_list): bool
    {
        return str_contains($href, 'google') && !in_array('google_news_link', $class_list, true);
    }

    /**
     * Checks if a link is external, considering some exceptions.
     */
    private function is_external_link(string $href, array $class_list): bool
    {
        if (
            str_contains($href, $this->home_host) ||
            str_contains($href, '.pdf') ||
            str_starts_with($href, '/') ||
            str_starts_with($href, '#') ||
            str_starts_with($href, 'javascript:') ||
            str_starts_with($href, 'mailto:') ||
            str_starts_with($href, 'tel:')
        ) {
            return false;
        }

        $excluded = ['btn', 'cover-card-heading', 'wp-block-button__link', 'share-btn', 'external-link'];
        foreach ($excluded as $class) {
            if (in_array($class, $class_list, true)) {
                return false;
            }
        }

        return true;
    }
}
