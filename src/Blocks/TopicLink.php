<?php

/**
 * TopicLink block class
 *
 * @package P4\MasterTheme
 * @since 0.1
 */

 namespace P4\MasterTheme\Blocks;

/**
 * Class TopicLink
 *
 * @package P4\MasterTheme\Blocks
 */
class TopicLink extends BaseBlock
{
    /**
     * Block name.
     *
     * @const string BLOCK_NAME.
     */
    public const BLOCK_NAME = 'topic-link';

    /**
     * Counter constructor.
     */
    public function __construct()
    {
        register_block_type(
            self::get_full_block_name(),
            [ // - Register the block for the editor
                'editor_script' => 'planet4-blocks', // in the PHP side.
                'attributes' => [
                    'title' => [
                        'type' => 'string',
                        'default' => '',
                    ],
                    'description' => [
                        'type' => 'string',
                        'default' => '',
                    ],
                    'completed' => [
                        'type' => 'integer',
                        'default' => '',
                    ],
                    'completed_api' => [
                        'type' => 'string',
                    ],
                    'target' => [
                        'type' => 'integer',
                        'default' => '',
                    ],
                    'text' => [
                        'type' => 'text',
                        'default' => '',
                    ],
                    'style' => [ // Needed to convert existing blocks.
                        'type' => 'string',
                        'default' => '',
                    ],
                ],
            ]
        );

        add_action('enqueue_block_editor_assets', [ self::class, 'enqueue_editor_assets' ]);
        add_action('wp_enqueue_scripts', [ self::class, 'enqueue_frontend_assets' ]);
    }

    /**
     * Required by the `Base_Block` class.
     *
     * @param array $fields Unused, required by the abstract function.
     * @phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     */
    public function prepare_data(array $fields): array
    {
        return [];
    }
    // @phpcs:enable SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
}
