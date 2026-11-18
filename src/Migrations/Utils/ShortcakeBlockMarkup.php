<?php

namespace P4\MasterTheme\Migrations\Utils;

/**
 * Saved markup of the blocks replacing Shortcake blocks.
 *
 * IMPORTANT!
 * The markup must match the block `save` function output, otherwise the editor marks the block as invalid.
 */
class ShortcakeBlockMarkup
{
    /**
     * Accordion block markup, see `assets/src/blocks/Accordion/AccordionFrontend.js`.
     *
     * @param array $attrs The block attributes.
     */
    public static function accordion(array $attrs): string
    {
        $html = '<section class="wp-block-planet4-blocks-accordion block accordion-block">';

        if (!empty($attrs['title'])) {
            $html .= '<header><h2 class="page-section-header">' . self::text($attrs['title']) . '</h2></header>';
        }
        if (!empty($attrs['description'])) {
            $html .= '<p class="page-section-description">' . $attrs['description'] . '</p>';
        }

        foreach ($attrs['tabs'] ?? [] as $tab) {
            $html .= '<div class="accordion-content">';
            if (!empty($tab['headline'])) {
                $html .= '<div class="accordion-headline" name="' . self::text($tab['headline']) . '">'
                    . self::text($tab['headline']) . '</div>';
            }
            $html .= '<div class="panel panel-hidden">';
            if (!empty($tab['text'])) {
                $html .= '<p class="accordion-text">' . $tab['text'] . '</p>';
            }
            $button = $tab['button'] ?? [];
            if (!empty($button['button_text'])) {
                $html .= '<a class="btn btn-secondary accordion-btn" href="' . self::text($button['button_url'] ?? '') . '"'
                    . (!empty($button['button_new_tab']) ? ' target="_blank" rel="noopener noreferrer"' : '')
                    . '>' . self::text($button['button_text']) . '</a>';
            }
            $html .= '</div></div>';
        }

        return $html . '</section>';
    }

    /**
     * Escape a text or attribute value the way React does: entities in the value are kept as text.
     *
     * @param string $value The value.
     */
    private static function text(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8', true);
    }
}
