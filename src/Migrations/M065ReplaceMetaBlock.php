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
        if (isset($block['attrs']['social_media_url'])) {
            $media = $block['attrs']['social_media_url'];
            $title = $block['attrs']['title'] ?? '';
            $description = $block['attrs']['description'] ?? '';
            $alignment = $block['attrs']['alignment_class'] ?? '';

            if (str_contains($media, 'facebook')) {
                $block_data = self::migrate_facebook($block, $title, $description, $alignment);
            } else {
                $block_data = self::migrate_instagram($block, $title, $description, $alignment);
            }

            $block['attrs']['embed_type'] = $block_data['embed_type'];
            $block['attrs']['embed_code'] = $block_data['embed_code'];

            $block['innerHTML'] = $block_data['html'];
            $block['innerContent'][0] = $block_data['html'];
        }

        var_dump($block);
        return $block;
    }

    private static function migrate_facebook(
        array $block, string $title, string $description, string $alignment
    ): array
    {
        $is_facebook_page = isset($block['attrs']['embed_type']) && $block['attrs']['embed_type'] === 'facebook_page';

        $fb_plugin = $is_facebook_page ? 'page.php' : 'post.php';
        $fb_url = $block['attrs']['social_media_url'] ?? '';
        $fb_src_base = 'https://www.facebook.com/plugins/' . $fb_plugin . '?href=' . esc_html($fb_url);

        if ($is_facebook_page) {
            $fb_page_tab = $block['attrs']['facebook_page_tab'] ?? 'timeline';
            $fb_src = $fb_src_base . '&tabs=' . esc_html($fb_page_tab) . '&width=500&height=500&small_header=false&adapt_container_width=true&hide_cover=false&show_facepile=true';
        } else {
            $fb_src = $fb_src_base . '&show_text=true&width=500&height=500';
        }

        $html = '<section class="block social-media-block">' .
        '<header><h2 class="page-section-header">' . esc_html($title) . '</h2></header>' .
        '<p class="page-section-description">' . esc_html($description) . '</p>' .
        '<div class="social-media-embed ' . esc_html($alignment) . '">' .
        '<iframe class="social-media-embed-facebook" src="' . esc_html($fb_src) . '" width="500" height="500" scrolling="no" frameborder="0" allow="encrypted-media" title="Social Media"></iframe>' .
        '</div></section>';

        return [
            'embed_type' => $is_facebook_page ? 'facebookPage' : 'facebookPost',
            'embed_code' => $fb_url,
            'html' => $html,
        ];
    }

    private static function migrate_instagram(
        array $block, string $title, string $description, string $alignment
    ): array
    {
        $ig_url = $block['attrs']['social_media_url'] ?? '';
        $ig_src = basename(rtrim($ig_url, '/'));

        $html = '<section class="block social-media-block">' .
        '<header><h2 class="page-section-header">' . esc_html($title) . '</h2></header>' .
        '<p class="page-section-description">' . esc_html($description) . '</p>' .
        '<div class="social-media-embed ' . esc_html($alignment) . '">' .
        '<blockquote class="instagram-media" data-instgrm-captioned="true" data-instgrm-permalink="https://www.instagram.com/reel/' . esc_html($ig_src) . '/?utm_source=ig_embed&amp;utm_campaign=loading" data-instgrm-version="14"></blockquote>' .
        '</div></section>';

        return [
            'embed_type' => 'instagramPost',
            'embed_code' => $ig_src,
            'html' => $html,
        ];
    }
}
