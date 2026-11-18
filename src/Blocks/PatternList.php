<?php

declare(strict_types=1);

namespace P4\MasterTheme\Blocks;

use WP_HTML_Tag_Processor;

/**
 * Renders the items of some block patterns as a list of links.
 *  This is mostly applied to Issues, Deep Dive and Quick Links
 * Editors get usual blocks (groups, columns, images, headings)
 */
class PatternList
{
    public const LIST_CLASS = 'pattern-list';
    public const ITEM_CLASS = 'pattern-list-item';
    public const LINK_CLASS = 'pattern-list-link';
    public const TITLE_CLASS = 'pattern-list-title';

    /**
     * Register the hooks.
     */
    public static function register_hooks(): void
    {
        add_filter('render_block', [self::class, 'render'], 10, 2);
    }

    /**
     * Transform the markup of the marked blocks.
     *
     * Inner blocks are rendered before their parents, so links and items
     * are already transformed when the list is processed.
     *
     * @param string $html The block content.
     * @param array $block The block data.
     * @return string The block content.
     */
    public static function render(string $html, array $block): string
    {
        $class_name = $block['attrs']['className'] ?? '';

        if (!is_string($class_name) || !str_contains($class_name, self::LIST_CLASS)) {
            return $html;
        }

        // Check if the class name is present into the block
        $classes = preg_split('/\s+/', trim($class_name));
        $is_link = in_array(self::LINK_CLASS, $classes, true);
        $is_item = in_array(self::ITEM_CLASS, $classes, true);
        $is_list = in_array(self::LIST_CLASS, $classes, true);

        if ($is_link) {
            $html = self::render_link($html, $is_item);
        }

        if ($is_item) {
            $html = $is_link ? '<li>' . $html . '</li>' : self::rename_wrapper($html, 'li');
        }

        if ($is_list) {
            $html = self::rename_wrapper(self::add_wrapper_class($html, 'list-unstyled'), 'ul');
        }

        return $html;
    }

    /**
     * Turn the block wrapper into a link to the first URL that is inside.
     *
     * @param string $html The block content.
     * @param bool $is_item Whether the link is also the list item.
     * @return string The block content.
     */
    private static function render_link(string $html, bool $is_item): string
    {
        // Prioritize the link of the title, if no, then get any other link inside.
        $link = [];
        if (preg_match('/<h[1-6]\b.*?<\/h[1-6]>/is', $html, $heading)) {
            $link = self::get_first_link($heading[0]);
        }
        if (empty($link)) {
            $link = self::get_first_link($html);
        }

        // Remove inner links.
        $html = preg_replace('/<a\b[^>]*>|<\/a>/i', '', $html) ?? $html;

        // Replace headings with paragraphs.
        $html = preg_replace('/<h[1-6]\b/i', '<p', $html) ?? $html;
        $html = preg_replace('/<\/h[1-6]>/i', '</p>', $html) ?? $html;

        $processor = new WP_HTML_Tag_Processor($html);
        while ($processor->next_tag('p')) {
            if (!$processor->has_class('wp-block-heading')) {
                continue;
            }
            $processor->add_class(self::TITLE_CLASS);
        }
        $html = $processor->get_updated_html();

        if (empty($link)) {
            return $html;
        }

        $processor = new WP_HTML_Tag_Processor($html);
        if (!$processor->next_tag()) {
            return $html;
        }

        foreach ($link as $name => $value) {
            $processor->set_attribute($name, $value);
        }

        $processor->add_class('text-decoration-none');

        // A link is inline by default, keep the layout of the block.
        $wrapper_class = (string) $processor->get_attribute('class');
        if (!preg_match('/\bis-layout-(flex|grid)\b/', $wrapper_class)) {
            $processor->add_class('d-block');
        }

        // Fill the list item, so all items keep the same height.
        if ($is_item) {
            $processor->add_class('h-100');
        }

        return self::rename_wrapper($processor->get_updated_html(), 'a');
    }

    /**
     * Get the attributes of the first link found in the content.
     *
     * @param string $html The block content.
     * @return array The link attributes (href, target, rel), empty if there is no link.
     */
    private static function get_first_link(string $html): array
    {
        $processor = new WP_HTML_Tag_Processor($html);

        while ($processor->next_tag('a')) {
            $href = $processor->get_attribute('href');
            if (!is_string($href) || $href === '') {
                continue;
            }

            $link = ['href' => $href];
            foreach (['target', 'rel'] as $name) {
                $value = $processor->get_attribute($name);
                if (!is_string($value) || $value === '') {
                    continue;
                }
                $link[$name] = $value;
            }

            return $link;
        }

        return [];
    }

    /**
     * Add a class to the wrapper element of the block.
     *
     * @param string $html The block content.
     * @param string $class_name The class to add.
     * @return string The block content.
     */
    private static function add_wrapper_class(string $html, string $class_name): string
    {
        $processor = new WP_HTML_Tag_Processor($html);

        if (!$processor->next_tag()) {
            return $html;
        }
        $processor->add_class($class_name);

        return $processor->get_updated_html();
    }

    /**
     * Change the tag of the wrapper element of the block.
     *
     * @param string $html The block content.
     * @param string $tag The new tag.
     * @return string The block content.
     */
    private static function rename_wrapper(string $html, string $tag): string
    {
        if (!preg_match('/^\s*<([a-z][a-z0-9]*)\b/i', $html, $matches)) {
            return $html;
        }

        $old_tag = $matches[1];
        $html = preg_replace('/^(\s*)<' . $old_tag . '\b/i', '$1<' . $tag, $html, 1) ?? $html;

        return preg_replace('/<\/' . $old_tag . '>(\s*)$/i', '</' . $tag . '>$1', $html, 1) ?? $html;
    }
}
