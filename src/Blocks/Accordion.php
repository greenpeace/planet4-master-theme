<?php

/**
 * Accordion block class.
 *
 * @package P4\MasterTheme
 * @since 0.1
 */

 namespace P4\MasterTheme\Blocks;

/**
 * Class Accordion
 *
 * @package P4\MasterTheme\Blocks
 */
class Accordion extends BaseBlock
{
    /**
     * Block name.
     *
     * @const string BLOCK_NAME.
     */
    public const BLOCK_NAME = 'accordion';

    /**
     * Accordion constructor.
     */
    public function __construct()
    {
        $this->register_accordion_block();
    }

    /**
     * Register Accordion block.
     */
    public function register_accordion_block(): void
    {
        register_block_type(
            self::get_full_block_name(),
            [
                'api_version' => 3,
                'attributes' => [
                    'title' => [
                        'type' => 'string',
                        'default' => '',
                    ],
                    'description' => [
                        'type' => 'string',
                        'default' => '',
                    ],
                    'tabs' => [
                        'type' => 'array',
                        'default' => [],
                        'items' => [
                            'type' => 'object',
                            // In JSON Schema you can specify object properties in the properties attribute.
                            'properties' => [
                                'headline' => [
                                    'type' => 'string',
                                    'default' => '',
                                ],
                                'text' => [
                                    'type' => 'string',
                                    'default' => '',
                                ],
                                'button' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'button_text' => [
                                            'type' => 'string',
                                            'default' => '',
                                        ],
                                        'button_url' => [
                                            'type' => 'string',
                                            'default' => '',
                                        ],
                                        'button_new_tab' => [
                                            'type' => 'boolean',
                                            'default' => false,
                                        ],
                                    ],
                                ],
                            ],
                        ],
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
