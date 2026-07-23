<?php

/**
 * Timeline block class
 *
 * @package P4\MasterTheme
 * @since 0.1
 */

namespace P4\MasterTheme\Blocks;

/**
 * Class Timeline
 *
 * @package P4\MasterTheme\Blocks
 * @since 0.1
 */
class Timeline extends BaseBlock
{
    /**
     * @const string BLOCK_NAME.
     */
    public const BLOCK_NAME = 'timeline';

    /**
     * Timeline constructor.
     */
    public function __construct()
    {
        $this->register_timeline_block();
    }

    /**
     * Register block
     */
    public function register_timeline_block(): void
    {
        // - Register the block for the editor
        // in the PHP side.
        register_block_type(
            self::get_full_block_name(),
            [
                'api_version' => 3,
                'editor_script' => 'planet4-blocks',
                // todo: Remove when all content is migrated.
                'render_callback' => [ self::class, 'hydrate_frontend' ],
                'attributes' => [
                    'timeline_title' => [
                        'type' => 'string',
                        'default' => '',
                    ],
                    'description' => [
                        'type' => 'string',
                        'default' => '',
                    ],
                    'google_sheets_url' => [
                        'type' => 'string',
                        'default' => '',
                    ],
                    'language' => [
                        'type' => 'string',
                        'default' => 'en',
                    ],
                    'timenav_position' => [
                        'type' => 'string',
                        'default' => 'bottom',
                    ],
                    'start_at_end' => [
                        'type' => 'boolean',
                        'default' => false,
                    ],
                    'timeline_id' => [
                        'type' => 'string',
                        'default' => '',
                    ],
                ],
            ]
        );

        if (is_admin()) {
            add_action('enqueue_block_assets', [ self::class, 'enqueue_editor_assets' ]);
        }
        add_action('wp_enqueue_scripts', [ self::class, 'enqueue_frontend_assets' ]);
    }

    /**
     * Required by BaseBlock.
     *
     * @param array $attributes This is the array of fields of this block.
     * @return array The data to be passed in the View.
     * @phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     */
    public function prepare_data(array $attributes): array
    {
        return [];
    }
}
