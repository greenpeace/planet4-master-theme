<?php
namespace P4\MasterTheme\Migrations;

use P4\MasterTheme\MigrationRecord;
use P4\MasterTheme\MigrationScript;
use P4\MasterTheme\BlockReportSearch\BlockSearch;
use WP_Block_Parser;

/**
 * Migrate Media block to Audio or Video blocks.
 */
class M034MigrateCoversContentBlockToPostsListBlock extends MigrationScript
{
    private const BLOCK_NAME = 'planet4-blocks/covers';
    private const POSTS_LIST_BLOCK_NAME = 'planet4-blocks/posts-list';
    private const POST_LIST_CLASS_NAME = 'posts-list p4-query-loop is-custom-layout-list';
    private const EXCLUDED_BLOCK_TYPES = ['take-action', 'campaign'];
    private const POST_TYPES = [ 'page', 'post', 'action', 'campaign' ];

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
                    // Check if the block is a Cover block.
                    if ($block['blockName'] !== self::BLOCK_NAME) {
                        continue;
                    }

                    // Check if the Cover block type is Content.
                    // if (!array_key_exists("cover_type", $block['attrs'])) {
                    //     continue;
                    // }

                    // Check if the Cover block type is Content.
                    // if (in_array($block['attrs']['cover_type'], self::EXCLUDED_BLOCK_TYPES)) {
                    //     continue;
                    // }

                    $attrs = self::get_posts_list_block_attrs($block);

                    // Transform the cover block into a posts list block.
                    $block = self::create_query_block($attrs);
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

    private static function get_posts_list_block_attrs($existing_block)
    {
        $attrs = [];
        $attrs['title'] = $existing_block['attrs']['title'] ?? '';
        $attrs['description'] = $existing_block['attrs']['description'] ?? '';
        $attrs['cover_type'] = $existing_block['attrs']['cover_type'] ?? '';
        $attrs['tags'] = $existing_block['attrs']['tags'] ?? [];
        $attrs['posts'] = $existing_block['attrs']['posts'] ?? [];

        return $attrs;
    }

    private static function create_query_block($existing_block_attrs) 
    {
        return array (
            'blockName' => 'core/query',
            'attrs' => self::get_query_attrs(),
            'innerBlocks' => self::get_core_query_inner_blocks($existing_block_attrs),
            'innerHTML' => self::get_inner_html_for_blocks('query_block'),
            'innerContent' => self::get_inner_content_for_blocks('query_block'),
        );
    }

    private static function get_query_attrs() 
    {
        $query = [];
        $query['perPage'] = 3;
        $query['pages'] = 0;
        $query['offset'] = 0;
        $query['postType'] = 'post';
        $query['order'] = 'desc';
        $query['orderBy'] = 'date';
        $query['author'] = '';
        $query['search'] = '';
        $query['exclude'] = [1231]; // TO-DO: Replace the ID with current post ID.
        $query['sticky'] = '';
        $query['inherit'] = false;
        $query['postIn'] = [];
        $query['hasPassword'] = false;

        $layout = [];
        $layout['type'] = 'default';
        $layout['columnCount'] = 3;

        $block = [];
        $block['queryId'] = 0;
        $block['query'] = $query;
        $block['namespace'] = self::POSTS_LIST_BLOCK_NAME;
        $block['className'] = self::POST_LIST_CLASS_NAME;
        $block['layout'] = $layout;

        return $block;
    }

    private static function get_core_query_inner_blocks($existing_block_attrs)
    {
        $main_title = $existing_block_attrs['title'];
        $description = $existing_block_attrs['description'];

        return array (
            0 => self::get_head_group_block($main_title),
            1 => self::get_paragraph_block($description),
            2 => self::get_query_no_results_block(),
            3 => self::get_post_template(),
            4 => self::get_buttons_block(),
            5 => self::get_nav_links_block(),
        );
    }

