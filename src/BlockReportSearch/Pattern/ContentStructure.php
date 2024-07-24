<?php

/**
 * Table displaying patterns usage
 */

namespace P4\MasterTheme\BlockReportSearch\Pattern;

use WP_Block_Parser;

/**
 * Prepare pattern usage, using native WordPress table
 */
class ContentStructure
{
    private WP_Block_Parser $parser;

    private string $content;

    private array $tree;

    private string $signature;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->parser = new WP_Block_Parser();
    }

    /**
     * Parse content as structure.
     *
     * @param string $content Post content.
     */
    public function parse_content(string $content): void
    {
        $this->content = $content;
        $this->make_content_tree($this->content);
        $this->make_structure_signature($this->tree);
    }

    /**
     * @return string|null Content
     */
    public function get_content(): ?string
    {
        return $this->content;
    }

    /**
     * @return array Content tree
     */
    public function get_content_tree(): array
    {
        return $this->tree;
    }

    /**
     * @return string Content signature
     */
    public function get_content_signature(): string
    {
        return $this->signature;
    }

    /**
     * Make tree structure from content.
     *
     * @param string $content Post content.
     */
    public function make_content_tree(string $content): void
    {
        $parsed = $this->parser->parse($content);
        $tree = [];
        while (! empty($parsed)) {
            /** @var array $block */
            $block = array_shift($parsed);
            $tree[] = $this->make_tree($block);
        }

        $this->tree = array_values(array_filter($tree));
    }

    /**
     * Make signature from tree structure.
     *
     * @param array $tree Tree.
     */
    public function make_structure_signature(array $tree): void
    {
        $this->normalize_tree_for_signature($tree);
        $signature = json_encode($tree); // phpcs:ignore WordPress.WP.AlternativeFunctions

        $this->signature = trim($signature, '[]');
    }

    /**
     * Normalize tree to remove duplications.
     *
     * @param array $tree Tree.
     */
    public function normalize_tree_for_signature(array &$tree): void
    {
        // No classes in content signature.
        if (isset($tree['classes'])) {
            unset($tree['classes']);
        }

        foreach ($tree as $key => &$node) {
            // No classes in content signature.
            if (isset($node['classes'])) {
                unset($node['classes']);
            }

            if (empty($node['children']) || ! is_array($node['children'])) {
                continue;
            }

            if ('core/columns' === $node['name']) {
                $columns_count = count($node['children']);
                $unique_columns = array_unique($node['children'], \SORT_REGULAR);
                $unique_count = count($unique_columns);

                if (1 === $unique_count && $columns_count > $unique_count) {
                    $node['children'] = $unique_columns;
                }
            }

            if ('core/group' === $node['name']) {
                $subgroups_count = count($node['children']);
                $unique_subgroups = array_unique($node['children'], \SORT_REGULAR);
                $unique_count = count($unique_subgroups);

                if (1 === $unique_count && $subgroups_count > $unique_count) {
                    $node['children'] = $unique_subgroups;
                }
            }

            if (empty($node['children'])) {
                continue;
            }

            $this->normalize_tree_for_signature($node['children']);
        }
    }

    /**
     * Make blocks tree
     *
     * @param array $block Block.
     *
     * @return array|null Tree representation of block content.
     */
    public function make_tree(array $block): ?array
    {
        if (empty($block['blockName'])) {
            return null;
        }

        return [
            'name' => $block['blockName'],
            'classes' => array_filter(
                explode(' ', $block['attrs']['className'] ?? '')
            ),
            'children' => empty($block['innerBlocks'])
                ? null
                : array_values(
                    array_filter(
                        array_map(
                            fn ($b) => $this->make_tree($b),
                            $block['innerBlocks']
                        )
                    )
                ),
        ];
    }
}
