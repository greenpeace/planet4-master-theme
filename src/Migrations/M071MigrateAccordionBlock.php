<?php

namespace P4\MasterTheme\Migrations;

use P4\MasterTheme\MigrationRecord;
use P4\MasterTheme\MigrationScript;

/**
 * Migrate our custom Accordion block to the core one.
 */
class M071MigrateAccordionBlock extends MigrationScript
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
            Utils\Constants::CAROUSEL_HEADER,
            $check_is_valid_block,
            $transform_block,
        );
    }
    // phpcs:enable SlevomatCodingStandard.Functions.UnusedParameter

    /**
     * Check whether the block is a P4 Accordion block
     *
     * @param array $block - A block data array.
     */
    private static function check_is_valid_block(array $block): bool
    {
       // Check if the block is an array, has a 'blockName' key.
        if (!is_array($block) || !isset($block['blockName']) || !empty($block['innerBlocks'])) {
            return false;
        }

        return $block['blockName'] === Utils\Constants::P4_ACCORDION_BLOCK;
    }

    /**
     * Transform the P4 Accordion into a core Accordion block.
     * The old block title and description are turned into a Heading and Paragraph.
     *
     * @param array $block - A block data array.
     * @return array - The transformed blocks (title, description, accordion) in a Group.
     */
    private static function transform_block(array $block): array // Using the parameter as a reference to update it
    {
        $title = $block['attrs']['title'] ?? '';
        $description = $block['attrs']['description'] ?? '';
        $inner_blocks = [];

        // Create Heading block for the old title.
        if ($title) {
            $title_block = Utils\Functions::create_block_heading(
                ['className' => 'page-section-header'],
                $title
            );
            $inner_blocks[] = $title_block;
        }

        // Create Paragraph block for the old description.
        if ($description) {
            $description_block = Utils\Functions::create_block_paragraph(
                ['className' => 'page-section-description'],
                $description
            );
            $inner_blocks[] = $description_block;
        }

        // Create Accordion block for the old tabs.
        $inner_blocks[] = self::create_core_accordion_block($block['attrs']);

        return Utils\Functions::create_group_block($inner_blocks, []);
    }

    /**
     * Transform a P4 Accordion block into a core WordPress Accordion block.
     *
     * @param array $attrs - The old block attributes.
     * @return array - The core block.
     */
    private static function create_core_accordion_block(array $attrs): array
    {
        // With our custom block the default style was green. The other options are "outline" and "light".
        $style = $attrs['className'] ?? 'is-style-green';

        // Build inner blocks for the new Accordion block, based on the old tabs.
        $inner_blocks = [];
        foreach ($attrs['tabs'] as $tab) {
            $heading = $tab['headline'] ?? '';
            // If there's no heading we entirely skip this tab
            if (!$heading) {
                continue;
            }
            $heading_block = Utils\Functions::create_block_accordion_heading($heading);

            $text = $tab['text'] ?? '';
            $panel_content = [];
            if ($text) {
                $text_block = Utils\Functions::create_block_paragraph(
                    [],
                    $text
                );
                $panel_content[] = $text_block;
            }

            $has_button = isset($tab['button']) && !empty($tab['button']);
            if ($has_button) {
                $button_block = Utils\Functions::create_block_buttons(
                    [],
                    [
                        Utils\Functions::create_block_single_button(
                            ['className' => 'is-style-secondary'],
                            $tab['button']['button_text'],
                            $tab['button']['button_url'],
                            isset($tab['button']['button_new_tab']) ? $tab['button']['button_new_tab'] : false,
                        ),
                    ]
                );
                $panel_content[] = $button_block;
            }

            $panel = Utils\Functions::create_block_accordion_panel($panel_content);
            $inner_blocks[] = Utils\Functions::create_block_accordion_item([$heading_block, $panel]);
        }

        return Utils\Functions::create_block_accordion(
            $style,
            $inner_blocks,
        );
    }
}
