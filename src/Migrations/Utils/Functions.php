<?php

namespace P4\MasterTheme\Migrations\Utils;

use WP_Block_Parser;
use P4\MasterTheme\BlockReportSearch\BlockSearch;
use P4\MasterTheme\BlockReportSearch\Block\Query\Parameters;

/**
 * Utility functions for the migration scripts.
 */
class Functions
{
    /**
     * Execute a block migration.
     *
     * @param callable $block_check_callback - Callback function to check if block is valid for migration.
     * @param callable $record block_transformation_callback - Callback function to transform a block.
     * phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter -- interface implementation
     */
    public static function execute_block_migration(
        string $block_name,
        callable $block_check_callback,
        callable $block_transformation_callback
    ): void {
        try {
            // Get the list of posts using split-two-columns block.
            $posts = self::get_posts_using_specific_block(
                $block_name,
                Constants::ALL_POST_TYPES,
                Constants::POST_STATUS_LIST
            );

            // If there are no posts, abort.
            if (!$posts) {
                return;
            }

            echo $block_name . " migration in progress...\n"; // phpcs:ignore

            $parser = new WP_Block_Parser();

            foreach ($posts as $post) {
                if (empty($post->post_content)) {
                    continue;
                }

                $current_post_id = $post->ID; // Store the current post ID

                echo 'Parsing post ', $current_post_id, "\n"; // phpcs:ignore

                // Get all the blocks of each post.
                $blocks = $parser->parse($post->post_content);

                if (!is_array($blocks)) {
                    throw new \Exception("Invalid block structure for post #" . $current_post_id);
                }

                foreach ($blocks as &$block) {
                    if (!$block_check_callback($block)) {
                        continue;
                    }
                    $block = $block_transformation_callback($block);
                }

                // Unset the reference to prevent potential issues.
                unset($block);

                // Serialize the blocks content.
                $new_content = serialize_blocks($blocks);

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
        } catch (\ErrorException $e) {
            // Catch any exceptions and display the post ID if available
            echo "Migration wasn't executed for post ID: ", $current_post_id ?? 'unknown', "\n";
            echo $e->getMessage(), "\n";
        }
    }
    // phpcs:enable SlevomatCodingStandard.Functions.UnusedParameter

    /**
     * Get all the posts using a specific type of block.
     *
     * @param string $block_name - The name of the block to be searched.
     * @param array $post_types - The list of post types to look for.
     * @param array $post_status - The list of post status to look for.
     * @return mixed - The posts using a type of block or null if no posts are found.
     */
    public static function get_posts_using_specific_block(
        string $block_name,
        array $post_types,
        ?array $post_status = null
    ): mixed {
        $search = new BlockSearch();
        $params = ( new Parameters() )->with_name($block_name);

        if ($post_status) {
            $params = $params->with_post_status($post_status);
        }

        $post_ids = $search->get_posts($params);

        if (empty($post_ids)) {
            return null;
        }

        $args = [
            'include' => $post_ids,
            'post_type' => $post_types,
        ];

        if ($post_status) {
            $args['post_status'] = 'any';
        }

        $posts = get_posts($args) ?? [];

        if (empty($posts)) {
            return null;
        }

        return $posts;
    }

    /**
     * Generate the code for the block attributes "innerHtml" and "innerContent".
     *
     * IMPORTANT!
     * The format of the html code must not be changed.
     * Any small change can affect the way the blocks are rendered and make them stop working.
     *
     * @param string $classname - A CSS classname.
     * @return array - The code for the block attributes.
     */
    public static function generate_html_content(string $classname): array
    {
        $html = "
  <div class=\"$classname\">

  </div>
  ";

        $content = [
            0 => "
<div class=\"$classname\">",
            1 => null,
            2 => "
",
            3 => null,
            4 => '</div>
',
        ];

        return [
            'html' => $html,
            'content' => $content,
        ];
    }

    /**
     * Create a new block.
     *
     * @param string $name - The name of the block.
     * @param array $attrs - The attributes of the block.
     * @param array $inner_blocks - The internal blocks.
     * @param string $inner_html - The internal HTML.
     * @param array $inner_content - The internal content.
     * @return array - The new block.
     */
    public static function create_new_block(
        string $name,
        array $attrs,
        array $inner_blocks,
        string $inner_html,
        array $inner_content
    ): array {
        $block = [];
        $block['blockName'] = $name;
        $block['attrs'] = $attrs;
        $block['innerBlocks'] = $inner_blocks;
        $block['innerHTML'] = $inner_html;
        $block['innerContent'] = $inner_content;
        return $block;
    }

    /**
     * Create a new heading block.
     *
     * @param array $attrs - The attributes of the block.
     * @param string $text - The heading text.
     * @return array - The new heading block.
     */
    public static function create_block_heading(array $attrs, string $text): array
    {
        $level = isset($attrs['level']) ? strval($attrs['level']) : '2';
        $html = '<h' . $level . ' class="wp-block-heading">';
        $html .= $text;
        $html .= '</h' . $level . '>';

        return self::create_new_block(
            Constants::BLOCK_HEADING,
            $attrs,
            [],
            $html,
            [$html]
        );
    }

    /**
     * Create a new paragraph block.
     *
     * @param array $attrs - The attributes of the block.
     * @param string $content - The paragraph text.
     * @return array - The new paragraph block.
     */
    public static function create_block_paragraph(array $attrs, string $content): array
    {
        $html = '<p>' . $content . '</p>';

        return self::create_new_block(
            Constants::BLOCK_PARAGRAPH,
            $attrs,
            [],
            $html,
            [$html]
        );
    }

    /**
     * Create a new column block.
     *
     * @param array $attrs - The attributes of the block.
     * @param array $inner_blocks - The inner blocks.
     * @return array - The new column block.
     */
    public static function create_block_single_column(array $attrs, array $inner_blocks): array
    {
        $classname =
            isset($attrs['verticalAlignment']) ?
            'wp-block-column is-vertically-aligned-center' :
            'wp-block-column';

        $html_content = self::generate_html_content($classname);

        return self::create_new_block(
            Constants::BLOCK_SINGLE_COLUMN,
            $attrs,
            $inner_blocks,
            $html_content['html'],
            $html_content['content']
        );
    }

    /**
     * Create a new columns block.
     *
     * @param array $attrs - The attributes of the block.
     * @param array $inner_blocks - The inner blocks.
     * @return array - The new columns block.
     */
    public static function create_block_columns(array $attrs, array $inner_blocks): array
    {
        $html_content = self::generate_html_content("wp-block-columns");

        return self::create_new_block(
            Constants::BLOCK_COLUMNS,
            $attrs,
            $inner_blocks,
            $html_content['html'],
            $html_content['content']
        );
    }

    /**
     * Create a new button block.
     *
     * @param array $attrs - The attributes of the block.
     * @param string $text - The button label.
     * @param string $link - The button link.
     * @return array - The new button block.
     */
    public static function create_block_single_button(array $attrs, string $text, string $link): array
    {
        $classname = isset($attrs['className']) ? $attrs['className'] : '';

        // phpcs:disable Generic.Files.LineLength.MaxExceeded
        $html = '
            <div class="wp-block-button ' . $classname . '"><a target="_self" href="' . $link . '" class="wp-block-button__link wp-element-button">' . $text . '</a></div>
        ';
        // phpcs:enable Generic.Files.LineLength.MaxExceeded

        return self::create_new_block(
            Constants::BLOCK_SINGLE_BUTTON,
            $attrs,
            [],
            $html,
            [$html]
        );
    }

    /**
     * Create a new buttons block.
     *
     * @param array $attrs - The attributes of the block.
     * @param array $inner_blocks - The inner blocks.
     * @return array - The new buttons block.
     */
    public static function create_block_buttons(array $attrs, array $inner_blocks): array
    {
        $classname =
            isset($attrs['className']) ?
            'wp-block-buttons ' . $attrs['className'] :
            'wp-block-buttons';

        $html_content = self::generate_html_content($classname);

        return self::create_new_block(
            Constants::BLOCK_BUTTONS,
            $attrs,
            $inner_blocks,
            $html_content['html'],
            $html_content['content']
        );
    }

    /**
     * Create a new media & text block.
     *
     * @param array $attrs - The attributes of the block.
     * @param array $inner_blocks - The inner blocks.
     * @param string $img_url - The image URL.
     * @param string $img_id - The image ID.
     * @return array - The new media & text block.
     */

    public static function create_media_text_block(
        array $attrs,
        array $inner_blocks,
        string $img_url,
        int $img_id
    ): array {
        // phpcs:disable Generic.Files.LineLength.MaxExceeded
        $html = array (
           0 => '
        <div class="wp-block-media-text is-stacked-on-mobile"><figure class="wp-block-media-text__media"><img src="' . $img_url . '" alt="" class="wp-image-' . $img_id . ' size-full"/></figure><div class="wp-block-media-text__content">',
           1 => null,
           2 => '

        ',
           3 => null,
           4 => '

        ',
           5 => null,
           6 => '</div></div>
        ',
        );
        // phpcs:enable Generic.Files.LineLength.MaxExceeded

        return self::create_new_block(
            Constants::BLOCK_MEDIA_TEXT,
            $attrs,
            $inner_blocks,
            $html[0] . $html[6],
            $html
        );
    }

    /**
     * Create a new Embed block.
     *
     * @param string $social_media_url - The Social Media URL.
     * @param string $type - The embed type.
     * @param $provider - The embed provider.
     *
     * @return array - The new Embed block.
     */

    public static function create_embed_block(
        string $social_media_url,
        string $type,
        string $provider,
    ): array {
        // phpcs:disable Generic.Files.LineLength.MaxExceeded
        $html_content = '<figure class="wp-block-embed is-type-' . $type . ' is-provider-' . $provider . ' wp-block-embed-' . $provider . '"><div class="wp-block-embed__wrapper">
        ' . $social_media_url . '
        </div></figure>';
        // phpcs:enable Generic.Files.LineLength.MaxExceeded

        $attrs = [];
        $attrs['type'] = $type;
        $attrs['providerNameSlug'] = $provider;
        $attrs['metadata']['name'] = $provider;
        $attrs['url'] = $social_media_url;
        $block['innerHTML'] = $html_content;
        $block['innerContent'][0] = $html_content;

        return self::create_new_block(
            Constants::BLOCK_EMBED,
            $attrs,
            [],
            $html_content,
            [$html_content],
        );
    }

    /**
     * Create a new Group block.
     *
     * @param array $inner_blocks - The block's inner blocks.
     * @param array $attrs - The block's attributes.
     *
     * @return array - The new Group block.
     */

    public static function create_group_block(array $inner_blocks, array $attrs): array
    {
        // IMPORTANT: DO NOT MODIFY THIS FORMAT!
        $inner_html =
        '<div class="wp-block-group">





        </div>';

        // IMPORTANT: DO NOT MODIFY THIS FORMAT!
        $inner_content = array (
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

        return self::create_new_block(
            Constants::BLOCK_GROUP,
            $attrs,
            $inner_blocks,
            $inner_html,
            $inner_content,
        );
    }
}