    private static function get_head_group_block($title)
    {
        $block = [];
        $block['blockName'] = 'core/group';
        $block['attrs']['layout']['type'] = 'flex';
        $block['attrs']['layout']['justifyContent'] = 'space-between';
        $block['innerBlocks'][0] = self::get_heading_block($title);
        $block['innerBlocks'][1] = self::get_nav_links_block();
        $block['innerHTML'] = self::get_inner_html_for_blocks('head_group_block');
        $block['innerContent'] = self::get_inner_content_for_blocks('head_group_block');
        
        return $block;
    }

    private static function get_heading_block($title)
    {
        // Do not change the value format of this variable!
        $inner_html = '
  <h2 class="wp-block-heading">'.$title.'</h2>
  ';

        $block = [];
        $block['blockName'] = 'core/heading';
        $block['attrs']['lock']['move'] = true;
        $block['innerBlocks'] = [];
        $block['innerHTML'] = $inner_html;
        $block['innerContent'][0] = $inner_html;

        return $block;
    }
    
    private static function get_paragraph_block($description)
    {
        $block = [];
        $block['blockName'] = 'core/paragraph';
        $block['attrs']['placeholder'] = 'Enter description';
        $block['attrs']['lock']['move'] = true;
        $block['attrs']['style']['spacing']['margin']['top'] = '24px';
        $block['attrs']['style']['spacing']['margin']['bottom'] = '36px';

        $block['innerBlocks'] = [];
        $block['innerHTML'] = '
            <p style="margin-top:24px;margin-bottom:36px">' . $description . '</p>
        ';
        $block['innerContent'] = [
            '
            <p style="margin-top:24px;margin-bottom:36px">' . $description . '</p>
        ',
        ];

        return $block;
    }

    private static function get_button_block($classname, $text)
    {
        $block = [];
        $block['blockName'] = 'core/button';
        $block['attrs']['className'] = $classname;
        $block['innerBlocks'] = [];
        $block['innerHTML'] = '
            <div class="wp-block-button ' . $classname . '"><a class="wp-block-button__link wp-element-button">' . $text . '</a></div>
        ';
        $block['innerContent'] = [
            '
            <div class="wp-block-button ' . $classname . '"><a class="wp-block-button__link wp-element-button">' . $text . '</a></div>
        ',
        ];

        return $block;
    }

    private static function get_buttons_block()
    {
        $block = [];
        $block['blockName'] = 'core/buttons';
        $block['className'] = 'carousel-controls';
        $block['attrs']['lock']['move'] = true;
        $block['attrs']['layout']['type'] = 'flex';
        $block['attrs']['layout']['justifyContent'] = 'space-between';
        $block['attrs']['layout']['orientation'] = 'horizontal';
        $block['attrs']['layout']['flexWrap'] = 'nowrap';
        $block['innerBlocks'][0] = self::get_button_block('carousel-control-prev', 'Prev');
        $block['innerBlocks'][1] = self::get_button_block('carousel-control-next', 'Next');
        $block['innerHTML'] = self::get_inner_content_for_blocks('buttons_block');
        $block['innerContent'] = self::get_inner_content_for_blocks('buttons_block');

        return $block;
    }

    private static function get_post_template()
    {
        $block = [];
        $block['blockName'] = 'core/post-template';
        $block['attrs']['lock']['move'] = true;
        $block['attrs']['lock']['remove'] = true;
        $block['innerBlocks'][0] = self::get_post_data_column_block();
        $block['innerHTML'] = self::get_inner_html_for_blocks('post_template');
        $block['innerContent'] = self::get_inner_content_for_blocks('post_template');

        return $block;
    }

    private static function get_nav_links_block()
    {
        $block = [];
        $block['blockName'] = 'core/navigation-link';
        $block['attrs']['label'] = 'See all stories';
        $block['attrs']['url'] = 'http://www.planet4.test/news-stories/';
        $block['attrs']['className'] = 'see-all-link';
        $block['innerBlocks'] = [];
        $block['innerHTML'] = '';
        $block['innerContent'][0] = '';
        return $block;
    }
    
