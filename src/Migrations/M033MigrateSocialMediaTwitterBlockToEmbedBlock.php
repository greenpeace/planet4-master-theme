<?php

// phpcs:disable Generic.Files.LineLength.MaxExceeded

namespace P4\MasterTheme\Migrations;

use P4\MasterTheme\MigrationRecord;
use P4\MasterTheme\MigrationScript;
use WP_Block_Parser;

/**
 * Migrate Social Media blocks with Twitter/X urls to Embed blocks.
 */
class M033MigrateSocialMediaTwitterBlockToEmbedBlock extends MigrationScript
{
    private const SOURCE_INSTAGRAM = 'instagram';
    private const SOURCE_FACEBOOK = 'facebook';
    private const SOURCE_TWITTER = 'twitter';
    private const SOURCE_X = 'x';

    private const SOURCE_PATTERNS = [
        self::SOURCE_INSTAGRAM => '/^(https?\:\/\/)?(www\.)?instagram\.com\/.+$/i',
        self::SOURCE_FACEBOOK => '/^(https?\:\/\/)?(www\.)?facebook\.com\/.+$/i',
        self::SOURCE_TWITTER => '/^(https?\:\/\/)?(www\.)?(twitter\.com)\/.+$/i',
        self::SOURCE_X => '/^(https?\:\/\/)?(www\.)?x\.com\/.+$/i',
    ];

    /**
     * Perform the actual migration.
     *
     * @param MigrationRecord $record Information on the execution, can be used to add logs.
     * phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter -- interface implementation
     */
    protected static function execute(MigrationRecord $record): void
    {
        try {
            // Get the list of posts using Social Media blocks.
            $posts = Utils\Functions::get_posts_using_specific_block(
                Utils\Constants::BLOCK_SOCIAL_MEDIA,
                Utils\Constants::ALL_POST_TYPES
            );

            // If there are no posts, abort.
            if (!$posts) {
                return;
            }

            echo "Social Media block migration in progress...\n"; // phpcs:ignore

            $parser = new WP_Block_Parser();

            // Variable to store the current post ID
            $current_post_id = null;

            // Get all the blocks of each post.
            foreach ($posts as $post) {
                if (empty($post->post_content)) {
                    continue;
                }

                $current_post_id = $post->ID; // Store the current post ID

                echo 'Parsing post ', $current_post_id, "\n"; // phpcs:ignore

                $blocks = $parser->parse($post->post_content);

                if (!is_array($blocks)) {
                    throw new \Exception("Invalid block structure for post #" . $current_post_id);
                }

                foreach ($blocks as &$block) {
                    // Check if the block is valid.
                    if (!is_array($block)) {
                        continue;
                    }

                    // Check if the block has a 'blockName' key.
                    if (!isset($block['blockName'])) {
                        continue;
                    }

                    // Check if the block is a Social Media block.
                    if ($block['blockName'] !== Utils\Constants::BLOCK_SOCIAL_MEDIA) {
                        continue;
                    }

                    // Check if the embed is a Facebook page, in which case we do nothing.
                    if (isset($block['attrs']['embed_type']) && $block['attrs']['embed_type'] === 'facebook_page') {
                        continue;
                    }

                    // If not, we get the social media URL.
                    $social_media_url = $block['attrs']['social_media_url'] ?? null;

                    // If there's no social media url, we do nothing.
                    if (!$social_media_url) {
                        continue;
                    }

                    // Identify the source of the media (Facebook, Instagram, Twitter, X)
                    $source = self::identify_source($social_media_url);

                    // If there's no source, or if it's Facebook or Instagram, we do nothing.
                    // We only want to migrate Twitter/X embeds.
                    if (!$source || $source === self::SOURCE_FACEBOOK || $source === self::SOURCE_INSTAGRAM) {
                        continue;
                    }

                    // If the source is X, we want to change it to Twitter.
                    // Links from x.com don't work in the Embed block at the moment.
                    if ($source === self::SOURCE_X) {
                        $social_media_url = str_replace(
                            self::SOURCE_X . '.com',
                            self::SOURCE_TWITTER . '.com',
                            $social_media_url,
                        );
                    }

                    // Transform the Social Media block into an Embed block.
                    $block = self::transform_blocks($block, $social_media_url);
                }

                // Unset the reference to prevent potential issues.
                unset($block);

                // Serialize the blocks content.
                $new_content = serialize_blocks($blocks);

                // We don't update the block if the new content is the same as before.
                if ($post->post_content === $new_content) {
                    continue;
                }

                $post_update = array(
                    'ID' => $current_post_id,
                    'post_content' => $new_content,
                );

                // Update the post with the replaced blocks.
                $result = wp_update_post($post_update);

                if ($result === 0) {
                    throw new \Exception("There was an error trying to update the post #" . $current_post_id);
                }

                echo "Migration successful\n";
            }
        } catch (\Exception $e) {
            // Catch any exceptions and display the post ID if available
            echo "Migration wasn't executed for post ID: ", $current_post_id ?? 'unknown', "\n";
            echo $e->getMessage(), "\n";
        }
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
        foreach (self::SOURCE_PATTERNS as $value => $key) {
            if (preg_match($key, $url)) {
                return $value;
            }
        }
        return '';
    }

    /**
     * Transform a Social Media block into a group of blocks.
     * This group contains a header for the Social Media block title,
     * a paragraph for the Social Media block description,
     * and a final block (Embed) for the Twitter/X embed.
     *
     * @param array $block - A parsed Social Media block.
     * @param string $source - The source of the Social Media block (Twitter or X).
     * @param string $social_media_url - The Social Media URL.
     * @return array - The transformed block.
     */
    private static function transform_blocks(array $block, string $social_media_url): array
    {
        $block_title = array_key_exists("title", $block['attrs']) ? $block['attrs']['title'] : null;
        $block_description = array_key_exists("description", $block['attrs']) ? $block['attrs']['description'] : null;

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
        $provider = self::SOURCE_TWITTER;

        // IMPORTANT: DO NOT MODIFY THIS FORMAT!
        $html_content = '<figure class="wp-block-embed is-type-' . $type . ' is-provider-' . $provider . ' wp-block-embed-' . $provider . ' wp-embed-aspect-16-9 wp-has-aspect-ratio"><div class="wp-block-embed__wrapper">
        ' . $social_media_url . '
        </div></figure>';

        $block['blockName'] = Utils\Constants::BLOCK_EMBED;
        $block['attrs']['type'] = $type;
        $block['attrs']['providerNameSlug'] = $provider;
        $block['attrs']['metadata']['name'] = $provider;

        $block = self::set_shared_attrs($block, $social_media_url, $html_content);
        return $block;
    }

    /**
     * Set attributes that are common to all the blocks.
     *
     * @param array $block - A parsed Social Media block.
     * @param string $social_media_url - The Social Media URL.
     * @param string $html_content - The HTML content for the new block.
     * @return array - The updated block.
     */
    private static function set_shared_attrs(array $block, string $social_media_url, string $html_content): array
    {
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
