<?php

namespace P4\MasterTheme;

/**
 * Class BlockRenderOverrides
 */
class BlockRenderOverrides
{
    public function __construct()
    {

        add_filter('render_block', [$this, 'apply_wpautop_to_non_block_content'], 10, 2);
        add_filter('render_block', [$this, 'replace_core_author_block'], 10, 2);
        add_filter('render_block_core/post-template', [$this, 'remove_empty_elements_from_template'], 10, 1);
    }

    /**
     * Apply wpautop to non-block content.
     * @link https://wordpress.stackexchange.com/q/321662/26317
     */
    public function apply_wpautop_to_non_block_content(string $block_content, array $block): string
    {
        if (is_null($block['blockName'])) {
            return wpautop($block_content);
        }
        return $block_content;
    }

    /**
     * Update Core Post Author block with P4 custom block.
     * P4 custom block has author override value.
     */
    public function replace_core_author_block(string $block_content, array $block): string
    {
        if ($block['blockName'] === 'core/post-author-name') {
            return render_block(['blockName' => 'p4/post-author-name']);
        }
        return $block_content;
    }

    /**
     * Remove from the Core Post Template the Taxonomy Breadcrumb inner block if empty.
     * Remove from the Core Post Template the Post Terms inner block if empty.
     */
    public function remove_empty_elements_from_template(string $block_content): string
    {
        $block_content = preg_replace(
            '/<div class="wrapper-post-term">\s*<\/div>/',
            '',
            $block_content
        );
        $block_content = preg_replace(
            '/<div class="wrapper-post-tag">\s*<\/div>/',
            '',
            $block_content
        );
        return $block_content;
    }
}
