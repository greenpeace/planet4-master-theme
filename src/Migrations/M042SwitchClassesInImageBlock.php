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
            'width' => [
                'v1' => 'width="90"',
                'v2' => 'width=90',
                'v3' => "width='90'",
            ],
            'height' => [
                'v1' => 'height="90"',
                'v2' => 'height=90',
                'v3' => "height='90'",
            ],
            'full' => [
                'v1' => 'width:"90px";height:"90px"',
                'v2' => 'width:90px;height:90px',
                'v3' => "width:'90px';height:'90px'",
            ],
        ],
        'big' => [
            'width' => [
                'v1' => 'width="180"',
                'v2' => 'width=180',
                'v3' => "width='180'",
            ],
            'height' => [
                'v1' => 'height="180"',
                'v2' => 'height=180',
                'v3' => "height='180'",
            ],
            'full' => [
                'v1' => 'width:"180px";height:"180px"',
                'v2' => 'width:180px;height:180px',
                'v3' => "width:'180px';height:'180px'",
            ],
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
     * @param array $block - A block array.
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
     * @param array $blocks - A block array.
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
                var_dump($block);

                $classname = str_replace(
                    [
                        self::CLASSNAME['old']['small'],
                        self::CLASSNAME['old']['big'],
                    ],
                    [
                        self::CLASSNAME['new']['small'],
                        self::CLASSNAME['new']['big'],
                    ],
                    $block['attrs']['className']
                );
                $html = str_replace(
                    [
                        self::CLASSNAME['old']['small'],
                        self::CLASSNAME['old']['big'],

                        self::SIZE_ATTRS['small']['width']['v1'],
                        self::SIZE_ATTRS['small']['width']['v1'],
                        self::SIZE_ATTRS['small']['width']['v1'],
                        self::SIZE_ATTRS['small']['height']['v2'],
                        self::SIZE_ATTRS['small']['height']['v2'],
                        self::SIZE_ATTRS['small']['height']['v2'],
                        self::SIZE_ATTRS['small']['full']['v3'],
                        self::SIZE_ATTRS['small']['full']['v3'],
                        self::SIZE_ATTRS['small']['full']['v3'],

                        self::SIZE_ATTRS['big']['width']['v1'],
                        self::SIZE_ATTRS['big']['width']['v1'],
                        self::SIZE_ATTRS['big']['width']['v1'],
                        self::SIZE_ATTRS['big']['height']['v2'],
                        self::SIZE_ATTRS['big']['height']['v2'],
                        self::SIZE_ATTRS['big']['height']['v2'],
                        self::SIZE_ATTRS['big']['full']['v3'],
                        self::SIZE_ATTRS['big']['full']['v3'],
                        self::SIZE_ATTRS['big']['full']['v3'],
                    ],
                    [
                        self::CLASSNAME['new']['small'],
                        self::CLASSNAME['new']['big'],
                        "",
                        "",
                        "",
                        "",
                        "",
                        "",
                        "",
                        "",
                        "",
                        "",
                        "",
                        "",
                        "",
                        "",
                        "",
                        "",
                        "",
                        "",
                    ],
                    $block['innerHTML']
                );

                unset($block['attrs']['width']);
                unset($block['attrs']['height']);

                $block['attrs']['className'] = $classname;
                $block['innerHTML'] = $html;
                $block['innerContent'][0] = $html;

                var_dump($block);
            }
            self::switch_classes($block['innerBlocks']);
        }
    }
}
