<?php

// phpcs:disable Generic.Files.LineLength.MaxExceeded

namespace P4\MasterTheme\Migrations;

use P4\MasterTheme\MigrationRecord;
use P4\MasterTheme\MigrationScript;
use P4\MasterTheme\BlockReportSearch\BlockSearch;
use WP_Block_Parser;

/**
 * Migrate Media block to Audio or Video blocks.
 */
class M033MigrateMediaBlockToAudioVideoBlock extends MigrationScript
{
    private const BLOCK_NAME = 'planet4-blocks/media-video';
    private const POST_TYPES = [ 'page', 'post', 'action', 'campaign' ];

    private const SOURCE_YOUTUBE = 'youtube';
    private const SOURCE_VIMEO = 'vimeo';
    private const SOURCE_SOUNDCLOUD = 'soundcloud';
    private const SOURCE_MP3 = 'mp3_file';
    private const SOURCE_VIDEO = 'other_video';

    private const SOURCE_PATTERNS = [
        self::SOURCE_YOUTUBE => '/^(https?\:\/\/)?(www\.)?(youtube\.com|youtu\.?be)\/.+$/i',
        self::SOURCE_VIMEO => '/^(https?\:\/\/)?(www\.)?vimeo\.com\/.+$/i',
        self::SOURCE_SOUNDCLOUD => '/^(https?\:\/\/)?(www\.)?(soundcloud\.com)\/.+$/i',
        self::SOURCE_MP3 => '/\.(mp3|wav)(\?.*)?$/i',
        self::SOURCE_VIDEO => '/\.mp4(\?.*)?$/i',
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
            // Get the list of posts using Media blocks.
            $posts = self::get_posts_using_media_blocks();

            // If there are no posts, abort.
            if (!$posts) {
                return;
            }

            foreach ($posts as $post) {
                // Get all the blocks of each post.
                $parser = new WP_Block_Parser();
                $blocks = $parser->parse($post->post_content);

                foreach ($blocks as &$block) {
                    // Check if the block is a Media block.
                    if ($block['blockName'] !== self::BLOCK_NAME) {
                        continue;
                    }

                    // If so, get the media URL.
                    $media_url = self::get_media_url($block);

                    // If there is no media URL the block is transformed into an empty embed-type block.
                    if (!$media_url) {
                        $block = self::transform_block_to_empty_embed($block);
                        continue;
                    }

                    // Identify the source of the media (Youtube, Vimeo, etc...)
                    $source = self::identify_source($media_url);

                    // If there is no source the block is transformed into an empty embed-type block.
                    if (!$source) {
                        $block = self::transform_block_to_empty_embed($block);
                        continue;
                    }

                    // Transform the media block into another block based on its source.
                    $block = self::transform_blocks($block, $source, $media_url);
                }

                // Unset the reference to prevent potential issues.
                unset($block);

                // Serialize the blocks content.
                $new_content = serialize_blocks($blocks);

                $post_update = array(
                    'ID' => $post->ID,
                    'post_content' => $new_content,
                );

                // Update the post with the replaced blocks.
                wp_update_post($post_update);
            }
        } catch (\ErrorException $e) {
            echo 'Error on post ', $post->ID, "\n";
            echo $e->getMessage(), "\n";
        }
    }
    // phpcs:enable SlevomatCodingStandard.Functions.UnusedParameter

    /**
     * Get all the posts using Media blocks.
     *
     * @return mixed - The posts using Media blocks or null if no posts are found.
     */
    private static function get_posts_using_media_blocks(): mixed
    {
        $search = new BlockSearch();

        $post_ids = $search->get_posts_with_block(self::BLOCK_NAME);

        if (empty($post_ids)) {
            return null;
        }

        $args = [
            'include' => $post_ids,
            'post_type' => self::POST_TYPES,
        ];

        $posts = get_posts($args) ?? [];

        if (empty($posts)) {
            return null;
        }

        return $posts;
    }

    /**
     * Get the media URL from a Media block.
     * If the value is not a URL then it is a Youtube ID.
     * In that case, the ID is turned into a full Youtube URL.
     *
     * @param array $block - A parsed Media block.
     * @return string - The media URL or null if no media was set.
     */
    private static function get_media_url(array $block): mixed
    {
        $media_url = $block['attrs']['youtube_id'] ?? $block['attrs']['media_url'] ?? null;

        if ($media_url && !filter_var($media_url, FILTER_VALIDATE_URL)) {
            $media_url = 'https://www.youtube.com/watch?v=' . $media_url;
        }

        return $media_url;
    }

    /**
     * Get the source of a Media block.
     * It can be Youtube, Vimeo, Souncloud, or an .mp3, .mp4 or .wav file.
     *
     * @param string $url - The Media URL used to identify the source.
     * @return string - The source of the media block or an empty string if no source was found.
     */
    private static function identify_source(string $url): mixed
    {
        foreach (self::SOURCE_PATTERNS as $value => $key) {
            if (preg_match($key, $url)) {
                return $value;
            }
        }
        return null;
    }

    /**
     * Transform a Media block into another block (embed, video or audio).
     *
     * @param array $block - A parsed Media block.
     * @param string $source - The source of the Media block.
     * @param string $media_url - The Media URL.
     * @return array - The transformed block.
     */
    private static function transform_blocks(array $block, string $source, string $media_url): array
    {
        if ($source === self::SOURCE_VIDEO) {
            return self::transform_block_to_video($block, $media_url);
        }
        if ($source === self::SOURCE_MP3) {
            return self::transform_block_to_audio($block, $media_url);
        }
        return self::transform_block_to_embed($block, $media_url, $source);
    }

    /**
     * Transform a Media block into an empty embed block.
     * This is useful when the Media block has no media URL
     * or its source cannot be identified.
     *
     * @param array $block - A parsed Media block.
     * @return array - The transformed block.
     */
    private static function transform_block_to_empty_embed(array $block): array
    {
        $block['blockName'] = 'core/embed';
        $block = self::set_shared_attrs($block, "", "");
        return $block;
    }

    /**
     * Transform a Media block into an embed block.
     *
     * @param array $block - A parsed Media block.
     * @param string $media_url - The Media URL.
     * @param string $provider - The source of the Media block.
     * @return array - The transformed block.
     */
    private static function transform_block_to_embed(array $block, string $media_url, string $provider): array
    {
        $type = $provider === self::SOURCE_YOUTUBE or $provider === self::SOURCE_VIMEO ? 'video' : 'rich';
        $caption = self::set_block_caption($block);

        // IMPORTANT: DO NOT MODIFY THIS FORMAT!
        $html_content = '<figure class="wp-block-embed is-type-' . $type . ' is-provider-' . $provider . ' wp-block-embed-' . $provider . ' wp-embed-aspect-16-9 wp-has-aspect-ratio"><div class="wp-block-embed__wrapper">
        ' . $media_url . '
        </div>' . ($caption ? '<figcaption class="wp-element-caption">' . $caption . '</figcaption>' : '') . '</figure>';

        $block['blockName'] = 'core/embed';
        $block['attrs']['type'] = $type;
        $block['attrs']['providerNameSlug'] = $provider;

        $block = self::set_shared_attrs($block, $media_url, $html_content);
        return $block;
    }

    /**
     * Transform a Media block into an audio block.
     *
     * @param array $block - A parsed Media block.
     * @param string $media_url - The Media URL.
     * @return array - The transformed block.
     */
    private static function transform_block_to_audio(array $block, string $media_url): array
    {
        $caption = self::set_block_caption($block);

        // IMPORTANT: DO NOT MODIFY THIS FORMAT!
        $html_content = '<figure class="wp-block-audio"><audio controls src="' . $media_url . '"></audio>' . ($caption ? '<figcaption class="wp-element-caption">' . $caption . '</figcaption>' : '') . '</figure>';

        $block['blockName'] = 'core/audio';

        $block = self::set_shared_attrs($block, $media_url, $html_content);
        return $block;
    }

    /**
     * Transform a Media block into a video block.
     *
     * @param array $block - A parsed Media block.
     * @param string $media_url - The Media URL.
     * @return array - The transformed block.
     */
    private static function transform_block_to_video(array $block, string $media_url): array
    {
        $poster = $block['attrs']['poster_url'] ?? "";
        $caption = self::set_block_caption($block);

        // IMPORTANT: DO NOT MODIFY THIS FORMAT!
        $html_content = '<figure class="wp-block-video"><video controls poster="' . $poster . '" src="' . $media_url . '"></video>' . ($caption ? '<figcaption class="wp-element-caption">' . $caption . '</figcaption>' : '') . '</figure>';

        $block['blockName'] = 'core/video';
        $block['attrs']['poster'] = $block['attrs']['poster_url'] ?? "";

        $block = self::set_shared_attrs($block, $media_url, $html_content);
        return $block;
    }

    /**
     * Set attributes that are common to all the blocks.
     *
     * @param array $block - A parsed Media block.
     * @param string $media_url - The Media URL.
     * @param string $html_content - The HTML content for the new block.
     * @return array - The updated block.
     */
    private static function set_shared_attrs(array $block, string $media_url, string $html_content): array
    {
        $block['attrs']['url'] = $media_url;
        $block['innerHTML'] = $html_content;
        $block['innerContent'][0] = $html_content;
        $block['innerBlocks'] = [];

        unset($block['attrs']['video_title']);
        unset($block['attrs']['description']);
        unset($block['attrs']['youtube_id']);

        return $block;
    }

    /**
     * Get the title and description of a Media block
     * and turn into a string to be used as the caption of another block.
     *
     * @param array $block - A parsed Media block.
     * @return mixed - The caption or null.
     */
    private static function set_block_caption(array $block): mixed
    {
        $block_title = array_key_exists("video_title", $block['attrs']) ? $block['attrs']['video_title'] : null;
        $block_description = array_key_exists("description", $block['attrs']) ? $block['attrs']['description'] : null;

        if ($block_title && $block_description) {
            return $block_title . " - " . $block_description;
        }
        if ($block_title && !$block_description) {
            return $block_title;
        }
        if (!$block_title && $block_description) {
            return $block_description;
        }
        return null;
    }
}
