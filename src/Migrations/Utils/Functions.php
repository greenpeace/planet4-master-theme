<?php

namespace P4\MasterTheme\Migrations\Utils;

use P4\MasterTheme\BlockReportSearch\BlockSearch;
use P4\MasterTheme\BlockReportSearch\Block\Query\Parameters;

/**
 * Utility functions for the migration scripts.
 */
class Functions
{
    /**
     * Get all the posts using a specific type of block.
     *
     * @param string $block_name - The name of the block to be searched.
     * @param array $post_types - The list of post types to look for.
     * @param array $post_status - The list of post status to look for.
     *
     * @return mixed - The posts using a type of block or null if no posts are found.
     */
    //phpcs:ignore Generic.Files.LineLength.MaxExceeded
    public static function get_posts_using_specific_block(string $block_name, array $post_types, ?array $post_status = null): mixed
    {
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
    public static function create_new_block(string $name, array $attrs, array $inner_blocks, string $inner_html, array $inner_content): array
    {
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
     * @param string $text - The paragraph text.
     * @return array - The new paragraph block.
     */
    public static function create_block_paragraph(array $attrs, $content): array
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
        $classname = isset($attrs['verticalAlignment']) ? 'wp-block-column is-vertically-aligned-center' : 'wp-block-column';

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

        $html = '
            <div class="wp-block-button ' . $classname . '"><a target="_self" href="' . $link . '" class="wp-block-button__link wp-element-button">' . $text . '</a></div>
        ';

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
        $classname = isset($attrs['className']) ? 'wp-block-buttons ' . $attrs['className'] : 'wp-block-buttons';

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

    public static function create_media_text_paragraph(array $attrs, array $inner_blocks, string $img_url, string $img_id): array
    {
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

        return self::create_new_block(
            Constants::BLOCK_MEDIA_TEXT,
            $attrs,
            $inner_blocks,
            $html[0] . $html[6],
            $html
        );
    }
}
