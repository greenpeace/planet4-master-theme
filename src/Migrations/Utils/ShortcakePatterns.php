<?php

namespace P4\MasterTheme\Migrations\Utils;

/**
 * Planet 4 patterns replacing Shortcake blocks, as parsed blocks.
 *
 * IMPORTANT!
 * The markup of each block must match its `save` function output, otherwise the editor marks the block as invalid.
 */
class ShortcakePatterns
{
    /**
     * "Side image with text and CTA" pattern: a Media & Text block with a heading, a paragraph and a button.
     * See `assets/src/block-templates/side-image-with-text-and-cta/template.js`.
     *
     * @param array $attrs `title`, `paragraph`, `link_label`, `link_url`, and `images` (comma separated
     *                     attachment IDs, only the first one is used).
     * @return array The parsed block.
     */
    public static function side_image_with_text_and_cta(array $attrs): array
    {
        $inner_blocks = [];

        if (!empty($attrs['title'])) {
            $inner_blocks[] = self::block(
                Constants::BLOCK_HEADING,
                [],
                '<h2 class="wp-block-heading">' . $attrs['title'] . '</h2>'
            );
        }

        if (!empty($attrs['paragraph'])) {
            $inner_blocks[] = self::block(Constants::BLOCK_PARAGRAPH, [], '<p>' . $attrs['paragraph'] . '</p>');
        }

        if (!empty($attrs['link_label']) && !empty($attrs['link_url'])) {
            $button = self::block(
                Constants::BLOCK_SINGLE_BUTTON,
                [],
                '<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="'
                    . self::escape($attrs['link_url']) . '">' . self::escape($attrs['link_label']) . '</a></div>'
            );
            $inner_blocks[] = self::block(
                Constants::BLOCK_BUTTONS,
                [],
                ['<div class="wp-block-buttons">', '</div>'],
                [$button]
            );
        }

        $media_text = self::block(
            Constants::BLOCK_MEDIA_TEXT,
            ...self::media_text_attrs_and_markup($attrs['images'] ?? ''),
            ...[$inner_blocks]
        );

        // The pattern block saves its inner blocks only.
        return self::block(Constants::BLOCK_TEMPLATE_SIDE_IMAGE_WITH_TEXT_AND_CTA, [], ['', ''], [$media_text]);
    }

    /**
     * Media & Text block attributes and markup around its inner blocks, see core `media-text/save.js`.
     *
     * @param string $images Comma separated attachment IDs, only the first one is used.
     * @return array The attributes, and the markup before and after the inner blocks.
     */
    private static function media_text_attrs_and_markup(string $images): array
    {
        $attrs = [];
        $media = '';

        $image_id = (int) explode(',', $images)[0];
        $image_url = $image_id ? wp_get_attachment_url($image_id) : false;

        if ($image_url) {
            $alt = (string) get_post_meta($image_id, '_wp_attachment_image_alt', true);
            $attrs = [
                'mediaId' => $image_id,
                'mediaType' => 'image',
            ];
            if ('' !== $alt) {
                $attrs['mediaAlt'] = $alt;
            }
            $media = '<img src="' . self::escape($image_url) . '" alt="' . self::escape($alt) . '"'
                . ' class="wp-image-' . $image_id . ' size-full"/>';
        }

        return [
            $attrs,
            [
                '<div class="wp-block-media-text is-stacked-on-mobile"><figure class="wp-block-media-text__media">'
                    . $media . '</figure><div class="wp-block-media-text__content">',
                '</div></div>',
            ],
        ];
    }

    /**
     * Create a parsed block.
     *
     * @param string          $name The block name.
     * @param array           $attrs The block attributes.
     * @param string|string[] $html The block markup, or the markup before and after its inner blocks.
     * @param array           $inner_blocks The inner blocks.
     * @return array The parsed block.
     */
    private static function block(string $name, array $attrs, $html, array $inner_blocks = []): array
    {
        if (is_string($html)) {
            return Functions::create_new_block($name, $attrs, [], $html, [$html]);
        }

        [$before, $after] = $html;
        $inner_content = array_merge([$before], array_fill(0, count($inner_blocks), null), [$after]);

        return Functions::create_new_block(
            $name,
            $attrs,
            $inner_blocks,
            $before . $after,
            array_values(array_filter($inner_content, fn ($chunk) => '' !== $chunk))
        );
    }

    /**
     * Escape a text or attribute value. Entities in the value are kept as text, like the editor does.
     *
     * @param string $value The value.
     */
    private static function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8', true);
    }
}