    private static function get_feat_image_block()
    {
        $block = [];
        $block['blockName'] = 'core/post-featured-image';
        $block['attrs']['isLink'] = true;
        $block = self::set_shared_attrs($block);
        return $block;
    }

    private static function get_post_title_block()
    {
        $block = [];
        $block['blockName'] = 'core/post-title';
        $block['attrs']['isLink'] = true;
        $block = self::set_shared_attrs($block);
        return $block;
    }

    private static function get_post_excerpt_block()
    {
        $block = [];
        $block['blockName'] = 'core/post-excerpt';
        $block['attrs'] = [];
        $block = self::set_shared_attrs($block);
        return $block;
    }

    private static function get_post_author_block()
    {
        $block = [];
        $block['blockName'] = 'core/post-author-name';
        $block['attrs']['isLink'] = true;
        $block = self::set_shared_attrs($block);
        return $block;
    }

    private static function get_post_date_block()
    {
        $block = [];
        $block['blockName'] = 'core/post-date';
        $block['attrs'] = [];
        $block = self::set_shared_attrs($block);
        return $block;
    }

    private static function get_post_terms_block($term, $separator)
    {
        $block = [];
        $block['blockName'] = 'core/post-terms';
        $block['attrs']['term'] = $term;
        $block['attrs']['separator'] = $separator;
        $block = self::set_shared_attrs($block);
        return $block;
    }

    private static function set_shared_attrs($block)
    {
        $block['innerHTML'] = '';
        $block['innerBlocks'] = [];
        $block['innerContent'] = [];
        return $block;
    }

    private static function get_query_no_results_block()
    {
        $block = [];
        $block['blockName'] = 'core/query-no-results';
        $block['attrs'] = [];
        $block['innerBlocks'][0] = self::get_query_no_results_paragraph_block();
        $block['innerHTML'] = self::get_inner_html_for_blocks('query-no-results');
        $block['innerContent'] = self::get_inner_content_for_blocks('query-no-results');

        return $block;
    }

    private static function get_query_no_results_paragraph_block()
    {
        $block = [];
        $block['blockName'] = 'core/paragraph';
        $block['attrs'] = [];
        $block['innerBlocks'] = [];
        $block['innerHTML'] = self::get_inner_html_for_blocks('query_no_results_paragraph_block');
        $block['innerContent'] = self::get_inner_content_for_blocks('query_no_results_paragraph_block');

        return $block;
    }

    private static function get_post_data_column_block()
    {
        $block = [];
        $block['blockName'] = 'core/columns';
        $block['attrs'] = [];
        $block['innerBlocks'] = [
            self::get_feat_image_block(),
            self::get_post_data_group_block(),
        ];
        $block['innerHTML'] = self::get_inner_html_for_blocks('post_data_column_block');
        $block['innerContent'] = self::get_inner_content_for_blocks('post_data_column_block');

        return $block;
    }

    private static function get_post_terms_group_block()
    {
        $block = [];
        $block['blockName'] = 'core/group';
        $block['attrs']['layout']['type'] = 'flex';
        $block['innerBlocks'][0] = self::get_post_terms_block('category', ' | ');
        $block['innerBlocks'][1] = self::get_post_terms_block('post_tag', ' ');
        $block['innerHTML'] = self::get_inner_html_for_blocks('post_terms_group_block');
        $block['innerContent'] = self::get_inner_content_for_blocks('post_terms_group_block');
        return $block;
    }

    private static function get_posts_list_meta_group_block()
    {
        $block = [];
        $block['blockName'] = 'core/group';
        $block['attrs']['className'] = 'posts-list-meta';
        $block['innerBlocks'][0] = self::get_post_author_block();
        $block['innerBlocks'][1] = self::get_post_date_block();
        $block['innerHTML'] = self::get_inner_html_for_blocks('posts_list_meta_group_block');
        $block['innerContent'] = self::get_inner_content_for_blocks('posts_list_meta_group_block');
        return $block;
    }

