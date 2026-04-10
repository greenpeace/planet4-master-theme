<?php

namespace P4\MasterTheme\Migrations;

use P4\MasterTheme\MigrationRecord;
use P4\MasterTheme\MigrationScript;

/**
 * Refactor Actions List blocks to use the stretched link functionality.
 * This allows us to simplify the block and get rid of some JS code.
 * At the same time, we remove the links from the featured image and the category.
 */
class M063ActionsListCarouselAccessibility extends MigrationScript
{
    /**
     * Perform the actual migration.
     *
     * @param MigrationRecord $record Information on the execution, can be used to add logs.
     * phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter -- interface implementation
     */
    public static function execute(MigrationRecord $record): void
    {
        $check_is_valid_block = function ($block) {
            return self::check_is_valid_block($block);
        };

        $transform_block = function ($block) {
            return self::transform_block($block);
        };

        Utils\Functions::execute_block_migration(
            Utils\Constants::BLOCK_QUERY,
            $check_is_valid_block,
            $transform_block,
        );
    }
    // phpcs:enable SlevomatCodingStandard.Functions.UnusedParameter

    /**
     * Check whether a block is an Actions List block.
     *
     * @param array $block - A block data array.
     */
    private static function check_is_valid_block($block): bool
    {
        if (!is_array($block)) {
            return false;
        }

        if (!isset($block['blockName']) || !isset($block['attrs']['namespace'])) {
            return false;
        }

        return
            $block['blockName'] === Utils\Constants::BLOCK_QUERY &&
            $block['attrs']['namespace'] === Utils\Constants::BLOCK_ACTIONS_LIST;
    }

    /**
     * Wrap the "carousel-controls" buttons within a nav-type group block.
     *
     * @param array $block - A block data array.
     * @return array - The transformed block.
     */
    private static function transform_block(array $block): array
    {
        if (empty($block['innerBlocks']) || !is_array($block['innerBlocks'])) {
            return $block;
        }

        foreach ($block['innerBlocks'] as $i => $innerBlock) {

            $current = $block['innerBlocks'][$i];
            $is_block_buttons = ($current['blockName'] ?? null) === Utils\Constants::BLOCK_BUTTONS;
            $has_classname = !empty($current['attrs']['className'] ?? '');

            if (!$is_block_buttons || !$has_classname) {
                continue;
            }

            $classes = preg_split('/\s+/', trim($current['attrs']['className']));

            if (!in_array('carousel-controls', $classes, true)) {
                continue;
            }

            unset($current['attrs']['className']);
            $current['innerHTML'] = self::remove_class_from_html($current['innerHTML'], 'carousel-controls');
            $current['innerContent'][0] = self::remove_class_from_html($current['innerContent'][0], 'carousel-controls');

            $block['innerBlocks'][$i] = Utils\Functions::create_group_block(
                [$current],
                [
                    'tagName'   => 'nav',
                    'className' => 'carousel-controls',
                ],
                'Actions List carousel controls',
            );
        }

        return $block;
    }

    /**
     * Removes a class from a piece of html.
     *
     * @param string $html - The HTML.
     * @param string $classToRemove - The class to be removed.
     * @return string.
     */
    private static function remove_class_from_html(string $html, string $classToRemove): string
    {
        return preg_replace_callback(
            '/class="([^"]*)"/',
            function ($matches) use ($classToRemove) {
                $classes = preg_split('/\s+/', trim($matches[1]));

                $classes = array_filter($classes, fn($c) => $c !== $classToRemove);

                if (empty($classes)) {
                    return '';
                }

                return 'class="' . implode(' ', $classes) . '"';
            },
            $html
        );
    }
}
