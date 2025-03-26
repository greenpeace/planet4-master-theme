<?php

declare(strict_types=1);

namespace P4\MasterTheme\Migrations;

use P4\MasterTheme\MigrationRecord;
use P4\MasterTheme\MigrationScript;

/**
 * Switch classes in the core image block.
 */
class M042SwitchClassesInImageBlock extends MigrationScript
{
    private const SIZE_ATTRS = [
        'small' => [
            'width' => 'width="90"',
            'height' => 'height="90"',
        ],
        'big' => [
            'width' => 'width="180"',
            'height' => 'height="180"',
        ],
    ];
    private const CLASSNAME = [
        'old' => [
            'small' => 'is-style-rounded-90',
            'big' => 'is-style-rounded-180',
        ],
        'new' => [
            'small' => 'is-style-small-circle',
            'big' => 'is-style-big-circle',
        ],
    ];

    /**
     * Switch classes in the core image block and remove some attributes.
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
            Utils\Constants::BLOCK_IMAGE,
            $check_is_valid_block,
            $transform_block,
        );
    }

    /**
     * Check whether a block is a core Image block.
     *
     * @param array $block - A block data array.
     */
    private static function check_is_valid_block(array $block): bool
    {
        // Check if the block is valid.
        if (!is_array($block)) {
            return false;
        }

        // Check if the block has a 'blockName' key.
        if (!isset($block['blockName'])) {
            return false;
        }

        // Check if the block is a core Image block. If not, abort.
        return ($block['blockName'] !== Utils\Constants::BLOCK_IMAGE);
    }

    /**
     * Transform the block.
     *
     * @param array $blocks - A block attrs array.
     * @return array - The transformed block.
     */
    private static function transform_block(array $block): array
    {
        self::switch_classes($block['innerBlocks']);
        return $block;
    }

    /**
     * Replace class names.
     * Remove width and height attributes.
     *
     * @param array $blocks - A block attrs array.
     */
    private static function switch_classes(array &$blocks): void
    {
        foreach ($blocks as &$block) {
            if (
                isset($block['blockName']) &&
                isset($block['attrs']['className']) &&
                $block['blockName'] === Utils\Constants::BLOCK_IMAGE &&
                    (
                        str_contains($block['attrs']['className'], self::CLASSNAME['old']['small']) ||
                        str_contains($block['attrs']['className'], self::CLASSNAME['old']['big'])
                    )
            ) {
                $html = str_replace(
                    [
                        self::CLASSNAME['old']['small'],
                        self::CLASSNAME['old']['big'],
                        self::SIZE_ATTRS['small']['width'],
                        self::SIZE_ATTRS['small']['height'],
                        self::SIZE_ATTRS['big']['width'],
                        self::SIZE_ATTRS['big']['height'],
                    ],
                    [
                        self::CLASSNAME['new']['small'],
                        self::CLASSNAME['new']['big'],
                        "",
                        "",
                        "",
                        "",
                    ],
                    $block['innerHTML']
                );

                unset($block['attrs']['width']);
                unset($block['attrs']['height']);

                $block['attrs']['className'] = $html;
                $block['innerHTML'] = $html;
                $block['innerContent'][0] = $html;
            }
            self::switch_classes($block['innerBlocks']);
        }
    }
}
