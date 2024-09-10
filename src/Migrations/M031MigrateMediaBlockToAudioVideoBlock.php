<?php

// phpcs:disable Generic.Files.LineLength.MaxExceeded

namespace P4\MasterTheme\Migrations;

use P4\MasterTheme\MigrationRecord;
use P4\MasterTheme\MigrationScript;
use WP_Block_Parser;

/**
 * Migrate Media block to Audio or Video blocks.
 */
class M031MigrateMediaBlockToAudioVideoBlock extends MigrationScript
{
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
            $posts = Utils\Functions::get_posts_using_specific_block(Utils\Constants::BLOCK_MEDIA_VIDEO, Utils\Constants::ALL_POST_TYPES);

            // If there are no posts, abort.
            if (!$posts) {
                return;
            }

            echo "Media block migration in progress...\n"; // phpcs:ignore

            $parser = new WP_Block_Parser();

            // Variable to store the current post ID
            $current_post_id = null;

            // Get all the blocks of each post.
            foreach ($posts as $post) {
                if (empty($post->post_content)) {
                    continue;
                }

                $current_post_id = $post->ID; // Store the current post ID

                echo 'Parsing post ', $post->ID, "\n"; // phpcs:ignore

                $blocks = $parser->parse($post->post_content);

                foreach ($blocks as &$block) {
                    // Check if the block is a Media block.
                    if ($block['blockName'] !== Utils\Constants::BLOCK_MEDIA_VIDEO) {
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
                $result = wp_update_post($post_update);

                if ($result === 0) {
                    throw new \Exception("There was an error trying to update the post #" . $post->ID);
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

        // Replace special characters in the url.
        $media_url = strtr($media_url, '&', 'u0026');

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
     * Transform a Media block into a group of blocks.
     * This group contains a header for the media block title,
     * a paragraph for the media block description,
     * and a final block (embed, video or audio) for the media source.
     *
     * @param array $block - A parsed Media block.
     * @param string $source - The source of the Media block.
     * @param string $media_url - The Media URL.
     * @return array - The transformed block.
     */
    private static function transform_blocks(array $block, string $source, string $media_url): array
    {
        $block_title = array_key_exists("video_title", $block['attrs']) ? $block['attrs']['video_title'] : null;
        $block_description = array_key_exists("description", $block['attrs']) ? $block['attrs']['description'] : null;

        $block = [];
        $block['blockName'] = Utils\Constants::BLOCK_GROUP;
        $block['attrs']['metadata']['name'] = 'Media Group';

        $block['innerBlocks'] = [];
        $block['innerBlocks'][0] = $block_title ? self::get_heading_block($block_title) : null;
        $block['innerBlocks'][1] = $block_description ? self::get_paragraph_block($block_description) : null;
        $block['innerBlocks'][3] = self::get_media_block($source, $block, $media_url);

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
     * Transform a Media block into an embed, video or audio block based on its source.
     *
     * @param string $source - The source of the Media block.
     * @param array $block - A parsed Media block.
     * @param string $media_url - The Media URL.
     * @return array - The transformed block.
     */
    private static function get_media_block(string $source, array $block, string $media_url): array
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
        $block['blockName'] = Utils\Constants::BLOCK_EMBED;
        $block['attrs']['metadata']['name'] = 'Embed';
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

        // IMPORTANT: DO NOT MODIFY THIS FORMAT!
        $html_content = '<figure class="wp-block-embed is-type-' . $type . ' is-provider-' . $provider . ' wp-block-embed-' . $provider . ' wp-embed-aspect-16-9 wp-has-aspect-ratio"><div class="wp-block-embed__wrapper">
        ' . $media_url . '
        </div></figure>';

        $block['blockName'] = Utils\Constants::BLOCK_EMBED;
        $block['attrs']['type'] = $type;
        $block['attrs']['providerNameSlug'] = $provider;
        $block['attrs']['metadata']['name'] = $provider;

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
        // IMPORTANT: DO NOT MODIFY THIS FORMAT!
        $html_content = '<figure class="wp-block-audio"><audio controls src="' . $media_url . '"></audio></figure>';

        $block['blockName'] = Utils\Constants::BLOCK_AUDIO;
        $block['attrs']['metadata']['name'] = 'Audio';

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

        // IMPORTANT: DO NOT MODIFY THIS FORMAT!
        $html_content = '<figure class="wp-block-video"><video controls poster="' . $poster . '" src="' . $media_url . '"></video></figure>';

        $block['blockName'] = Utils\Constants::BLOCK_VIDEO;
        $block['attrs']['metadata']['name'] = 'Video';
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