    private static function get_post_data_group_block()
    {
        $block = [];
        $block['blockName'] = 'core/group';
        $block['attrs'] = [];
        $block['innerBlocks'] = [
            self::get_post_terms_group_block(),
            self::get_post_title_block(),
            self::get_post_excerpt_block(),
            self::get_posts_list_meta_group_block(),
        ];
        $block['innerHTML'] = self::get_inner_html_for_blocks('post_data_group_block');
        $block['innerContent'] = self::get_inner_content_for_blocks('post_data_group_block');

        return $block;
    }

    private static function get_inner_html_for_blocks($block)
    {
        if ($block === 'post_data_column_block') {
            return '
  <div class="wp-block-columns">
  
  </div>
  ';
        }
        if ($block === 'post_terms_group_block') {
            return '
<div class="wp-block-group">

</div>
';
        }
        if ($block === 'posts_list_meta_group_block') {
            return '
<div class="wp-block-group posts-list-meta">

</div>
';
        }
        if ($block === 'post_data_group_block') {
            return '
<div class="wp-block-group">





</div>
';
        }
        if ($block === 'query_no_results_block') {
            return '
    
    ';
        }
        if ($block === 'query_no_results_paragraph_block') {
            return '
<p>No posts found. (This default text can be edited)</p>
';
        }
        if ($block === 'query_block') {
            return '
      <div class="wp-block-query posts-list p4-query-loop is-custom-layout-list">
      
      
      
      
      
      
      
      
      
      </div>
      ';
        }
        if ($block === 'post_template') {
            return '
  
  ';
        }
        if ($block === 'head_group_block') {
            return '
  <div class="wp-block-group">
  
  </div>
  ';
        }
        if ($block === 'buttons_block') {
            return '
  <div class="wp-block-buttons carousel-controls">
  
  </div>
  ';
        }
    }

    private static function get_inner_content_for_blocks($block)
    {
        if ($block === 'post_data_column_block') {
            return array (
                0 => '
    <div class="wp-block-columns">',
                1 => NULL,
                2 => '
    
    ',
                3 => NULL,
                4 => '</div>
    ',
              );
        }
        if ($block === 'post_terms_group_block') {
            return array (
                0 => '
  <div class="wp-block-group">',
                1 => NULL,
                2 => '
  
  ',
                3 => NULL,
                4 => '</div>
  ',
              );
        }
        if ($block === 'posts_list_meta_group_block') {
            return array (
                0 => '
  <div class="wp-block-group posts-list-meta">',
                1 => NULL,
                2 => '
  
  ',
                3 => NULL,
                4 => '</div>
  ',
              );
        }
        if ($block === 'post_data_group_block') {
            return array (
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
              );
        }
        if ($block === 'query_no_results_block') {
            return array (
                0 => '
        ',
                1 => NULL,
                2 => '
        ',
                );
        }
        if ($block === 'query_no_results_paragraph_block') {
            return array (
                0 => '
    <p>No posts found. (This default text can be edited)</p>
    ',
                );
        }
        if ($block === 'query_block') {
            return array (
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
            );
        }
        if ($block === 'post_template') {
            return array (
                0 => '
        ',
                1 => NULL,
                2 => '
        ',
              );
        }
        if ($block === 'head_group_block') {
            return array (
                0 => '
        <div class="wp-block-group">',
                1 => NULL,
                2 => '
        
        ',
                3 => NULL,
                4 => '</div>
        ',
              );
        }
        if ($block === 'buttons_block') {
            return array (
                0 => '
        <div class="wp-block-buttons carousel-controls">',
                1 => NULL,
                2 => '
        
        ',
                3 => NULL,
                4 => '</div>
        ',
              );
        }
    }
}
