<?php

namespace P4\MasterTheme\Migrations;

use P4\MasterTheme\MigrationRecord;
use P4\MasterTheme\MigrationScript;

class M065ReplaceMetaBlock extends MigrationScript
{
    /**
     * Perform the actual migration.
     *
     * @param MigrationRecord $record Information on the execution, can be used to add logs.
     * phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter -- interface implementation
     */
    public static function execute(MigrationRecord $record): void
    {
        $check_is_valid_block = function ($block) {
            return self::check_is_valid_block($block);
        };

        $transform_block = function ($block) {
            return self::transform_block($block);
        };

        Utils\Functions::execute_block_migration(
            Utils\Constants::BLOCK_SOCIAL_MEDIA,
            $check_is_valid_block,
            $transform_block,
        );
    }
    // phpcs:enable SlevomatCodingStandard.Functions.UnusedParameter

    /**
     * Check whether a block is a Meta (aka Social Media) block.
     *
     * @param array $block - A block data array.
     */
    private static function check_is_valid_block(array $block): bool
    {
        if (!is_array($block) || !isset($block['blockName'])) {
            return false;
        }

        return $block['blockName'] === Utils\Constants::BLOCK_SOCIAL_MEDIA;
    }

    /**
     * Transform the blocks.
     *
     * @param array $block - A block data array.
     * @return array - The transformed block.
     */
    private static function transform_block(array $block): array
    {
        if (isset($block['attrs']['embed_type']) && $block['attrs']['embed_type'] === 'facebook_page') {
            $facebook_url = $block['attrs']['social_media_url'] ?? '';
            $facebook_page_tab = $block['attrs']['facebook_page_tab'] ?? 'timeline';
            $alignment = $block['attrs']['alignment_class'] ?? '';
            $title = $block['attrs']['title'] ?? '';
            $description = $block['attrs']['description'] ?? '';

            $html = '<section class="block social-media-block">' .
            '<header><h2 class="page-section-header">' . esc_html($title) . '</h2></header>' .
            '<p class="page-section-description">' . esc_html($description) . '</p>' .
            '<div class="social-media-embed ' . esc_html($alignment) . '">' .
            '<iframe class="social-media-embed-facebook" src="https://www.facebook.com/plugins/page.php?href=' . esc_html($facebook_url) . '&amp;tabs=' . esc_html($facebook_page_tab) . '&amp;width=500&amp;height=500&amp;small_header=false&amp;adapt_container_width=true&amp;hide_cover=false&amp;show_facepile=true" width="500" height="500" scrolling="no" frameborder="0" allow="encrypted-media" title="Social Media"></iframe>' .
            '</div></section>';

            $block['attrs']['embed_type'] = 'facebookPage';
            $block['attrs']['embed_code'] = $facebook_url;

            $block['innerHTML'] = $html;
            $block['innerContent'][0] = $html;
        }

        var_dump($block);

        return $block;
    }
}
