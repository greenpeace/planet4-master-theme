<?php

// phpcs:disable Generic.Files.LineLength.MaxExceeded

namespace P4\MasterTheme\Migrations;

use P4\MasterTheme\MigrationRecord;
use P4\MasterTheme\MigrationScript;

/**
 * Migrate Social Media blocks with Twitter/X urls to Embed blocks.
 */
class M033MigrateSocialMediaTwitterBlockToEmbedBlock extends MigrationScript
{
    /**
     * Perform the actual migration.
     *
     * @param MigrationRecord $record Information on the execution, can be used to add logs.
     * phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter -- interface implementation
     */
    protected static function execute(MigrationRecord $record): void
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
     * Get the source of a Social Media block.
     * It can be Instagram, Facebook, or Twitter/X.
     *
     * @param string $url - The social media URL used to identify the source.
     * @return string - The source of the Social Media block or an empty string if no source was found.
     */
    private static function identify_source(string $url): string
    {
        foreach (Utils\Constants::SOCIAL_MEDIA_PATTERNS as $value => $key) {
            if (preg_match($key, $url)) {
                return $value;
            }
        }
        return '';
    }

    /**
     * Check that the block is a valid Social Media block,
     * and that the URL is Twitter/X since these are the only ones
     * we want to migrate.
     *
     * @param array $block - A parsed Social Media block.
     * @return bool - Whether or not the block should be migrated.
     */
    private static function check_is_valid_block(array $block): bool
    {
        // Check if the block is valid.
        if (!is_array($block)) {
            return false;
        }

        // Check if the block has a 'blockName' key.
        if (!isset($block['blockName'])) {
            return false;
        }

        // Check if the block is a Social Media block.
        if ($block['blockName'] !== Utils\Constants::BLOCK_SOCIAL_MEDIA) {
            return false;
        }

        // Check if the embed is a Facebook page, in which case we do nothing.
        if (isset($block['attrs']['embed_type']) && $block['attrs']['embed_type'] === 'facebook_page') {
            return false;
        }

        // If not, we get the social media URL.
        $social_media_url = $block['attrs']['social_media_url'] ?? null;

        // If there's no social media url, we do nothing.
        if (!$social_media_url) {
            return false;
        }

        // Identify the source of the media (Facebook, Instagram, Twitter, X)
        $source = self::identify_source($social_media_url);

        // We only want to migrate Twitter/X embeds.
        return $source === Utils\Constants::TWITTER || $source === Utils\Constants::X;
    }

    /**
     * Transform a Social Media block into a group of blocks.
     * This group contains a header for the Social Media block title,
     * a paragraph for the Social Media block description,
     * and a final block (Embed) for the Twitter/X embed.
     *
     * @param array $block - A parsed Social Media block.
     * @return array - The transformed block.
     */
    private static function transform_block(array $block): array
    {
        $block_title = array_key_exists("title", $block['attrs']) ? $block['attrs']['title'] : null;
        $block_description = array_key_exists("description", $block['attrs']) ? $block['attrs']['description'] : null;

        // If the source is X, we want to change it to Twitter.
        // Links from x.com don't work in the Embed block at the moment.
        $social_media_url = $block['attrs']['social_media_url'];
        $source = self::identify_source($social_media_url);
        if ($source === Utils\Constants::X) {
            $social_media_url = str_replace(
                Utils\Constants::X . '.com',
                Utils\Constants::TWITTER . '.com',
                $social_media_url,
            );
        }

        $block = [];
        $block['blockName'] = Utils\Constants::BLOCK_GROUP;
        $block['attrs']['metadata']['name'] = 'Twitter/X Group';

        $block['innerBlocks'] = [];
        $block['innerBlocks'][0] = $block_title ? self::get_heading_block($block_title) : null;
        $block['innerBlocks'][1] = $block_description ? self::get_paragraph_block($block_description) : null;
        $block['innerBlocks'][3] = self::transform_block_to_embed($block, $social_media_url);

        // IMPORTANT: DO NOT MODIFY THIS FORMAT!
        $block['innerHTML'] =
        '<div class="wp-block-group">





        </div>';

        // IMPORTANT: DO NOT MODIFY THIS FORMAT!
        $block['innerContent'] = array (
            0 => '
        <div class="wp-block-group">',
            1 => null,
            2 => '
        ',
            3 => null,
            4 => '
        ',
            5 => null,
            6 => '
        ',
            7 => null,
            8 => '</div>
        ',
        );

        return $block;
    }

    /**
     * Transform a Social Media block into an embed block.
     *
     * @param array $block - A parsed Social Media block.
     * @param string $social_media_url - The Social Media URL.
     * @return array - The transformed block.
     */
    private static function transform_block_to_embed(array $block, string $social_media_url): array
    {
        $type = 'rich';
        $provider = Utils\Constants::TWITTER;

        // IMPORTANT: DO NOT MODIFY THIS FORMAT!
        $html_content = '<figure class="wp-block-embed is-type-' . $type . ' is-provider-' . $provider . ' wp-block-embed-' . $provider . ' wp-embed-aspect-16-9 wp-has-aspect-ratio"><div class="wp-block-embed__wrapper">
        ' . $social_media_url . '
        </div></figure>';

        $block['blockName'] = Utils\Constants::BLOCK_EMBED;
        $block['attrs']['type'] = $type;
        $block['attrs']['providerNameSlug'] = $provider;
        $block['attrs']['metadata']['name'] = $provider;
        $block['attrs']['url'] = $social_media_url;
        $block['innerHTML'] = $html_content;
        $block['innerContent'][0] = $html_content;
        $block['innerBlocks'] = [];

        unset($block['attrs']['title']);
        unset($block['attrs']['description']);

        return $block;
    }

    /**
     * Get a heading block.
     *
     * @param string $text - The text for the heading.
     * @return array - The block.
     */
    private static function get_heading_block(string $text): array
    {
        // IMPORTANT: DO NOT MODIFY THIS FORMAT!
        $html = '
  <h2 class="wp-block-heading">' . $text . '</h2>
  ';

        $heading = [];
        $heading['blockName'] = Utils\Constants::BLOCK_HEADING;
        $heading['attrs'] = [];
        $heading['innerBlocks'] = [];
        $heading['innerHTML'] = $html;
        $heading['innerContent'][0] = $html;

        return $heading;
    }

    /**
     * Get a paragraph block.
     *
     * @param string $text - The text for the paragraph.
     * @return array - The block.
     */
    private static function get_paragraph_block(string $text): array
    {
        // IMPORTANT: DO NOT MODIFY THIS FORMAT!
        $html = '
    <p>' . $text . '</p>
    ';

        $paragraph = [];
        $paragraph['blockName'] = Utils\Constants::BLOCK_PARAGRAPH;
        $paragraph['attrs'] = [];
        $paragraph['innerBlocks'] = [];
        $paragraph['innerHTML'] = $html;
        $paragraph['innerContent'][0] = $html;

        return $paragraph;
    }
}
