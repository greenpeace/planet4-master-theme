<?php

namespace P4\MasterTheme\Migrations;

use P4\MasterTheme\MigrationRecord;
use P4\MasterTheme\MigrationScript;

/**
 * Update Carousel Header Block first image with a new class name
 */
class M0070UpdateCarouselHeaderFirstImage extends MigrationScript
{
    private const FIRST_IMAGE_CLASS = 'carousel-header-image--first';
    private const FIRST_IMAGE_FETCH_PRIORITY = 'fetchpriority="high"';

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
            Utils\Constants::CAROUSEL_HEADER,
            $check_is_valid_block,
            $transform_block,
        );
    }
    // phpcs:enable SlevomatCodingStandard.Functions.UnusedParameter

    /**
     * Check whether the block is a Carousel Header block
     *
     * @param array $block - A block data array.
     */
    private static function check_is_valid_block(array $block): bool
    {
       // Check if the block is an array, has a 'blockName' key.
        if (!is_array($block) || !isset($block['blockName']) || !empty($block['innerBlocks'])) {
            return false;
        }

        // Check if the block is a Carousel Header block.
        return $block['blockName'] === Utils\Constants::CAROUSEL_HEADER;
    }

    /**
     * Modify the Carousel Header block first image to add the "carousel-header-image--first" class
     *
     * @param array $block - A block data array.
     * @return array - The transformed block.
     */
    private static function transform_block(array &$block): array // Using the parameter as a reference to update it
    {
        if (isset($block['innerHTML']) && is_string($block['innerHTML'])) {
            $block['innerHTML'] = self::update_first_image($block['innerHTML']);
        }

        if (!empty($block['innerContent']) && is_array($block['innerContent'])) {
            foreach ($block['innerContent'] as &$inner_content) {
                if (!is_string($inner_content)) {
                    continue;
                }
                $inner_content = self::update_first_image($inner_content);
            }
            unset($inner_content);
        }

        return $block;
    }

     /**
     * Add the new class to the first <img> inside the first ".background-holder".
     * Also adds fetch priority to the first image if not already present.
     * It skips images that already have the class.
     *
     * @param string $html - The block markup.
     * @return string - The updated markup.
     */
    private static function update_first_image(string $html): string
    {
        if ($html === '' || !str_contains($html, 'background-holder')) {
            return $html;
        }

        $pattern = '/(class="[^"]*\bbackground-holder\b[^"]*"[^>]*>\s*)(<img\b[^>]*>)/';

        return preg_replace_callback(
            $pattern,
            function (array $matches): string {
                $img = $matches[2];

                if (str_contains($img, self::FIRST_IMAGE_CLASS)) {
                    return $matches[0];
                }

                if (preg_match('/\sclass="[^"]*"/', $img)) {
                    // The image already has a class attribute: append to it.
                    $img = preg_replace(
                        '/\sclass="([^"]*)"/',
                        ' class="$1 ' . self::FIRST_IMAGE_CLASS . '"',
                        $img,
                        1
                    );
                } else {
                    // No class attribute yet: add one right after "<img".
                    $img = preg_replace(
                        '/^<img\b/',
                        '<img class="' . self::FIRST_IMAGE_CLASS . '"',
                        $img,
                        1
                    );
                }

                // Add fetch priority
                if (!preg_match('/\sfetchpriority=/i', $img)) {
                    $img = preg_replace(
                        '/^<img\b/',
                        '<img ' . self::FIRST_IMAGE_FETCH_PRIORITY,
                        $img,
                        1
                    );
                }

                return $matches[1] . $img;
            },
            $html,
            1
        ) ?? $html;
    }
}
