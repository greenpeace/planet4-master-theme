<?php

// phpcs:disable Generic.Files.LineLength.MaxExceeded

namespace P4\MasterTheme\Migrations\Utils;

/**
 * This class includes a set of functions and constant values needed to perform
 * the migration of Covers block of type Content into Posts List blocks.
 *
 * IMPORTANT!
 * The format of the html code in the constants and the return statements
 * must not be changed. Any small change can affect the way the blocks are rendered
 * and make them stop working.
 */
class M034Helper
{
    /**
     * Get the html content for a heading block.
     *
     * @param string $text - The text to be included.
     * @return string - The html content.
     */
    public static function get_heading_block_content(string $text): string
    {
        return '
  <h2 class="wp-block-heading">' . $text . '</h2>
  ';
    }

    /**
     * Get the html content for a paragraph block.
     *
     * @param string $text - The text to be included.
     * @return string - The html content.
     */
    public static function get_paragraph_block_content(string $text): string
    {
        return '
            <p style="margin-top:24px;margin-bottom:36px">' . $text . '</p>
        ';
    }

    /**
     * Get the html content for a button block.
     *
     * @param string $classname - The button class name.
     * @param string $text - The button label.
     * @return string - The html content.
     */
    public static function get_button_block_content(string $classname, string $text): string
    {
        return '
            <div class="wp-block-button ' . $classname . '"><a class="wp-block-button__link wp-element-button">' . $text . '</a></div>
        ';
    }

    /**
     * Get the html content for a query block.
     *
     * @param string $classname - The block class name.
     * @return string - The html content.
     */
    public static function get_query_block_html_content(string $classname): string
    {
        return '
      <div class="wp-block-query posts-list p4-query-loop is-custom-layout-' . $classname . '">









      </div>
      ';
    }

    /**
     * Get the inner content for a query block.
     *
     * @param string $classname - The block class name.
     * @return array - The inner content.
     */
    public static function get_query_block_inner_content(string $classname): array
    {
        return array (
            0 => '
        <div class="wp-block-query posts-list p4-query-loop is-custom-layout-' . $classname . '">',
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
            8 => '

        ',
            9 => null,
            10 => '

        ',
            11 => null,
            12 => '</div>
        ',
        );
    }

    public const COLUMN_BLOCK = [
        'html' => '
  <div class="wp-block-columns">

  </div>
  ',
        'content' => array (
            0 => '
<div class="wp-block-columns">',
            1 => null,
            2 => '

',
            3 => null,
            4 => '</div>
',
          ),
    ];

    public const POST_TERMS_GROUP_BLOCK = [
        'html' => '
<div class="wp-block-group">

</div>
',
        'content' => array (
            0 => '
<div class="wp-block-group">',
            1 => null,
            2 => '

',
            3 => null,
            4 => '</div>
',
        ),
    ];

    public const POSTS_LIST_META_GROUP_BLOCK = [
        'html' => '
<div class="wp-block-group posts-list-meta">

</div>
',
        'content' => array (
            0 => '
<div class="wp-block-group posts-list-meta">',
            1 => null,
            2 => '

',
            3 => null,
            4 => '</div>
',
        ),
    ];

    public const POST_DATA_GROUP_BLOCK = [
        'html' => '
<div class="wp-block-group">





</div>
',
        'content' => array (
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
        ),
    ];

    public const QUERY_NO_RESULTS_BLOCK = [
        'html' => '

    ',
        'content' => array (
            0 => '
    ',
            1 => null,
            2 => '
    ',
        ),
    ];

    public const QUERY_NO_RESULTS_PARAGRAPH_BLOCK = [
        'html' => '
<p>No posts found. (This default text can be edited)</p>
',
        'content' => array (
            0 => '
<p>No posts found. (This default text can be edited)</p>
',
        ),
    ];

    public const HEAD_GROUP_BLOCK = [
        'html' => '
  <div class="wp-block-group">

  </div>
  ',
        'content' => array (
            0 => '
    <div class="wp-block-group">',
            1 => null,
            2 => '

    ',
            3 => null,
            4 => '</div>
    ',
        ),
    ];

    public const BUTTONS_BLOCK = [
        'html' => '
  <div class="wp-block-buttons carousel-controls">

  </div>
  ',
        'content' => array (
            0 => '
    <div class="wp-block-buttons carousel-controls">',
            1 => null,
            2 => '

    ',
            3 => null,
            4 => '</div>
    ',
        ),
    ];
}
