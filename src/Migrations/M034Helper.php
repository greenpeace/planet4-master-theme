<?php
namespace P4\MasterTheme\Migrations;

/**
 * Migrate Media block to Audio or Video blocks.
 */
class M034Helper
{
    public const COLUMN_BLOCK = [
        'html' => '
  <div class="wp-block-columns">
  
  </div>
  ',
        'content' => array (
            0 => '
<div class="wp-block-columns">',
            1 => NULL,
            2 => '

',
            3 => NULL,
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
            1 => NULL,
            2 => '

',
            3 => NULL,
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
            1 => NULL,
            2 => '

',
            3 => NULL,
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
            1 => NULL,
            2 => '

',
            3 => NULL,
            4 => '

',
            5 => NULL,
            6 => '

',
            7 => NULL,
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
            1 => NULL,
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

    public const QUERY_BLOCK = [
        'html' => '
      <div class="wp-block-query posts-list p4-query-loop is-custom-layout-list">
      
      
      
      
      
      
      
      
      
      </div>
      ',
      'content' => array (
        0 => '
    <div class="wp-block-query posts-list p4-query-loop is-custom-layout-list">',
        1 => NULL,
        2 => '
    
    ',
        3 => NULL,
        4 => '
    
    ',
        5 => NULL,
        6 => '
    
    ',
        7 => NULL,
        8 => '
    
    ',
        9 => NULL,
        10 => '
    
    ',
        11 => NULL,
        12 => '</div>
    ',
    ),
    ];

    public const POST_TEMPLATE = [
        'html' => '
  
  ',
        'content' => array (
            0 => '
    ',
            1 => NULL,
            2 => '
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
            1 => NULL,
            2 => '
    
    ',
            3 => NULL,
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
            1 => NULL,
            2 => '
    
    ',
            3 => NULL,
            4 => '</div>
    ',
        ),
    ];
}
